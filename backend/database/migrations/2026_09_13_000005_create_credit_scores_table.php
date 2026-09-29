<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(50);
            $table->string('risk_tier')->default('Moderate');
            $table->decimal('projected_disposable_cash_flow', 12, 2)->default(0.00);
            $table->decimal('approved_ceiling', 12, 2)->default(3000.00);
            $table->json('shap_factors')->nullable();
            $table->json('psite_metrics')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_scores');
    }
};
