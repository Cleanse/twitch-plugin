<?php namespace Cleanse\Twitch\Models;

use Event;
use Model;

/**
 * @property integer $id
 * @property integer $streamer_id
 * @property string $name
 * @property string $display_name
 * @property string $logo
 * @property string $profile_banner
 * @property string $game
 * @property string $status
 * @property integer $viewers
 * @property integer $views
 * @property integer $followers
 * @property integer $live
 */
class Streamer extends Model
{
    public $table = 'cleanse_twitch_streamers';

    public static function boot()
    {
        parent::boot();

        static::saved(function($model) {
            Event::fire('cleanse.twitch.streamer');
        });
    }

    public function beforeCreate()
    {
        $this->name = strtolower($this->name);
    }
}
