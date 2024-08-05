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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment("Field name");
            $table->json('location')->comment("Field location with latitude and longitude");
            $table->string('type', 50)->comment("Field type (e.g., soccer, basketball)");
            $table->decimal('hourly_rate', 8, 2)->comment('Hourly rate for renting the field');
            $table->string('status', 20)->default('active')->comment("Field status: active or inactive");
            $table->string('cep', 10)->comment("Field postal code");
            $table->string('district', 100)->comment("Field district");
            $table->string('address', 255)->comment("Field address");
            $table->string('number', 10)->comment("Field address number");
            $table->string('city', 100)->comment("Field city");
            $table->string('uf', 100)->comment("Field state");
            $table->string('complement', 255)->nullable()->comment("Field address complement");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
