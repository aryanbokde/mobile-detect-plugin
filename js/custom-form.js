jQuery(document).ready(function($) {
    $('#ast_mobile_detect_formHandler').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var formData = $form.serialize();

        // Disable the submit button during form submission
        $form.find('button[type="submit"]').prop('disabled', true);
        
        $.ajax({
            type: 'POST',
            url: ast_mobile_detect_plugin_ajax_object.ajax_url,
            data: formData + '&action=ast_custom_form_submit',
            success: function(response) {
                // Do any additional handling of the response data here
                if ( response.success === true ) {

                    alert(response.data.message);
                    console.log(response);
                    // Clear form inputs and enable the submit button
                    $form[0].reset();
                    // Enable the submit button after form submission
                    $form.find('button[type="submit"]').prop('disabled', false);
                    // Automatically reset the form after 3 seconds
                    setTimeout(function() {
                        $form.find('.error-message').remove();
                    }, 30000);
                    
                }else{

                    // Clear previous error messages
                    $('.error-message').remove();
                    // console.log(response.data.errors);
                    // Display error messages next to corresponding input fields
                    if (response.data.errors) {
                        $.each(response.data.errors, function(field,message) {
                            $('#' + field).after('<span class="error-message" style="color:red;">' + message + '</span>');
                        });
                    }

                    // Enable the submit button after form submission
                    $form.find('button[type="submit"]').prop('disabled', false);
                    
                    // Automatically reset the form after 3 seconds
                    setTimeout(function() {
                        $form.find('.error-message').remove();
                    }, 30000);

                }                
            },
            error: function(xhr, textStatus, errorThrown) {
                alert('Error: ' + xhr.responseText);
                // Enable the submit button after form submission
                $form.find('button[type="submit"]').prop('disabled', false);
                // Automatically reset the form after 3 seconds
                setTimeout(function() {
                    $form.find('.error-message').remove();
                }, 30000);
            }
        });
    });
    // Reset the form and remove error messages when the "Reset" button is clicked
    // $('#ast_mobile_detect_formHandler').on('reset', function() {
        $('#ast_mobile_detect_formHandler').find('.error-message').remove();
        $('#ast_mobile_detect_formHandler').find('button[type="submit"]').prop('disabled', false);
    // });
});