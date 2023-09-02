# Changelog

## 3.1.8 - 2023-09-02

### Fixed
- Fixed an error caused by Embedded Assets, that occurred when changing subfolders while browsing assets to add to a field

## 3.1.7 - 2023-08-30

### Fixed
- Fixed an error that occurred when embedding using an assets field, if the field is set to restrict assets to a single location and the location uses variables

## 3.1.6 - 2023-08-01

### Changed
- The default `whitelist` plugin setting now includes `wistia.com` and `wistia.net`
- `spicyweb\embeddedassets\models\EmbeddedAsset::getIframeCode()` now supports Wistia videos
- `spicyweb\embeddedassets\models\EmbeddedAsset::getIframeSrc()` now supports Wistia videos
- `spicyweb\embeddedassets\models\EmbeddedAsset::getVideoId()` now supports Wistia videos

## 3.1.5 - 2023-04-15

### Added
- Added French translations (thanks @scandella)

### Fixed
- Fixed an error that could occur when trying to load an embedded asset created on Craft 2

## 3.1.4 - 2023-04-12

### Fixed
- Fixed an error that occurred when trying to save a direct Vimeo URL as an embedded asset

## 3.1.3 - 2023-03-25

### Fixed
- Fixed Craft 4.4 compatibility issues

## 3.1.2 - 2023-03-25

### Fixed
- Fixed a bug where some strings weren't translatable when they should have been
- Fixed an error that occurred when trying to embed a TikTok asset

## 3.1.1 - 2023-01-07

### Changed
- `spicyweb\embeddedassets\models\EmbeddedAsset::getVideoId()` now supports Dailymotion videos

## 3.1.0 - 2022-11-02

### Added
- Added `spicyweb\embeddedassets\assets\main\MainAsset`
- Added `spicyweb\embeddedassets\assets\preview\PreviewAsset`
- Added `spicyweb\embeddedassets\errors\NotWhitelistedException`
- Added `spicyweb\embeddedassets\models\Settings::$preventNonWhitelistedUploads` (defaults to `false`, adds the ability to prevent the saving of embedded assets from providers that are not whitelisted in the plugin settings)

### Changed
- Embedded Assets' JavaScript source has been converted to TypeScript

### Deprecated
- Deprecated `spicyweb\embeddedassets\assets\Main`; use `spicyweb\embeddedassets\assets\main\MainAsset` instead
- Deprecated `spicyweb\embeddedassets\assets\Preview`; use `spicyweb\embeddedassets\assets\preview\PreviewAsset` instead

## 3.0.5 - 2022-08-30

### Added
- Added the `$removeAttributes` argument to `spicyweb\embeddedassets\models\EmbeddedAsset::getIframeCode()`, for removing tag attributes from an iframe

### Changed
- Updated JavaScript dependencies

## 3.0.4 - 2022-06-26

### Added
- Added a second parameter (`$attributes`) to `spicyweb\embeddedassets\models\EmbeddedAsset::getIframeCode()`, for adding attributes to the iframe element, in the format `attribute` or `attribute=value`

### Changed
- The first parameter (`$params`) to `spicyweb\embeddedassets\models\EmbeddedAsset::getIframeCode()` is no longer required

## 3.0.3 - 2022-06-15

### Fixed
- Fixed a bug where non-admin users who have the permission to save assets in a volume were unable to save embedded assets in that volume (thanks @aodihis)

## 3.0.2 - 2022-05-27

### Added
- Added the `enableAutoRefresh` plugin setting (defaults to `true`) for controlling whether Instagram embedded assets are auto-refreshed
- Added `spicyweb\embeddedassets\Service::refreshEmbeddedAsset()`
- Added `spicyweb\embeddedassets\errors\RefreshException`

## 3.0.1 - 2022-05-18

### Fixed
- Fixed a bug where trying to save an embedded asset to a subfolder of a volume's filesystem would save the embedded asset in the filesystem's root folder instead

## 3.0.0 - 2022-05-05

### Added
- Added Craft 4 compatibility

### Removed
- Removed Craft 3 compatibility
- Removed `spicyweb\embeddedassets\models\EmbeddedAsset::getCacheAge()`
- Removed `spicyweb\embeddedassets\models\EmbeddedAsset::getRequestUrl()`; use the `url` property instead
- Removed `spicyweb\embeddedassets\models\EmbeddedAsset::getSafeHtml()`; use a combination of the `getIsSafe()` method and the `code` property instead
- Removed `spicyweb\embeddedassets\models\EmbeddedAsset::getThumbnailHeight()`; use the `imageHeight` property instead
- Removed `spicyweb\embeddedassets\models\EmbeddedAsset::getThumbnailUrl()`; use the `image` property instead
- Removed `spicyweb\embeddedassets\models\EmbeddedAsset::getThumbnailWidth()`; use the `imageWidth` property instead
- Removed `spicyweb\embeddedassets\models\EmbeddedAsset::isSafe()`; use `getIsSafe()` instead
- Removed `spicyweb\embeddedassets\Service::getCachedAssetPath()`
- Removed `spicyweb\embeddedassets\Variable::fromAsset()`; use `get()` instead
- Removed `spicyweb\embeddedassets\Variable::fromAssets()`; iterate your assets manually and call `get()` on each instead
- Removed `spicyweb\embeddedassets\Variable::isEmbedded()`; use `get()` instead

