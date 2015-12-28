<?php
 
//include('includes/imis.php');
//include('functions/cpt.php');
//include('functions/custom-taxonomies.php');

 

//Roles and Workflow
include('includes/roles.php');
include('includes/workflow.php');
include('includes/email.php');
include('includes/twitter.php');

 
/********************************************* SWITCHES/VARIABLE SETUP ***********************************************/

$test = false;														// true = TEST DATABASE, false = PRODUCTION DATABASE
define ("EMAILCRON", FALSE);										// true = send email at import cron, false = do not send email
define ("IMPORT_PER_CRON", 1000);									// maximum number of rows to import per cron job - tweak to make sure script doesn't timeout
define ("SP_SECURITY_PWD", "F46DB250-B294-4B3D-BC95-45B7DDFEE334"); // Stored Procedure Security Password
define ("SOAP_ACCOUNT_PWD", "300A6E01-5DB9-4217-A2DE-CDB2F08FE1F7");// SOAP web services security password
define ("MAX_WP_USERS_UPDATED", '400');								// How many users updated in WP from the wp_aeh_import_full table

/****************************************** PROD or TEST? IMIS STORED PROCS ETC. *************************************/
if ($test){
	define ("SP_IMPORT_USERS",   "test_importUsers");				// main import users cron job
	define ("SP_GET_IMIS_USER",  "test_GetImisUser");				// retrieve info on one iMIS user
	define ("SP_GET_ROW_COUNT",  "test_GetRowCount");				// get total row count for main import users table from iMIS
	define ("SP_LOGIN_TIME",     "test_MESH_UD_Security");			// update last login time in iMIS
	define ("SP_DOES_USER_EXIST","test_DoesUserExist");				// check if user already exists in iMIS from their email address
	define ("SP_GET_TITLES",     "test_GetTitles");					// cron to get user TITLES and set serialized value in WP Options table
	define ("SP_WEB_INTERESTS",  "test_GetWebInterests");			// cron to get WEB_INTEREST and set serialized value in WP Options table
	define ("SP_COMPANY_LIST",  "test_GetCompanyList");				// cron to get COMPANY and HQ address info and set serialized value in WP Options table
	define ("IMIS_SOAP_URL",'http://isgweb.naph.org/ibridge_test/Account.asmx?wsdl'); 								// URL for test SOAP Client comms with iMIS
	define ("IMIS_POST_URL",'http://isgweb.naph.org/ibridge_test/DataAccess.asmx/ExecuteDatasetStoredProcedure');	// URL for test POST comms (read) with iMIS
	define ("SP_POST_UPDATE_URL", 'http://isgweb.naph.org/ibridge_test/DataAccess.asmx/ExecuteStoredProcedure');	// URL for POST execute SP on Account Updates
	define ("SOAP_DEMOG_UPDATE_URL",'http://isgweb.naph.org/ibridge_test/Demographics.asmx?wsdl');					// URL for POST execute SP on Demographic Updates
}else{
	define ("SP_IMPORT_USERS",   "importUsers");					// main import users cron job
	define ("SP_GET_IMIS_USER",  "GetImisUser");					// retrieve info on one iMIS user
	define ("SP_GET_ROW_COUNT",  "GetRowCount");					// get total row count for main import users table from iMIS
	define ("SP_LOGIN_TIME",     "MESH_UD_Security");				// update last login time in iMIS
	define ("SP_DOES_USER_EXIST","DoesUserExist");					// check if user already exists in iMIS from their email address
	define ("SP_GET_TITLES",     "GetTitles");						// cron to get user TITLES and set serialized value in WP Options table
	define ("SP_WEB_INTERESTS",  "GetWebInterests");				// cron to get WEB_INTEREST and set serialized value in WP Options table
	define ("SP_COMPANY_LIST",  "GetCompanyList");					// cron to get COMPANY and HQ address info and set serialized value in WP Options table
	define ("SP_EMAIL_LIST",  "GetEmailList");				     	// cron to get verified email domains and update wp_aeh_email table
	define ("IMIS_SOAP_URL",'http://isgweb.naph.org/ibridge/Account.asmx?wsdl'); 									// URL for SOAP Client comms with iMIS
	define ("IMIS_POST_URL",'http://isgweb.naph.org/ibridge/DataAccess.asmx/ExecuteDatasetStoredProcedure');		// URL for POST comms (read) with iMIS
	define ("SP_POST_UPDATE_URL", 'http://isgweb.naph.org/ibridge/DataAccess.asmx/ExecuteStoredProcedure');			// URL for POST execute SP on Account Updates
	define ("SOAP_DEMOG_UPDATE_URL",'http://isgweb.naph.org/ibridge/Demographics.asmx?wsdl');	
	define ("AUTHENTICATE_URL",'http://isgweb.naph.org/ibridge/Authentication.asmx?wsdl');					// URL for POST execute SP on Demographic Updates
}

/*
iMIS passwords
Account        = 300A6E01-5DB9-4217-A2DE-CDB2F08FE1F7
Activities     = 300A6E01-5DB9-4217-A2DE-CDB2F08FE1F7
Authentication = 27D5F4B5-57B2-4A67-BC82-AA2E1756DED3
DataAccess     = F46DB250-B294-4B3D-BC95-45B7DDFEE334
Demographics   = 300A6E01-5DB9-4217-A2DE-CDB2F08FE1F7
Purchase       = 300A6E01-5DB9-4217-A2DE-CDB2F08FE1F7
Relationships  = 300A6E01-5DB9-4217-A2DE-CDB2F08FE1F7
*/
/*********************************************************************************************************************/

 
//Extra User Fields when creating through the dashboard
 
 

//Authentication check
// add_filter('wp_authenticate_user', 'check_login', 10, 2);
// function check_login($user, $password) {
// 	$auth = get_user_meta($user->ID, 'verified', true);
// 	$verif = get_user_meta($user->ID,'imis_verified',true);
// 	$memberType = get_user_meta($user->ID,'aeh_member_type',true);
// 	$loginurl = site_url().'/membernetwork/member-activation/?memid='.$user->ID;
// 	if($verif != 1 && $memberType == 'hospital'){
// 		wp_redirect( $loginurl,302);
// 		exit;
// 	}
//     if(!$auth) {
//     	//wp_mail('pat@meshfresh.com','login attempt','you tried to login, Admin');
//     	add_filter( 'wpmem_login_failed', 'my_login_failed_msg' );
//     	return null;
//     }
//     return $user;
// }
// function my_login_failed_msg( $str ){
// 	$str = "Your account has not been verified";
// 	return $str;
// }


//Check if current page exists in nav
function page_in_menu( $menu = null, $object_id = null ) {
    $menu_object = wp_get_nav_menu_items( esc_attr( $menu ) );
    if( ! $menu_object )
        return false;
    $menu_items = wp_list_pluck( $menu_object, 'object_id' );
    if( !$object_id ) {
        global $post;
        $object_id = get_queried_object_id();
    }
    return in_array( (int) $object_id, $menu_items );
}

//Stylesheet
function admin_styles() {
    wp_register_style( 'admin_stylesheet', get_template_directory_uri().'/css/admin.css' );
    wp_enqueue_style( 'admin_stylesheet' );
}
add_action( 'admin_enqueue_scripts', 'admin_styles' );

//At a Glance
add_action('dashboard_glance_items', 'add_custom_post_counts');
function add_custom_post_counts() {
   $post_types = array('policy','quality','institute','webinar','story','group','discussion','general'); // array of custom post types to add to 'At A Glance' widget
   foreach ($post_types as $pt) :
      $pt_info = get_post_type_object($pt); // get a specific CPT's details
      $num_posts = wp_count_posts($pt); // retrieve number of posts associated with this CPT
      $num = number_format_i18n($num_posts->publish); // number of published posts for this CPT
      $text = _n( $pt_info->labels->singular_name, $pt_info->labels->name, intval($num_posts->publish) ); // singular/plural text label for CPT
      echo '<li class="page-count '.$pt_info->name.'-count"><a href="edit.php?post_type='.$pt.'">'.$num.' '.$text.'</li>';
   endforeach;
}

//Constant Contact Sidebar Widget
register_sidebar(array(
  'name' => __( 'Constant Contact' ),
  'id' => 'email-reg',
  'description' => __( 'Used for Constant Contact widget only' ),
  'before_title' => '',
  'after_title' => ''
));


//RSS Feeds
add_action('init','newslineRSS');
function newslineRSS(){
	add_feed('newsline','newslineRSSFunc');
}
function newslineRSSFunc(){
	get_template_part('partial/feed','newsline');
}




//Private post - adds lock icon next to title if post is private
function private_lock($title){
	$pT = get_post_type();
	if(get_post_status() == 'private'){
		// Might aswell make use of this function to escape attributes
		$title = attribute_escape($title);
		// What to find in the title
		$findthese = array(
			'#Protected:#', // # is just the delimeter
			'#Private:#'
		);
		// What to replace it with
		$replacewith = array(
			'<span style="border:none;" class="lock-icon"><img src="http://essentialhospitals.org/wp-content/themes/EssentialHospitals/images/lock'.$pT.'.png"></span>', // What to replace protected with
			'<span style="border:none;" class="lock-icon"><img src="http://essentialhospitals.org/wp-content/themes/EssentialHospitals/images/lock'.$pT.'.png"></span>' // What to replace private with
		);
		// Items replace by array key
		$title = preg_replace($findthese, $replacewith, $title);
		return $title;
	}else{
		return $title;
	}
}
add_filter('the_title','private_lock');




//Cycle function
function cycle($first_value, $values = '*') {
  static $count = array();
  $values = func_get_args();
  $name = 'default';
  $last_item = end($values);
  if( substr($last_item, 0, 1) === ':' ) {
    $name = substr($last_item, 1);
    array_pop($values);
  }
  if( !isset($count[$name]) )
    $count[$name] = 0;
  $index = $count[$name] % count($values);
  $count[$name]++;
  return $values[$index];
}

//Truncate and close function
function html_cut($text, $max_length)
{
    $tags   = array();
    $result = "";

    $is_open   = false;
    $grab_open = false;
    $is_close  = false;
    $in_double_quotes = false;
    $in_single_quotes = false;
    $tag = "";

    $i = 0;
    $stripped = 0;

    $stripped_text = strip_tags($text);

    while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
    {
        $symbol  = $text{$i};
        $result .= $symbol;

        switch ($symbol)
        {
           case '<':
                $is_open   = true;
                $grab_open = true;
                break;

           case '"':
               if ($in_double_quotes)
                   $in_double_quotes = false;
               else
                   $in_double_quotes = true;

            break;

            case "'":
              if ($in_single_quotes)
                  $in_single_quotes = false;
              else
                  $in_single_quotes = true;

            break;

            case '/':
                if ($is_open && !$in_double_quotes && !$in_single_quotes)
                {
                    $is_close  = true;
                    $is_open   = false;
                    $grab_open = false;
                }

                break;

            case ' ':
                if ($is_open)
                    $grab_open = false;
                else
                    $stripped++;

                break;

            case '>':
                if ($is_open)
                {
                    $is_open   = false;
                    $grab_open = false;
                    array_push($tags, $tag);
                    $tag = "";
                }
                else if ($is_close)
                {
                    $is_close = false;
                    array_pop($tags);
                    $tag = "";
                }

                break;

            default:
                if ($grab_open || $is_close)
                    $tag .= $symbol;

                if (!$is_open && !$is_close)
                    $stripped++;
        }

        $i++;
    }

    while ($tags)
        $result .= "</".array_pop($tags).">";

    return $result;
}

