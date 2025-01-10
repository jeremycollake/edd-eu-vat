=== Easy Digital Downloads - EU VAT ===
Contributors: barn2media
Tags: easy digital downloads, edd, vat, checkout, tax
Requires at least: 6.0
Tested up to: 6.7.1
Requires PHP: 7.4
Stable tag: 1.5.27
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

= 1.5.27 =
Release date 02 December 2024

 * Fix: Translation compatibility issues with WordPress 6.7
 * Dev: Updated internal libraries.
 * Dev: Tested up to WP 6.7.1 and EDD 3.3.5

= 1.5.26 =
Release date 16 September 2024

 * Fix: Removed the "Requires Plugins" section from the plugin header.

= 1.5.25 =
Release date 09 September 2024

 * Tweak: VAT rate for Finland has been increased to 25.5%
 * Tweak: No longer set session data when not reverse charged or no VAT number is present.
 * Tweak: Added a new filter for the `insert_subscription_payment` method.
 * Dev: Minor internal changes.
 * Dev: Tested up to WP 6.6.1 and EDD 3.3.3

= 1.5.24 =
Release date 02 April 2024

 * Fix: fatal error when activating the plugin while EDD is not active.
 * Fix: VAT "Billed To" section not visible due to payment key not longer available via global variable.
 * Dev: Updated internal libraries.

= 1.5.23 =
Release date 11 January 2024

 * Tweak: Updated VAT rate for Estonia.
 * Tweak: Hide "Additional Information" header when you don't have notes.
 * Tweak: Display appropriate list of countries under the "Country of VAT registration" input inside the setup wizard.
 * Tweak: Minor changes to the settings panel and steps of the setup wizard.
 * Fix: Display missing countries under the "Country" input inside the setup wizard.
 * Dev: Minor internal changes.
 * Dev: Updated internal libraries.
 * Dev: Tested up to WP 6.4.2

= 1.5.22 =
Release date 31 October 2023

 * Tweak: clear the tax rate cache prior to displaying the cart when the checkout block is used.
 * Tweak: VAT field description hidden when using checkout blocks for consistency.
 * Tweak: Added payment object and payment ID as extra arguments to the "edd_vat_export_vat_ec_sales_vat_number" filter.
 * Fix: spacing between the state/province/zip fields and the VAT field.
 * Dev: Updated internal libraries.
 * Dev: Tested up to WP 6.3.2

<!--more-->

= 1.5.21 =
Release date 22 August 2023

 * Tweak: Display both EU and UK VAT numbers in invoices template.

= 1.5.20 =
Release date 09 August 2023

 * Dev: Updated internal libraries.
 * Dev: Tested up to WP 6.3.

= 1.5.19 =
Release date 01 August 2023

  * Fix: Checkout displaying "Invalid Requester member state" when the "Country of VAT registration" is set to "United Kingdom" due to change in VIES api.

= 1.5.18 =
Release date 25 July 2023

 * Tweak: Display all countries into the "Country" field.
 * Tweak: Added filters for the selectors used when updating the checkout template.

= 1.5.17 =
Release date 11 July 2023

 * Tweak: Renamed the current "Country" setting to “Country of VAT registration”
 * Tweak: Added a new “Country” field used for invoices.
 * Tweak: Added partial German translation.

= 1.5.16 =
Release date 13 June 2023

 * Fix: Increased number of returned custom tax rates.
 * Tweak: Updated internal libraries.
 * Tweak: Added new `edd_vat_recurring_insert_subscription_payment_id` filter.

= 1.5.15 =
Release date 03 May 2023

 * Fix: Incorrect tax rate applied at checkout when custom and disabled tax rates are being used in EDD 3.0.0+

= 1.5.14 =
Release date 19 April 2023

  * Tweak: set new temporary VAT rate for Luxembourg.

= 1.5.13 =
Release date 13 March 2023

 * Tweak: disabled clearing of VAT details on login.
 * Tweak: added 2 new filters that can be used to fire custom validation requests for VAT numbers.
 * Tweak: updated internal libraries.

= 1.5.12 =
Release date 20 February 2023

 * Tweak: added internal debugging utilities.

= 1.5.11 =
Release date 08 February 2023

 * Tweak: updated list of countries available for the "Country" setting.
 * Fix: content of the "Additional Text" setting from the EDD Invoices plugin not displaying.
 * Fix: unable to reverse charge UK VAT for valid UK/GB VAT Numbers.

= 1.5.10 =
Release date 03 January 2023

 * Tweak: updated EDD Invoices integration to conditionally replace templates based on the country of origin of an order.
 * Tweak: backwards compatible filters for "VAT number" inside Payments exports.
 * Fix: PHP warning when name or address are not defined in the VIES API response.

= 1.5.9 =
Release date 10 October 2022

 * Tweak: updated design of export tools to match EDD 3.0+
 * Tweak: update "data-total" attribute on tax recalculation on checkout page.
 * Tweak: toggle "disabled" attribute on billing country dropdown and purchase button while recalculating taxes.
 * Tweak: added friendly message for the MS_MAX_CONCURRENT_REQ VIES API error.
 * Fix: VAT number not included in the payments export.
 * Fix: clear VAT session on login.
 * Dev: removed plugin promos section in the settings panel.
 * Dev: updated internal libraries.

= 1.5.8 =
Release date 26 July 2022

 * Fix: infinite loop if no orders or results to export.
 * Fix: incomplete exports of EU VAT Report & Export EC Sales List Report in EDD 3.0+
 * Fix: wrong VAT percentage rate displayed in the invoice generated by EDD Invoices in EDD 3.0+
 * Tweak: adjusted positioning of the "Settings" link on the plugins screen.
 * Tweak: plugin promo positioning in EDD 3.0+
 * Dev: updated Barn2 libraries.
 * Dev: tested to EDD 3.0.1

= 1.5.7 =
Release date 22 June 2022

 * Fix: fixed an issue where UK VAT being reverse charged when it shouldn't be.
 * Dev: updated Barn2 libraries.

= 1.5.6 =
Release date 08 June 2022

 * Tweak: added tooltip to the country selection field in the plugin's settings panel.
 * Tweak: updated barn2 libraries.
 * Tweak: updated language files.
 * Dev: tested up to WordPress 6.0.

= 1.5.5 =
Release date 15 April 2022

 * Tweak: added "EU MOSS Number" (EU code) as selectable country under the EU Vat settings panel.
 * Tweak: added "Northern Ireland" (XI code) as selectable country under the EU Vat settings panel.
 * Tweak: use the EDD Store country name in invoices and receipts when the "EU Moss Number" country is selected.
 * Fix: make sure that the trader address property exists when checking consultation number.
 * Dev: added edd_vat_invoice_address_country_code filter.

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
