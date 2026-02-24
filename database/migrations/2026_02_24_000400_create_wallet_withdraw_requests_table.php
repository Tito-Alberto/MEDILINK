<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_account_id')->constrained('wallet_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pharmacy_id')->nullable()->constrained('pharmacies')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('bank_name', 120)->nullable();
            $table->string('account_holder', 160)->nullable();
            $table->string('iban', 80)->nullable();
            $table->string('status', 20)->default('pending'); // pending, processing, paid, rejected
            $table->text('notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_withdraw_requests');
    }
};

