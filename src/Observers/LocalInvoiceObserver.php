<?php

namespace KeithBrink\SegmentSpark\Observers;

use Cache;
use Laravel\Spark\LocalInvoice;
use Laravel\Spark\Spark;
use Segment;

class LocalInvoiceObserver
{
    public function created(LocalInvoice $invoice)
    {
        $billable_model = $invoice->user_id ? $invoice->user : $invoice->team;
        $user_id = $invoice->user_id ? $invoice->user_id : $invoice->team->owner_id;

        $discount_amount = $billable_model->sparkPlan()->price - $invoice->total - $invoice->tax;
        if (Spark::$billsUsing == 'stripe' && $discount_amount > 0) {
            if ($discount = $billable_model->asStripeCustomer()->discount) {
                $discount_code = $discount->coupon ? $discount->coupon->id : null;
            }
        }
        if ($billable_model->localInvoices()->count() > 1) {
            $integrations = [
                'Google Analytics' => false,
            ];
        } else {
            $integrations = [
                'All' => true,
            ];
        }

        $properties = [
            'products' => [[
                'product_id' => $billable_model->sparkPlan()->id,
                'sku' => $billable_model->sparkPlan()->id,
                'name' => $billable_model->sparkPlan()->name,
                'price' => $billable_model->sparkPlan()->price,
                'quantity' => 1,
            ]],
            'order_id' => $invoice->id,
            'total' => $invoice->total,
            'tax' => $invoice->tax,
            'discount' => $billable_model->sparkPlan()->price - $invoice->total - $invoice->tax,
            'coupon' => isset($discount_code) ? $discount_code : null,
        ];

        if ($invoice->team_id) {
            $properties['companyId'] = $billable_model->id;
            $properties['companyName'] = $billable_model->name;
        }

        Segment::track([
            'userId' => $user_id,
            'event' => 'Order Completed',
            'properties' => $properties,
            'integrations' => $integrations,
            'context' => $this->getContext($user_id),
        ]);
        Segment::flush();
    }

    /**
     * Get the Google Analytics Client ID to send to Segment
     * from the cached result from the user event subscriber.
     */
    public function getContext($user_id)
    {
        if (Cache::has('segment-spark-ga-client-id-user-id-'.$user_id)) {
            $client_id = Cache::get('segment-spark-ga-client-id-user-id-'.$user_id);
            $context = [
                'Google Analytics' => [
                    'clientId' => $client_id,
                ],
            ];

            return $context;
        }
    }
}
