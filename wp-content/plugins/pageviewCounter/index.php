<?php
/*
Plugin Name: Page View Counter
Plugin URI: http://pateason.com
Description: Calculate view count whenever a page is loaded. Displays a page's view count in the Dashboard, totally sortable so you can find what the most popular pages,posts, etc. on your site are.
Author: Pat Eason
Version: 0.12
Author URI: http://pateason.com

Notes : 2.26.14 - Changed sorting function to actually work. Was looking for ViewCount instead of viewCount.
		2.27.14 - Resolved situation where view count would increment when viewing or searching for posts in CPT in Dashboard.

*/

//Pageview counter
function pageview_counter($post){
	global $post;
	$post_id = $post->ID;
	$viewcount = get_post_meta($post_id, 'viewCount', true);
	//echo $viewcount;
	if(!is_admin()){
		if($viewcount){
			++$viewcount;
			update_post_meta($post_id, 'viewCount', $viewcount);
		}else{
			$viewcount = 1;
			add_post_meta($post_id, 'viewCount', $viewcount, true);
		}
	}
}
add_action('wp','pageview_counter');
// ----- Pageview Column
function pageviewColumn( $column ) {
    $column['viewCount'] = 'View Count';
    return $column;
}
add_filter( 'manage_posts_columns', 'pageviewColumn' );
add_filter( 'manage_pages_columns', 'pageviewColumn' );
function pageviewColumn_cont( $column_name, $post_id ) {
    $custom_fields = get_post_custom( $post_id );
    switch ($column_name) {
        case 'viewCount' :
        	$excMeta = get_post_meta($post_id, 'viewCount', true);
        	if($excMeta){
	            echo $excMeta;
        	}
            break;
        default:
    }
}
add_filter( 'manage_posts_custom_column', 'pageviewColumn_cont', 10, 2 );

function pageviewColumn_sortable( $columns ) {
    $columns['viewCount'] = 'View Count';
    return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'pageviewColumn_sortable' );
add_filter( 'manage_edit-page_sortable_columns', 'pageviewColumn_sortable' );
add_action('wp', 'pageviewColumn_sortableCPT');
function pageviewColumn_sortableCPT(){
   $args=array(
     'public'   => true,
     '_builtin' => false
   );
   $post_types=get_post_types($args);
   foreach ($post_types  as $post_type ) {
      add_filter( 'manage_edit-'.$post_type.'_sortable_columns', 'pageviewColumn_sortable' );
   }
}
add_action( 'pre_get_posts', 'pageView_orderby' );
function pageView_orderby( $query ) {
    if( ! is_admin() )
        return;

    $orderby = $query->get( 'orderby');

    if( 'viewCount' == $orderby || 'ViewCount' == $orderby) {
        $query->set('meta_key','viewCount');
        $query->set('orderby','meta_value_num');
        $query->set('order','desc');

    }
}


// ------ Front-end widget


// ------ Dashboard metabox

?>