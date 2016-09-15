<?php
class Table_Vod_List extends WP_List_Table {

    private static $total_list_vod;
    function __construct(){
        global $status, $page;
        parent::__construct( array(
            'plural' => 'list',
            'singular' => 'lists',
            'ajax'      => false
        ) );
    }
    public static function get_files_vod( $per_page = 15, $page_number = 1 ) {

        //INSTANCIAMOS MODELO DE PETICIONES CURL
        $peticiones_Curl = new Peticiones_Curl();
        //FIN INSTANCIA ------------------------

        $cadenaOrder='';
        $search='';
        if(isset($_GET["orderby"]) and isset($_GET["order"]) and 
               ( mb_strtolower($_GET["order"])=='asc' or mb_strtolower($_GET["order"])=='desc' ) ){
            $cadenaOrder='&sortCol='.$_GET["orderby"].'&colOrder='.$_GET["order"];
        }
        if(isset($_GET["s"]) and !empty($_GET["s"]) )
            $search='&search='.urlencode($_GET["s"]);

        $start=( $page_number - 1 ) * $per_page;
        $url_list=$GLOBALS['RUDO_VOD_API'].'/public_files/wp_api.php?start='.$start.'&limit='.$per_page.'&action=files'.$cadenaOrder.$search;

        //PETICION CURL
        $listado_vod=$peticiones_Curl->get_resource($url_list, null);

        if( $listado_vod==false ){
            return false;
        }
        self::$total_list_vod=$listado_vod->totalRecords;

        //RETORNAMOS ARRAY CON LOS DATOS DE VIDEO
        $arr_return=array();
        foreach ($listado_vod->videos as $key => $value) {
            $arr_return[]=array(
                'id'            =>$value->id,
                'key'           =>$value->key,
                'titulo'        =>$value->titulo,
                'fecha_f'       =>$value->fecha_f,
                'estado_icon'   =>$value->estado_icon,
                'estado_num'    =>$value->estado_num,
                'duracion'      =>$value->duracion,
                'tipo'          =>$value->tipo,
                'publico_icon'  =>$value->publico_icon,
                'imagen'        =>$value->imagenes->small,
                'podcast'       =>$value->podcast,
                'origen'        =>$value->origen,
            );
        }
        return $arr_return;

    }
    
    function column_default($item, $column_name){
        switch($column_name){
            case 'key':
                if( (int)$item['podcast']==0)
                    return '<a href="'.$GLOBALS['RUDO_VOD_VOD'].'/'.$item[$column_name].'" target="_blank">'.$item[$column_name].'</a>';
                else
                    return '<a href="'.$GLOBALS['RUDO_VOD_VOD_PODCAST'].'/'.$item[$column_name].'" target="_blank">'.$item[$column_name].'</a>';
            case 'titulo':
                return $item[$column_name].'<br/><span class="text-duration " id="rudo_time_'.$item['id'].'" data="'.$item['id'].'" data-status="'.$item['estado_num'].'" data-origin="'.$item['origen'].'" >['.$item['duracion'].']</span>';
            case 'fecha_f':
                return $item[$column_name];
            case 'estado_icon':
                return $item[$column_name];
            case 'tipo':
                return $item[$column_name];
            case 'publico_icon':
                return $item[$column_name];
            case 'imagen':
                return '<img src="'.$item[$column_name].'" style="width:100px; height:auto;" />';
            case 'accion':
                return '<a class="rudo-btn-get-data-vod button thickbox" id="row_'.$item['key'].'" type="button" data-key="'.$item['key'].'" title="Embed Vod" href="#TB_inline?height=500&width=500&inlineId=myModalRudoEmbed"><i class="fa fa-code"></i></a>';
        }
    }
    function get_columns(){
        $columns = array(
            'key'           => 'KEY',
            'imagen'        => 'IMAGEN',
            'titulo'        => 'TITULO',
            'fecha_f'       => 'FECHA',
            'estado_icon'   => 'ESTADO',
            'tipo'          => 'TIPO',
            'publico_icon'  => 'PUBLICO',
            'accion'        => 'ACCIÓN'
        );
        return $columns;
    }
    function get_sortable_columns() {
        $sortable_columns = array(
            'key'           => array('key',false),
            'imagen'        => array('imagen',false),
            'titulo'        => array('titulo',false),
            'fecha_f'       => array('fecha',false),
            'estado_icon'   => array('estado',false),
            'tipo'          => array('tipo',false),
            'publico_icon'  => array('publico',false),
            'accion'        => array('accion',false),
        );
        return $sortable_columns;
    }
    function prepare_items() {
        // global $wpdb;
        $per_page   = 10;
        $columns    = $this->get_columns();
        $hidden     = array();
        $sortable   = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        /* Datos y paginacion */
        $this->items    = self::get_files_vod( $per_page, $this->get_pagenum() );
        $total_items    = self::$total_list_vod;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );
    }
}

?>
