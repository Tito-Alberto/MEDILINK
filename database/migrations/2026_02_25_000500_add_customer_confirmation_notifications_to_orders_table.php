<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_user_id')->nullable()->after('id');
            $table->timestamp('customer_confirmed_notified_at')->nullable()->after('invoice_date');
            $table->timestamp('customer_confirmed_seen_at')->nullable()->after('customer_confirmed_notified_at');

            $table->index('customer_user_id');
            $table->index(['customer_user_id', 'customer_confirmed_seen_at'], 'orders_customer_confirm_seen_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_user_id']);
            $table->dropIndex('orders_customer_confirm_seen_idx');
            $table->dropColumn([
                'customer_user_id',
                'customer_confirmed_notified_at',
                'customer_confirmed_seen_at',
            ]);
        });
    }
};
