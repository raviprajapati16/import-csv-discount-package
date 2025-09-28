<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add foreign key to products table
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('primary_image_id')
                  ->references('id')
                  ->on('images')
                  ->onDelete('set null');
        });

        // Add foreign keys to uploads table
        Schema::table('uploads', function (Blueprint $table) {
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });

        // Add foreign keys to images table
        Schema::table('images', function (Blueprint $table) {
            $table->foreign('upload_id')
                  ->references('id')
                  ->on('uploads')
                  ->onDelete('cascade');
                  
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['primary_image_id']);
        });

        // Drop foreign keys from uploads table
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // Drop foreign keys from images table
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['upload_id']);
            $table->dropForeign(['product_id']);
        });
    }
};
