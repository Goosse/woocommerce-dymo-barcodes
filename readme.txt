=== Woocommerce Product Barcodes ===
Contributors: Jack Gregory
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=info@platformplatform.com&item_name=Donation+for+WooCommerce+Product+Barcodes
Tags: woocommerce, dymo, barcodes
Requires at least: 3.9.1
Tested up to: 4.6
Stable tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Allows printing of WooCommerce product labels using a DYMO LabelWriter printer.

== Features ==

- Print barcode labels from the WordPress admin.
- Select from small, medium and large labels in the settings.
- Configure what data prints on each label in the settings.
- Export labels in .csv format to import into Dymo software for bulk printing.

== Installation  ==

1. Install most recent Dymo LabelWriter printer software and driver v8.5.3.
2. Dymo web service should be active and running.
3. Connect Dymo LabelWriter 450 barcode printer. 
4. Download master branch unzip and upload via your favourite FTP application to your plugins folder. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
5. Active plugin in your WordPress plugin dashboard.
6. Go to WooCommerce > Settings > Integration > Product Barcodes and choose label size and choose barcode label options.
7. After first install future updates can be done in your WordPress plugin dashboard.

== Upgrade Notice ==

After first install future automatic updates can be done in your WordPress plugin dashboard. Before updating its recommended to perform a site backup.

== Frequently Asked Questions ==

= How do I install the DYMO Label Web Service? =

First, download the appropriate installer for your OS. You can find them at the following URL: http://www.dymo.com/en-US/online-support/

= How can I tell if I have DYMO Label Web Service installed? =

The DYMO Label Web Service is installed as long as you have installed DYMO Label Software 8.5.3 or newer using the express "Express" mode. If you choose to install DYMO Label Software in “Custom” mode, be sure to select the DYMO Label Web Service component.

= How can I tell if the DYMO Label Web Service is running? =

You should see the DYMO Label service application icon within the system tray (Windows) and toolbar (Mac).

= I do not see the DYMO Label service application icon. How can I start it?  =

- Windows: You can start the web service again by navigating to the DLS working folder and running the executable named DYMO.DLS.Printing.Host.exe.
- Mac: Open a Finder window, navigate to the /Library/Frameworks/DYMO/SDK/ folder, and click on the DYMO.DLS.Printing.Host.app icon. Open a terminal prompt and enter the following command: launchctl start com.dymo.dls.webservice

= The Web Service has failed to install = 

If this happens, try and turn off your Anti-virus software before re-installing.

== Changelog ==

= 1.1.0 =
* Updated SDK to version 2.0 to support new DYMO label web service and Chrome.
* Added small barcode label option.

= 1.0.4.1 =
* Add better event handling of barcode label print button

= 1.0.4 =
* Add better error messages if the dymo sdk plugin fails or if the browser is unsupported

= 1.0.3 =
* Fix issue with how products and variations were displayed
* Add option to bulk print labels from products table
* Fix an issue where prices weren't printing

= 1.0.2 =
* Refactor product barcode wp table.
* Add filter for csv headers.

= 1.0.1 =
* Add filter for wp list table per page.

= 1.0 =
* This is the first public release.
