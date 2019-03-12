# Changelog

## Unreleased
### Fixed
- Fixed error when saving an embedded asset if Embedded Assets' Parameters setting was empty
- Fixed error when saving an embedded asset if the title contained emoji (now removes any emoji from the asset title)

## 2.0.0 - 2019-02-26
> {note} The plugin’s package name has changed to `spicyweb/craft-embedded-assets`. Embedded Assets will need be updated to 2.0 from a terminal, by running `composer require spicyweb/craft-embedded-assets` and then `composer remove benjamminf/craft-embedded-assets`.

### Added
- Embedded Assets is now maintained by Spicy Web
- Added `extraWhitelist` setting (thanks @benjamminf)

### Changed
- The asset preview controller action now supports passing an `assetId` parameter (thanks @benjamminf)
- Show image preview if large enough / reduce max height for smaller screens (thanks @benjamminf)
- Refactored preview iframe JS into its own class (thanks @benjamminf)
- Improved asset previews in assets table (thanks @benjamminf)

### Fixed
- Fixed issues with not being able to save embedded assets in subfolders (thanks @kyle51north)
- Fixed asset volume permission issue, preventing embedded assets from being saved in Craft 3.1 (thanks @limesquare-nl / @kyle51north)
- Prevent scripts from loading async in preview, causing the asset preview to jump around after it loads (thanks @benjamminf)
- Fixed issue with blank thumbnails showing in asset preview (thanks @benjamminf)

### Removed
-  Removed unnecessary JS size detection in favour of readily available embed data (thanks @benjamminf)

## 1.0.2 - 2018-05-09
### Added
- Added `html` property for conveniently handling checks for embed codes and safety

### Fixed
- Fixed issue with `isSafe` method throwing an error if the `code` property is empty
- If a URL can't be loaded, the UI will now [timeout and show an error notice][#64]
- Improved stability of the [method of reading JSON files][#63]

[#63]: https://github.com/spicywebau/craft-embedded-assets/issues/63
[#64]: https://github.com/spicywebau/craft-embedded-assets/issues/64

## 1.0.1 - 2018-05-09
### Fixed
- Implemented missing legacy properties on embedded assets

## 1.0.0 - 2018-05-08
- Initial release for Craft 3
