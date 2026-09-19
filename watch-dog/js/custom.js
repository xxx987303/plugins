jQuery(document).ready(function($) {
    $('#modal-id .modal-content').html('<p>This is custom content for the modal jQuery.</p>');
});

$(document).on('modal_open_event', function() {
    $('#modal-id .modal-content').html('<p>This is custom content for the modal "modal_open_event".</p>');
});
