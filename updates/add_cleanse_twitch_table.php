<?php namespace Cleanse\Twitch\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddCleanseTwitchTable extends Migration
{
    public function up()
    {
        Schema::create('cleanse_twitch_streamers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('streamer_id')->nullable();
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('profile_banner')->nullable();
            $table->string('game')->nullable();
            $table->string('status')->nullable();
            $table->integer('viewers')->default(0);
            $table->integer('views')->default(0);
            $table->integer('followers')->default(0);
            $table->smallInteger('live')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cleanse_twitch_streamers');
    }
}
