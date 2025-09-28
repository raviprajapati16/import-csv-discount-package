<?php

namespace Vendor\UserDiscounts\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Vendor\UserDiscounts\Events\DiscountApplied;
use Vendor\UserDiscounts\Events\DiscountAssigned;
use Vendor\UserDiscounts\Events\DiscountRevoked;
use Vendor\UserDiscounts\Models\Discount;
use Vendor\UserDiscounts\Models\DiscountAudit;
use Vendor\UserDiscounts\Models\UserDiscount;

class DiscountService
{
    public function assign(int $userId, int $discountId, ?int $maxUses = null): UserDiscount
    {
        return DB::transaction(function () use ($userId, $discountId, $maxUses) {
            $userDiscount = UserDiscount::create([
                'user_id' => $userId,
                'discount_id' => $discountId,
                'max_uses' => $maxUses,
                'assigned_at' => now(),
            ]);

            DiscountAudit::create([
                'user_id' => $userId,
                'discount_id' => $discountId,
                'action' => 'assigned',
                'performed_at' => now(),
            ]);

            Event::dispatch(new DiscountAssigned($userDiscount));

            return $userDiscount;
        });
    }

    public function revoke(int $userId, int $discountId): bool
    {
        return DB::transaction(function () use ($userId, $discountId) {
            $userDiscount = UserDiscount::where('user_id', $userId)
                ->where('discount_id', $discountId)
                ->whereNull('revoked_at')
                ->firstOrFail();

            $userDiscount->update(['revoked_at' => now()]);

            DiscountAudit::create([
                'user_id' => $userId,
                'discount_id' => $discountId,
                'action' => 'revoked',
                'performed_at' => now(),
            ]);

            Event::dispatch(new DiscountRevoked($userDiscount));

            return true;
        });
    }

    public function eligibleFor(int $userId): array
    {
        return UserDiscount::with('discount')
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get()
            ->filter(fn($ud) => $ud->isActive())
            ->values()
            ->toArray();
    }

    public function apply(int $userId, float $originalAmount): array
    {
        return DB::transaction(function () use ($userId, $originalAmount) {
            $eligibleDiscounts = $this->getEligibleDiscounts($userId);
            $result = $this->calculateDiscounts($originalAmount, $eligibleDiscounts);

            foreach ($result['applied_discounts'] as $discountData) {
                $userDiscount = UserDiscount::find($discountData['user_discount_id']);
                
                $userDiscount->incrementUses();
                $userDiscount->discount->incrementUses();

                DiscountAudit::create([
                    'user_id' => $userId,
                    'discount_id' => $userDiscount->discount_id,
                    'action' => 'applied',
                    'amount' => $discountData['amount'],
                    'original_amount' => $originalAmount,
                    'final_amount' => $result['final_amount'],
                    'performed_at' => now(),
                ]);

                Event::dispatch(new DiscountApplied(
                    $userDiscount,
                    $originalAmount,
                    $discountData['amount'],
                    $result['final_amount']
                ));
            }

            return $result;
        });
    }

    private function getEligibleDiscounts(int $userId)
    {
        return UserDiscount::with('discount')
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get()
            ->filter(fn($ud) => $ud->isActive())
            ->sortBy(function ($ud) {
                $order = array_flip(config('discounts.stacking.order'));
                return $order[$ud->discount->type] ?? 999;
            });
    }

    private function calculateDiscounts(float $originalAmount, $eligibleDiscounts): array
    {
        $currentAmount = $originalAmount;
        $appliedDiscounts = [];
        $totalPercentage = 0;

        foreach ($eligibleDiscounts as $userDiscount) {
            $discount = $userDiscount->discount;
            $discountAmount = 0;

            if ($discount->type === 'percentage') {
                $maxPercentage = config('discounts.stacking.max_percentage', 80);
                $availablePercentage = $maxPercentage - $totalPercentage;
                
                if ($availablePercentage <= 0) continue;
                
                $applicablePercentage = min($discount->value, $availablePercentage);
                $discountAmount = $currentAmount * ($applicablePercentage / 100);
                $totalPercentage += $applicablePercentage;
            } else {
                $discountAmount = min($discount->value, $currentAmount);
            }

            if ($discountAmount > 0) {
                $currentAmount -= $discountAmount;
                $appliedDiscounts[] = [
                    'user_discount_id' => $userDiscount->id,
                    'discount_id' => $discount->id,
                    'type' => $discount->type,
                    'value' => $discount->value,
                    'amount' => $discountAmount,
                ];
            }
        }

        $currentAmount = $this->applyRounding($currentAmount);

        return [
            'original_amount' => $originalAmount,
            'final_amount' => $currentAmount,
            'total_discount' => $originalAmount - $currentAmount,
            'applied_discounts' => $appliedDiscounts,
        ];
    }

    private function applyRounding(float $amount): float
    {
        $roundingMethod = config('discounts.stacking.rounding_method', 'round');
        $decimals = config('discounts.stacking.rounding', 2);

        return match ($roundingMethod) {
            'ceil' => ceil($amount * pow(10, $decimals)) / pow(10, $decimals),
            'floor' => floor($amount * pow(10, $decimals)) / pow(10, $decimals),
            default => round($amount, $decimals),
        };
    }
}