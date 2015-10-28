<?php
/*
Plugin Name: Group and Webinar Members
Plugin URI: http://www.meshfresh.com
Description: Add users to posts as members. Based on a plugin by Hart Associates.
Author: MESH
Version: 1.0.0
Author URI: http://www.meshfresh.com
*/



/**
*  wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

include_once("variables.php");
wp_enqueue_script( 'filedrag', plugin_dir_url( __FILE__ ) . 'filedrag.js' ,'' ,'', true);


//JS for autofill
function autofill_script($hook) {
    if( 'edit.php' != $hook ){
    	wp_enqueue_script( 'autofill', plugin_dir_url( __FILE__ ) . '/autofill.js' );

    }
}
add_action( 'admin_enqueue_scripts', 'autofill_script' );

// Plugin Hooks
register_activation_hook( __FILE__, array('autp','autp_plugin_activate') );
register_uninstall_hook( __FILE__, array('autp','autp_plugin_uninstall') );







if (!class_exists('autp')) {
    class autp {
        //This is where the class variables go, don't forget to use @var to tell what they're for
        /**
        * @var string The options string name for this plugin
        */
        static public  $optionsName = 'autp_options';

        /**
        * @var string $localizationDomain Domain used for localization
        */
        static public  $localizationDomain = "autp";

        /**
        * @var string $pluginurl The path to this plugin
        */
        var $thispluginurl = '';
        /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $thispluginpath = '';

        /**
        * @var array $options Stores the options for this plugin
        */
        var $options = array();

		static public $allUsers;

		var $optionsmenuRole='administrator';
		var $adminmenuRole='administrator';
		var $assignmenuRole='author';

		static public $adminCapability = 'manage_media_categories';
		static public $assignCapability = 'assign_media_categories';
		static public $optionsMenuCapability = 'manage_options';


        //Class Functions
        /**
        * PHP 4 Compatible Constructor
        */
        function autp(){$this->__construct();}

        /**
        * PHP 5 Constructor
        */
        function __construct(){

            //"Constants" setup
            $this->thispluginurl = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
            $this->thispluginpath = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)).'/';

            //fillUsers array for use in the dropdown
            //add_action('admin_init', array(&$this,"fillUsers"), 0);

			//Menu for Media Categories Options Admin section
			add_action("admin_menu", array(&$this,"admin_menu_link"), 0);

			//Initialize the options
            $this->getOptions();

			/* Define the custom box */
			add_action( 'add_meta_boxes', array(&$this,'autp_add_custom_box') );
			/* Do something with the data entered */
			add_action( 'save_post', array(&$this,'autp_save_postdata') );

			/* AJAX for adding users */
			add_action('admin_head', array(&$this,'adduser_autp_action_javascript'));
			add_action('wp_ajax_adduser_autp_action', array(&$this,'adduser_autp_action_callback'));

        }



        function fillUsers()
		{
			$args = array(
				'blog_id' => $GLOBALS['blog_id'],
				'orderby' => 'display_name',
				'order' => 'ASC',
				'count_total' => true,
				'fields' => 'all'
			);

			autp::$allUsers = get_users($args);
		}

		function getOptions() {
            if (!$theOptions = get_option(self::$optionsName)) {
                $theOptions = array('default'=>'options');
				update_option(self::$optionsName, $theOptions);
            }
            $this->options = $theOptions;

			if (!isset($this->options['autp_posttypes']))
			{
				$defaultValues= array();
				array_push($defaultValues, 'post');
				array_push($defaultValues, 'page');
				$this->options['autp_posttypes']=$defaultValues;
			}
        }

		/* Adds a box to the main column on the Post and Page edit screens */
		function autp_add_custom_box() {
			//global $pagenow, $post;
			//if (current_user_can(self::$adminCapability))
			//{
				$autp_posttypes = isset($this->options['autp_posttypes']);
				if ($autp_posttypes == null || !is_array($autp_posttypes))
				{
					foreach($this->options['autp_posttypes'] as $posttype)
					{
						add_meta_box(
							'autp_sectionid',
							__( 'Members', 'autp_textdomain' ),
							array( &$this, 'autp_inner_custom_box' ),
							$posttype,
							'normal',
							'high'
						);
					}
				}
			//}
		}


		/* Prints the box content */
		function autp_inner_custom_box( $post ) {


			global $autp_dir, $autp_base;
			// Use nonce for verification
			wp_nonce_field( plugin_basename( __FILE__ ), 'autp_noncename' );

			echo "
			<script>var pluginDir = '".plugin_dir_url( __FILE__ )."';</script>
			<style type=\"text/css\">
				#autp_addnewuser, .deleteWrapper{
					position:absolute;
					right:0px;
					top:-39px;
					padding:0 5px 0 0;
				}
				#autp_autofillme{position:relative;float:left;}
					#autp_autofillme input[type='text']{position:relative;z-index:1;}
				#autp_fillcont{position:absolute;top:100%;left:0;width:auto;z-index:9999;background:#fff;}
				.autp_fillentry{float:left;width:155px;border-bottom:1px solid #ccc;padding:5px 5%;}
					.autp_fillentry a{display:block;}
					.autp_fillentry a:hover{cursor:pointer;}
					.autp_fillentry a img{float:right;}

				.deleteWrapper{
					top:3px;
				}
				#autp_users{
					width:auto;
				}
				li.user{
					border-color: #DFDFDF;
					border-radius: 3px 3px 3px 3px;
					box-shadow: 0 1px 0 #FFFFFF inset;
					background-color: #F5F5F5;
					background-image: -moz-linear-gradient(center top , #F9F9F9, #F5F5F5);
					border-style: solid;
					border-width: 1px;
					line-height: 1;
					padding: 0;
					position:relative;
					display:inline-block;
					width:45%;margin:0 1% 20px 3%;
					clear:none;
				}
				.currentuser_image{
					display: block;
					float: left;
					height: 60px;
					width: 60px;
					padding: 10px;
				}
				.currentuser_image img{	max-width:60px;}
				.currentuser_userinfo
				{
					display: block;
					float:left;
					padding: 10px;
				}
				.currentuser_bio{
					display: none;
					float:left;
					padding: 10px;
				}
				.currentuser_bio li{
					margin-top:5px;
					clear;both;
					width: 100%;
					position:relative;
				}
				.currentuser_bio li label{
					position:absolute;
					display:block;
					width:140px;
					text-align:right;
					margin:5px 5px 0 0;
				}
				.currentuser_bio li input{

					margin: 7px 0 0 150px;
				}
				.currentuser_bio li textarea{

					margin: 7px 0 0 150px;
					width:300px;
				}
				#filedrag
{

	font-weight: bold;
	text-align: center;
	padding: 1em 0;
	margin: 1em 0;
	color: #555;
	border: 2px dashed #555;
	border-radius: 7px;
	cursor: default;
}

#filedrag.hover
{
	color: #f00;
	border-color: #f00;
	border-style: solid;
	box-shadow: inset 0 3px 4px #888;
}

img
{
	max-width: 100%;
}

pre
{
	width: 95%;
	height: 8em;
	font-family: monospace;
	font-size: 0.9em;
	padding: 1px 2px;
	margin: 0 0 1em auto;
	border: 1px inset #666;
	background-color: #eee;
	overflow: auto;
}

#messages
{
	padding: 0 10px;
	margin: 1em 0;
	border: 1px solid #999;
}

#progress p
{
	display: block;
	width: 240px;
	padding: 2px 5px;
	margin: 2px 0;
	border: 1px inset #446;
	border-radius: 5px;
	background: #ffcc00;
}

#progress p.success
{
	background: #0c0 none 0 0 no-repeat;
}

#progress p.failed
{
	background: #c00 none 0 0 no-repeat;
}
			</style>

			<script>

				jQuery(document).ready(function(){
					jQuery('ul#autpWrapper').sortable();
					ClientUser_UpdateSort();

					jQuery('ul#autpWrapper').bind( \"sortdeactivate\", function(event, ui) {
						ClientUser_UpdateSort();
					});
				});

				function ClientUser_UpdateSort()
				{
					var updatedautport=  jQuery('ul#autpWrapper').sortable('serialize');
					jQuery('#currentuser_updatedsort').val(updatedautport);
				}
			</script>
			";


			echo "



				<fieldset>
				<legend>CSV File Upload</legend>
							<input type=\"hidden\" id=\"MAX_FILE_SIZE\" name=\"MAX_FILE_SIZE\" value=\"300000\" />

				<div>
					<label for=\"fileselect\">Files to upload:</label>
					<input type=\"file\" id=\"fileselect\" name=\"fileselect[]\" multiple=\"multiple\" />
					<div id=\"filedrag\">or drop files here</div>
				</div>

				<div id=\"submitbutton\">
					<button type=\"submit\">Upload File</button>
				</div>

				</fieldset>




			<div id=\"progress\">Progress:</div>


			<div id=\"autp_addnewuser\">
				<div id='autp_autofillme'>
					<div id='userloader'></div>
					<input type='text' name='autp_autofill' length='20' placeholder='Member Name' />
					<div id='autp_fillcont'>

					</div>
				</div>
				<input type=\"hidden\" value=\" \" name=\"autp_users\" id=\"autp_users\" > <span id=\"autp_users_disp_name\"></span>";


					/*echo "<option value=\"0\" selected=\"selected\">Select User</option>";
					foreach(autp::$allUsers as $user)
					{
						echo "<option value=\"$user->ID\">$user->display_name</option>";
					}
					*/
				echo "
				</nput>
				<a href=\"javascript:AddClientUserUser();\" class=\"button\" title='Add User'>Add Member</a>
			</div>
			<ul id=\"autpWrapper\">
			";
			$i=1;
			$CurrentUsers = get_post_meta($post->ID, 'autp');

			if(!empty($CurrentUsers))
			{

				foreach($CurrentUsers as $cu)
				{
					foreach($cu as $value)
					{
						$id= 			$value['user_id'];
						$info= 			$value['user_info'];

						$user_info = get_userdata($id);
						if($info=='')
							$info = $user_info->user_description;
						if($id){
							echo self::GetLI($i, $id, $info);
						}

						$i+=1;
					}
				}


			}

			echo "</ul>";
			echo "<a name=\"autp_bottom\"></a>";
			echo "<input id=\"currentuser_lastsort\" name=\"currentuser_lastsort\" type=\"hidden\" value=\"".($i-1)."\" />";
			echo "<input id=\"currentuser_updatedsort\" name=\"currentuser_updatedsort\" type=\"hidden\" value=\"\" />";


		}

		/* When the post is saved, saves our custom data */
		function autp_save_postdata( $post_id ) {
			// verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
				return;
			}

			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			if (isset($_POST['autp_noncename']) && !wp_verify_nonce( $_POST['autp_noncename'], plugin_basename( __FILE__ ) ) ){
				return;
			}

			//Make sure not a new post



			// Check permissions
			if ( 'page' == $_POST['post_type'] )
			{
				if ( !current_user_can( 'edit_page', $post_id ) )
					return;
			}
			else
			{
				if ( !current_user_can( 'edit_post', $post_id ) )
				return;
			}

			// OK, we're authenticated: we need to find and save the data
			$i=1;

			$newvalue = array();
			$groupIDs = array();

			$thispost = get_post($post_id);
			$thisposttitle = $thispost->post_title;
			$thispostlink  = get_permalink($post_id);

			if(isset($_POST['currentuser_updatedsort']))
			{
				$currentsort = $this->_unserializeJQuery($_POST['currentuser_updatedsort']);

				if(!empty($currentsort))
				{
					$count_total = count($currentsort);

					for($i = 0; $i <$count_total; ++$i)
					{

						$id=$currentsort[$i];


						$array = array();
						$array['user_id'] = $_POST['currentuser_'.$id.'_id'];
						$array['user_title'] = $_POST['currentuser_'.$id.'_title'];
						$array['user_info'] = $_POST['currentuser_'.$id.'_info'];
						$array['user_showinfo'] = isset($_POST['currentuser_'.$id.'_show'])?true:false;
						$array['user_sortorder'] = $i;


						$newvalue[] = $array;

						//Variables
						$groupUser = $_POST['currentuser_'.$id.'_id'];
						$newUserMeta = array();
						$oldUserMeta = get_user_meta($groupUser, 'groupMem', true);
						$toUserData  = get_userdata($groupUser);
						$toUserName  = $toUserData->first_name;
						$toUserEmail = $toUserData->user_email;

						//Check if meta exists
						if($oldUserMeta){
							$newUserMeta = $oldUserMeta;
						}else{
							add_user_meta($groupUser, 'groupMem');
						}

						//Add data to meta if ID doesn't already exist
						if (in_array($post_id, $newUserMeta)) {

						}else{
							array_push($newUserMeta, $post_id);
							update_user_meta($groupUser, 'groupMem', $newUserMeta);
						}

						//Add new userIDs to temp array
						array_push($groupIDs, $groupUser);
					}
				//add moderator to meta
				$modfield = get_field_object('mod');
				$nr = $modfield['key'];
				foreach($_POST['fields'] as $k=>$v){
					if($k == $nr){
						$theMOD = $v;
					}
				}
				if(!in_array($theMOD,$groupIDs)){
					$mod = get_userdata($theMOD);
					$array = array();
					$array['user_id'] = $mod->ID;
					$array['user_title'] = '';
					$array['user_info'] = '';
					$array['user_showinfo'] = true;
					$array['user_sortorder'] = 0;

					$newvalue[] = $array;
				}

				$oldPostMeta = get_post_meta($post_id, 'autp');
				add_post_meta($post_id, 'autp', $newvalue, true) or update_post_meta($post_id, 'autp', $newvalue);
				$newPostMeta = get_post_meta($post_id, 'autp');

				//Create array of user IDs with groupMem
				$newarray = array();
				$existingUsers = get_users(array('meta_key' => 'groupMem'));
					foreach($existingUsers as $user){
						$userid = $user->ID;
						array_push($newarray, $userid);
					}
				//Difference between arrays
				$result = array_diff($newarray, $groupIDs);
					//Remove postID from difference
					foreach($result as $r){
						$rM = get_user_meta($r, 'groupMem', true);
						if(($key = array_search($post_id, $rM)) !== false) {
						    unset($rM[$key]);
						}
						update_user_meta($r, 'groupMem', $rM);
					}

				//Convert autp to array of IDs for new
				$oldIDs = array();
				foreach($oldPostMeta as $member){
					foreach($member as $value){
						$id = $value['user_id'];
						array_push($oldIDs,$id);
					}
				}
				//Convert autp to array of IDs for new
				$newIDs = array();
				foreach($newPostMeta as $member){
					foreach($member as $value){
						$id = $value['user_id'];
						array_push($newIDs,$id);
					}
				}
				//Send Email to the difference between the new array and the old array (only results that exist in the NEW that don't exist in the OLD)
				$IDdiff = array_diff($newIDs, $oldIDs);
				foreach($IDdiff as $ID){
					$thisuser = get_userdata($ID);
					$thisname = $thisuser->first_name;
					$thisemail = $thisuser->user_email;

					$mailout = "
						<h3>You've been added to $thisposttitle</h3>
						<p>Hello $thisname,</p>
						<p>Join the conversation at the <a href='$thispostlink'>Member Network</a></p>
					";
					$headers[] = 'From: America\'s Essential Hospitals <info@example.net>';
					$headers[] = 'Content-type: text/html';
					//wp_mail( $thisemail, 'You\'ve been added to a group', $mailout, $headers );
				}
				}
			}
		}



		function adduser_autp_action_javascript() {
		?>
			<script type="text/javascript" >
				function DeleteClientUserUser(index)
				{
					var answer = confirm("Delete User?")
					if (answer){
						//remove div
						jQuery("#user_"+index).remove();

						//update last sort
						var lastsort = jQuery("#currentuser_lastsort").val();
						jQuery("#currentuser_lastsort").val(lastsort-1);


						ClientUser_UpdateSort();
					}
				}

				function AddClientUserUser()
				{
					var selecteduser = jQuery("#autp_users").val();

					jQuery('input[name="autp_autofill"]').val('');
		            jQuery('#autp_fillcont').empty();
					if(selecteduser>0)
					{


						var lastsort = jQuery("#currentuser_lastsort").val();

						var data = {
							action: 'adduser_autp_action',
							userid: selecteduser,
							sort: lastsort
						};
						jQuery.post(ajaxurl, data, function(response) {
							if(response);
							{

								var wrapper = document.getElementById("autpWrapper");

								wrapper.innerHTML=wrapper.innerHTML+response;

								var lastsort = document.getElementById("currentuser_lastsort");
								var lastsortvalue= parseInt(lastsort.value);
								lastsort.value=(lastsortvalue+1);


								ClientUser_UpdateSort();

								//window.location = "#autp_sectionid";
							}
						});
					}
					else
					{
						alert('You must first select a user to add!');
					}
				}

			</script>
		<?php
		}
		function adduser_autp_action_callback() {
			global $autp_base;

			if(isset($_POST["userid"])&&isset($_POST["sort"])) {

				$html='';
				$id=$_POST["userid"];
				$lastsort=$_POST["sort"];
				$i=$lastsort+1;

				$user_info = get_userdata($id);
				$info = $user_info->user_description;

				$html = $this->GetLI($i, $id, $info);


				echo $html;

			}

			die(); // this is required to return a proper result
		}

		function GetLI($fieldId, $user_id, $info)
		{

			$user_info = get_userdata($user_id);
			$image = get_avatar( $user_id, 60 );

			$html = "
					<li class=\"user\" id=\"user_".$fieldId."\">
						<input id=\"currentuser_".$fieldId."_id\" name=\"currentuser_".$fieldId."_id\" type=\"hidden\" value=\"".$user_id."\" />

						<h3>$user_info->first_name $user_info->last_name :  $user_info->user_nicename </h3>
						<div class=\"deleteWrapper\">
							<a href=\"javascript:DeleteClientUserUser('".$fieldId."');\" class=\"deleteLink button\">Remove Member</a>
						</div>

						<div class=\"currentuser_image\">
							$image
						</div>
						<div class=\"currentuser_userinfo\">
							<p><span class=\"lablel\">Email:</span> $user_info->user_email</p>
						</div>
						<ul class=\"currentuser_bio\">
							<li>
								<label>Biographical Info</label>
								<textarea id=\"currentuser_".$fieldId."_info\" name=\"currentuser_".$fieldId."_info\" >$info</textarea>
							</li>
						</ul>
						<div style=\"clear:both;\">&nbsp;</div>
					</li>
				";

				return $html;

		}


		//Place Options Link In Left Nav
        function admin_menu_link() {
            add_options_page('Add User Options', 'Add User Options', self::$optionsMenuCapability, basename(__FILE__), array(&$this,'admin_options_page'));
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), self::$optionsMenuCapability, 2 );
        }
		//Place settings link on plugin page
		function filter_plugin_actions($links, $file) {
           $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
           array_unshift( $links, $settings_link ); // before other links

           return $links;
        }



		function saveAdminOptions(){
            return update_option(self::$optionsName, $this->options);
        }

        function admin_options_page() {
			global $autp_dir, $autp_base;
			global $wp_roles;

            if(isset($_POST['autp_save']) && $_POST['autp_save']){
                if (! wp_verify_nonce($_POST['_wpnonce'], 'autp-update-options') ) die('Whoops! There was a problem with the data you posted. Please go back and try again.');

				//SAVE OPTIONS
				$autp_posttypes = @$_POST['autp_posttypes'] ;

				if ($autp_posttypes !== null && is_array($autp_posttypes))
				{
					$this->options['autp_posttypes'] =$autp_posttypes;

				}
                $this->saveAdminOptions();
				//SAVE ROLES
                $adminRoles = @$_POST['autp_adminRoles'] ;
                $assignRoles = @$_POST['autp_assignRoles'];
                foreach ($wp_roles->get_names() as $role_name => $formal_name ) {
                	$role = get_role( $role_name ) ;
                	if ($adminRoles !== null && is_array($adminRoles) &&
                				in_array($role_name, $adminRoles)) {
                		$role->add_cap( autp::$adminCapability ) ;
                	}
                	else {
                		$role->remove_cap( autp::$adminCapability );
                	}
                    if ($assignRoles !== null && is_array($assignRoles) &&
                				in_array($role_name, $assignRoles)) {
                		$role->add_cap( autp::$adminCapability) ;
                	}
                	else {
                		$role->remove_cap( autp::$assignCapability );
                	}
                }
                echo '<div class="updated"><p>Success! Your changes were sucessfully saved!</p></div>';
            }






			//post types for multiselect
			$post_types = get_post_types(array('public' => true));
			foreach($post_types as $key => $value)
			{
				if($value == 'attachment')
				{
					unset($post_types[$key]);
				}
			}





?>
                <div class="wrap">
                <h2>Options - Add Users To PostType</h2>
                <form method="post" id="autp_options">
                <?php wp_nonce_field('autp-update-options'); ?>
                    <table width="100%" cellspacing="2" cellpadding="5" class="form-table">
                        <tr valign="top">
                            <th width="33%" scope="row">
								<?php _e('Custom Post Types:', self::$localizationDomain); ?>
							</th>
                            <td>
								<select name="autp_posttypes[]" id="autp_posttypes" multiple="multiple" style="height: 120px;">
									<?php
										foreach($post_types as $key => $value):
											$selected=false;

											if(isset($this->options['autp_posttypes']))
											{
												if (in_array($value, $this->options['autp_posttypes'])) {
													$selected=true;
												}
											}
									?>
										<option <?php echo ($selected?'selected="selected" ': '') ?> value="<?php echo $value; ?>"><?php echo $value?></option>
									<?php
										endforeach;
									?>
								</select>
							</td>
                        </tr>
                        <tr valign="top">
                            <th width="33%" scope="row"><?php _e('Manager Role Access:', self::$localizationDomain); ?></th>
                            <td>
								<select name="autp_adminRoles[]" id="autp_adminRoles" multiple="multiple" style="height: 120px;">
									<?php
										foreach ($wp_roles->get_names() as $role_name => $formal_name):
											$role_O = get_role( $role_name ) ;
									?>
											<option <?php echo ($role_O->has_cap(autp::$adminCapability)?'selected="selected" ': '') ?>value="<?php echo $role_name?>"><?php echo $formal_name?></option>
									<?php
										endforeach;
									?>
                            </select>
							</td>
                        </tr>
                        <tr valign="top">
                            <th width="33%" scope="row"><?php _e('Options Page Role Access:', self::$localizationDomain); ?></th>
                            <td>
                            <select name="autp_assignRoles[]" id="autp_assignRoles" multiple="multiple" style="height: 120px;">
								<?php
									foreach ($wp_roles->get_names() as $role_name => $formal_name):
										$role_O = get_role( $role_name ) ;
								?>
										<option <?php echo ($role_O->has_cap(autp::$assignCapability)?'selected="selected" ': '') ?>value="<?php echo $role_name?>"><?php echo $formal_name?></option>
								<?php
									endforeach;
								?>
                            </select>
                        </td>
                        </tr>

                        <tr>
                            <th colspan=2><input type="submit" name="autp_save" value="Save" /></th>
                        </tr>
                    </table>
                </form>
                <?php
        }























	   /* ============================
		* PUBLIC FUNCTIONS
		* ============================
		*/

	   public function getusers()
	   {
			global $post, $autp_base;
			if($post)
			{
				$CurrentUsers = get_post_meta($post->ID, 'autp');

				if(!empty($CurrentUsers))
				{
					foreach($CurrentUsers as $cu)
					{
						foreach($cu as $value)
						{
							$id= 			$value['user_id'];
							$info= 			$value['user_info'];

							$image = get_avatar( $id, 60 );
							$user_info = get_userdata($id);

							if($info=='')
								$info = $user_info->user_description;

							echo "
								<div class=\"user\" >
									<h3>$user_info->first_name $user_info->last_name</h3>
									<div class=\"currentuser_image\">
										$image
									</div>
									<div class=\"currentuser_userinfo\">
										<p><span class=\"lablel\">Email:</span> <a href='mailto:$user_info->user_email'>$user_info->user_email</a></p>
										<p>$info</p>
									</div>
								</div>
							";

						}
					}
				}
			}
	   }



		/* ============================
		* MISC FUNCTIONS
		* ============================
		*/

		function _unserializeJQuery($rubble = NULL) {
			$bricks = explode('&', $rubble);
			$built= array();
			foreach ($bricks as $key => $value) {
				$walls = preg_split('/=/', $value);
				$value = urldecode($walls[1]);
				array_push($built, ((int)$value));
			}

			return $built;
		}

		function contains($mystring, $findme) {
				$pos = strpos($mystring, $findme);

				if($pos === false) {
						// string needle NOT found in haystack
						return false;
				}
				else {
						// string needle found in haystack
						return true;
				}

		}
		function cleanQuery($string)
		{
		  if(get_magic_quotes_gpc())  // prevents duplicate backslashes
		  {
			$string = stripslashes($string);
		  }
		  if (phpversion() >= '4.3.0')
		  {
			$string = mysql_real_escape_string($string);
		  }
		  else
		  {
			$string = mysql_escape_string($string);
		  }
		  return $string;
		}

		/* ============================
		* Activation
		* ============================
		*/

		static function autp_plugin_activate()
		{
			// Initialize default capabilities

			$role = get_role( 'administrator' );
			$role->add_cap( self::$adminCapability );
			$role->add_cap( self::$assignCapability );

			$role = get_role( 'content_creator' );
			$role->add_cap( self::$assignCapability );
			$role->add_cap( self::$adminCapability );
		}

		/* ============================
		* UNINTALL
		* ============================
		*/
		static function autp_plugin_uninstall()
		{
			global $wp_roles;
			// Initialize default capabilities

			$rolenames = $wp_roles->get_names() ;
			foreach ( $rolenames as $rolename => $displ ) {
				$role = get_role( $rolename );
				$role->remove_cap( self::$adminCapability );
				$role->remove_cap( self::$assignCapability );
			}

			delete_option(self::$optionsName);
		}


  } //End Class
} //End if class exists statement

//instantiate the class
if (class_exists('autp')) {
    $autp_var = new autp();
}


//Prevent update
add_filter( 'http_request_args', 'dm_prevent_update_check', 10, 2 );
function dm_prevent_update_check( $r, $url ) {
    if ( 0 === strpos( $url, 'http://api.wordpress.org/plugins/update-check/' ) ) {
        $my_plugin = plugin_basename( __FILE__ );
        $plugins = unserialize( $r['body']['plugins'] );
        unset( $plugins->plugins[$my_plugin] );
        unset( $plugins->active[array_search( $my_plugin, $plugins->active )] );
        $r['body']['plugins'] = serialize( $plugins );
    }
    return $r;
}

?>