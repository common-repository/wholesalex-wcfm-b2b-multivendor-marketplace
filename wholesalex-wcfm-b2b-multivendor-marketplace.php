<?php

/**
 * WholesaleX WCFM B2B Multivendor Marketplace
 *
 *
 * @link    https://www.wpxpo.com/
 * @since   1.0.0
 * @package           Wholesalex_WCFM
 *
 * Plugin Name:       WholesaleX WCFM B2B Multivendor Marketplace
 * Plugin URI:        https://wordpress.org/plugins/wholesalex-wcfm-b2b-multivendor-marketplace
 * Description:       This is a wholesalex addon plugin, that Turn WCFM marketplace into a wholesale multi vendor marketplace by letting vendors add wholesale prices, control product visibility, and use the conversation feature.
 * Version:           1.0.3
 * Author:            Wholesale Team
 * Author URI:        https://getwholesalex.com/
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wholesalex-wcfm-b2b-multivendor-marketplace
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defince Plugin Version
 */
define( 'WHOLESALEX_WCFM_VERSION', '1.0.3' ); // This should change each new release
define( 'WHOLESALEX_WCFM_URL', plugin_dir_url(__FILE__) ); 

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wholesalex-wcfm-b2b-multivendor-marketplace-activator.php
 *
 * @since 1.0.0
 */
function wholesalex_wcfm_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wholesalex-wcfm-b2b-multivendor-marketplace-activator.php';
	Wholesalex_WCFM_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wholesalex-wcfm-b2b-multivendor-marketplace-deactivator.php
 *
 * @since 1.0.0
 */
function wholesalex_wcfm_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wholesalex-wcfm-b2b-multivendor-marketplace-deactivator.php';
	Wholesalex_WCFM_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'wholesalex_wcfm_activate' );
register_deactivation_hook( __FILE__, 'wholesalex_wcfm_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wholesalex-wcfm-b2b-multivendor-marketplace.php';

function wholesalex_wcfm_init() {
	$required_plugins = array(
        'WooCommerce' => array('path'=> 'woocommerce/woocommerce.php', 'version'=>''),
        'WholesaleX' => array('path'=>'wholesalex/wholesalex.php','version'=>'1.2.4'),
		'WCFM' => array('path'=>'wc-frontend-manager/wc_frontend_manager.php','version'=>'') 
    );
	$plugin = new Wholesalex_WCFM($required_plugins);
	$plugin->run();
}

/**
 * Begins execution of the plugin.
 *
 *
 * @since    1.0.0
 */
function wholesalex_wcfm_run() {

	add_action('init','wholesalex_wcfm_init');

}
wholesalex_wcfm_run();
