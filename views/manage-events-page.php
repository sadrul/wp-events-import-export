<?php
global $title;
?>

<div class="wrap">

    <h1><?php echo $title; ?></h1>

    <form action="" method="post" name="import-events-form">
        <table class="form-table">
            <tr>
                <td>
                    <h2><?php _e( 'Import Events', 'events-import-export' ); ?></h2>
                    <div>
                        <input type="submit" name="import-events"
                               value="<?php _e( 'Import Events', 'events-import-export' ); ?>"
                               class="button button-primary"/>
                    </div>
                </td>
            </tr>
        </table>
    </form>

    <hr>

    <form action="" method="post" name="export-events-form">
        <table class="form-table">
            <tr>
                <td>
                    <h2><?php _e( 'Export Events', 'events-import-export' ); ?></h2>
                    <div>
                        <input type="submit" name="export-events"
                               value="<?php _e( 'Export Events', 'events-import-export' ); ?>"
                               class="button button-primary"/>
                    </div>
                </td>
            </tr>
        </table>
    </form>

</div>



