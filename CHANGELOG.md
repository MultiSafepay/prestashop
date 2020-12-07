## 4.7.1
Release date: December 7th, 2020

### Fixed
+ PLGPRSS17-16: Refactor order creation flow to prevent double orders

***

## 4.7.0
Release date: October 19th, 2020

### Added
+ PLGPRSS17-124: Add Good4fun Giftcard
+ DAVAMS-233: Add in3 payment method
+ PLGPRSS17-15: Add Spanish translations
+ PLGPRSS17-108: Add VVV Cadeaukaart as a giftcard
+ DAVAMS-213: Add track & trace to shipment request

### Fixed
+ PLGPRSS17-125: Gateways don't show for combination of conflicting shipping methods
+ PLGPRSS17-110: Align payment method names and logo with radio buttons
+ PLGPRSS17-46: Prevent bypassing Terms & Conditions during checkout by using enter
+ PLGPRSS17-74: Encryption function was called statically instead of non-static
+ PLGPRSS17-103: Fix warning not activated payment methods on php version < 7.0
+ PLGPRSS17-41: Fix rounding issue in the shopping cart
+ PLGPRSS17-119: Prevent order is created twice
+ PLGPRSS17-112: Validate IP addresses to prevent error 1000
+ PLGPRSS17-106: Fix gateway code for Webshop Giftcard
+ PLGPRSS17-107: Fix gateway code for Fashion Giftcard
+ PLGPRSS17-115: Fix Apple Pay issue with One Page Checkout PS
+ PLGPRSS17-104: Fix VAT issue when no tax is defined
+ PLGPRSS17-111: Fix JS error on Apple Pay with non-existing form element

### Changed
+ DAVAMS-315: Rebrand Klarna
+ DAVAMS-297: Rebrand Direct Bank Transfer to Request to Pay
+ PLGPRSS17-12: Bank transfer email send with MultiSafepay email systems
+ DAVAMS-285: Update name and logo for Santander
+ PLGPRSS17-120: Update Italian translations
+ PLGPRSS17-8: Remove default tax from shopping cart
+ PLGPRSS17-14: Change AfterPay from redirect to direct
+ PLGPRSS17-9: Improve address parser

### Removed
+ PLGPRSS17-93: Remove automatic API key validation
+ PLGPRSS17-88: Remove unsupported giftcards from plugin
+ PLGPRSS17-118: Remove VVV Bon

***

## 4.6.1
Release date: April 17th, 2020

### Fixed
+ PLGPRSS17-105: Gift cards not visible in checkout

***

## 4.6.0
Release date: March 27th, 2020

### Added
+ PLGPRSS17-97: Add Apple Pay
+ PLGPRSS17-96: Add Direct Bank Transfer

### Fixed
+ PLGPRSS17-94: Fix ClassNotFoundException when cURL returns an error

***

## 4.5.1
Release date: February 26th, 2020

### Fixed
+ PLGPRSS17-83: Fix transaction status was not updated when set to shipped

## 4.5.0
Release date: December 12th, 2019

### Added
+ PLGPRSS17-17: Add refund support within PrestaShop
+ PLGPRSS17-45: Add Bank transfer details to PrestaShop invoices
+ PLGPRSS17-49: Add PSR-4 namespaces

### Changed
+ PLGPRSS17-21: Set order to status shipped for all payment methods

### Fixed
+ PLGPRSS17-64: Fix groups restrictions for payment methods and gift cards
+ PLGPRSS17-44: Fix issue where a free gift was not recognized as free
+ PLGPRSS17-30: Fix notifications returning HTTP 302

## 4.4.0
Release date: July 3rd, 2019

### Added
+ PLGPRSS-244: Add support for tokenization

### Changed
+ PLGPRSS17-35: Display proper gateway name as used payment method, instead of gateway code

## 4.3.1
Release date: May 15th, 2019

### Fixed
+ PLGPRSS-372: Prevent creation of duplicate orders

## 4.3.0
Release date: April 25th, 2019

