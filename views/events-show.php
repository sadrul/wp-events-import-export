<li>
    <div><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
    <div>
	    <?php
	    $event_timestamp = get_post_meta( get_the_ID(), 'event_timestamp', true );
	    echo \EventsImportExport\EventsShow::instance()->events_time_diff_format( $event_timestamp );
	    ?>
    </div>
    <div><?php the_excerpt(); ?></div>
</li>