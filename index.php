<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/*
	Plugin Name: Extended Order Details for WooCommerce
	Plugin URI: https://profiles.wordpress.org/fahadmahmood/wc-order-details
	Description: A user friendly plugin to view order details.
	Version: 2.0.0
	Author: Fahad Mahmood
	Author URI: http://androidbubble.com/blog/
	Text Domain: wc-order-details
	Domain Path: /languages/
	License: GPL2
	
	
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
*/


	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}else{
		 clearstatcache();
	}
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$wcod_all_plugins = get_plugins();
	$wcod_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
	
	if ( array_key_exists('woocommerce/woocommerce.php', $wcod_all_plugins) && in_array('woocommerce/woocommerce.php', $wcod_active_plugins) ) {
		
		
		
		
		global $wcod_data, $wcod_pro, $wcod_activated, $affiliate_wp, $wcod_premium_link;
		
		$wcod_premium_link = 'https://shop.androidbubbles.com/product/wc-order-details';//https://shop.androidbubble.com/products/wordpress-plugin?variant=36439507861659';//
		
		
		$affiliate_wp = in_array( 'affiliate-wp/affiliate-wp.php',  $wcod_active_plugins);
		
		$wcod_activated = true;
		

		$wcod_data = get_plugin_data(__FILE__);
		
		
		define( 'WCOD_PLUGIN_DIR', dirname( __FILE__ ) );
		
		$wcod_pro_file = WCOD_PLUGIN_DIR . '/pro/wcod-pro.php';
		$wcod_pro =  file_exists($wcod_pro_file);
		require_once WCOD_PLUGIN_DIR . '/inc/functions.php';
		
		if($wcod_pro)
		include_once($wcod_pro_file);		
		
		if(is_admin()){
			add_action( 'admin_menu', 'wcod_admin_menu' );	
			
			if(function_exists('wcod_plugin_links')){
				$plugin = plugin_basename(__FILE__); 
				add_filter("plugin_action_links_$plugin", 'wcod_plugin_links' );	
			}
			
		}
		
		//wcod_pre($wcod_active_plugins);
		
	}