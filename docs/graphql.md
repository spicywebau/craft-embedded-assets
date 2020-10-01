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

All properties of an embedded asset listed on the [Templating](templating.md#properties) page can be retrieved from an `embeddedAsset`, as well as the result of [`isSafe`](templating.md#getissafe).
