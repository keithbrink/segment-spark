<?php

namespace Keithbrink\SegmentSpark\Observers;

use Segment;
use Laravel\Spark\Subscription;

class SubscriptionsObserver
{
    public function created(Subscription $subscription)
    {
        Segment::track([
            'userId' => $subscription->user->id,
            'event' => 'Subscription Added',

            'properties' => [
                'products' => [[
                    'product_id' => $subscription->user->sparkPlan()->id,
                    'sku' => $subscription->user->sparkPlan()->id,
                    'name' => $subscription->user->sparkPlan()->name,
                    'price' => $subscription->user->sparkPlan()->price,
                    'quantity' => 1,
                ]],
            ],
        ]);
        Segment::flush();
    }

    public function updated(Subscription $subscription)
    {
        $plan = $subscription->user->availablePlans()->first(function ($value) use ($subscription) {
            return $value->id === $subscription->provider_plan;
        });
        if ($subscription->cancelled()) {
            Segment::track([
                'userId' => $subscription->user->id,
                'event' => 'Subscription Cancelled',
                'properties' => [
                    'products' => [[
                        'product_id' => $plan->id,
                        'sku' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'quantity' => 1,
                    ]],
                ],
            ]);
        } elseif ($subscription->active()) {
            Segment::track([
                'userId' => $subscription->user->id,
                'event' => 'Subscription Switched',
                'properties' => [
                    'products' => [[
                        'product_id' => $plan->id,
                        'sku' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'quantity' => 1,
                    ]],
                ],
            ]);
        }
        Segment::flush();
    }
}
