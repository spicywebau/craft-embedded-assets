# Templating

- [Example](#example)
- [Properties](#properties)
- [Methods](#methods)
- [Upgrading from Craft 2](#upgrading-from-craft-2)


## Example

```twig
{% set embeddedAsset = craft.embeddedAssets.get(asset) %}
{{ embeddedAsset ? embeddedAsset.html }}
```

## Functions
Embedded asset Twig functions that are globally available.

### get
`craft.embeddedAssets.get(asset)`

Gets the embedded asset model from an asset element. If the asset isn't actually an embedded asset, `null` is returned.

Parameters (\*required) | Description
-|-
\*`asset` Asset | The asset element linked to the embedded asset JSON file.
**Returns** |
EmbeddedAsset&#124;null | The embedded asset, or `null` if it doesn't exist for the asset.

### create
`craft.embeddedAssets.create(data)`

Gets an embedded asset model, given property name/value pairs that are valid for an embedded asset (e.g. if the data is from getting an asset's contents, rather than using `get()` on the asset itself, if the contents are needed for other purposes). If any of the property names or values are invalid, `null` is returned.

Parameters (\*required) | Description
-|-
\*`data` | Embedded asset property name/value pairs.
**Returns** |
EmbeddedAsset&#124;null | The embedded asset, or `null` if the `data` was invalid.


## Properties
The properties of the embedded asset model returned from the [get](#get) function.

Property (\*required) | Description
-|-
\*`title` String | The title of the embedded asset.
`description` String | The description of the embedded asset.
\*`url` String | The URL of the embedded asset.
\*`type` String | The type of the embedded asset. Possible values are `'link'`, `'image'`, `'video'`, and `'rich'`
`tags` Array&lt;String&gt; | The keywords or tags for the embedded asset.
`images` Array&lt;[Image](#image)&gt; | List of all images found for the embedded asset.
`image` String | The URL of the main image for the embedded asset.
`imageWidth` Number | The width of the main image.
`imageHeight` Number | The height of the main image.
`code` HTML | The embed code for the embedded asset.
\*`html` HTML | Usable HTML for the embedded asset. Automatically checks if the embed code is safe to use. If it is, then the code is returned. Otherwise, if the embedded asset is not a `'link'` type and it has an image, an `<img>` tag is returned. Else an `<a>` link tag is returned.
`width` Number | The width of the embed code.
`height` Number | The height of the embed code.
`aspectRatio` Number | The aspect ratio of the embed code.
`authorName` String | The embedded asset author.
`authorUrl` String | The URL of the author.
`providerName` String | The name of the embedded assets provider (eg. `'YouTube'`).
`providerUrl` String | The URL of the provider.
`providerIcons` Array&lt;[Image](#image)&gt; | List of all provider icons found for the embedded asset.
`providerIcon` String | The URL of the main provider icon for the embedded asset.
`publishedTime` String | The published time of the embedded asset.
`license` String | The URL to the embedded asset license.
`feeds` Array&lt;String&gt; | Links to any RSS/Atom feeds found from the URL.

### Image

This is just a plain object with the following keys:

Key (\*required) | Description
-|-
\*`url` String | The URL of the image.
\*`width` Number | The width of the image.
\*`height` Number | The height of the image.
\*`size` Number | The size of the image.
`mime` String | The MIME type of  the image (eg. `'image/jpeg'`).


## Methods
The methods of the embedded asset model returned from the [get](#get) function.

### getIsSafe
`getIsSafe()` or `isSafe`

Checks an embedded asset embed code for URLs that are safe. This is strongly recommended to be used when outputting codes in your templates as a security measure. As embedded assets are pulling in data from external sources, there is a risk of gathering malicious code. The method checks the code itself for scripts and external resources against a [whitelist](configuration.md#whitelist).

Returns | Description
-|-
Boolean | Whether or not the the embed code is safe to use.

### getImageToSize
`getImageToSize(size)`

Returns the image from an embedded asset closest to some size. It favours images that most minimally exceed the supplied size.

Parameters (\*required) | Description
-|-
\*`size` Integer | The preferred size of the image.
**Returns** |
[Image](#image)&#124;null | The image object, or `null` if one is not found.

### getProviderIconToSize
`getProviderIconToSize(size)`

Returns the provider icon from an embedded asset closest to some size. It favours icons that most minimally exceed the supplied size.

Parameters (\*required) | Description
-|-
\*`size` Integer | The preferred size of the provider icon.
**Returns** |
[Image](#image)&#124;null | The image object, or `null` if one is not found.

### getIframeSrc
`getIframeSrc(params)`

If the embedded asset's `code` contains an `<iframe>`, returns the contents of the iframe's `src` attribute, optionally with extra parameters added to the URL.

Parameters (\*required) | Description
-|-
`params` Array | Array of params
**Returns** |
[String](#string) | The URL string.

Example Usage:
```twig
{% set vid = craft.embeddedAssets.get(entry.vid.one()) %}
{{ vid.getIframeSrc(['autoplay=1', 'controls=0', 'playsinline=1']) }}
```

### getIframeCode
`getIframeCode(params)`

If the embedded asset's `code` contains an `<iframe>`, returns the iframe code, optionally with extra parameters added to the URL, or extra attributes added to the `<iframe>` element.

Parameters (\*required) | Description
-|-
`params` Array | Parameters to add to the iframe source URL, in the format `param` or `param=value`
`attributes` Array | Attributes to add to the iframe element, in the format `attribute` or `attribute=value`
`removeAttributes` Array | Attributes to remove from the iframe element
**Returns** |
[\Twig\Markup](#\Twig\Markup) | The code markup.

Example Usage:
```twig
{% set vid = craft.embeddedAssets.get(entry.vid.one()) %}
{{ vid.getIframeCode(
    ['autoplay=1', 'controls=0', 'playsinline=1'],
    ['loading=lazy'],
    ['frameborder']
) }}
```

### getVideoId
`getVideoId()`

Returns an embedded asset's video ID, if the embedded asset is a video from a supported provider.

Supported providers:

- Dailymotion
- Vimeo
- Wistia
- YouTube

Returns | Description
-|-
[String](#string)&#124;null | The video ID, or `null` if the embedded asset is not a video from a supported provider.

Example Usage:
```twig
{% set vid = craft.embeddedAssets.get(entry.vid.one()) %}
{{ vid.getVideoId() }}
```
