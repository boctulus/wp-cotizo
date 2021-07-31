<?php
/*
Plugin Name: Wp Cotizo
Description: Cotizador para WooCommerce
Version: 1.0.0
Author: boctulus@gmail.com <Pablo>
*/

use cotizo\libs\Debug;
use cotizo\libs\Files;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/libs/Debug.php';
require __DIR__ . '/libs/Files.php';
require __DIR__ . '/config.php';


if (!function_exists('dd')){
	function dd($val, $msg = null, $pre_cond = null){
		Debug::dd($val, $msg, $pre_cond);
	}
}

/**
 * Check if WooCommerce is active
 */
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}	

/*
add_action('init', 'start_session', 1);

function start_session() {
	if(!session_id()) {
		session_start();
	}
}
*/

function enqueues() 
{  
	// JS
    wp_register_script('prefix_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');
	#wp_register_script('prefix_bootstrap', Files::get_rel_path(). '/assets/js/bootstrap.bundle.min.js');
    wp_enqueue_script('prefix_bootstrap');

    // CSS
	#wp_register_style('prefix_bootstrap', Files::get_rel_path() . '/assets/css/bootstrap.min.css');
    wp_register_style('prefix_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
    wp_enqueue_style('prefix_bootstrap');

	wp_register_style('prefix_bootstrap', Files::get_rel_path() . '/assets/css/cotizo.css');
    wp_enqueue_style('prefix_bootstrap');
}

add_action( 'wp_enqueue_scripts', 'enqueues');


// function that runs when shortcode is called
function wpb_demo_shortcode() {  
	$html = file_get_contents(__DIR__ . '/form.html'); 
	return $html;
} 


// register shortcode
add_shortcode('greeting', 'wpb_demo_shortcode');