//Cat and Tags for Media Library
function register_mediaCat_tax() {
  $labels = array(
    'name'          => _x( 'Media Category', 'taxonomy general name' ),
    'singular_name'     => _x( 'Media Category', 'taxonomy singular name' ),
    'add_new'         => 'Add New Media Category',
    'add_new_item'      => __( 'Add New Media Category' ),
    'edit_item'       => __( 'Edit Media Category' ),
    'new_item'        => __( 'New Media Category' ),
    'view_item'       => __( 'View Media Category' ),
    'search_items'      => __( 'Search Media Category' ),
    'not_found'       => __( 'No Media Categories found' ),
    'not_found_in_trash'  => __( 'No Media Categories found in Trash' ),
  );
  $pages = array('attachment');
  $args = array(
    'labels'      => $labels,
    'singular_label'  => __('Media Category'),
    'public'      => false,
    'show_ui'       => true,
    'hierarchical'    => true,
    'show_tagcloud'   => false,
    'show_in_nav_menus' => false,
    'rewrite'       => array('slug' => 'mediaCat', 'with_front' => false ),
   );
  register_taxonomy('mediaCat', $pages, $args);
}
add_action('init', 'register_mediaCat_tax');
add_filter( 'manage_taxonomies_for_attachment_columns', 'activity_type_columns' );
function activity_type_columns( $taxonomies ) {
    $taxonomies[] = 'mediaCat';
    return $taxonomies;
}
function wptp_add_tags_to_attachments() {
    register_taxonomy_for_object_type( 'post_tag', 'attachment' );
}
add_action( 'init' , 'wptp_add_tags_to_attachments' );

//Hide Member Network Pages
add_action( 'pre_get_posts', 'hide_member_network' );
function hide_member_network( $query ) {
	global $post_type, $current_user;
	$userRole = get_current_user_role();
    if ( is_admin() && $query->is_main_query() && $post_type == 'page' && $userRole != 'Administrator' || $query->is_search){
        $query->set('post__not_in', array(271,301,297,295,299,308,330,278,392,244,257,287,248,260,274,280,310,276,290,547));
    }
}
//Sort based on whether author is editor



//Get Current User Role
function get_current_user_role() {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
}

//Show Future Posts
function show_future_posts($posts)
{
   global $wp_query, $wpdb;
   if(is_single() && $wp_query->post_count == 0)
   {
      $posts = $wpdb->get_results($wp_query->request);
   }
   return $posts;
}
add_filter('the_posts', 'show_future_posts');

//Get All Authors
function get_all_authors($authCount) {
	global $wpdb;
	$i = 0;
	foreach ( $wpdb->get_results("SELECT DISTINCT post_author, COUNT(ID) AS count FROM $wpdb->posts WHERE post_type = 'post' AND " . get_private_posts_cap_sql( 'post' ) . " GROUP BY post_author") as $row ){

		if($authCount && $i >= $authCount){ break; }

	    $author = get_userdata( $row->post_author );
	    $authors[$row->post_author]['name'] = $author->display_name;
	    $authors[$row->post_author]['post_count'] = $row->count;
	    $authors[$row->post_author]['ID'] = $author->ID;
	    $authors[$row->post_author]['desc'] = $author->user_description;
	    $authors[$row->post_author]['posts_url'] = get_author_posts_url( $author->ID, $author->user_nicename );
	    $authors[$row->post_author]['nice_name'] = $author->first_name.' '.$author->last_name;
	    $i++;
	}
	return $authors;
}

//Page Columns
function page_columns($columns)
{
	$columns = array(
		'cb'	 	=>  '<input type="checkbox" />',
		'title' 	=>  'Title',
		'author'	=>	'Author',
		'theme'		=>  'Theme',
		'date'		=>	'Date',
	);
	return $columns;
}

function custom_columns($column)
{
	global $post;
	if($column == 'theme')
	{
		echo get_field('theme', $post->ID);
	}
}
function column_register_sortable( $columns )
{
	$columns['theme'] = 'theme';
	return $columns;
}

add_filter("manage_edit-page_sortable_columns", "column_register_sortable" );
add_action("manage_pages_custom_column", "custom_columns");
add_filter("manage_edit-page_columns", "page_columns");

//Branding
function AEH_branding() {
    wp_enqueue_style('AEH-theme', get_template_directory_uri() . '/css/login.css');
}
add_action('login_enqueue_scripts', 'AEH_branding');

function AEH_editor_styles() {
    add_editor_style( 'css/editor.css' );
}
add_action( 'init', 'AEH_editor_styles' );

//Global Variables

add_action('init', 'register_my_menus');
add_action('init', 'loadup_scripts'); // Add Custom Scripts
function register_my_menus() {
	register_nav_menus(
		array(
			'primary-menu'   => __('Primary Menu'),
			'utility-menu'   => __('Utility Navigation'),
			'footer-menu'    => __('Footer Menu'),
			'action-nav'     => __('Action Menu'),
			'quality-nav'    => __('Quality Menu'),
			'institute-nav'  => __('Institute Menu'),
			'education-nav'  => __('Education Menu'),
			'member-network' => __('Member Network'),
			'ehu'            => __('Essential Hospitals U'),
			'general-nav'    => __('General Navigation - used on default page template'),
			'social-nav'	 => __('Social Navigation in footer')
		)
	);
}

//Nav Walker
class Menu_With_Description extends Walker_Nav_Menu {
	function start_el(&$output, $item, $depth, $args) {
		global $wp_query;
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
		$class_names = ' class="' . esc_attr( $class_names ) . '"';

		$output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';

		$attributes = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) .'"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) .'"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) .'"' : '';

		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '<br /><span class="sub">' . $item->description . '</span>';
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}


//Widget Sections
register_sidebar(array(
  'name' => __( 'Left Footer' ),
  'id' => 'left-footer',
  'description' => __( 'Footer - About' ),
  'before_title' => '<h2>',
  'after_title' => '</h2>',
  'before_widget' => '',
  'after_widget'  => '',
));
register_sidebar(array(
  'name' => __( 'Center Footer' ),
  'id' => 'center-footer',
  'description' => __( 'Footer - Center' ),
  'before_widget' => '',
  'after_widget'  => '',
));
register_sidebar(array(
  'name' => __( 'Right-Top Footer' ),
  'id' => 'righttop-footer',
  'description' => __( 'Footer - Right Top' ),
  'before_widget' => '',
  'after_widget'  => '',
));
register_sidebar(array(
  'name' => __( 'Right-Bottom Footer' ),
  'id' => 'rightbottom-footer',
  'description' => __( 'Footer - Right Bottom' ),
  'before_widget' => '',
  'after_widget'  => '',
));


register_sidebar(array(
  'name' => __( 'Footer - Contact Info' ),
  'id' => 'ffooter-contact',
  'description' => __( 'Contact info for America\'s Essential Hospitals'),
  'before_widget' => '',
  'after_widget'  => '',
));
register_sidebar(array(
  'name' => __( 'Footer - Department Contact' ),
  'id' => 'ffooter-departments',
  'description' => __( 'Contact info for specific departments of America\'s Essential Hospitals'),
  'before_widget' => '<div class="contact-section">',
  'after_widget'  => '</div>',
));
register_sidebar(array(
  'name' => __( 'Footer - Section Descriptions' ),
  'id' => 'ffooter-sections',
  'description' => __( 'Descriptions for the sections'),
  'before_widget' => '<div id="%1$s" class="desc-col %2$s">',
  'after_widget'  => '</div>',
  'before_title'  => '<h2 class="hidden">',
  'after_title'   => '</h2>'
));




if (!current_user_can('administrator')):
	show_admin_bar(false);
endif;


function loadup_scripts()
{
    if (!is_admin()) {
        wp_deregister_script('jquery'); // Deregister WordPress jQuery
        wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js' ); // Google CDN jQuery
        wp_enqueue_script('jquery'); // Enqueue it!


		// Script - registered
		wp_register_script('kinetic', get_template_directory_uri() . '/js/jquery.kinetic.min.js');
		wp_register_script('hammer', get_template_directory_uri() . '/js/jquery.hammer.min.js');
        wp_register_script('masonry', get_template_directory_uri() . '/js/masonry.pkgd.min.js');
		wp_register_script('jquerytools', get_template_directory_uri() . '/js/jquery.tools.min.js');
		wp_register_script('themetools', get_template_directory_uri() . '/js/script-pat.js');
		wp_register_script('membernetwork', get_template_directory_uri() . '/js/theme.script.js');

        // Script - queued
        wp_enqueue_script('kinetic');
        wp_enqueue_script('hammer');
        wp_enqueue_script('masonry');
        wp_enqueue_script('jquerytools');
        wp_enqueue_script('themetools');
        wp_enqueue_script('membernetwork');
    }
}

// Add Thumbnail Theme Support
add_theme_support('post-thumbnails');
add_image_size('large', 700, '', true); // Large Thumbnail
add_image_size('medium', 250, '', true); // Medium Thumbnail
add_image_size('small', 120, '', true); // Small Thumbnail
add_image_size('custom-size', 700, 200, true); // Custom Thumbnail Size call using the_post_thumbnail('custom-size');

add_image_size('story-home',300,300,true);
add_image_size('story-focus',754,754,true);
add_image_size('story-nav',362,362,true);

// Register Widget Area for the Sidebar
register_sidebar( array(
	'name' => __( 'Primary Widget Area', 'Sidebar' ),
	'id' => 'primary-widget-area',
	'description' => __( 'The primary widget area', 'Sidebar' ),
	'before_widget' => '<div class="box">',
	'after_widget' => '</div>',
	'before_title' => '<h1>',
	'after_title' => '</h1>',
) );

 


/*** CLEAN UP FUNCTIONS ----------------------------------------*/

  /* admin part cleanups */
  add_action('admin_menu','remove_dashboard_widgets'); // cleaning dashboard widgets
  add_action('admin_menu', 'delete_menu_items'); // deleting menu items from admin area
  add_action('admin_menu','customize_meta_boxes'); // remove some meta boxes from pages and posts edition page
  add_filter('manage_posts_columns', 'custom_post_columns'); // remove column entries from list of posts
  add_filter('manage_pages_columns', 'custom_pages_columns'); // remove column entries from list of page
  add_action('wp_before_admin_bar_render', 'wce_admin_bar_render' ); // clean up the admin bar
 

  /* selfish frshstart plugins code parts*/
  add_action('admin_notices','rynonuke_update_notification_nonadmins',1); // remove notification for enayone but admin
  add_action('pre_ping','rynonuke_self_pings'); // disable self-trackbacking


  /***************** Security + header clean-ups ************************/

  /** remove the wlmanifest (useless !!) */
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'rsd_link');
  remove_action( 'wp_head', 'index_rel_link' ); // index link
  remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
  remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
  remove_action('wp_head','start_post_rel_link');
  remove_action('wp_head','adjacent_posts_rel_link_wp_head');
  remove_action('wp_head', 'wp_generator'); // remove WP version from header
  remove_action('wp_head','wp_shortlink_wp_head');
  remove_filter( 'the_content', 'capital_P_dangit' ); // Get outta my Wordpress codez dangit!
  remove_filter( 'the_title', 'capital_P_dangit' );
  remove_filter( 'comment_text', 'capital_P_dangit' );


  // removes detailed login error information for security
  add_filter('login_errors',create_function('$a', "return null;"));

