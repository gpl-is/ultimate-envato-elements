<?php
/**
 * GitHub Updater for Ultimate Envato Elements
 *
 * @link       https://gpl.is
 * @since      1.0.0
 *
 * @package    Ultimate_Envato_Elements
 * @subpackage Ultimate_Envato_Elements/includes
 */

/**
 * GitHub Updater class.
 *
 * Handles checking for updates from GitHub and updating the plugin.
 *
 * @since      1.0.0
 * @package    Ultimate_Envato_Elements
 * @subpackage Ultimate_Envato_Elements/includes
 * @author     GPL.IS <hi@gpl.is>
 */
class Ultimate_Envato_Elements_Updater {

	/**
	 * The GitHub repository owner.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $owner    The GitHub repository owner.
	 */
	private $owner;

	/**
	 * The GitHub repository name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $repo    The GitHub repository name.
	 */
	private $repo;

	/**
	 * The plugin slug.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The plugin slug.
	 */
	private $plugin_slug;

	/**
	 * The plugin basename.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_basename    The plugin basename.
	 */
	private $plugin_basename;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->owner       = 'gpl-is';
		$this->repo        = 'ultimate-envato-elements';
		$this->plugin_slug = 'ultimate-envato-elements';

		// Get the current plugin directory name.
		$plugin_dir            = basename( dirname( __DIR__ ) );
		$this->plugin_basename = $plugin_dir . '/ultimate-envato-elements.php';

		// Add update checker.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'rename_github_folder' ), 10, 4 );

