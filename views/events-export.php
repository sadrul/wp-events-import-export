<?php
/**
 * Events import export
 *
 * @package   events-import-export
 * @author    Sadrul <https://github.com/sadrul>

 * @link      https://github.com/sadrul/events-import-export
 */

$events_data = array();

$args = array(
	'post_type' => 'event',
	'meta_key'  => 'event_timestamp',
	'meta_type' => 'NUMERIC',
	'orderby'   => 'meta_value_num',
	'order'     => 'ASC',
	'nopaging'  => 1
);

$events_query = new WP_Query( $args );

if ( $events_query->have_posts() ) :

	while ( $events_query->have_posts() ) : $events_query->the_post();

		$event_title   = get_the_title();
		$event_content = get_the_content();
		$event_tags    = get_the_tags( get_the_ID() );
		$event_tags    = wp_list_pluck( $event_tags, 'name' );

		$events_data[] = array(
			'id'        => get_post_meta( get_the_ID(), 'event_id', true ),
			'title'     => trim( $event_title ),
			'about'     => trim( $event_content ),
			'organizer' => get_post_meta( get_the_ID(), 'event_organizer', true ),
			'timestamp' => date( 'Y-m-d H:i:s', get_post_meta( get_the_ID(), 'event_timestamp', true ) ),
			'email'     => get_post_meta( get_the_ID(), 'event_email', true ),
			'address'   => get_post_meta( get_the_ID(), 'event_address', true ),
			'latitude'  => get_post_meta( get_the_ID(), 'event_latitude', true ),
			'longitude' => get_post_meta( get_the_ID(), 'event_longitude', true ),
			'tags'      => $event_tags,
		);

	endwhile;

endif;

wp_reset_postdata();

echo json_encode( $events_data );
