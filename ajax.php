<?php

use cotizo\libs\Url;
use cotizo\libs\Debug;

require __DIR__ . '/libs/Url.php';

function getPrice($wxh, $thickness, $color){
    global $formats;

    foreach ($formats as $format){
        if ($format['wxh'] == $wxh){
            $rows = $format[0];

            foreach ($rows as $row){
                if ($row['thickness'] == $thickness && $row['color'] == $color){
                    return $row['price'];
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
    $calculated_price = $price;  

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
	/wp-json/cotizo/v1/xxxxx
*/
add_action( 'rest_api_init', function () {	
	#	/wp-json/cotizo/v1/products
	register_rest_route( 'cotizo/v1', '/products', array(
		'methods' => 'POST',
		'callback' => 'create_product',
        'permission_callback' => '__return_true'
	) );
} );