		// Clear update info after plugin update is complete.
		add_action( 'upgrader_process_complete', array( $this, 'clear_update_info' ), 10, 2 );
	}

	/**
	 * Rename the GitHub folder to match the expected plugin folder name.
	 *
	 * @since    1.0.0
	 * @param    string $source        The source folder.
	 * @param    string $remote_source The remote source.
	 * @param    object $upgrader      The upgrader object.
	 * @param    array  $hook_extra    Extra arguments.
	 * @return   string                The modified source folder.
	 */
	public function rename_github_folder( $source, $remote_source, $upgrader, $hook_extra ) {
		global $wp_filesystem;

		// Check if this is our plugin.
		if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin_basename ) {
			$new_source = trailingslashit( $remote_source ) . 'ultimate-envato-elements/';

			// Create the new directory.
			$wp_filesystem->mkdir( $new_source );

			// Move all files from the versioned folder to the new folder.
			$old_source = trailingslashit( $source );
			$files      = $wp_filesystem->dirlist( $old_source );

			if ( $files ) {
				foreach ( $files as $file ) {
					$wp_filesystem->move( $old_source . $file['name'], $new_source . $file['name'], true );
				}
			}

			// Remove the old directory.
			$wp_filesystem->delete( $old_source, true );

			return $new_source;
		}

		return $source;
	}

	/**
	 * Check for updates from GitHub.
	 *
	 * @since    1.0.0
	 * @param    object $transient    The transient object.
	 * @return   object               The modified transient object.
	 */
	public function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote_data     = $this->get_remote_version();
		$remote_version  = $remote_data['version'];
		$current_version = ULTIMATE_ENVATO_ELEMENTS_VERSION;

		// Only add update if there's a newer version.
		if ( version_compare( $current_version, $remote_version, '<' ) ) {
			$obj              = new stdClass();
			$obj->slug        = $this->plugin_slug;
			$obj->plugin      = $this->plugin_basename;
			$obj->new_version = $remote_version;
			$obj->url         = "https://github.com/{$this->owner}/{$this->repo}";
			$obj->package     = "https://github.com/{$this->owner}/{$this->repo}/archive/refs/tags/{$remote_data['tag_name']}.zip";
			$transient->response[ $this->plugin_basename ] = $obj;
		}

		return $transient;
	}

	/**
	 * Get plugin information from GitHub.
	 *
	 * @since    1.0.0
	 * @param    bool|object $result   False or object.
	 * @param    string      $action   The action.
	 * @param    object      $args     The arguments.
	 * @return   object                The plugin information.
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( $args->slug !== $this->plugin_slug ) {
			return $result;
		}

		$remote_data     = $this->get_remote_version();
		$remote_version  = $remote_data['version'];
		$current_version = ULTIMATE_ENVATO_ELEMENTS_VERSION;

		$obj                 = new stdClass();
		$obj->slug           = $this->plugin_slug;
		$obj->plugin_name    = 'Ultimate Envato Elements';
		$obj->name           = 'Ultimate Envato Elements';
		$obj->version        = $remote_version;
		$obj->last_updated   = gmdate( 'Y-m-d' );
		$obj->requires       = '6.0';
		$obj->tested         = '6.8';
		$obj->author         = '<a href="https://gpl.is">GPL.IS</a>';
		$obj->author_profile = 'https://gpl.is';
		$obj->homepage       = 'https://gpl.is';
		$obj->sections       = array(
			'description' => 'Access premium Elementor template kits and stock photos without an Envato Elements subscription.',
			'changelog'   => $this->get_changelog(),
		);
		$obj->download_link  = "https://github.com/{$this->owner}/{$this->repo}/archive/refs/tags/{$remote_data['tag_name']}.zip";

		return $obj;
	}

	/**
	 * Get the remote version from GitHub.
	 *
	 * @since    1.0.0
	 * @return   array    The remote version data.
	 */
	private function get_remote_version() {
		$response = wp_remote_get( "https://api.github.com/repos/{$this->owner}/{$this->repo}/releases/latest" );

		if ( is_wp_error( $response ) ) {
			return array(
				'version'  => ULTIMATE_ENVATO_ELEMENTS_VERSION,
				'tag_name' => 'v' . ULTIMATE_ENVATO_ELEMENTS_VERSION,
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( ! isset( $data->tag_name ) ) {
			return array(
				'version'  => ULTIMATE_ENVATO_ELEMENTS_VERSION,
				'tag_name' => 'v' . ULTIMATE_ENVATO_ELEMENTS_VERSION,
			);
		}

		return array(
			'version'  => ltrim( $data->tag_name, 'v' ),
			'tag_name' => $data->tag_name,
		);
	}

	/**
	 * Get the changelog from GitHub.
	 *
	 * @since    1.0.0
	 * @return   string    The changelog.
	 */
	private function get_changelog() {
		$response = wp_remote_get( "https://api.github.com/repos/{$this->owner}/{$this->repo}/releases/latest" );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( ! isset( $data->body ) ) {
			return '';
		}

		// Convert markdown to HTML.
		$changelog = $data->body;

		// Split content into sections.
		$sections            = preg_split( '/\n(?=#)/', $changelog );
		$formatted_changelog = '';

		foreach ( $sections as $section ) {
			// Convert headers.
			$section = preg_replace( '/^#\s+(.*)$/m', '<h1>$1</h1>', $section );
			$section = preg_replace( '/^##\s+(.*)$/m', '<h2>$1</h2>', $section );
			$section = preg_replace( '/^###\s+(.*)$/m', '<h3>$1</h3>', $section );

			// Convert lists.
			$section = preg_replace( '/^\s*-\s+(.*)$/m', '<li>$1</li>', $section );
			$section = preg_replace( '/(<li>.*<\/li>)/s', '<ul>$1</ul>', $section );

			// Convert links.
			$section = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $section );

			// Remove markdown characters.
			$section = str_replace( '**', '', $section );
			$section = str_replace( '*', '', $section );
			$section = str_replace( '`', '', $section );

			// Add section to formatted changelog.
			$formatted_changelog .= $section;
		}

		// Ensure proper HTML structure.
		$formatted_changelog = '<div class="changelog">' . $formatted_changelog . '</div>';

		return $formatted_changelog;
	}

	/**
	 * Clear update information from transient after update completion.
	 *
	 * @since    1.0.4
	 * @param    object $upgrader_object The upgrader object.
	 * @param    array  $options         The update options.
	 */
	public function clear_update_info( $upgrader_object, $options ) {
		if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
			$update_plugins = get_site_transient( 'update_plugins' );

			// Check if our plugin is in the update list and remove it from the response.
			if ( isset( $update_plugins->response[ $this->plugin_basename ] ) ) {
				unset( $update_plugins->response[ $this->plugin_basename ] );
				set_site_transient( 'update_plugins', $update_plugins );
			}
		}
	}
}
