<?
/*
  Generic RPX Library
*/

define('RPX_LIBRARY_VERSION', '0.1');

class RPX {

  const create_api = 'https://rpxnow.com/plugin/create_rp';
  const lookup_api = 'https://rpxnow.com/plugin/lookup_rp';
  const auth_api = 'https://rpxnow.com/api/v2/auth_info';
    
  /* performs a lookup request for getting information about an RPX account */
  function lookup( $value, $type ) {
    $demographics = 'PHP RPX ' . RPX_LIBRARY_VERSION;
    
    if ( RPX_CLIENT_VERSION ) {
      $demographics .= " / " . RPX_CLIENT_VERSION;
    }
    
    $post_data = array(
        $type => $value,
        'demographics' => $demographics
      );
    
    $raw_result = RPX::http_post( RPX::lookup_api, $post_data );
    return json_decode( $raw_result, true );
  }
  
  /* fetches authorization information from a token */
  function auth_info( $token, $api_key ) {
    $post_data = array(
        'token' => $token,
        'apiKey' => $api_key,
        'format' => 'json'
      );
      
      $raw_result = RPX::http_post( RPX::auth_api, $post_data );
      return json_decode( $raw_result, true );
  }

  function locales() { 
    return array('pt-BR', 'vi', 'zh', 'nl', 'sr', 'ko', 'ru', 'sv-SE', 'ro', 'pt', 'it', 'hu', 'fr', 'ja', 'en', 'bg', 'es', 'el', 'pl', 'de', 'cs', 'da');
  }
  
  /*
    Everything below is an internal utility function
  */


  /* builds the current URL for the page being looked at */
  function current_url() {
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {                    
      $proto = "https";
      $standard_port = '443';                                                 
    } else {                                                                    
      $proto = 'http';                                                        
      $standard_port = '80';                                                  
    }                                                                           

    $authority = $_SERVER['HTTP_HOST'];                                         
    if (strpos($authority, ':') === FALSE &&                                    
        $_SERVER['SERVER_PORT'] != $standard_port) {                            
      $authority .= ':' . $_SERVER['SERVER_PORT'];                            
    }                                                                           

    if (isset($_SERVER['REQUEST_URI'])) {                                       
      $request_uri = $_SERVER['REQUEST_URI'];                                 
    } else {                                                                    
      $request_uri = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];         
      $query = $_SERVER['QUERY_STRING'];                                      
      if (isset($query)) {                                                    
        $request_uri .= '?' . $query;                                       
      }                                                                       
    }                                                                           

    return $proto . '://' . $authority . $request_uri;                          
  }


  /* next three functions are for sending HTTP POST requests */
  function http_post($url, $post_data) {
    if (function_exists('curl_init')) {
      return RPX::curl_http_post($url, $post_data);
    } else {
      return RPX::builtin_http_post($url, $post_data);
    }
  }

  function curl_http_post($url, $post_data) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $raw_data = curl_exec($curl);
    curl_close($curl);
    return $raw_data;
  }


  function rpx_builtin_http_post($url, $post_data) {
    $content = http_build_query($post_data);
    $opts = array('http'=>array('method'=>"POST", 'content'=>$content));
    $context = stream_context_create($opts);
    $raw_data = file_get_contents($url, 0, $context);
    return $raw_data;
  }



}


?>