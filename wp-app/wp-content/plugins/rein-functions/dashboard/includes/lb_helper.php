<?php if(count(get_included_files()) == 1) exit("No direct script access allowed");

if ( !defined('ABSPATH') ) exit;

require_once(ABSPATH . 'wp-admin/includes/file.php');

if( !defined("LB_API_DEBUG") ){
    define("LB_API_DEBUG", false);
}

define("LB_TEXT_CONNECTION_FAILED", 'Server is unavailable at the moment, please try again.');
define("LB_TEXT_INVALID_RESPONSE", 'Server returned an invalid response, please contact support.');
define("LB_TEXT_VERIFIED_RESPONSE", 'Verified! Thanks for purchasing.');
define("LB_TEXT_PREPARING_MAIN_DOWNLOAD", 'Preparing to download main update...');
define("LB_TEXT_MAIN_UPDATE_SIZE", 'Main Update size:');
define("LB_TEXT_DONT_REFRESH", '(Please do not refresh the page).');
define("LB_TEXT_DOWNLOADING_MAIN", 'Downloading main update...');
define("LB_TEXT_UPDATE_PERIOD_EXPIRED", 'Your update period has ended or your license is invalid, please contact support.');
define("LB_TEXT_UPDATE_PATH_ERROR", 'Folder does not have write permission or the update file path could not be resolved, please contact support.');
define("LB_TEXT_MAIN_UPDATE_DONE", 'Main update files downloaded and extracted.');
define("LB_TEXT_UPDATE_EXTRACTION_ERROR", 'Update zip extraction failed.');
define("LB_TEXT_PREPARING_SQL_DOWNLOAD", 'Preparing to download SQL update...');
define("LB_TEXT_SQL_UPDATE_SIZE", 'SQL Update size:');
define("LB_TEXT_DOWNLOADING_SQL", 'Downloading SQL update...');
define("LB_TEXT_SQL_UPDATE_DONE", 'SQL update files downloaded.');
define("LB_TEXT_UPDATE_WITH_SQL_DONE", 'Update successful, please import the downloaded sql file in your database.');
define("LB_TEXT_UPDATE_WITHOUT_SQL_DONE", 'Update successful, there were no SQL updates. So you can run the updated application directly.');

class LicenseBoxAPI{

	private $product_id;
	private $api_url;
	private $api_key;
	private $api_language;
	private $current_version;
	private $verify_type;
	private $verification_period;
	private $current_path;
	private $root_path;
    private $license_file;
    private $slug;
    private $license_status;
    private $license_key;
    private $license_email;
    private $license_user;

	public function __construct(){ 
        $theme = wp_get_theme( EPCL_THEMESLUG );
        $ver = $theme->version;
        $theme_root = get_theme_root();

        $this->slug = EPCL_THEMESLUG;
		$this->product_id = EPCL_THEMESLUG;
		$this->api_url = 'http://updates.estudiopatagon.com/';
		$this->api_key = EPCL_APIKEY;
		$this->api_language = 'english';
        $this->current_version = $ver;
		$this->verify_type = 'envato';
		$this->verification_period = 3;
		$this->current_path = realpath(__DIR__);
		$this->root_path = realpath( $theme_root );
        $this->license_file = $this->current_path.'/lb.lic';
        $this->license_status = get_option( $this->slug . '_license_key_status' );
        $this->license_key = get_option( $this->slug . '_license_key');
        $this->license_email = get_option( $this->slug . '_license_email' );
        $this->license_user = get_option( $this->slug . '_license_user' );
	}

	public function check_local_license_exist(){
		return is_file($this->license_file);
	}

	public function get_current_version(){
		return $this->current_version;
	}

	private function init_wp_fs(){
		global $wp_filesystem;
		if(false === ($credentials = request_filesystem_credentials(''))){
			return false;
		}
		if(!WP_Filesystem($credentials)){ 
			request_filesystem_credentials('');
			return false;
		}
		return true;
	}

	private function write_wp_fs($file_path, $content){
		global $wp_filesystem;
		$save_file_to = $file_path;
		if($this->init_wp_fs()){    
			if($wp_filesystem->put_contents($save_file_to, $content, FS_CHMOD_FILE)){
				return true;
			}
			else{
				return false;
			}
		}
	}

