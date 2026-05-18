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
        // 1. Quotations
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('customer_id');
            
            $table->string('quotation_no', 100)->unique();
            $table->string('pr_no', 100)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('status', 50)->default('Draft'); // Draft, Sent, Invoiced, Final, Rejected, Cancelled
            $table->timestamp('valid_until')->nullable();
            $table->text('terms')->nullable();
            $table->string('created_by', 150)->default('Admin');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnUpdate()->restrictOnDelete();
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // 2. Quotation Products
        Schema::create('quotation_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('product_id')->nullable(); // nullable for manual items
            
            $table->string('code', 100)->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('uom', 50)->default('pcs');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->integer('margin')->default(0);
            $table->decimal('discount', 5, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);

            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // 3. Delivery Challans
        Schema::create('delivery_challans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('customer_id');

            $table->string('challan_no', 100)->unique();
            $table->string('pr_no', 100)->nullable();
            $table->string('quotation_no', 100)->nullable();
            $table->string('po_no', 100)->nullable();
            $table->string('status', 50)->default('Pending'); // Pending, Delivered
            $table->string('created_by', 150)->default('Admin');
            $table->timestamp('delivered_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnUpdate()->restrictOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // 4. Delivery Challan Products
        Schema::create('delivery_challan_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_challan_id');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('code', 100)->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('uom', 50)->default('pcs');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->integer('margin')->default(0);
            $table->decimal('discount', 5, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);

            $table->foreign('delivery_challan_id')->references('id')->on('delivery_challans')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_challan_products');
        Schema::dropIfExists('delivery_challans');
        Schema::dropIfExists('quotation_products');
        Schema::dropIfExists('quotations');
    }
};