/*** cleaning up the dashboard- ----------------------------------------*/
function remove_dashboard_widgets(){

  //remove_meta_box('dashboard_right_now','dashboard','core'); // right now overview box
  //remove_meta_box('dashboard_incoming_links','dashboard','core'); // incoming links box
  remove_meta_box('dashboard_quick_press','dashboard','core'); // quick press box
  remove_meta_box('dashboard_plugins','dashboard','core'); // new plugins box
  remove_meta_box('dashboard_recent_drafts','dashboard','core'); // recent drafts box
  remove_meta_box('dashboard_primary','dashboard','core'); // wordpress development blog box
  remove_meta_box('dashboard_secondary','dashboard','core'); // other wordpress news box


}

 
 

/** remove column entries from posts **/
function custom_post_columns($defaults) {
  return $defaults;
}


/** remove column entries from pages **/
function custom_pages_columns($defaults) {
  return $defaults;
}
 

/****** removings items froms admin bars
use the last part of the ID after "wp-admin-bar-" to add some menu to the list  exemple for comments : id="wp-admin-bar-comments" so the id to use is "comments"  ***********/
function wce_admin_bar_render() {
global $wp_admin_bar;
  $wp_admin_bar->remove_menu('wp-logo');
  $wp_admin_bar->remove_menu('updates');
  $wp_admin_bar->remove_menu('comments');
  $wp_admin_bar->remove_menu('new-content');

}
/*-----------------------------------------------------------------------**/




/**  Other usefull cleanups from selfish fresh start plugin http://wordpress.org/extend/plugins/selfish-fresh-start/ --------------------*/

// remove update notifications for everybody except admin users
function rynonuke_update_notification_nonadmins() {
  if (!current_user_can('administrator'))
    remove_action('admin_notices','update_nag',3);
}

// disable self-trackbacking
function rynonuke_self_pings( &$links ) {
    foreach ( $links as $l => $link )
        if ( 0 === strpos( $link, home_url() ) )
            unset($links[$l]);
}

/** WordPress user profil cleanups  ------------------------------------*/

/* remove the color scheme options */
  function admin_color_scheme() {
   global $_wp_admin_css_colors;
   $_wp_admin_css_colors = 0;
}

// add_action('admin_head', 'admin_color_scheme');

// rem/add user profile fields
function rynonuke_contactmethods($contactmethods) {
  unset($contactmethods['yim']);
  unset($contactmethods['aim']);
  unset($contactmethods['jabber']);
  $contactmethods['rynonuke_twitter']='Twitter';
  $contactmethods['rynonuke_facebook']='Facebook';
  return $contactmethods;
}


/*----------------------------------------------------------------------- **/

 

/*-------------------FUNCTIONS--------------------- */
function wp_list_categories_for_post_type($post_type, $args = '') {
    $exclude = array();

    // Check ALL categories for posts of given post type
    foreach (get_categories() as $category) {
        $posts = get_posts(array('post_type' => $post_type, 'category' => $category->cat_ID));

        // If no posts found, ...
        if (empty($posts))
            // ...add category to exclude list
            $exclude[] = $category->cat_ID;
    }

    // Set up args
    if (! empty($exclude)) {
        $args .= ('' === $args) ? '' : '&';
        $args .= 'exclude='.implode(',', $exclude);
    }

    // List categories
    //wp_get_categories($args);
    return $args;
}
add_action('init', 'wd_hierarchical_tags_register');


/*---------------------------CUSTOM POST TYPES----------------------------- **/

