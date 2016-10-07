# Embedded assets for Craft ![Craft 2.5](https://img.shields.io/badge/craft-2.5-red.svg?style=flat-square)

Add embeddable media such as YouTube videos to your assets manager.

## Demo

![Embedded Assets index demo](screenshots/demo-01.gif)
![Embedded Assets URL input demo](screenshots/demo-02.gif)

## Installation

**Embedded Assets requires Craft CMS 2.5+ and PHP 5.5+ minimum**

1. Copy the `embeddedassets` folder to your plugins directory and install the plugin through the control panel.
2. Modify your `general.php` configuration file to include the setting `'extraAllowedFileExtensions' => 'json'`. This
   needs to be done because the embedded assets are stored as JSON files, and Craft doesn't allow JSON files by default. 

And you're done!

## Usage

### Templates

```twig
{% for asset in assets %}
	{% set embed = craft.embeddedAssets.fromAsset(asset) %}
	{% if embed %}
		{{ embed.safeHtml|raw }}
	{% endif %}
{% endfor %}
```

As embedded assets are still normal asset files, you can access them through the `craft.assets` elements API. In order
to get the actual embed data, call the `craft.embeddedAssets.fromAsset(asset)` function where `asset` is the asset
model.

### Fields

As embedded assets are stored as JSON files, they can be targeted when creating Asset fields. If you want to allow/disallow embedded assets from being selected, you can modify the "Restrict allowed file types?" setting to either include or exclude JSON files.

Unfortunately there was no way of explicitly specifying embedded types, but this is the next best thing.

## API

### `craft.embeddedAssets.*`

| Function             | Description                                                                                                                                             |
|----------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| `isEmbedded(asset)`  | Determines if an asset is an embedded asset. This method is usually not necessary as you can use the `fromAsset` method and check if it returns `null`. |
| `fromAsset(asset)`   | Returns the embedded asset model from an asset if it's an embedded asset, otherwise returns `null`.                                                     |
| `fromAssets(assets)` | Takes an array of assets and returns an array consisting of only embedded assets.                                                                       |

### `EmbeddedAssetModel`

| Parameter         | Description                                                                                            |
|-------------------|--------------------------------------------------------------------------------------------------------|
| `id`              | The ID of the asset.                                                                                   |
| `type`            | The type of media the embedded asset is. Either `photo`, `video`, `link` or `rich`.                    |
| `url`             | The URL of the media.                                                                                  |
| `requestUrl`      | The URL that was originally provided by the user.                                                      |
| `title`           | The title of the media.                                                                                |
| `description`     | The description of the media.                                                                          |
| `authorName`      | The author of the media.                                                                               |
| `authorUrl`       | The URL to the author.                                                                                 |
| `providerName`    | The provider of the media.                                                                             |
| `providerUrl`     | The URL to the provider.                                                                               |
| `cacheAge`        | The recommended cache lifetime of the media, in seconds.                                               |
| `thumbnailUrl`    | The thumbnail of the media.                                                                            |
| `thumbnailWidth`  | The width of the thumbnail, in pixels.                                                                 |
| `thumbnailHeight` | The height of the thumbnail, in pixels.                                                                |
| `html`            | The embeddable HTML of the media. It is recommended not to use this parameter in favour of `safeHtml`. |
| `safeHtml`        | The embeddable HTML of the media that's been purified.                                                 |
| `width`           | The width of the media, in pixels.                                                                     |
| `height`          | The height of the media, in pixels.                                                                    |

For more information, read through the [oEmbed spec](http://oembed.com/).

## Configuration

You can configure the settings of the plugin through the control panel. Advanced configuration options are found in the
`config.php` file, which can be overridden by including a `embeddedassets.php` file in your `config` directory.

### Whitelist

This is a list of domains (separated by a new line) that will be preserved when purifying the HTML. Purified HTML is
found in the `safeHtml` parameter, and is recommended to be used over the `html` parameter. Domains
in this list will automatically include all subdomains, directories and protocols, so they do not need to (and should not) be
specified.

It's important to emphasise that this whitelist does not apply to links added through the modal window (all links are
welcome here). This means that you only need to consider URL's in the `html` parameter. Keep in mind some providers will
use different or multiple domains here - for example, YouTube may embed videos with the `youtube-nocookie.com` domain.

The plugin comes with this default whitelist, which should be enough for the majority of uses:

- 23hq.com
- app.net
- animoto.com
- aol.com
- collegehumor.com
- dailymotion.com
- deviantart.com
- embed.ly
- fav.me
- flic.kr
- flickr.com
- funnyordie.com
- hulu.com
- imgur.com
- instagr.am
- instagram.com
- kickstarter.com
- meetup.com
- meetup.ps
- nfb.ca
- official.fm
- rdio.com
- soundcloud.com
- twitter.com
- vimeo.com
- vine.co
- wikipedia.org
- wikimedia.org
- wordpress.com
- youtu.be
- youtube.com
- youtube-nocookie.com

### Parameters

Extra `GET` parameters to be supplied when requesting media. These parameters may or may not be recognised or followed
by providers, so keep that in mind. The plugin comes default with two parameters:

- `maxwidth` (1920)
- `maxheight` (1080)

These are parameters specified in section 2.2 in the [oEmbed spec](http://oembed.com/). Certain providers may allow
other parameters.

### Filename Prefix

This parameter is found in the `config.php` file, and specifies the string to prefix embed files with. This setting is
not normally required to be changed, but can be modified in case you already have JSON files in your assets folders
that are prefixed with `embed_`, and do not want them to be recognised by the plugin.

## FAQ

### Embedded Assets won't install

As mentioned above, Embedded Assets requires Craft CMS 2.5+ and PHP 5.5+ as a minimum.

### How can I get YouTube/Vimeo ID's?

Embedded Assets goal is to be agnostic to the provider of media, by adhering to the oEmbed spec. The benefit for this is so that the plugin is not tied to a fixed set of providers – any website link that contains oEmbed data can be used as an embedded asset. While it would be useful, the downside to adding vendor-specific functionality means that the plugin would then be dependent on one or many third-party services.

That said, there's a way of getting this data, using a little bit of Twig markup. The following code will extract the ID from a YouTube URL (you could also use the same approach for Vimeo and other URL's):

```twig
{% set embed = craft.embeddedAssets.fromAsset(asset) %}
{% if embed.providerName|lower == 'youtube' %}
    {% set videoId = embed.url|replace('/.+watch\\?v=(.+)/', '$1') %}
    ...
{% endif %}
```
