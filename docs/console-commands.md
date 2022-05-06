# Console Commands

Embedded Assets offers embedded asset refreshing operations using console commands.

## Refreshing Embedded Assets

Console commands can be used to refresh embedded asset data, in case a change on the provider's end has caused some of the embedded asset data to no longer be valid (as long as the embedded asset URL is still valid).

### Refresh By Volume

The following will refresh the data for all embedded assets in a `videos` volume:

```sh
php craft embeddedassets/refresh/by-volume --volume=videos
```

More than one volume can have its embedded assets refreshed at once. The following will refresh embedded assets in the `videos` and `embedded` volumes:

```sh
php craft embeddedassets/refresh/by-volume --volume=videos,embedded
```

### Refresh By Provider

The following will refresh the data for all YouTube and Vimeo embedded assets:

```sh
php craft embeddedassets/refresh/by-provider --provider=YouTube,Vimeo
```

### Refresh All

If you just want to refresh all of your embedded assets, regardless of the provider or the volume they belong to, you can run the following:

```sh
php craft embeddedassets/refresh/all
```

Or, if you want to refresh based on both the volume and the provider:

```sh
php craft embeddedassets/refresh/all --volume=videos --provider=YouTube
```
