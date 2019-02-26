## 4.2.0
Release date: May 25th, 2018

### Added
+ PLGPRSS-312: Added support for Santander Betaalplan payment method
+ PLGPRSS-313: Added support for AfterPay payment method
+ PLGPRSS-314: Added support for Trustly payment method
***

## 4.1.0
Release date: Mar. 12th, 2018

### Added
+ PLGPRSS-222: Added Dutch translations
+ PLGPRSS-240: Added direct transaction support for iDEAL, Pay After Delivery, E-Invoice, Bank transfer, ING Home’Pay, KBC and PayPal
+ PLGPRSS-246: Added a check to see whether the used API key corresponds with the Live or Test environment
+ PLGPRSS-253: Added a warning when enabling a gateway not available in the MultiSafepay Control
+ PLGPRSS-277: Update Klarna payment method logo
+ PLGPRSS-278: Removed Multisafepay.js from the front-end
+ PLGPRSS-280: Added Italian translations
+ PLGPRSS-286: Removed debugging statement
+ PLGPRSS-310: Add message to order in case amount paid is not equal to order amount

### Fixed
+ PLGPRSS-248: When in debug mode a long list of warnings is displayed when entering the configuration.
+ PLGPRSS-254: Gateway not visible when min_amount is set and max_amount is not.
+ PLGPRSS-255: Changes in the configuration are not shown
+ PLGPRSS-257: Parsing address failed when the house-number is on the second address row.
+ PLGPRSS-258: Order-confirmation page not always shown, due order not existing yet
+ PLGPRSS-268: Changed gateway-code ING to INGHOME
+ PLGPRSS-269: Updates within payment method configuration are not updated on save
+ PLGPRSS-276: Resolved an issue where multiple transactions were created after multiple clicks on the order-confirmation button
+ PLGPRSS-281: Resolved a PHP notice "gateway_info undefined" which occurred when using direct transactions
+ PLGPRSS-285: No gateways available for virtual products
+ PLGPRSS-287: Fixed spelling mistake in "Gezondheidsbon"
+ PLGPRSS-288: Updated uninstall function to unregister missing hooks
+ PLGPRSS-293: Fixed wrong spelling of some gateway names
+ PLGPRSS-294: Resolved an issue where Pay After Delivery uncleared orders remained set to ‘Payment accepted’ despite the transaction having been declined.
+ PLGPRSS-301: Resolved Pay After Delivery/E-invoice layout error which occurred with non-English languages
+ PLGPRSS-303: Resolved an issue where order creation failed when the order amount differed from paid amount

### Changed
+ PLGPRSS-245: Combined Live- Test- API key to use one API key
+ PLGPRSS-249: Removed min/max amount configuration for gift cards
+ PLGPRSS-250: Removed IP-restrictions in the Pay After Delivery, Klarna and E-Invoice configuration
+ PLGPRSS-265: Replaced DAYS_ACTIVE with SECONDS_ACTIVE
+ PLGPRSS-275: Made form fields required for iDEAL, Pay After Delivery and E-Invoice
+ PLGPRSS-297: Changed the redirect message to the order-history page
***

## 4.0.0
Release date: January 9th, 2018

### Changed
+ Initial release for PrestaShop 1.7
***