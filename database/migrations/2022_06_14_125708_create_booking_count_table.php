<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_count', function (Blueprint $table) {
            $table->integer('count_id')->primary();
            $table->dateTime('date_time');
            $table->integer('event_id');
            $table->integer('count')->default(0);
            
            $table->unique(['event_id', 'date_time'], 'event_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_count');
    }
}
