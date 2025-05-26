# Configuration

Embedded Assets has various plugin settings, all of which can be set using the `config/embeddedassets.php` file. Some can also be configured through the Craft control panel; navigate to **Settings &rarr; Embedded Assets** to find these settings.

## `cacheDuration`

Type: `int`
Default: `300`
Available in the control panel: no

The number of seconds that the Craft data cache should store embedded asset requests after they are made.

## `disableVimeoTracking`

Type: `bool`
Default: `false`
Available in the control panel: yes (Disable tracking on Vimeo iframes?)

Whether to force Vimeo video iframes to use the query parameter `dnt=1` to prevent tracking. Note that the embedded asset files will still be saved without `dnt=1` even if this setting is enabled, in case it is disabled in the future.

## `enableAutoRefresh`

Type: `bool`
Default: `true`
Available in the control panel: no

Whether to automatically refresh Instagram embedded assets, which are [required to occasionally be refreshed](https://github.com/spicywebau/craft-embedded-assets/issues/114).

## `extraWhitelist`

Type: `string[]`
Default: `[]`
Available in the control panel: no

A list of domain names that are considered trusted by Embedded Assets, in addition to the [default whitelist][1].

## `facebookKey`

Type: `string`
Default: `''`
Available in the control panel: yes (Facebook Graph API Key)

Sets an API key for use with Facebook and Instagram embedded assets.

## `googleKey`

Type: `string`
Default: `''`
Available in the control panel: yes (Google API Key)

Sets an API key for Google services that require it. Currently, the only service supported by Embedded Assets using this field is Google Maps.

## `maxAssetNameLength`

Type: `int`
Default: `50`
Available in the control panel: no

The maximum number of characters that an embedded asset's asset title can have.

## `maxFileNameLength`

Type: `int`
Default: `50`
Available in the control panel: no

The maximum number of characters that an embedded asset's filename can have, excluding the `'.json'` extension.

## `parameters`

Type: `Array<string, string>[]`
Default: see [Settings.php][4] for the full list
Available in the control panel: yes (Parameters)

List of extra parameters and their values to be sent when retrieving embed data.

## `preventNonWhitelistedUploads`

Type: `bool`
Default: `false`
Available in the control panel: no

By default, Embedded Assets will allow saving of embedded assets with a provider that is not included in either the [default whitelist][1] or the [extra whitelist][2], although they will be treated as untrusted. Setting this to `true` will entirely prevent the saving of embedded assets from untrusted sources.

## `referer`

Type: `string`
Default: `''`
Available in the control panel: yes (HTTP Referer)

Sets a `Referer` HTTP header on the request. By default, no `Referer` header is sent by curl. In some cases, it is necessary to add this header. For example, Vimeo videos with [domain-level privacy](https://developer.vimeo.com/api/oembed/videos#embedding-videos-with-domain-privacy) require this to be set.

## `showThumbnailsInCp`

Type: `bool`
Default: `true`
Available in the control panel: no

Whether to show embedded asset thumbnails in the Craft control panel.

## `useYouTubeNoCookie`

Type: `bool`
Default: `false`
Available in the control panel: yes (Use YouTube nocookie?)

Whether to force YouTube video iframes to use the `youtube-nocookie.com` domain name in place of `youtube.com`. Note that the embedded asset files will still be saved with `youtube.com` even if this setting is enabled, in case it is disabled in the future.

## `whitelist`

Type: `string[]`
Default: see [Settings.php][4] for the full list
Available in the control panel: no

A default list of domain names that are considered trusted by Embedded Assets. Overriding this setting is discouraged unless you need to restrict the websites that can act as providers, in combination with the [`preventNonWhitelistedUploads`][3] setting. If you need to add domain names, use the [`extraWhitelist`][2] setting.

[1]: #whitelist
[2]: #extrawhitelist
[3]: #preventnonwhitelisteduploads
[4]: ../src/models/Settings.php
