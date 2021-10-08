# Configuration

Some of Embedded Assets settings can be configured through the control panel. Navigate to **Settings &rarr; Embedded Assets** to find these settings.

## HTTP Referer
Set a `Referer` HTTP header on the request. By default no `Referer` header is send by curl. In some cases it is necessary to add this header. An example would be a Vimeo video with [domain-level privacy](https://developer.vimeo.com/api/oembed/videos#embedding-videos-with-domain-privacy).
