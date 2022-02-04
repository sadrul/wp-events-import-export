<?php
/**
 * Events import export
 *
 * @package   events-import-export
 * @author    Sadrul <https://github.com/sadrul>

 * @link      https://github.com/sadrul/events-import-export
 */

namespace EventsImportExport;


use DateTime;
use WP_Query;

/**
 * The class definition for events show.
 *
 * @since 1.0.0
 */
class EventsShow {

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
		// create events list page.
		add_action( 'admin_init', array( $this, 'create_events_list_page' ) );
		// register shortcode.
		add_action( 'init', array( $this, 'register_shortcode' ) );
		// event single post content modify.
		add_filter( 'the_content', array( $this, 'event_single_post_content_modify' ) );
	}

	/**
	 * Event single post content modify.
	 *
	 * @since 1.0.0
	 */
	public function event_single_post_content_modify( $content ) {
		if ( is_singular() && in_the_loop() && is_main_query() && get_post_type( get_the_ID() ) == 'event' ) {
			ob_start();
			?>
            <div class="event-timestamp">
				<?php
				$event_timestamp = get_post_meta( get_the_ID(), 'event_timestamp', true );
				echo \EventsImportExport\EventsShow::instance()->events_time_diff_format( $event_timestamp );
				?>
            </div>

			<?php echo $content; ?>

            <div class="event-id"><?php printf( __( '<span>ID</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_id', true ) ); ?></div>
            <div class="event-organizer"><?php printf( __( '<span>Organizer</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_organizer', true ) ); ?></div>
            <div class="event-email"><?php printf( __( '<span>Email</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_email', true ) ); ?></div>
            <div class="event-address"><?php printf( __( '<span>Address</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_address', true ) ); ?></div>
            <div class="event-latitude"><?php printf( __( '<span>Latitude</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_latitude', true ) ); ?></div>
            <div class="event-longitude"><?php printf( __( '<span>Longitude</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_longitude', true ) ); ?></div>

			<?php
			$content = ob_get_clean();
		}

		return $content;
	}

	/**
	 * Register shortcode.
	 *
	 * @since 1.0.0
	 */
	public function register_shortcode(){
		add_shortcode( 'events_list', array( $this, 'events_list_render' ) );
	}

	/**
	 * Create shortcode to display events list.
	 *
	 * @since 1.0.0
	 */
	public function events_list_render( $atts ) {
		ob_start();

		$paged        = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$args         = array(
			'post_type'      => 'event',
			'posts_per_page' => 10,
			'meta_key'       => 'event_timestamp',
			'meta_type'      => 'NUMERIC',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'paged'          => $paged
		);
		$events_query = new WP_Query( $args );

		if ( $events_query->have_posts() ) : ?>
            <div class="events-wrap">
                <ul class="events-lists">
                    <?php
                    while ( $events_query->have_posts() ) : $events_query->the_post();
                        include events_import_export()->plugin_dir . 'views/events-show.php';
                    endwhile;
                    $this->events_pagination( $paged, $events_query->max_num_pages);
                    ?>
                </ul>
            </div>
            <?php
        endif;
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Number pagination function.
	 *
	 * @since 1.0.0
	 */
	public function events_pagination( $paged, $max_page ) {
		$big = 999999999;
		if ( ! $paged ) {
			$paged = get_query_var( 'paged' );
		}
		echo paginate_links( array(
			'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'    => '?paged=%#%',
			'current'   => max( 1, $paged ),
			'total'     => $max_page,
			'mid_size'  => 1,
			'prev_text' => __( '«' ),
			'next_text' => __( '»' ),
			'type'      => 'list'
		) );
	}

	/**
	 * Create a page to display events list.
	 *
	 * @since 1.0.0
	 */
	public function create_events_list_page() {
		$page_ID = 0;

		// check if page already exists.
		$if_exists = get_page_by_title( 'Loop events list' );

		// bail out if already exists with target shortcode.
		if ( $if_exists && isset( $if_exists->post_content ) && $if_exists->post_content == '[events_list]' ) {
			return;
		}

		// if target shortcode not in content, then update the page.
		if ( $if_exists && isset( $if_exists->ID ) && ! empty( $if_exists->ID ) ) {
			$page_ID = $if_exists->ID;
		}

		// create/update page.
		wp_insert_post(
			array(
				'post_title'   => 'Loop events list',
				'post_content' => '[events_list]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'ID'           => $page_ID,
			)
		);

		// flush rewrite rules.
		events_import_export()->flush_rewrite_rules();
	}

	/**
	 * Event time diff format.
	 *
	 * @since 1.0.0
	 */
	public function events_time_diff_format( $to_date ) {
		$to_date    = date( 'Y-m-d H:i:s', $to_date );
		$from_date  = date( 'Y-m-d H:i:s' );
		$from_date  = new DateTime( $from_date );
		$to_date    = new DateTime( $to_date );
		$interval   = $from_date->diff( $to_date );
		$difference = array();

		if ( $interval->m > 0 ) {
			$difference[] = $interval->m . _n( ' month', ' months', $interval->m, 'events-import-export' ) . "\n";
		}
		if ( $interval->d > 0 ) {
			$difference[] = $interval->d . _n( ' day', ' days', $interval->d, 'events-import-export' ) . "\n";
		}
		if ( $interval->h > 0 ) {
			$difference[] = $interval->h . _n( ' hour', ' hours', $interval->h, 'events-import-export' ) . "\n";
		}
		if ( $interval->i > 0 ) {
			$difference[] = $interval->i . _n( ' minute', ' minutes', $interval->i, 'events-import-export' ) . "\n";
		}

		if ( ! empty( $difference ) ) {
			$difference = __( 'in ', 'events-import-export' ) . implode( ', ', $difference );
		}

		return $difference;
	}

}