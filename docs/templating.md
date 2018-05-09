# Templating

- [Example](#example)
- [Properties](#properties)
- [Methods](#methods)
- [Upgrading from Craft 2](#upgrading-from-craft-2)


## Example

```twig
{% set embeddedAsset = craft.embeddedAssets.get(asset) %}
{% if embeddedAsset and embeddedAsset.isSafe() %}
    {{ embeddedAsset.code }}
{% endif %}
```

## Properties

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
`publishedDate` String | The published date of the embedded asset.
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

### isSafe
`isSafe()`

Checks an embedded asset embed code for URL's that are safe. This is strongly recommended to be used when outputting codes in your templates as a security measure. As embedded assets are pulling in data from external sources, there is a risk of gathering malicious code. The method checks the code itself for scripts and external resources against a [whitelist](configuration.md#whitelist).

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


## Upgrading from Craft 2

Embedded assets from the Craft 2 version of the plugin are fully compatible with the Craft 3 version.
