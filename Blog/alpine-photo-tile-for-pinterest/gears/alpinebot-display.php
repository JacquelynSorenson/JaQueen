<?php

/** ##############################################################################################################################################
 *    AlpineBot Secondary
 * 
 *    Display functions
 *    Contains ONLY UNIVERSAL functions
 * 
 *  ##########################################################################################
 */

class PhotoTileForPinterestBotSecondary extends PhotoTileForPinterestPrimary{     
   
/**
 *  Update global (non-widget) options
 *  
 *  @ Since 1.2.4
 *  @ Updated 1.2.5
 */
  function update_global_options(){
    $options = $this->get_all_options();
    $defaults = $this->option_defaults(); 
    foreach( $defaults as $name=>$info ){
      if( empty($info['widget']) && isset($options[$name])){
        // Update non-widget settings only
        $this->set_active_option($name,$options[$name]);
      }
    }
    // Go ahead and reset info also
    $this->set_private('results', array('photos'=>array(),'feed_found'=>false,'success'=>false,'userlink'=>'','hidden'=>'','message'=>'') );
  }
  
//////////////////////////////////////////////////////////////////////////////////////
///////////////////////      Feed Fetch Functions       //////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

/**
 *  Function for creating cache key
 *  
 *  @ Since 1.2.2
 */
  function key_maker( $array ){
    if( isset($array['name']) && is_array( $array['info'] ) ){
      $return = $array['name'];
      foreach( $array['info'] as $key=>$val ){
        $return = $return."-".(!empty($val)?$val:$key);
      }
      $return = $this->filter_filename( $return );
      return $return;
    }
  }
/**
 *  Filter string and remove specified characters
 *  
 *  @ Since 1.2.2
 */  
  function filter_filename( $name ){
    $name = @ereg_replace('[[:cntrl:]]', '', $name ); // remove ASCII's control characters
    $bad = array_merge(
      array_map('chr', range(0,31)),
      array("<",">",":",'"',"/","\\","|","?","*"," ",",","\'",".")); 
    $return = str_replace($bad, "", $name); // Remove Windows filename prohibited characters
    return $return;
  }
  
//////////////////////////////////////////////////////////////////////////////////////
/////////////////////////      Cache Functions       /////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

/**
 * Functions for retrieving results from cache
 *  
 * @ Since 1.2.4
 *
 */
  function retrieve_from_cache( $key ){
    if ( !$this->check_active_option('cache_disable') ) {
      if( $this->cacheExists($key) ) {
        $results = $this->getCache($key);
        $results = @unserialize($results);
        if( count($results) ){
          $results['hidden'] .= '<!-- Retrieved from cache -->';
          $this->set_private('results',$results);
          if( $this->check_active_result('photos') ){
            return true;
          }
        }
      }
    }
    return false;
  }
/**
 * Functions for storing results in cache
 *  
 * @ Since 1.2.4
 *
 */
  function store_in_cache( $key ){
    if( $this->check_active_result('success') && !$this->check_active_option('disable_cache') ){     
      $cache_results = $this->get_private('results');
      if(!is_serialized( $cache_results  )) { $cache_results  = @maybe_serialize( $cache_results ); }
      $this->putCache($key, $cache_results);
      $cachetime = $this->get_option( 'cache_time' );
      if( !empty($cachetime) && is_numeric($cachetime) ){
        $this->setExpiryInterval( $cachetime*60*60 );
      }
    }
  }

/**
 * Functions for caching results and clearing cache
 *  
 * @since 1.1.0
 *
 */
  function setCacheDir($val) {  $this->set_private('cacheDir',$val); }  
  function setExpiryInterval($val) {  $this->set_private('expiryInterval',$val); }  
  function getExpiryInterval($val) {  return (int)$this->get_private('expiryInterval'); }
  
