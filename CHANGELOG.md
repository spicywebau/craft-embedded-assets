## Changelog

### v0.4.0
- `Improved` Big performance improvement when loading embedded asset thumbnails in the control panel
- `Improved` Local embedded assets are now read by their path, improving server's ability to read embedded assets (thanks @awcross)
- `Improved` Relative URL's now work with thumbnails and URL's in embedded asset data
- `Fixed` Fixed issue on some servers when saving embedded asset files (thanks @leevigraham)
- `Fixed` Fixed issue where some embedded assets would create multiple files (thanks @timkelty)

#### v0.3.4
- `Improved` Getting thumbnails is now more reliable (cheers @mmikkel)
- `Improved` Added logging at all critical points to aid in diagnosing issues
- `Fixed` Fixed issue with PHP 5.4 and below (this plugin supports 5.5+ but if you're bold enough you could use it on 5.4)

#### v0.3.3
- `Fixed` Fixed issue where certain filenames weren't being cleansed properly, resulting in the file not being recognised by the plugin

#### v0.3.2
- `Improved` Improved reading performance on embedded asset files
- `Improved` Implemented control panel caching on embedded asset thumbnails
- `Improved` Now checking for compatible PHP version (5.5+)

#### v0.3.1
- `Added` Embedded asset models now include the original provided URL under the "requestUrl" attribute
- `Improved` Embedded asset file names now include the title of the embedded media
- `Improved` Implemented a more secure way of reading embedded asset files

#### v0.3.0
- `Added` Added plugin icon
- `Added` Added author asset table attribute
- `Improved` Embed URL's in asset table now have their protocol and www's stripped

#### v0.2.1
- `Fixed` Fixed "Opengraph not found" error on front-end templates
- `Fixed` Fixed fatal issue with non-ASCII characters in entry slugs
- `Fixed` Implemented better error capturing and reporting to fix front-end Javascript issues

#### v0.2.0
- `Added` Added fallback to Open Graph metadata if no value could be found for certain properties.
- `Improved` Improved how the modal window responds to inputting and changing URL's.
- `Improved` Placeholder title and descriptions now show up if they aren't found.
- `Fixed` Fixed type labelling in the index, so images will be correctly labelled as "Embedded Image".

#### v0.0.1
- Initial release
