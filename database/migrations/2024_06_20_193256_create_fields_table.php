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
            $table->string('location', 255)->comment("Field location");
            $table->string('type', 50)->comment("Field type (e.g., soccer, basketball)");
            $table->decimal('hourly_rate', 8, 2)->comment('Hourly rate for renting the field');
            $table->string('status', 20)->default('active')->comment("Field status: active or inactive");
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
