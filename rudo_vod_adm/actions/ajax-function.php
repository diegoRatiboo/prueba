<?php 

//solamente usuarios logeados
add_action('wp_ajax_rudo_upload_vod', 'ajax_rudo_upload_vod');
add_action('wp_ajax_rudo_load_more_vod', 'ajax_rudo_load_more_vod');
add_action('wp_ajax_rudo_associate_vod_post', 'ajax_rudo_associate_vod_post');

function ajax_rudo_upload_vod(){

	@ini_set( 'upload_max_size' , '1000M' );
	@ini_set( 'post_max_size', '1000M');


	$json=array();
	$data=array();

	if(!current_user_can('publish_pages')){
 		$json["status"]='error';
    	$json["msj"]='No tienes permitida esta opción.';
        wp_send_json($json);
 	}

	//INSTANCIAMOS MODELO DE PETICIONES CURL
    $peticiones_Curl = new Peticiones_Curl();
    //FIN INSTANCIA ------------------------

	$rudo_plataforma='';
	if( !isset($_POST["rudo_plataforma"]) or 
			( $_POST["rudo_plataforma"]!='yt' and $_POST["rudo_plataforma"]!='down_yt' and 
				$_POST["rudo_plataforma"]!='up_file' and $_POST["rudo_plataforma"]!='down_url' and 
					$_POST["rudo_plataforma"]!='down_fb' and $_POST["rudo_plataforma"]!='down_tw' ) ){

		$json["status"]='ok';
		$json["msj"]='Opcion de subida incorrecta.';
		wp_send_json($json);
	}
	$rudo_plataforma=(string)$_POST["rudo_plataforma"];

	$imagen='';
	$imagen_ext='';
	$archivo='';
	$archivo_ext='';
	$rudo_type='v';
	switch ($rudo_plataforma) {
		case 'yt':
			$data["plataforma"]='url_yt';
			$data["url_file"]=filter_var($_POST["rudo_urlYoutube"], FILTER_SANITIZE_URL);
			break;
		case 'down_yt':
			$data["plataforma"]='yt';
			$data["url_file"]=filter_var($_POST["rudo_urlYoutube"], FILTER_SANITIZE_URL);
			break;
		case 'down_fb':
			$data["plataforma"]='fb';
			$data["url_file"]=filter_var($_POST["rudo_urlTwFb"], FILTER_SANITIZE_URL);
			break;
		case 'down_url':
			$data["plataforma"]='url';
			$data["url_file"]=filter_var($_POST["rudo_urlDown"], FILTER_SANITIZE_URL);
			break;
		case 'down_tw':
			$data["plataforma"]='tw';
			$data["url_file"]=filter_var($_POST["rudo_urlTwFb"], FILTER_SANITIZE_URL);
			break;
		case 'up_file':
			
			if( isset($_FILES["rudo_image_vod"]["name"]) and !empty($_FILES["rudo_image_vod"]["name"]) ){
				if( floatval(phpversion())>=5.5 ){
					$imagen = new CURLFile($_FILES["rudo_image_vod"]["tmp_name"], $_FILES["rudo_image_vod"]["type"],$_FILES["rudo_image_vod"]["name"]);
				}
				else{
					$imagen='@'.$_FILES["rudo_image_vod"]["tmp_name"];
				}
			    $imagen_ext=pathinfo($_FILES["rudo_image_vod"]["name"], PATHINFO_EXTENSION );
			}

			
			if( isset($_FILES["rudo_file_vod"]["name"]) and !empty($_FILES["rudo_file_vod"]["name"]) ){
				if( floatval(phpversion())>=5.5 ){
					$archivo = new CURLFile($_FILES["rudo_file_vod"]["tmp_name"], $_FILES["rudo_file_vod"]["type"],$_FILES["rudo_file_vod"]["name"]);
				}
				else{
			    	$archivo='@'.$_FILES["rudo_file_vod"]["tmp_name"];
			    }
			    $archivo_ext=pathinfo($_FILES["rudo_file_vod"]["name"], PATHINFO_EXTENSION );
			}
			$data["image"]=$imagen;
			$data["image_ext"]=$imagen_ext;

			$rudo_type='v';
			if( isset($_POST["rudo_type_file"]) and $_POST["rudo_type_file"]=='a'){
				$rudo_type='a';
			}
			break;
	}

	
	

	$data["title"]=(string)$_POST["rudo_title_vod"];//maximo 200
	$data["type"]=$rudo_type; 
	$data["description"]=$_POST["rudo_description_vod"];
	$data["cats"]=abs($_POST["rudo_folder_vod"]);
	$data["tags"]=(string)$_POST["tags_video"];
	$data["file_name"]=$archivo;
	$data["file_name_ext"]=$archivo_ext;

	$url_post=$GLOBALS['RUDO_VOD_API'].'/public_files/wp_api.php?action=upload';
	$result_post=$peticiones_Curl->post_resource( $url_post,  $data );
	if($result_post==false or $result_post->status=='error'){
		$json["status"]='error';
		$json["msj"]=( isset($result_post->msj) )?$result_post->msj: 'Error al enviar el formulario.';
		wp_send_json($json);
	}

	$json["status"]='ok';
	$json["msj"]='El archivo fue subido a rudo.';
	wp_send_json($json);
}

