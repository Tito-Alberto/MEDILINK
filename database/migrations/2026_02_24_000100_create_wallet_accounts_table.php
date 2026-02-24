<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 30);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('label')->nullable();
            $table->string('currency', 10)->default('AOA');
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id']);
            $table->index('owner_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_accounts');
    }
};

