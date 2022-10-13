<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->integer('booking_id')->primary();
            $table->integer('event_id');
            $table->time('booking_start_time');
            $table->time('booking_end_time');
            $table->date('booking_date');
            $table->dateTime('booked_on');
            $table->string('customer_email', 50);
            $table->string('customer_first_name', 50);
            $table->string('customer_last_name', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
