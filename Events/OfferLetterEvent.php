<?php

namespace Modules\Recruit\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Recruit\Entities\RecruitJobOfferLetter;

class OfferLetterEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobOffer;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RecruitJobOfferLetter $jobOffer)
    {
        $this->jobOffer = $jobOffer;
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
