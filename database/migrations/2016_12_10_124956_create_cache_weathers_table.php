<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCacheWeathersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cache_weathers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('origin_code')->unique();
            $table->float('precipitation', 4, 2);
            $table->integer('pressure');
            $table->integer('temp_c');
            $table->integer('temp_f');
            $table->integer('wind_speed_miles');
            $table->integer('wind_speed_kmph');
            $table->integer('wind_direction');
            $table->string('description_id');
            $table->string('description_icon');
            $table->string('description_value');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cache_weathers');
    }
}
