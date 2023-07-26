<?php
/**
 * Plugin Name: Mobile Device Detector
 * Description: Detects mobile devices and performs actions based on the detection.
 * Version: 1.0
 * Author: Your Name
 */
if (!defined('ABSPATH')) {
    die('401 Unauthorized: Access is denied');
}


// Activation hook
register_activation_hook(__FILE__, 'ast_mobile_detect_activate');

function ast_mobile_detect_activate() {
    // Any setup you want to do upon plugin activation
}


// Ast Mobile detect wordpress plugin script & stylesheet
function ast_mobile_detect_plugin_scripts() {
    wp_enqueue_style('ast_mobile_detect_plugin_css', plugin_dir_url(__FILE__) . 'css/style.css', array(), '1.0');
    wp_enqueue_script('ast-mobile-detect-plguin_js', plugin_dir_url(__FILE__) . 'js/custom-form.js', array('jquery'), '1.0', true);
    wp_localize_script('ast-mobile-detect-plguin_js', 'ast_mobile_detect_plugin_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'ast_mobile_detect_plugin_scripts');

// Ast Mobile detect wordpress plugin detect mobile
function is_mobile_device() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array('Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Windows Phone');
   
    foreach ($mobile_agents as $agent) {
        if (stripos($user_agent, $agent) !== false) {
            return true;
        }
    }

    return false;
}


// Ast Mobile detect wordpress plugin Shortcode html render
function ast_mobile_detect_plugin_shortcode() {
    ob_start(); 
    //if (is_mobile_device()) {
    ?>

        <form id="ast_mobile_detect_formHandler">
            <div id="ast-errors"></div>
            <div id="ast-success"></div>
            <!-- Your form fields here -->
            <p><input type="text" name="name" placeholder="Name" id="name"></p>
            <p><input type="text" name="email" placeholder="Email" id="email"></p>
            <p><input type="number" name="mobile" placeholder="Mobile" id="mobile"></p>
            <!-- Add more fields as needed -->
            <button type="submit">Submit</button>

        </form>
    <?php //} ?> 
    <?php return ob_get_clean();
}
add_shortcode('custom_form', 'ast_mobile_detect_plugin_shortcode');


// Ast Mobile detect wordpress plugin Ajax request handler
add_action('wp_ajax_ast_custom_form_submit', 'ast_custom_form_submit');
add_action('wp_ajax_nopriv_ast_custom_form_submit', 'ast_custom_form_submit');

function ast_custom_form_submit() {
    // Perform form validation and processing here
    // For example:
    global $wpdb;

    $errors = array();
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $mobile = absint($_POST['mobile']);

    // Perform form validation
    if (empty($_POST['name'])) {
        $errors['name'] = "Name Field is required."; 
    }
    if (!is_email($_POST['email'])) {
        $errors['email'] = 'Invalid email address.'; 
    }
    
    if (empty($_POST['email'])) {
        $errors['email'] = "Email Field is required."; 
    }
    
    if (empty($_POST['mobile'])) {
        $errors['mobile'] = "Mobile Field is required."; 
    }
    if (!is_valid_mobile_number($mobile)) {
        $errors['mobile'] = 'Invalid mobile number. Please enter a valid 10-digit number starting with 7, 8, or 9.';
    }

    //	
    $to = $email;
    $subject = "Download Link";
	$download_link = 'https://kharadionline.com/oxfam/wp-content/uploads/2023/07/3744.webp';


    if (!empty($errors)) {       

        $response = array(
            'errors' => $errors,
        );
        wp_send_json_error($response);

    }else{

        // // Register new user when form submited
        // ast_mobile_detect_woo_user_register($name, $email, $mobile);

        // // Add $attachments as the fifth parameter if you have attachments.
        // send_download_email($to, $subject, $download_link /*, $attachments*/ );

        //Send response
        $response = array(
            'message' => 'Form submitted successfully!',
        );
        wp_send_json_success($response);
    }

}


function send_download_email($to, $subject, $download_link /*, $file_path*/ ) {
    // Load the email template
    $email_template = file_get_contents(plugin_dir_path( __FILE__ ) . 'templates/email/download-template.html');

    // Replace the placeholder with the download link
    $email_content = str_replace('{DOWNLOAD_LINK}', $download_link, $email_template);
	
    // Set the email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
    );

    // Attach the file to the email
	//$attachments = array($file_path);

    // Send the email
    $sent = wp_mail($to, $subject, $email_content, $headers /*, $attachments*/);

    // Optionally, you can check if the email was sent successfully
    if ($sent) {
        // Email sent successfully
        return true;
    } else {
        // Failed to send email
        return false;
    }
}

function ast_mobile_detect_woo_user_register($name, $email, $mobile){

    // Check if the email address already exists
    $check_user_id = email_exists($email);
    
    if (!$check_user_id && class_exists('WooCommerce') ) {
         
        // Email address does not exist, create a new user
        // Generate a random password for the new user
        $random_password = wp_generate_password();

        // Create the new user
        $user_data = array(
            'user_login' => $email,
            'user_pass'  => $random_password,
            'user_email' => $email,
            'role'       => 'customer',
            'first_name' => $name,
        );
    
        $user_id  = wp_insert_user( $user_data ); 

        // Get an instance of the WC_Customer Object from user Id
        $customer = new WC_Customer( $user_id ); 

        $customer->set_billing_first_name( $name );
        $customer->set_billing_phone( $mobile );

        // Save data to database (add the user meta data)
        $customer->save(); 
        return true;

    } else {

        return false;

    }
}

function is_valid_mobile_number($mobile_number) {
    // Define the regular expression pattern for a valid mobile number.
    // For this example, I'll assume a simple 10-digit number starting with 7, 8, or 9.
    $pattern = '/^[6789]\d{9}$/';

    // Use preg_match to check if the mobile number matches the pattern.
    if (preg_match($pattern, $mobile_number)) {
        return true; // Valid mobile number.
    } else {
        return false; // Invalid mobile number.
    }
}