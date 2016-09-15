<?php
class Peticiones_Curl {

    public function getToken(){

        $rudo_value_user=get_option('rudo_value_user');//optenemos el valor de la opcion
        if($rudo_value_user==false){
            return false;
        }
        return (string)stripslashes($rudo_value_user);
    }

    public function getAditional(){

        $rudo_value_aditional=get_option('siteurl');//optenemos el valor de la opcion
        if($rudo_value_aditional==false){
            return false;
        }
        return (string)stripslashes($rudo_value_aditional);
    }

    public function get_resource( $url, $data = null ) {


        $access_token=$this->getToken();
        if($access_token==false){
            return false;
        }

        
        try {

            $data_aditional = $this->getAditional();
            $headers = array(
                'X-ACCESS-TOKEN: '.$access_token,
                'Accept: application/json',
                'X-DATA-ADITIONAL:'.$data_aditional,
            );
            // Inicia cURL
            $session = curl_init( $url );
            curl_setopt($session,CURLOPT_HTTPHEADER,$headers);

            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($session);
            $code = curl_getinfo($session, CURLINFO_HTTP_CODE);
            curl_close($session);

            $response=json_decode($response);
            if($response->status=='error'){
                return false;
            }
            return $response;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function post_resource( $url, $data = null ) {


        $access_token=$this->getToken();
        if($access_token==false){
            return false;
        }
        
        try {
            $content=$data;
            $content["rudo_tmp"]='y';
            $content["origin"]='native';

            $data_aditional = $this->getAditional();
            $headers = array(
                'X-ACCESS-TOKEN: '.$access_token,
                'Content-Type: multipart/form-data',
                'X-DATA-ADITIONAL:'.$data_aditional,
            );

            $session = curl_init( $url );
            curl_setopt($session,CURLOPT_HTTPHEADER,$headers);
            curl_setopt($session,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($session,CURLOPT_POSTFIELDS, $content );
            $result = curl_exec($session);
            curl_close($session);
            $result=json_decode($result);
            return $result;


        } catch (\Exception $e) {
            return false;
        }
    }
    
}

?>
