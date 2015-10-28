<?php
/*
Plugin Name: Exclude From Search
Plugin URI: http://pateason.com
Description: Exclude specified pages, posts, and other post types search queries.
Author: Pat Eason
Version: 1.11
Author URI: http://pateason.com
*/

// ----- Meta Box

	//Meta Box Content
	function searchMetaBoxCont( $post ) {
	  wp_nonce_field( 'searchMetaBoxCont', 'searchMetaBox_nonce' );

	  $value = get_post_meta( $post->ID, 'excludeFromSearch', true );

	  echo '<label for="excludeSearch">Exclude from Searches?</label>';
	  echo '<input type="checkbox" name="exclude_search"';
	  	if($value){
		  	echo 'checked="checked" ';
	  	}
	  echo '>';

	}

	//Render Meta Box
	function searchMetaBox() {
		//Get Posttypes
		$args = array(
		   'public'   => true,
		);
		$output = 'names';
		$operator = 'and';

		$postTypes = get_post_types( $args, $output, $operator );
	    foreach ( $postTypes as $postType ) {

	        add_meta_box(
	            'excludeFromSearch',
	            __( 'Exclude from Search' ),
	            'searchMetaBoxCont',
	            $postType,
	            'side',
	            'high'
	        );
	    }
	}
	add_action( 'add_meta_boxes', 'searchMetaBox' );

// ----- Exclude Column
function excludeColumn( $column ) {
    $column['excludeSearch'] = 'Excluded from Search?';
    return $column;
}
add_filter( 'manage_posts_columns', 'excludeColumn' );
function excludecolumn_cont( $column_name, $post_id ) {
    $custom_fields = get_post_custom( $post_id );
    switch ($column_name) {
        case 'excludeSearch' :
        	$excMeta = get_post_meta($post_id, 'excludeFromSearch', true);
        	if($excMeta){
	            echo 'Excluded';
        	}
            break;

        default:
    }
}
add_filter( 'manage_posts_custom_column', 'excludeColumn_cont', 10, 2 );

// ----- Save to Site Meta
	function searchMeta_save( $post_id ) {
	  // Check if our nonce is set.
	  if ( ! isset( $_POST['searchMetaBox_nonce'] ) )
	    return $post_id;

	  $nonce = $_POST['searchMetaBox_nonce'];

	  // Verify that the nonce is valid.
	  if ( ! wp_verify_nonce( $nonce, 'searchMetaBoxCont' ) )
	      return $post_id;

	  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	      return $post_id;

	  // Check the user's permissions.
	  if ( 'page' == $_POST['post_type'] ) {

	    if ( ! current_user_can( 'edit_page', $post_id ) )
	        return $post_id;

	  } else {

	    if ( ! current_user_can( 'edit_post', $post_id ) )
	        return $post_id;
	  }

	  /* OK, its safe for us to save the data now. */

	  // Get user input.
	  if(isset($_POST['exclude_search'])){
		  $searchKey = true;
		  // Update the meta field in the database.
		  update_post_meta( $post_id, 'excludeFromSearch', $searchKey );
	  }else{
		  // Delete the meta field in the database.
		  delete_post_meta( $post_id, 'excludeFromSearch' );
	  }


	}
	add_action( 'save_post', 'searchMeta_save' );

// ----- Search Pre-Get
add_action( 'pre_get_posts', 'exclude_search' );
function exclude_search( $query ) {
    if (!is_admin() && $query->is_search){
        $query->set('meta_query' , array(
        	array(
        		'key' => 'excludeFromSearch',
        		'value' => 1,
        		'compare' => 'NOT EXISTS'
        	)
        ));
    }
}

?>