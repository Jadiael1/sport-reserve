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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->decimal('amount', 8, 2);
            $table->string('status')->default('WAITING');
            $table->timestamp('payment_date');
            $table->string('url');
            $table->longText('response')->nullable();
            $table->longText('response_payment')->nullable();
            $table->string('checkout_id')->comment('Pagbank checkout payment identification');
            $table->string('charge_id')->nullable()->comment('Pagbank charge payment identification');
            $table->string('self_url');
            $table->string('inactivate_url');
            $table->timestamps();
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_reservation_id_foreign');
        });
        Schema::dropIfExists('payments');
    }
};