$themeDIR = get_bloginfo('template_directory');
  //Stories CPT
  function register_stories_posttype() {
    $labels = array(
      'name'        => _x( 'Stories', 'post type general name' ),
      'singular_name'   => _x( 'Story', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Story' ),
      'edit_item'     => __( 'Edit Story' ),
      'new_item'      => __( 'New Story' ),
      'view_item'     => __( 'View Story' ),
      'search_items'    => __( 'Search Stories' ),
      'not_found'     => __( 'No Stories Found' ),
      'not_found_in_trash'=> __( 'No Stories in Trash' ),
      'parent_item_colon' => __( 'Story' ),
      'menu_name'     => __( 'Stories' )
    );

    $taxonomies = array();

    $supports = array('title');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Story'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => false,
      'capability_type'   => 'post',
      'has_archive'     => true,
      'hierarchical'    => false,
      'rewrite'       => array('slug' => 'stories', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 5,
      'taxonomies'    => $taxonomies
     );
     register_post_type('story',$post_type_args);
  }
  add_action('init', 'register_stories_posttype');


  // registration code for general post type
  function register_general_posttype() {
    $labels = array(
      'name'        => _x( 'General', 'post type general name' ),
      'singular_name'   => _x( 'General', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new General' ),
      'edit_item'     => __( 'Edit General' ),
      'new_item'      => __( 'New General' ),
      'view_item'     => __( 'View General article' ),
      'search_items'    => __( 'Search General' ),
      'not_found'     => __( 'No General articles found' ),
      'not_found_in_trash'=> __( 'No General articles found' ),
      'parent_item_colon' => __( 'General' ),
      'menu_name'     => __( 'General' )
    );

    $taxonomies = array('series','category','post_tag');

    $supports = array('title','editor','thumbnail','excerpt','comments','revisions','author');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('General'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => false,
      'capability_type'   => 'post',
      'has_archive'     => true,
      'hierarchical'    => true,
      'rewrite'       => array('slug' => 'general', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 5,
      'taxonomies'    => $taxonomies
     );
     register_post_type('general',$post_type_args);
  }
  add_action('init', 'register_general_posttype');


  // registration code for webinars post type
  function register_webinar_posttype() {
    $labels = array(
      'name'        => _x( 'Webinars', 'post type general name' ),
      'singular_name'   => _x( 'Webinar', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new Webinar' ),
      'edit_item'     => __( 'Edit Webinar' ),
      'new_item'      => __( 'New Webinar' ),
      'view_item'     => __( 'View Webinar' ),
      'search_items'    => __( 'Search Webinars' ),
      'not_found'     => __( 'No Webinars found' ),
      'not_found_in_trash'=> __( 'No Webinars found' ),
      'parent_item_colon' => __( 'Webinar' ),
      'menu_name'     => __( 'Webinars' )
    );

    $taxonomies = array('post_tag', 'policytopics', 'educationtopics', 'qualitytopics', 'institutetopics','webinartopics');

    $supports = array('title','editor','thumbnail','excerpt','comments','revisions','author');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Webinar'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => false,
      'capability_type'   => 'post',
      'has_archive'     => true,
      'hierarchical'    => true,
      'rewrite'       => array('slug' => 'webinar', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 5,
      'menu_icon'     => get_bloginfo('template_directory').'/images/education-menu.png',
      'taxonomies'    => $taxonomies
     );
     register_post_type('webinar',$post_type_args);
  }
  add_action('init', 'register_webinar_posttype');

  // registration code for alerts post type
  function register_alerts_posttype() {
    $labels = array(
      'name'        => _x( 'Alerts', 'post type general name' ),
      'singular_name'   => _x( 'Alert', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new Alert' ),
      'edit_item'     => __( 'Edit Alert' ),
      'new_item'      => __( 'New Alert' ),
      'view_item'     => __( 'View Alert' ),
      'search_items'    => __( 'Search Alerts' ),
      'not_found'     => __( 'No Alerts found' ),
      'not_found_in_trash'=> __( 'No Alerts found' ),
      'parent_item_colon' => __( 'No Alerts found' ),
      'menu_name'     => __( 'Announcements' )
    );

    $taxonomies = array('post_tag', 'policytopics', 'educationtopics', 'qualitytopics', 'institutetopics','category');

    $supports = array('title','excerpt');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Alert'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => false,
      'capability_type'   => 'post',
      'has_archive'     => true,
      'hierarchical'    => true,
      'rewrite'       => array('slug' => 'alert', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 5,
      'taxonomies'    => $taxonomies
     );
     register_post_type('alert',$post_type_args);
  }
  add_action('init', 'register_alerts_posttype');

  // registration code for policy post type
  function register_policy_posttype() {
    $labels = array(
      'name'        => _x( 'Action', 'post type general name' ),
      'singular_name'   => _x( 'Action', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new Action article' ),
      'edit_item'     => __( 'Edit Action' ),
      'new_item'      => __( 'New Action article' ),
      'view_item'     => __( 'View Action article' ),
      'search_items'    => __( 'Search Action articles' ),
      'not_found'     => __( 'No Action articles found' ),
      'not_found_in_trash'=> __( 'No Action articles found' ),
      'parent_item_colon' => __( 'Action' ),
      'menu_name'     => __( 'Action' )
    );

    $taxonomies = array('post_tag', 'policytopics', 'educationtopics', 'qualitytopics', 'institutetopics');

    $supports = array('title','editor','author','thumbnail','excerpt','comments','revisions');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Policy'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => false,
      'capability_type'   => 'post',
      'has_archive'     => true,
      'hierarchical'    => true,
      'rewrite'       => array('slug' => 'policy', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 2,
      'menu_icon'     => get_bloginfo('template_directory').'/images/policy-menu.png',
      'taxonomies'    => $taxonomies
     );
     register_post_type('policy',$post_type_args);
  }
  add_action('init', 'register_policy_posttype');

  // registration code for quality post type
  function register_quality_posttype() {
    $labels = array(
      'name'        => _x( 'Quality', 'post type general name' ),
      'singular_name'   => _x( 'Quality', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new Quality article' ),
      'edit_item'     => __( 'Edit Quality' ),
      'new_item'      => __( 'New Quality article' ),
      'view_item'     => __( 'View Quality articles' ),
      'search_items'    => __( 'Search Quality articles' ),
      'not_found'     => __( 'No Quality articles found' ),
      'not_found_in_trash'=> __( 'No Quality articles found' ),
      'parent_item_colon' => __( 'Quality' ),
      'menu_name'     => __( 'Quality' )
    );

     $taxonomies = array('post_tag', 'policytopics', 'educationtopics', 'qualitytopics', 'institutetopics');

    $supports = array('title','editor','author','thumbnail','excerpt','comments','revisions');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Quality'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => true,
      'capability_type'   => 'post',
      'has_archive'     => false,
      'hierarchical'    => false,
      'rewrite'       => array('slug' => 'quality', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 3,
      'menu_icon'     => get_bloginfo('template_directory').'/images/quality-menu.png',
      'taxonomies'    => $taxonomies
     );
     register_post_type('quality',$post_type_args);
  }
  add_action('init', 'register_quality_posttype');

  // registration code for institute post type
  function register_institute_posttype() {
    $labels = array(
      'name'        => _x( 'Institute', 'post type general name' ),
      'singular_name'   => _x( 'Institute', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new Institute article' ),
      'edit_item'     => __( 'Edit Institute' ),
      'new_item'      => __( 'New Institute article' ),
      'view_item'     => __( 'View Institute article' ),
      'search_items'    => __( 'Search Institute articles' ),
      'not_found'     => __( 'No Institute articles found' ),
      'not_found_in_trash'=> __( 'No Institute articles found' ),
      'parent_item_colon' => __( 'Institute' ),
      'menu_name'     => __( 'Institute' )
    );

    $taxonomies = array('post_tag', 'policytopics', 'educationtopics', 'qualitytopics', 'institutetopics');

    $supports = array('title','editor','author','thumbnail','excerpt','comments','revisions');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Institute'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => true,
      'capability_type'   => 'post',
      'has_archive'     => false,
      'hierarchical'    => false,
      'rewrite'       => array('slug' => 'institute', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 4,
      'menu_icon'     => get_bloginfo('template_directory').'/images/institute-menu.png',
      'taxonomies'    => $taxonomies
     );
     register_post_type('institute',$post_type_args);
  }
  add_action('init', 'register_institute_posttype');

  // registration code for discussion post type
  function register_discussion_posttype() {
    $labels = array(
      'name'        => _x( 'Discussions', 'post type general name' ),
      'singular_name'   => _x( 'Discussion', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new Discussion' ),
      'edit_item'     => __( 'Edit Discussion' ),
      'new_item'      => __( 'New Discussion' ),
      'view_item'     => __( 'View Discussion' ),
      'search_items'    => __( 'Search Discussions' ),
      'not_found'     => __( 'No Discussions found' ),
      'not_found_in_trash'=> __( 'No Discussions found' ),
      'parent_item_colon' => __( 'Discussion' ),
      'menu_name'     => __( 'Discussions' )
    );

     $taxonomies = array('discussions','discussion_tags');

    $supports = array('title','editor','author','comments');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Discussion'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => true,
      'exclude_from_search'=> false,
      'capability_type'   => 'post',
      'has_archive'     => true,
      'rewrite'       => array('slug' => 'discussions', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 5,
      'taxonomies'    => $taxonomies
     );
     register_post_type('discussion',$post_type_args);
  }
  add_action('init', 'register_discussion_posttype');


  // registration code for discussion post type
  function register_group_posttype() {
    $labels = array(
      'name'        => _x( 'Groups', 'post type general name' ),
      'singular_name'   => _x( 'Group', 'post type singular name' ),
      'add_new'       => __( 'Add New' ),
      'add_new_item'    => __( 'Add new Group' ),
      'edit_item'     => __( 'Edit Group' ),
      'new_item'      => __( 'New Group' ),
      'view_item'     => __( 'View Group' ),
      'search_items'    => __( 'Search Groups' ),
      'not_found'     => __( 'No Groups found' ),
      'not_found_in_trash'=> __( 'No Group found' ),
      'parent_item_colon' => __( 'Group' ),
      'menu_name'     => __( 'Groups' )
    );

     $taxonomies = array('post_tag', 'groups');

    $supports = array('title','editor','author','comments','page-attributes','revisions');

    $post_type_args = array(
      'labels'      => $labels,
      'singular_label'  => __('Group'),
      'public'      => true,
      'show_ui'       => true,
      'publicly_queryable'=> true,
      'query_var'     => true,
      'exclude_from_search'=> false,
      'show_in_nav_menus' => true,
      'capability_type'   => 'post',
      'has_archive'     => true,
      'hierarchical'    => true,
      'rewrite'       => array('slug' => 'groups', 'with_front' => false ),
      'supports'      => $supports,
      'menu_position'   => 5,
      //'menu_icon'     => get_bloginfo('template_directory').'//images/quality-menu.png',
      'taxonomies'    => $taxonomies,
      'exclude_from_search' => true
     );
     register_post_type('group',$post_type_args);
  }
  add_action('init', 'register_group_posttype');

  //Menu Order
  function custom_menu_order($menu_ord) {
	    if (!$menu_ord) return true;

	    return array(
	        'index.php', 							// Dashboard
	        'edit.php?post_type=policy', 			// Policy
	        'edit.php?post_type=quality', 			// Quality
	        'edit.php?post_type=institute',			// Insitute
	        'edit.php?post_type=webinar',			// Webinars
	        'edit.php?post_type=events',			// Events
			'edit.php?post_type=presentation',		// Presentaitons
	        'edit.php', 							// Posts
	        'edit.php?post_type=page', 				// Pages
	        'separator1', 							// First separator
	        'edit.php?post_type=group',				// Groups
	        'edit.php?post_type=discussion',		// Discussions
	        'edit.php?post_type=story', 			// Stories
	        'edit.php?post_type=alert',				// Alerts
	        'edit.php?post_type=general',			// General
	        'edit-comments.php', 					// Comments
	        'upload.php', 							// Media
	        'separator2', 							// Second separator
	        'themes.php', 							// Appearance
	        'plugins.php', 							// Plugins
	        'users.php', 							// Users
	        'tools.php', 							// Tools
	        'options-general.php',					// Settings
	        'separator-last', 						// Last separator
	    );
	}
	add_filter('custom_menu_order', 'custom_menu_order'); // Activate custom_menu_order
	add_filter('menu_order', 'custom_menu_order');

  //Remove preview from posttype
  function posttype_admin_css() {
	    global $post_type;
	    $post_types = array('group','webinar');
	    if(in_array($post_type, $post_types))
	    echo '<style type="text/css">li#wp-admin-bar-view,#post-preview, #view-post-btn{display: none;}</style>';
	}
	//add_action( 'admin_head-post-new.php', 'posttype_admin_css' );
	//add_action( 'admin_head-post.php', 'posttype_admin_css' );


  //-------------CUSTOM TAXONOMIES----------------------------------------------------------------------------//

  	// registration code for series taxonomy
    function register_series_tax() {
      $labels = array(
        'name'          => _x( 'Stream', 'taxonomy general name' ),
        'singular_name'     => _x( 'Stream', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Stream', 'Stream'),
        'add_new_item'      => __( 'Add New Stream' ),
        'edit_item'       => __( 'Edit Stream' ),
        'new_item'        => __( 'New Stream' ),
        'view_item'       => __( 'View Stream' ),
        'search_items'      => __( 'Search Streams' ),
        'not_found'       => __( 'No Streams found' ),
        'not_found_in_trash'  => __( 'No Streams found in Trash' ),
      );

      $pages = array('policy','quality','externallinks','institute','general');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Stream'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => true,
        'rewrite'       => array('slug' => 'series', 'with_front' => false ),
       );
      register_taxonomy('series', $pages, $args);
    }
    add_action('init', 'register_series_tax');

    // registration code for educationtopics taxonomy
    function register_educationtopics_tax() {
      $labels = array(
        'name'          => _x( 'Education Topics', 'taxonomy general name' ),
        'singular_name'     => _x( 'Education Topic', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Education Topic', 'Education Topic'),
        'add_new_item'      => __( 'Add New Education Topic' ),
        'edit_item'       => __( 'Edit Education Topic' ),
        'new_item'        => __( 'New Education Topic' ),
        'view_item'       => __( 'View Education Topic' ),
        'search_items'      => __( 'Search Education Topics' ),
        'not_found'       => __( 'No Education Topic found' ),
        'not_found_in_trash'  => __( 'No Education Topic found in Trash' ),
      );

      $pages = array('policy','quality','institute','externallinks');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Education Topic'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => true,
        'rewrite'       => array('slug' => 'educationtopics', 'with_front' => false ),
       );
      register_taxonomy('educationtopics', $pages, $args);
    }
    add_action('init', 'register_educationtopics_tax');

    // registration code for educationtopics taxonomy
    function register_qualitytopics_tax() {
      $labels = array(
        'name'          => _x( 'Quality Topics', 'taxonomy general name' ),
        'singular_name'     => _x( 'Quality Topic', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Quality Topic', 'Quality Topic'),
        'add_new_item'      => __( 'Add New Quality Topic' ),
        'edit_item'       => __( 'Edit Quality Topic' ),
        'new_item'        => __( 'New Quality Topic' ),
        'view_item'       => __( 'View Quality Topic' ),
        'search_items'      => __( 'Search Quality Topics' ),
        'not_found'       => __( 'No Quality Topic found' ),
        'not_found_in_trash'  => __( 'No Quality Topic found in Trash' ),
      );

      $pages = array('policy','quality','institute','externallinks','post','webinar');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Quality Topic'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => true,
        'rewrite'       => array('slug' => 'qualitytopics', 'with_front' => false ),
       );
      register_taxonomy('qualitytopics', $pages, $args);
    }
    add_action('init', 'register_qualitytopics_tax');


    // registration code for educationtopics taxonomy
    function register_policytopics_tax() {
      $labels = array(
        'name'          => _x( 'Action Topics', 'taxonomy general name' ),
        'singular_name'     => _x( 'Action Topic', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Action Topic', 'Policy Topic'),
        'add_new_item'      => __( 'Add New Action Topic' ),
        'edit_item'       => __( 'Edit Action Topic' ),
        'new_item'        => __( 'New Action Topic' ),
        'view_item'       => __( 'View Action Topic' ),
        'search_items'      => __( 'Search Action Topics' ),
        'not_found'       => __( 'No Action Topic found' ),
        'not_found_in_trash'  => __( 'No Action Topic found in Trash' ),
      );

      $pages = array('policy','quality','institute','externallinks','post','webinar');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Action Topic'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => true,
        'rewrite'       => array('slug' => 'policytopics', 'with_front' => false ),
       );
      register_taxonomy('policytopics', $pages, $args);
    }
    add_action('init', 'register_policytopics_tax');


    // registration code for educationtopics taxonomy
    function register_institutetopics_tax() {
      $labels = array(
        'name'          => _x( 'Institute Topics', 'taxonomy general name' ),
        'singular_name'     => _x( 'Institute Topic', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Institute Topic', 'Institute Topic'),
        'add_new_item'      => __( 'Add New Institute Topic' ),
        'edit_item'       => __( 'Edit Institute Topic' ),
        'new_item'        => __( 'New Institute Topic' ),
        'view_item'       => __( 'View Institute Topic' ),
        'search_items'      => __( 'Search Institute Topics' ),
        'not_found'       => __( 'No Institute Topic found' ),
        'not_found_in_trash'  => __( 'No Institute Topic found in Trash' ),
      );

      $pages = array('policy','quality','institute','externallinks');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Institute Topic'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => true,
        'rewrite'       => array('slug' => 'institutetopics', 'with_front' => false ),
       );
      register_taxonomy('institutetopics', $pages, $args);
    }
    add_action('init', 'register_institutetopics_tax');

    // registration code for institute centers taxonomy
    function register_institutecenters_tax() {
      $labels = array(
        'name'          => _x( 'Institute Centers', 'taxonomy general name' ),
        'singular_name'     => _x( 'Institute Center', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Institute Center', 'Institute Center'),
        'add_new_item'      => __( 'Add New Institute Center' ),
        'edit_item'       => __( 'Edit Institute Center' ),
        'new_item'        => __( 'New Institute Center' ),
        'view_item'       => __( 'View Institute Center' ),
        'search_items'      => __( 'Search Institute Centers' ),
        'not_found'       => __( 'No Institute Center found' ),
        'not_found_in_trash'  => __( 'No Institute Center found in Trash' ),
      );

      $pages = array('institute','externallinks');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Institute Center'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => true,
        'has_archive' => true,
        'rewrite'       => array('slug' => 'center', 'with_front' => false ),
       );
      register_taxonomy('centers', $pages, $args);
    }
    add_action('init', 'register_institutecenters_tax');

    //Description Field for Centers
    function centers_tax_fields($tag){
	    // Check for existing taxonomy meta for the term you're editing
	    $t_id = $tag->term_id; // Get the ID of the term you're editing
	    $term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check
	?>

	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="presenter_id">About this Center</label>
		</th>
		<td>
			<textarea type="text" name="term_meta[about_center]" id="term_meta[about_center]" rows="5" style="width:95%;"><?php echo $term_meta['about_center'] ? $term_meta['about_center'] : ''; ?></textarea><br />
			<span class="description">About paragraph that will display on the archive page</span>
		</td>
	</tr>

	<?php
    }
    function centers_save_tax_fields($term_id){
	    if ( isset( $_POST['term_meta'] ) ) {
		        $t_id = $term_id;
		        $term_meta = get_option( "taxonomy_term_$t_id" );
		        $cat_keys = array_keys( $_POST['term_meta'] );
		            foreach ( $cat_keys as $key ){
		            if ( isset( $_POST['term_meta'][$key] ) ){
		                $term_meta[$key] = $_POST['term_meta'][$key];
		            }
		        }
		        //save the option array
		        update_option( "taxonomy_term_$t_id", $term_meta );
		    }
		}
    add_action( 'centers_edit_form_fields', 'centers_tax_fields', 10, 2 );
    add_action( 'edited_centers', 'centers_save_tax_fields', 10, 2 );

	// registration code for webinar topics taxonomy
    function register_webinartopics_tax() {
      $labels = array(
        'name'          => _x( 'Webinar Topics', 'taxonomy general name' ),
        'singular_name'     => _x( 'Webinar Topic', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Webinar Topic', 'Institute Center'),
        'add_new_item'      => __( 'Add New Webinar Topic' ),
        'edit_item'       => __( 'Edit Webinar Topic' ),
        'new_item'        => __( 'New Webinar Topic' ),
        'view_item'       => __( 'View Webinar Topic' ),
        'search_items'      => __( 'Search Webinar Topics' ),
        'not_found'       => __( 'No Webinar Topics found' ),
        'not_found_in_trash'  => __( 'No Webinar Topics found in Trash' ),
      );

      $pages = array('webinar');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Webinar Topic'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => true,
        'has_archive' => true,
        'rewrite'       => array('slug' => 'webinars', 'with_front' => false ),
       );
      register_taxonomy('webinartopics', $pages, $args);
    }
    add_action('init', 'register_webinartopics_tax');

    // registration code for discussion taxonomy
    function register_discussion_tax() {
      $labels = array(
        'name'          => _x( 'Discussions', 'taxonomy general name' ),
        'singular_name'     => _x( 'Discussion', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Discussion', 'Institute Center'),
        'add_new_item'      => __( 'Add New Discussion' ),
        'edit_item'       => __( 'Edit Discussion' ),
        'new_item'        => __( 'New Discussion' ),
        'view_item'       => __( 'View Discussions' ),
        'search_items'      => __( 'Search Discussions' ),
        'not_found'       => __( 'No Discussions found' ),
        'not_found_in_trash'  => __( 'No Discussions found in Trash' ),
      );

      $pages = array('discussion');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Discussion'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => false,
        'show_in_nav_menus' => false,
        'rewrite'       => array('slug' => 'discussions', 'with_front' => false ),
       );
      register_taxonomy('discussions', $pages, $args);
    }
    add_action('init', 'register_discussion_tax');

     // registration code for discussion tags taxonomy
    function register_discussionTags_tax() {
      $labels = array(
        'name'          => _x( 'Discussion Tags', 'taxonomy general name' ),
        'singular_name'     => _x( 'Discussion Tag', 'taxonomy singular name' ),
        'add_new'         => _x( 'Add New Discussion Tag', 'Institute Center'),
        'add_new_item'      => __( 'Add New Discussion Tag' ),
        'edit_item'       => __( 'Edit Discussion Tag' ),
        'new_item'        => __( 'New Discussion Tag' ),
        'view_item'       => __( 'View Discussion Tags' ),
        'search_items'      => __( 'Search Discussion Tags' ),
        'not_found'       => __( 'No Discussion Tags found' ),
        'not_found_in_trash'  => __( 'No Discussion Tags found in Trash' ),
      );

      $pages = array('discussion');

      $args = array(
        'labels'      => $labels,
        'singular_label'  => __('Discussion Tags'),
        'public'      => true,
        'show_ui'       => true,
        'hierarchical'    => true,
        'show_tagcloud'   => true,
        'show_in_nav_menus' => false,
        'has_archive' => true,
        'rewrite'       => array('slug' => 'discussion_tags', 'with_front' => false ),
       );
      register_taxonomy('discussion_tags', $pages, $args);
    }
    add_action('init', 'register_discussionTags_tax');



    function new_excerpt_more( $more ) {
        return ' ';
    }
    add_filter('excerpt_more', 'new_excerpt_more');

    add_filter('the_excerpt', 'my_excerpts');

    function my_excerpts($content = false) {
            global $post;
            $mycontent = $post->post_excerpt;

            $mycontent = $post->post_content;
            $mycontent = strip_shortcodes($mycontent);
            $mycontent = str_replace(']]>', ']]&gt;', $mycontent);
            $mycontent = strip_tags($mycontent);
            $excerpt_length = 30;
            $words = explode(' ', $mycontent, $excerpt_length + 1);
            if(count($words) > $excerpt_length) :
                array_pop($words);
                array_push($words, '...');
                $mycontent = implode(' ', $words);
            endif;
            $mycontent = '<p>' . $mycontent . '</p>';
    // Make sure to return the content
    return $mycontent;
	}

function mytheme_enqueue_comment_reply() {
    // on single blog post pages with comments open and threaded comments
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        // enqueue the javascript that performs in-link comment reply fanciness
        wp_enqueue_script( 'comment-reply' );
    }
}
// Hook into wp_enqueue_scripts
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_comment_reply' );

function wpa_cpt_tags( $query ) {
    if ( $query->is_tag() && $query->is_main_query() ) {
        $query->set( 'post_type', array( 'policy', 'quality', 'institute' ,'webinar','education') );
    }
}
add_action( 'pre_get_posts', 'wpa_cpt_tags' );



    ///////////////////////////////////////
	// Default Alternative Nav Function (When Already Logged in)
	///////////////////////////////////////
	function default_alt_nav() {
		echo '<ul id="alt-nav" class="alt-nav clearfix">';
		wp_list_pages('title_li=');
		echo '</ul>';
	}


    ///////////////////////////////////////
	// Content for the Member Profile page
	///////////////////////////////////////

add_filter( 'wpmem_member_links', 'my_member_links' );
function my_member_links( $links )
{
	// get the current_user object
	global $current_user;
	get_currentuserinfo();

	     // format the date they registered
	$regdate = strtotime( $current_user->user_registered );

	// and the user info
	$str = '<div id="theuser">
	<h3 id="userlogin"><span style="color: white">' . $current_user->user_login . '</span></h3>
	        <div id="useravatar">' . get_avatar( $current_user->ID, '82' ) . '</div>
	          <dl id="userinfo">
				  <dt>Member Since</dt>
				  <dd>' . date( 'M d, Y', $regdate ) . '</dd>
				  <dt>Website</dt>
				  <dd><a class="url" href="' . $current_user->user_url . '" rel="nofollow">' . $current_user->user_url . '</a></dd>
				  <dt>Location</dt>
				  <dd>'
					. get_user_meta( $current_user->ID, 'city', true )
					. ', '
					. get_user_meta( $current_user->ID, 'thestate', true )
					. '</dd>
	          </dl>
	        </div>
	        <hr />';

     // tag the original links on to the end
     $string = $str . $links;

     // send back our content
     return $string;
}

	///////////////////////////////////////
	// Register Sidebar A and B
	///////////////////////////////////////
	if ( function_exists('register_sidebar') ) {
		register_sidebar(array(
			'name' => 'Sidebar A',
			'id' => 'sidebara',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h4 class="widgettitle">',
			'after_title' => '</h4>',
		));
	}
	if ( function_exists('register_sidebar') ) {
		register_sidebar(array(
			'name' => 'Sidebar B',
			'id' => 'sidebarb',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h4 class="widgettitle">',
			'after_title' => '</h4>',
		));
	}

	/* get featured image url function */
	function mdw_featured_img_url( $mdw_featured_img_size ) {
		$mdw_image_id = get_post_thumbnail_id();
		$mdw_image_url = wp_get_attachment_image_src( $mdw_image_id, $mdw_featured_img_size );
		$mdw_image_url = $mdw_image_url[0];
		return $mdw_image_url;
	}

add_image_size('cat-thumb', 120, 90);

/***************************************************************************************************/
function aeh_member($user_id) {
	$user_info = get_userdata($user_id);
	$result = check_aeh_email($user_info->user_email);          // key 0 => member_type, 1 => domain, 2 => staff(Y/N), 3 => organization
	update_user_meta( $user_id, 'email_domain', $domain );	    // save the domain in a new user_meta value
	update_user_meta( $user_id, 'aeh_member_type', $result[0]); // initialize the member type in meta table
	update_user_meta( $user_id, 'aeh_staff', $result[2]);	    // initialize the staff type in meta table
	update_user_meta( $user_id, 'title', "");
	update_user_meta( $user_id, 'job_title', "");
	update_user_meta( $user_id, 'job_function', "");
	update_user_meta( $user_id, 'employer', "");
	update_user_meta( $user_id, 'tos', 'agree');
	delete_user_meta( $user_id, 'password'); 					// remove the plaintext password from the DB
}

add_action('user_register', 'aeh_member', 10, 2);
//add_action('bp_core_signup_user', 'aeh_member', 10, 2);

define ("CUSTOM_NEWS_URL", home_url("custom-news-feed/"));

function get_member_type(){
	$member = array();
	$current_user= wp_get_current_user();
	$member[0]   = $current_user->ID;
	$member[1]   = get_user_meta($member[0], 'aeh_member_type', TRUE);
	$member[2]   = get_user_meta($member[0], 'email_domain', TRUE);
	return $member;
}

function alertbox_check($userID){
	$return = ""; //presume no need for alerts at first
	if (get_user_meta($userID, 'custom_news_feed', TRUE) == ""){
		$return = "You have not set up your custom news feed yet.<br /><a href=" . CUSTOM_NEWS_URL . ">Click here to set up your custom news settings.</a>"; //user has no custom news feed so signal an alert
	}
	return $return;
}

function get_excerpt_by_id($post_id, $words){
	$the_post = get_post($post_id); //Gets post ID
	$the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
	$excerpt_length = $words; //Sets excerpt length by word count
	$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
	$words = explode(' ', $the_excerpt, $excerpt_length + 1);
	if(count($words) > $excerpt_length) :
		array_pop($words);
		array_push($words, '…');
		$the_excerpt = implode(' ', $words);
	endif;
	//$the_excerpt = '<p>' . $the_excerpt . '</p>';
	return $the_excerpt;
}



//Create new Group/Webinar cat on publication
function new_cat_group( $new_status, $old_status, $post ) {
    if ( $old_status != 'publish' && $new_status == 'publish' ) {
        $postType = $_POST['post_type'];
        if($postType == 'group' || $postType == 'webinar'){
	        $postID = $_POST['ID'];
			$parent_term = term_exists( $postType, 'discussions' ); // array is returned if taxonomy is given
			$parent_term_id = $parent_term['term_id']; // get numeric term id
			wp_insert_term(
			  $postType.'-'.$postID, // the term
			  'discussions', // the taxonomy
			  array(
			    'slug' => $postID,
			    'parent'=> $parent_term_id
			  )
			);
        }
    }
}
add_action( 'transition_post_status', 'new_cat_group', 10, 3 );



//When a Webinar is created add the author as a member

//When a Webinar is updated add the mod to the group unless that mod already exists in which case do nothing.





// ----- Member Network -----

class myUsers {
	static function init() {
		// Change the user's display name after insertion
		add_action( 'user_register', array( __CLASS__, 'change_display_name' ) );
	}

	static function change_display_name( $user_id ) {
		$info = get_userdata( $user_id );

		$args = array(
			'ID' => $user_id,
			'display_name' => $info->first_name . ' ' . $info->last_name
		);

		wp_update_user( $args ) ;
	}
}
myUsers::init();


add_filter( 'wpmem_login_redirect', 'my_login_redirect' );
function my_login_redirect(){
	// return the url that the login should redirect to
	$location = $_SERVER['HTTP_REFERER'];
	if($location == 'http://essentialhospitals.org/membernetwork/registration/'){
		$location = 'http://essentialhospitals.org/membernetwork/dashboard/';
	}
	print_r($location);
	if(strpos($location,'http://essentialhospitals.org/membernetwork/member-activation/') !== false) {
		$location = 'http://essentialhospitals.org/membernetwork/dashboard/';
	}
    wp_safe_redirect($location);
    exit();
}




function remove_from_db( $user_id ) {
	global $wpdb;
	$wpdb->query( "DELETE FROM `wp_aeh_connections` WHERE `user_ID` = $user_id OR `friend_ID` = $user_id" );
}
add_action( 'delete_user', 'remove_from_db' );


//Change Private to Association Members Only
function custom_admin_js() {
    $url = get_option('siteurl');
    $url = get_bloginfo('template_directory') . '/js/wp-admin.js';
    echo '"<script type="text/javascript" src="'. $url . '"></script>"';
}
add_action('admin_footer', 'custom_admin_js');


//Staff Settings Admin Panel & Email Verification
add_filter('admin_init', 'my_general_settings_register_fields');

function my_general_settings_register_fields()
{
    register_setting('general', 'email_ver', 'esc_attr');
    add_settings_field('email_ver', '<label for="email_ver">'.__('Verified Email Addresses' , 'my_field' ).'</label>' , 'my_general_settings_fields_html', 'general');
}

function my_general_settings_fields_html()
{
    $value = get_option( 'email_ver', '' );

}

add_action('user_register', 'email_verification');
function email_verification($user_id) {
    if ( isset( $_POST['user_email'] ) ){
        $uEmail = $_POST['user_email'];
		if(strpos($uEmail,'essentialhospitals.org') !== false){
			update_user_meta($user_id,'staff_mem','Y');
		}else{
			update_user_meta($user_id,'staff_mem','N');
		}
    }
}


//Comments Walker
function commentWalker($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;
		extract($args, EXTR_SKIP);
		$ispingb = false;
		if($comment->comment_type == 'pingback') {
			$ispingb = true;
		}
		 
 

		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
?>
		<li <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
			<?php if ( 'div' != $args['style'] ) : ?>
				<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
			<?php endif; ?>


			<div class="comment-author vcard">
				<?php if(!$ispingb){ ?>
				<?php if ($args['avatar_size'] != 0) echo '<a href="'.get_permalink(276).'?member='.$comment->user_id.'">'.get_avatar( $comment, $args['avatar_size'] ).'</a>'; 
						else echo '<img src="http://essentialhospitals.org/wp-content/uploads/2015/04/ad516503a11cd5ca435acc9bb6523536.png" class="avatar avatar-96 photo" height="96" width="96">';
				?>

				<?php  echo '<cite class="fn"><a href="'.get_permalink(276).'?member='.$comment->user_id.'">' .get_comment_author() .'</a></cite> '; ?>
				<?php }?>
			</div>

			<?php if ($comment->comment_approved == '0') : ?>
				<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
				<br />
			<?php endif; ?>

			<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
				<?php printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'  ','' ); ?>
			</div>

			<div class="comment-contentbox">
				<?php if($ispingb){ ?><strong><br><?php echo $comment->comment_author;?> </strong><?php }?>
				<?php comment_text() ?> <?php if($ispingb){ ?> <a target="_blank" href="<?php echo $comment->comment_author_url ;?>">Read Article </a> <?php }?>
			</div>
			<div class="reply">
				<?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
			</div>
			<div class="cancelreply">
				<?php cancel_comment_reply_link('Cancel Reply'); ?>
			</div>

		</li>
<?php
        }

//Description Field for Centers
    function series_tax_fields($tag){
	    // Check for existing taxonomy meta for the term you're editing
	    $t_id = $tag->term_id; // Get the ID of the term you're editing
	    $term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check
	?>

	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="presenter_id">Section</label>
		</th>
		<td>
			<select type="text" name="term_meta[section]" id="term_meta[section]">
				<option <?php if($term_meta['section'] == 'policy'){ echo 'selected="checked"'; } ?> value="policy">Action</option>
				<option <?php if($term_meta['section'] == 'quality'){ echo 'selected="checked"'; } ?>value="quality">Quality</option>
				<option <?php if($term_meta['section'] == 'education'){ echo 'selected="checked"'; } ?>value="education">Education</option>
				<option <?php if($term_meta['section'] == 'institute'){ echo 'selected="checked"'; } ?>value="institute">Institute</option>
        <option <?php if($term_meta['section'] == 'utility'){ echo 'selected="checked"'; } ?>value="utility">General</option>
			</select><br />
			<span class="description">Which section is this Stream/Series a part of?</span>
		</td>
	</tr>

	<?php
    }
    function series_save_tax_fields($term_id){
	    if ( isset( $_POST['term_meta'] ) ) {
		        $t_id = $term_id;
		        $term_meta = get_option( "taxonomy_term_$t_id" );
		        $cat_keys = array_keys( $_POST['term_meta'] );
		            foreach ( $cat_keys as $key ){
		            if ( isset( $_POST['term_meta'][$key] ) ){
		                $term_meta[$key] = $_POST['term_meta'][$key];
		            }
		        }
		        //save the option array
		        update_option( "taxonomy_term_$t_id", $term_meta );
		    }
		}
    add_action( 'series_edit_form_fields', 'series_tax_fields', 10, 2 );
    add_action( 'edited_series', 'series_save_tax_fields', 10, 2 );

//In taxonomy?
function tax_check($tax, $term, $_post = NULL) {
	// if neither tax nor term are specified, return false
	if ( !$tax || !$term ) { return FALSE; }
	// if post parameter is given, get it, otherwise use $GLOBALS to get post
	if ( $_post ) {
	$_post = get_post( $_post );
	} else {
	$_post =& $GLOBALS['post'];
	}
	// if no post return false
	if ( !$_post ) { return FALSE; }
	// check whether post matches term belongin to tax
	$return = is_object_in_term( $_post->ID, $tax, $term );
	// if error returned, then return false
	if ( is_wp_error( $return ) ) { return FALSE; }
	return $return;
}







/* ************************************************************************************************************************************************/
// update profile hook to send updated data to iMIS DB
 
function prof_update_hook( $fields ){}
 
function admin_profile_update( $user_id ) {}
 
function update_imis($user_id){}
 
/******************************************************** UPDATE IMIS FROM WP USERMETA ***********************************************************/

function update_imis_from_wp($imis_id, $userdata, $user){}


/******************************************************** UPDATE IMIS ON CREATE NEW IMIS ACCOUNT ****************************************************************/


function update_create_imis_from_wp($imis_id, $userdata, $user){}

/******************************************************** CREATE NEW IMIS ACCOUNT ****************************************************************/

function new_imis($first,$last,$email,$password){}

/************************************************** CHECK TO GET STATUS OF EMAIL ADDRESS *********************************************************/

function check_aeh_email($email){  
 
}
/*************************************************************************************************************************************************/



/******************************************************** CRON Jobs & Misc Functions *************************************************************/
//


//add_action('aeh_import_imis', 'import_imis');

function import_imis() { 	 
 

}

/******************************************* CRON TO TAKE iMIS VALUES AND UPDATE WP USERS *************************************************/

add_action('aeh_update_wp_users', 'update_wp_users');
function update_wp_users() { 
 
}




/**************************************** CHECK IF IMIS USER EXISTS FROM THEIR EMAIL ADDRESS **********************************************/
// if they do then return an array of key => value pairs. If an error return false.
function does_imis_user_exist($email){

	$params = array(
		'securityPassword'=> SP_SECURITY_PWD,
		'name'            => SP_DOES_USER_EXIST,
		'parameters'      => "@email='$email'"
	);

	$result = post_request(IMIS_POST_URL, $params);
	//print_r($result);
	if ($result['status'] == 'ok'){ 									//if no status then an error occurred.
		$xml = simplexml_load_string($result['content']);
		if ($xml === false)return false;
		$xml = dom_import_simplexml($xml);
		if ($xml === false)return false;
		$nodelist = $xml->getElementsByTagName('Table');
		if ($nodelist->length==0)return false;
		$ID          = $nodelist->item(0)->getElementsByTagName('ID');
		$prefix      = $nodelist->item(0)->getElementsByTagName('PREFIX');
		$firstname   = $nodelist->item(0)->getElementsByTagName('FIRST_NAME');
		$middlename  = $nodelist->item(0)->getElementsByTagName('MIDDLE_NAME');
		$lastname    = $nodelist->item(0)->getElementsByTagName('LAST_NAME');
		$designation = $nodelist->item(0)->getElementsByTagName('DESIGNATION');
		//$informal    = $nodelist->item(0)->getElementsByTagName('INFORMAL');
		$workphone   = $nodelist->item(0)->getElementsByTagName('WORK_PHONE');
		$fax         = $nodelist->item(0)->getElementsByTagName('FAX');
		$suffix      = $nodelist->item(0)->getElementsByTagName('SUFFIX');
		$addressnum	 = $nodelist->item(0)->getElementsByTagName('ADDRESS_NUM');
		$address1	 = $nodelist->item(0)->getElementsByTagName('ADDRESS_1');
		$city        = $nodelist->item(0)->getElementsByTagName('CITY');
		$state       = $nodelist->item(0)->getElementsByTagName('STATE_PROVINCE');
		$zip         = $nodelist->item(0)->getElementsByTagName('ZIP');
		$country     = $nodelist->item(0)->getElementsByTagName('COUNTRY');
		$email       = $nodelist->item(0)->getElementsByTagName('EMAIL');
		$password    = $nodelist->item(0)->getElementsByTagName('WEB_PASSWORD');
		$mem_type    = $nodelist->item(0)->getElementsByTagName('MEMBER_TYPE');
		$company     = $nodelist->item(0)->getElementsByTagName('COMPANY');
		$co_id       = $nodelist->item(0)->getElementsByTagName('CO_ID');
		$title       = $nodelist->item(0)->getElementsByTagName('TITLE');
		$website     = $nodelist->item(0)->getElementsByTagName('WEBSITE');
		$mobile      = $nodelist->item(0)->getElementsByTagName('MOBILE_PHONE');
		$asst_name   = $nodelist->item(0)->getElementsByTagName('ASSISTANT_NAME');
		$asst_phone  = $nodelist->item(0)->getElementsByTagName('ASSISTANT_PHONE');
		$asst_email  = $nodelist->item(0)->getElementsByTagName('ASSISTANT_EMAIL');
		$webinterest = $nodelist->item(0)->getElementsByTagName('WEB_INTERESTS');


		unset ($result);$result = array();
		$result[ID] = $ID->item(0)->nodeValue;
		$result[prefix] = $prefix->item(0)->nodeValue;
		$result[firstname] = $firstname->item(0)->nodeValue;
		$result[middlename] = $middlename->item(0)->nodeValue;
		$result[lastname] = $lastname->item(0)->nodeValue;
		$result[suffix] = $suffix->item(0)->nodeValue;
		$result[designation] = $designation->item(0)->nodeValue;
		//$result[informal] = $informal->item(0)->nodeValue;
		$result[workphone] = $workphone->item(0)->nodeValue;
		$result[fax] = $fax->item(0)->nodeValue;
		$result[addressnum] = $addressnum->item(0)->nodeValue;
		$result[address1] = $address1->item(0)->nodeValue;
		$result[city] = $city->item(0)->nodeValue;
		$result[state] = $state->item(0)->nodeValue;
		$result[zip] = $zip->item(0)->nodeValue;
		$result[country] = $country->item(0)->nodeValue;
		$result[email] = $email->item(0)->nodeValue;
		$result[password] = $password->item(0)->nodeValue;
		$result[mem_type] = $mem_type->item(0)->nodeValue;
		$result[company] = $company->item(0)->nodeValue;
		$result[companyID] = $co_id->item(0)->nodeValue;
		$result[title] = $title->item(0)->nodeValue;
		$result[website] = $website->item(0)->nodeValue;
		$result[mobile] = $mobile->item(0)->nodeValue;
		$result[asst_name] = $asst_name->item(0)->nodeValue;
		$result[asst_phone] = $asst_phone->item(0)->nodeValue;
		$result[asst_email] = $asst_email->item(0)->nodeValue;
		$result[webinterest] = $webinterest->item(0)->nodeValue;

		$the_status = $result['status'];
		global $wpdb;
 
		return $result;
	}else{
		return false;
	}
}


/******************************************* GET IMIS USER BY IMIS ID **********************************************/

function get_imis_user($imisuser){
	$addressnum = false;
	$params = array(
		'securityPassword'=> SP_SECURITY_PWD,
		'name'            => SP_GET_IMIS_USER,
		'parameters'      => "@user=$imisuser"
	);

	$result = post_request(IMIS_POST_URL, $params);
	//print_r($result);
	if ($result['status'] == 'ok'){ 									//if no status then an error occurred.
		$xml = simplexml_load_string($result['content']);
		if ($xml === false)return false;
		
		$xml = dom_import_simplexml($xml);
		if ($xml === false)return false;
		$nodelist = $xml->getElementsByTagName('Table');
		if ($nodelist->length==0)return false;
		$ID          = $nodelist->item(0)->getElementsByTagName('ID');
		$prefix      = $nodelist->item(0)->getElementsByTagName('PREFIX');
		$firstname   = $nodelist->item(0)->getElementsByTagName('FIRST_NAME');
		$middlename  = $nodelist->item(0)->getElementsByTagName('MIDDLE_NAME');
		$lastname    = $nodelist->item(0)->getElementsByTagName('LAST_NAME');
		$designation = $nodelist->item(0)->getElementsByTagName('DESIGNATION');
		//$informal    = $nodelist->item(0)->getElementsByTagName('INFORMAL');
		$workphone   = $nodelist->item(0)->getElementsByTagName('WORK_PHONE');
		$fax         = $nodelist->item(0)->getElementsByTagName('FAX');
		$suffix      = $nodelist->item(0)->getElementsByTagName('SUFFIX');
		$addressnum	 = $nodelist->item(0)->getElementsByTagName('ADDRESS_NUM');
		$address1	 = $nodelist->item(0)->getElementsByTagName('ADDRESS_1');
		$city        = $nodelist->item(0)->getElementsByTagName('CITY');
		$state       = $nodelist->item(0)->getElementsByTagName('STATE_PROVINCE');
		$zip         = $nodelist->item(0)->getElementsByTagName('ZIP');
		$country     = $nodelist->item(0)->getElementsByTagName('COUNTRY');
		$email       = $nodelist->item(0)->getElementsByTagName('EMAIL');
		$password    = $nodelist->item(0)->getElementsByTagName('WEB_PASSWORD');
		$mem_type    = $nodelist->item(0)->getElementsByTagName('MEMBER_TYPE');
		$company     = $nodelist->item(0)->getElementsByTagName('COMPANY');
		$co_id       = $nodelist->item(0)->getElementsByTagName('CO_ID');
		$title       = $nodelist->item(0)->getElementsByTagName('TITLE');
		$website     = $nodelist->item(0)->getElementsByTagName('WEBSITE');
		$mobile      = $nodelist->item(0)->getElementsByTagName('MOBILE_PHONE');
		$asst_name   = $nodelist->item(0)->getElementsByTagName('ASSISTANT_NAME');
		$asst_phone  = $nodelist->item(0)->getElementsByTagName('ASSISTANT_PHONE');
		$asst_email  = $nodelist->item(0)->getElementsByTagName('ASSISTANT_EMAIL');
		$webinterest = $nodelist->item(0)->getElementsByTagName('WEB_INTERESTS');


		unset ($result);$result = array();
		$result[ID] = $ID->item(0)->nodeValue;
		$result[prefix] = $prefix->item(0)->nodeValue;
		$result[firstname] = $firstname->item(0)->nodeValue;
		$result[middlename] = $middlename->item(0)->nodeValue;
		$result[lastname] = $lastname->item(0)->nodeValue;
		$result[suffix] = $suffix->item(0)->nodeValue;
		$result[designation] = $designation->item(0)->nodeValue;
		//$result[informal] = $informal->item(0)->nodeValue;
		$result[workphone] = $workphone->item(0)->nodeValue;
		$result[fax] = $fax->item(0)->nodeValue;
		$result[addressnum] = $addressnum->item(0)->nodeValue;
		$result[address1] = $address1->item(0)->nodeValue;
		$result[city] = $city->item(0)->nodeValue;
		$result[state] = $state->item(0)->nodeValue;
		$result[zip] = $zip->item(0)->nodeValue;
		$result[country] = $country->item(0)->nodeValue;
		$result[email] = $email->item(0)->nodeValue;
		$result[password] = $password->item(0)->nodeValue;
		$result[mem_type] = $mem_type->item(0)->nodeValue;
		$result[company] = $company->item(0)->nodeValue;
		$result[companyID] = $co_id->item(0)->nodeValue;
		$result[title] = $title->item(0)->nodeValue;
		$result[website] = $website->item(0)->nodeValue;
		$result[mobile] = $mobile->item(0)->nodeValue;
		$result[asst_name] = $asst_name->item(0)->nodeValue;
		$result[asst_phone] = $asst_phone->item(0)->nodeValue;
		$result[asst_email] = $asst_email->item(0)->nodeValue;
		$result[webinterest] = $webinterest->item(0)->nodeValue;

		$the_status = $result['status'];
		global $wpdb;

		$wpdb->query("INSERT INTO `test` (`name`, `value`) VALUES ('get mis user :$the_status', '$result[ID]  - $result[firstname]')");




		return $result;
	}else{
		return false;
	}
}


 
/********************************************** HTTP POST REQUEST FUNCTION **************************************************/
function post_request($url, $data, $referer='') {

    // Convert the data array into URL Parameters like a=b&foo=bar etc.
    $data = http_build_query($data);

    // parse the given URL
    $url = parse_url($url);

    if ($url['scheme'] != 'http') {
        die('Error: Only HTTP request are supported !');
    }

    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];

    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($host, 80, $errno, $errstr, 30);

    if ($fp){

        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");

        if ($referer != '')
            fputs($fp, "Referer: $referer\r\n");

        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ". strlen($data) ."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $result = '';
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 4096);
        }
    }
    else {
        return array(
            'status' => 'err',
            'error' => "$errstr ($errno)"
        );
    }

    // close the socket connection:
    fclose($fp);

    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);

    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';

    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );
}

/**
 * XML2Array: A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * See Array2XML: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (07 Dec 2011)
 * Version: 0.2 (04 Mar 2012)
 * 			Fixed typo 'DomDocument' to 'DOMDocument'
 *
 * Usage:
 *       $array = XML2Array::createArray($xml);
 */

class XML2Array {

    private static $xml = null;
	private static $encoding = 'UTF-8';

    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $format_output
     */
    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
        self::$xml = new DOMDocument($version, $encoding);
        self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
    }

    /**
     * Convert an XML to Array
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DOMDocument
     */
    public static function &createArray($input_xml) {
        $xml = self::getXMLRoot();
		if(is_string($input_xml)) {
			$parsed = $xml->loadXML($input_xml);
			if(!$parsed) {
				throw new Exception('[XML2Array] Error parsing the XML string.');
			}
		} else {
			if(get_class($input_xml) != 'DOMDocument') {
				throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
			}
			$xml = self::$xml = $input_xml;
		}
		$array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $array;
    }

    /**
     * Convert an Array to XML
     * @param mixed $node - XML as a string or as an object of DOMDocument
     * @return mixed
     */
    private static function &convert($node) {
		$output = array();

		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;

			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;

			case XML_ELEMENT_NODE:

				// for each child node, call the covert function recursively
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = self::convert($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;

						// assume more nodes of same kind are coming
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					} else {
						//check if it is not an empty text node
						if($v !== '') {
							$output = $v;
						}
					}
				}

				if(is_array($output)) {
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1) {
							$output[$t] = $v[0];
						}
					}
					if(empty($output)) {
						//for empty nodes
						$output = '';
					}
				}

				// loop through the attributes and collect them
				if($node->attributes->length) {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if(!is_array($output)) {
						$output = array('@value' => $output);
					}
					$output['@attributes'] = $a;
				}
				break;
		}
		return $output;
    }

    /*
     * Get the root XML node, if there isn't one, create it.
     */
    private static function getXMLRoot(){
        if(empty(self::$xml)) {
            self::init();
        }
        return self::$xml;
    }
}

