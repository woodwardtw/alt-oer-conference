<?php
/**
 * acf json save
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page('Conference Import');
	
}

//from https://rubendivine.com/run-php-function-when-updating-wordpress-acf-options-page/
add_action('acf/save_post', 'alt_oer_csv_import');
function alt_oer_csv_import(){
    $screen = get_current_screen();
    if (strpos($screen->id, "acf-options-conference-import") == true) {
        // Function code here: 
        $speakers = get_field('speaker_csv', 'option');
        $presentations = get_field('presentation_csv', 'option');
        if($presentations){
        	alt_oer_parse_sessions_csv($presentations);
        }
        if($speakers){
        	alt_oer_parse_speaker_csv($speakers);
        }
    }
}

function alt_oer_csv_make_sessions($title, $description){
	$args = array(
	  'post_title'    => wp_strip_all_tags($title),
	  'post_type'     => 'presentation',
	  'post_status'  => 'publish',	 
	);
	$post_id = wp_insert_post($args);
	update_field('session_description', $description, $post_id);
}

function alt_oer_parse_sessions_csv($file_path){
	 if(isset($file_path)){
    
    	$filename = $file_path;    
    
        $file = fopen($filename, "r");
        fgetcsv($file);
          while (($getData = fgetcsv($file, 1000, ",")) !== FALSE) {
          	$title = $getData[0];
          	$description = $getData[1];
          	if($title != ''){
          		alt_oer_csv_make_sessions($title, $description);
          	}
           }
      
           fclose($file);  
     }   
}


function alt_oer_csv_make_speakers($first, $last){
	$title = $last . ', ' . $first;
	$args = array(
	  'post_title'    => wp_strip_all_tags($title),
	  'post_type'     => 'speaker',
	  'post_status'  => 'publish',	 
	);
	$post_id = wp_insert_post($args);
	update_field('first_name', $first, $post_id);
	update_field('last_name', $last, $post_id);
}

function alt_oer_parse_speaker_csv($file_path){
	 if(isset($file_path)){
    
    	$filename = $file_path;    
    
        $file = fopen($filename, "r");
        fgetcsv($file);
          while (($getData = fgetcsv($file, 1000, ",")) !== FALSE) {
          	$first = $getData[0];
          	$last = $getData[1];
          	if($first != '' && $last != ''){
          		alt_oer_csv_make_speakers($first, $last);
          	}
           }
      
           fclose($file);  
     }   
}

add_shortcode( 'mess', 'alt_oer_clean_mess' );


function alt_oer_clean_mess(){
	$args = array(
			'post_type' => array('speaker'),
			'title' => ',',
	);


	$the_query = new WP_Query( $args );

	// The Loop
	if ( $the_query->have_posts() ) :
	while ( $the_query->have_posts() ) : $the_query->the_post();
	  // Do Stuff
		wp_trash_post(get_the_ID());
	endwhile;
	endif;

	// Reset Post Data
	wp_reset_postdata();		
}