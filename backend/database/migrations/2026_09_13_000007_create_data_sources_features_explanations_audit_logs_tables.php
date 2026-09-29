<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Data_Sources: Linked e-wallets, digital banks, and gig platform accounts
        if (!Schema::hasTable('data_sources')) {
            Schema::create('data_sources', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('provider'); // e.g. GCash, Maya, Grab, Foodpanda, Shopee, BDO, BPI
                $table->string('source_type')->default('e_wallet'); // e_wallet, digital_bank, gig_platform
                $table->string('account_id')->nullable();
                $table->string('account_name')->nullable();
                $table->string('status')->default('connected'); // connected, disconnected, expired
                $table->timestamp('connected_at')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // Add data_source_id to transactions if not present
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'data_source_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('data_source_id')->nullable()->after('smile_account_id')->constrained('data_sources')->nullOnDelete();
            });
        }

        // 2. Features: 12 primary credit risk indicators
        if (!Schema::hasTable('features')) {
            Schema::create('features', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->integer('transaction_count')->default(0);
                $table->decimal('avg_monthly_income', 12, 2)->default(0.00);
                $table->decimal('income_consistency', 8, 4)->default(0.0000); // CV
                $table->decimal('income_growth_rate', 8, 4)->default(0.0000);
                $table->decimal('inflow_outflow_ratio', 8, 4)->default(0.0000);
                $table->decimal('cash_flow_stability', 12, 2)->default(0.00);
                $table->integer('active_financial_days')->default(0);
                $table->decimal('avg_monthly_balance', 12, 2)->default(0.00);
                $table->decimal('min_account_balance', 12, 2)->default(0.00);
                $table->decimal('transaction_frequency', 8, 2)->default(0.00); // tx per month
                $table->decimal('payment_consistency', 8, 4)->default(0.0000);
                $table->decimal('gig_earnings_activity', 8, 4)->default(0.0000); // ratio of gig income
                $table->decimal('burn_rate_trajectory', 8, 4)->default(1.0000);
                $table->json('raw_features')->nullable();
                $table->timestamp('computed_at')->nullable();
                $table->timestamps();
            });
        }

        // Add model_version to credit_scores if not present
        if (Schema::hasTable('credit_scores') && !Schema::hasColumn('credit_scores', 'model_version')) {
            Schema::table('credit_scores', function (Blueprint $table) {
                $table->string('model_version')->default('2.0.0')->after('risk_tier');
            });
        }

        // 3. Explanations: Mathematical contribution values and direction of SHAP/LIME indicators
        if (!Schema::hasTable('explanations')) {
            Schema::create('explanations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('credit_score_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('feature_key');
                $table->decimal('raw_value', 14, 4)->default(0.0000);
                $table->decimal('shap_value', 10, 4)->default(0.0000);
                $table->integer('impact_points')->default(0);
                $table->string('direction')->default('positive'); // positive, negative, neutral
                $table->text('display_text')->nullable();
                $table->timestamps();
            });
        }

        // 4. Audit_Logs: System activity logs for security auditing
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action'); // e.g. login, consent_granted, data_synced, score_generated, loan_applied
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('details')->nullable();
                $table->string('status')->default('success');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('explanations');
        Schema::dropIfExists('features');
        Schema::dropIfExists('audit_logs');
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'data_source_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('data_source_id');
            });
        }
        Schema::dropIfExists('data_sources');
    }
};
