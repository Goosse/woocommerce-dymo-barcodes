## Woocommerce Product Barcodes

Print WooCommerce product barcode labels using a DYMO LabelWriter printer.

### Usage with Chrome

Update: This plugin will no longer work in Chrome Version 45. Since this version NPAPI support has been permanently removed. A new DYMO 2.0 SDK is in beta development which will remove the dependency of browser specific plugins like NPAPI and Active-X.

For now this plugin works in Safari.

With version 42 of Chrome, Google now disables NPAPI which is required to run the DYMO plugin. However, you can manually enable it by typing the following into the Chrome address bar and adjusting the setting:
chrome://flags/#enable-npapi

### Features

- Print product barcode labels from the WordPress admin.
- Select from medium and large labels in the settings.
- Configure what data prints on each label in the settings.
- Export labels in .csv format to import into Dymo software for bulk printing.

### Usage

1. Install Dymo LabelWriter printer software.
2. Connect Dymo LabelWriter 450 barcode printer.
3. Allow Plugins in your Browser.
4. Go to WooCommerce > Settings > Integration > Barcodes and choose Dymo printer from list, choose label size and design a simple barcode label.

### Planned

Update SDK to version 2.0 to support new DYMO label web service and Chrome.

### Changelog

#### 1.0.4
* Add better error messages if the dymo sdk plugin fails or if the browser is unsupported

#### 1.0.3
* Fix issue with how products and variations were displayed
* Add option to bulk print labels from products table
* Fix an issue where prices weren't printing

#### 1.0.2
* Refactor product barcode wp table.
* Add filter for csv headers.

#### 1.0.1
* Add filter for wp list table per page.

#### 1.0
* This is the first public release.
