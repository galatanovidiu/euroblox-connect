<?php
/**
 * Plugin Name: EuroBlox Connect
 * Plugin URI: https://euroblox.com/
 * Description: WooCommerce Plugin for EuroBlox Marketplace
 * Version: 0.1.3
 * Author: Galatan Ovidiu Iulian
 * Author URI: https://euroblox.com
 * Copyright: © 2020 EuroBlox.
 * Requires at least: 4.7
 * Tested up to: 5.1
 * Depends: Woocommerce
 * Text Domain: euroblox
 **
 *
 * @package WooCommerce EuroBlox Marketplace
 * @category Core
 * @author Galatan Ovidiu Iulian
 */

if ( ! function_exists( 'is_woocommerce_activated' ) ) {
    include_once 'lib/EBX_WC_Integration_Init.php';
    include_once 'lib/EBX_WC_products_admin.php';
    include_once 'lib/EBX_WC_category_connect.php';
    include_once 'lib/EBX_WC_products_management.php';

}