	private function read_wp_fs($file_path){
		global $wp_filesystem;
		if($this->init_wp_fs()){    
			return $wp_filesystem->get_contents($file_path);
		}
	}

	private function call_api($method, $url, $data){
		$wp_args = array('body' => $data);	
		$wp_args['method'] = $method;

		$this_url = site_url();
		$this_ip = getenv('SERVER_ADDR')?:
			$this->get_ip_from_third_party()?:
			gethostbyname(gethostname());

		$wp_args['headers'] = array(
			'Content-Type' => 'application/json', 
			'LB-API-KEY' => $this->api_key, 
			'LB-URL' => $this_url, 
			'LB-IP' => $this_ip, 
			'LB-LANG' => $this->api_language
		);
		$wp_args['timeout'] = 30;

		$result = wp_remote_request($url, $wp_args);

        if( is_wp_error($result) ) {
            $rs = array(
                'status' => FALSE,
                'message' => LB_TEXT_CONNECTION_FAILED
            );
            return json_encode($rs);
        }

		if(!$result['body']&&!LB_API_DEBUG){
			$rs = array(
				'status' => FALSE, 
				'message' => LB_TEXT_CONNECTION_FAILED
			);
			return json_encode($rs);
		}
		$http_status = $result['response']['code'];
		if($http_status != 200){
			if(LB_API_DEBUG){
				$temp_decode = json_decode($result['body'], true);
				$rs = array(
					'status' => FALSE, 
					'message' => ((!empty($temp_decode['error']))?
						$temp_decode['error']:
						$temp_decode['message'])
				);
				return json_encode($rs);
			}else{
				$rs = array(
					'status' => FALSE, 
					'message' => LB_TEXT_INVALID_RESPONSE
				);
				return json_encode($rs);
			}
		}
		return $result['body'];
	}

	public function check_connection(){
		$data_array =  array();
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/check_connection_ext', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);
		return $response;
	}

	public function get_latest_version(){
		$data_array =  array(
			"product_id"  => $this->product_id
		);
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/latest_version', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);
		return $response;
	}

	public function activate_license($license, $client, $create_lic, $license_email){
		$data_array =  array(
			"product_id"  => $this->product_id,
			"license_code" => $license,
			"client_name" => $client,
            "verify_type" => $this->verify_type,
            "license_email" => $license_email,
            "license_domain" => $_SERVER['SERVER_NAME']
		);
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/activate_license', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);
		if(!empty($create_lic)){
			if($response['status']){
				$licfile = trim($response['lic_response']);
                $this->write_wp_fs($this->license_file, $licfile);
                update_option( $this->slug . '_license_key_file', $licfile );
                update_option( $this->slug . '_license_key_status', 'valid' );
                update_option( $this->slug . '_license_key', $license);
                update_option( $this->slug . '_license_email', $license_email );
                update_option( $this->slug . '_license_user', $client );
			}else{
				if(is_writeable($this->license_file)){
                    unlink($this->license_file);
                    set_site_transient('update_themes', null);
				}
			}
		}
		return $response;
    }

    public function create_license( $license = false, $client = false, $license_key_file = false ){

        if( $license_key_file ){
            $licfile = trim( $license_key_file );
            $this->write_wp_fs($this->license_file, $licfile);
        }else{
            if(is_writeable($this->license_file)){
                unlink($this->license_file);
                set_site_transient('update_themes', null);
            }
        }
        
    }

