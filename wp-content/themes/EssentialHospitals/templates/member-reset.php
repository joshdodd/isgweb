<?php
/*
Template Name: Member Network - Reset Password
*/
// this page displays your own profile information and allows you to edit it
include ("includes/aeh_config.php");
include ("includes/aeh-functions.php");

global $wpdb;
$currentUser = get_current_user_id();
$user_info = get_userdata($currentUser);
$user_avatar = get_avatar($currentUser);
get_header();

$usermeta = get_user_meta($userID);
$aeh_member = $usermeta['aeh_member_type'][0];
$user_email = $usermeta['user_email'][0];
$imisid = $usermeta['aeh_imis_id'][0];



$token = get_user_meta($currentUser, 'isg_token', true );
?>



 
    <div id="membernetwork">
        <div class="container" style="width: ">
            <h1 class="title"><span class="grey">Essential Hospitals</span> Member Network | My Profile</h1>
            <?php get_template_part('membernetwork/content','usernav'); $output = "";?>

            <div class="groupcol clearfix <?php if(!is_user_logged_in()){echo "floatleft fullwidth";}else{echo "group-details";}?>">
             
                	<iframe src='https://isgweb.essentialhospitals.org/ISGweb/LogIn/RetrievePassword.aspx?Token=<?php echo $token; ?>' isgwebsite="1" name="ISGwebContainer" id="ISGwebContainer" marginwidth="1" marginheight="0" frameborder="0" vspace="0" hspace="0" scrolling="yes" width="100%" style="overflow:visible; height: 2000px; display:block;"> Sorry, your browser doesn't support iframes. </iframe> 

			 
            </div>
 
        </div>
<?php get_footer('sans'); ?>