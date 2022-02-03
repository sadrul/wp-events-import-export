<?php
/**
 * Events import export
 *
 * @package   events-import-export
 * @author    Sadrul <https://github.com/sadrul>

 * @link      https://github.com/sadrul/events-import-export
 */

global $title;
?>

<div class="wrap">

    <h1><?php echo $title; ?></h1>

    <form action="" method="post" name="import-events-form" id="import-events-form">
        <table class="form-table">
            <tr>
                <td>
                    <h2><?php _e( 'Import Events', 'events-import-export' ); ?></h2>
                    <div>
	                    <?php wp_nonce_field( 'import_events', 'import_events_nonce' ); ?>
                        <input type="submit" name="import-events"
                               value="<?php _e( 'Import Events', 'events-import-export' ); ?>"
                               class="button button-primary"/>

                        <h4 class="processing-spinner" style="display: none">
                            <img src="<?php site_url(); ?>/wp-includes/images/wpspin.gif" alt="">
                            <?php _e( 'Processing.... It might take some time. Please be patient.', 'events-import-export' ); ?>
                        </h4>
                        <h4 class="processing-success" style="display: none"></h4>
                    </div>
                </td>
            </tr>
        </table>
    </form>

    <hr>

    <table class="form-table">
        <tr>
            <td>
                <h2><?php _e( 'Show Events', 'events-import-export' ); ?></h2>
                <div>
                    <?php $events_page = get_page_by_title( 'Loop events list' ); ?>
                    <a target="_blank" href="<?php echo get_the_permalink( $events_page->ID ); ?>"><?php _e( 'Go to events list page', 'events-import-export' ); ?></a>
                </div>
            </td>
        </tr>
    </table>

    <hr>

    <table class="form-table">
        <tr>
            <td>
                <h2><?php _e( 'Export Events', 'events-import-export' ); ?></h2>
                <div>
			        <?php $events_export_page = get_page_by_title( 'Events export' ); ?>
                    <a target="_blank" href="<?php echo get_the_permalink( $events_export_page->ID ); ?>"><?php _e( 'Export Events', 'events-import-export' ); ?></a>
                </div>
            </td>
        </tr>
    </table>

</div>



