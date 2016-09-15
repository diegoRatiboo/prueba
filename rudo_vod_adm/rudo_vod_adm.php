<?php
/*
Plugin Name: Rudo vod
Plugin URI: http://digitalproserver.com/
Description: Permite administrar los archivos vod de tu plataforma rudo.
Version: 1.1.0
Author: @dpschile
Author URI: http://digitalproserver.com/
Licence: GPL2
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
define('RUDO_VOD_ADM', dirname(__FILE__));

global $RUDO_VOD_API;
global $RUDO_VOD_VOD;
global $RUDO_VOD_VOD_PODCAST;
global $RUDO_VOD_EMBED;
$RUDO_VOD_API='https://api.rudo.video/api';
$RUDO_VOD_VOD='https://rudo.video/vod';
$RUDO_VOD_VOD_PODCAST='https://rudo.video/podcast';
$RUDO_VOD_EMBED='<iframe id="{{rudo_id}}" class="{{rudo_class}}" src="{{rudo_url}}" width="{{ancho}}" height="{{alto}}" allowscriptaccess="always" allowfullscreen="true" webkitallowfullscreen="true" frameborder="0" scrolling="no"></iframe>';

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
@include_once RUDO_VOD_ADM."/models/peticiones_curl.php";
@include_once RUDO_VOD_ADM."/models/table_vod.php";
@include_once RUDO_VOD_ADM."/models/folder_vod.php";
@include_once RUDO_VOD_ADM."/actions/ajax-function.php";
@include_once RUDO_VOD_ADM."/actions/sanear-function.php";

//FORMULARIO VOD
@include_once RUDO_VOD_ADM."/includes/formulario_vod.php";

add_action("admin_menu", "rudo_function_add_menu_option");//inicializa la funcionalidad
add_action("admin_init", "rudo_function_add_field_init");//inicializa los campos q se utilizaran
register_activation_hook(__FILE__,'rudo_db_install');//agrega la tabla a la bd
register_deactivation_hook(__FILE__, 'rudo_db_uninstall' );//elimina la tabla de la bd

add_action('media_buttons', 'rudo_button_open_modal_vod', 11);
add_action('admin_footer', 'rudo_content_list_vod');
add_action('admin_footer', 'rudo_content_embed');

//BOTON OPEN MODAL
if( !function_exists('rudo_button_open_modal_vod') ){
	function rudo_button_open_modal_vod() {
		if( $GLOBALS["typenow"]!='' and $GLOBALS["typenow"]!='page' and current_user_can('publish_pages') ):

			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		?>
			<a id="rudo_button_open_modal" title="Listado de Vod" class="button thickbox" href="#TB_inline?height=600&width=500&inlineId=myModalRudoListVod">
		        Add RUDO
		    </a>
	    <?php
	    endif;
	}
}


// CONTENIDO MODAL ASSOCIATE
if( !function_exists('rudo_content_list_vod') ){
	function rudo_content_list_vod(){

		if( $GLOBALS["typenow"]!='' and $GLOBALS["typenow"]!='page' and current_user_can('publish_pages') ):
			?>
			<div id="myModalRudoListVod" style="display: none;">

				<?php

					//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					// ZONA SCRIPT
					//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					wp_enqueue_script('jquery');
					wp_enqueue_script("jquery-ui-full-js", plugins_url('public/js/jquery-ui/jquery-ui.js' , __FILE__ ) );
					wp_enqueue_script("tagit_js", plugins_url('public/js/tag-it.js' , __FILE__ ) );
					wp_enqueue_script("handlebars-template", plugins_url('public/js/handlebars/handlebars-v3.0.0.js' , __FILE__ ) );

					wp_enqueue_script("jquery-validate-min", plugins_url('public/js/jquery-validation/jquery.validate.min.js' , __FILE__ ),array('jquery'),'1.14',true);
					wp_enqueue_script("additional-methods-min", plugins_url('public/js/jquery-validation/additional-methods.min.js' , __FILE__ ),array('jquery'),'1.14',true);
					wp_enqueue_script("jquery-form", plugins_url('public/js/jquery-form/jquery.form.js' , __FILE__ ) );
					wp_enqueue_script("sweetalert", plugins_url('public/js/sweetalert/sweetalert.min.js' , __FILE__ ) );
					//SCRIPT PLUGIN
					wp_enqueue_script("rudo_plugin", $GLOBALS['RUDO_VOD_API']."/public_files/js/plugin-wp/1.1.0/plugin.js");
					//SCRIPT PLUGIN (ASSOCIATE)
					wp_enqueue_script("rudo_plugin_associate", $GLOBALS['RUDO_VOD_API']."/public_files/js/plugin-wp/1.1.0/plugin-associate.js");


					//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					// ZONA STYLES
					wp_enqueue_style("flick_theme_tagit", "http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/flick/jquery-ui.css");
					wp_enqueue_style("jqueryCss_tagit", plugins_url('public/css/jquery.tagit.css' , __FILE__ ) );
					wp_enqueue_style("jquery_tagit", plugins_url('public/css/tagit.ui-zendesk.css' , __FILE__ ) );
					wp_enqueue_style("sweetalert", plugins_url('public/css/sweetalert/sweetalert.css' , __FILE__ ) );

					wp_enqueue_style("jquery-ui-full-css", plugins_url('public/css/jquery-ui/jquery-ui.css', __FILE__ ) );
					wp_enqueue_style("jquery-ui-full-theme-css", plugins_url('public/css/jquery-ui/jquery-ui.theme.css', __FILE__ ) );

					wp_enqueue_style("font-awesome-min", plugins_url('public/font-awesome/css/font-awesome.min.css' , __FILE__ ) );

					wp_enqueue_style("rudo-css-custom", plugins_url('public/css/plugin-custom.css' , __FILE__ ) );


				?>

					<div id="rudo-tabs">
						<ul>
						    <li><a href="#rudo-listado">Archivos</a></li>
						    <li><a href="#rudo-new-vod">Nuevo</a></li>
						 </ul>
						<div id="rudo-listado">
							<div id="rudo-msj-associate">
							</div>

							<div class="rudo-div-search">
								<div class="form-field">
									<input type="text" name="rudo_search_text" id="rudo_search_text">
								</div>
								<button id="rudo_button_search_text" class="button" >Buscar</button>
							</div>
							<br />
							<ul id="rudo_show_list_vod">
								<!-- aqui vods -->
							</ul>
							<button data-page="1" class="rudo_button_load_more button">Más</button>

						</div>
						 <div id="rudo-new-vod">

							<?php

								//DECLARAMOS EL MODELO FOLDER
								$folder_Vod = new Folder_Vod();
								$folders=$folder_Vod->get_folders_vod();

								//IMPRIMIMOS EL FORMULARIO PARA AGREGAR VOD
								$formulario_Vod = new Formulario_vod();
								$formulario_Vod->print_formulario_vod($folders, array(), 'modal');
							?>

						</div>

					</div>

				<script type="text/javascript">// <![CDATA[
				 	var rudo_post_id='<?php echo get_the_ID(); ?>';
	    			var rudo_url_site='<?php echo get_site_url(); ?>';
	    			var rudo_embed='<?php echo $GLOBALS["RUDO_VOD_EMBED"]; ?>';
	    			var rudo_url='<?php echo $GLOBALS["RUDO_VOD_VOD"]; ?>';
	    			var rudo_url_podcast='<?php echo $GLOBALS["RUDO_VOD_VOD_PODCAST"]; ?>';
	    			var rudo_radio_auto_save_options='<?php echo get_option("rudo_radio_auto_save_options"); ?>';
	    			var rudo_radio_multiple_fields='<?php echo get_option("rudo_radio_multiple_fields"); ?>';
	    			var rudo_name_custom_field='<?php echo get_option("rudo_name_custom_field"); ?>';
	    			var rudo_name_sub_custom_field='<?php echo get_option("rudo_name_sub_custom_field"); ?>';
	    			var rudo_radio_acf='<?php echo get_option("rudo_radio_acf"); ?>';

				// ]]></script>

				<script id="alert-template-associate" type="text/x-handlebars-template">
					  <div class="updated  {{tipo}}">
					  	<p>{{msj}}</p>
					  </div>
				</script>

			</div>
			<?php

		endif;
	}
}


// MODAL IFRAME DEL LISTADO
if( !function_exists('rudo_content_embed') ){
	function rudo_content_embed(){

		if( current_user_can('publish_pages') ):
			?>
			<div id="myModalRudoEmbed" style="display: none;">

				<?php

					//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					// ZONA SCRIPT
					//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					wp_enqueue_script('jquery');

				?>
				<style>
					#TB_window{
						width: 500px !important;
						height: 400px !important;
					}
				</style>

				<div id="">
					<br />
					<div class="form-field">
						<label for="">Url Directa</label>
						<input type="text" name="rudo_url_direct" id="rudo_url_direct" />
					</div>
					<br />
					<div class="form-field">
						<label for="">Embed</label>
						<textarea name="rudo_embed" id="rudo_embed" cols="30" rows="10"></textarea>
					</div>
				</div>

			</div>
			<?php

		endif;
	}
}



//SE INICIALIZA EL PANEL DEL PLUGIN
if(!function_exists("rudo_function_add_menu_option")){

	function rudo_function_add_menu_option(){
		add_menu_page("Listado Rudo", "RUDO", "publish_pages", "RUDO", "rudo_get_pagina_principal", "dashicons-format-video");//puede entrar el super admin, admin, y editor
		add_submenu_page("RUDO",__("Listado"),__("Listado"), "publish_pages","RUDO");
		add_submenu_page("RUDO","Vod","Nuevo", "publish_pages","RUDO_VOD", "rudo_new_vod_file");//puede entrar el super admin, admin, y editor
		add_submenu_page("RUDO","Configurar Key rudo","Configuración", "manage_options","RUDO_KEY", "rudo_get_config_key");//puede entrar el super admin, y admin
	}
}


//LISTADO DE VIDEOS (VODs)
if(!function_exists("rudo_get_pagina_principal")){
	function rudo_get_pagina_principal(){

		$Table_Vod_List = new Table_Vod_List();
    	$Table_Vod_List->prepare_items();

    	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		// ZONA SCRIPT
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    	wp_enqueue_script('jquery');
		wp_enqueue_script('thickbox');
		wp_enqueue_script("plugin-wp-table", $GLOBALS['RUDO_VOD_API']."/public_files/js/plugin-wp/1.1.0/plugin-table.js");


		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		// ZONA STYLES
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		wp_enqueue_style('thickbox');
		wp_enqueue_style("font-awesome-min", plugins_url('public/font-awesome/css/font-awesome.min.css' , __FILE__ ) );

		wp_enqueue_style("rudo-css-custom", plugins_url('public/css/plugin-custom.css' , __FILE__ ) );
    	?>

    	<div class="wrap">
	        <div id="icon-users" class="icon32"><br/></div>
	        <h3>Listado <a href="<?php echo admin_url(); ?>admin.php?page=RUDO_VOD" class="page-title-action">Nuevo</a></h3>

	        <form method="get">
			    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			  	<?php $Table_Vod_List->search_box('Buscar', 'rudo_search_vod_table'); ?>
			</form>
	        <br/>

	        <?php $Table_Vod_List->display(); ?>
	    </div>

	    <script>
	    	var url_get_file='<?php echo $GLOBALS["RUDO_VOD_API"] ?>/getvideo/';
	    </script>
		<?php
	}
}

//PAGINA DE CONFIGURACION
if(!function_exists("rudo_get_config_key")){
	function rudo_get_config_key(){
    	?>
    	<div class="wrap">
	        <div id="icon-users" class="icon32"><br/></div>
	        <h3>Configurar Key</h3>
	        <form action="options.php" method="post">

	        	<?php

	        		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					// ZONA SCRIPT
	        		wp_enqueue_script("sweetalert", plugins_url('public/js/sweetalert/sweetalert.min.js' , __FILE__ ) );
	        		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					// ZONA STYLES
	        		wp_enqueue_style("sweetalert", plugins_url('public/css/sweetalert/sweetalert.css' , __FILE__ ) );


	        		settings_fields("rudo_form_key_user");
	        		@do_settings_fields("rudo_form_key_user");

	        		//ASIGNACION DE VALORES
	        		$rudo_radio_multiple_fields = get_option( 'rudo_radio_multiple_fields' );
	        		$rudo_radio_acf = get_option( 'rudo_radio_acf' );
	        		$rudo_value_user = get_option( 'rudo_value_user' );
	        		$rudo_radio_auto_save_options = get_option( 'rudo_radio_auto_save_options' );

	        	?>
	        	<div class="form-group form-field">
	        		<label for="rudo_value_user">Key Rudo</label><br />
	        		<input type="text" class="form-control" name="rudo_value_user" id="rudo_value_user" value="<?php echo $rudo_value_user ?>" >
	        	</div>
	        	
	        	<br />
				<div class="form-group form-field">
					<label for="">Opciones de Campos</label><br />
	        		<input name="rudo_radio_multiple_fields" id="rudo_radio_multiple_fields_no" type="radio" value="n" <?php checked( 'n', $rudo_radio_multiple_fields ); ?> />
	        		<label for="rudo_radio_multiple_fields_no">Solo Utilizare un campo</label>
					<input name="rudo_radio_multiple_fields" id="rudo_radio_multiple_fields_yes" type="radio" value="y" <?php checked( 'y', $rudo_radio_multiple_fields ); ?> <?php checked( '', $rudo_radio_multiple_fields ); ?>  />
					<label for="rudo_radio_multiple_fields_yes">Utilizaré Multiples Campos <em>(Se agregará un identificador numérico al final del nombre)</em></label>
				</div>
				
				<br />
	        	<div class="form-group form-field">
	        		<label for="rudo_name_custom_field">Nombre Campo Personalizado</label><br />
	        		<input type="text" class="form-control" name="rudo_name_custom_field" id="rudo_name_custom_field" value="<?php echo get_option('rudo_name_custom_field') ?>" >
	        	</div>
				

				<br />
				<?php 
					$rudo_name_sub_custom_field='';
					$rudo_name_sub_custom_field_disabled='disabled';
					$rudo_display_none='display:none;';
					if( $rudo_radio_acf=='y' and ($rudo_radio_multiple_fields=='y' or $rudo_radio_multiple_fields=='') ):
						$rudo_name_sub_custom_field=get_option('rudo_name_sub_custom_field');
						$rudo_name_sub_custom_field_disabled='';
						$rudo_display_none='';
					endif; 
				?>
	        	<div class="form-group form-field" style="<?php echo $rudo_display_none; ?>">
	        		<label for="rudo_name_sub_custom_field">Nombre/key Sub Campo</label><br />
	        			<input <?php echo $rudo_name_sub_custom_field_disabled ?> type="text" class="form-control" name="rudo_name_sub_custom_field" id="rudo_name_sub_custom_field" value="<?php echo $rudo_name_sub_custom_field; ?>" >
	        		<br />
	        		<em>Este campo es el asociado al campo repeater de mas arriba</em>
	        	</div>

				<br />
				<div class="form-group form-field">
					<label for="">Seleccione si utilizará el plugin Advance Custom Field Pro (ACF)</label><br />
	        		<input name="rudo_radio_acf" id="rudo_radio_acf_yes" type="radio" value="y" <?php checked( 'y', $rudo_radio_acf ); ?> />
	        		<label for="rudo_radio_acf_yes" title="Esta opción requiere que el custom field (ACF Pro) ya este creado">Utilizar ACF</label>
					<input name="rudo_radio_acf" id="rudo_radio_acf_no" type="radio" value="n" <?php checked( 'n', $rudo_radio_acf ); ?> />
					<label for="rudo_radio_acf_no">No Utilizar ACF</label>
				</div>
				
				
				<?php 
					$rudo_display_none='display:none;';
					if( $rudo_radio_acf=='y' ):
						$rudo_display_none='';
					endif;
				?>
				<br />
				<div class="form-group form-field" style="<?php echo $rudo_display_none; ?>">
					<label for="">Seleccione el modo de operación</label><br />
	        		<input name="rudo_radio_auto_save_options" id="rudo_radio_auto_save_options_no" type="radio" value="n" <?php checked( 'n', $rudo_radio_auto_save_options ); ?> />
	        		<label for="rudo_radio_auto_save_options_no" title="Se guardará solo cuando se guarde/publique/actualice el post">Manual</label>
					
					<input name="rudo_radio_auto_save_options" id="rudo_radio_auto_save_options_yes" type="radio" value="y" <?php checked( 'y', $rudo_radio_auto_save_options ); ?> <?php checked( '', $rudo_radio_multiple_fields ); ?>  />
					<label for="rudo_radio_auto_save_options_yes" title="Al asociar un video se guardará automaticamente en la BD.">Guardar Automaticamente</label>
				</div>
				
	        	<?php @submit_button('Guardar Key') ?>
	        </form>

	        <script type="text/javascript">
	        	jQuery(document).ready(function($) {

	        		$("input[name='rudo_radio_acf']").on('click', function(){

	        			//MOSTRAMOS EL CAMPO DE MODO DE OPERACION
	        			$("input[name='rudo_radio_auto_save_options']").parent().fadeIn();
	        			//+++++++++++++++++++++++++++++++++++++++

	        			//+++++++++++++++++++++++++++++++++++++++
	        			//SI UTILIZA EL PLUGIN ACF Y EL CAMPO MULTIPLES CAMPOS ESTA CHECK MUESTRA CAMPO "SUB CAMPO"
	        			if( $(this).val()=='y' && $("#rudo_radio_multiple_fields_yes").is(':checked') ){
	        				$("#rudo_name_sub_custom_field").prop('disabled', false);
	        				$("#rudo_name_sub_custom_field").parent().fadeIn();
	        			}
	        			else if( $(this).val()=='n' ) {//SI NO UTILIZA EL PLUGIN ACF OCULTAMOS CAMPO "SUB CAMPO"

	        				$("#rudo_name_sub_custom_field").prop('disabled', true);
	        				$("#rudo_name_sub_custom_field").parent().fadeOut();

	        				//SI TRATA DE UTILIZAR EL GUARDADO MANUAL, SIN USAR ACF PRO
	        				if( $("#rudo_radio_auto_save_options_no").is(':checked') ){
	        					$("#rudo_radio_auto_save_options_yes").prop('checked', true);
	        					swal({title: "Advertencia!",text: 'Esta opción solo pue ser utilizada si utiliza el plugin ACF Pro.', type: 'warning'});
	        				}

	        				//OCULTAMOS EL CAMPO DE MODO DE OPERACION, Y SELECCIONAMOS GUARDAR AUTOMATICAMENTE
	        				$("input[name='rudo_radio_auto_save_options']").parent().fadeOut();
	        				$("#rudo_radio_auto_save_options_yes").prop('checked', true);
	        				//+++++++++++++++++++++++++++++++++++++++
	        			}
	        		});
	
	        		$("input[name='rudo_radio_multiple_fields']").on('click', function(){
	        			if( $(this).val()=='y' && $("#rudo_radio_acf_yes").is(':checked') ){
	        				$("#rudo_name_sub_custom_field").prop('disabled', false);
	        				$("#rudo_name_sub_custom_field").parent().fadeIn();
	        			}
	        			else if( $(this).val()=='n' ) {
	        				$("#rudo_name_sub_custom_field").prop('disabled', true);
	        				$("#rudo_name_sub_custom_field").parent().fadeOut();
	        			}
	        		});


	        		$("input[name='rudo_radio_auto_save_options']").on('click', function(){
	        			//SI TRATA DE UTILIZAR EL GUARDADO MANUAL, SIN USAR ACF PRO
	        			if( $(this).val()=='n' && $("#rudo_radio_acf_no").is(':checked') ){
	        				$("#rudo_radio_auto_save_options_yes").prop('checked', true);
	        				swal({title: "Advertencia!",text: 'Esta opción solo pue ser utilizada si utiliza el plugin ACF Pro.', type: 'warning'});
	        			}
	        		});

	        		$("form #submit").on('click', function(){
	        			if( $("input[name='rudo_radio_acf']:checked").val()=='y' && $("input[name='rudo_radio_multiple_fields']:checked").val()=='y' && $("#rudo_name_sub_custom_field").val()=="" ){
	        				swal({title: "Advertencia!",text: 'Por favor ingrese el nombre del sub campo', type: 'warning'});
	        				return false;
	        			}
	        		})
	        	});
			</script>
	        <br/>
	    </div>
		<?php
	}
}

//PAGINA NUEVO VOD
if(!function_exists("rudo_new_vod_file")){
	function rudo_new_vod_file(){

			//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			// ZONA SCRIPT
			//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			wp_enqueue_script('jquery');
			wp_enqueue_script("jquery-ui-full-js", plugins_url('public/js/jquery-ui/jquery-ui.js' , __FILE__ ) );
			wp_enqueue_script("tagit_js", plugins_url('public/js/tag-it.js' , __FILE__ ) );
			wp_enqueue_script("jquery-validate-min", plugins_url('public/js/jquery-validation/jquery.validate.min.js' , __FILE__ ),array('jquery'),'1.14',true);
			wp_enqueue_script("additional-methods-min", plugins_url('public/js/jquery-validation/additional-methods.min.js' , __FILE__ ),array('jquery'),'1.14',true);
			wp_enqueue_script("handlebars-template", plugins_url('public/js/handlebars/handlebars-v3.0.0.js' , __FILE__ ) );
			wp_enqueue_script("jquery-form", plugins_url('public/js/jquery-form/jquery.form.js' , __FILE__ ) );
			wp_enqueue_script("sweetalert", plugins_url('public/js/sweetalert/sweetalert.min.js' , __FILE__ ) );
			//SCRIPT PLUGIN
			wp_enqueue_script("rudo_plugin", $GLOBALS['RUDO_VOD_API']."/public_files/js/plugin-wp/1.1.0/plugin.js");


			//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			// ZONA STYLES
			wp_enqueue_style("flick_theme_tagit", "http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/flick/jquery-ui.css");
			wp_enqueue_style("jqueryCss_tagit", plugins_url('public/css/jquery.tagit.css' , __FILE__ ) );
			wp_enqueue_style("jquery_tagit", plugins_url('public/css/tagit.ui-zendesk.css' , __FILE__ ) );
			wp_enqueue_style("sweetalert", plugins_url('public/css/sweetalert/sweetalert.css' , __FILE__ ) );

			wp_enqueue_style("jquery-ui-full-css", plugins_url('public/css/jquery-ui/jquery-ui.css', __FILE__ ) );
			wp_enqueue_style("jquery-ui-full-theme-css", plugins_url('public/css/jquery-ui/jquery-ui.theme.css', __FILE__ ) );

			wp_enqueue_style("font-awesome-min", plugins_url('public/font-awesome/css/font-awesome.min.css' , __FILE__ ) );

			wp_enqueue_style("rudo-css-custom", plugins_url('public/css/plugin-custom.css' , __FILE__ ) );

			//DECLARAMOS EL MODELO FOLDER
			$folder_Vod = new Folder_Vod();
			$folders=$folder_Vod->get_folders_vod();

			//IMPRIMIMOS EL FORMULARIO PARA AGREGAR VOD
			$formulario_Vod = new Formulario_vod();
			$formulario_Vod->print_formulario_vod($folders, array(), 'ingresar');

			?>
		<script>
			var url_redirect='<?php echo admin_url(); ?>admin.php?page=RUDO';
		</script>
		<?php
	}
}



//SE REGISTRAN LOS CAMPOS QUE UTILIZARA EL PLUGIN
if(!function_exists("rudo_function_add_field_init")){
	function rudo_function_add_field_init(){
		register_setting("rudo_form_key_user", "rudo_value_user");
		register_setting("rudo_form_key_user", "rudo_name_custom_field", 'rudo_format_name_custom_field');
		register_setting("rudo_form_key_user", "rudo_name_sub_custom_field", 'rudo_format_name_custom_field');

		register_setting("rudo_form_key_user", "rudo_radio_acf");
		register_setting("rudo_form_key_user", "rudo_radio_multiple_fields");
		register_setting("rudo_form_key_user", "rudo_radio_auto_save_options");
	}
}

//AGREGA LA TABLA A LA BD
if(!function_exists("rudo_db_install")){
	function rudo_db_install(){
	   	global $wpdb;
	   	$table_name = $wpdb->prefix . "rudo_config";

		$charset_collate = $wpdb->get_charset_collate();
		//+++++++++++++++++++++++++++++++
		//POR AHORA NO SE UTILIZARA
		//+++++++++++++++++++++++++++++++
		// $sql = "CREATE TABLE $table_name (
		//   `id` smallint(4) NOT NULL AUTO_INCREMENT,
		//   `value` text NOT NULL,
		//   `type` varchar(8) NOT NULL,
		//   UNIQUE KEY id (id)
		// ) $charset_collate;";
		// require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// dbDelta( $sql );
	}
}
//ELIMINA LA TABLA DE LA BD
function rudo_db_uninstall()
{
	global $wpdb;	//required global declaration of WP variable
	$table_name = $wpdb->prefix."rudo_config";
	//+++++++++++++++++++++++++++++++
	//POR AHORA NO SE UTILIZARA
	//+++++++++++++++++++++++++++++++
	// $sql = "DROP TABLE IF EXISTS ". $table_name;
	// $wpdb->query($sql);
}