function ajax_rudo_load_more_vod() {
 	
 	$json=array();
	$data=array();

 	if(!current_user_can('publish_pages')){
 		$json["status"]='error';
    	$json["msj"]='No tienes permitida esta opción.';
        wp_send_json($json);
 	}

 	//INSTANCIAMOS MODELO DE PETICIONES CURL
    $peticiones_Curl = new Peticiones_Curl();
    //FIN INSTANCIA ------------------------

	
	$page_number=1;
	$limit=10;
	$search='';
	if( isset($_POST["rudo_page"]) and is_numeric($_POST["rudo_page"]) and $_POST["rudo_page"]>=0 ){
		$page_number=(int)$_POST["rudo_page"];
	}
	if( isset($_POST["rudo_search"]) and !empty($_POST["rudo_search"]) ){
		$search=(string)$_POST["rudo_search"];
	}


	$start=( $page_number - 1 ) * $limit;
	$url_list=$GLOBALS['RUDO_VOD_API'].'/public_files/wp_api.php?action=files&start='.$start.'&limit='.$limit.'&search='.urlencode($search);
	
	//PETICION CURL
    $listado_vod=$peticiones_Curl->get_resource($url_list, null);
    if( $listado_vod==false ){
    	$json["status"]='error';
    	$json["msj"]='Error en la peticion de recursos';
        wp_send_json($json);
    }

    //RETORNAMOS ARRAY CON LOS DATOS DE VIDEO
    $arr_return=array();
    foreach ($listado_vod->videos as $key => $value) {
        $arr_return[]=array(
            'key'			=>$value->key,
            'titulo'		=>$value->titulo,
            'publico'		=>mb_strtolower($value->publico),
            'duracion'		=>$value->duracion,
            'estado'		=>mb_strtolower($value->estado),
            'imagen'		=>$value->imagenes->medium,
            'fecha_f'		=>$value->fecha_f,
            'tipo'			=>$value->tipo,
            'podcast'		=>$value->podcast
        );
    }

    $json["status"]='ok';
    $json["msj"]='';
    $json["page_number"]=(int)$listado_vod->page_next;
    $json["total_user"]=$listado_vod->totalRecords;
    $json["total_list"]=$listado_vod->totalDisplayRecords;
    $json["vods"]=$arr_return;
    wp_send_json($json);
}


function _rudo_row_post_meta($last_id, $rudo_name_custom_field, $total_key, $rudo_value_field){
	// $update_meta=wp_create_nonce( 'add-meta' );

	if( (int)$total_key==0 )
		$name_field_post_meta=$rudo_name_custom_field;
	else
		$name_field_post_meta=$rudo_name_custom_field.(int)$total_key;

	return '<tr id="meta-'.$last_id.'">
			<td class="left">
				<label class="screen-reader-text" for="meta-'.$last_id.'-key">Clave</label><input name="meta['.$last_id.'][key]" id="meta-'.$last_id.'-key" type="text" size="20" value="'.$name_field_post_meta.'" />
				<div class="submit"><input type="submit" name="deletemeta['.$last_id.']" id="deletemeta['.$last_id.']" class="button deletemeta button-small" value="Eliminar" data-wp-lists="delete:the-list:meta-'.$last_id.'::_ajax_nonce='.wp_create_nonce('delete-meta_'.$last_id).'"  />
				'.wp_nonce_field( 'change-meta', '_ajax_nonce', false, false ).'
				
			<td>
				<label class="screen-reader-text" for="meta-'.$last_id.'-value">Valor</label>
				<textarea name="meta['.$last_id.'][value]" id="meta-'.$last_id.'-value" rows="2" cols="30">'.$rudo_value_field.'</textarea>
				</td>
		</tr>';
}

