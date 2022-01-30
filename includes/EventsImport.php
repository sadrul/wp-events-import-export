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
 * The class definition for events import.
 *
 * @since 1.0.0
 */
class EventsImport {

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
		add_action( 'init', array( $this, 'create_events_cpt' ) );
		add_action( 'admin_init', array( $this, 'import_events_form_submit' ) );
		add_filter( 'acf/settings/save_json', array( $this, 'acf_json_save_point' ) );
	}

	/**
	 * Set ACF field groups json file save point.
	 *
	 * @param string $path acf json save point path
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function acf_json_save_point( $path ) {
		$path = events_import_export()->plugin_dir . 'acf';

		return $path;
	}

	/**
	 * Import events form submit action.
	 *
	 * @since 1.0.0
	 */
	public function import_events_form_submit() {
		if ( isset( $_POST['import_events_nonce'] )
		     && wp_verify_nonce( $_POST['import_events_nonce'], 'import_events' )
		) {
			// get data from file
			$json_data = file_get_contents( events_import_export()->plugin_dir . 'data.json' );

			if ( ! empty ( $json_data ) ) {
				// decode json data
				$events_data = json_decode( $json_data );

				// create events post from data
				$this->create_events_posts( $events_data );
			}
		}
	}

	/**
	 * Create events posts.
	 *
	 * @param array $events_data events data array.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function create_events_posts( $events_data ) {
		if ( is_array( $events_data ) && ! empty( $events_data ) ) {
			foreach ( $events_data as $single_event ) {
				$this->create_single_event_post( $single_event );
			}
		}
	}

	/**
	 * Create single event post.
	 *
	 * @param array $single_event single event data.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function create_single_event_post( $single_event ) {
		$post_id = wp_insert_post( array(
			'post_type'      => 'event',
			'post_title'     => ( isset( $single_event->title ) && ! empty( $single_event->title ) ) ? $single_event->title : '',
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		) );

		if ( $post_id ) {
			if ( isset( $single_event->id ) && ! empty( $single_event->id ) ) {
				update_field( 'event_id', $single_event->id, $post_id );
			}
			if ( isset( $single_event->about ) && ! empty( $single_event->about ) ) {
				update_field( 'event_about', $single_event->about, $post_id );
			}
			if ( isset( $single_event->organizer ) && ! empty( $single_event->organizer ) ) {
				update_field( 'event_organizer', $single_event->organizer, $post_id );
			}
			if ( isset( $single_event->timestamp ) && ! empty( $single_event->timestamp ) ) {
				update_field( 'event_timestamp', $single_event->timestamp, $post_id );
			}
			if ( isset( $single_event->email ) && ! empty( $single_event->email ) ) {
				update_field( 'event_email', $single_event->email, $post_id );
			}
			if ( isset( $single_event->address ) && ! empty( $single_event->address ) ) {
				update_field( 'event_address', $single_event->address, $post_id );
			}
			if ( isset( $single_event->latitude ) && ! empty( $single_event->latitude ) ) {
				update_field( 'event_latitude', $single_event->latitude, $post_id );
			}
			if ( isset( $single_event->longitude ) && ! empty( $single_event->longitude ) ) {
				update_field( 'event_longitude', $single_event->longitude, $post_id );
			}

			if ( isset( $single_event->tags ) && is_array( $single_event->tags ) && ! empty( $single_event->tags ) ) {
				foreach ( $single_event->tags as $single_tag ) {
					wp_set_object_terms( $post_id, $single_tag, 'post_tag', true );
				}
			}
		}
	}

	/**
	 * Create events cpt.
	 *
	 * @since 1.0.0
	 */
	public function create_events_cpt() {
		$labels = array(
			'name'           => _x( 'Events', 'Post type general name', 'events-import-export' ),
			'singular_name'  => _x( 'Event', 'Post type singular name', 'events-import-export' ),
			'menu_name'      => _x( 'Events', 'Admin Menu text', 'events-import-export' ),
			'name_admin_bar' => _x( 'Event', 'Add New on Toolbar', 'events-import-export' ),
			'add_new'        => __( 'Add New', 'events-import-export' ),
			'add_new_item'   => __( 'Add New event', 'events-import-export' ),
			'new_item'       => __( 'New event', 'events-import-export' ),
			'edit_item'      => __( 'Edit event', 'events-import-export' ),
			'view_item'      => __( 'View event', 'events-import-export' ),
			'all_items'      => __( 'All events', 'events-import-export' ),
			'search_items'   => __( 'Search events', 'events-import-export' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Events custom post type.', 'events-import-export' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'event' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
			'taxonomies'         => array( 'post_tag' ),
			'show_in_rest'       => true
		);
		register_post_type( 'event', $args );
	}

}