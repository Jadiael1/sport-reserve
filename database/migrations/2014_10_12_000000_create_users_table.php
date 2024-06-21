<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment("User's full name");
            $table->char('cpf', 11)->unique()->comment("User's CPF (Cadastro de Pessoas Físicas)");
            $table->string('phone', 15)->unique()->comment("User's phone number");
            $table->string('email', 255)->unique()->comment("User's email address");
            $table->timestamp('email_verified_at')->nullable()->comment("Timestamp for email verification");
            $table->string('password')->comment("user password");
            $table->boolean('is_admin')->default(false);
            $table->rememberToken()->comment("Token for remembering user login");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
