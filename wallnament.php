<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.wallnament.com
 * @since             1.0.0
 * @package           Wallnament
 *
 * @wordpress-plugin
 * Plugin Name:       Wallnament
 * Plugin URI:        https://www.wallnament.com/plugins/wordpress
 * Description:       Wallnament plugin for integration with WooCommerce
 * Version:           1.0.0
 * Author:            Wallnament
 * Author URI:        https://www.wallnament.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wallnament
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WALLNAMENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wallnament-activator.php
 */
function activate_wallnament() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wallnament-activator.php';
	Wallnament_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wallnament-deactivator.php
 */
function deactivate_wallnament() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wallnament-deactivator.php';
	Wallnament_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wallnament' );
register_deactivation_hook( __FILE__, 'deactivate_wallnament' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wallnament.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wallnament() {

	$plugin = new Wallnament();
	$plugin->run();

}
run_wallnament();
