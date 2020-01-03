<?php

namespace KeithBrink\SegmentSpark\Listeners;

use Cache;
use Segment;

class TeamEventSubscriber
{
    public function onTeamSubscribed($event)
    {
        Segment::track([
            'userId' => $event->team->owner_id,
            'event' => 'Team Subscription Added',

            'properties' => [
                'products' => [[
                    'product_id' => $event->plan->id,
                    'sku' => $event->plan->id,
                    'name' => $event->plan->name,
                    'price' => $event->plan->price,
                    'quantity' => 1,
                ]],
                'companyId' => $event->team->id,
                'companyName' => $event->team->name,
            ],
            'context' => $this->getContext($event->team->owner_id),
        ]);
        Segment::flush();
    }

    public function onTeamSubscriptionUpdated($event)
    {
        $plan = $event->team->sparkPlan();
        $subscription = $event->team->subscription();
        if ($subscription->active()) {
            Segment::track([
                'userId' => $event->team->owner_id,
                'event' => 'Team Subscription Switched',
                'properties' => [
                    'products' => [[
                        'product_id' => $plan->id,
                        'sku' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'quantity' => 1,
                    ]],
                    'companyId' => $event->team->id,
                    'companyName' => $event->team->name,
                ],
                'context' => $this->getContext($event->team->owner_id),
            ]);
        }
        Segment::flush();
    }

    public function onTeamSubscriptionCancelled($event)
    {
        Segment::track([
            'userId' => $event->team->owner_id,
            'event' => 'Team Subscription Cancelled',
            'properties' => [
                'companyId' => $event->team->id,
                'companyName' => $event->team->name,
            ],
            'context' => $this->getContext($event->team->owner_id),
        ]);
    }

    public function onTeamCreated($event)
    {
        Segment::track([
            'userId' => $event->team->owner_id,
            'event' => 'Team Created',
            'properties' => [
                'companyId' => $event->team->id,
                'companyName' => $event->team->name,
            ],
            'context' => $this->getContext($event->team->owner_id),
        ]);
    }

    /**
     * Get the Google Analytics Client ID to send to Segment.
     *
     * The client ID is cached so that it can also be associated
     * with the first invoice / order completion
     */
    public function getContext($user_id)
    {
        $client_id = null;
        if (Cache::has('segment-spark-ga-client-id-user-id-'.$user_id)) {
            $client_id = Cache::get('segment-spark-ga-client-id-user-id-'.$user_id);
        } elseif (request()->hasCookie('_ga')) {
            $client_id = str_replace('GA1.2.', '', request()->cookie('_ga'));
            Cache::put('segment-spark-ga-client-id-user-id-'.$user_id, $client_id, 1440);
        }
        if ($client_id) {
            $context = [
                'Google Analytics' => [
                    'clientId' => $client_id,
                ],
            ];

            return $context;
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Laravel\Spark\Events\Teams\TeamCreated',
            'KeithBrink\SegmentSpark\Listeners\TeamEventSubscriber@onTeamCreated'
        );

        $events->listen(
            'Laravel\Spark\Events\Teams\Subscription\TeamSubscribed',
            'KeithBrink\SegmentSpark\Listeners\TeamEventSubscriber@onTeamSubscribed'
        );

        $events->listen(
            'Laravel\Spark\Events\Teams\Subscription\SubscriptionUpdated',
            'KeithBrink\SegmentSpark\Listeners\TeamEventSubscriber@onTeamSubscriptionUpdated'
        );

        $events->listen(
            'Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled',
            'KeithBrink\SegmentSpark\Listeners\TeamEventSubscriber@onTeamSubscriptionCancelled'
        );
    }
}
