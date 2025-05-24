<?php

namespace Workdo\Hrm\Events;

use Illuminate\Queue\SerializesModels;

class CreateAnnouncement
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    
     public $request;
     public $announcement;
 
     public function __construct($request ,$announcement)
     {
         $this->request = $request;
         $this->announcement = $announcement;
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