## 2.11.4 - 2023-03-25

### Fixed
- Fixed Craft 3.8 compatibility issues

## 2.11.3 - 2022-06-30

### Fixed
- Fixed a bug where the 'Uploaded by' field would be empty for newly created embedded assets

## 2.11.2 - 2022-06-24

### Fixed
- Fixed a type error that could occur in version 2.11.1

## 2.11.1 - 2022-06-24

### Fixed
- Fixed an error that occurred when saving an embedded asset, if the embedded asset title (and therefore the filename) contained invalid characters

## 2.11.0 - 2022-06-15

### Added
- Added `spicyweb\embeddedassets\gql\interfaces\EmbeddedAssetImage`
- Added `spicyweb\embeddedassets\gql\types\EmbeddedAssetImage`
- Added `spicyweb\embeddedassets\gql\types\generators\EmbeddedAssetImageType`

### Fixed
- Fixed a bug where accessing embedded assets' `images` and `providerIcons` properties through GraphQL outside of dev mode would cause an error

## 2.10.7 - 2022-04-21

### Fixed
- Fixed a bug where replacing an embedded asset would not cause its cached data to be replaced

## 2.10.6 - 2022-04-20

### Fixed
- Fixed an error that could occur when loading a saved embedded asset preview on Craft 3.6

## 2.10.5 - 2022-04-16

### Fixed
- Fixed a bug that could cause invalid Vimeo URLs to be loaded when the 'Disable tracking on Vimeo iframes' setting was enabled
- Fixed a bug with Instagram auto-refreshing in 2.10.4

## 2.10.4 - 2022-04-14

### Changed
- Moved auto-refreshing of Instagram embedded assets to a queue job

## 2.10.3 - 2022-03-21

### Fixed
- Fixed a bug when auto-refreshing Instagram embedded assets, where the previous cached data was not being replaced

## 2.10.2 - 2022-03-15

### Added
- Added support for embedding Vimeo URLs with the new external embed format (thanks @boboldehampsink)

### Changed
- Updated JavaScript dependencies

### Fixed
- Fixed some issues with Instagram asset auto-refreshing (thanks @arifje)

## 2.10.1 - 2022-02-07

### Fixed
- Fixed a bug where refreshing embedded assets from the console wouldn't refresh an embedded asset if the data was still cached from when the embedded asset was created

## 2.10.0 - 2022-01-06

### Added
- Added `spicyweb\embeddedassets\Variable::create()` (`craft.embeddedAssets.create()`) for creating an `EmbeddedAsset` model from an asset's contents or other user-provided data that represents a valid embedded asset
- Added a console command for refreshing embedded asset data by provider

### Changed
- The console command for refreshing all embedded asset data now accepts both `--volume` and `--provider` options

## 2.9.1 - 2021-11-30

### Fixed
- Fixed an error that occurred when executing a GraphQL query for embedded asset data using Gridsome, or after generating types using GraphQL codegen

## 2.9.0 - 2021-11-03

### Added
- Added console commands for refreshing all embedded asset data and refreshing embedded asset data by volume

## 2.8.1 - 2021-10-08

### Changed
- Updated `spicyweb\embeddedassets\events\BeforeCreateAdapterEvent` to allow custom configuration of `Dispatcher` instance (thanks @qrazi)

## 2.8.0 - 2021-08-02

### Added
- Added `spicyweb\embeddedassets\Service::EVENT_BEFORE_CREATE_ADAPTER`
- Added `spicyweb\embeddedassets\events\BeforeCreateAdapterEvent`

### Changed
- Updated JavaScript dependencies

## 2.7.0 - 2021-06-02

### Added
- Added the `iframeCode` and `iframeSrc` fields for GraphQL queries, which take a `params` argument in the same format as an embedded asset model's `getIframeCode()` and `getIframeSrc()` methods

### Changed
- Embedded Assets now requires Embed 3.4.17 or any later Embed 3 version

## 2.6.1 - 2021-05-10

### Changed
- Restricted Embedded Assets' required Embed version to 3.4.15 for now

## 2.6.0 - 2021-05-10

