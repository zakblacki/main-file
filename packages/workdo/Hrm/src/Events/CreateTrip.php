<?php

namespace Workdo\Hrm\Events;

use Illuminate\Queue\SerializesModels;

class CreateTrip
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $request;
    public $travel;

    public function __construct($request, $travel)
    {
        $this->request = $request;
        $this->travel = $travel;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
