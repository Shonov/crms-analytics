<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('appointments_count');
            $table->integer('success')->default(0)->nullable();
            $table->integer('canceled')->default(0)->nullable();
            $table->integer('no_show')->default(0)->nullable();
            $table->integer('trial')->default(0)->nullable();
            $table->integer('home_sessions')->default(0)->nullable();
            $table->integer('memberships')->default(0)->nullable();
//            $table->integer('member_to_trial');
            $table->time('full_time')->nullable();
            $table->time('free_time')->nullable();
            $table->string('location');
            $table->date('created_at');
//            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statistics');
    }
}