	public function verify_license($time_based_check = false, $license = false, $client = false){
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"product_id"  => $this->product_id,
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"product_id"  => $this->product_id,
					"license_file" => $this->read_wp_fs($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		} 
		$res = array('status' => TRUE, 'message' => LB_TEXT_VERIFIED_RESPONSE);
		if($time_based_check && $this->verification_period > 0){
			ob_start();
			if(session_status() == PHP_SESSION_NONE){
				session_start();
			}
			$type = (int) $this->verification_period;
			$today = date('d-m-Y');
			if(empty($_SESSION["17b7528d98068e4"])){
				$_SESSION["17b7528d98068e4"] = '00-00-0000';
			}
			if($type == 1){
				$type_text = '1 day';
			}elseif($type == 3){
				$type_text = '3 days';
			}elseif($type == 7){
				$type_text = '1 week';
			}elseif($type == 30){
				$type_text = '1 month';
			}elseif($type == 90){
				$type_text = '3 months';
			}elseif($type == 365) {
				$type_text = '1 year';
			}else{
				$type_text = $type.' days';
			}
			if(strtotime($today) >= strtotime($_SESSION["17b7528d98068e4"])){
				$get_data = $this->call_api(
					'POST',
					$this->api_url.'api/verify_license', 
					json_encode($data_array)
				);
				$res = json_decode($get_data, true);
				if($res['status']==true){
					$tomo = date('d-m-Y', strtotime($today. ' + '.$type_text));
					$_SESSION["17b7528d98068e4"] = $tomo;
				}
			}
            ob_end_clean();
            
		}else{
			$get_data = $this->call_api(
				'POST',
				$this->api_url.'api/verify_license', 
				json_encode($data_array)
			);
            $res = json_decode($get_data, true);
        }        

		return $res;
    }

	public function deactivate_license($license = false, $client = false){
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"product_id"  => $this->product_id,
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"product_id"  => $this->product_id,
					"license_file" => $this->read_wp_fs($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		}
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/deactivate_license', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);
		if($response['status']){
			if(is_writeable($this->license_file)){
                unlink($this->license_file);
                set_site_transient('update_themes', null);
			}
		}
		return $response;
	}

	public function check_update(){
		$data_array =  array(
			"product_id"  => $this->product_id,
			"current_version" => $this->current_version
		);
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/check_update', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);
		return $response;
	}

