<?php

namespace Keithbrink\SegmentSpark\Observers;

use Segment;
use Laravel\Spark\Spark;
use Laravel\Spark\LocalInvoice;

class LocalInvoiceObserver
{
    public $context = [];

    public function __construct()
    {
        if (request()->cookie('_ga')) {
            $client_id = str_replace('GA1.2.', '', request()->cookie('_ga'));
            $context = [
                'Google Analytics' => [
                    'clientId' => $client_id,
                ],
            ];
            $this->context = $context;
        }
    }

    public function created(LocalInvoice $invoice)
    {
        $discount_amount = $invoice->user->sparkPlan()->price - $invoice->total - $invoice->tax;
        if (Spark::$billsUsing == 'stripe' && $discount_amount > 0) {
            if ($discount = $invoice->user->asStripeCustomer()->discount) {
                $discount_code = $discount->coupon ? $discount->coupon->id : null;
            }
        }
        Segment::track([
            'userId' => $invoice->user->id,
            'event' => 'Order Completed',

            'properties' => [
                'products' => [[
                    'product_id' => $invoice->user->sparkPlan()->id,
                    'sku' => $invoice->user->sparkPlan()->id,
                    'name' => $invoice->user->sparkPlan()->name,
                    'price' => $invoice->user->sparkPlan()->price,
                    'quantity' => 1,
                ]],
                'order_id' => $invoice->id,
                'total' => $invoice->total,
                'tax' => $invoice->tax,
                'discount' => $invoice->user->sparkPlan()->price - $invoice->total - $invoice->tax,
                'coupon' => isset($discount_code) ? $discount_code : null,
            ],
            'context' => $this->context,
        ]);
        Segment::flush();
    }
}