/*************************************************************************************************************************************************/

//Show Private posts on normal queries
function show_private($query) {
  if (!is_admin() && $_GET['preview'] != 'true') {
     $query->set('post_status', array('publish','private'));
  }
}
add_action('pre_get_posts','show_private');

//Action articles refuse to sort by date
add_action( 'pre_get_posts', 'mycpt_order' );
/**
 * Change order of custom post type to alphabetical ascending
 */
function mycpt_order( $query ) {
    // check if we're in admin, if not exit
    if ( ! is_admin() ) {
        return;
    }
    $post_type = $query->get('post_type');
    if ( $post_type == 'policy' ) {
        /* Post Column: e.g. title */
        if ( $query->get( 'orderby' ) == 'menu_order title' && $query->get( 'order' ) == 'asc') {
            $query->set( 'orderby', 'date' );
             $query->set( 'order', 'desc' );
        }
    }
}


//wpmembers stuff
add_filter( 'wpmem_login_form', 'remove_wpmem_txt' );
add_filter( 'wpmem_register_form', 'remove_wpmem_txt' );

function remove_wpmem_txt( $form ) {
	$old = array( '[wpmem_txt]', '<p>', '[/wpmem_txt]');
	$new = array( "" );
	return str_replace( $old, $new, $form );
}


