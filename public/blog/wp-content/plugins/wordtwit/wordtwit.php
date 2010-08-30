<?php
/*
	Plugin Name: WordTwit
	Plugin URI: http://www.bravenewcode.com/wordtwit
	Description: Posts updates to Twitter about entries automatically. Includes many configurable options, statistics and more.
	Author: Dale Mugford & Duane Storey (BraveNewCode)
	Author URI: http://www.bravenewcode.com
	Version: 2.4.4

	# Some ideas taken from http://twitter.slawcup.com/twitter.class.phps
	
	# This plugin is free software; you can redistribute the code aspects of it and/or
	# modify the code under the terms of the GNU Lesser General Public
	# License as published by the Free Software Foundation; either
	# version 2.1 of the License, or (at your option) any later version.
	
	# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
	# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
	# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
	# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
	# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
	#
	# See the GNU lesser General Public License for more details.
*/

require_once( 'compat.php' );

define( 'WORDTWIT_CACHE_TIME', 600 );
define( 'WORDTWIT_HOOTSUITE_API_KEY', 'GS90ormTrXy715xE8ADTn' );
define( 'WORDTWIT_QUEUE_RETRY_TIME', 15 );

require_once( ABSPATH . 'wp-includes/class-snoopy.php' );

require_once( 'xml.php' );
require_once( 'tinyurl.php' );
require_once( 'include/widgets.php' );
require_once( 'include/oauth.php' );

$twit_plugin_name = 'WordTwit';
$twit_plugin_prefix = 'wordtwit_';

global $wordtwit_version;
$wordtwit_version = '2.4.4';
$wordtwit_cache_time = 500;

// set up hooks for WordPress
add_action( 'publish_post', 'wordtwit_post_now_published' );
add_action( 'admin_head', 'wordtwit_admin_css' );
add_action( 'wp_head', 'wordtwit_head' );
add_action( 'save_post', 'wordtwit_save_post' );
add_filter( 'init', 'wordtwit_init');
add_action( 'delete_post', 'wordtwit_delete_post' );
add_action( 'query_vars', 'wordtwit_query_vars' );
add_action( 'parse_request', 'wordtwit_parse_request' );
add_action( 'admin_menu', 'wordtwit_admin_menu' );

global $wordtwit_oauth;
$wordtwit_oauth = new WordTwitOAuth;

//echo $wordtwit_oauth->get_request_token();
//die;

function wordtwit_query_vars( $vars ) {
	$vars[] = "wordtwit";
	return $vars;
}

function wordtwit_parse_request( $wp ) {
	if (array_key_exists( 'wordtwit', $wp->query_vars ) ) {
		switch( $wp->query_vars['wordtwit'] ) {
			case 'create':
				include( 'ajax/create-url.php' );
				break;
			case 'news':
				include( 'ajax/news.php' );
				break;
			case 'tweets':
				include( 'ajax/tweets.php' );
				break;
		}			
		exit;
	}	
}

function wordtwit_avatar( $avatar ) {
	global $comment;
	
	if ( $comment->comment_type == 'wordtwit' ) {
		echo "<img src=\"" . $comment->comment_agent . "\" alt=\"\" />";
	} else return $avatar;
}

function wordtwit_head() {
	echo '<link rel="stylesheet" type="text/css" media="screen" href="' . compat_get_plugin_url('wordtwit') . '/css/style.css" />';	
}

