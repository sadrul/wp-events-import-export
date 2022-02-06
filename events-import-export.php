<?php
/**
 * Events import export
 *
 * @package   events-import-export
 * @author    Sadrul <https://github.com/sadrul>
 * @link      https://github.com/sadrul/events-import-export
 *
 * Plugin Name:     Events import export
 * Plugin URI:      https://github.com/sadrul/events-import-export
 * Description:     Import, export and show events data.
 * Version:         1.0
 * Author:          Sadrul
 * Author URI:      https://github.com/sadrul
 * Text Domain:     events-import-export
 * Domain Path:     /languages
 * Requires PHP:    7.1
 * Requires WP:     5.5.0
 * Namespace:       EventsImportExport
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
	public $plugin_prefix = 'eie';

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
	public $domain = 'events-import-export';

	/**
	 * namespace.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $namespace = 'EventsImportExport';

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

		// Register activation hooks.
		register_activation_hook( $root_file, array( $this, 'on_plugin_activate' ) );

		add_action( 'plugins_loaded', array( $this, 'load' ), 0 );
	}

	/**
	 * Actions on plugin activate.
	 *
	 * @since 1.0.0
	 */
	public function on_plugin_activate(){
		// create events list page.
		EventsImportExport\EventsShow::instance()->create_events_list_page();

		// create events export page.
		EventsImportExport\EventsExport::instance()->create_events_export_page();

		// Create events cpt.
		EventsImportExport\EventsImport::instance()->create_events_cpt();

		// flush rewrite rules.
		$this->flush_rewrite_rules();
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

		// Import events.
		EventsImportExport\EventsImport::instance();

		// Export events.
		EventsImportExport\EventsExport::instance();

		// Show events.
		EventsImportExport\EventsShow::instance();
	}

	/**
	 * Register all hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		// add admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
		// add frontend assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_assets' ) );
		// add admin menu.
		add_action( 'admin_menu', array( $this, 'create_admin_pages' ) );
		// make wp-cli ready.
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::add_command( 'import-events', array( $this, 'import_events_data' ) );
		}
	}

	/**
	 * Add frontend assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function frontend_enqueue_assets() {
		wp_enqueue_style( 'frontend-css', $this->plugin_url . 'assets/css/frontend.css', array(), $this->plugin_version );
	}

	/**
	 * Add admin assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function admin_enqueue_assets() {
		wp_enqueue_style( 'frontend-css', $this->plugin_url . 'assets/css/frontend.css', array(), $this->plugin_version );

		wp_enqueue_script(
			'backend-js',
			$this->plugin_url . 'assets/js/backend.js',
			array(
				'jquery',
			),
			$this->plugin_version,
			true
		);
	}

	/**
	 * Create admin pages.
	 *
	 * @since 1.0.0
	 */
	function create_admin_pages(){
		add_menu_page(
			__('Manage Events', 'events-import-export'),
			__('Manage Events', 'events-import-export'),
			'manage_options',
			'manage-events',
			array( $this, 'manage_events_render' )
		);
	}

	/**
	 * Events import export admin page render
	 *
	 * @since 1.0.0
	 */
	function manage_events_render(){
		include events_import_export()->plugin_dir . 'views/manage-events-page.php';
	}

	/**
	 * Import events data.
	 *
	 * @since 1.0.0
	 */
	public function import_events_data() {
		$import_details = EventsImportExport\EventsImport::instance()->import_events_from_json_file();
		// display import result.
		if ( class_exists( 'WP_CLI' ) ) {
			if ( isset( $import_details ) && ! empty( $import_details ) ) {
				WP_CLI::log( sprintf( __( 'Total %d events imported successfully! Newly created: %d events and updated: %d events', 'events-import-export' ), $import_details['total'], $import_details['new'], $import_details['update'] ) );
			} else {
				WP_CLI::log( __( 'Something went wrong! Please check if the json file exists and in correct format.', 'events-import-export' ) );
			}
		}
	}

	/**
	 * Flush rewrite rules.
	 *
	 * @since 1.0.0
	 */
	public function flush_rewrite_rules(){
		flush_rewrite_rules();

		// flush cache when caching plugin is active.
		if ( function_exists( 'flush_all' ) ) {
			flush_all();
		} else {
			// Clear the cache.
			wp_cache_flush();
		}
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

/**
 * Autoload class files.
 */
spl_autoload_register( function ( $className ) {
	$namespace = events_import_export()->namespace;

	if ( strpos( $className, $namespace ) !== 0 ) {
		return;
	}

	$className = str_replace( $namespace, '', $className );
	$className = str_replace( '\\', DIRECTORY_SEPARATOR, $className ) . '.php';

	$directory = events_import_export()->plugin_dir;
	$path      = $directory . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $className;

	if ( file_exists( $path ) ) {
		require_once( $path );
	}
} );