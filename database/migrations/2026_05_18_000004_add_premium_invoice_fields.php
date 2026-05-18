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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'invoice_no')) {
                $table->string('invoice_no', 100)->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('invoices', 'status')) {
                $table->string('status', 50)->default('Unpaid')->after('payable'); // Unpaid, Paid, Partial
            }
            if (!Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0.00)->after('payable');
            }
            if (!Schema::hasColumn('invoices', 'created_by')) {
                $table->string('created_by', 150)->default('Admin')->after('user_id');
            }
            if (!Schema::hasColumn('invoices', 'quotation_no')) {
                $table->string('quotation_no', 100)->nullable()->after('invoice_no');
            }
        });

        Schema::table('invoice_products', function (Blueprint $table) {
            // make product_id nullable for manual products
            $table->unsignedBigInteger('product_id')->nullable()->change();

            if (!Schema::hasColumn('invoice_products', 'code')) {
                $table->string('code', 100)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('invoice_products', 'name')) {
                $table->string('name', 255)->nullable()->after('code');
            }
            if (!Schema::hasColumn('invoice_products', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('invoice_products', 'uom')) {
                $table->string('uom', 50)->default('pcs')->after('description');
            }
            if (!Schema::hasColumn('invoice_products', 'margin')) {
                $table->integer('margin')->default(0)->after('qty');
            }
            if (!Schema::hasColumn('invoice_products', 'discount')) {
                $table->decimal('discount', 5, 2)->default(0.00)->after('margin');
            }
            if (!Schema::hasColumn('invoice_products', 'total_price')) {
                $table->decimal('total_price', 15, 2)->default(0.00)->after('sale_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_no', 'status', 'paid_amount', 'created_by', 'quotation_no']);
        });

        Schema::table('invoice_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->dropColumn(['code', 'name', 'description', 'uom', 'margin', 'discount', 'total_price']);
        });
    }
};
