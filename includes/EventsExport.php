<?php
/**
 * Events import export
 *
 * @package   events-import-export
 * @author    Sadrul <https://github.com/sadrul>

 * @link      https://github.com/sadrul/events-import-export
 */

namespace EventsImportExport;


/**
 * The class definition for events export.
 *
 * @since 1.0.0
 */
class EventsExport {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @var $instance
	 */
	private static $instance;

	/**
	 * events export page ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $events_export_page_id = '';

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
	 */
	public function _load() {
		$this->hooks();
	}

	/**
	 * Function to load all hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		// create events export page.
		add_action( 'admin_init', array( $this, 'create_events_export_page' ) );
		// add events export page template.
		add_filter( 'page_template', array( $this, 'events_export_page_template' ) );
	}

	/**
	 * Create events export page.
	 *
	 * @since 1.0.0
	 */
	public function create_events_export_page() {
		$page_ID = 0;

		// check if page already exists.
		$if_exists = get_page_by_title( 'Events export' );

		if ( $if_exists && isset( $if_exists->ID ) && ! empty( $if_exists->ID ) ) {
			$page_ID = $if_exists->ID;
		}

		// create/update page.
		wp_insert_post(
			array(
				'post_title'   => 'Events export',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'ID'           => $page_ID,
			)
		);

		$this->events_export_page_id = $page_ID;
	}

	/**
	 * Add events export page template.
	 *
	 * @since 1.0.0
	 */
	public function events_export_page_template( $single ) {
		global $post;

		if ( $post->post_type == 'page' && $post->post_title == 'Events export' ) {
			if ( file_exists( events_import_export()->plugin_dir . 'views/events-export.php' ) ) {
				return events_import_export()->plugin_dir . 'views/events-export.php';
			}
		}

		return $single;
	}

}