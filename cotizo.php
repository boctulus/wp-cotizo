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
require __DIR__ . '/ajax.php';


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
    #wp_register_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');
	wp_register_script('bootstrap', Files::get_rel_path(). 'assets/js/bootstrap/bootstrap.bundle.min.js');
    wp_enqueue_script('bootstrap');

	wp_register_style('bootstrap', Files::get_rel_path() . 'assets/css/bootstrap/bootstrap.min.css');
    #wp_register_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap');

	#wp_register_script('fontawesome', 'https://kit.fontawesome.com/813f54acc9.js');
	wp_register_script('fontawesome', Files::get_rel_path(). 'assets/js/fontawesome-5.js');
    wp_enqueue_script('fontawesome');

	wp_register_style('cotizo', Files::get_rel_path() . 'assets/css/cotizo.css');
    wp_enqueue_style('cotizo');
}

add_action( 'wp_enqueue_scripts', 'enqueues');


// function that runs when shortcode is called
function cotizo_shortcode() {  
	global $formats;
	global $abs_min_dim;

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

			let code = (new Date().getTime()).toString();
			let id_notice = "notice-" + code;
			let id_close  = "close-"  + code;

			div = document.createElement('div');			
			div.innerHTML = `
			<div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert" id="${id_notice}">
				<span>
					${message}
				</span>
				<button type="button" class="btn-close notice" data-bs-dismiss="alert" aria-label="Close" id="${id_close}"></button>
			</div>`;

			alert_container.classList.add('mt-5');
			alert_container.prepend(div);

			document.getElementById(id_close).addEventListener('click', () => {
				let cnt = document.querySelectorAll('button.btn-close.notice').length -1;
				if (cnt == 0){
					alert_container.classList.remove('mt-5');
					alert_container.classList.add('mt-3');
				}
			});


			return id_notice;
		}

		function hideNotice(id_container = 'alert_container', notice_id = null){
			if (notice_id == null){
				let div  = document.querySelector(`div#${id_container}`);
				div.innerHTML = '';
				alert_container.classList.remove('mt-5');
			} else {
				document.getElementById(notice_id).remove();
			}
		}

		/*
			Encuentra paneles con esas dimensiones de ancho y alto mínimas (intercambiables)
		*/
		function searchFormatsByWxH(min_d0, min_d1){
			let results = [];
			for (let i=0; i<formats.length; i++){
				let wxh = formats[i]['wxh'];

				if ((wxh[0] >= min_d0 && wxh[1] >= min_d1) || (wxh[0] >= min_d1 && wxh[1] >= min_d0)){
					results.push(formats[i]);
				}
			}

			return results;
		}

		/*
			searchThickness(searchFormatsByWxH(2000, 500))
			=>
			[3, 4, 5]
		*/
		function searchThickness(arr){
			let espesores = [];
			for (let i=0; i<arr.length; i++){
				let rows = arr[i][0];
				
				for (let j=0; j<rows.length; j++){
					espesor = rows[j]['thickness'];
					espesores.push(espesor);
				}
			}

			espesores = Object.keys(espesores.reduce((l, r) => l[r] = l, {})).sort()
			return espesores;
		}

		/*
			ssearchFormatsByWxHxT(500, 500, 3)
			=>
			Array de formatos => color, precio,...
		*/
		function searchFormatsByWxHxT(min_d0, min_d1, thickness){
			let results = [];
			for (let i=0; i<formats.length; i++){
				let wxh = formats[i]['wxh'];

				if ((wxh[0] >= min_d0 && wxh[1] >= min_d1) || (wxh[0] >= min_d1 && wxh[1] >= min_d0)){
					let rows = formats[i][0];
						
					for (let j=0; j<rows.length; j++){
						let espesor = rows[j]['thickness'];
						if (espesor == thickness){
							let row = rows[j];
							row['wxh']  = wxh;
							results.push(row);
						}
					}	
				}
			}

			return results;
		}

		// General
		function setDropdownOptions(select_elem, options, default_option){
			select_elem.innerHTML = '';

			let opt = new Option(default_option['text'], default_option['value']);
			opt.setAttribute('selected', true);
			select_elem.appendChild(opt);

			if (typeof options == 'undefined' || options == null || options.length == 0){
				select_elem.disabled = true;
				return;
			} else {
				select_elem.disabled = false;
			}

			for (let i=0; i<options.length; i++){
				let opt = new Option(options[i]['text'], options[i]['value']);
				select_elem.appendChild(opt);
			}
		}

		function setThicknessOptions(values){
			if (typeof values == 'undefined'){
				return;
			}

			let options = [];
			for (let i=0; i<values.length; i++){
				options.push({'text': `${values[i]} mm`, 'value': values[i]});
			}

			setDropdownOptions(espesor_elem, options, {'text': 'Espesor (mm)', 'value': ''});
		}

		function run_step1()
		{
			let largo = largo_elem.value == '' ? 0 : parseInt(largo_elem.value);
			let ancho = ancho_elem.value == '' ? 0 : parseInt(ancho_elem.value);

			if (largo >max_dim || ancho >max_dim){
				addNotice(`Ninguna dimensión puede superar los ${max_dim} cm`, 'warning', 'alert_container', true);
				clearFields();
				return;
			} 

			if (largo <abs_min_dim || ancho <abs_min_dim){
				clearFields();
				return;
			} 

			let _formats = searchFormatsByWxH(largo, ancho);

			if (_formats.length == 0){
				addNotice(`Las dimensiones están fuera de rango.`, null, 'alert_container', true);
				clearFields();
				return;
			}

			let espesores = searchThickness(_formats);
			setThicknessOptions(espesores);
			
			hideNotice();
		}

		function run_step2(){
			let largo = largo_elem.value == '' ? 0 : parseInt(largo_elem.value);
			let ancho = ancho_elem.value == '' ? 0 : parseInt(ancho_elem.value);
			let espesor = espesor_elem.value == '' ? 0 : parseInt(espesor_elem.value);

			let data = searchFormatsByWxHxT(largo, ancho, espesor);

			/*
				Ordeno por espesor, color y finalmente precio
			*/
			data.sort( function (a, b) {
				if (parseInt(a.thickness) > parseInt(b.thickness)) {
					return 1;
				} else if (parseInt(a.thickness) < parseInt(b.thickness)){
					return -1;
				} else {
					if (a.color > b.color) {
						return 1;
					} else if (a.color < b.color) {
						return -1;
					} else {
						if (a.price > b.price){
							return 1;
						} else if (a.price < b.price) {
							return -1;
						} else {
							return 0;
						}
					}
				}
			});

			/*
				Me quedo con el más barato para los mismos atributos (espesor y color)    
			*/

			let items  = [];
			let prev = [null, null, null];

			for (var i=0; i<data.length; i++){
				let c = [ data[i].thickness.toString(), data[i].color, data[i].price ];

				if (c[0] == prev[0] && c[1] == prev[1]){
					//console.log('Escaping', c);
					continue;
				}

				items.push(data[i]);
				prev = c;
			}

			options = [];
			for (var i=0; i<items.length; i++)
			{
				let wxh   = items[i]['wxh'][0] + '-' + items[i]['wxh'][1];

				/*
					Idealmente definir "extra" junto con text y value y allí enviar data-*
				*/
				let value = `${wxh}-${items[i]['thickness']}-${items[i]['color']}-${items[i]['price']}`;

				options.push({
					'text': items[i]['color'],
					'value': value
				});
			}

			setDropdownOptions(color_elem, options, {'text': 'Color', 'value': null});
		}

		function parseJSON(response) {
			return response.text().then(function(text) {
				return text ? JSON.parse(text) : {}
			})
		}

		/*
			 Main
		*/

		let largo_elem;
		let ancho_elem;
		let espesor_elem;
		let color_elem;
		let price_elem;
		let subtotal_elem;

		let price;
		let subtotal;

		const getValue = (elem, $default = null) => {
			if ($default != null && (typeof elem == 'undefined' || elem.value == '')){
				return $default;
			}
			return parseInt(elem.value);
		}

		const getLargo = ($default = null) => {
			if (typeof largo_elem == 'undefined'){
				return $default;
			}
			return parseInt(largo_elem.value);
		}

		const getAncho = () => {
			if (typeof ancho_elem == 'undefined'){
				return $default;
			}
			return parseInt(ancho_elem.value);
		}

		const getEspesor = () => {
			if (typeof espesor_elem == 'undefined'){
				return $default;
			}
			return parseInt(espesor_elem.value);
		}

		const clearEspesor = () => {
			setDropdownOptions(espesor_elem, null, {'text': 'Espesor (mm)', 'value': ''});
			espesor_elem.classList.remove('black')
			espesor_elem.classList.add('grey');
		}

		const clearColor = () => {
			setDropdownOptions(color_elem, null, {'text': 'Color', 'value': ''});
			color_elem.classList.remove('black')
			color_elem.classList.add('grey')
		}

		const clearPrice = () => {
			price = 0;
			price_elem.value = '';
		}

		const clearSubTotal = () => {
			subtotal = 0;
			subtotal_elem.value = '';
		}

		const clearFields = () => {
			clearEspesor();			
			clearColor();
			clearPrice();
			clearSubTotal();
		};

		const setPrice = (value) => {		
			_v = value.toString();

			switch (_v){
				case 'NaN':
					v = '';
				break;
				default:
					v = _v;
			}

			price = value;
			price_elem.value = '$ ' + v;
		}

		const setSubTotal = (value) => {		
			_v = value.toString();

			switch (_v){
				case 'NaN':
					v = '';
				break;
				default:
					v = _v;
			}

			subtotal = value;
			subtotal_elem.value = '$ ' + v;
		}



		document.addEventListener('DOMContentLoaded', () => {
			largo_elem = document.getElementById('cotizo_length');
			ancho_elem = document.getElementById('cotizo_width');
			espesor_elem = document.getElementById('cotizo_thickness');
			color_elem = document.getElementById('cotizo_color');
			price_elem = document.getElementById('cotizo_price');
			subtotal_elem = document.getElementById('cotizo_subtotal')
		
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

			largo_elem.addEventListener("change", function() {
				if (getLargo(0) <abs_min_dim){
					addNotice(`El largo mínimo es de ${abs_min_dim} cm`, 'warning', 'alert_container', true);
					clearFields();
					return;
				} 

				run_step1();
			}); 

			ancho_elem.addEventListener("change", function() {
				if (getAncho(0) <abs_min_dim){
					addNotice(`El ancho mínimo es de ${abs_min_dim} cm`, 'warning', 'alert_container', true);
					clearFields();
					return;
				} 

				run_step1();
			});

			espesor_elem.addEventListener("change", function() {
				run_step2();
			});

			color_elem.addEventListener("change", function() {
				// innecesario si usara data-*
				let f = color_elem.value.split('-');

				let w = f[0];
				let h = f[1];
				let thickness = f[2];
				let color = f[3];
				
				price = f[4];				

				setPrice(price);
				setSubTotal(price * getValue(cotizo_cant_elem, 1));

				/*
					Creo el producto
				*/

				let url = '/wp-json/cotizo/v1/products'; 

				let wxh = [w,h];
				let cut = [getLargo(), getAncho()];

				let data = JSON.stringify({ wxh: wxh, thickness: thickness, color: color, cut: cut });

				var settings = {
				"url": url,
				"method": "POST",
				"timeout": 0,
				"headers": {
					"Content-Type": "text/plain"
				},
					"data": data,
				};

				jQuery.ajax(settings)
				.done(function (response) {
					console.log(response);
				})
				.fail(function (jqXHR, textStatus) {
					//console.log(jqXHR);
					//console.log(textStatus);
					addNotice('Error desconocido', 'danger', 'warning', 'alert_container', true);
				});
			});		

			let cotizo_cant_elem = document.getElementById('cotizo_cant');
			let cotizo_cant_up_elem = document.getElementById('cotizo_cant_up');
			let cotizo_cant_down_elem = document.getElementById('cotizo_cant_down');

			cotizo_cant_up_elem.addEventListener('click', () => {				
				let val = getValue(cotizo_cant_elem, 0);
				val++;
				cotizo_cant_elem.value = val;
				cotizo_cant_down_elem.disabled = false;
				setSubTotal(price * val);
			});
			
			cotizo_cant_down_elem.addEventListener('click', () => {					
				let val = getValue(cotizo_cant_elem, 0);

				if (val == 1){
					cotizo_cant_down_elem.disabled = true;
					return;
				}

				val--;
				cotizo_cant_elem.value = val;
				setSubTotal(price * val);
			});


			document.getElementById("cotizo_form").addEventListener('submit', function(event){
				console.log('Voy a agregar al carrito. Tranqui.')
				event.preventDefault();
				return;
			});

		}, false);


		let formats = <?php echo json_encode($formats); ?>;
		let abs_min_dim = <?php echo $abs_min_dim; ?>;
		
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
	</script>
	
	<?php
	$html = file_get_contents(__DIR__ . '/form.html'); 
	return $html;
} 


// register shortcode
add_shortcode('cotizo', 'cotizo_shortcode');



