<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAmocrmTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('ALTER TABLE amocrm_tasks CHANGE new new integer DEFAULT 0 NULL;');
        \DB::statement('ALTER TABLE amocrm_tasks CHANGE closed closed integer DEFAULT 0 NULL;');
        \DB::statement('ALTER TABLE amocrm_tasks CHANGE not_closed not_closed integer DEFAULT 0 NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement('ALTER TABLE amocrm_tasks CHANGE new new integer NOT NULL;');
        \DB::statement('ALTER TABLE amocrm_tasks CHANGE closed closed integer NOT NULL;');
        \DB::statement('ALTER TABLE amocrm_tasks CHANGE not_closed not_closed integer NOT NULL;');
        \DB::statement('ALTER TABLE amocrm_tasks ALTER new DROP DEFAULT;');
        \DB::statement('ALTER TABLE amocrm_tasks ALTER closed DROP DEFAULT;');
        \DB::statement('ALTER TABLE amocrm_tasks ALTER not_closed DROP DEFAULT;');
    }
}
