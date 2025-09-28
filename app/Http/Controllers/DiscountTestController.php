<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Vendor\UserDiscounts\Services\DiscountService;
use Vendor\UserDiscounts\Models\Discount;
use App\Models\User;

class DiscountTestController extends Controller
{
    private $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    public function index()
    {
        $discounts = Discount::all();
        // Fetch user IDs from the users table
        $users = User::pluck('name', 'id')->toArray();
        
        // If no users exist, create one
        if (empty($users)) {
            $user = auth()->user() ?? User::factory()->create();
            $users = User::pluck('name', 'id')->toArray();
        }

        return view('discount-test.index', compact('discounts', 'users'));
    }

    public function setUser(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Store selected user ID in session
        session(['test_user_id' => $request->user_id]);

        return response()->json([
            'success' => true,
            'message' => 'User set to: ' . $request->user_id,
            'user_id' => $request->user_id,
        ]);
    }

    public function getUsers(): JsonResponse
    {
        // Fetch user IDs from the users table
        $userIds = User::pluck('id')->toArray();

        // If no users exist, create one
        if (empty($userIds)) {
            $user = auth()->user() ?? User::factory()->create();
            $userIds = [$user->id];
        }

        return response()->json([
            'success' => true,
            'users' => $userIds,
        ]);
    }

    private function getTestUserId()
    {
        $userId = session('test_user_id');
        if ($userId && User::where('id', $userId)->exists()) {
            return $userId;
        }

        // Use authenticated user or create a new one
        $user = auth()->user() ?? User::factory()->create();
        session(['test_user_id' => $user->id]);
        return $user->id;
    }

    public function assignDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'discount_id' => 'required|exists:discounts,id',
            'max_uses' => 'nullable|integer|min:1',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $userId = $request->user_id ?: $this->getTestUserId();
            
            $userDiscount = $this->discountService->assign(
                $userId,
                $request->discount_id,
                $request->max_uses
            );

            return response()->json([
                'success' => true,
                'message' => 'Discount assigned successfully to User ' . $userId . '!',
                'data' => $userDiscount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'message' => 'Something went wrong while assigning the discount.',
            ], 500);
        }
    }

    public function applyDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $userId = $request->user_id ?: $this->getTestUserId();
            
            $result = $this->discountService->apply($userId, $request->amount);

            return response()->json([
                'success' => true,
                'message' => 'Discount applied successfully for User ' . $userId . '!',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'message' => 'Something went wrong while applying the discount.',
            ], 500);
        }
    }

    public function revokeDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'discount_id' => 'required|exists:discounts,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $userId = $request->user_id ?: $this->getTestUserId();
            
            $this->discountService->revoke($userId, $request->discount_id);

            return response()->json([
                'success' => true,
                'message' => 'Discount revoked successfully from User ' . $userId . '!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'message' => 'Something went wrong while revoking the discount.',
            ], 500);
        }
    }

    public function getEligible(Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id ?: $this->getTestUserId();
            
            $eligible = $this->discountService->eligibleFor($userId);

            return response()->json([
                'success' => true,
                'data' => $eligible,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'message' => 'Something went wrong while fetching eligible discounts.',
            ], 500);
        }
    }

    public function createDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:discounts,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        try {
            $discount = Discount::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Discount created successfully!',
                'data' => $discount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'message' => 'Something went wrong while creating the discount.',
            ], 500);
        }
    }
}