function possibly_redirect(){
  global $pagenow;
  if( 'wp-login.php' == $pagenow ) {
    if ( isset( $_POST['wp-submit'] ) ||   // in case of LOGIN
      ( isset($_GET['action']) && $_GET['action']=='logout') ||   // in case of LOGOUT
      ( isset($_GET['checkemail']) && $_GET['checkemail']=='confirm') ||   // in case of LOST PASSWORD
      ( isset($_GET['checkemail']) && $_GET['checkemail']=='registered') ) return;    // in case of REGISTER
    else wp_redirect( home_url().'/membernetwork/registration/' ); // or wp_redirect(home_url('/login'));
    exit();
  }
}
add_action('init','possibly_redirect');


apply_filters('coauthors_show_create_profile_user_link',true);

add_filter( 'auth_cookie_expiration', 'keep_me_logged_in_for_1_year' );
function keep_me_logged_in_for_1_year( $expirein ) {
    return 31556926; // 1 year in seconds
}


add_filter('logout_url', 'logout_home', 10, 2);
function logout_home($logouturl, $redir){
	$redir = get_option('siteurl');
	return $logouturl . '&redirect_to=' . urlencode($redir);
}

function hwl_home_pagesize($query){
    if(is_admin() || !$query->is_main_query())
        return;

    if(is_tax() || is_tag() || is_category() || is_archive()){
	    $query->set('posts_per_page',999);
    }
}
add_action('pre_get_posts','hwl_home_pagesize',1);


