# GraphQL

As of Embedded Assets 2.4.0, Embedded Assets supports the GraphQL functionality provided by Craft CMS Pro.  Embedded asset data can be retrieved by accessing an `embeddedAsset` property on a Craft asset field.  A basic example follows, in which an embedded asset's title is retrieved:

```
query {
  entry(slug: "homepage") {
    ... on homepage_homepage_Entry {
      embeddedAssetField {
        embeddedAsset {
          title
        }
      }
    }
  }
}
```

If the asset field contains any assets that are not embedded assets, `embeddedAsset` will just return `null` for those assets.

All properties of an embedded asset listed on the [Templating](templating.md#properties) page can be retrieved from an `embeddedAsset`, as well as the following:

- `isSafe` - checks an embedded asset embed code for URLs that are safe. See the documentation for the `EmbeddedAsset` model's [`getIsSafe()`](templating.md#getissafe) method for more details.
- `iframeSrc` - if the embedded asset's `code` is an `<iframe>`, returns the `<iframe>`'s `src` attribute with extra parameters added. Requires the `params` argument, passed in the same format as the `EmbeddedAsset` model's [`getIframeSrc()`](templating.md#getiframesrc) method.
- `iframeCode` - if the embedded asset's `code` is an `<iframe>`, returns the `<iframe>` with extra parameters added to the `src` attribute, or extra attributes added to the `<iframe>` element. Requires the `params` argument and also accepts the `attributes` argument, passed in the same format as the `EmbeddedAsset` model's [`getIframeCode()`](templating.md#getiframecode) method.
