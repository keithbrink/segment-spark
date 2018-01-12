<?php

namespace Keithbrink\SegmentSpark\Listeners;

use Segment;

class UserEventSubscriber
{
    public function onUserLogin($event)
    {
        Segment::identify([
            'userId' => $event->user->id,
            'traits' => [
                'name'  => $event->user->name,
                'email' => $event->user->email,
            ],
        ]);
    }

    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'Keithbrink\SegmentSpark\Listeners\UserEventSubscriber@onUserLogin'
        );
    }
}
