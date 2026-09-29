<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('smile_account_id')->nullable()->constrained('smile_accounts')->nullOnDelete();
            $table->string('transaction_id')->nullable()->index();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('type')->default('debit'); // credit / debit
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