add_filter('wp_mail_from','from_mail');
    function from_mail($content_type) {
        return 'cms@essentialhospitals.org';
    }

function update_presentation_section( $post_id, $post ) {
	$pt = $post->post_type; 
 	 
	if ($pt == 'presentation'){
		
		$event = get_post_meta($post_id,'event',true);
 
		$section = get_post_meta($event,'section',true);
		update_post_meta($post_id, 'section', $section); 
	} 
	else{
		return;
	}

}
add_action( 'save_post', 'update_presentation_section', 10, 2 );
 


//WYSISYG FOR COMMENTS
 
add_filter( 'comment_form_field_comment', 'comment_editor' );
 
function comment_editor() {
  global $post;
 
  ob_start();
 
  wp_editor( '', 'comment', array(
    'textarea_rows' => 15,
    'teeny' => true,
    'quicktags' => false,
    'media_buttons' => false
  ) );
 
  $editor = ob_get_contents();
 
  ob_end_clean();
 
  //make sure comment media is attached to parent post
  $editor = str_replace( 'post_id=0', 'post_id='.get_the_ID(), $editor );
 
  return $editor;
}
 
// wp_editor doesn't work when clicking reply. Here is the fix.
add_action( 'wp_enqueue_scripts', '__THEME_PREFIX__scripts' );
function __THEME_PREFIX__scripts() {
    wp_enqueue_script('jquery');
}
add_filter( 'comment_reply_link', '__THEME_PREFIX__comment_reply_link' );
function __THEME_PREFIX__comment_reply_link($link) {
    return str_replace( 'onclick=', 'data-onclick=', $link );
}
add_action( 'wp_head', '__THEME_PREFIX__wp_head' );
function __THEME_PREFIX__wp_head() {
?>
<script type="text/javascript">
  jQuery(function($){
    $('.comment-reply-link').click(function(e){
      e.preventDefault();
      var args = $(this).data('onclick');
      args = args.replace(/.*\(|\)/gi, '').replace(/\"|\s+/g, '');
      args = args.split(',');
      tinymce.EditorManager.execCommand('mceRemoveEditor', true, 'comment');
      addComment.moveForm.apply( addComment, args );
      tinymce.EditorManager.execCommand('mceAddEditor', true, 'comment');
    });
  });
</script>
<?php 
}  


/*
if (!current_user_can('administrator')){
function hide_post_page_options() {
global $post;
$hide_post_options = "<style type=\"text/css\"> .jaxtag { display: none; }</style>";
print($hide_post_options);
}
add_action( 'admin_head', 'hide_post_page_options'  );
}
*/

add_action('create_term','undo_create_term',10, 3);

function undo_create_term ($term_id, $tt_id, $taxonomy) {
    if ( !current_user_can( 'administrator' ) )  {
        if($taxonomy == 'post_tag') {
        	wp_delete_term($term_id,$taxonomy);
        }
    }
}
 
 

//EVENTS AND PRESETNATIONS CLASSES
require_once('functions/class-events.php');
require_once('functions/class-presentations.php');    




// ----------- Remove Tag Metabox and Show as checklist -------------- //
add_action( 'meta_boxes', 'do_my_meta_boxes' );
 
function do_my_meta_boxes( $post_type ) {
 
        
}

/* remove some meta boxes from pages and posts -------------------------
feel free to comment / uncomment  */

function customize_meta_boxes($post_type ) {
  /* Removes meta boxes from pages */
  remove_meta_box('postcustom','page','normal'); // custom fields metabox
 
}

//TAGS AS CHECKBOXES!
function wd_hierarchical_tags_register() {

  // Maintain the built-in rewrite functionality of WordPress tags

  global $wp_rewrite;

  $rewrite =  array(
    'hierarchical'              => false, // Maintains tag permalink structure
    'slug'                      => get_option('tag_base') ? get_option('tag_base') : 'tag',
    'with_front'                => ! get_option('tag_base') || $wp_rewrite->using_index_permalinks(),
    'ep_mask'                   => EP_TAGS,
  );

  // Redefine tag labels (or leave them the same)

  $labels = array(
    'name'                       => _x( 'Tags', 'Taxonomy General Name', 'hierarchical_tags' ),
    'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'hierarchical_tags' ),
    'menu_name'                  => __( 'Tags', 'hierarchical_tags' ),
    'all_items'                  => __( 'All Tags', 'hierarchical_tags' ),
    'parent_item'                => __( 'Parent Tag', 'hierarchical_tags' ),
    'parent_item_colon'          => __( 'Parent Tag:', 'hierarchical_tags' ),
    'new_item_name'              => __( 'New Tag Name', 'hierarchical_tags' ),
    'add_new_item'               => __( 'Add New Tag', 'hierarchical_tags' ),
    'edit_item'                  => __( 'Edit Tag', 'hierarchical_tags' ),
    'update_item'                => __( 'Update Tag', 'hierarchical_tags' ),
    'view_item'                  => __( 'View Tag', 'hierarchical_tags' ),
    'separate_items_with_commas' => __( 'Separate tags with commas', 'hierarchical_tags' ),
    'add_or_remove_items'        => __( 'Add or remove tags', 'hierarchical_tags' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'hierarchical_tags' ),
    'popular_items'              => __( 'Popular Tags', 'hierarchical_tags' ),
    'search_items'               => __( 'Search Tags', 'hierarchical_tags' ),
    'not_found'                  => __( 'Not Found', 'hierarchical_tags' ),
  );

  // Override structure of built-in WordPress tags

  register_taxonomy( 'post_tag', 'post', array(
    'hierarchical'              => true, // Was false, now set to true
    'query_var'                 => 'tag',
    'labels'                    => $labels,
    'rewrite'                   => $rewrite,
    'public'                    => true,
    'show_ui'                   => true,
    'show_admin_column'         => true,
    '_builtin'                  => true,
  ) );

}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//NEW FUNCTIONS!

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 
add_action('wp_head','pluginname_ajaxurl');
function pluginname_ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}

