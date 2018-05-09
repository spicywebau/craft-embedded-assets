# Changelog

## 1.0.2 - 2018-05-09
### Added
- Added `html` property for conveniently handling checks for embed codes and safety

### Fixed
- Fixed issue with `isSafe` method throwing an error if the `code` property is empty
- If a URL can't be loaded, the UI will now [timeout and show an error notice][#64]
- Improved stability of the [method of reading JSON files][#63]

[#63]: https://github.com/benjamminf/craft-embedded-assets/issues/63
[#64]: https://github.com/benjamminf/craft-embedded-assets/issues/64

## 1.0.1 - 2018-05-09
### Fixed
- Implemented missing legacy properties on embedded assets

## 1.0.0 - 2018-05-08
- Initial release for Craft 3
