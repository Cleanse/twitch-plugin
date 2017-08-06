<?php namespace Cleanse\Twitch\Classes;

use Log;
use GuzzleHttp;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Cleanse\Twitch\Models\Streamer;

class UpdateStreams
{
    /**
     * Gets and sorts streams. Updates online streamers and then sets those offline as off.
     *
     * @param string $list
     * @return void
     */
    public function update($list)
    {
        $streamers = $this->guzzleTwitch($list);
        $offline = explode(',', $list);

        if (empty($streamers)) {
            Log::info('Streamer 505.');
            return;
        }

        foreach ($streamers['streams'] as $streamer) {
            $this->onlineStreamer($streamer);

            $offline = array_diff(
                $offline, [
                $streamer['channel']['_id']
            ]);
        }

        $this->updateOffline($offline);
    }

    /**
     * Gets an array of streamer_id's from our database.
     *
     * @return array
     */
    public function getList()
    {
        $streamers = Streamer::whereNotNull('streamer_id')
            ->get(['streamer_id']);

        $array = [];
        $i = 0;
        $chunk = 0;
        $list = '';

        foreach ($streamers as $streamer) {
            $list .= $streamer->streamer_id . ',';
            $string = rtrim($list, ',');
            $array[$chunk] = $string;
            $i++;

            if ($i % 90 == 0) {
                $list = '';
                $chunk++;
            }
        }

        return $array;
    }

    /**
     * Sets streamer as online in our database.
     *
     * @param array $streamer
     * @return void
     */
    public function onlineStreamer($streamer)
    {
        $update = Streamer::where('streamer_id', '=', $streamer['channel']['_id'])->first();

        $update->name = isset($streamer['channel']['name']) ? $streamer['channel']['name'] : '';
        $update->display_name = isset($streamer['channel']['display_name']) ? $streamer['channel']['display_name'] : '';
        $update->logo = $streamer['channel']['logo'];
        $update->profile_banner = $streamer['channel']['profile_banner'];
        $update->game = $streamer['game'];
        $update->viewers = $streamer['viewers'];
        $update->status = $streamer['channel']['status'];
        $update->views = $streamer['channel']['views'];
        $update->followers = $streamer['channel']['followers'];
        $update->live = 1;

        $update->save();
    }

    /**
     * Checks if our streamers are online.
     *
     * @param string $list
     * @return void
     */
    public function guzzleTwitch($list)
    {
        $client = new GuzzleHttp\Client;

        try {
            $res = $client->get('https://api.twitch.tv/kraken/streams/?limit=100&channel=' . $list,
                [
                    'headers' => [
                        'Client-ID' => Config::get('cleanse.twitch::twitchId'),
                        'Accept' => 'application/vnd.twitchtv.v5+json'
                    ]
                ]
            );

            return json_decode($res->getBody(), true);

        } catch (RequestException $e) {
            Log::info(Psr7\str($e->getRequest()));

            if ($e->hasResponse()) {
                Log::info(Psr7\str($e->getResponse()));
            }

        }
    }

    /**
     * Takes array of offline streamers and sets them to offline in our database.
     *
     * @param array $offline
     * @return void
     */
    public function updateOffline($offline)
    {
        foreach ($offline as $streamer) {
            $this->offlineStreamer($streamer);
        }
    }

    /**
     * If streamer is offline, update our database.
     *
     * @param int $streamer
     * @return void
     */
    public function offlineStreamer($streamer)
    {
        $update = Streamer::where('streamer_id', '=', $streamer)->first();

        $update->game = 'Offline.';
        $update->live = 0;

        $update->save();
    }

    /**
     * Checks database for rows without a streamer_id and updates them.
     *
     * @return void
     */
    public function updateIds()
    {
        $bigList = $this->getIdsList();

        foreach ($bigList as $list) {
            ob_start();

            $streamers = $this->guzzleIdsTwitch($list);

            foreach ($streamers['users'] as $streamer) {
                $this->idStreamer($streamer);
            }

            ob_end_flush();
        }
    }

    /**
     * Sets streamer name and newly found streamer_id.
     *
     * @param array $streamer
     * @return void
     */
    private function idStreamer($streamer)
    {
        $update = Streamer::where('name', '=', $streamer['name'])->first();

        $update->streamer_id = $streamer['_id'];
        $update->display_name = $streamer['display_name'];

        $update->save();
    }

    /**
     * Gets streamer_id from usernames for the v5 api.
     *
     * @param string $list
     * @return array
     */
    private function guzzleIdsTwitch($list)
    {
        $client = new GuzzleHttp\Client();

        $res = $client->get('https://api.twitch.tv/kraken/users?login=' . $list,
            [
                'headers' => [
                    'Client-ID' => Config::get('cleanse.twitch::twitchId'),
                    'Accept' => 'application/vnd.twitchtv.v5+json'
                ]
            ]
        );

        return json_decode($res->getBody(), true);
    }

    /**
     * Gets list of streamers without v5 streamer_id's.
     *
     * @return array
     */
    private function getIdsList()
    {
        $streamers = Streamer::whereNull('streamer_id')
            ->get(['name']);

        $array = [];
        $i = 0;
        $chunk = 0;
        $list = '';

        foreach ($streamers as $streamer) {
            $list .= $streamer->name . ',';
            $string = rtrim($list, ',');
            $array[$chunk] = $string;
            $i++;

            if ($i % 100 == 0) {
                $list = '';
                $chunk++;
            }
        }

        return $array;
    }
}
