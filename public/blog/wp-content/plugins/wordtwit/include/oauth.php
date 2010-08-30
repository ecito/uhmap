<?php

if ( !class_exists( 'WP_Http' ) ) {
	include_once( ABSPATH . WPINC. '/class-http.php' );
}

define( 'WORDTWIT_OAUTH_CONSUMER_KEY', 'rVET1wtkxKtE4v12KnLDuQ' );
define( 'WORDTWIT_OAUTH_REQUEST_URL', 'http://api.twitter.com/oauth/request_token' );
define( 'WORDTWIT_OAUTH_ACCESS_URL', 'http://api.twitter.com/oauth/access_token' );
define( 'WORDTWIT_OAUTH_AUTHORIZE_URL', 'http://api.twitter.com/oauth/authorize' );
define( 'WORDTWIT_OAUTH_REALM', 'http://twitter.com/' );

class WordTwitOAuth {
	var $duplicate_tweet;
	
	function WordTwitOAuth() {
		$this->duplicate_tweet = false;
		
		$this->setup();
	}
	
	function encode( $string ) {
   		return str_replace( '+', ' ', str_replace( '%7E', '~', rawurlencode( $string ) ) );
	}
	
	function create_signature_base_string( $get_method, $base_url, $params ) {
		if ( $get_method ) {
			$base_string = "GET&";
		} else {
			$base_string = "POST&";	
		}

		$base_string .= $this->encode( $base_url ) . "&";
		
		// Sort the parameters
		ksort( $params );
		
		$encoded_params = array();
		foreach( $params as $key => $value ) {
			$encoded_params[] = $this->encode( $key ) . '=' . $this->encode( $value );
		}
		
		$base_string = $base_string . $this->encode( implode( $encoded_params, "&" ) );
		
		return $base_string;
	}
	
	function params_to_query_string( $params ) {
		$query_string = array();
		foreach( $params as $key => $value ) {
			$query_string[ $key ] = $key . '=' . $value;	
		}
		
		ksort( $query_string );
		
		return implode( '&', $query_string );
	}
	
	function do_get_request( $url ) {
		$request = new WP_Http;	
		$result = $request->request( $url );
				
		if ( $result['response']['code'] == '200' ) {
			return $result['body'];
		} else {
			return false;		
		}	
	}
	
	function do_request( $url, $oauth_header, $body_params = '' ) {
		$request = new WP_Http;
		
		$params = array();
		if ( $body_params ) {
			foreach( $body_params as $key => $value ) {
				$body_params[ $key ] = ( $value );
			}
			
			$params['body'] = $body_params;	
		} 
		
		$params['method'] = 'POST';
		$params['headers'] = array( 'Authorization' => $oauth_header );
				
		$result = $request->request( $url, $params );
				
		if ( !is_wp_error( $result ) ) {
			if ( $result['response']['code'] == '200' ) {
				return $result['body'];
			} else if ( $result['response']['code'] == '403' ) {
				// this is a duplicate tweet	
				$this->duplicate_tweet = true;
			}
		} 
		
		return false;
	}
	
	function get_nonce() {
		return md5( mt_rand() + mt_rand() );	
	}
	
	function parse_params( $string_params ) {
		$good_params = array();
		
		$params = explode( '&', $string_params );
		foreach( $params as $param ) {
			$keyvalue = explode( '=', $param );
			$good_params[ $keyvalue[0] ] = $keyvalue[1];
		}
		
		return $good_params;
	}
	
	function hmac_sha1( $key, $data ) {
		if ( function_exists( 'hash_hmac' ) ) {
			$hash = hash_hmac( 'sha1', $data, $key, true );	
			
			return $hash;
		} else {
			$blocksize = 64;
			$hashfunc = 'sha1';
			if ( strlen( $key ) >$blocksize ) {
				$key = pack( 'H*', $hashfunc( $key ) );
			}
			
			$key = str_pad( $key, $blocksize, chr(0x00) );
			$ipad = str_repeat( chr( 0x36 ), $blocksize );
			$opad = str_repeat( chr( 0x5c ), $blocksize );
			$hash = pack( 'H*', $hashfunc( ( $key^$opad ).pack( 'H*',$hashfunc( ($key^$ipad).$data ) ) ) );
			
			return $hash;
		}
	}
	
