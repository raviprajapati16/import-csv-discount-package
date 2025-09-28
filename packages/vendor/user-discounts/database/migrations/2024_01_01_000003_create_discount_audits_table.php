<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discount_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('discount_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('original_amount', 10, 2)->nullable();
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discount_audits');
    }
};