# Events

Embedded Assets uses events to allow other plugins or modules to interact with Embedded Assets' functionality.

## BeforeCreateAdapterEvent
This event is fired before the instantiation of the `Adapter`. It allows modifying the `url` and the `config` parameters. 

This event also has a `dispatcherConfig` property that can be modified. This config is used for the instantiation of the `Dispatcher` used by the `Adapter`. As Embedded Assets always uses a `CurlDispatcher` instance, the relevant options can be found at [https://github.com/oscarotero/Embed/tree/3.4.17#the-dispatcher](https://github.com/oscarotero/Embed/tree/3.4.17#the-dispatcher).

### Example

A Vimeo video with domain-level privacy needs to have the `Referer` HTTP header configured, but also needs to be requested with a common browser `User-Agent` HTTP header, otherwise a bare-bones oEmbed JSON object will be returned. This would be setup by adding the correct option to the `dispatchConfig`: 

```php
use spicyweb\embeddedassets\events\BeforeCreateAdapterEvent;
use spicyweb\embeddedassets\Service;
use yii\base\Event;

Event::on(
    Service::class,
    Service::EVENT_BEFORE_CREATE_ADAPTER,
    static function (BeforeCreateAdapterEvent $event) {
        $event->dispatcherConfig[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36';
    }
);
```

