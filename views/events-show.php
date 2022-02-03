<?php
/**
 * Events import export
 *
 * @package   events-import-export
 * @author    Sadrul <https://github.com/sadrul>

 * @link      https://github.com/sadrul/events-import-export
 */
?>

<li>
    <div class="event-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
    <div class="event-timestamp">
		<?php
		$event_timestamp = get_post_meta( get_the_ID(), 'event_timestamp', true );
		echo \EventsImportExport\EventsShow::instance()->events_time_diff_format( $event_timestamp );
		?>
    </div>
    <div class="event-organizer"><?php printf( __( '<span>Organizer</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_organizer', true ) ); ?></div>
    <div class="event-address"><?php printf( __( '<span>Address</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_address', true ) ); ?></div>
</li>