# Templating

```twig
{% set embeddedAsset = craft.embeddedAssets.get(asset) %}

{{ embeddedAsset ? embeddedAsset.code }}
```

## Upgrading from Craft 2

Embedded assets from the Craft 2 version of the plugin are fully compatible with the Craft 3 version.
