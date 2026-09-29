<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type')->default('gig_worker');
            $table->string('role')->default('gig_worker');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};