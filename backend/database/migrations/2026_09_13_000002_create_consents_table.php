<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_agreed')->default(false);
            $table->string('policy_version')->default('1.0');
            $table->string('legal_basis')->default('RA 10173 - Data Privacy Act of 2012');
            $table->string('ip_address')->nullable();
            $table->text('scope')->nullable();
            $table->timestamp('agreed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
