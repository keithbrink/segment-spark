<?php

namespace KeithBrink\SegmentSpark;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use KeithBrink\SegmentSpark\Listeners\TeamEventSubscriber;
use KeithBrink\SegmentSpark\Listeners\UserEventSubscriber;
use KeithBrink\SegmentSpark\Observers\LocalInvoiceObserver;
use Laravel\Spark\LocalInvoice;
use Segment;

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
        ], 'config');

        $this->publishes([
            __DIR__.'/resources/assets/js/segment-spark.js' => resource_path('js/segment-spark.js'),
        ], 'resources');

        if ($write_key = $this->app->config->get('segment-spark.write_key')) {
            Segment::init($write_key);

            LocalInvoice::observe(LocalInvoiceObserver::class);

            Event::subscribe(UserEventSubscriber::class);

            Event::subscribe(TeamEventSubscriber::class);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/segment-spark.php', 'segment-spark'
        );
    }
}
