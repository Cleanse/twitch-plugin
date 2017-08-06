<?php namespace Cleanse\Twitch\Classes\Jobs;

use Cleanse\Twitch\Classes\UpdateStreams;

class GetStreams
{
    /**
     * Takes comma separated string and sends it to the update class.
     *
     * @param QueueJob $job
     * @param array $data
     * @return void
     */
    public function fire($job, $data)
    {
        $streams = new UpdateStreams;
        $streams->update($data['streamers']);

        $job->delete();
    }
}
