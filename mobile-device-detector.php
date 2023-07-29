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
	
    $site_key = get_option('custom_recaptcha_site_key');
    wp_enqueue_style('ast_mobile_detect_plugin-style', plugin_dir_url(__FILE__) . 'css/style.css', array(), time());
	
    wp_enqueue_script('ast-mobile-detect-plguin_js', plugin_dir_url(__FILE__) . 'js/custom-form.js', array('jquery'), time(), true);
	wp_localize_script('ast-mobile-detect-plguin_js', 'ast_ajax_object', array(
			'ajax_url' => admin_url('admin-ajax.php'),
	));
	
	if (!empty($site_key)) {
    	wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?render='.esc_attr($site_key), [], time(), true);
	}   
	
}
add_action('wp_enqueue_scripts', 'ast_mobile_detect_plugin_scripts');


function ast_mobile_detect_footer_custom_script() {
	$site_key = get_option('custom_recaptcha_site_key');
	if(!empty($site_key)){ ?>
		<script>
			jQuery(document).ready(function () {
				const siteKey = '<?php echo $site_key ?>';

				// Load ReCAPTCHA
				grecaptcha.ready(function () {
					// Request the token
					grecaptcha.execute(siteKey, { action: 'submit' }).then(function (token) {
						// Append the token to your form
						$('.g-recaptcha').append('<input type="hidden" name="recaptcha_response" value="' + token + '">');
					});
				});
			});
		</script>
	<?php }
}
add_action('wp_footer', 'ast_mobile_detect_footer_custom_script');



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
            <p><input type="text" name="mobile" placeholder="Mobile" id="mobile"></p>
			<p><input type="hidden" name="ast_nonce" id="ast-custom-nonce" value="<?php echo wp_create_nonce( 'ast-custom-nonce-action' ); ?>"></p>
			<div class="g-recaptcha" id="recaptcha"></div>           
            <!-- Add more fields as needed -->
            <button type="submit">Submit</button>

        </form>
    <?php //} ?> 
    <?php return ob_get_clean();
}
add_shortcode('ast_custom_form', 'ast_mobile_detect_plugin_shortcode');


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
   	$response = $_POST['recaptcha_response'];  
	
	if ( ! isset( $_POST['ast_nonce'] ) || ! wp_verify_nonce( $_POST['ast_nonce'], 'ast-custom-nonce-action' ) ) {
        $errors['ast-custom-nonce'] = 'WP_Nonce Invalid!';
    }  	
	
	if (get_recaptcha_response($response)) {
	}else{
		$errors['recaptcha'] = 'reCAPTCHA validation failed.';
	}		
	
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

        // Register new user when form submited
        ast_mobile_detect_woo_user_register($name, $email, $mobile);

        // Add $attachments as the fifth parameter if you have attachments.
        send_download_email($to, $subject, $download_link /*, $attachments*/ );

        //Send response
        $response = array(
            'message' => 'Form submitted successfully!',
        );
        wp_send_json_success($response);
    }

}

function get_recaptcha_response($response) {
	

	$secret_key = get_option('custom_recaptcha_secret_key');
	
	if($response){
		
		$remote_ip = $_SERVER['REMOTE_ADDR'];
		// Make a POST request to Google reCAPTCHA API
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => $secret_key,
			'response' => $response,
			'remoteip' => $remote_ip
		);
		$options = array(
			'http' => array(
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'method' => 'POST',
				'content' => http_build_query($data),
			),
		);

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		$result_json = json_decode($result);	
		// Return true or false based on the condition
		return $result_json->success ? true : false;
		
	}else{
		return true;
	}
	
	
// 	if( !empty($response) ){	
// 		$recaptcha_secret = esc_attr($secret_key);
// 		$recaptcha_response = $response;
// 		$remote_ip = $_SERVER['REMOTE_ADDR'];

// 		// Make a POST request to Google reCAPTCHA API
// 		$url = 'https://www.google.com/recaptcha/api/siteverify';
// 		$data = array(
// 			'secret' => $recaptcha_secret,
// 			'response' => $recaptcha_response,
// 			'remoteip' => $remote_ip
// 		);

// 		$options = array(
// 			'http' => array(
// 				'header' => 'Content-type: application/x-www-form-urlencoded',
// 				'method' => 'POST',
// 				'content' => http_build_query($data),
// 			),
// 		);

// 		$context = stream_context_create($options);
// 		$result = file_get_contents($url, false, $context);
// 		$result_json = json_decode($result);	
// 		// Return true or false based on the condition
// 		return $result_json->success ? true : false;
// 	}
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





// Add admin menu
add_action('admin_menu', 'custom_recaptcha_plugin_add_admin_menu');
function custom_recaptcha_plugin_add_admin_menu() {
    add_options_page('Mobile reCAPTCHA Settings', 'Mobile reCAPTCHA', 'manage_options', 'mobile_recaptcha_settings', 'custom_recaptcha_plugin_settings_page');
}

// Settings page content
function custom_recaptcha_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ast reCAPTCHA Settings</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('custom_recaptcha_options');
                do_settings_sections('custom_recaptcha_settings');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register and add settings
add_action('admin_init', 'ast_custom_recaptcha_plugin_settings_init');
function ast_custom_recaptcha_plugin_settings_init() {
    register_setting('custom_recaptcha_options', 'custom_recaptcha_site_key');
    register_setting('custom_recaptcha_options', 'custom_recaptcha_secret_key');

    add_settings_section('custom_recaptcha_section', 'reCAPTCHA Settings', 'custom_recaptcha_section_callback', 'custom_recaptcha_settings');

    add_settings_field('custom_recaptcha_site_key', 'Site Key', 'custom_recaptcha_site_key_render', 'custom_recaptcha_settings', 'custom_recaptcha_section');
    add_settings_field('custom_recaptcha_secret_key', 'Secret Key', 'custom_recaptcha_secret_key_render', 'custom_recaptcha_settings', 'custom_recaptcha_section');
}

// Section description
function custom_recaptcha_section_callback() {
    echo '<p>Please enter your Google reCAPTCHA site key and secret key below:</p>';
}

// Field render functions
function custom_recaptcha_site_key_render() {
    $site_key = get_option('custom_recaptcha_site_key');
    echo '<input type="text" name="custom_recaptcha_site_key" value="' . esc_attr($site_key) . '" />';
}

function custom_recaptcha_secret_key_render() {
    $secret_key = get_option('custom_recaptcha_secret_key');
    echo '<input type="text" name="custom_recaptcha_secret_key" value="' . esc_attr($secret_key) . '" />';
}

// Save the keys as options
add_action('admin_init', 'custom_recaptcha_plugin_save_keys');
function custom_recaptcha_plugin_save_keys() {
    if (isset($_POST['custom_recaptcha_site_key'])) {
        update_option('custom_recaptcha_site_key', sanitize_text_field($_POST['custom_recaptcha_site_key']));
    }

    if (isset($_POST['custom_recaptcha_secret_key'])) {
        update_option('custom_recaptcha_secret_key', sanitize_text_field($_POST['custom_recaptcha_secret_key']));
    }
}

// Display the reCAPTCHA on your forms
function custom_recaptcha_display() {
    $site_key = get_option('custom_recaptcha_site_key');

    if (!empty($site_key)) {
        echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($site_key) . '"></script>';
    }
}
