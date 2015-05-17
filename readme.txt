=== Paga Woocommerce Payment Gateway ===
Contributors: tubiz, mypaga.com, k_uko
Tags: woocommerce, payment gateway, payment gateways, paga, interswitch, verve cards, tubiz plugins, verve, nigeria
Requires at least: 3.5
Tested up to: 4.2
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Paga Woocommerce Payment Gateway allows you to accept payment on your Woocommerce store via Visa Cards, Mastercards, Verve Cards and Paga account




== Description ==

This is a Paga payment gateway for Woocommerce.

Paga is a unique online payment processor whose vision is to offer buyers and sellers a secure and easy-to-use means of transacting business online.

Paga allows site owners to receive payment for their goods and services on their website without any setup fee.

To signup for a Paga Merchant account visit their website by clicking [here](https://mypaga.com)

Paga Woocommerce Payment Gateway allows you to accept payment on your Woocommerce store using Nigeria issued Visa Card, Mastercard, Verve Cards and Mypaga accounts.

With this Paga Woocommerce Payment Gateway plugin, you will be able to accept the following payment methods in your shop:

* __MasterCards__
* __Visa Card__
* __Verve Cards__
* __Mypaga Accounts__

= Note =

This plugin is meant to be used by merchants in Nigeria.

= Plugin Features =

*   	__Accept payment__ via Visa Cards, Mastercards,Verve Cards and Paga accounts.
* 	__Seamless integration__ into the WooCommerce checkout page.
* 	__Add Naira__ currency symbol. To select it go to go to __WooCommerce > Settings__ from the left hand menu, then click __General__ from the top tab. From __Currency__ select Naira, then click on __Save Changes__ for your changes to be effected.


= Suggestions / Feature Request =

If you have suggestions or a new feature request, feel free to get in touch with me via the contact form on my website [here](http://bosun.me/get-in-touch/)

You can also follow me on Twitter! **[@tubiz](http://twitter.com/tubiz)**


== Installation ==

= Automatic Installation =
* 	Login to your WordPress Admin area
* 	Go to "Plugins > Add New" from the left hand menu
* 	In the search box type "Paga Woocommerce Payment Gateway"
*	From the search result you will see "Paga Woocommerce Payment Gateway" click on "Install Now" to install the plugin
*	A popup window will ask you to confirm your wish to install the Plugin.

= Note: =
If this is the first time you've installed a WordPress Plugin, you may need to enter the FTP login credential information. If you've installed a Plugin before, it will still have the login information. This information is available through your web server host.

* Click "Proceed" to continue the installation. The resulting installation screen will list the installation as successful or note any problems during the install.
* If successful, click "Activate Plugin" to activate it, or "Return to Plugin Installer" for further actions.

= Manual Installation =
1. 	Download the plugin zip file
2. 	Login to your WordPress Admin. Click on "Plugins > Add New" from the left hand menu.
3.  	Click on the "Upload" option, then click "Choose File" to select the zip file from your computer. Once selected, press "OK" and press the "Install Now" button.
4.  	Activate the plugin.
5. 	Open the settings page for WooCommerce and click the "Payment Gateways," tab.
6. 	Click on the sub tab for "pay with paga".
7.	Configure your "Paga Payment Gateway" settings. See below for details.



= Configure the plugin =
To configure the plugin, go to __WooCommerce > Settings__ from the left hand menu, then click "Payment Gateways" from the top tab. You should see __"pay with paga"__ as an option at the top of the screen. Click on it to configure the payment gateway.

__*You can select the radio button next to the Paga Payment Gateway from the list of payment gateways available to make it the default gateway.*__

* __Enable/Disable__ - check the box to enable Paga Payment Gateway.
* __Title__ - allows you to determine what your customers will see this payment option as on the checkout page.
* __Description__ - controls the message that appears under the payment fields on the checkout page. Here you can list the types of cards you accept.
* __Paga Merchant Key__  - enter your Paga Merchant Merchant key here, this is gotten from your account page on [Mypaga website](https://mypaga.com).
* __Return URL__ - This URL should be copied and put in the Payment notification URL field under the Merchant Information section in the E-Pay Set-up area under your Paga Merchant account.
* __Test Mode__ - Check to enable test mode. Test mode enables you to test payments before going live. If you ready to start receving payment on your site, kindly uncheck this.
* Click on __Save Changes__ for the changes you made to be effected.





== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

1.	You need to have Woocommerce plugin installed and activated on your WordPress site.
2.	You need to open a merchant account on [Paga](https://mypaga.com)




== Changelog ==

= 1.2.0 =
*	Fix: Use wc_get_order instead or declaring a new WC_Order class
*	Fix: Removed all global $woocommerce variable
* 	Add support for Woocommerce 2.3

= 1.1.0 =
* 	Add support for Woocommerce 2.1
*	Plugin now uses ePay v2

= 1.0.0 =
*   First release





== Upgrade Notice ==

= 1.2.0 =
* Make plugin compatible with latest WordPress version






== Screenshots ==

1. Paga Wooocommerce Payment Gateway Setting Page

2. Paga Wooocommerce Payment Gateway method on the checkout page

3. Paga available payment method page

4. Successful Payment Transaction Message

5. Failed Payment Transaction Declined Message





== Other Notes ==