function get_mid_by_key( $post_id, $meta_key ) {
  global $wpdb;
  $mid = $wpdb->get_var( $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s limit 1", $post_id, $meta_key) );
  if( $mid != '' )
    return (int)$mid;
 
  return false;
}

function ajax_rudo_associate_vod_post() {
 	
	$json=array();
	$data=array();

	if(!current_user_can('publish_pages')){
 		$json["status"]='error';
    	$json["msj"]='No tienes permitida esta opción.';
        wp_send_json($json);
 	}

 	if( get_option('rudo_radio_auto_save_options')=='n' ){
 		$json["status"]='error';
    	$json["msj"]='Esta opcion esta deshabilitada.';
        wp_send_json($json);
 	}

 	//ASIGNAMOS PARA UTILIZAR MULTIPLES CAMPOS O NO
 	$rudo_radio_multiple_fields = get_option('rudo_radio_multiple_fields');


	$rudo_key='';
	$rudo_duration='00:00:00';
	$rudo_tipo_archivo='video';
	$rudo_post_id=0;
	if( isset($_POST["rudo_post_id"]) and is_numeric($_POST["rudo_post_id"]) and $_POST["rudo_post_id"]>=0 ){
		$rudo_post_id=(int)$_POST["rudo_post_id"];
	}
	if( isset($_POST["rudo_key"]) and !empty($_POST["rudo_key"]) ){
		$rudo_key=(string)$_POST["rudo_key"];
	}
	if( isset($_POST["rudo_duration"]) and !empty($_POST["rudo_duration"]) ){
		$rudo_duration=(string)$_POST["rudo_duration"];
	}
	if( isset($_POST["tipo"]) and !empty($_POST["tipo"]) and strtolower($_POST["tipo"])=='audio' ){
		$rudo_tipo_archivo='audio';
		if( isset($_POST["podcast"]) and (int)$_POST["podcast"]==1 )
			$rudo_tipo_archivo='podcast';
	}

	$rudo_radio_acf=false;
	if( get_option('rudo_radio_acf')=='y' ){
		$rudo_radio_acf=true;
		if (!is_plugin_active('advanced-custom-fields-pro/acf.php')) {
			$json["status"]='error';
			$json["msj"]='Imposible agregar el video, No se encontro el plugin Advance Custom Field Pro (ACF), o no esta activado.';
			wp_send_json($json);
		}
	}

	$data_post = get_post($rudo_post_id);
	if($data_post==null ){
		$json["status"]='error';
	    $json["msj"]='El post no existe.';
	    wp_send_json($json);
    }
    
    if( $data_post->post_type=='page' ){
        $json["status"]='error';
	    $json["msj"]='Debe seleccionar un post válido.';
	    wp_send_json($json);
    }


    switch ($rudo_radio_acf) {
    	case true:
    		$rudo_name_custom_field=get_option('rudo_name_custom_field');
		    if( $rudo_name_custom_field==false or empty($rudo_name_custom_field) ){
		    	$json["status"]='error';
			    $json["msj"]='Imposible agregar el video, no se encontró ningún custom field (ACF) asociado al nombre ingresado en la seccion de Configuración.';
			    wp_send_json($json);
		    }

		    $field_key=$rudo_name_custom_field;
			$post_id = $data_post->ID;

		    if( $rudo_radio_multiple_fields!='n' ){
		    	// SI UTILIZA MULTIPLES CAMPOS UN CAMPO
			    $rudo_name_sub_custom_field=get_option('rudo_name_sub_custom_field');
			    if( $rudo_name_sub_custom_field==false or empty($rudo_name_sub_custom_field) ){
			    	$json["status"]='error';
				    $json["msj"]='Imposible agregar el video, no se encontró ningún sub custom field (ACF) asociado al campo repeater ingresado en la seccion RUDO->Configuración.';
				    wp_send_json($json);
			    }

			    $tmp_value=get_field($field_key, $post_id);
				if( $tmp_value==false or empty($tmp_value) )
					$tmp_value=array();


				$value=$tmp_value;
				$value[] = array($rudo_name_sub_custom_field => $rudo_key);
				update_field( $field_key, $value, $post_id );
			}
			else{
			
				// SI SOLO UTILIZA UN CAMPO
				update_field( $field_key, $rudo_key, $post_id );
			}
		    

			

			//RETORNAMOS EL ROW META VACIO EN ESTA OPCION
			$return_row_meta='';
			$last_id='';
			$last_id_time='';

    		break;
    	case false:

    		$rudo_name_custom_field=get_option('rudo_name_custom_field');
		    if( $rudo_name_custom_field==false or empty($rudo_name_custom_field) ){
		    	$rudo_name_custom_field='rudo_key';
		    }
		    $rudo_name_custom_field_duration=$rudo_name_custom_field;
		    $rudo_name_custom_field_duration.='_duration';

		    $rudo_name_custom_field_tipo_archivo=$rudo_name_custom_field;
		    $rudo_name_custom_field_tipo_archivo.='_type';

		    //SI SE ESTAN USANDO MULTIPLES CAMPOS
		    if($rudo_radio_multiple_fields=='' or $rudo_radio_multiple_fields=='y' ){
		    	$rudo_name_custom_field.='_';
		    	$rudo_name_custom_field_duration.='_';
		    	$rudo_name_custom_field_tipo_archivo.='_';

		    	global $wpdb;
			    $sql = "
			        SELECT count(*) as total_key
			        FROM wp_postmeta as pm
			        WHERE pm.meta_key LIKE '".$rudo_name_custom_field."%' AND post_id='$data_post->ID' ";
			    $result = $wpdb->get_results( $sql, 'ARRAY_A' );
			    $total_key=$result[0]["total_key"];
			    $total_key++;

			    $last_id=add_post_meta($data_post->ID, $rudo_name_custom_field.(int)$total_key, $rudo_key );
			    $last_id_time=add_post_meta($data_post->ID, $rudo_name_custom_field_duration.(int)$total_key, $rudo_duration );
			    $last_id_tipo=add_post_meta($data_post->ID, $rudo_name_custom_field_tipo_archivo.(int)$total_key, $rudo_tipo_archivo );
		    }
		    else{

		    	//SI SE ESTA USANDO SOLO UN CAMPO
		    	$last_id='';
		    	$get_meta=get_post_meta( $data_post->ID, $rudo_name_custom_field, true );
		    	if( $get_meta==false ){
		    		$last_id=add_post_meta( $data_post->ID, $rudo_name_custom_field, $rudo_key );
		    	}
		    	else{
		    		update_post_meta( $data_post->ID, $rudo_name_custom_field, $rudo_key );
		    		$last_id=get_mid_by_key($data_post->ID, $rudo_name_custom_field);
		    	}

		    	$last_id_time='';
		    	$get_meta_duration=get_post_meta( $data_post->ID, $rudo_name_custom_field_duration, true );
		    	if( $get_meta_duration==false ){
		    		$last_id_time=add_post_meta( $data_post->ID, $rudo_name_custom_field_duration, $rudo_duration );
		    	}
		    	else{
		    		update_post_meta( $data_post->ID, $rudo_name_custom_field_duration, $rudo_duration );
		    		$last_id_time=get_mid_by_key($data_post->ID, $rudo_name_custom_field_duration);
		    	}

		    	$last_id_tipo='';
		    	$get_meta_tipo=get_post_meta( $data_post->ID, $rudo_name_custom_field_tipo_archivo, true );
		    	if( $get_meta_tipo==false ){
		    		$last_id_tipo=add_post_meta( $data_post->ID, $rudo_name_custom_field_tipo_archivo, $rudo_tipo_archivo );
		    	}
		    	else{
		    		update_post_meta( $data_post->ID, $rudo_name_custom_field_tipo_archivo, $rudo_tipo_archivo );
		    		$last_id_tipo=get_mid_by_key($data_post->ID, $rudo_name_custom_field_tipo_archivo);
		    	}
		    }
		   	
		   	//entregamos la row que se agregara en el listado de post meta (campos personalizados)
		    $return_row_meta=_rudo_row_post_meta($last_id, $rudo_name_custom_field, $total_key, $rudo_key);
		    $return_row_meta.=_rudo_row_post_meta($last_id_time, $rudo_name_custom_field_duration, $total_key, $rudo_duration);
		    $return_row_meta.=_rudo_row_post_meta($last_id_tipo, $rudo_name_custom_field_tipo_archivo, $total_key, $rudo_tipo_archivo);

    		break;
    }
    

    $json["status"]='ok';
    $json["msj"]='Se ha agregado al post.';
    $json["return_row_meta"]=$return_row_meta;
    $json["acf"]=$rudo_radio_acf;
    $json["meta_id"]=$last_id;
    $json["meta_id_time"]=$last_id_time;
    $json["meta_id_tipo"]=$last_id_tipo;
    wp_send_json($json);
}



