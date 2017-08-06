<?php namespace Cleanse\Twitch\Components;

use Config;
use Cms\Classes\ComponentBase;
use Cleanse\Twitch\Models\Streamer;

class StreamersMini extends ComponentBase
{
    /**
     * @var Collection A collection of streamers to display
     */
    public $streamers;
    public $carebears;

    public function componentDetails()
    {
        return [
            'name' => 'List Streamers Mini',
            'description' => 'Grabs the streamers who are currently live on Twitch for a side bar.'
        ];
    }

    public function onRun()
    {
        $this->streamers = $this->page['streamers'] = $this->loadStreamers();
    }

    public function loadStreamers()
    {
        return Streamer::where('live', 1)
            ->orderBy('viewers', 'desc')
            ->take(Config::get('cleanse.twitch::miniMax', 10))
            ->get();
    }
}
