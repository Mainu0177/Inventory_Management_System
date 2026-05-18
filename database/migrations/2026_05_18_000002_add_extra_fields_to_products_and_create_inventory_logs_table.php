<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->string('uom', 20)->default('pcs')->after('description');
            $table->integer('low_stock_threshold')->default(10)->after('unit');
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['IN', 'OUT']);
            $table->integer('quantity');
            $table->string('reference_id', 100)->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'uom', 'low_stock_threshold']);
        });
    }
};
