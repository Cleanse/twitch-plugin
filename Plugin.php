<?php namespace Cleanse\Twitch;

use Backend;
use Controller;
use Event;
use Queue;
use System\Classes\PluginBase;
use Cleanse\Twitch\Models\Streamer;
use Cleanse\Twitch\Classes\UpdateStreams;

/**
 * Twitch Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Twitch',
            'description' => 'Add custom Twitch.tv streamer components to your website.',
            'author'      => 'Paul Lovato',
            'icon'        => 'icon-video-camera'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Cleanse\Twitch\Components\Streamers' => 'twitchStreamers',
            'Cleanse\Twitch\Components\StreamersMini' => 'twitchStreamersMini'
        ];
    }

    /**
     * Registers new backend permission option.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'cleanse.twitch.access_streamers' => [
                'tab'   => 'Twitch',
                'label' => 'Manage Streamers'
            ]
        ];
    }

    /**
     * Registers navigation for backend controller.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'twitch' => [
                'label'       => 'Twitch',
                'url'         => Backend::url('cleanse/twitch/streamers'),
                'icon'        => 'facetime-video',
                'iconSvg'     => 'plugins/cleanse/twitch/assets/images/twitch.svg',
                'permissions' => ['cleanse.twitch.*'],
                'order'       => 30,

                'sideMenu' => [
                    'new_streamer' => [
                        'label'       => 'New Streamer',
                        'icon'        => 'icon-plus',
                        'url'         => Backend::url('cleanse/twitch/streamers/create'),
                        'permissions' => ['cleanse.twitch.access_streamers']
                    ],
                    'streamersmini' => [
                        'label'       => 'Streamers',
                        'icon'        => 'icon-copy',
                        'url'         => Backend::url('cleanse/twitch/streamers'),
                        'permissions' => ['cleanse.twitch.access_streamers']
                    ]
                ]
            ]
        ];
    }

    public function boot()
    {
        Event::listen('cleanse.twitch.streamer', function () {
            $streams = new UpdateStreams;
            $streams->updateIds();
        });
    }

    /**
     * @param string $schedule
     * Used to update streams.
     */
    public function registerSchedule($schedule)
    {
       $schedule->call(function () {
           $update = new UpdateStreams;
           $streams = $update->getList();

           foreach ($streams as $streamers) {
               Queue::push('\Cleanse\Twitch\Classes\Jobs\GetStreams', ['streamers' => $streamers]);
           }
       })->everyFiveMinutes();
    }
}
