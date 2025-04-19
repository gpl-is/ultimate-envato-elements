<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://gpl.is
 * @since      1.0.0
 *
 * @package    Ultimate_Envato_Elements
 * @subpackage Ultimate_Envato_Elements/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ultimate_Envato_Elements
 * @subpackage Ultimate_Envato_Elements/includes
 * @author     GPL.IS <hi@gpl.is>
 */
class Ultimate_Envato_Elements_I18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ultimate-envato-elements',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
