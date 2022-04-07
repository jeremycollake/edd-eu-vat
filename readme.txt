=== Easy Digital Downloads - EU VAT ===
Contributors: andykeith, barn2media
Tags: easy digital downloads, edd, vat, checkout, tax
Requires at least: 5.0
Tested up to: 5.9
Requires PHP: 7.2
Stable tag: 1.5.4
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl.html

This plugin extends the Easy Digital Downloads plugin with EU VAT support.

== Description ==

This plugin extends the Easy Digital Downloads plugin with EU VAT support.

== Installation ==

1. Download the plugin from the order confirmation email or your Account area at https://barn2.com/account.
1. In the WordPress dashboard, Go to Plugins -> Add New, select Upload Plugin and find the zip file you downloaded.
1. Once installed, activate the plugin from the Plugins menu.
1. Enter your license key under Downloads -> Settings -> Extensions -> EU VAT.

== Changelog ==

= 1.5.4 =
Release date 24 March 2022

 * Fix: WordPress crashes when EDD is disabled without disabling EDD EU VAT first

= 1.5.3 =
Release date 22 March 2022

 * New: improved handling of ajax validation errors at checkout.
 * New: improved handling of expired nonces at checkout.
 * New: store VIES consultation number if available.
 * Tweak: swapped the hooks used to copy data for recurring payments.
 * Dev: updated setup wizard library
 * Dev: updated barn2 library
 * Dev: tested up to WordPress 5.9.2 and Easy Digital Downloads 2.11.6.

<!--more-->

= 1.5.2 =
Release date 12 January 2022

 * New: Compatibility with the EDD Invoices plugin version 1.3.2
 * Tweak: EDD Invoices custom templates only load when the EDD Invoices plugin version is lower than 1.3.2.
 * Fix: Customer VAT details not displaying within the EDD Invoices plugin.
 * Tested up to WordPress 5.8.3 and Easy Digital Downloads 2.11.4.1.

= 1.5.1 =
Release date 13 December 2021

 * Fix: Compatibility issue with the EDD Invoices plugin version 1.3.1
 * Fix: Frontend javascript ignoring SCRIPT_DEBUG constant.

= 1.5.0 =
Release date 07 December 2021

 * New: Added support for the Easy Digital Downloads - Invoices plugin.
 * New: Added ability to display website address and email address inside the invoice.
 * Tweak: Switched VIES api url from http to https.
 * Tweak: Updated language files.
 * Tested up to WordPress 5.8.2 and Easy Digital Downloads 2.11.3.1.

= 1.4.2 =
Release date 13 Oct 2021

 * Tweak: Clear EDD Recurring license cancel and renew checkout notice if a VAT number is successfully applied.
 * Dev: Add a filter to allow showing the hidden incorrectly priced EDD Recurring notices.
 * Tested up to WordPress 5.8.1 and Easy Digital Downloads 2.11.2.


= 1.4.1 =
Release date 29 July 2021

 * Tweak: Clear EDD Recurring license upgrade checkout notice if a VAT number is successfully applied.
 * Fix: VAT details were not displayed on the Purchase Receipt or EDD PDF Invoice for certain renewal payments.
 * Fix: When using the 'Blue Stripe' template with EDD PDF Invoices, special characters would not display correctly.
 * Tested up to WordPress 5.8 and Easy Digital Downloads 2.10.6

= 1.4 =
Release date 5 May 2021

 * New: Added UK VAT number validation.
 * New: Added Company UK VAT number field in settings, which is displayed in purchase receipts and emails.
 * New: EDD 3.0 compatibility.
 * Tweak: Added an admin warning if the PHP Soap extension is not installed on the server.

= 1.3.5 =
Release date 8 April 2021

 * Fix: Fixed an issue introduced in EDD 2.10.1 which made the VAT number a required field on checkout when a discount code was applied.

= 1.3.4 =
Release date 16 February 2021

 * Tweak: Switched the purchase receipt details to use a CSS flexbox layout for the columns.
 * Dev: Added new filters to allow VAT number formatting and for handling payment rounding in the EC Sales List export.

= 1.3.3 =
Release date 20 January 2021

 * UK VAT: Remove VAT number field from checkout for United Kingdom following Brexit.
 * Tweak: Add expiry for Ireland temporary VAT rate cut.
 * Fix: Cart totals on checkout when prices are inclusive of tax.
 * Dev: Filter added to allow users to optionally display VAT number field for United Kingdom on checkout.
 * Dev: Add Composer project type and remove deprecated file include.

= 1.3.2 =
Release date 7 September 2020

 * New: Applied the temporary VAT cut for Ireland from 23% to 21%.
 * Fix: Minor error which prevented the VAT field description filter working.

= 1.3.1 =
Release date 30 June 2020

 * New: Added temporary VAT rate cut for Germany. The VAT rate applied on checkout will be reduced to 16% automatically for customers from 1 July 2020 to 31 December 2020 inclusive.
 * Tested with latest versions of WordPress and EDD.

= 1.3 =
Release date 14 May 2020

 * New: Added full VAT company information to purchase receipt and PDF invoice (if using EDD PDF Invoices).
 * Fix: Added VAT details to subscription renewal payments for EDD Recurring.
 * Fix: Prevent double submission of VAT number validation on checkout.
 * Fix: Hide VAT field when checkout first loaded if billing country not in EU.
 * Fix: The address state on the purchase receipt was displaying the code rather than the full name.
 * Fix: Improve compatibility with EDD Stripe.
 * Tweak: Always show the VAT tax rate on purchase receipt, regardless of plugin setting to show/hide invoice details.
 * Dev: Added new plugin license system and refactored code.
 * Dev: Added more filters for easier developer customisation.
 * Dev: Added Composer support.

= 1.2 =
Release date 15 January 2020

 * Added new EC Sales List report for VAT-registered businesses in the EU.
 * Added hooks to VAT Payments report to allow adjustment of total and tax amounts (e.g. for currency conversion).
 * Prevent VAT field being hidden by EDD Stripe when using the saved billing address feature.
 * Move VAT check result inside main VAT field on checkout.

= 1.1 =
Release date 17 December 2019

* Hide VAT field on checkout if billing country is not in the EU.

= 1.0 =
Release date 28 November 2019

* Initial release.
