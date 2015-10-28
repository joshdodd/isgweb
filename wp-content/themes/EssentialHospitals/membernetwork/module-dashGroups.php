<div class="panel" id="run-groups">
	<h2 class="heading">Programs and Groups</h2>
	<?php $groups = get_user_meta($currentUser, 'groupMem', true);
		if($groups){
		foreach($groups as $group){
			$post = get_post($group);
			$title = $post->post_title;
			$desc = $post->post_excerpt;
			$link = get_permalink($group);
			$type = get_post_type($post->ID);
			$status = get_post_status( $post->ID );
			if($type == 'group' && $status == 'publish'){ ?>
			<div class="grouplist">
				<div class="gutter">
					<span class="title"><a href="<?php echo $link; ?>"><?php echo $title; ?></a></span>
					<span class="desc"><?php echo $desc; ?></span>
				</div>
			</div>
		<?php } } }else{
			echo '<p>No group memberships yet.</p>';
		} ?>
	<div class="dashboard-button"> <a href="<?php echo get_permalink(392); ?>" id="addnewgroup">Start a Private Group</a> </div>
</div>