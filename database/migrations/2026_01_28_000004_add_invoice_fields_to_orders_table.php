<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_nif')->nullable()->after('customer_address');
            $table->string('invoice_number')->nullable()->after('status');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('delivery_fee');
            $table->timestamp('invoice_date')->nullable()->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_nif', 'invoice_number', 'tax_amount', 'invoice_date']);
        });
    }
};
