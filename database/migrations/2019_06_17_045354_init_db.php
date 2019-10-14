<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InitDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('statistics');

        Schema::create('acuity_appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('location');
            $table->integer('completed_count');
            $table->integer('no_show_count');
            $table->integer('cancelled_count');
            $table->date('date');
        });

        Schema::create('acuity_scheduled_appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('count');
            $table->date('month');
        });

        Schema::create('operators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('amocrm_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('operator_id')->unsigned();
            $table->integer('new');
            $table->integer('closed');
            $table->integer('not_closed');
            $table->date('date');
            $table->foreign('operator_id')->references('id')->on('operators');
        });

        Schema::create('amocrm_sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('operator_id')->unsigned();
            $table->integer('new_member');
            $table->integer('renew_member');
            $table->date('date');
            $table->foreign('operator_id')->references('id')->on('operators');
        });

        Schema::create('asterisk_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('operator_id')->unsigned();
            $table->string('type');
            $table->string('status');
            $table->integer('duration');
            $table->date('date');
            $table->foreign('operator_id')->references('id')->on('operators');
        });

        Schema::create('ga_statistic', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('goals_achieved');
            $table->integer('site_click');
            $table->integer('cost_per_goal');
            $table->date('date');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amocrm_tasks');
        Schema::dropIfExists('amocrm_sales');
        Schema::dropIfExists('asterisk_calls');
        Schema::dropIfExists('ga_statistic');
        Schema::dropIfExists('operators');
        Schema::dropIfExists('acuity_appointments');
        Schema::dropIfExists('acuity_scheduled_appoitments');
    }
}
