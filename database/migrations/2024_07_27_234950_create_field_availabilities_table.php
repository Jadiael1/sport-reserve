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
        Schema::create('field_availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_id');
            $table->string('day_of_week', 10)->comment("Day of the week");
            $table->time('start_time')->comment("Start time for availability");
            $table->time('end_time')->comment("End time for availability");

            $table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('field_availabilities', function (Blueprint $table) {
            $table->dropForeign('field_availabilities_field_id_foreign');
        });
        Schema::dropIfExists('field_availabilities');
    }
};
