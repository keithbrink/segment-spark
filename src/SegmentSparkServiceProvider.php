<?php

namespace Keithbrink\SegmentSpark;

use Config;
use Illuminate\Support\ServiceProvider;
use Keithbrink\SegmentSpark\Observers\LocalInvoiceObserver;
use Keithbrink\SegmentSpark\Observers\SubscriptionObserver;
use Laravel\Spark\LocalInvoice;
use Laravel\Spark\Subscription;

class SegmentSparkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/segment-spark.php' => config_path('segment-spark.php'),
        ]);

        $this->publishes([
            __DIR__.'/resources/assets/js/segment-spark.js' => resource_path('assets/js/segment-spark.js'),
        ], 'resources');

        if ($write_key = $this->app->config->get('segment-spark.write_key')) {
            $this->app->config->set('segment.write_key', $write_key);
        }

        LocalInvoice::observe(LocalInvoiceObserver::class);
        Subscription::observe(SubscriptionObserver::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
