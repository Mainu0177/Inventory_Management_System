<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('contact_person', 100)->nullable()->after('address');
            $table->string('tax_id', 50)->nullable()->after('contact_person');
            $table->string('shipping_address', 255)->nullable()->after('tax_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'tax_id', 'shipping_address']);
        });
    }
};
