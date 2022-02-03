<?php
/**
 * Events import export
 *
 * @package   events-import-export
 * @author    Sadrul <https://github.com/sadrul>

 * @link      https://github.com/sadrul/events-import-export
 */

get_header();

while ( have_posts() ) :
	the_post();
    ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header alignwide">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>

	<div class="entry-content">
        <div class="event-timestamp">
			<?php
			$event_timestamp = get_post_meta( get_the_ID(), 'event_timestamp', true );
			echo \EventsImportExport\EventsShow::instance()->events_time_diff_format( $event_timestamp );
			?>
        </div>

		<?php the_content(); ?>

        <div class="event-id"><?php printf( __( '<span>ID</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_id', true ) ); ?></div>
        <div class="event-organizer"><?php printf( __( '<span>Organizer</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_organizer', true ) ); ?></div>
        <div class="event-email"><?php printf( __( '<span>Email</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_email', true ) ); ?></div>
        <div class="event-address"><?php printf( __( '<span>Address</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_address', true ) ); ?></div>
        <div class="event-latitude"><?php printf( __( '<span>Latitude</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_latitude', true ) ); ?></div>
        <div class="event-longitude"><?php printf( __( '<span>Longitude</span>: %s', 'events-import-export' ), get_post_meta( get_the_ID(), 'event_longitude', true ) ); ?></div>

    </div>

	<?php if ( ! is_singular( 'attachment' ) ) : ?>
		<?php get_template_part( 'template-parts/post/author-bio' ); ?>
	<?php endif; ?>

</article>

<?php
endwhile;

get_footer();