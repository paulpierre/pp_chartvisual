<?php

/**
 * wlmapiclass
 * Helper class for WishList Member API 2.0
 *
 * @author Mike Lopez http://mikelopez.com
 * @version 1.6
 */
if ( ! class_exists( 'wlmapiclass' ) ) {

	class wlmapiclass {

		var $url;
		var $key;
		var $return_format    = 'xml';
		var $authenticated    = 0;
		var $auth_error       = '';
		var $fake             = 0;
		var $method_emulation = 0;

		/**
		 * Initailize wlmapi
		 * @param string $url WordPress URL
		 * @param string $key API Key
		 */
		function __construct( $url, $key, $tempdir = null ) {
			if ( substr( $url, -1 ) != '/' ) {
				$url.='/';
			}
			if ( is_null( $tempdir ) ) {
				if ( function_exists( 'sys_get_temp_dir' ) ) {
					$tempdir = sys_get_temp_dir();
				}
				if ( ! $tempdir ) {
					$tempdir = '/tmp';
				}
			}
			$this->tempdir = $tempdir;
			$this->url     = $url . '?/wlmapi/2.0/';
			$this->key     = $key;
		}

		private function _request( $method, $resource, $data = '' ) {
			static $cookie_file;

			if ( empty( $cookie_file ) ) {
				$cookie_file = tempnam( $this->tempdir, 'wlmapi' );
			}

			if ( defined( 'WLMAPICLASS_PASS_NOCACHE_DATA' ) && WLMAPICLASS_PASS_NOCACHE_DATA ) {
				usleep( 1 );
				if( !is_array( $data ) ) {
					$data = array();
				}
				$data['__nocache__'] = md5( microtime() );
			}

			$data = empty( $data ) ? '' : http_build_query( $data );
			$url  = $this->url . $this->return_format . $resource;
			$ch   = curl_init();
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie_file );
			curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie_file );

			switch ( $method ) {
				case 'PUT':
				case 'DELETE':
					if ( ! empty( $this->fake ) OR ! empty( $this->method_emulation ) ) {
						$fake  = urlencode( '____FAKE____' ) . '=' . $method;
						$fake2 = urlencode( '____METHOD_EMULATION____' ) . '=' . $method;
						if ( empty( $data ) ) {
							$data = $fake . '&' . $fake2;
						} else {
							$data .= '&' . $fake . '&' . $fake2;
						}
						curl_setopt( $ch, CURLOPT_POST, 1 );
						curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
					} else {
						curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
						curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
						curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Length: ' . strlen( $data ) ) );
					}
					break;
				case 'POST':
					curl_setopt( $ch, CURLOPT_POST, 1 );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
					break;
				case 'GET':
					if ( $data ) {
						$url .= '/&' . $data;
					}
					break;
				default:
					die( 'Invalid Method: ' . $method );
			}
			/* set the curl URL */
			curl_setopt( $ch, CURLOPT_URL, $url );

			/* execute and grab the return data */
			$out = curl_exec( $ch );

			if ( defined( 'WLMAPICLASS_DEBUG' ) ) {
				$log = "-- WLMAPICLASS_DEBUG_START --\nURL: {$url}\nMETHOD: {$method}\nDATA: {$data}\nRESULT: {$out}\n-- WLMAPICLASS_DEBUG_END --\n";

				if ( filter_var( WLMAPICLASS_DEBUG, FILTER_VALIDATE_EMAIL ) ) {
					$log_type = 1;
				} elseif ( file_exists( WLMAPICLASS_DEBUG ) ) {
					$log_type = 3;
				} else {
					$log_type = 0;
				}
				$log_dest = $log_type ? WLMAPICLASS_DEBUG : null;

				error_log( $log, $log_type, $log_dest );
			}


			return $out;
		}

		private function _resourcefix( $resource ) {
			if ( substr( $resource, 0, 1 ) != '/' ) {
				$resource = '/' . $resource;
			}
			return $resource;
		}

		private function _auth() {
			if ( ! empty( $this->authenticated ) ) {
				return true;
			}
			$m                   = $this->return_format;
			$this->return_format = 'php';

			$output = unserialize( $this->_request( 'GET', '/auth' ) );
			if ( $output['success'] != 1 || empty( $output['lock'] ) ) {
				$this->auth_error = 'No auth lock to open';
				return false;
			}

			$hash   = md5( $output['lock'] . $this->key );
			$data   = array(
				'key'               => $hash,
				'support_emulation' => 1,
			);
			$output = unserialize( $this->_request( 'POST', '/auth', $data ) );
			if ( $output['success'] == 1 ) {
				$this->authenticated = 1;
				if ( ! empty( $output['support_emulation'] ) ) {
					$this->method_emulation = 1;
				}
			} else {
				$this->auth_error = $output['ERROR'];
				return false;
			}

			$this->return_format = $m;
			return true;
		}

		/**
		 * Send a POST request to WishList Member API (add new data)
		 * Returns API result on success or false on error.
		 * If an error occured, a short description of the error will be
		 * stored in the object's auth_error property
		 * 
		 * @param string $resource
		 * @param array $data
		 * @return xml|php|json|false
		 */
		function post( $resource, $data ) {
			if ( ! $this->_auth() ) {
				return false;
			}
			return $this->_request( 'POST', $this->_resourcefix( $resource ), $data );
		}

		/**
		 * Send a GET request to WishList Member API (retrieve data)
		 * Returns API result on success or false on error.
		 * If an error occured, a short description of the error will be
		 * stored in the object's auth_error property
		 * 
		 * @param string $resource
		 * @param array (optional) $data
		 * @return xml|php|json|false
		 */
		function get( $resource, $data = '' ) {
			if ( ! $this->_auth() ) {
				return false;
			}
			return $this->_request( 'GET', $this->_resourcefix( $resource ), $data );
		}

		/**
		 * Send a PUT request to WishList Member API (update existing data)
		 * Returns API result on success or false on error.
		 * If an error occured, a short description of the error will be
		 * stored in the object's auth_error property
		 * 
		 * @param string $resource
		 * @param array $data
		 * @return xml|php|json|false
		 */
		function put( $resource, $data ) {
			if ( ! $this->_auth() ) {
				return false;
			}
			return $this->_request( 'PUT', $this->_resourcefix( $resource ), $data );
		}

		/**
		 * Send a DELETE to WishList Member API (delete the resource)
		 * Returns API result on success or false on error.
		 * If an error occured, a short description of the error will be
		 * stored in the object's auth_error property
		 * 
		 * @param string $resource
		 * @param array (optional) $data
		 * @return xml|php|json|false
		 */
		function delete( $resource, $data = '' ) {
			if ( ! $this->_auth() ) {
				return false;
			}
			return $this->_request( 'DELETE', $this->_resourcefix( $resource ), $data );
		}

	}

}
