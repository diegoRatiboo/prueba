<?php
class Folder_Vod {

    public static function get_folders_vod() {

        //INSTANCIAMOS MODELO DE PETICIONES CURL
        $peticiones_Curl = new Peticiones_Curl();
        //FIN INSTANCIA ------------------------

        $url_list=$GLOBALS['RUDO_VOD_API'].'/public_files/wp_api.php?action=categories';

        //PETICION CURL
        $folders_vod=$peticiones_Curl->get_resource($url_list, null);

        if( $folders_vod==false ){
            return false;
        }

        //RETORNAMOS ARRAY CON LOS DATOS
        $arr_return=array();
        foreach ($folders_vod->categorias as $key => $value) {
            $arr_return[]=array(
                'id'=>$value->id,
                'nombre'=>$value->nombre
            );
        }
        return $arr_return;

    }

}

?>