	public function download_update($update_id, $type, $version, $license = false, $client = false){ 
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"license_file" => $this->read_wp_fs($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		}
		ob_end_flush(); 
		ob_implicit_flush(true);  
        $version = str_replace(".", "_", $version);
        echo '<div class="epcl-notice">';
		ob_start();
		$source_size = $this->api_url."api/get_update_size/main/".$update_id; 
		echo LB_TEXT_PREPARING_MAIN_DOWNLOAD."<br>";
		ob_flush();
		echo LB_TEXT_MAIN_UPDATE_SIZE." ".$this->get_remote_filesize($source_size)." ".LB_TEXT_DONT_REFRESH."<br>";
		ob_flush();
		$temp_progress = '';
		$source = $this->api_url."api/download_update/main/".$update_id; 
		$wp_args = array('body' => json_encode($data_array));	
		$wp_args['method'] = 'POST';
		$this_url = site_url();
		$this_ip = getenv('SERVER_ADDR')?:
			$this->get_ip_from_third_party()?:
			gethostbyname(gethostname());
		$wp_args['headers'] = array(
			'Content-Type' => 'application/json', 
			'LB-API-KEY' => $this->api_key, 
			'LB-URL' => $this_url, 
			'LB-IP' => $this_ip, 
			'LB-LANG' => $this->api_language
		);
		$wp_args['timeout'] = 30;
		echo LB_TEXT_DOWNLOADING_MAIN."<br>";
		ob_flush();
		$result = wp_remote_request($source, $wp_args);
        if( is_wp_error($result) ) {
            exit(LB_TEXT_CONNECTION_FAILED);
        }
		$data = $result['body'];
		$http_status = $result['response']['code'];
		if($http_status != 200){
			if($http_status == 401){
				exit("<br>".LB_TEXT_UPDATE_PERIOD_EXPIRED);
			}else{
				exit("<br>".LB_TEXT_INVALID_RESPONSE);
			}
		}
		$destination = $this->root_path."/update_main_".$version.".zip"; 
		$file = $this->write_wp_fs($destination, $data);
		if(!$file){
			exit("<br>".LB_TEXT_UPDATE_PATH_ERROR);
		}
		ob_flush();
		$zip = new ZipArchive;
		$res = $zip->open($destination);
		if($res === TRUE){
			$zip->extractTo($this->root_path."/"); 
			$zip->close();
			unlink($destination);
            //echo LB_TEXT_MAIN_UPDATE_DONE."<br><br>";
            echo "<p style='margin-top: 0;'>Successfully installed!, don't forget to <b>update ".EPCL_THEMENAME." Functions Plugin:</b><br><br><a href='".admin_url('themes.php?page=install-required-plugins&plugin_status=update')."' class='button button-primary'>Click here to Update Plugin</a></p>";
            set_site_transient('update_themes', null);
			ob_flush();
		}else{
			echo LB_TEXT_UPDATE_EXTRACTION_ERROR."<br><br>";
			ob_flush();
		}
		if($type == true){
			$source_size = $this->api_url."api/get_update_size/sql/".$update_id; 
			echo LB_TEXT_PREPARING_SQL_DOWNLOAD."<br>";
			ob_flush();
			echo LB_TEXT_SQL_UPDATE_SIZE." ".$this->get_remote_filesize($source_size)." ".LB_TEXT_DONT_REFRESH."<br>";
			ob_flush();
			$temp_progress = '';
			$source = $this->api_url."api/download_update/sql/".$update_id;
			$wp_args = array('body' => json_encode($data_array));	
			$wp_args['method'] = 'POST';
			$this_url = site_url();
			$this_ip = getenv('SERVER_ADDR')?:
				$this->get_ip_from_third_party()?:
				gethostbyname(gethostname());
			$wp_args['headers'] = array(
				'Content-Type' => 'application/json', 
				'LB-API-KEY' => $this->api_key, 
				'LB-URL' => $this_url, 
				'LB-IP' => $this_ip, 
				'LB-LANG' => $this->api_language
			);
			$wp_args['timeout'] = 30;
			echo LB_TEXT_DOWNLOADING_SQL."<br>";
			ob_flush();
			$result = wp_remote_request($source, $wp_args);
			$data = $result['body'];
			$http_status = $result['response']['code'];
			if($http_status!=200){
				exit(LB_TEXT_INVALID_RESPONSE);
			}
			$destination = $this->root_path."/update_sql_".$version.".sql"; 
			$file = $this->write_wp_fs($destination, $data);
			if(!$file){
				exit(LB_TEXT_UPDATE_PATH_ERROR);
			}
			echo LB_TEXT_SQL_UPDATE_DONE."<br><br>";
			echo LB_TEXT_UPDATE_WITH_SQL_DONE;
			ob_flush();
		}else{
			//echo LB_TEXT_UPDATE_WITHOUT_SQL_DONE;
			ob_flush();
		}
        ob_end_flush(); 
        echo '</div>';
    }

	private function get_ip_from_third_party(){
		$wp_args = array('method' => 'GET');	
		$wp_args['timeout'] = 30;
		$result = wp_remote_request('http://ipecho.net/plain', $wp_args);
		return $result['body'];
	}

	private function get_remote_filesize($url){
		$wp_args = array('method' => 'HEAD');	
		$this_url = site_url();
		$this_ip = getenv('SERVER_ADDR')?:
			$this->get_ip_from_third_party()?:
			gethostbyname(gethostname());
		$wp_args['headers'] = array(
			'Content-Type' => 'application/json', 
			'LB-API-KEY' => $this->api_key, 
			'LB-URL' => $this_url, 
			'LB-IP' => $this_ip, 
			'LB-LANG' => $this->api_language
		);
		$wp_args['timeout'] = 30;
		$result = wp_remote_request($url, $wp_args);
		$filesize = $result['headers']['content-length'];
		if ($filesize){
			switch ($filesize){
				case $filesize < 1024:
					$size = $filesize .' B'; break;
				case $filesize < 1048576:
					$size = round($filesize / 1024, 2) .' KB'; break;
				case $filesize < 1073741824:
					$size = round($filesize / 1048576, 2) . ' MB'; break;
				case $filesize < 1099511627776:
					$size = round($filesize / 1073741824, 2) . ' GB'; break;
			}
			return $size; 
		}
    }
    