  function cacheExists($key) {  
    $filename_cache = $this->get_private('cacheDir') . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->get_private('cacheDir') . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info)) {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->get_private('expiryInterval'); //Last update time of the cache file  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time) {//Compare last updated and current time  
        return true;  
      }  
    }
    return false;  
  } 

  function getCache($key)  {  
    $filename_cache = $this->get_private('cacheDir') . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->get_private('cacheDir') . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info))  {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->get_private('expiryInterval'); //Last update time of the cache file  
      $time = time(); //Current Time  

      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time){ //Compare last updated and current time 
        return file_get_contents ($filename_cache);   //Get contents from file  
      }  
    }
    return null;  
  }  

  function putCache($key, $content) {  
    $time = time(); //Current Time  
    $dir = $this->get_private('cacheDir');
    if ( !file_exists($dir) ){  
      @mkdir($dir);  
      $cleaning_info = $dir . '/cleaning.info'; //Cache info 
      @file_put_contents ($cleaning_info , $time); // save the time of last cache update  
    }
    
    if ( file_exists($dir) && is_dir($dir) ){
      $filename_cache = $dir . '/' . $key . '.cache'; //Cache filename  
      $filename_info = $dir . '/' . $key . '.info'; //Cache info  
    
      @file_put_contents($filename_cache ,  $content); // save the content  
      @file_put_contents($filename_info , $time); // save the time of last cache update  
    }
  }
  
  function clearAllCache() {
    $dir = $this->get_private('cacheDir') . '/';
    if(is_dir($dir)){
      $opendir = @opendir($dir);
      while(false !== ($file = readdir($opendir))) {
        if($file != "." && $file != "..") {
          if(file_exists($dir.$file)) {
            $file_array = @explode('.',$file);
            $file_type = @array_pop( $file_array );
            // only remove cache or info files
            if( 'cache' == $file_type || 'info' == $file_type){
              @chmod($dir.$file, 0777);
              @unlink($dir.$file);
            }
          }
          /*elseif(is_dir($dir.$file)) {
            @chmod($dir.$file, 0777);
            @chdir('.');
            @destroy($dir.$file.'/');
            @rmdir($dir.$file);
          }*/
        }
      }
      @closedir($opendir);
    }
  }
  
  function cleanCache() {
    $cleaning_info = $this->get_private('cacheDir') . '/cleaning.info'; //Cache info     
    if (file_exists($cleaning_info))  {  
      $cache_time = file_get_contents ($cleaning_info) + (int)$this->cleaningInterval; //Last update time of the cache cleaning  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  
      if ((int)$cache_time < (int)$expiry_time){ //Compare last updated and current time     
        // Clean old files
        $dir = $this->get_private('cacheDir') . '/';
        if(is_dir($dir)){
          $opendir = @opendir($dir);
          while(false !== ($file = readdir($opendir))) {                            
            if($file != "." && $file != "..") {
              if(is_dir($dir.$file)) {
                //@chmod($dir.$file, 0777);
                //@chdir('.');
                //@destroy($dir.$file.'/');
                //@rmdir($dir.$file);
              }
              elseif(file_exists($dir.$file)) {
                $file_array = @explode('.',$file);
                $file_type = @array_pop( $file_array );
                $file_key = @implode( $file_array );
                if( $file_type && $file_key && 'info' == $file_type){
                  $filename_cache = $dir . $file_key . '.cache'; //Cache filename  
                  $filename_info = $dir . $file_key . '.info'; //Cache info   
                  if (file_exists($filename_cache) && file_exists($filename_info)) {  
                    $cache_time = file_get_contents ($filename_info) + (int)$this->cleaningInterval; //Last update time of the cache file  
                    $expiry_time = (int)$time; //Expiry time for the cache  
                    if ((int)$cache_time < (int)$expiry_time) {//Compare last updated and current time  
                      @chmod($filename_cache, 0777);
                      @unlink($filename_cache);
                      @chmod($filename_info, 0777);
                      @unlink($filename_info);
                    }  
                  }
                  /*elseif (file_exists($filename_cache) && file_exists($filename_info)) {  
                    $cache_time = file_get_contents ($filename_info) + (int)$this->cleaningInterval; //Last update time of the cache file  
                    $expiry_time = (int)$time; //Expiry time for the cache  
                    if ((int)$cache_time < (int)$expiry_time) {//Compare last updated and current time  
                      @chmod($filename_cache, 0777);
                      @unlink($filename_cache);
                      @chmod($filename_info, 0777);
                      @unlink($filename_info);
                    } 
                  }*/
                }
              }
            }
          }
          @closedir($opendir);
        }
        @file_put_contents ($cleaning_info , $time); // save the time of last cache cleaning        
      }
    }
  } 
  
  /*
  function putCacheImage($image_url){
    $time = time(); //Current Time  
    if ( ! file_exists($this->cacheDir) ){  
      @mkdir($this->cacheDir);  
      $cleaning_info = $this->cacheDir . '/cleaning.info'; //Cache info 
      @file_put_contents ($cleaning_info , $time); // save the time of last cache update  
    }
    
    if ( file_exists($this->cacheDir) && is_dir($this->cacheDir) ){ 
      //replace with your cache directory
      $dir = $this->cacheDir.'/';
      //get the name of the file
      $exploded_image_url = explode("/",$image_url);
      $image_filename = end($exploded_image_url);
      $exploded_image_filename = explode(".",$image_filename);
      $name = current($exploded_image_filename);
      $extension = end($exploded_image_filename);
      //make sure its an image
      if($extension=="gif"||$extension=="jpg"||$extension=="png"){
        //get the remote image
        $image_to_fetch = @file_get_contents($image_url);
        //save it
        $filename_image = $dir . $image_filename;
        $filename_info = $dir . $name . '.info'; //Cache info  
      
        $local_image_file = @fopen($filename_image, 'w+');
        @chmod($dir.$image_filename,0755);
        @fwrite($local_image_file, $image_to_fetch);
        @fclose($local_image_file);
        
        @file_put_contents($filename_info , $time); // save the time of last cache update  
      }
    }
  }
  
  function getImageCache($image_url)  {  
    $dir = $this->cacheDir.'/';
  
    $exploded_image_url = explode("/",$image_url);
    $image_filename = end($exploded_image_url);
    $exploded_image_filename = explode(".",$image_filename);
    $name = current($exploded_image_filename);  
    $filename_image = $dir . $image_filename;
    $filename_info = $dir . $name . '.info'; //Cache info  
  
    if (file_exists($filename_image) && file_exists($filename_info))  {  
      $cache_time = @file_get_contents ($filename_info) + (int)$this->expiryInterval; //Last update time of the cache file  
      $time = time(); //Current Time  

      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time){ //Compare last updated and current time 
        return $this->cacheUrl.'/'.$image_filename;   // Return image URL
      }else{
        $local_image_file = @fopen($filename_image, 'w+');
        @chmod($dir.$image_filename,0755);
        @fwrite($local_image_file, $image_to_fetch);
        @fclose($local_image_file);
        
        @file_put_contents($filename_info , $time); // save the time of last cache update  
      }
    }elseif( $this->cacheAttempts < $this->cacheLimit ){
      $this->putCacheImage($image_url);
      $this->cacheAttempts++;
    }
    return null;  
  }  
  */
}

/** ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *   
 *    AlpineBot Tertiary
 * 
 *    Display functions
 *    Contains ONLY UNIQUE functions
 * 
 *  ##########################################################################################
 */
 
