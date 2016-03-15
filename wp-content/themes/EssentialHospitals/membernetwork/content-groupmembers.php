<?php
global $post;
if($post->post_parent){
	$parent = array_reverse(get_post_ancestors($post->ID));
	$members = get_post_meta($parent[0], 'autp');
}else{
	$members = get_post_meta($post->ID, 'autp');
}

if($post->post_parent){
	$imis_code = get_post_meta($parent[0], 'imis_code'); //Get imis code of parent group for new auth
	$imis_type = get_post_meta($parent[0], 'imis_type'); //Get imis code of parent group for new auth
}else{
	$imis_code = get_post_meta($post->ID, 'imis_code', true); //Get imis code for new auth
	$imis_type = get_post_meta($post->ID, 'imis_type', true); //Get imis code for new auth
}


/*
1. Call SP - send imis_type and imis_code.
2. Return array of imis IDS
3. Build array of wp ids (create new user if imis id doesn't exist?)
4. Combine new array with legacy $members arry
*/
 
$emails = GetGroupMembers($imis_code,$imis_type);
 
	
if($emails!=''){
	$first_set = array_slice($emails , 0, 9);
	$second_set = array_slice($emails ,9);
 


	echo "<div id='firstninemembers'>";

	foreach($first_set as $email){

		$user = get_user_by('email', $email);
		$id = $user->ID;
		$user_info = get_userdata($id);
		$user_avatar = get_avatar($id); 
		?>

		<div class="group-memberavatar">
			<span class="group-membername"><?php echo $user_info->user_firstname.' ' .$user_info->user_lastname; ?></span>
			<a href="<?php echo get_permalink(276); ?>?member=<?php echo $id;?>"><?php echo $user_avatar; ?></a>
		</div>

	<?php }

	echo "</div>"; 

	if($second_set){
		echo "<div id='leftovermembers'>";
		foreach($second_set as $email){
			$user = get_user_by('email', $email);
			$id = $user->ID;
			$user_info = get_userdata($id);
			$user_avatar = get_avatar($id); 
			if($id == 2 )continue; ?>

			<div class="group-memberavatar">
				<span class="group-membername"><?php echo $user_info->user_firstname.' ' .$user_info->user_lastname; ?></span>
				<a href="<?php echo get_permalink(276); ?>?member=<?php echo $id;?>"><?php echo $user_avatar; ?></a>
			</div>
	<?php }
		echo "</div><div id='moremembers'>See all Members</div>";
	}  

}
else{	
 

	foreach($members as $member){
		$first = array_slice($member, 0, 9);
		$second = array_slice($member,9);

		echo "<div id='firstninemembers'>";
		foreach($first as $user){
			$id	= $user['user_id'];
			$user_info = get_userdata($id);
			$user_avatar = get_avatar($id); 
			if($id == 2 )continue; 
			?>

			<div class="group-memberavatar">
				<span class="group-membername"><?php echo $user_info->user_firstname.' ' .$user_info->user_lastname; ?></span>
				<a href="<?php echo get_permalink(276); ?>?member=<?php echo $id;?>"><?php echo $user_avatar; ?></a>
			</div>
	<?php }
		echo "</div>";
		if($second){
			echo "<div id='leftovermembers'>";
			foreach($second as $user){
				$id	= $user['user_id'];
				$user_info = get_userdata($id);
				$user_avatar = get_avatar($id); 
				if($id == 2 )continue; ?>

				<div class="group-memberavatar">
					<span class="group-membername"><?php echo $user_info->user_firstname.' ' .$user_info->user_lastname; ?></span>
					<a href="<?php echo get_permalink(276); ?>?member=<?php echo $id;?>"><?php echo $user_avatar; ?></a>
				</div>
		<?php }
			echo "</div><div id='moremembers'>See all Members</div>";
		}
	 } 

}?>