//Add a link to settings on the plugin listings page
function wordtwit_settings_link( $links, $file ) {
 	if( $file == 'wordtwit/wordtwit.php' && function_exists( "admin_url" ) ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=wordtwit.php' ) . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

function wordtwit_get_auth_url() {
	global $wordtwit_oauth;
	$settings = wordtwit_get_settings();
	
	$token = $wordtwit_oauth->get_request_token();
	if ( $token ) {
		$settings['oauth_request_token'] = $token['oauth_token'];
		$settings['oauth_request_token_secret'] = $token['oauth_token_secret'];
		
		wordtwit_save_settings( $settings );
		
		return $wordtwit_oauth->get_auth_url( $token['oauth_token'] );
	}
		
}

$wordtwit_defaults = array(
	'alternate_domain' => '',
	'tags' => array(),
	'reverse' => false,
	'activation_time' => 0,
	'message' => 'New blog posting, [title] - [link]',
	'url_type' => 'tinyurl',
	'enable_banner' => true,
	'enable_content_conversion' => false,
	'oauth_request_token' => false,
	'oauth_request_token_secret' => false,
	'oauth_access_token' => false,
	'oauth_access_token_secret' => false,
	'enable-utm' => false,
	'tweet_queue' => array(),
	'last_tweet_time' => 0,
	'user_id' => 0,
	'profile_image_url' => '',
	'screen_name' => '',
	'location' => false,
	'enabled_tweet_queue' => false
);

function wordtwit_is_bitly() {
	$settings = wordtwit_get_settings();
	
	return ( $settings['url_type'] == 'bitly' );	
}

function wordtwit_get_username() {
	$settings = wordtwit_get_settings();
	
	return $settings['screen_name'];
}


function wordtwit_get_settings() {
	global $wordtwit_defaults;
	
	$settings = $wordtwit_defaults;
	
	$wordpress_settings = get_option( 'wordtwit_settings' );
	if ( $wordpress_settings ) {
		foreach( $wordpress_settings as $key => $value ) {
			$settings[ $key ] = $value;
		}	
	}
	
	return $settings;
}

function wordtwit_save_post( $post_id ) {
	if ( !wp_verify_nonce( $_POST['wordtwit_nonce'], 'WordTwit' ) ) {
		return $post_id;
	}
	
	delete_post_meta( $post_id, 'wordtwit_hashtags' );
}

function wordtwit_delete_post( $id ) {
	global $wpdb;
	global $table_prefix;
	
	$sql = $wpdb->prepare( 'DELETE FROM ' . $table_prefix . 'tweet_urls WHERE post_id = %d', $id );
	$wpdb->query( $sql );
}

function wordtwit_post_box() {
	include( 'html/post-box.php' );
}

function wordtwit_admin_menu() {
	if ( function_exists( 'add_meta_box' ) ) {
		add_meta_box( 'wordtwit-box', __( 'WordTwit Management', 'wordtwit' ), 'wordtwit_post_box', 'post', 'side' );
	}
}

function wordtwit_friendly_date( $datetime ) {
	$datetime = strtotime( str_replace( 'T', ' ', $datetime ) );
	$server_time = current_time( 'timestamp', 1 );
	$tweet_time = $server_time - $datetime;
	
	$minutes = floor( $tweet_time / (60) );
	$hours = $tweet_time / (60*60);
	$days = $tweet_time / (60*60*24);
	
	if ( $minutes == 0 ) {
		return __( "Just now", "wordtwit" );	
	} else if ( $minutes < 120 && $hours < 2 ) {
		return sprintf( __( "About %d minutes ago", "wordtwit" ), $minutes );
	} else if ( $hours < 48 ) {
		return sprintf( __( "About %d hours ago", "wordtwit" ), $hours );
	} else {
		return sprintf( __( "About %d days ago", "wordtwit" ), $days );
	}
}

function wordtwit_service_queue() {
	$settings = wordtwit_get_settings();
	
	// Service the pending Tweet queue
	if ( isset( $settings['tweet_queue'] ) && count( $settings['tweet_queue'] ) ) {	
		if ( time() > ( $settings['last_tweet_time'] + WORDTWIT_QUEUE_RETRY_TIME ) ) {
			$f = @fopen( WP_PLUGIN_DIR . '/wordtwit/wordtwit.lock', 'r+' );
			if ( $f ) {
				// Use a lock file to prevent race condition with the queue
				if ( flock( $f, LOCK_EX | LOCK_NB ) ) {
					foreach( $settings['tweet_queue'] as $post_id => $tweet_data ) {
						// output tweet
						if ( wordtwit_do_tweet( $post_id ) ) {
							// delete it from the queue and update status
							unset( $settings['tweet_queue'][ $post_id ] );
							
							update_post_meta( $post_id, 'has_been_twittered', 'yes' );	
							
							wordtwit_save_settings( $settings );
						} else {
							global $wordtwit_oauth;
							if ( $wordtwit_oauth->was_duplicate_tweet() ) {
								unset( $settings['tweet_queue'][ $post_id ] );
							
								update_post_meta( $post_id, 'has_been_twittered', 'previously' );		
								
								wordtwit_save_settings( $settings );	
							}
						}
					}
				}
				
				fclose( $f );
			}
			
			$settings['last_tweet_time'] = time();
			wordtwit_save_settings( $settings );
		}
	}
}

function wordtwit_is_tweet_queue_enabled() {
	$settings = wordtwit_get_settings();	
	
	if ( isset( $settings['tweet-queue-enabled'] ) ) {
		return $settings['tweet-queue-enabled'];	
	}
	
	return false;
}

function wordtwit_do_tweet( $post_id ) {
	$settings = wordtwit_get_settings();
	
	$message = wordtwit_get_message( $post_id );
	
	// If we have a valid message, Tweet it
	// this will fail if the Tiny URL service is done
	if ( $message ) {	
		// If we successfully posted this to Twitter, then we can remove it from the queue eventually
		if ( twit_update_status( $message ) ) {	
			return true;
		}
	} 
	
	return false;
}

function wordtwit_init() {
	global $twit_plugin_prefix;
	global $wordtwit_cache_time;
	global $wpdb;
	global $table_prefix;
	
	if ( isset( $_GET['wordtwit_oauth'] ) ) {
		global $wordtwit_oauth;
		
		$settings = wordtwit_get_settings();
		$result = $wordtwit_oauth->get_access_token( $settings['oauth_request_token'], $settings['oauth_request_token_secret'], $_GET['oauth_verifier'] );
		if ( $result ) {
			$settings['oauth_access_token'] = $result['oauth_token'];
			$settings['oauth_access_token_secret'] = $result['oauth_token_secret'];
			$settings['user_id'] = $result['user_id'];
			
			$result = $wordtwit_oauth->get_user_info( $result['user_id'] );
			if ( $result ) {
				$settings['profile_image_url'] = $result['user']['profile_image_url'];
				$settings['screen_name'] = $result['user']['screen_name'];
				if ( isset( $result['user']['location'] ) ) {
					$settings['location'] = $result['user']['location'];
				} else {
					$settings['location'] = false;
				}	
			}
			
			wordtwit_save_settings( $settings );

			header( 'Location: ' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wordtwit.php' );
			die;
		}
	} else if ( isset( $_GET['wordtwit'] ) && $_GET['wordtwit'] == 'deauthorize' ) {
		$settings = wordtwit_get_settings();
		$settings['oauth_access_token'] = '';
		$settings['oauth_access_token_secret'] = '';
		$settings['user_id'] = '';
		$settings['tweet_queue'] = array();
		
		wordtwit_save_settings( $settings );

		header( 'Location: ' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wordtwit.php' );
		die;
	}
	
	$settings = wordtwit_get_settings();
	
	if ( $settings['enable_content_conversion'] ) {
		add_action( 'the_content', 'wordtwit_content' );	
	}
	
	if ( $settings['activation_time'] == 0 ) {
		$settings['activation_time'] = time();
		
		wordtwit_save_settings( $settings );	
	}
	
	if ( strpos( $_SERVER["REQUEST_URI"], "wp-admin" ) !== false ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wordtwit.php' ) {
			wp_enqueue_script( 'fancybox', compat_get_plugin_url('wordtwit') . '/js/fancybox_1.2.5.js', array( 'jquery' ) );
			wp_enqueue_script( 'wordtwitadmin', compat_get_plugin_url('wordtwit') . '/js/wordtwit_admin_2.1.js', array( 'jquery' ) );
		}
	}
	
	if ( isset( $_GET['wordtwit_retweet'] ) ) {
		$post_id = (int)$_GET['wordtwit_retweet'];
		
		delete_post_meta( $post_id, 'has_been_twittered' );
		wordtwit_post_now_published( $post_id, true );
		
		header( 'Location: ' . get_bloginfo('home') . $_GET['wordtwit_redirect'] );
		die;
	}
	
	wordtwit_check_table();
	
	// Only service the queue if the Tweet queue is enabled
	if ( wordtwit_is_tweet_queue_enabled() ) {
		wordtwit_service_queue();
	}
	
	// check TinyURl
	$url = $_SERVER["REQUEST_URI"];
	
	if ( strlen( $url ) < 6 ) {
		// might be a tiny URL
		$params = explode( '/', $url );
		if ( count( $params ) == 2 ) {
			$tiny_url = $params[1];
			
			$sql = $wpdb->prepare( "SELECT original FROM " . $table_prefix . "tweet_urls WHERE url = %s",  $tiny_url );
			$result = $wpdb->get_row( $sql );
			if ( $result ) {
				$sql = $wpdb->prepare( "UPDATE " . $table_prefix . "tweet_urls SET views = views + 1 WHERE url = %s",  $tiny_url );
				$wpdb->query( $sql );
				
				$this_site = strtolower( wordtwit_short_home( get_bloginfo('home') ) );
				$short_url = strtolower( str_replace( 'www.', '', $result->original ) );
				if ( strpos( $short_url, $this_site ) === false && $settings['enable_banner'] ) {
					// external site
					header( "Content-type: text/html" );
					
					$profile = $settings['profile'];
					
					
					echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\"  dir=\"ltr\" lang=\"en-US\">\n";
					echo "\t<head>\n";
					echo "\t<title>" . $result->original . " - Courtesy of " . $profile['user']['name'] . "</title>\n";
					echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . compat_get_plugin_url( 'wordtwit' ) . "/css/banner.css\"></link>\n";
					echo "\t<script type=\"text/javascript\" src=\"" . get_bloginfo('wpurl') . "/wp-includes/js/jquery/jquery.js?ver=1.2.6\"></script>\n";
					echo "\t<script type=\"text/javascript\" src=\"" . compat_get_plugin_url( 'wordtwit' ) . "/js/banner.js\"></script>\n";
					echo "</head>\n";
					echo "<body>\n";

					echo "\t<div id=\"banner\"><img src=\"" . $profile['user']['profile_image_url'] . "\" alt=\"\" /><strong><a href=\"http://twitter.com/" . $profile['user']['screen_name'] . "\">Follow " . $profile['user']['name'] . " On Twitter</a></strong><p id=\"blog-url\"><a href=\"" . get_bloginfo('home') . "\">" . str_replace( 'www.', '', str_replace('http://', '', strtolower( get_bloginfo('home') ) ) ) . "</a></p>\n";
					echo "<p id=\"latest\">";
					echo "<h2>Latest posts from '" . get_bloginfo('name') . "'</h2>\n";
					echo "<ul>";
					$query = new WP_Query('showposts=3');
					$wti = 1;
					while ( $query->have_posts() ) { 
						$query->the_post();
						echo '<li class="banner-posts" id="banner-post-' . $wti . '"><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
						$wti++;	
					}
					echo "</ul>\n";
					echo "</p>\n";
					echo "<div id=\"close-frame\"><img src=\"" . compat_get_plugin_url( 'wordtwit' ) . "/images/frame.png\" alt=\"\" /><a href=\"" . $result->original . "\">View Original</a></div>";						
					echo "</div>\n";
				
					echo "\t<div id=\"site-frame\"><iframe src=\"" . $result->original . "\" /></div>\n";					
					echo "</body>\n";
					echo "</html>\n";
					die;				
				} else {
					// internal site
					header( "HTTP/1.1 301 Moved Permanently" ); 
					header( "Location: " . $result->original ); 					
				}
				die;
			}	
		}
	}
}

function wordtwit_get_profile_url() {
	$settings = wordtwit_get_settings();
		
	return $settings['profile_image_url'];
}

function wordtwit_get_recent_tweets( $max_tweets = 5 ) {
	$snoopy = new Snoopy;
	$snoopy->agent = 'WordTwit ' . $wordtwit_version;

	$settings = wordtwit_get_settings();
   
	$last_fetch_time = get_option( 'wordtwit_last_fetch_time', 0 );
	$time_since_last = time() - $last_fetch_time;
   
   if ( $time_since_last > WORDTWIT_CACHE_TIME ) {	   
		$xml_output = false;
   	
		$output = '';
   		if ( function_exists( 'wp_remote_request' ) && true ) {
			$options = array( 'method' => 'GET', 'timeout' => 5 );
			$options['headers'] = array(
				'User-Agent' => 'WordPress/' . get_bloginfo("version") . '/WPtouch-Pro',
			 	'Referer' => get_bloginfo("url")
			);
		
			$raw_response = wp_remote_request( "http://search.twitter.com/search.atom?q=from:{$settings['screen_name']}", $options );   		
			if ( !is_wp_error( $raw_response ) ) {
				$output = $raw_response['body'];
			}   
   		} else {
			$result = $snoopy->fetch( "http://search.twitter.com/search.atom?q=from:{$settings['screen_name']}" );
			if ( $result ) {
			   $output = $snoopy->results;  
			}
   		}
   	
   		if ( $output ) {		   
			$xml_output = wordtwit_parsexml( $output );
		   
			update_option( 'wordtwit_recent_tweets', $xml_output );
   		}
		
		update_option( 'wordtwit_last_fetch_time', time() );
		$slice = @array_slice( $xml_output['feed']['entry'], 0, $max_tweets );
		
		if ( !isset( $slice['id'] ) ) {
			return $slice;	
		} else {
			return array( $slice );
		}
	} else {
		$xml_output = get_option( 'wordtwit_recent_tweets', false );
		$slice = @array_slice( $xml_output['feed']['entry'], 0, $max_tweets );
   	
		if ( !isset( $slice['id'] ) ) {
			return $slice;	
		} else {
			return array( $slice );
		}
	}
	
	return false;
}

function twit_hit_server( $location, $username, $password, &$output, $post = false, $post_fields = '' ) {
   global $wordtwit_version;
   $output = '';
   
   $snoopy = new Snoopy;
   $snoopy->agent = 'WordTwit ' . $wordtwit_version;
   
   if ( $username ) {
      $snoopy->user = $username;
      if ( $password ) {
         $snoopy->pass = $password;      
      }
   }
   
   if ( $post ) {
      // need to do the actual post
      $result = $snoopy->submit( $location, $post_fields );
      if ( $result ) {
			$output = $snoopy->results;
			return true;  
      }
   } else {
      $result = $snoopy->fetch( $location );
      if ( $result ) {
         $output = $snoopy->results;  
      }
      
      $code = explode( ' ', $snoopy->response_code );
      if ( $code[1] == 200) {
         return true;
      } else {
         return false;
      }
   }
}

function twit_get_ow_ly_url( $link ) {
	$output = false;
	
	$result = twit_hit_server( 'http://ow.ly/api/1.0/url/shorten?apiKey=' . WORDTWIT_HOOTSUITE_API_KEY . '&longUrl=' . $link, '', '', $output );
	
	if ( $result ) {
		if ( preg_match( '#"shortUrl":"(.*)"#iUs', $output, $matches ) ) {
			return stripslashes( $matches[1] );
		} 
	}
	
	return $output;
}


function twit_update_status( $new_status ) {
	global $wordtwit_oauth;
	$settings = wordtwit_get_settings();

	if ( isset( $settings['oauth_access_token'] ) && isset( $settings['oauth_access_token_secret'] ) ) {
		return $wordtwit_oauth->update_status( $settings['oauth_access_token'], $settings['oauth_access_token_secret'], $new_status );
	}

	return false;
}

function twit_has_tokens() {
	$settings = wordtwit_get_settings();
	
	return ( $settings[ 'oauth_access_token' ] && $settings['oauth_access_token_secret'] );
}

function wordtwit_is_valid() {
	return twit_has_tokens();
}

function twit_get_tiny_url( $link ) {
   $output = false;
   
   $result = twit_hit_server( 'http://tinyurl.com/api-create.php?url=' . $link, '', '', $output );
   
   return $output;
}

function twit_get_bitly_views( $status_message ) {
	$settings = wordtwit_get_settings();
	
	$success = preg_match( '#http://(.*)#', $status_message, $matches );
	if ( $success ) {		
		$short_url = $matches[0];
		
		$output = false;
		$url = 'http://api.bit.ly/stats?version=2.0.1&shortUrl=' . $short_url . '&format=xml&history=1&login=' . $settings['bitly-user-name'] . '&apiKey=' . $settings['bitly-api-key'];
		$result = twit_hit_server( $url, '', '', $output );	

		if ( $result ) {
			preg_match( '#<userClicks>(.*)</userClicks>#iUs', $output, $clicks );
			
			$num = (string)$clicks[0];
			return $num;	
		}
	}
	
	return 0;
}

function twit_get_bitly_url( $link ) {
	$settings = wordtwit_get_settings();
		
	$output = false;
	
	$result = twit_hit_server( 'http://api.bit.ly/shorten?version=2.0.1&longUrl=' . urlencode( $link ) . '&format=xml&history=1&login=' . $settings['bitly-user-name'] . '&apiKey=' . $settings['bitly-api-key'], '', '', $output );
	
	preg_match( '#<shortUrl>(.*)</shortUrl>#iUs', $output, $url );
	
	if ( isset( $url[1] ) ) {
		return $url[1];	
	} else {
		return false;
	}
}

function twit_get_isgd_url( $link ) {
	$output = false;
	
	$result = twit_hit_server( 'http://is.gd/api.php?longurl=' . urlencode( $link ), '', '', $output );
	if ( $result && strlen( $output ) ) {
		return $output;	
	} else {
		return false;	
	}
}

function wordtwit_make_tinyurl( $link, $update = true, $post_id ) {
	if ( strpos( $link, 'http://' ) === false ) {
		return $link;	
	}
	
	$settings = wordtwit_get_settings();

	$short_link = false;
	
	$utm_add_on = "utm_source=wordtwit&utm_medium=social&utm_campaign=wordtwit";
	if ( isset( $settings['enable-utm'] ) && $settings['enable-utm'] ) {
		if ( strpos( $link, "?" ) !== false ) {
			$link = $link . "&" . $utm_add_on;	
		} else {
			$link = $link . "?" . $utm_add_on;
		}
	}
	
	if ( $settings['url_type'] == 'tinyurl' ) { 
		$short_link = twit_get_tiny_url( $link );			
	} else if ( $settings['url_type'] == 'owly' ) {
		$short_link = twit_get_ow_ly_url( $link );
	} else if ( $settings['url_type'] == 'local' ) {
		$short_link = wordtwit_tinyurl( $link, $update );				
	} else if ( $settings['url_type'] == 'bitly' ) {
		if ( isset( $settings['bitly-user-name'] ) && strlen( $settings['bitly-user-name'] ) ) {
			$short_link = twit_get_bitly_url( $link );
		} else {
			// fall back to tinyurl if they forgot to put in API information
			$short_link = twit_get_tiny_url( $link );
		}
	} else if ( $settings['url_type'] == 'isgd' ) {
		$short_link = twit_get_isgd_url( $link );
	} else if ( $settings['url_type'] == 'post_id' ) {
		if ( isset( $settings['enable-utm'] ) && $settings['enable-utm'] ) {
			$short_link = wordtwit_post_id_url_base() . $post_id . "&" . $utm_add_on; 		
		} else {
			$short_link = wordtwit_post_id_url_base() . $post_id; 				
		}
	}

	return $short_link;
}

function wordtwit_get_message( $post_id ) {
	$my_post = get_post( $post_id );
	if ( $my_post ) {		
		$settings = wordtwit_get_settings();
		
		$message = $settings['message'];
		$message = str_replace( '[title]', $my_post->post_title, $message );
		
		$tinyurl = wordtwit_make_tinyurl( get_permalink( $post_id ), true, $my_post->ID );
		
		if ( $tinyurl ) {
			$message = str_replace( '[link]', $tinyurl, $message );		
			
			return $message;
		}
	} 	
	
	return false;	
}

function wordtwit_post_now_published( $post_id, $force_tweet = false ) {
	global $twit_plugin_prefix;
	
	$settings = wordtwit_get_settings();
	
	$wt_tags = $settings['tags'];
	$wt_reverse = $settings['reverse'];
		
	$activation_time = $settings['activation_time'];
	$cur_time = time();

	query_posts( 'p=' . $post_id );
	if ( have_posts() ) {
		the_post();
		
		global $post;
		$cur_time = strtotime( $post->post_date );
		if ( $cur_time < $activation_time && !$force_tweet ) {
			//echo $cur_time . " " . $activation_time . " " . time() . " " ;
			//echo 'leaving'; die;
			// don't publish old posts
			return;
		}
		
		$can_tweet = true;
		
		// check tags
		if ( count( $wt_tags ) ) {
						
			// we have a tag or a category
			$new_taxonomy = array();
						
			$post_tags = get_the_tags();
			if ( $post_tags ) {
				foreach ( $post_tags as $some_tag ) {
					$new_taxonomy[] = strtolower( $some_tag->name );
				}
			}
			
			$post_categories = get_the_category();
			if ( $post_categories ) {
				foreach ( $post_categories as $some_category ) {
					$new_taxonomy[] = strtolower( $some_category->name );
				}
			}
			
			$category_hits = array_intersect( $wt_tags, $new_taxonomy );	
			
			if ( $wt_reverse ) {
				$can_tweet = ( count( $category_hits ) == 0);
			} else {
				$can_tweet = ( count( $category_hits ) > 0);
			}
		}

		$has_been_twittered = get_post_meta( $post_id, 'has_been_twittered', true );
		if ( $has_been_twittered !== 'yes' && $has_been_twittered !== 'previously' && $can_tweet ) {
			if ( wordtwit_is_tweet_queue_enabled() ) {
				$new_tweet = new stdClass;
				
				$new_tweet->post_id = $post_id;
				$new_tweet->queue_time = time();
				
				if ( !isset( $settings['tweet_queue'][ $post_id ] ) ) {
					$settings['tweet_queue'][ $post_id ] = $new_tweet;
					
					// Sort it from oldest to newest
					ksort( $settings['tweet_queue'] );
					
					wordtwit_save_settings( $settings );
				}
		
				add_post_meta( $post_id, 'has_been_twittered', 'pending' );
			} else {
				wordtwit_do_tweet( $post_id ); 
				
				add_post_meta( $post_id, 'has_been_twittered', 'yes' );
			}
		}
	}
}

function wordtwit_retweet_link() { 
	echo get_bloginfo( 'home') . '?wordtwit_retweet=' . $_GET['post'] . '&wordtwit_redirect=' . urlencode( $_SERVER['REQUEST_URI'] );	
}

function wordtwit_admin_css() {
	if ( (isset( $_GET['page'] ) && $_GET['page'] == 'wordtwit.php') || ( strpos( $_SERVER["REQUEST_URI"], "wp-admin/post.php" ) !== false ) || ( strpos( $_SERVER["REQUEST_URI"], "post-new.php" ) !== false ) ) {
		echo '<link rel="stylesheet" type="text/css" href="' . compat_get_plugin_url('wordtwit') . '/css/wordtwit-admin.css" />';
		echo '<link rel="stylesheet" type="text/css" href="' . compat_get_plugin_url('wordtwit') . '/css/bnc-global.css" />';
	}
}

function bnc_stripslashes_deep( $value ) {
	$value = is_array($value) ?
	array_map( 'bnc_stripslashes_deep', $value ) :
	stripslashes($value);
	return $value;
}

function wordtwit_options_subpanel() {
	if ( get_magic_quotes_gpc() ) {
		$_POST = array_map( 'bnc_stripslashes_deep', $_POST );
	    $_GET = array_map( 'bnc_stripslashes_deep', $_GET );
	    $_COOKIE = array_map( 'bnc_stripslashes_deep', $_COOKIE );
	    $_REQUEST = array_map( 'bnc_stripslashes_deep', $_REQUEST );
	}

	global $twit_plugin_name;
	global $twit_plugin_prefix;
	
	$settings = wordtwit_get_settings();

  	if ( isset($_POST['info_update']) ) {
		if ( isset($_POST['username'] ) ) {
			$username = $_POST['username'];
		} else {
			$username = '';
		}
		
		if ( isset( $_POST['enable_content_conversion'] ) ) {
			$settings['enable_content_conversion'] = true;	
		} else {
			$settings['enable_content_conversion'] = false;
		}

		if ( isset($_POST['password']) ) {
			$password = $_POST['password'];
		} else {
			$password = '';
		}

		if ( isset($_POST['message']) ) {
			$message = $_POST['message'];
		} else {
			$message = '';
		}
		
		if ( isset( $_POST['wordtwit-url-type'] ) ) {
			$settings['url_type'] = $_POST['wordtwit-url-type'];	
		}
		
		if ( isset( $_POST['enable_banner'] ) ) { 
			$settings['enable_banner'] = true;
		} else {
			$settings['enable_banner'] = false;	
		}		
		
		if ( isset( $_POST['bitly-api-key'] ) ) { 
			$settings['bitly-api-key'] = $_POST['bitly-api-key'];
		} else {
			$settings['bitly-api-key'] = '';	
		}		
		
		if ( isset( $_POST['bitly-user-name'] ) ) { 
			$settings['bitly-user-name'] = $_POST['bitly-user-name'];
		} else {
			$settings['bitly-user-name'] = '';	
		}			
		
		if ( isset( $_POST['alternate-domain'] ) ) {
			if ( strlen( $_POST['alternate-domain'] ) == 0 ) {
				$settings['alternate_domain'] = '';
			} else {		
				$settings['alternate_domain'] = $_POST['alternate-domain'];	
				$settings['alternate_domain'] = strtolower( rtrim( $settings['alternate_domain'], '/' ) );
				if ( strpos( $settings['alternate_domain'], 'http://' ) === false ) {
					$settings['alternate_domain'] = 'http://' . $settings['alternate_domain'];	
				}
			}
		}
		
		if ( isset( $_POST['wordtwit-reverse'] ) ) {
			$reverse = true;
		} else {
			$reverse = false;
		}	
		
		if ( isset( $_POST['remove-www'] ) ) {
			$settings['remove-www'] = true;
		} else {
			$settings['remove-www'] = false;	
		}
			
		if ( isset( $_POST['enable-utm'] ) ) {
			$settings['enable-utm'] = true;
		} else {
			$settings['enable-utm'] = false;	
		}		
		
		if ( isset( $_POST['enable-tweet-queue'] ) ) {
			$settings['enable-tweet-queue'] = true;	
		} else {
			$settings['enable-tweet-queue'] = false;
		}
		
		$wt_tags = explode( ",", $_POST['wordtwit-tags'] );

		$settings['username'] = $username;
		$settings['password'] = $password;
		
		$new_tags = array();
		foreach ( $wt_tags as $tags ) {
			$clean_tag = strtolower( rtrim( ltrim( $tags ) ) );
			if ( strlen( $clean_tag ) ) {
				$new_tags[] = 	$clean_tag;
			}
		}
		
		$settings['tags'] = $new_tags;
		$settings['message'] = stripslashes( $message );
		$settings['reverse'] = $reverse;
		
		wordtwit_save_settings( $settings );
		update_option( 'wordtwit_last_fetch_time', 0 );
	} 
	
	// The Master Kill Switch
 	elseif ( isset( $_POST['reset'] ) ) {
		update_option( 'wordtwit_settings', '' );
		update_option( 'wordtwit_last_fetch_time', 0 );
    }

   include( 'html/options.php' );
}

function wordtwit_save_settings( $settings ) {
	update_option( 'wordtwit_settings', $settings );
}

function wordtwit_check_table() {
	global $table_prefix;
	global $wpdb;
	
	$table_name = $table_prefix . 'tweet_urls';
	$sql = "describe $table_name";
	$result = $wpdb->get_results( $sql );
	if ( !$result ) {
		// create table		
		$sql = "CREATE TABLE `" . $table_name . "` (" .
		  "`id` int(10) unsigned NOT NULL auto_increment," .
		  "`original` varchar(512) collate utf8_bin default NULL," .
		  "`url` varchar(7) collate utf8_bin NOT NULL," .
		  "`views` int(11) NOT NULL default '0'," .
		  "`post_id` int(11) NOT NULL default '0'," .
		  "PRIMARY KEY  (`id`)," .
		  "UNIQUE KEY `url_index` (`url`)," .
		  "KEY `original` (`original`)," .
		  "KEY `url_index_2` (`url`)" .
		") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
		
		$result = $wpdb->query( $sql );
	}
}

function wordtwit_post_id_url_base() {
	$settings = wordtwit_get_settings();
	$url = get_bloginfo( 'home' ) . '/?p=';
	if ( isset( $settings['remove-www'] ) && $settings['remove-www'] ) {
		$url = str_replace( 'www.', '', $url );
	}
	
	return $url;
}

function wordtwit_add_plugin_option() {
	global $twit_plugin_name;
	if (function_exists('add_options_page')) {
		add_options_page($twit_plugin_name, $twit_plugin_name, 9, basename(__FILE__), 'wordtwit_options_subpanel');
   }	
}

function wordtwit_content( $content ) {
	global $post;
	
	preg_match_all( "'href=\"?(.[^\"\>]*)\"?(.[^\>]*)>(.[^\<]*)</a>'im",$content,$matches );
	if ( $matches ) {
		foreach ( $matches[1] as $url ) {
			$update_post_num = false;
			if ( strtolower( $url ) == strtolower( get_permalink() ) ) {
				$update_post_num = true;
			}
			
			$tinyurl = wordtwit_make_tinyurl( $url, $update_post_num, $post->ID );
			$content = str_replace( $url, $tinyurl, $content );
		}	
	}
	
	return $content;
}
add_action( 'admin_menu', 'wordtwit_add_plugin_option' );
add_filter( 'plugin_action_links', 'wordtwit_settings_link', 9, 2 );

?>
