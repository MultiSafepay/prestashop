## 4.4.0
Release date: May 24th, 2019

### Added
+ PLGPRSS-244: Add support for tokenization

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
+ PLGPRSS-312: Add support for Santander Betaalplan payment method
+ PLGPRSS-313: Add support for AfterPay payment method
+ PLGPRSS-314: Add support for Trustly payment method
***

## 4.1.0
Release date: Mar. 12th, 2018

### Added
+ PLGPRSS-222: Add Dutch translations
+ PLGPRSS-240: Support direct transactions iDEAL, Pay After Delivery, E-Invoice, Bank transfer, ING Home’Pay, KBC and PayPal
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
+ PLGPRSS-301: Resolve Pay After Delivery/E-invoice layout error which occurred with non-English languages
+ PLGPRSS-303: Order creation failed when order amount differs from paid amount

### Changed
+ PLGPRSS-245: Combine Live- Test- API key to use one API key
+ PLGPRSS-249: Remove min/max amount configuration for gift cards
+ PLGPRSS-250: Remove IP-restrictions in the Pay After Delivery, Klarna and E-Invoice configuration
+ PLGPRSS-265: Replace DAYS_ACTIVE with SECONDS_ACTIVE
+ PLGPRSS-275: Make form fields required for iDEAL, Pay After Delivery and E-Invoice
+ PLGPRSS-297: Change the message of the redirect to the order-history page
***

## 4.0.0
Release date: January 9th, 2018

### Changed
+ Initial release for PrestaShop 1.7
***