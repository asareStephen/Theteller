
# Theteller extension for Magento eCommerce v2

This extension allows you to use Theteller as payment gateway in your Magento 2.1.8 store.


## Magento Version Compatibility
- Magento (CE) 2.0.x - 2.1.x
- PHP 5.5.22 or above

## Dependencies
- None


## Installation
1. Upload the included **Thetellermagento** directory to “app/code/” of your Magento root directory.
2. On your server, install the module by running `bin/magento setup:upgrade` in your magento root directory.
3. Sign in to your Magento admin.
4. Flush your Magento cache under **System**->**Cache Management** and reindex all templates under **System**->**Index Management**.
5. Navigate to Payment Methods under **Stores**->**Configuration**->**Sales**->**Payment Methods** expand **theteller Payment** and make sure that it is enabled.
6. Select **No** under **Sandbox**. _(Unless you are testing in the extension)_
7. Enter your **Merchant ID**. _(theteller issued Merchant ID)_
8. Enter your **the base64 econde of the API user and API key** _(theteller issued Hash Key)_
8. Enter your **the returen URL** _(theteller issued Hash Key)_
9. Save your changes.


## Merchant requirements
- The **Merchant ID** and **Hash Key** acquisition process is found on [theteller Website](https://theteller.net )
