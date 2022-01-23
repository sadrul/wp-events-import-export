<?php
/*
Plugin Name: Events import export
Description: Import, export and show events data.
Version: 1.0
Author: Sadrul
*/

declare( strict_types = 1 );

/**
 * Define the default root file of the plugin
 *
 * @since 1.0.0
 */
const EIE_PLUGIN_FILE = __FILE__;

/**
 * EventsImportExport class
 *
 * @since 1.0.0
 */
class EventsImportExport {

	/**
	 * Plugin url.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Plugin dir
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Plugin prefix.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $plugin_prefix = 'eie'; // should be char & _

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $plugin_version = '1.0.0';

	/**
	 * Plugin domain.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $domain = 'events-import-export'; // it's used for language domain.

	/**
	 * Plugin root file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $root_file;

	/**
	 * DB version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $db_version = '0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Create Instance of the class
	 *
	 * @param bool $root_file Root file location.
	 *
	 * @since 1.0.0
	 */
	final public static function instance( $root_file = false ): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
			if ( $root_file ) {
				self::$instance->_load( $root_file );
			} else {
				self::$instance->_load();
			}
		}
		return self::$instance;
	}

	/**
	 * Function to load all essential components
	 *
	 * @since 1.0.0
	 * @param bool $root_file root file.
	 */
	public function _load( $root_file = false ) {
		$this->root_file      = $root_file;
		$this->plugin_dir     = plugin_dir_path( $this->root_file );
		$this->plugin_url     = plugin_dir_url( $this->root_file );

		define( 'EIE_PLUGIN_URL', $this->plugin_url );

		add_action( 'plugins_loaded', array( $this, 'load' ), 0 );
	}

	/**
	 * Everything which needs to be load under plugins_loaded priority.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		// Load Translate
		$this->load_i18n();

		// Register the hooks
		$this->hooks();
	}

	/**
	 * Register all hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
	}

	/**
	 * Loads text domain.
	 *
	 * @since 1.0.0
	 */
	public function load_i18n() {
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->domain );

		// first try to load from wp-content/languages/plugins/ directory.
		load_textdomain( $this->domain, WP_LANG_DIR . '/plugins/' . $this->domain . '-' . $locale . '.mo' );

		load_plugin_textdomain(
			$this->domain,
			false,
			basename( $this->domain ) . '/languages'
		);
	}

	/**
	 * On plugin deactivate actions.
	 *
	 * @since 1.0.0
	 */
	public function on_plugin_deactivate() {

		do_action( "{$this->plugin_prefix}_on_plugin_deactivate" );
	}

}


/**
 * Main function to use externally
 *
 * @return EventsImportExport
 * @since 1.0.0
 */
function events_import_export(): \EventsImportExport {
	return EventsImportExport::instance( EIE_PLUGIN_FILE );
}

/**
 * Call the main function to load class Instance.
 */
events_import_export();
