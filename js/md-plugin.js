jQuery(document).ready(function ($) {
    $('#md_plugin_formHandler').on('submit', function (e) {
        e.preventDefault();
        $('#md_plugin_formHandler').find('.error-message').remove();
        var $form = $(this);
        var formData = $form.serialize();
        $form.find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: md_plugin_ajax_object.ajax_url,
            data: formData + '&action=md_plugin_form_submission_validator',
            success: function (response) {
                // Do any additional handling of the response data here
                if (response.success === true) {
                    var elementID = 'ast-success';
                    // Append the message to the element with the specified ID
                    $('#' + elementID).append("<p class='ast-success'>" + response.data.message + "</p>");

                    // Clear form inputs and enable the submit button
                    $form[0].reset();
                    // Enable the submit button after form submission
                    $form.find('button[type="submit"]').prop('disabled', false);
                    // Automatically reset the form after 3 seconds
                    setTimeout(function () {
                        $form.find('.error-message').remove();
                        $('.ast-success').remove();
                    }, 3000);

                } else {
                    if (response.data.errors) {
                        $.each(response.data.errors, function (field, message) {
                            $('#' + field).after('<span class="error-message">' + message + '</span>');
                        });
                    }
                    // Enable the submit button after form submission
                    $form.find('button[type="submit"]').prop('disabled', false);
                    // Automatically reset the form after 3 seconds
                    setTimeout(function () {
                        $form.find('.error-message').remove();
                    }, 3000);

                }
            },
            error: function (xhr, textStatus, errorThrown) {
                console.log('Error: ' + xhr.responseText);
                $form.find('button[type="submit"]').prop('disabled', false);
                setTimeout(function () {
                    $form.find('.error-message').remove();
                }, 3000);
            }
        });

    });
    // Reset the form and remove error messages when the "Reset" button is clicked
    // $('#md_plugin_formHandler').on('reset', function() {
    $('#md_plugin_formHandler').find('.error-message').remove();
    $('#md_plugin_formHandler').find('button[type="submit"]').prop('disabled', false);
    // });
});

