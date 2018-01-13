<?php

namespace Keithbrink\SegmentSpark\Observers;

use Segment;
use Laravel\Spark\LocalInvoice;

class LocalInvoiceObserver
{
    public function created(LocalInvoice $invoice)
    {
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
                'discount' => $invoice->total - $invoice->tax - $invoice->user->sparkPlan()->price,
            ],
        ]);
        Segment::flush();
    }
}
