# SegmentSpark

An automatic [Segment](https://segment.com/) analytics package for [Laravel Spark](https://spark.laravel.com/), which will track all page views and eCommerce events in Segment.

## Installation

This version requires [PHP](https://php.net) 7, and supports Laravel 5.5+ and Spark 5+.

To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require keithbrink/segment-spark
```

On Laravel 5.5+, the `KeithBrink\SegmentSpark\SegmentSparkServiceProvider` service provider and `KeithBrink\SegmentSpark\SegmentSparkFacade` facade will be automatically discovered so it will not need to be added to your config. On previous versions, you will need to add those manually to your `config/app.php`.

## Configuration

First, set your Segment write key in your .env file:

`MIX_SEGMENT_WRITE_KEY=xxx`

Prefixing the property with `MIX_` will allow the value to be accessed in Javascript.

Next, you'll need to publish the resources:

```bash
$ php artisan vendor:publish --provider="KeithBrink\SegmentSpark\SegmentSparkServiceProvider" --tag=resources
```

You can also optionally publish the `segment-spark.php` config file: 

```bash
$ php artisan vendor:publish --provider="KeithBrink\SegmentSpark\SegmentSparkServiceProvider" --tag=config
```

The first publish command creates a `resources/js/segment-spark.js` file, which is a Vue plugin you will need to include in your `resources/js/app.js` file.

```bash
var SegmentSpark = require('./segment-spark.js');
Vue.use(SegmentSpark);

var app = new Vue({
    mixins: [require('spark')]
});
```

Remember that you will need to run `npm run dev` to compile your assets.

If you would like to associate server-side analytics requests with the client tracked by Google Analytics, you will need to add an exception for cookie encryption in the EncryptCookies middleware at `app\Http\Middleware\EncryptCookies.php`:

```bash
protected $except = [
    '_ga',
];
```

It's also a good idea to add [User-ID Tracking](https://segment.com/docs/destinations/google-analytics/#user-id) to Google Analytics. 

## Usage

After you have set your write key and added Vue plugin to app.js, the package will automatically track all of your page views (including the various tabs on the settings page) and will send events for eCommerce activity, such as viewing, subscribing, renewing, switching, or cancelling a plan. 

Logged in users will be automatically tracked using their user ID (`$user->id`), and their entire user object will be included as traits.

If you are using Google Analytics, the server side events will automatically use the Google Analytics cookie to track events to correct user. Remember to set the server-side tracking ID in Segment's Google Analytics settings.

If you would like to track any custom events, you can use the original [Segment](https://github.com/segmentio/analytics-php) class.

```bash
Segment::track([
    "event"      => "XXX",
    "properties" => [
        "type" => "Video",
    ]
]);
```

Or, use the original Javascript Segment library.

```bash
analytics.track(event, [properties], [options], [callback]);
```

## Google Analytics Enhanced eCommerce

If you would like to use Google Analytics enhanced eCommerce, this package will automatically track two checkout steps: When the user clicks the Select button on one of the plans (Step 1), and when the user clicks the Subscribe button after filling out their billing information (Step 2). You can name those steps whatever you like in your Google Analytics enhanced eCommerce settings.

Remember that you will need to activate Google Analytics enhanced eCommerce on both Google Analytics and Segment.

## License

SegmentSpark is licensed under [The MIT License (MIT)](LICENSE).

## Thanks To

This library borrows code and design structure from [AltThree/Segment](https://github.com/AltThree/Segment) and [Ipunkt/LaravelAnalytics](https://github.com/ipunkt/laravel-analytics). 
