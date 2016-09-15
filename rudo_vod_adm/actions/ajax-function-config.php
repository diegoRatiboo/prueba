<?php 

//solamente usuarios logeados
add_action('wp_ajax_rudo_save_ing_config', 'ajax_rudo_save_ing_config');


function ajax_rudo_save_ing_config() {
 	
	$json=array();
	$data=array();

	if(!current_user_can('manage_options')){
 		$json["status"]='error';
    	$json["msj"]='No tienes permitida esta opción.';
        wp_send_json($json);
 	}

    $json["status"]='ok';
    $json["msj"]='Configuración ingresada.';
    wp_send_json($json);
}



