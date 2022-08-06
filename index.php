<?php 
/*
Plugin Name: ALT OER Conference
Plugin URI:  https://
Description: Allowing the creation of JSON through ACF Structures for MBS's javascript ingestion
Version:     1.0
Author:      Tom Woodward
Author URI:  https://bionicteaching.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// UnderStrap's includes directory.
$alt_conf_inc_dir = plugin_dir_path(__FILE__)  . 'inc';

$alt_conf_includes = array(
   '/custom-post-types.php',                          // Load custom post types and taxonomies
   '/acf.php'                          // Load custom post types and taxonomies
);


// Include files.
foreach ( $alt_conf_includes as $file ) {
   require_once $alt_conf_inc_dir . $file;
}

function acf_to_rest_api_presentation($response, $post, $request) {
    if (!function_exists('get_fields')) return $response;

    if (isset($post)) {
        $acf = get_fields($post->id);
        $response->data['mbs'] = $acf;
    }
    return $response;
}
add_filter('rest_prepare_presentation', 'acf_to_rest_api_presentation', 10, 3);



// add_filter( 'acf/rest_api/presentation/get_fields', function( $data, $response ) {
//    if ( isset( $data['acf']['session_description'] ) ) {
//       // my change here
//       $data['acf']['session_description'] = apply_filters('wpautop', $data['acf']['session_description'] );
//    }

//    return $data;
// }, 10, 2 );

//save acf json

//deal with me making the import do  last comma first . . . 

add_filter( 'the_title', 'oerxdomain_flip_speaker_name' );
function oerxdomain_flip_speaker_name($title){
   global $post;
   $post_id = $post->ID;
   $type = get_post_type($post_id);
   if($type === 'speaker'){
      return oerxdomain_flip_it($title);
   } else {
      return $title;
   }
}

function oerxdomain_flip_it($title){
   if(strpos($title, ',')>0){ //server is not running php 8 so str_contains is not an option
      $pieces = explode(',', $title);
      $first = $pieces[1];
      $last = $pieces[0];
      return $first . ' ' . $last;
   } else {
      return $title;
   }
   
}

add_filter('acf/settings/save_json', 'oerxdomains_json_save_point');
 
function oerxdomains_json_save_point( $path ) {
    
    // update path
    $path = plugin_dir_path(__FILE__) . '/acf-json'; //replace w get_stylesheet_directory() for theme
    
    
    // return
    return $path;
    
}


// load acf json
add_filter('acf/settings/load_json', 'oerxdomains_json_load_point');

function oerxdomains_json_load_point( $paths ) {
    
    // remove original path (optional)
    unset($paths[0]);
    
    
    // append path
    $paths[] = plugin_dir_path(__FILE__) . '/acf-json';//replace w get_stylesheet_directory() for theme
    
    
    // return
    return $paths;
    
}

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

function altoer_custom_mime_types( $mimes ) {

        // New allowed mime types.
       
        $mimes['csv'] = 'text/csv';

    return $mimes;
}
add_filter( 'upload_mimes', 'altoer_custom_mime_types', 999,1 );