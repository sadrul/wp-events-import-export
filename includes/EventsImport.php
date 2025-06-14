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
		if ( class_exists( 'ACF' ) ) {
			add_filter( 'acf/settings/save_json', array( $this, 'acf_json_save_point' ) );
			add_filter( 'acf/settings/load_json', array( $this, 'acf_json_load_point' ) );
		}
		add_action( 'wp_ajax_import_events', array( $this, 'import_events_form_submit' ) );
	}

	/**
	 * ACF json file load point.
	 *
	 * @param string $path acf json load point path
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function acf_json_load_point( $paths ) {
		unset( $paths[0] );
		$paths[] = events_import_export()->plugin_dir . 'acf';

		return $paths;
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
			$import_details = $this->import_events_from_json_file();
			if ( isset( $import_details ) && ! empty( $import_details ) ) {
				printf( __( 'Total %d events imported successfully! Newly created: %d events and updated: %d events', 'events-import-export' ), $import_details['total'], $import_details['new'], $import_details['update'] );
			}else{
				_e( 'Something went wrong! Please check if the json file exists and in correct format.', 'events-import-export' );
			}
			exit;
		}
	}

	/**
	 * Import events form json file.
	 *
	 * @since 1.0.0
	 */
	public function import_events_from_json_file() {
		$result = array();

		// get data from file
		$json_data = file_get_contents( events_import_export()->plugin_dir . 'data.json' );

		if ( ! empty ( $json_data ) ) {
			// decode json data
			$events_data = json_decode( $json_data );

			// create events post from data
			$result = $this->create_events_posts( $events_data );
		}

		return $result;
	}

	/**
	 * Check if event exists.
	 *
	 * @param int $event_id event id from custom field.
	 *
	 * @since 1.0.0	 *
	 * @return false|int
	 */
	public function check_event_exists( $event_id ) {
		$args = array(
			'post_type'   => 'event',
			'post_status' => array( 'draft', 'publish' ),
			'meta_query'  => array(
				array(
					'key'     => 'event_id',
					'value'   => $event_id,
					'compare' => '=',
				),
			),
		);
		$event_query = new \WP_Query( $args );
		if ( $event_query->have_posts() ) {
			return isset( $event_query->posts[0] ) ? $event_query->posts[0]->ID : false;
		}

		return false;
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
		$import_details      = array();
		$new_events_count    = 0;
		$update_events_count = 0;

		if ( is_array( $events_data ) && ! empty( $events_data ) ) {
			foreach ( $events_data as $single_event ) {
				// check event already exists.
				if ( $event_post_id = $this->check_event_exists( $single_event->id ) ) {
					// update event.
					if ( $this->create_single_event_post( $single_event, $event_post_id ) ) {
						$update_events_count ++;
					}
				} else {
					// create new event.
					if ( $this->create_single_event_post( $single_event ) ) {
						$new_events_count ++;
					}
				}
			}

			update_option( 'import_events_new_count', $new_events_count );
			update_option( 'import_events_update_count', $update_events_count );

			// send email
			$import_details = array(
				'total'  => ( $new_events_count + $update_events_count ),
				'new'    => $new_events_count,
				'update' => $update_events_count,
			);
			$this->import_events_send_email( $import_details );
		}

		return $import_details;
	}

	/**
	 * Send emails regarding import events.
	 *
	 * @param array $import_details import details.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function import_events_send_email( $import_details ) {
		$events_page = get_page_by_title( 'Loop events list' );
		$events_page = get_the_permalink( $events_page->ID );

		$events_export_page = get_page_by_title( 'Events export' );
		$events_export_page = get_the_permalink( $events_export_page->ID );

		$subject = __( 'Update regarding events import', 'events-import-export' );
		$message = sprintf( __( "Total %d events imported successfully! Newly created: %d events and updated: %d events", "events-import-export" ), $import_details['total'], $import_details['new'], $import_details['update'] );
		$message .= sprintf( __( "\n\nTo see the upcoming events, please click here - %s", "events-import-export" ), $events_page );
		$message .= sprintf( __( "\n\nTo export the upcoming events, please click here - %s", "events-import-export" ), $events_export_page );
		wp_mail( 'testemail@testemail.com', $subject, $message );
	}

	/**
	 * Create single event post.
	 *
	 * @param array $single_event single event data.
	 *
	 * @return false|int
	 * @since 1.0.0
	 */
	public function create_single_event_post( $single_event, $event_post_id = 0 ) {
		$post_status = 'publish';

		if ( isset( $single_event->timestamp ) && ! empty( $single_event->timestamp ) && ( time() > strtotime( $single_event->timestamp ) ) ) {
			$post_status = 'draft';
		}

		$post_id = wp_insert_post(
			array(
				'ID'             => $event_post_id,
				'post_type'      => 'event',
				'post_title'     => ( isset( $single_event->title ) && ! empty( $single_event->title ) ) ? $single_event->title : '',
				'post_content'   => ( isset( $single_event->about ) && ! empty( $single_event->about ) ) ? $single_event->about : '',
				'post_status'    => $post_status,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			// Add custom fields.
			if ( class_exists( 'ACF' ) ) {
				$this->acf_add_custom_fields( $post_id, $single_event );
			} else {
				$this->wp_add_custom_fields( $post_id, $single_event );
			}
			// Add tags.
			if ( isset( $single_event->tags ) && is_array( $single_event->tags ) && ! empty( $single_event->tags ) ) {
				foreach ( $single_event->tags as $single_tag ) {
					wp_set_object_terms( $post_id, $single_tag, 'post_tag', true );
				}
			}

			return $post_id;
		} else {
			return false;
		}
	}

	/**
	 * WP add custom fields
	 *
	 * @param $post_id
	 * @param $single_event
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function wp_add_custom_fields( $post_id, $single_event ) {
		if ( isset( $single_event->id ) && ! empty( $single_event->id ) ) {
			update_post_meta( $post_id, 'event_id', $single_event->id );
		}
		if ( isset( $single_event->organizer ) && ! empty( $single_event->organizer ) ) {
			update_post_meta( $post_id, 'event_organizer', $single_event->organizer );
		}
		if ( isset( $single_event->timestamp ) && ! empty( $single_event->timestamp ) ) {
			update_post_meta( $post_id, 'event_timestamp', strtotime( $single_event->timestamp ) );
		}
		if ( isset( $single_event->email ) && ! empty( $single_event->email ) ) {
			update_post_meta( $post_id, 'event_email', $single_event->email );
		}
		if ( isset( $single_event->address ) && ! empty( $single_event->address ) ) {
			update_post_meta( $post_id, 'event_address', $single_event->address );
		}
		if ( isset( $single_event->latitude ) && ! empty( $single_event->latitude ) ) {
			update_post_meta( $post_id, 'event_latitude', $single_event->latitude );
		}
		if ( isset( $single_event->longitude ) && ! empty( $single_event->longitude ) ) {
			update_post_meta( $post_id, 'event_longitude', $single_event->longitude );
		}
	}

	/**
	 * ACF add custom field
	 *
	 * @param int $post_id post ID
	 * @param object $single_event single event data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function acf_add_custom_fields( $post_id, $single_event ) {
		if ( isset( $single_event->id ) && ! empty( $single_event->id ) ) {
			update_field( 'event_id', $single_event->id, $post_id );
		}
		if ( isset( $single_event->organizer ) && ! empty( $single_event->organizer ) ) {
			update_field( 'event_organizer', $single_event->organizer, $post_id );
		}
		if ( isset( $single_event->timestamp ) && ! empty( $single_event->timestamp ) ) {
			update_field( 'event_timestamp', strtotime( $single_event->timestamp ), $post_id );
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