<?php

namespace Keithbrink\SegmentSpark;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class SegmentSparkEventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        'Keithbrink\SegmentSpark\Listeners\UserEventSubscriber',
    ];
}
