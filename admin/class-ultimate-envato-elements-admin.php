<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://gpl.is
 * @since      1.0.0
 *
 * @package    Ultimate_Envato_Elements
 * @subpackage Ultimate_Envato_Elements/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and handles the admin-specific hooks for
 * enqueueing styles and scripts, as well as checking for required dependencies.
 *
 * @package    Ultimate_Envato_Elements
 * @subpackage Ultimate_Envato_Elements/admin
 * @author     GPL.IS <hi@gpl.is>
 */
class Ultimate_Envato_Elements_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name    The name of this plugin.
	 * @param    string $version        The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * Enqueues the admin-specific stylesheet for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ultimate-envato-elements-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * Enqueues the admin-specific JavaScript for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ultimate-envato-elements-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Check if Envato Elements plugin is installed and activated.
	 *
	 * Verifies the presence and activation status of the required Envato Elements plugin.
	 * If the plugin is not installed or activated, it adds an admin notice.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function check_envato_elements_dependency() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Don't show notice during plugin installation
		// Skip dependency check if Envato Elements is being installed.
		$is_installing_envato = isset( $_GET['action'] )
			&& isset( $_GET['_wpnonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'install-plugin' )
			&& 'install-plugin' === $_GET['action']
			&& isset( $_GET['plugin'] )
			&& 'envato-elements' === $_GET['plugin'];

		if ( $is_installing_envato ) {
			return;
		}

		$plugin_slug      = 'envato-elements/envato-elements.php';
		$is_installed     = file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug );
		$is_active        = is_plugin_active( $plugin_slug );
		$required_version = '2.0.16'; // Minimum required version.

		// Check if user has capability to install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		if ( ! $is_installed || ! $is_active ) {
			add_action( 'admin_notices', array( $this, 'display_envato_elements_notice' ) );
		} elseif ( $is_active ) {
			// Check version if plugin is active.
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_slug );

			if ( version_compare( $plugin_data['Version'], $required_version, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'display_version_notice' ) );
			}
		}
	}

	/**
	 * Display admin notice for Envato Elements dependency.
	 *
	 * Shows a warning notice in the admin area if the Envato Elements plugin
	 * is not installed or activated, with appropriate action links.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_envato_elements_notice() {
		$plugin_slug  = 'envato-elements/envato-elements.php';
		$is_installed = file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug );
		$install_url  = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=envato-elements' ), 'install-plugin_envato-elements' );
		$activate_url = wp_nonce_url( 'plugins.php?action=activate&plugin=' . $plugin_slug, 'activate-plugin_' . $plugin_slug );

		$message = sprintf(
			/* translators: %1$s: Plugin name, %2$s: Action or install link */
			__( '%1$s requires Envato Elements plugin to be installed and activated. %2$s', 'ultimate-envato-elements' ),
			'<strong>Ultimate Envato Elements</strong>',
			$is_installed
				? '<a href="' . esc_url( $activate_url ) . '">' . __( 'Click here to activate it', 'ultimate-envato-elements' ) . '</a>'
				: '<a href="' . esc_url( $install_url ) . '">' . __( 'Click here to install it', 'ultimate-envato-elements' ) . '</a>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Display version requirement notice.
	 *
	 * Shows a warning notice if the installed Envato Elements plugin version
	 * is lower than the required version.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_version_notice() {
		$plugin_slug = 'envato-elements/envato-elements.php';
		$update_url  = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $plugin_slug ), 'upgrade-plugin_' . $plugin_slug );
		$dismiss_url = wp_nonce_url( add_query_arg( 'ultimate-envato-elements-dismiss', '1' ), 'ultimate-envato-elements-dismiss' );

		$message = sprintf(
			/* translators: %1$s: Plugin name, %2$s: Update link, %3$s: Dismiss link */
			__( '%1$s requires a newer version of Envato Elements plugin. %2$s %3$s', 'ultimate-envato-elements' ),
			'<strong>Ultimate Envato Elements</strong>',
			'<a href="' . esc_url( $update_url ) . '">' . __( 'Click here to update it', 'ultimate-envato-elements' ) . '</a>',
			'<a href="' . esc_url( $dismiss_url ) . '" style="float: right;">' . __( 'Dismiss', 'ultimate-envato-elements' ) . '</a>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Modify Envato Elements options to enable premium features.
	 *
	 * This method overrides the Envato Elements authentication state
	 * to enable premium features by setting a valid paid subscription.
	 * The cached authentication information is updated with a fixed token
	 * and current timestamp.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function modify_envato_elements_options() {
		// Get current options or initialize empty array.
		$options = get_option( 'envato_elements_options', array() );

		// Ensure options is an array.
		$options = is_array( $options ) ? $options : array();

		// Get current user ID.
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		// Initialize user options if not exists.
		if ( ! isset( $options[ $user_id ] ) ) {
			$options[ $user_id ] = array();
		}

		// Set authentication data with premium status.
		$options[ $user_id ]['elements_token'] = array(
			'valid'  => true,     // Mark authentication as valid.
			'token'  => 'gpl.is', // Fixed authentication token.
			'time'   => time(),   // Current timestamp for cache validation.
			'status' => 'paid',    // Set subscription status to paid.
		);

		// Update options without autoload.
		update_option( 'envato_elements_options', $options, false );
	}

	/**
	 * Modify Envato API request to use our endpoint
	 *
	 * @since    1.0.0
	 * @param    bool|array $preempt     Whether to preempt an HTTP request's return value.
	 * @param    array      $args        HTTP request arguments.
	 * @param    string     $url         The request URL.
	 * @return   bool|array              Modified request response.
	 */
	public function modify_envato_api_request( $preempt, $args, $url ) {
		// Check if this is an Envato Elements API request.
		if ( false !== strpos( $url, 'api.extensions.envato.com/extensions/item/' ) ) {
			// Extract the item ID from the URL.
			preg_match( '/\/item\/([^\/]+)\/download/', $url, $matches );

			if ( ! empty( $matches[1] ) ) {
				$item_id = $matches[1];

				// Construct new URL with our endpoint.
				$new_url = 'https://api.gpl.is/ultimate-envato-elements/?id=' . $item_id;

				// Make the request to our endpoint.
				$response = wp_safe_remote_post( $new_url, $args );

				// Return the response from our endpoint.
				return $response;
			}
		}

		// Return original preempt value for all other requests.
		return $preempt;
	}
}