    public function verify_license_updates($time_based_check = false, $license = false, $client = false){
        $lb_update_data = null;
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"product_id"  => $this->product_id,
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"product_id"  => $this->product_id,
					"license_file" => $this->read_wp_fs($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		} 
        $res = array('status' => TRUE, 'message' => LB_TEXT_VERIFIED_RESPONSE);
        
		if($time_based_check && $this->verification_period > 0){
			ob_start();
			if(session_status() == PHP_SESSION_NONE){
				session_start();
			}
			$type = (int) $this->verification_period;
			$today = date('d-m-Y');
			if(empty($_SESSION["17b7528d98068e4"])){
				$_SESSION["17b7528d98068e4"] = '00-00-0000';
			}
			if($type == 1){
				$type_text = '1 day';
			}elseif($type == 3){
				$type_text = '3 days';
			}elseif($type == 7){
				$type_text = '1 week';
			}elseif($type == 30){
				$type_text = '1 month';
			}elseif($type == 90){
				$type_text = '3 months';
			}elseif($type == 365) {
				$type_text = '1 year';
			}else{
				$type_text = $type.' days';
            }
            $_SESSION["17b7528d98068e4"] = date('d-m-Y');
			if(strtotime($today) >= strtotime($_SESSION["17b7528d98068e4"])){
                
				$get_data = $this->call_api(
					'POST',
					$this->api_url.'api/verify_license', 
					json_encode($data_array)
				);
				$res = json_decode($get_data, true);
				if($res['status']==true){
					$tomo = date('d-m-Y', strtotime($today. ' + '.$type_text));
                    $_SESSION["17b7528d98068e4"] = $tomo;
                    $lb_update_data = $this->check_update(); 
				}
			}
            ob_end_clean();
            
		}else{
            // echo 'calling api<br>';
			$get_data = $this->call_api(
				'POST',
				$this->api_url.'api/verify_license', 
				json_encode($data_array)
			);
            $res = json_decode($get_data, true);
            $lb_update_data = $this->check_update(); 
        }
        
		return $lb_update_data;
	}
    
    public function download_zip($update_id, $type, $version, $license = false, $client = false){ 
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"license_file" => $this->read_wp_fs($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		}
		$version = str_replace(".", "_", $version);

		$source = $this->api_url."api/download_update/main/".$update_id; 
		$wp_args = array('body' => json_encode($data_array));	
		$wp_args['method'] = 'POST';
		$this_url = site_url();
		$this_ip = getenv('SERVER_ADDR')?:
			$this->get_ip_from_third_party()?:
			gethostbyname(gethostname());
		$wp_args['headers'] = array(
			'Content-Type' => 'application/json', 
			'LB-API-KEY' => $this->api_key, 
			'LB-URL' => $this_url, 
			'LB-IP' => $this_ip, 
			'LB-LANG' => $this->api_language
		);
		$wp_args['timeout'] = 30;

		$result = wp_remote_request($source, $wp_args);
        if( is_wp_error($result) ) {
            return(LB_TEXT_CONNECTION_FAILED);
        }

        $data = $result['body'];
		$http_status = $result['response']['code'];
		if($http_status != 200){
			if($http_status == 401){
				return(LB_TEXT_UPDATE_PERIOD_EXPIRED);
			}else{
				return(LB_TEXT_INVALID_RESPONSE);
			}
        }
 
        $destination = EPCL_PLUGIN_PATH."dashboard/includes/update_main_".$update_id.".zip"; 
        if( !file_exists($destination) ){
            $file = $this->write_wp_fs($destination, $data);
            if(!$file){
                return(LB_TEXT_UPDATE_PATH_ERROR);
            }else{
                return('Zip downloaded');
            }
        }
		
    }
    
