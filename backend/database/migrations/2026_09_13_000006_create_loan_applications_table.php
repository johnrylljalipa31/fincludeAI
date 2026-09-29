<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->integer('tenure_days')->default(90);
            $table->decimal('interest_rate_apr', 5, 2)->default(24.00);
            $table->decimal('monthly_amortization', 12, 2)->default(0.00);
            $table->decimal('total_repayment', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, approved, disbursed, repaid
            $table->string('purpose')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('repaid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
