( function ( $ ) {
    'use strict';

    window.Events_Import_Export = {
        init: function () {
            this.import_events_ajax();
        },

        import_events_ajax: function () {
            $( document ).on( 'click', '#import-events-form input[type=submit]', function ( e ) {
                e.preventDefault();

                $('.processing-success').html('').hide();
                $('.processing-spinner').show();

                const data = {
                    'action': 'import_events',
                    'import_events_nonce': $('input#import_events_nonce').val()
                }
                jQuery.post(ajaxurl, data, function (response) {
                    $('.processing-spinner').hide();
                    $('.processing-success').html(response).show();
                })

            } );
        }
    };


    $( document ).on( 'ready', function () {
        Events_Import_Export.init();
    } );


} )( jQuery );