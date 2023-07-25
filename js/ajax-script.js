jQuery(document).ready(function(jQuery) {
    // Handle the form submission using AJAX
    console.log("working");
    $('#submit-handler').on('submit', function(event) {
        console.log("working2");
        event.preventDefault();
        var form_data = $(this).serialize();
        jQuery.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: form_data + '&action=submit_form_data',
            success: function(response) {
                // Handle the response, e.g., display success message
                console.log(response);
            },
        });
    });
});