### Added
+ PLGPRSS-274: Add iDEAL QR payment method
+ PLGPRSS-311: Add transaction ID to payment details when viewing order in backend

### Changed
+ PLGPRSS-225: Change way path to plugin js/css files is determined to prevent sporadic loading issue
+ PLGPRSS-351: Change merchant_item_id to support product variants
+ PLGPRSS-261: Improve parsing of address into street and apartment
+ PLGPRSS-335: Correct spelling ING Home'Pay

### Fixed
+ PLGPRSS-353: Fix no payment methods visible after updating carriers
+ PLGPRSS-356: Fix refund issue for products with a variation
+ PLGPRSS-348: Fix when installing through commandline, config is not initialized
+ PLGPRSS-347: Fix warning during sorting of gateways and giftcards
+ PLGPRSS-345: Prevent order status be changed on not MultiSafepay orders

### Removed
+ PLGPRSS-283: Remove Klarna invoice link
***

## 4.2.0
Release date: May 25th, 2018

### Added
+ PLGPRSS-312: Add support for Betaalplan payment method
+ PLGPRSS-313: Add support for AfterPay payment method
+ PLGPRSS-314: Add support for Trustly payment method
***

## 4.1.0
Release date: Mar. 12th, 2018

### Added
+ PLGPRSS-222: Add Dutch translations
+ PLGPRSS-240: Support direct transactions iDEAL, Pay After Delivery, E-Invoicing, Bank transfer, ING Home’Pay, KBC and PayPal
+ PLGPRSS-246: Add check to see if test/live option corresponds with the used API key
+ PLGPRSS-253: Add warning when enabling gateway which is not active in MultiSafepay Control
+ PLGPRSS-277: Update Klarna payment method logo
+ PLGPRSS-278: Remove Multisafepay.js from the front-end
+ PLGPRSS-280: Add Italian translations
+ PLGPRSS-286: Remove debugging statement
+ PLGPRSS-310: Add message to order in case amount paid is not equal to order amount

### Fixed
+ PLGPRSS-248: When in debug mode a long list of warnings is displayed when entering the configuration.
+ PLGPRSS-254: Gateway not visible when min_amount is set and max_amount is not.
+ PLGPRSS-255: Changes in the configuration are not shown
+ PLGPRSS-257: Parsing address failed when the house-number is on the second address row.
+ PLGPRSS-258: Order-confirmation page not always shown, due order not existing yet
+ PLGPRSS-268: Change gateway-code ING to INGHOME
+ PLGPRSS-269: Updates within payment method configuration are not updated on save
+ PLGPRSS-276: Resolve an issue where multiple transactions were created after multiple clicks on the order-confirmation button
+ PLGPRSS-281: Resolve a PHP notice "gateway_info undefined" which occurred when using direct transactions
+ PLGPRSS-285: No gateways available for virtual products
+ PLGPRSS-287: Fix spelling mistake in Gezondheidsbon
+ PLGPRSS-288: Update uninstall function to unregister missing hooks
+ PLGPRSS-293: Fix wrong spelling of some gateway names
+ PLGPRSS-294: Resolve an issue where Pay After Delivery uncleared orders remained set to ‘Payment accepted’ despite the transaction having been declined.
+ PLGPRSS-301: Resolve Pay After Delivery/E-Invoicing layout error which occurred with non-English languages
+ PLGPRSS-303: Order creation failed when order amount differs from paid amount

### Changed
+ PLGPRSS-245: Combine Live- Test- API key to use one API key
+ PLGPRSS-249: Remove min/max amount configuration for gift cards
+ PLGPRSS-250: Remove IP-restrictions in the Pay After Delivery, Klarna and E-Invoicing configuration
+ PLGPRSS-265: Replace DAYS_ACTIVE with SECONDS_ACTIVE
+ PLGPRSS-275: Make form fields required for iDEAL, Pay After Delivery and E-Invoicing
+ PLGPRSS-297: Change the message of the redirect to the order-history page
***

## 4.0.0
Release date: January 9th, 2018

### Changed
+ Initial release for PrestaShop 1.7
***
