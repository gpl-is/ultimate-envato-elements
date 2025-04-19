<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * and defines a function that starts the plugin.
 *
 * @link              https://gpl.is
 * @since             1.0.0
 * @package           Ultimate_Envato_Elements
 *
 * @wordpress-plugin
 * Plugin Name:       Ultimate Envato Elements
 * Plugin URI:        https://gpl.is
 * Description:       Access premium Elementor template kits and stock photos without an Envato Elements subscription. Seamlessly integrate professional templates and high-quality images into your WordPress site. Browse, preview, and import assets directly from your dashboard to elevate your website's design.
 * Version:           1.0.4
 * Author:            GPL.IS
 * Author URI:        https://gpl.is/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ultimate-envato-elements
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
define( 'ULTIMATE_ENVATO_ELEMENTS_VERSION', '1.0.4' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ultimate-envato-elements.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ultimate_envato_elements() {

	$plugin = new Ultimate_Envato_Elements();
	$plugin->run();
}
run_ultimate_envato_elements();
