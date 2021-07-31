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
    #wp_register_script('prefix_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');
	wp_register_script('prefix_bootstrap', Files::get_rel_path(). '/assets/js/bootstrap/bootstrap.bundle.min.js');
    wp_enqueue_script('prefix_bootstrap');

    // CSS
	wp_register_style('prefix_bootstrap', Files::get_rel_path() . '/assets/css/bootstrap/bootstrap.min.css');
    #wp_register_style('prefix_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
    wp_enqueue_style('prefix_bootstrap');

	wp_register_style('cotizo', Files::get_rel_path() . '/assets/css/cotizo.css');
    wp_enqueue_style('cotizo');
}

add_action( 'wp_enqueue_scripts', 'enqueues');


// function that runs when shortcode is called
function wpb_demo_shortcode() {  
	global $formats;
	?>

	<script>
		function addNotice(message, type = 'info', id_container = 'alert_container', replace = false){
			let types = ['info', 'danger', 'warning', 'success'];

			if (jQuery.inArray(type, types) == -1){
				throw "Tipo de notificación inválida para " + type;
			}

			if (message === ""){
				throw "Mensaje de notificación no puede quedar vacio";
				return;
			}

			let alert_container  = document.getElementById(id_container);
		
			if (replace){
				alert_container.innerHTML = '';
			}

			let id_notice = "notice-" + (new Date().getTime()).toString();

			div = document.createElement('div');			
			div.innerHTML = `
			<div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
				<span>
					${message}
				</span>
				<button type="button" class="btn-close notice" data-bs-dismiss="alert" aria-label="Close" id="${id_notice}"></button>
			</div>`;

			alert_container.classList.add('mt-5');
			alert_container.prepend(div);
		}

		function hideNotice(id_container = 'alert_container'){
			let div  = document.querySelector(`div#${id_container}`);
			div.innerHTML = '';
			alert_container.classList.remove('mt-3');
		}

		let formats = <?php echo json_encode($formats); ?>

		let min_dim = 999999;
		let max_dim = 0;
		for (let i=0; i<formats.length; i++){
			let wxh = formats[i]['wxh'];
			let min_local = Math.min(wxh[0], wxh[1]);
			let max_local = Math.max(wxh[0], wxh[1]);
			
			if (min_local < min_dim){
				min_dim = min_local;
			}

			if (max_local > max_dim){
				max_dim = max_local;
			}
		}

		console.log(min_dim);
		console.log(max_dim);

		document.addEventListener('DOMContentLoaded', () => {
			let espesor_elem = document.getElementById('thickness');
			let color_elem = document.getElementById('color');
		
			espesor_elem.addEventListener("change", function() {
				if (espesor_elem.value == 0){
					espesor_elem.classList.remove('black')
					espesor_elem.classList.add('grey');
				} else {
					espesor_elem.classList.remove('grey')
					espesor_elem.classList.add('black');
				}
			}); 

			color_elem.addEventListener("change", function() {
				if (color_elem.value == 0){
					color_elem.classList.remove('black')
					color_elem.classList.add('grey');
				} else {
					color_elem.classList.remove('grey')
					color_elem.classList.add('black');
				}
			}); 
		}, false);
	</script>
	
	<?php
	$html = file_get_contents(__DIR__ . '/form.html'); 
	return $html;
} 


// register shortcode
add_shortcode('greeting', 'wpb_demo_shortcode');