add_action( 'wp_ajax_login_authenticate', 'login_authenticate' );
add_action( 'wp_ajax_nopriv_login_authenticate', 'login_authenticate' );

function login_authenticate(){
	
	$email = $_POST['email'];
	$password = $_POST['password'];

	$url = 'http://isgweb.naph.org/ibridge/Authentication.asmx/AuthenticateUser';
	$result = wp_remote_post( $url, array(
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => array('securityPassword'=> '27D5F4B5-57B2-4A67-BC82-AA2E1756DED3','username'=> $email,'password' => $password),
		'cookies' => array()
	    )
	);

	$body  = wp_remote_retrieve_body($result);
	//echo $body; 
 
	//print_r($result);
	if ($result['response']['message'] == 'OK'){  //if no status then an error occurred.
 
		//NEED TO CHECK HERE FOR AUTHENTICATE OR NOT!!!


 		//Manipulate iBridge XML to get what we want into a string	
 		$body = preg_replace('/<string[^>]+\>/i', "", $body);
 		$body = str_replace('</string>', '', $body);
 		$body = substr($body, 85);
 
 
 		//Turn on XML Error Reporting
 		libxml_use_internal_errors(true);

 		//load in string to conver to  simpleXML
 		$xml = simplexml_load_string(html_entity_decode($body),'SimpleXMLElement', LIBXML_NOCDATA);
 		if ($xml === false) {
		    echo "Failed loading XML\n";
		    foreach(libxml_get_errors() as $error) {
		        echo "\t", $error->message;
		    }
		}

		//Create DOM element to traverse
		$dom = dom_import_simplexml($xml);
		if ($dom === false)return false;

		//print_r($dom);
	 	 
		//Get User Data!
	 	$user = $dom->getElementsByTagName('User'); 

 
	 	//Failed Attempt
	 	if($user->item(0) == 0){

 			return false;
 			
	 	}
	 	else{
	 		//Success 
	 		$token = $user->item(0)->getAttribute('TOKEN');
	 		$email = $user->item(0)->getAttribute('EMAIL');

	 		//login_wp_user("joshdodssddd@mailer.com");
	 		login_wp_user($email);


	 		//print_r($user->item(0)->getAttribute('ID'));
	 		//print_r($user->item(0)->getAttribute('EMAIL'));
	 		//print_r($user->item(0)->getAttribute('TOKEN'));
	 		 
	 	}
 

	 	//SAVE TO ARRAY AND RETURN



 
	}
	else{
		return false;
	}


	//***************************************************************************************************

 
}


function login_wp_user($email){

	$user = get_user_by('email', $email );
		//print_r($user);
	// Redirect URL //
	if ( $user != '')
	{
	    wp_clear_auth_cookie();
	    wp_set_current_user ( $user->ID );
	    wp_set_auth_cookie  ( $user->ID );
        do_action( 'wp_login', $user_login, $user );

	    // $redirect_to = user_admin_url();
	    // wp_safe_redirect( $redirect_to );
	    exit();
	}

	else{
		//User Doesn't exist, create user and login
		if( null == username_exists( $email_address ) ) {
			print_r("OK");
			$password = wp_generate_password( 12, true );
			$user_id = wp_create_user($email, $password, $email);
			login_wp_user($email);
		}
	}
 
}

 






//AUTHENTICATE TEST//////////

function authenticate_user($email, $password){

	$url = 'http://isgweb.naph.org/ibridge/Authentication.asmx/AuthenticateUser';
	$result = wp_remote_post( $url, array(
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => array('securityPassword'=> '27D5F4B5-57B2-4A67-BC82-AA2E1756DED3','username'=> $email,'password' => $password),
		'cookies' => array()
	    )
	);

	$body  = wp_remote_retrieve_body($result);
	//echo $body; 
 
	//print_r($result);
	if ($result['response']['message'] == 'OK'){  //if no status then an error occurred.
 
 		//Manipulate iBridge XML to get what we want into a string	
 		$body = preg_replace('/<string[^>]+\>/i', "", $body);
 		$body = str_replace('</string>', '', $body);
 		$body = substr($body, 85);
 
 		//Turn on XML Error Reporting
 		libxml_use_internal_errors(true);

 		//load in string to conver to  simpleXML
 		$xml = simplexml_load_string(html_entity_decode($body),'SimpleXMLElement', LIBXML_NOCDATA);
 		if ($xml === false) {
		    echo "Failed loading XML\n";
		    foreach(libxml_get_errors() as $error) {
		        echo "\t", $error->message;
		    }
		}

		//Create DOM element to traverse
		$dom = dom_import_simplexml($xml);
		if ($dom === false)return false;
	 	 
		//Get User Date!
	 	$user = $dom->getElementsByTagName('User'); 

	 	print_r($user->item(0)->getAttribute('ID'));
	 	print_r($user->item(0)->getAttribute('EMAIL'));
	 	print_r($user->item(0)->getAttribute('TOKEN'));
	 	//.....

	 	//SAVE TO ARRAY AND RETURN



 
	}
	else{
		return false;
	}


	//***************************************************************************************************

 
}










 
 

?> 