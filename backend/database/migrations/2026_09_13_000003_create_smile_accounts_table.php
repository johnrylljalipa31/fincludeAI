<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smile_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('smile_user_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('provider')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('connected');
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smile_accounts');
    }
};
