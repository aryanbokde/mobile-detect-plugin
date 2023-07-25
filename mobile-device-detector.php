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
    if (is_mobile_device()) {
    ?>

        <form id="ast_mobile_detect_formHandler">
            <div id="errors"></div>
            <div id="success"></div>
            <!-- Your form fields here -->
            <p><input type="text" name="name" placeholder="Name" id="name"></p>
            <p><input type="text" name="email" placeholder="Email" id="email"></p>
            <p><input type="number" name="mobile" placeholder="Mobile" id="mobile"></p>
            <!-- Add more fields as needed -->
            <button type="submit">Submit</button>

        </form>
    <?php } ?> 
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

    $to = $email;
    $subject = 'Testing mail from testing!';
    $message = 'This is a test email sent from WordPress using wp_mail.<a href="https://shop.kharadionline.com/wp-content/uploads/2023/01/popup-cutout.png" download>
    <img src="https://shop.kharadionline.com/wp-content/uploads/2023/01/popup-cutout.png" alt="testing">
  </a>';
    $blog_name = bloginfo( 'name' );
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: BlogName <noreply@gmail.com>',
    );

// Sending the email and checking for success
$is_email_sent = wp_mail( $to, $subject, $message, $headers );

if ( $is_email_sent ) {
    $errors['success'] = 'Email sent successfully!';
} else {
    $errors['errors'] = "Failed to send the email."; 
}



    if (!empty($errors)) {
        $response = array(
            'errors' => $errors,
        );
        wp_send_json_error($response);
    }


    // Process the data (you can add your custom logic here)
    // $table_name = $wpdb->prefix . 'custom_table';
    // $data = array(
    //     'name' => $name,
    //     'email' => $email,
    //     'mobile' => $mobile,
    // );
    // $wpdb->insert($table_name, $data);

    


    // Send response
    // $response = array(
    //     'message' => 'Form submitted successfully!',
    // );
    // wp_send_json_success($response);

}

