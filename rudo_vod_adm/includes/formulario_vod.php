<?php

class Formulario_Vod {

	//se agrega el formulario html
	function print_formulario_vod($folders=array(), $data=array(), $action='ingresar'){

		?>

    	<div class="wrap">
	        <div id="icon-users" class="icon32"><br/></div>
	        <h3>Nuevo VOD</h3>

			<?php if($action=='ingresar'): ?>
			<div id="col-container">
		       	<div id="col-left">
		    <?php endif; ?>

					<div class="rudo_progress_content" style="display:none;">
						<label></label>
						<div id="rudo-progressbar" ></div>
					</div>


		       		<div class="cargandoIcon text-center">
		       		</div>

			        <div class="form-wrap">
				        <form action="<?php echo admin_url( 'admin-ajax.php' );?>" method="post" id="formVodWP" enctype="multipart/form-data">

				        	<div class="form-group form-field">
				        		<label for="rudo_title_vod">Título</label>
				        		<input type="text" class="form-control" name="rudo_title_vod" id="rudo_title_vod" value=""  required>
				        	</div>

				        	<div class="form-group form-field">
				        		<label for="rudo_folder_vod">Carpeta</label>
				        		<select name="rudo_folder_vod" id="rudo_folder_vod" required>
				        			<option value="">Seleccione Carpeta</option>
				        			<?php
				        				foreach ($folders as $value):
				        					echo '<option value="'.$value["id"].'" >'.$value["nombre"].'</option>';
				        				endforeach;
				        			?>
				        		</select>
				        	</div>

                            <div class="radio radio-primary">
                            	<label for="rudo_checkYT">
                            		<input type="radio" name="rudo_plataforma" id="rudo_checkYT" value="yt">
                            		Video de Youtube
                            	</label>
                            </div>
                            <div class="radio radio-primary">
                            	<label for="rudo_checkUrlDownYT">
                            		<input type="radio" name="rudo_plataforma" id="rudo_checkUrlDownYT" value="down_yt">
                            		Descargar desde Youtube
                            	</label>
                            </div>

                            <div class="radio radio-primary">
                            	<label for="rudo_checkUrlDownTW">
                            		<input type="radio" name="rudo_plataforma" id="rudo_checkUrlDownTW" value="down_tw">
                            		Descargar desde Twitter
                            	</label>
                            </div>
                            <div class="radio radio-primary">
                            	<label for="rudo_checkUrlDownFB">
                            		<input type="radio" name="rudo_plataforma" id="rudo_checkUrlDownFB" value="down_fb">
                            		Descargar desde Facebook
                            	</label>
                            </div>

                            <div class="radio radio-primary">
                            	<label for="rudo_checkUrlDown">
                            		<input type="radio" name="rudo_plataforma" id="rudo_checkUrlDown" value="down_url">
                            		Descargar desde Url
                            	</label>
                            </div>

                            <div class="radio radio-primary">
                            	<label for="rudo_checkVodUp">
                            		<input type="radio" name="rudo_plataforma" id="rudo_checkVodUp" value="up_file" checked>
                            		Subir Archivo
                            	</label>
                            </div>

							<div class="form-group form-field">
				        		<label for="rudo_folder_vod">Tipo de Archivo</label>
				        		<select name="rudo_type_file" id="rudo_type_file" required>
				        			<option value="v">Video</option>
				        			<option value="a">Audio</option>
				        		</select>
				        	</div>

				        	<div class="form-group form-field">
				        		<label for="rudo_file_vod">Archivo</label>
				        		<input type="file" class="form-control" name="rudo_file_vod" id="rudo_file_vod" accept="" required>
				        	</div>

				        	<div class="form-group form-field">
				        		<label for="rudo_image_vod">Imagen</label>
				        		<input type="file" class="form-control" name="rudo_image_vod" id="rudo_image_vod" accept=".png, .jpeg, .jpg">
				        	</div>

				        	<!-- EN CASO DE SELECCIONAR URL DE YOUTUBE -->
				        	<div class="form-group form-field dual-form-field" style="display:none;">
								<label>URL Youtube</label>
								<div class="input-group" data-autoclose="true">
	                                <input type="text" maxlength="250" name="rudo_urlYoutube" id="rudo_urlYoutube" class="form-control" placeholder="https://www.youtube.com/watch?v=ypVxH15-HRM" >
	                                <span class="input-group-btn">
	                                	<button type="button" class="btn btn-primary rudo_btn_import_yt" data-toggle="tooltip" data-placement="top" title="Cargar datos de Youtube">
	                                		<i class="fa fa-cloud-download"></i>
                                		</button>
                                	</span>
	                            </div>
	                        </div>

							<!-- EN CASO DE SELECCIONAR URL DIRECTA -->
	                        <div class="form-group form-field" style="display:none;">
                                <label>URL Directa</label>
                                <input type="text" maxlength="250" name="rudo_urlDown" id="rudo_urlDown" class="form-control" placeholder="https://example.com/video.mp4" >
                            </div>

							<!-- EN CASO DE SELECCIONAR URL TWITTER O FACEBOOK -->
                            <div class="form-group form-field dual-form-field" style="display:none;">
                                <label>URL</label>
                                <div class="input-group" data-autoclose="true">
                                    <input type="text" maxlength="250" name="rudo_urlTwFb" id="rudo_urlTwFb" class="form-control" placeholder="" >
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary rudo_btn_import_twfb" data-toggle="tooltip" data-placement="top" title="Cargar información">
                                            <i class="fa fa-cloud-download"></i>
                                        </button>
                                    </span>
                                </div>
                                <em>El video debe ser publico</em>
                            </div>

				        	<!-- ==================== -->
			                <!-- tags -->
			                <label for="mySingleField">Tags</label> <em>(para agregar el tag, presione "Enter")</em>
			                <input type="hidden" name="tags_video" id="mySingleField" value="" >
			                <ul id="myTags">
			                </ul>


				        	<div class="form-group form-field">
				        		<label for="rudo_description_vod">Descripción</label>
				        		<textarea name="rudo_description_vod" id="rudo_description_vod" cols="30" rows="10"></textarea>
				        	</div>
				        	<input type="hidden" class="" name="action" id="action" value="rudo_upload_vod">

				        	<?php @submit_button('Guardar') ?>
				        </form>
			        </div>

			<?php if($action=='ingresar'): ?>
		        </div>
	        </div>
	    	<?php endif; ?>

	        <br/>
	    </div>

	    <script id="cargando-template" type="text/x-handlebars-template">
		  <div class="text-center" ><i class="fa fa-circle-o-notch fa-spin fa-3x"></i></div>
		</script>

		<script id="alert-template" type="text/x-handlebars-template">
		  <div id="message" class="updated {{tipo}}">
		  	<p>{{msj}}</p>
		  </div>
		</script>

		<?php
	}
}



