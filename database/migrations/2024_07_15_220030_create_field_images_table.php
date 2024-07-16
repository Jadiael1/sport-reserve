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
        Schema::create('field_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_id');
            $table->string('path');

            $table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('field_images', function (Blueprint $table) {
            $table->dropForeign('field_images_field_id_foreign');
        });
        Schema::dropIfExists('field_images');
    }
};