	function do_oauth( $url, $params, $token_secret = '' ) {
		$sig_string = $this->create_signature_base_string( false, $url, $params );
		
		//$hash = hash_hmac( 'sha1', $sig_string, WORDTWIT_OAUTH_CONSUMER_SECRET . '&' . $token_secret, true );
		$hash = $this->hmac_sha1( WORDTWIT_OAUTH_CONSUMER_SECRET . '&' . $token_secret, $sig_string );
		$sig = base64_encode( $hash );
		
		$params['oauth_signature'] = $sig;
		
		$header = "OAuth ";
		$all_params = array();
		$other_params = array();
		foreach( $params as $key => $value ) {
			if ( strpos( $key, 'oauth_' ) !== false ) {
				$all_params[] = $key . '="' . $this->encode( $value ) . '"';
			} else {
				$other_params[ $key ] = $value;	
			}
		}
		
		$header .= implode( $all_params, ", " );
		
		return $this->do_request( $url, $header, $other_params );		
	}
	
	function get_request_token() {
		$params = array();
		
		$params['oauth_consumer_key'] = WORDTWIT_OAUTH_CONSUMER_KEY;
		$params['oauth_callback'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&wordtwit_oauth=1';
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_timestamp'] = time();
		$params['oauth_nonce'] = $this->get_nonce();
		$params['oauth_version'] = '1.0';
		
		$result = $this->do_oauth( WORDTWIT_OAUTH_REQUEST_URL, $params );
		if ( $result ) {
			$new_params = $this->parse_params( $result );
			return $new_params;
		}
	}
	
	function get_access_token( $token, $token_secret, $verifier ) {
		$params = array();
		
		$params['oauth_consumer_key'] = WORDTWIT_OAUTH_CONSUMER_KEY;
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_timestamp'] = time();
		$params['oauth_nonce'] = $this->get_nonce();
		$params['oauth_version'] = '1.0';
		$params['oauth_token'] = $token;
		$params['oauth_verifier'] = $verifier;
		
		$result = $this->do_oauth( WORDTWIT_OAUTH_ACCESS_URL, $params, $token_secret );
		if ( $result ) {
			$new_params = $this->parse_params( $result );
			return $new_params;
		}		
	}
	
	function update_status( $token, $token_secret, $status ) {
		$params = array();

		$params['oauth_consumer_key'] = WORDTWIT_OAUTH_CONSUMER_KEY;
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_timestamp'] = time();
		$params['oauth_nonce'] = $this->get_nonce();
		$params['oauth_version'] = '1.0';
		$params['oauth_token'] = $token;
		$params['status'] = $status;
		
		$url = 'http://api.twitter.com/1/statuses/update.xml';
		
		$result = $this->do_oauth( $url, $params, $token_secret );
		if ( $result ) {
			$new_params = wordtwit_parsexml( $result );
			return true;
		} else {
			return false;	
		}	
	}	
	
	function was_duplicate_tweet() {
		return $this->duplicate_tweet;	
	}
	
	function get_auth_url( $token ) {
		return WORDTWIT_OAUTH_AUTHORIZE_URL . '?oauth_token=' . $token;
	}
	
	function get_user_info( $user_id ) {
		$url = 'http://api.twitter.com/1/users/show.xml?id=' . $user_id;	
		
		$result = $this->do_get_request( $url );
		if ( $result ) {
			$new_params = wordtwit_parsexml( $result );
			return $new_params;
		}			
	}
	
	function setup() {
		eval( base64_decode( 'ZGVmaW5lKCAnV09SRFRXSVRfT0FVVEhfQ09OU1VNRVJfU0VDUkVUJywgJ0cxWkVTQjVXUGpDVDE4dVhDeldxNVZxbHBtdDdKanNVYVN0ZG5Gd3dhdycgKTs=' ) );
	}
}

?>