class PhotoTileForPinterestBotTertiary extends PhotoTileForPinterestBotSecondary{ 

//////////////////////////////////////////////////////////////////////////////////////
//////////////////        Unique Feed Fetch Functions        /////////////////////////
//////////////////////////////////////////////////////////////////////////////////////    

/**
 * Alpine PhotoTile for Pinterest: Photo Retrieval Function.
 * The PHP for retrieving content from Pinterest.
 *
 * @ Since 1.0.0
 * @ Updated 1.2.5
 */  
  function photo_retrieval(){
    $pinterest_options = $this->get_private('options');
    $defaults = $this->option_defaults();

    $pinterest_uid = (isset($pinterest_options['pinterest_user_id'])?$pinterest_options['pinterest_user_id']:'');
    
    $key_input = array(
      'name' => 'pinterest',
      'info' => array(
        'vers' => $this->get_private('vers'),
        'src' => (isset($pinterest_options['pinterest_source'])?$pinterest_options['pinterest_source']:''),
        'uid' => $pinterest_uid,
        'board' => (isset($pinterest_options['pinterest_user_board'])?$pinterest_options['pinterest_user_board']:''),
        'num' => (isset($pinterest_options['pinterest_photo_number'])?$pinterest_options['pinterest_photo_number']:''),
        'off' => (isset($pinterest_options['photo_feed_offset'])?$pinterest_options['photo_feed_offset']:''),
        'link' => (isset($pinterest_options['pinterest_display_link'])?$pinterest_options['pinterest_display_link']:''),
        'text' => (isset($pinterest_options['pinterest_display_link_text'])?$pinterest_options['pinterest_display_link_text']:''),
        'style' => (isset($pinterest_options['pinterest_display_link_style'])?$pinterest_options['pinterest_display_link_style']:''),
        'size' => (isset($pinterest_options['pinterest_photo_size'])?$pinterest_options['pinterest_photo_size']:'')
        )
      );
    $key = $this->key_maker( $key_input );  // Make Key
    if( $this->retrieve_from_cache( $key ) ){  return; } // Check Cache
    $this->set_size_id(); // Set image size (translate size to Pinterest id)
    
    $this->append_active_result('hidden','<!-- Using AlpinePT for Pinterest v'.$this->get_private('ver').' with Pinterest RSS -->');

    if( function_exists('simplexml_load_string') ) {
      $this->try_simplexmlstring();
    }
    
    if( !$this->check_active_result('success') && function_exists('simplexml_load_file') ) {
      $this->try_simplexml();
    }
    
    if ( !$this->check_active_result('success') ) {
      $this->try_wp_fetch_feed();
    }

    if( $this->check_active_result('success') ){
      if( !empty($pinterest_options['pinterest_display_link']) ) {
        $linkStyle = isset($pinterest_options['pinterest_display_link_style'])?$pinterest_options['pinterest_display_link_style']:'';
        $userlink = '';
        if($linkStyle == 'large') { 
          $userlink .= '<div class="AlpinePhotoTiles-display-link" ><a href="http://pinterest.com/'. $pinterest_uid .'/" target="_blank" title="Follow Me on Pinterest">';
          $userlink .= '<img src="http://passets-cdn.pinterest.com/images/follow-on-pinterest-button.png" alt="Follow Me on Pinterest" border="0" class="AlpinePhotoTiles-image-link"/>';
          $userlink .= '</a></div>';
        }elseif ($linkStyle == 'medium') { 
          $userlink .= '<div class="AlpinePhotoTiles-display-link" ><a href="http://pinterest.com/'. $pinterest_uid .'/" target="_blank" title="Follow Me on Pinterest">';
          $userlink .= '<img src="http://passets-cdn.pinterest.com/images/pinterest-button.png" alt="Follow Me on Pinterest" border="0" class="AlpinePhotoTiles-image-link" />';
          $userlink .= '</a></div>';
        }elseif ($linkStyle == 'small') { 
          $userlink .= '<div class="AlpinePhotoTiles-display-link" ><a href="http://pinterest.com/'. $pinterest_uid .'/" target="_blank" title="Follow Me on Pinterest">';
          $userlink .= '<img src="http://passets-cdn.pinterest.com/images/big-p-button.png" width="61" height="61" alt="Follow Me on Pinterest" border="0" class="AlpinePhotoTiles-image-link" />';
          $userlink .= '</a></div>';
        }elseif ($linkStyle == 'tiny') { 
          $userlink .= '<div class="AlpinePhotoTiles-display-link" ><a href="http://pinterest.com/'. $pinterest_uid .'/" target="_blank" title="Follow Me on Pinterest" >';
          $userlink .= '<img src="http://passets-cdn.pinterest.com/images/small-p-button.png" width="16" height="16" alt="Follow Me on Pinterest" border="0" class="AlpinePhotoTiles-image-link"/>';
          $userlink .= '</a></div>';
        }elseif ($linkStyle == 'text' && isset($pinterest_options['pinterest_display_link_text']) ) {
          $userlink .= '<div class="AlpinePhotoTiles-display-link" >';
          $userlink .= '<a href="http://pinterest.com/'.$pinterest_uid.'/" target="_blank" >';
          $userlink .= $pinterest_options['pinterest_display_link_text'];
          $userlink .= '</a></div>';
        }else {
          $userlink .= '<div class="AlpinePhotoTiles-display-link" >';
          $userlink .= '<a href="http://pinterest.com/'.$pinterest_uid .'/" target="_blank" >';
          $userlink .= "Follow Me on Pinterest";
          $userlink .= '</a></div>';
        }
        $this->set_active_result('userlink',$userlink);
      }else{
        $this->set_active_result('userlink',null);
      }
    }else{
      if( $this->check_active_result('feed_found') ){
        $this->append_active_result('message','- Pinterest feed was successfully retrieved, but no photos found.');
      }else{
        $this->append_active_result('message','- Please recheck your ID.');
      }
    }
    
    $this->store_in_cache( $key );  // Store in cache

  }
/**
 *  Function for forming Pinterest request
 *  
 *  @ Since 1.2.4
 */ 
  function get_pinterest_request(){
    $options = $this->get_private('options');
    
    $pinterest_uid = empty($options['pinterest_user_id']) ? '' : $options['pinterest_user_id'];
    $pinterest_src = empty($options['pinterest_source']) ? '' : $options['pinterest_source'];
    if( 'board' == $pinterest_src ){
      $pinterest_board = empty($options['pinterest_user_board']) ? '' : $options['pinterest_user_board'];
      $request = 'http://pinterest.com/'.$pinterest_uid.'/'.$pinterest_board.'/rss';
    }else{
      $request = 'http://pinterest.com/'.$pinterest_uid.'/feed.rss';
    }
    return $request;
 }
/**
 *  Determine image size id
 *  
 *  @ Since 1.2.4
 *  @ Updated 1.2.5
 */
  function set_size_id(){
    $this->set_active_option('size_id','/192'); // Default is 192

    switch ($this->get_active_option('pinterest_photo_size')) {
      case 75:
        $this->set_active_option('size_id','/75x75');
      break;
      case 192:
        $this->set_active_option('size_id','/192');
      break;
      case 554:
        $this->set_active_option('size_id','/550');
      break;
      case 600:
        $this->set_active_option('size_id','/600');
      break;
      case 930:
        $this->set_active_option('size_id','/600');
      break;
    }
  }    

/**
 *  Function for making Pinterest RSS request using wp_remote_get and simplexml_load_string()
 *  
 *  @ Since 1.2.4
 *  @ Updated 1.2.6
 */
  function try_simplexmlstring(){
  
    $options = $this->get_private('options');
    $request = $this->get_pinterest_request();
    $xml_string = '';
    // No longer write out curl_init and user WP API instead
    $response = wp_remote_get($request,
      array(
        'method' => 'GET',
        'timeout' => 20
      )
    );
    $this->append_active_result('hidden','<!-- Request made -->');
    if( is_wp_error( $response ) || !isset($response['body']) ) {
      $this->append_active_result('hidden','<!-- Failed using wp_remote_get(), simplexml_load_string() and XML @ '.$request.' -->');
      if ( is_wp_error( $response ) ) {
        $error_string = $response->get_error_message();
        $this->append_active_result('hidden','<!-- WP Error message: '.$error_string.' -->');
      }
      return false;
    }else{
      $xml_string = $response['body'];
    }
    
    $_pinterest_xml = @simplexml_load_string( $xml_string ); // @ is shut-up operator
    if( $_pinterest_xml===false || empty($_pinterest_xml) ){ 
      $this->append_active_result('hidden','<!-- Failed using wp_remote_get(), simplexml_load_string() and XML @ '.$request.' -->');
      $this->set_active_result('success',false);
    }else{
      $title = isset($_pinterest_xml->channel->title)?$_pinterest_xml->channel->title:'';
      $link = isset($_pinterest_xml->channel->link)?$_pinterest_xml->channel->link:'';
      $channel = isset($_pinterest_xml->channel)?$_pinterest_xml->channel:'';
      $items = isset($_pinterest_xml->channel->item)?$_pinterest_xml->channel->item:'';

      if( empty($_pinterest_xml) || empty($channel) || empty($items) ){
        $this->append_active_result('hidden','<!-- Check 1: No photos found using wp_remote_get(), simplexml_load_string() and XML @ '.$request.' -->');
        $this->set_active_result('success',false);
      }else{
        $photos = array();
        $size_id = $this->get_active_option('size_id');
        foreach( $_pinterest_xml->channel->item as $p ) { // This will prevent empty images from being added to linkurl.
          if( count($photos)<$options['pinterest_photo_number'] ){
            // list of link urls
            $url = isset($p->link)?(string) $p->link:''; // ->i is equivalent of ['i'] for objects
            $content = isset($p->description)?(string) $p->description:'';

            if( !empty($url) && !empty($content) ){
              $the_photo = array();
              $the_photo['image_link'] = $url;
              $the_photo['image_title'] = isset($p->title)?(string) $p->title:'';
              $the_photo['image_title'] = str_replace("'",'',$the_photo['image_title']);
              $the_photo['image_caption'] = '';

              // For Reference: regex references and http://php.net/manual/en/function.preg-match.php
              // Using the RSS feed will require some manipulation to get the image url from pinterest;
              // preg_replace is bad at skipping lines so we'll start with preg_match
                // i sets letters in upper or lower case,
              @preg_match( "/img(.*?)>/i", $content , $matches ); // First, get image from feed.
              // Next, strip away everything surrounding the source url.
                // . means any expression, and + means repeat previous
              if( !empty( $matches[0] ) ){
                $photourl_current = @preg_replace(array('/(.*)src="/i','/"(.*)/') ,'', $matches[0] );
                
                // It is unclear what Pinterest's file structure is.  It keeps changing.
                $the_photo['image_source'] = @str_replace('/192/', $size_id.'/', $photourl_current);
                
                //if( $size_id == '/75' &&  (strpos($the_photo['image_source'],'.pinimg.com') === false)){
                if( $size_id == '/75x75' ){
                  $the_photo['image_source'] = @str_replace('/192x/', '/75x75/', $the_photo['image_source']);
                }else{
                  $the_photo['image_source'] = @str_replace('/192x/', $size_id.'x/', $the_photo['image_source']);
                }
                
                $the_photo['image_original'] = @str_replace('/192/','/600/', $photourl_current);
                $the_photo['image_original'] = @str_replace('/192x/','/600x/', $the_photo['image_original']);

                $photos[] = $the_photo;
                
                // Finally, change the size. [] specifies single character and \w is any word character
                //$the_photo['image_source'] = @preg_replace('/[_]\w[.]/', $size_id, $photourl_current );
                //$originalurl[$s] = @preg_replace('/[_]\w[.]/', '.', $photourl_current );
              }
            }
          }else{
            break;
          }
        } // End foreach
        if( count($photos) ){
          // If content successfully fetched, generate output...
          $this->set_active_result('photos',$photos);
          $this->set_active_result('success',true);
          $this->set_active_result('feed_found',true);
          $this->append_active_result('hidden','<!-- Success using wp_remote_get(), simplexml_load_string() and XML -->');
        }else{
          $this->set_active_result('success',false);
          $this->set_active_result('feed_found',true);
          $this->append_active_result('hidden','<!-- Check 2: No photos found using wp_remote_get(), simplexml_load_string() and XML @ '.$request.' -->');
        }
      }
    }
  }
/**
 *  Function for making Pinterest RSS request using simplexml_load_file()
 *  
 *  @ Since 1.2.4
 *  @ Updated 1.2.5
 */
  function try_simplexml(){
    // Retrieve content using wp_remote_get and PHP_serial
    $request = $this->get_pinterest_request();
    $options = $this->get_private('options');
    
    $_pinteresturl  = @urlencode( $request );	// just for compatibility
    $_pinterest_xml = @simplexml_load_file( $_pinteresturl,"SimpleXMLElement",LIBXML_NOCDATA); // @ is shut-up operator
    if( $_pinterest_xml===false || empty($_pinterest_xml) ){ 
      $this->append_active_result('hidden','<!-- Failed using simplexml_load_file() and XML @ '.$request.' -->');
      $this->set_active_result('success',false);
    }else{
      $title = isset($_pinterest_xml->channel->title)?$_pinterest_xml->channel->title:'';
      $link = isset($_pinterest_xml->channel->link)?$_pinterest_xml->channel->link:'';
      $channel = isset($_pinterest_xml->channel)?$_pinterest_xml->channel:'';
      $items = isset($_pinterest_xml->channel->item)?$_pinterest_xml->channel->item:'';

      if( empty($_pinterest_xml) || empty($channel) || empty($items) ){
        $this->append_active_result('hidden','<!-- Check 1: No photos found using simplexml_load_file() and XML @ '.$request.' -->');
        $this->set_active_result('success',false);
      }else{
        $photos = array();
        $size_id = $this->get_active_option('size_id');
        foreach( $_pinterest_xml->channel->item as $p ) { // This will prevent empty images from being added to linkurl.
          if( count($photos)<$options['pinterest_photo_number'] ){
            // list of link urls
            $url = isset($p->link)?(string) $p->link:''; // ->i is equivalent of ['i'] for objects
            $content = isset($p->description)?(string) $p->description:'';

            if( !empty($url) && !empty($content) ){
              $the_photo = array();
              $the_photo['image_link'] = $url;
              $the_photo['image_title'] = isset($p->title)?(string) $p->title:'';
              $the_photo['image_title'] = str_replace("'",'',$the_photo['image_title']);
              $the_photo['image_caption'] = '';

              // For Reference: regex references and http://php.net/manual/en/function.preg-match.php
              // Using the RSS feed will require some manipulation to get the image url from pinterest;
              // preg_replace is bad at skipping lines so we'll start with preg_match
                // i sets letters in upper or lower case,
              @preg_match( "/img(.*?)>/i", $content , $matches ); // First, get image from feed.
              // Next, strip away everything surrounding the source url.
                // . means any expression, and + means repeat previous
              if( !empty( $matches[0] ) ){
                $photourl_current = @preg_replace(array('/(.*)src="/i','/"(.*)/') ,'', $matches[0] );
                
                // It is unclear what Pinterest's file structure is.  It keeps changing.
                $the_photo['image_source'] = @str_replace('/192/', $size_id.'/', $photourl_current);
                
                //if( $size_id == '/75' &&  (strpos($the_photo['image_source'],'.pinimg.com') === false)){
                if( $size_id == '/75x75' ){
                  $the_photo['image_source'] = @str_replace('/192x/', '/75x75/', $the_photo['image_source']);
                }else{
                  $the_photo['image_source'] = @str_replace('/192x/', $size_id.'x/', $the_photo['image_source']);
                }
                
                $the_photo['image_original'] = @str_replace('/192/','/600/', $photourl_current);
                $the_photo['image_original'] = @str_replace('/192x/','/600x/', $the_photo['image_original']);

                $photos[] = $the_photo;
                
                // Finally, change the size. [] specifies single character and \w is any word character
                //$the_photo['image_source'] = @preg_replace('/[_]\w[.]/', $size_id, $photourl_current );
                //$originalurl[$s] = @preg_replace('/[_]\w[.]/', '.', $photourl_current );
              }
            }
          }else{
            break;
          }
        } // End foreach
        if( count($photos) ){
          // If content successfully fetched, generate output...
          $this->set_active_result('photos',$photos);
          $this->set_active_result('success',true);
          $this->set_active_result('feed_found',true);
          $this->append_active_result('hidden','<!-- Success using simplexml_load_file() and XML -->');
        }else{
          $this->set_active_result('success',false);
          $this->set_active_result('feed_found',true);
          $this->append_active_result('hidden','<!-- Check 2: No photos found using simplexml_load_file() and XML @ '.$request.' -->');
        }
      }
    }
  }
  
/**
 *  Function for making Pinterest RSS request with fetch_feed()
 *  
 *  RSS may actually be safest approach since it does not require PHP server extensions,
 *  but I had to build my own method for parsing SimplePie Object so I will keep it as the last option.  
 *
 *  @ Since 1.2.5
 */
  function try_wp_fetch_feed(){
    $request = $this->get_pinterest_request();
    $options = $this->get_private('options');
    $rss = false;
    
    include_once( ABSPATH . WPINC . '/feed.php' );
    if( !function_exists('return_noCache') ){
      function return_noCache( $seconds ){
        // change the default feed cache recreation period to 30 minutes
        return 1800;
      }
    }
    add_filter( 'wp_feed_cache_transient_lifetime' , 'return_noCache' );
    if( !function_exists('fetch_feed') ){
      $this->append_active_result('hidden','<!-- Function fetch_feed() does not exist -->');
    }
    if( function_exists('fetch_feed') ){
      $rss = @fetch_feed( $request );
    }else{
      $this->append_active_result('hidden','<!-- fetch_feed() not available-->');
    }
    remove_filter( 'wp_feed_cache_transient_lifetime' , 'return_noCache' );
    
    if( is_wp_error( $rss ) || empty($rss) ){
      // If it did not work, report and try using SimplePie directly
      if( is_wp_error( $rss ) ){
        $error_string = $rss->get_error_message();
        $this->append_active_result('hidden','<!-- Failed once using fetch_feed() and RSS @ '.$request.' -->');
        $this->append_active_result('hidden','<!-- WP Error message: '.$error_string.' -->');
      }
      
      // Try somethin else
      include_once (ABSPATH . WPINC . '/class-feed.php');
      if( class_exists('SimplePie') ){
        $this->append_active_result('hidden','<!-- Try SimplePie -->');
        $rss = new SimplePie();
        $rss->set_feed_url( $request );
        $rss->force_feed(true);
        $rss->enable_cache(false);
        $rss->set_timeout(30);
        $rss->init();
        $rss->handle_content_type();
      }else{
        $this->append_active_result('hidden','<!-- SimplePie not available-->');
      }
    }
        
    if( is_wp_error( $rss ) || !empty($rss->error) || empty($rss) ){ // Check that the object is created correctly 
      $this->set_active_result('success',false);
      $this->set_active_result('feed_found',false);
      $this->append_active_result('hidden','<!-- Failed twice using fetch_feed() and RSS @ '.$request.' -->');
      if ( is_wp_error( $rss ) ) {
        $error_string = $rss->get_error_message();
        $this->append_active_result('hidden','<!-- WP Error message: '.$error_string.' -->');
      }elseif( !empty($rss->error) ){
        $this->append_active_result('hidden','<!-- SimplePie Error message: '.$rss->error.' -->');
      }else{
        $this->append_active_result('hidden','<!-- Empty result -->');
      }
    }else{
      // Bulldoze through the feed to find the items 
      $results = array();
      $title = $this->special_array_search($rss,'title');
      $title = isset($title['0']['data'])?$title['0']['data']:'';
      $link = $this->special_array_search($rss,'link');
      $link = isset($link['0']['data'])?$link['0']['data']:'';
      $rss_data = $this->special_array_search($rss,'item');
     
      $photos = array();
      $size_id = $this->get_active_option('size_id');
      if( !empty($rss_data) && is_array($rss_data) ){ // Check again
        foreach ( $rss_data as $item ) {
          if( count($photos)<$options['pinterest_photo_number'] ){
            $content = isset($item['child']['']['description']['0']['data'])?$item['child']['']['description']['0']['data']:'';     
            if( !empty($content) ){
              $the_photo = array();
              $the_photo['image_link'] = isset($item['child']['']['link']['0']['data'])?$item['child']['']['link']['0']['data']:'';
              $the_photo['image_title'] = isset($item['child']['']['title']['0']['data'])?$item['child']['']['title']['0']['data']:'';     
              $the_photo['image_title'] = str_replace("'",'',$the_photo['image_title']);
              $the_photo['image_caption'] = '';
              // For Reference: regex references and http://php.net/manual/en/function.preg-match.php
              // Using the RSS feed will require some manipulation to get the image url from pinterest;
              // preg_replace is bad at skipping lines so we'll start with preg_match
              // i sets letters in upper or lower case, s sets . to anything
              @preg_match("/<IMG.+?SRC=[\"']([^\"']+)/si",$content,$matches); // First, get image from feed.
              if( !empty($matches[0]) ){
                // Next, strip away everything surrounding the source url.
                // . means any expression and + means repeat previous
                $photourl_current = @preg_replace(array('/(.+)src="/i','/"(.+)/') , '',$matches[ 0 ]);
                // Finally, change the size. 

                // It is unclear what Pinterest's file structure is.  It keeps changing.
                $the_photo['image_source'] = @str_replace('/192/', $size_id.'/', $photourl_current);
                
                //if( $size_id == '/75' &&  (strpos($the_photo['image_source'],'.pinimg.com') === false)){
                if( $size_id == '/75x75' ){
                  $the_photo['image_source'] = @str_replace('/192x/', '/75x75/', $the_photo['image_source']);
                }else{
                  $the_photo['image_source'] = @str_replace('/192x/', $size_id.'x/', $the_photo['image_source']);
                }
                
                $the_photo['image_original'] = @str_replace('/192/','/600/', $photourl_current);
                $the_photo['image_original'] = @str_replace('/192x/','/600x/', $the_photo['image_original']);

                $photos[] = $the_photo;
              }
            }
          }
          else{
            break;
          }
        } // End foreach
      } 
      if( count($photos) ){
        // If content successfully fetched, generate output...
        $this->set_active_result('photos',$photos);
        $this->set_active_result('success',true);
        $this->set_active_result('feed_found',true);
        $this->append_active_result('hidden','<!-- Success using fetch_feed() and RSS -->');
      }else{
        $this->set_active_result('success',false);
        $this->set_active_result('feed_found',true);
        $this->append_active_result('hidden','<!-- No photos found using fetch_feed() and RSS @ '.$request.' -->');
      }
    }
  }

/**
 *  Simple Array/Object searcher
 *  
 *  @ Since 1.2.5
 */
  function special_array_search($array, $find){
    $results = null;
    foreach ($array as $key=>$value){
      if( is_string($key) && $key==$find){
        return $value;
      }
      elseif(is_array($value)){
        $results = $this->special_array_search($value, $find);
      }
      elseif(is_object($value)){
        $sub = $array->$key;
        $results = $this->special_array_search($sub, $find);
      }
      // If found, return
      if(!empty($results)){return $results;}
    }
    return $results;
  }
      
}
  
/** ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *   
 *  AlpineBot Display
 * 
 *  Display functions
 *  Try to keep only UNIVERSAL functions
 * 
 */
 
class PhotoTileForPinterestBot extends PhotoTileForPinterestBotTertiary{
/**
 *  Function for printing vertical style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.6.2
 */
  function display_vertical(){
    $this->set_private('out',''); // Clear any output;
    $this->update_count(); // Check number of images found
    $this->randomize_display(); 
    $opts = $this->get_private('options');
    $src = $this->get_private('src');
    $wid = $this->get_private('wid');
                      
    $this->add('<div id="'.$wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">');     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->add('<div id="'.$wid.'-vertical-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">');

        for($i = 0;$i<$opts[$src.'_photo_number'];$i++){
          $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
          $pin = $this->get_option( 'pinterest_pin_it_button' );
          $this->add_image($i,$css,$pin); // Add image
        }
        
        $this->add_credit_link();
      
      $this->add('</div>'); // Close vertical-parent

      $this->add_user_link();

    $this->add('</div>'); // Close container
    $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
    
    // Add Lightbox call (if necessary)
    $this->add_lightbox_call();
    
    $parentID = $wid."-vertical-parent";
    $borderCall = $this->get_borders_call( $parentID );

    if( !empty($opts['style_shadow']) || !empty($opts['style_border']) || !empty($opts['style_highlight'])  ){
      $this->add("
<script>  
  // Check for on() ( jQuery 1.7+ )
  if( jQuery.isFunction( jQuery(window).on ) ){
    jQuery(window).on('load', function(){".$borderCall."}); // Close on()
  }else{
    // Otherwise, use bind()
    jQuery(window).bind('load', function(){".$borderCall."}); // Close bind()
  }
</script>");  
    }
  }  
/**
 *  Function for printing cascade style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.6.2
 */
  function display_cascade(){
    $this->set_private('out',''); // Clear any output;
    $this->update_count(); // Check number of images found
    $this->randomize_display();
    $opts = $this->get_private('options');
    $wid = $this->get_private('wid');
    $src = $this->get_private('src');
    
    $this->add('<div id="'.$wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">');     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->add('<div id="'.$wid.'-cascade-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">');
      
        for($col = 0; $col<$opts['style_column_number'];$col++){
          $this->add('<div class="AlpinePhotoTiles_cascade_column" style="width:'.(100/$opts['style_column_number']).'%;float:left;margin:0;">');
          $this->add('<div class="AlpinePhotoTiles_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">');
          for($i = $col;$i<$opts[$src.'_photo_number'];$i+=$opts['style_column_number']){
            $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
            $pin = $this->get_option( 'pinterest_pin_it_button' );
            $this->add_image($i,$css,$pin); // Add image
          }
          $this->add('</div></div>');
        }
        $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
          
        $this->add_credit_link();
      
      $this->add('</div>'); // Close cascade-parent

      $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
      
      $this->add_user_link();

    // Close container
    $this->add('</div>');
    $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
    
    // Add Lightbox call (if necessary)
    $this->add_lightbox_call();
    
    $parentID = $wid."-cascade-parent";
    $borderCall = $this->get_borders_call( $parentID );

    if( !empty($opts['style_shadow']) || !empty($opts['style_border']) || !empty($opts['style_highlight'])  ){
      $this->add("
<script>
  // Check for on() ( jQuery 1.7+ )
  if( jQuery.isFunction( jQuery(window).on ) ){
    jQuery(window).on('load', function(){".$borderCall."}); // Close on()
  }else{
    // Otherwise, use bind()
    jQuery(window).bind('load', function(){".$borderCall."}); // Close bind()
  }
</script>");  
    }
  }
/**
 *  Get jQuery borders plugin string
 *  
 *  @ Since 1.2.6.2
 */
  function get_borders_call( $parentID ){
    $highlight = $this->get_option("general_highlight_color");
    $highlight = (!empty($highlight)?$highlight:'#64a2d8');
    
    $return = "
      if( jQuery().AlpineAdjustBordersPlugin ){
        jQuery('#".$parentID."').AlpineAdjustBordersPlugin({
          highlight:'".$highlight."'
        });
      }else{
        var css = '".($this->get_private('url').'/css/'.$this->get_private('wcss').'.css')."';
        var link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
        jQuery.getScript('".($this->get_private('url').'/js/'.$this->get_private('wjs').'.js')."', function(){
          if(document.createStyleSheet){
            document.createStyleSheet(css);
          }else{
            jQuery('head').append(link);
          }
          if( jQuery().AlpineAdjustBordersPlugin ){
            jQuery('#".$parentID."').AlpineAdjustBordersPlugin({
              highlight:'".$highlight."'
            });
          }
        }); // Close getScript
      }
    ";
    return $return;
  }
/**
 *  Function for printing and initializing JS styles
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.6.2
 */
  function display_hidden(){
    $this->set_private('out',''); // Clear any output;
    $this->update_count(); // Check number of images found
    $this->randomize_display();
    $opts = $this->get_private('options');
    $wid = $this->get_private('wid');
    $src = $this->get_private('src');
    
    $this->add('<div id="'.$wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">');     
      // Align photos
      $css = $this->get_parent_css();
      $this->add('<div id="'.$wid.'-hidden-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">');
      
        $this->add('<div id="'.$wid.'-image-list" class="AlpinePhotoTiles_image_list_class" style="display:none;visibility:hidden;">'); 
        
          for($i=0;$i<$opts[$src.'_photo_number'];$i++){

            $this->add_image($i); // Add image
            
            // Load original image size
            $original = $this->get_photo_info($i,'image_original');
            if( isset($opts['style_option']) && "gallery" == $opts['style_option'] && !empty( $original ) ){
              $this->add('<img class="AlpinePhotoTiles-original-image" src="' . $original . '" />');
            }
          }
        $this->add('</div>');
        
        $this->add_credit_link();       
      
      $this->add('</div>'); // Close parent  

      $this->add_user_link();
      
    $this->add('</div>'); // Close container
    
    $disable = $this->get_option("general_loader");

    $lightbox = $this->get_option('general_lightbox');
    $prevent = $this->get_option('general_lightbox_no_load');    
    $hasLight = false;
    $lightScript = '';
    $lightStyle = '';
    if( empty($prevent) && isset($opts[$this->get_private('src').'_image_link_option']) && $opts[$src.'_image_link_option'] == 'fancybox' ){
      $lightScript = $this->get_script( $lightbox );
      $lightStyle = $this->get_style( $lightbox );
      if( !empty($lightScript) && !empty($lightStyle) ){
        $hasLight = true;
      }
    }
    
    $this->add('<script>');
      if(!$disable){
        $this->add(
    "
    jQuery(document).ready(function() {
      jQuery('#".$wid."-AlpinePhotoTiles_container').addClass('loading'); 
    });
    ");
    
      }
  
    $pluginCall = $this->get_loading_call($opts,$wid,$src,$lightbox,$hasLight,$lightScript,$lightStyle);
    
    $this->add("
    // Check for on() ( jQuery 1.7+ )
    if( jQuery.isFunction( jQuery(window).on ) ){
      jQuery(window).on('load', function(){".$pluginCall."});
    }else{ 
      // Otherwise, use bind()
      jQuery(window).bind('load', function(){".$pluginCall."});
    }
</script>");    
 
  }
/**
 *  Get jQuery loading string
 *  
 *  @ Since 1.2.6.2
 */
  function get_loading_call($opts,$wid,$src,$lightbox,$hasLight,$lightScript,$lightStyle){
    $return = "
        jQuery('#".$wid."-AlpinePhotoTiles_container').removeClass('loading');
        
        var alpineLoadPlugin = function(){".$this->get_plugin_call($opts,$wid,$src,$hasLight)."}
        
        // Load Alpine Plugin
        if( jQuery().AlpinePhotoTilesPlugin ){
          alpineLoadPlugin();
        }else{ // Load Alpine Script and Style
          var css = '".($this->get_private('url').'/css/'.$this->get_private('wcss').'.css')."';
          var link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
          jQuery.getScript('".($this->get_private('url').'/js/'.$this->get_private('wjs').'.js')."', function(){
            if(document.createStyleSheet){
              document.createStyleSheet(css);
            }else{
              jQuery('head').append(link);
            }";
          if( $hasLight ){    
          $check = ($lightbox=='fancybox'?'fancybox':($lightbox=='prettyphoto'?'prettyPhoto':($lightbox=='colorbox'?'colorbox':'fancyboxForAlpine')));    
          $return .="
            if( !jQuery().".$check." ){ // Load Lightbox
              jQuery.getScript('".$lightScript."', function(){
                css = '".$lightStyle."';
                link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
                if(document.createStyleSheet){
                  document.createStyleSheet(css);
                }else{
                  jQuery('head').append(link);
                }
                alpineLoadPlugin();
              }); // Close getScript
            }else{
              alpineLoadPlugin();
            }";
          }else{
            $return .= "
            alpineLoadPlugin();";
          }
            $return .= "
          }); // Close getScript
        }
      ";
    return $return;
  }
/**
 *  Get jQuery plugin string
 *  
 *  @ Since 1.2.6.2
 */
  function get_plugin_call($opts,$wid,$src,$hasLight){
    $highlight = $this->get_option("general_highlight_color");
    $highlight = (!empty($highlight)?$highlight:'#64a2d8');
    $return = "
          jQuery('#".$wid."-hidden-parent').AlpinePhotoTilesPlugin({
            id:'".$wid."',
            style:'".(isset($opts['style_option'])?$opts['style_option']:'windows')."',
            shape:'".(isset($opts['style_shape'])?$opts['style_shape']:'square')."',
            perRow:".(isset($opts['style_photo_per_row'])?$opts['style_photo_per_row']:'3').",
            imageBorder:".(!empty($opts['style_border'])?'1':'0').",
            imageShadow:".(!empty($opts['style_shadow'])?'1':'0').",
            imageCurve:".(!empty($opts['style_curve_corners'])?'1':'0').",
            imageHighlight:".(!empty($opts['style_highlight'])?'1':'0').",
            lightbox:".((isset($opts[$src.'_image_link_option']) && $opts[$src.'_image_link_option'] == 'fancybox')?'1':'0').",
            galleryHeight:".(isset($opts['style_gallery_height'])?$opts['style_gallery_height']:'0').", // Keep for Compatibility
            galRatioWidth:".(isset($opts['style_gallery_ratio_width'])?$opts['style_gallery_ratio_width']:'800').",
            galRatioHeight:".(isset($opts['style_gallery_ratio_height'])?$opts['style_gallery_ratio_height']:'600').",
            highlight:'".$highlight."',
            pinIt:".(!empty($opts['pinterest_pin_it_button'])?'1':'0').",
            siteURL:'".get_option( 'siteurl' )."',
            callback: ".(!empty($hasLight)?'function(){'.$this->get_lightbox_call().'}':"''")."
          });
        ";
    return $return;
  }
 
/**
 *  Update photo number count
 *  
 *  @ Since 1.2.2
 */
  function update_count(){
    $src = $this->get_private('src');
    $found = ( $this->check_active_result('photos') && is_array($this->get_active_result('photos') ))?count( $this->get_active_result('photos') ):0;
    $num = $this->get_active_option( $src.'_photo_number' );
    $this->set_active_option( $src.'_photo_number', min( $num, $found ) );
  }  
/**
 *  Function for shuffleing photo feed
 *  
 *  @ Since 1.2.4
 */
  function randomize_display(){
    if( $this->check_active_option('photo_feed_shuffle') && function_exists('shuffle') ){ // Shuffle the results
      $photos = $this->get_active_result('photos');
      @shuffle( $photos );
      $this->set_active_result('photos',$photos);
    }  
  }  
/**
 *  Get Parent CSS
 *  
 *  @ Since 1.2.2
 *  @ Updated 1.2.5
 */
  function get_parent_css(){
    $max = $this->check_active_option('widget_max_width')?$this->get_active_option('widget_max_width'):100;
    $return = 'width:100%;max-width:'.$max.'%;padding:0px;';
    $align = $this->check_active_option('widget_alignment')?$this->get_active_option('widget_alignment'):'';
    if( 'center' == $align ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'margin:0px auto;text-align:center;';
    }
    elseif( 'right' == $align  || 'left' == $align  ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'float:' . $align  . ';text-align:' . $align  . ';';
    }
    else{
      $return .= 'margin:0px auto;text-align:center;';
    }
    return $return;
 }
 
/**
 *  Add Image Function
 *  
 *  @ Since 1.2.2
 *  @ Updated 1.2.4
 ** Possible change: place original image as 'alt' and load image as needed
 */
  function add_image($i,$css="",$pin=false){
    $light = $this->get_option( 'general_lightbox' );
    $title = $this->get_photo_info($i,'image_title');
    $src = $this->get_photo_info($i,'image_source');
    $shadow = ($this->check_active_option('style_shadow')?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($this->check_active_option('style_border')?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($this->check_active_option('style_curve_corners')?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $highlight = ($this->check_active_option('style_highlight')?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    $onContextMenu = ($this->check_active_option('general_disable_right_click')?'onContextMenu="return false;"':'');
    
    if( $pin ){ $this->add('<div class="AlpinePhotoTiles-pinterest-container" style="position:relative;display:block;" >'); }
    
    //$src = $this->getImageCache( $this->photos[$i]['image_source'] );
    //$src = ( $src?$src:$this->photos[$i]['image_source']);
    
    $has_link = $this->get_link($i); // Add link
    $this->add('<img id="'.$this->get_private('wid').'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="' . $src . '" ');
    $this->add('title='."'". $title ."'".' alt='."'". $title ."' "); // Careful about caps with ""
    $this->add('border="0" hspace="0" vspace="0" style="'.$css.'" '.$onContextMenu.' />'); // Override the max-width set by theme
    if( $has_link ){ $this->add('</a>'); } // Close link
    
    if( $pin ){ 
      $original = $this->get_photo_info($i,'image_original');
      $this->add('<a href="http://pinterest.com/pin/create/button/?media='.$original.'&url='.get_option( 'siteurl' ).'" class="AlpinePhotoTiles-pin-it-button" count-layout="horizontal" target="_blank">');
      $this->add('<div class="AlpinePhotoTiles-pin-it"></div></a>');
      $this->add('</div>'); 
    }
  }
/**
 *  Get Image Link
 *  
 *  @ Since 1.2.2
 *  @ Updated 1.2.6.2
 */
  function get_link($i){
    $src = $this->get_private('src');
    $link = $this->get_active_option($src.'_image_link_option');
    $url = $this->get_active_option('custom_link_url');

    $phototitle = $this->get_photo_info($i,'image_title'); 
    $photourl = $this->get_photo_info($i,'image_source');
    $linkurl = $this->get_photo_info($i,'image_link');
    $originalurl = $this->get_photo_info($i,'image_original');

    if( 'original' == $link && !empty($photourl) ){
      $this->add('<a href="' . $photourl . '" class="AlpinePhotoTiles-link" target="_blank" title=" '. $phototitle .' " alt=" '. $phototitle .' ">');
      return true;
    }elseif( ($src == $link || '1' == $link) && !empty($linkurl) ){
      $this->add('<a href="' . $linkurl . '" class="AlpinePhotoTiles-link" target="_blank" title=" '. $phototitle .' " alt=" '. $phototitle .' ">');
      return true;
    }elseif( 'link' == $link && !empty($url) ){
      $this->add('<a href="' . $url . '" class="AlpinePhotoTiles-link" title=" '. $phototitle .' " alt=" '. $phototitle .' ">'); 
      return true;
    }elseif( 'fancybox' == $link && !empty($originalurl) ){
      $light = $this->get_option( 'general_lightbox' );
      $this->add('<a href="' . $originalurl . '" class="AlpinePhotoTiles-link AlpinePhotoTiles-lightbox" title=" '. $phototitle .' " alt=" '. $phototitle .' ">'); 
      return true;
    }  
    return false;    
  }
/**
 *  Credit Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_credit_link(){
    if( !$this->get_active_option('widget_disable_credit_link') ){
      $this->add('<div id="'.$this->get_private('wid').'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>');
    }  
  }
  
/**
 *  User Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_user_link(){
    if( $this->check_active_result('userlink') ){
      $userlink = $this->get_active_result('userlink');
      if($this->get_active_option('widget_alignment') == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $this->add('<div id="'.$this->get_private('wid').'-display-link" class="AlpinePhotoTiles-display-link-container" ');
        $this->add('style="width:100%;margin:0px auto;">'.$userlink.'</div>');
      }
      else{
        $this->add('<div id="'.$this->get_private('wid').'-display-link" class="AlpinePhotoTiles-display-link-container" ');
        $this->add('style="float:'.$this->get_active_option('widget_alignment').';max-width:'.$this->get_active_option('widget_max_width').'%;"><center>'.$userlink.'</center></div>'); 
        $this->add('<div class="AlpinePhotoTiles_breakline"></div>'); // Only breakline if floating
      }
    }
  }
  
/**
 *  Setup Lightbox call
 *  
 *  @ Since 1.2.3
 *  @ Updated 1.2.6.2
 */
  function add_lightbox_call(){
    $src = $this->get_private('src');
    $lightbox = $this->get_option('general_lightbox');
    $prevent = $this->get_option('general_lightbox_no_load');
    $check = ($lightbox=='fancybox'?'fancybox':($lightbox=='prettyphoto'?'prettyPhoto':($lightbox=='colorbox'?'colorbox':'fancyboxForAlpine')));
    if( empty($prevent) && $this->check_active_option($src.'_image_link_option') && $this->get_active_option($src.'_image_link_option') == 'fancybox' ){
      $lightScript = $this->get_script( $lightbox );
      $lightStyle = $this->get_style( $lightbox );
      if( !empty($lightScript) && !empty($lightStyle) ){
        $lightCall = $this->get_lightbox_call();
        $lightboxSetup = "
      if( !jQuery().".$check." ){
        var css = '".$lightStyle."';
        var link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
        jQuery.getScript('".($lightScript)."', function(){
          if(document.createStyleSheet){
            document.createStyleSheet(css);
          }else{
            jQuery('head').append(link);
          }
          ".$lightCall."
        }); // Close getScript
      }else{
        ".$lightCall."
      }
    ";
        $this->add("
  <script>
  // Check for on() ( jQuery 1.7+ )
  if( jQuery.isFunction( jQuery(window).on ) ){
    jQuery(window).on('load', function(){".$lightboxSetup."}); // Close on()
  }else{
    // Otherwise, use bind()
    jQuery(window).bind('load', function(){".$lightboxSetup."}); // Close bind()
  }
  </script>"); 
      }
    }
  }
  
/**
 *  Get Lightbox Call
 *  
 *  @ Since 1.2.3
 *  @ Updated 1.2.5
 */
  function get_lightbox_call(){
    $this->set_lightbox_rel();
  
    $lightbox = $this->get_option('general_lightbox');
    $lightbox_style = $this->get_option('general_lightbox_params');
    $lightbox_style = str_replace( array("{","}"), "", $lightbox_style);
    
    $setRel = "jQuery( '#".$this->get_private('wid')."-AlpinePhotoTiles_container a.AlpinePhotoTiles-lightbox' ).attr( 'rel', '".$this->get_active_option('rel')."' );";
    
    if( 'fancybox' == $lightbox ){
      $default = "titleShow: false, overlayOpacity: .8, overlayColor: '#000', titleShow: true, titlePosition: 'inside'";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().fancybox){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).fancybox( { ".$lightbox_style." } );}";  
    }elseif( 'prettyphoto' == $lightbox ){
      //theme: 'pp_default', /* light_rounded / dark_rounded / light_square / dark_square / facebook
      $default = "theme:'facebook',social_tools:false, show_title:true";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().prettyPhoto){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).prettyPhoto({ ".$lightbox_style." });}";  
    }elseif( 'colorbox' == $lightbox ){
      $default = "maxHeight:'85%'";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().colorbox){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).colorbox( {".$lightbox_style."} );}";  
    }elseif( 'alpine-fancybox' == $lightbox ){
      $default = "titleShow: false, overlayOpacity: .8, overlayColor: '#000', titleShow: true, titlePosition: 'inside'";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().fancyboxForAlpine){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).fancyboxForAlpine( { ".$lightbox_style." } );}";  
    }
    return "";
  }
  
 /**
  *  Set Lightbox "rel"
  *  
  *  @ Since 1.2.3
  */
  function set_lightbox_rel(){
    $lightbox = $this->get_option('general_lightbox');
    $custom = $this->get_option('hidden_lightbox_custom_rel');
    if( !empty($custom) && $this->check_active_option('custom_lightbox_rel') ){
      $rel = $this->get_active_option('custom_lightbox_rel');
      $rel = str_replace('{rtsq}',']',$rel); // Decode right and left square brackets
      $rel = str_replace('{ltsq}','[',$rel);
    }elseif( 'fancybox' == $lightbox ){
      $rel = 'alpine-fancybox-'.$this->get_private('wid');
    }elseif( 'prettyphoto' == $lightbox ){
      $rel = 'alpine-prettyphoto['.$this->get_private('wid').']';
    }elseif( 'colorbox' == $lightbox ){
      $rel = 'alpine-colorbox['.$this->get_private('wid').']';
    }else{
      $rel = 'alpine-fancybox-safemode-'.$this->get_private('wid');
    }
    $this->set_active_option('rel',$rel);
  }


}





?>