### Added
- Added the `Use YouTube nocookie?` plugin setting, which will force usage of the `youtube-nocookie.com` domain for YouTube iframes when enabled
- Added the `Disable tracking on Vimeo iframes?` plugin setting, which will force usage of the `dnt=1` query parameter for Vimeo iframes when enabled
- Added `spicyweb\embeddedassets\models\EmbeddedAsset::getIframeCode()` (like `getVideoCode()`, but for all cases where the embedded asset's `code` is an `<iframe>`)
- Added `spicyweb\embeddedassets\models\EmbeddedAsset::getIframeSrc()` (like `getVideoUrl()`, but for all cases where the embedded asset's `code` is an `<iframe>`)

### Changed
- Embedded Assets now requires Craft 3.6.0 or later
- Changed Embedded Assets' JavaScript dependency package management from Yarn to NPM

## 2.5.3 - 2021-04-09

### Fixed
- Fixed an issue where direct Vimeo URLs could not be embedded (thanks @boboldehampsink)
- Fixed a potential preview styling issue when the embedded asset code is a `<video>`

## 2.5.2 - 2021-04-02

### Changed
- Updated the `embed/embed` library version requirement to ^3.4.15

## 2.5.1 - 2021-02-27

### Fixed
- Fixed a bug where a validation error would occur when saving the plugin settings, if any of the Parameters list values were 0

## 2.5.0 - 2021-02-17

### Changed
- The embedded asset caching added in Embedded Assets 2.3.0 now uses the Craft data cache, rather than manual saving of files within `storage/runtime/assets/embeddedassets` (thanks @johndwells)

### Fixed
- Fixed a potential error when trying to get the `EmbeddedAsset` model of an asset that wasn't an embedded asset

## 2.4.5 - 2020-12-29

### Changed
- Updated the `embed/embed` library version requirement to ^3.4.13

## 2.4.4 - 2020-11-24

### Added
- Added a default English translation file

### Fixed
- Fixed an issue with the `getVideoCode()` and `getVideoUrl()` methods where an extra `?` could sometimes be included in the returned URL

## 2.4.3 - 2020-11-03

### Changed
- Updated `embed/embed` library minimum requirement to 3.4.9
- The exceptions thrown if an embedded asset's `getVideoCode()` method is called on an embedded asset that is not a video, or where it is not passed an array, have been given more descriptive error messages

## 2.4.2 - 2020-10-26

### Fixed
- Fixed a JavaScript error that prevented Redactor fields' 'link to an asset' option from working correctly

## 2.4.1 - 2020-10-19

### Added
- Added the `referer` plugin setting, allowing setting the domain to be sent as the referer with embedded asset requests, which allows the embedding of domain-restricted Vimeo videos (thanks @johndwells)

## 2.4.0 - 2020-10-02

### Added
- Added support for retrieving embedded asset data with GraphQL in Craft CMS Pro
- Added `spicyweb\embeddedassets\models\EmbeddedAsset::getIsSafe()`

### Deprecated
- Deprecated `spicyweb\embeddedassets\models\EmbeddedAsset::isSafe()` (this should not require any Twig template updates)

### Fixed
- Updated the node-sass version requirement to 4.13.1; resolves a security issue

## 2.3.4 - 2020-08-25

### Fixed
- Fixed an issue where the Embed and Replace button text could not be translated

## 2.3.3 - 2020-08-10

### Fixed
- Fixed a bug with asset index modals for asset fields that don't allow JSON assets, where the Embed button would appear after selecting an asset
- Updated the elliptic version requirement in yarn.lock to 6.5.3

## 2.3.2 - 2020-07-28

### Fixed
- Fixed an issue where Instagram embeds would include the login URL (with otherwise correct data) in some cases after checking for expired signatures

## 2.3.1 - 2020-07-20

### Fixed
- Fixed a bug with Embedded Assets 2.3.0, where asset index modals for asset fields with no restriction on allowed file types would not show an Embed button

## 2.3.0 - 2020-07-10

### Changed
- Embedded Assets now requires Craft 3.4.0 or later
- Embedded Assets now caches embedded asset JSON files in a Craft install's `storage/runtime/assets/embeddedassets` directory, improving the performance of the Craft Assets page when using a remote storage volume
- Updated `embed/embed` library minimum requirement to 3.4.8
- Embedded Assets' JavaScript source has been converted to use the Standard JS style

### Fixed
- Fixed an issue where Embedded Assets was putting an Embed button on a Redactor field's Add Image modal
- Fixed a performance issue with Embedded Assets' check for expired Instagram signatures
- Fixed an issue where Instagram embeds would include the login URL (with otherwise correct data) in some cases
- Replaced usage of the deprecated `Twig_Markup` class with `Twig\Markup`

## 2.2.7 - 2020-07-01

### Fixed
- Embedded Assets now internally stores all embedded asset data that has been loaded during a request, to avoid unnecessary reloads of embedded asset file contents, improving the performance of the Craft Assets page

## 2.2.6 - 2020-05-31

### Fixed
- Updated `embed/embed` library minimum requirement to ^3.4.5, to fix issue with Instagram embeds not working in some cases
- Fixed JavaScript error when entering an embed URL which returns an embedded asset with no associated media

## 2.2.5 - 2020-05-26

### Added
- Added support for embedding videos from PBS

## 2.2.4 - 2020-05-19

### Changed
- Updated `embed/embed` library minimum requirement to ^3.4.4, for compatibility with TikTok embeds
- Updated default whitelist to include TikTok

### Fixed
- Fixed error when trying to embed a TikTok asset
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

### Fixed
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