    public function install_update( $update_id = null, $has_sql = null, $version = null ){
        global $wp_filesystem;
        $zip_from = EPCL_PLUGIN_PATH."dashboard/includes/update_main_".$update_id.".zip";        

        if ( ! file_exists($zip_from) ){
            if( !$this->check_local_license_exist() ){
                $license_key = get_option( EPCL_THEMESLUG . '_license_key');
                $license_user = get_option( EPCL_THEMESLUG . '_license_user');
                $this->download_update( $update_id, $has_sql, $version, $license_key, $license_user ); 
            }else{
                $this->download_update( $update_id, $has_sql, $version );
            }            
        }else{
            if( $this->init_wp_fs() ){
                $zip_to = get_theme_root();
                $unzipfile = unzip_file($zip_from, $zip_to);
                if ( is_wp_error( $unzipfile ) ) {
                    echo "<p style='margin-top: 0;'>There was an error installing the theme (zip file).</p>";
                } else {
                    echo "<p style='margin-top: 0;'>Successfully installed!, don't forget to <b>update ".EPCL_THEMENAME." Functions Plugin:</b><br><br><a href='".admin_url('themes.php?page=install-required-plugins&plugin_status=update')."' class='button button-primary'>Click here to Update Plugin</a></p>";
                    unlink($zip_from);
                }
            } else {
                echo "<p style='margin-top: 0;'>There was an error installing the theme (zip file).</p>";
            }
        }
        
    }

}

// set_site_transient('update_themes', null);
add_filter ('pre_set_site_transient_update_themes', 'epcl_update_transient_themes');
function epcl_update_transient_themes ($transient){
    if (empty($transient->checked)) return $transient;

    $license_key = get_option( EPCL_THEMESLUG . '_license_key');
    $license_user = get_option( EPCL_THEMESLUG . '_license_user');

    if( !$license_key || !$license_user ) return $transient;
  
    $lbapi = new LicenseBoxAPI();
    $lb_update_data = $lbapi->verify_license_updates( true );

    if( !empty($lb_update_data) ){
        
        $destination = EPCL_PLUGIN_PATH."dashboard/includes/update_main_".$lb_update_data['update_id'].".zip";
        $obj = array();
        if( !file_exists($destination) && is_writable( realpath(EPCL_PLUGIN_PATH."dashboard/includes") ) ){
            if( !$lbapi->check_local_license_exist() ){
                $license_key = get_option( EPCL_THEMESLUG . '_license_key');
                $license_user = get_option( EPCL_THEMESLUG . '_license_user');
                $lbapi->download_zip( $lb_update_data['update_id'], false, false, $license_key, $license_user ); 
            }else{
                $response = $lbapi->download_zip( $lb_update_data['update_id'], false, false );
            }
            if( $response !== 'Zip downloaded'){                        
                // $transient->response[EPCL_THEMESLUG] = null;
                // set_site_transient('update_themes', null);
            }
        }else{
            $obj['slug'] = EPCL_THEMESLUG;
            $obj['new_version'] = $lb_update_data['version'];
            $obj['url'] = 'http://updates.estudiopatagon.com/changelog/'.EPCL_THEMESLUG;
            $obj['package'] = EPCL_PLUGIN_URL.'/dashboard/includes/update_main_'.$lb_update_data['update_id'].'.zip';
            $transient->response[EPCL_THEMESLUG] = $obj;
        }
    }

    return $transient;
}

function epcl_core_plugin_notice() {
    global $pagenow;
    if( in_array($pagenow, array('post.php', 'post-new.php')) ){
        return;
    }
    if( !defined('EPCL_PLUGIN_PATH') || !current_user_can( 'install_plugins' ) ){
        return;
    }
    $theme = wp_get_theme( EPCL_THEMESLUG );
    $ver = $theme->version;
    $plugin_data = get_plugin_data( EPCL_PLUGIN_PATH.'/'.EPCL_THEMESLUG.'-functions.php' );
    $plugin_version = $plugin_data['Version'];
    // If theme version is lower than plugin, just ignore
    if( version_compare($ver, $plugin_version, '<=') ){
        return;
    }
    ?>
    <div class="update-nag notice notice-warning inline" style="display: table;">
        Update <b> <?php echo EPCL_THEMENAME; ?> Functions Plugin to (v<?php echo esc_attr($ver); ?>)</b> to ensure maximum compatibility.
        <span>
            <a href="<?php echo admin_url('themes.php?page=install-required-plugins&plugin_status=update'); ?>">Please update now</a>.
        </span>
        <div style="margin-top: 0.5em;"><b>Note:</b> If you can't update the plugin, <b>just remove</b> and <b>re-install</b> from <b>Appearance &rarr; Install plugins.</b></div>
    </div>
    <div class="clear"></div>
    <?php
}
add_action( 'admin_notices', 'epcl_core_plugin_notice' );