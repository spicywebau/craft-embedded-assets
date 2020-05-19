# Changelog

## Unreleased
### Changed
- Updated default whitelist to include TikTok

### Fixed
- Fixed error when trying to embed a Giphy asset

## 2.2.3 - 2020-05-18
### Added
- Added the `getVideoId()` method for an embedded asset from YouTube or Vimeo

### Fixed
- Fixed typo in 'netflix.com' in Embedded Assets' default whitelist

## 2.2.2 - 2020-05-15
### Added
- Added support for Craft 3.4 asset previews

## 2.2.1.1 - 2020-04-22
### Fixed
- Fix PHP 7.4 deprecation #127 - thanks @engram-design

## 2.2.1 - 2020-02-13
### Fixed
- Fix #121 - check for data URL when validating image URL

## 2.2.0 - 2020-02-13
### Added
- Instagram auto refresh (once the Instragram signature expires, the JSON file will be updated)
- Added the replace button on the asset index page

### Fixed
- Fixed bug which was causing embedded assets to save to the wrong asset folder
- add fix for PHP 7.4 deprecation error #122 - thanks @oddnavy

## 2.1.1.1 - 2019-10-28
### Fixed
- Fix releases

## 2.1.1 - 2019-10-28
### Fixed
- Fix #117 - make sure the url has the query string when adding params

## 2.1.0 - 2019-10-18
### Added
- embed button won't be shown if the field doesn't allow json files
- Allow API Keys to be set using env variables.

### Changed
- Update composer craft requirement to ^3.1.0

## 2.0.12 - 2019-09-20
### Fix
- Fix #116 - updating embed library to the latest version to fix the vimeo issue - thanks @cole007

## 2.0.11 - 2019-07-30
### Changed
- switch to yarn.

### Fixed
- fix an issue with previews for assets that's not an embedded asset.

## 2.0.10 - 2019-07-11
### Fixed
- Fix - Dependancy security vulnarability fix with lodash.mergewith

## 2.0.9 - 2019-07-03
### Fixed
- Fix - Register the assets first before getting the default thumbnail

## 2.0.8 - 2019-07-01
### Added
- Added getVideoUrl and getVideoCode. Allows additional params to be added to the embedded video urls.

## 2.0.7 - 2019-06-28
### Fixed
- Make sure to not execute any thumbnail retrieval functions if showThumbnailsInCp is false

## 2.0.6 - 2019-06-12
### Fixed
- Fixed #99 stretched thumbnail issue


## 2.0.5 - 2019-06-06
### Fixed
- Fix vulnarability issue with js-yaml<3.13.1

## 2.0.4 - 2019-05-24
### Fixed
- Fix vulnerability issues with tar <4.4.2
- Actually catch the error thrown if the json file doesn't exist - thanks @engram-design

## 2.0.3 - 2019-04-24
### Added
- Add `showThumbnailsInCp` setting - Thanks @ttempleton
- New icon

## 2.0.2 - 2019-03-18
### Fixed
- Fixed incompatibility with Internet Explorer 11
- Fixed Embedded Assets 2.0.1 incompatibility with Craft 3.1 releases prior to 3.1.13

## 2.0.1 - 2019-03-12
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
