# Installation

The plugin can be installed either through the [Plugin Store](https://plugins.craftcms.com/) or through [Composer](https://packagist.org/).

### Plugin Store
Open the control panel for your website. Navigate to **Settings &rarr; Plugins** and search for **Embedded Assets**. Click **Install**.

### Composer
Run the following command in the root directory of your Craft project:
```
composer require spicyweb/craft-embedded-assets
```

## Requirements

### Craft version
Embedded Assets requires Craft CMS 3.1.0 or later.

### PHP version
Embedded Assets requires PHP 7.0 or greater.

## Allow JSON filetype

Prior to Craft 3.0.23, JSON files were disabled by default when uploading files to the asset manager. 

To fix this, either upgrade Craft to 3.0.23 or later, or open your `config/general.php` file and add the following setting:
```php
'extraAllowedFileExtensions' => 'json'
```
