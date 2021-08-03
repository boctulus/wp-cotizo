<?php

use cotizo\libs\Url;
use cotizo\libs\Debug;

require __DIR__ . '/libs/Url.php';


/**
 * Invoke class private method
 *
 * @since   0.1.0
 *
 * @param   string $class_name
 * @param   string $methodName
 *
 * @return  mixed
 */
function woo_hack_invoke_private_method( $class_name, $methodName ) {
    if ( version_compare( phpversion(), '5.3', '<' ) ) {
        throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
    }
 
    $args = func_get_args();
    unset( $args[0], $args[1] );
    $reflection = new ReflectionClass( $class_name );
    $method = $reflection->getMethod( $methodName );
    $method->setAccessible( true );
 
    $args = array_merge( array( $class_name ), $args );
    return call_user_func_array( array( $method, 'invoke' ), $args );
}

function woocommerce_maybe_add_multiple_products_to_cart( $url = false ) {
    // Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
    if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
        return;
    }
 
    // Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
    remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
 
    $product_ids = explode( ',', $_REQUEST['add-to-cart'] );
    $count       = count( $product_ids );
    $number      = 0;
 
    foreach ( $product_ids as $id_and_quantity ) {
        // Check for quantities defined in curie notation (<product_id>:<product_quantity>)
        // https://dsgnwrks.pro/snippets/woocommerce-allow-adding-multiple-products-to-the-cart-via-the-add-to-cart-query-string/#comment-12236
        $id_and_quantity = explode( ':', $id_and_quantity );
        $product_id = $id_and_quantity[0];
 
        $_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;
 
        if ( ++$number === $count ) {
            // Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
            $_REQUEST['add-to-cart'] = $product_id;
 
            return WC_Form_Handler::add_to_cart_action( $url );
        }
 
        $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
        $was_added_to_cart = false;
        $adding_to_cart    = wc_get_product( $product_id );
 
        if ( ! $adding_to_cart ) {
            continue;
        }
 
        $add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );
 
        // Variable product handling
        if ( 'variable' === $add_to_cart_handler ) {
            woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );
 
        // Grouped Products
        } elseif ( 'grouped' === $add_to_cart_handler ) {
            woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );
 
        // Custom Handler
        } elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
            do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );
 
        // Simple Products
        } else {
            woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_simple', $product_id );
        }
    }
}
 
// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action( 'wp_loaded', 'woocommerce_maybe_add_multiple_products_to_cart', 15 );


##############################################################################################################


function getPrice($wxh, $thickness, $color){
    global $formats;

    normalize($formats);

    foreach ($formats as $format){
        if ($format['wxh'] == $wxh){
            $rows = $format[0];

            foreach ($rows as $row){
                if ($row['thickness'] == $thickness && $row['color'] == $color){
                    return $row['price_normalized'];
                }
            }
             
        }
    }
}

/*
	REST

*/

function create_product($req)
{
    $data = $req->get_body();

    if ($data === null){
        throw new \Exception("Body está vacio");
    }

    $data = json_decode($data, true);

    if ($data === null){
        throw new \Exception("Invalid JSON");
    }

    $wxh = $data['wxh'];
    $thickness = $data['thickness'];
    $color = $data['color'];
    $cut = $data['cut'];

    $cut_w = $cut[0];
    $cut_h = $cut[1];


    /*
        Por seguridad el precio se determina en el backend
    */

    $price = getPrice($wxh, $thickness, $color);
    
    if ($price === null){
        $res = new WP_REST_Response("El precio final no pudo determinarse");
        $res->set_status(404);
        return;
    }

    // Acá iría la fórmula
    $calculated_price = $price * $cut_h * $cut_w;  

    $color_lo = strtolower($color);

	//add them in an array
    $post = array(
        'post_title' => "Acrílico {$cut_h}CMx{$cut_w}CMx{$thickness}MM - ". $color_lo,
        'post_status' => "publish",
        'post_content' => "Panel acrílico de color $color_lo de {$cut_h} cm x {$cut_w} cm y $thickness mm de espesor",
        'post_type' => "product",
    );

    //create product
    $product_id = wp_insert_post( $post, __('Cannot create product', 'bones') );

    //type of product
    wp_set_object_terms($product_id, 'simple', 'product_type');

    //add price to the product, this is where you can add some descriptions such as sku's and measurements
    update_post_meta( $product_id, '_regular_price', $calculated_price );
    update_post_meta( $product_id, '_sale_price', $calculated_price );
    update_post_meta( $product_id, '_price', $calculated_price );

    // custom field => not working!
    update_post_meta( $product_id, 'wxh', $wxh );

    $res = [
        'price'      => $calculated_price,
        'product_id' => $product_id
    ];

    $res = new WP_REST_Response($res);
    $res->set_status(201);

    return $res;
}

/*
    Temporal,

    luego debe haber en un solo endpoint para poder destruir el producto luego de agregarlo 
    (que no queden productos olvidados)

*/
function add_to_cart($req){
    $data = $req->get_body();

    if ($data === null){
        throw new \Exception("Body está vacio");
    }

    $data = json_decode($data, true);

    if ($data === null){
        throw new \Exception("Invalid JSON");
    }

    $product_id = $data['product_id'] ?? null;
    $quantity   = $data['qty'] ?? 1;

    WC()->frontend_includes();

    if ( null === WC()->session ) {
        $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
    
        WC()->session = new $session_class();
        WC()->session->init();
    }
    
    if ( null === WC()->customer ) {
        WC()->customer = new WC_Customer( get_current_user_id(), true );
    }

    $cart_hash = null;
    if ( null === WC()->cart ) {
        WC()->cart = new WC_Cart();
        $cart_hash = WC()->cart->add_to_cart($product_id, $quantity);
    }    

    return [
        'cart_hash' => $cart_hash
    ];
}

function add_to_cart_test($req){
    $data = $req->get_body();

    if ($data === null){
        throw new \Exception("Body está vacio");
    }

    $data = json_decode($data, true);

    if ($data === null){
        throw new \Exception("Invalid JSON");
    }

    $product_id = $data['product_id'] ?? null;
    $quantity   = $data['qty'] ?? 1;

    $url = esc_url_raw( add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() ) );
    
    return [
        'url' => $url
    ];
}

/*
	/wp-json/cotizo/v1/xxxxx
*/
add_action( 'rest_api_init', function () {	
	#	/wp-json/cotizo/v1/products
	register_rest_route( 'cotizo/v1', '/products', array(
		'methods' => 'POST',
		'callback' => 'create_product',
        'permission_callback' => '__return_true'
	) );

    #	/wp-json/cotizo/v1/cart
	register_rest_route( 'cotizo/v1', '/cart', array(
		'methods' => 'POST',
		'callback' => 'add_to_cart',
        'permission_callback' => '__return_true'
	) );

    #	/wp-json/cotizo/v1/test
	register_rest_route( 'cotizo/v1', '/test', array(
		'methods' => 'POST',
		'callback' => 'add_to_cart_test',
        'permission_callback' => '__return_true'
	) );
} );





