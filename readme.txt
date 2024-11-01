=== Extended Order Details for WooCommerce ===
Contributors: fahadmahmood
Tags: extended order details, order items, export orders, order export
Requires at least: 4.4
Tested up to: 6.2
Stable tag: 2.0.0
Requires PHP: 7.0
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
A user friendly plugin to view order details.

== Description ==

* Author: [Fahad Mahmood](https://www.androidbubbles.com/contact)

* Project URI: <http://androidbubble.com/blog/wordpress/plugins/wc-order-details>

After activation there will be a settings page "order details" under WooCommerce menu. You can list orders related to the customer account on front-end with the appropriate login and user role restrictions. It can display other related meta data on one single page. Affiliate WP is another plugin which it is compatible with. So in case you are using Affiliate WP, it will display all the orders from which you got the commission and you can see your customers.
 
 
= Tags =
woocommerce, orders, details, affiliate wp, search orders, search customer, search affiliate


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. That's it. Check the submenu under WooCommerce.


== Frequently Asked Questions ==

= What can I do with this plugin? =

You can list orders related to the customer account on front-end with the appropriate login and user role restrictions. It can display other related meta data on one single page which normally isn't available in admin side.

= Is it compatible with Affiliate WP? =

Yes, in case you are using Affiliate WP, it will display all the orders from which you got the commission and you can see your customers orders in left sidebar.

= How to create template file in theme directory? =

Create a file in your theme directory as "wcod-template.php" and paste the code given below in it:
<?php
/**
* Template Name: WC Order Details
*/
wp_head();
echo do_shortcode('[WC-ORDER-DETAILS]');
wp_footer();

= How can I know that file has been created? =

There will be an icon appeared in second line of instructions paragraph, when the file has been created.

= May I search the orders with customer name? =

Yes, you can search by customer name using "Search by customer name" box. This feature is available in Premium version.

== Screenshots ==
1. Settings page
2. Page Attributes - Template File Appeared
3. Help & Support Tab
4. Template file in action, front-end view
5. Collapsed sections are containing relevant details
6. Orders search by customer name
7. Orders filtration by customer name and order ID
8. Shortcode Attributes (yes/no)

== Changelog ==
= 2.0.0 =
* Updated version for WordPress. - 02/05/2023
= 1.9.9 =
* Updated version with extra product meta displayed. - 20/09/2022 [Thanks to Niels Brinch Feldberg]
= 1.9.8 =
* Updated version for WordPress. - 07/09/2022
= 1.9.7 =
* Improved shortcodes. - 28/10/2021 [Thanks to Raffaele Bonadies]
= 1.9.6 =
* Updated deprecated PHP functions. - 27/10/2021
= 1.9.5 =
* Updated CSS styles and a couple of JavaScript functions. - 21/10/2021
= 1.9.4 =
* Updated Bootstrap version and toggleClass related improvements. - 21/10/2021 [Thanks to Bruno Tommaso]
= 1.9.3 =
* Updated version for WordPress. - 11/09/2021
= 1.9.2 =
* Switch to back functionality linked with WooCommerce logout link(s). - 29/10/2019 [Thanks to Jim]
= 1.9.1 =
* Switch to back functionality added. - 29/10/2019 [Thanks to Jim]
= 1.9 =
* Another improved version after extensive user-experience. - 26/10/2019 [Thanks to Jim Fulford & Bryan Honaker]
= 1.8 =
* A few PHP notices are handled with if conditions. - 14/08/2019
= 1.7 =
* A few important options added on settings page. - 13/08/2019
= 1.6 =
* Searched orders listed in DESC order. - 10/08/2019
= 1.5 =
* Bootstrap scripts and styles restricted to the template file only. - 10/08/2019
= 1.4 =
* Updated with UPS - 10/08/2019
= 1.3 =
* Orders search feature added - 07/08/2019
= 1.2 =
* Orders filter feature added - 06/08/2019
= 1.1 =
* First updated after review - 05/08/2019
= 1.0 =
* First release.

== Upgrade Notice ==
= 2.0.0 =
Updated version for WordPress.
= 1.9.9 =
Updated version with extra product meta displayed.
= 1.9.8 =
Updated version for WordPress.
= 1.9.7 =
Improved shortcodes. 
= 1.9.6 =
Updated deprecated PHP functions.
= 1.9.5 =
Updated CSS styles and a couple of JavaScript functions.
= 1.9.4 =
Updated Bootstrap version and toggleClass related improvements.
= 1.9.3 =
Updated version for WordPress.
= 1.9.2 =
Switch to back functionality linked with WooCommerce logout link(s).
= 1.9.1 =
Switch to back functionality added.
= 1.9 =
Another improved version after extensive user-experience.
= 1.8 =
A few PHP notices are handled with if conditions.
= 1.7 =
A few important options added on settings page.
= 1.6 =
Searched orders listed in DESC order.
= 1.5 =
Bootstrap scripts and styles restricted to the template file only.
= 1.4 =
Updated with UPS
= 1.3 =
Orders search feature added.
= 1.2 =
Orders filter feature added.
= 1.1 =
First updated after review.
= 1.0 =
* First release.


== License ==
This WordPress plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
This WordPress plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this WordPress plugin. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
