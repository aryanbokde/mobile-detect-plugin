<?php
/**
 * Plugin Name: Mobile Device Detector2
 * Description: Detects mobile devices and performs actions based on the detection.
 * Version: 1.0
 * Author: Your Name
 */

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


function enqueue_styles_based_on_device() {
    if (is_mobile_device()) {
        // Load the mobile stylesheet
        wp_enqueue_style('mobile-style', plugins_url('css/mobile-style.css', __FILE__));
    } else {
        // Load the regular stylesheet
        wp_enqueue_style('regular-style', plugins_url('css/style.css', __FILE__));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_styles_based_on_device');


// function custom_plugin_init() {
//     // Your plugin code here
    
// }
// add_action('init', 'custom_plugin_init');

add_shortcode('booking2', 'display_mobile_detected_form');

function display_mobile_detected_form(){
    ob_start();
    if (is_mobile_device()) {
        // Display the form HTML for mobile devices
        echo '<form action="#" method="post" id="mobile-detect-form">
                  <!-- Your form fields go here -->
                  <input type="text" name="name" placeholder="Name">
                  <input type="email" name="email" placeholder="Email">
                  <textarea name="message" placeholder="Message"></textarea>
                  <button type="submit" id="submit-handler">Submit</button>
              </form>';
    }
    return ob_get_clean();
}

function add_jquery() {
    wp_enqueue_script( 'jquery' );
    if (!is_admin()) {
        // comment out the next two lines to load the local copy of jQuery
        wp_deregister_script('jquery'); 
        wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js', false, '1.8.3'); 
        wp_enqueue_script('jquery');
    }
}    

add_action('init', 'add_jquery');

// Enqueue necessary scripts for AJAX
function enqueue_ajax_scripts() {
    wp_enqueue_script('ajax-script', plugin_dir_url(__FILE__) . 'js/ajax-script.js', array('jquery'), '1.0', true);
    wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_scripts');


// AJAX callback function to handle form submission
add_action('wp_ajax_submit_form_data', 'submit_form_data');
add_action('wp_ajax_nopriv_submit_form_data', 'submit_form_data');
function submit_form_data() {
    global $wpdb;

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);
    $created_at = current_time('mysql');

    $table_name = $wpdb->prefix . 'custom_table';
    $data = array(
        'name' => $name,
        'email' => $email,
        'mobile' => $message,
    );

    // $wpdb->insert($table_name, $data);

    // Return a success message (you can modify this as needed)
    echo 'Form data submitted successfully!';
    wp_die();
}