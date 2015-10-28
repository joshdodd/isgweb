<div class="panel" id="run-webinars">
	<h2 class="heading">Upcoming Webinars</h2>
		<?php $groups = get_user_meta($currentUser, 'groupMem', true);
		if($groups){
			$today = mktime(0, 0, 0, date('n'), date('j'));
		foreach($groups as $group){
			$post = get_post($group);
			if($post){
			$title = $post->post_title;
			$desc = $post->post_excerpt;
			$link = get_permalink($group);
			$webinar_date = get_post_meta($post->ID,'webinar_date', true );
			$type = get_post_type($post->ID);
			$terms = wp_get_post_terms($post->ID, 'webinartopics');
			$termCount = count($terms);
			if(($termCount > 0)&&($webinar_date > $today)){ ?>
			<div class="grouplist">
				<div class="gutter">
					<span class="title"><a href="<?php echo $link; ?>"><?php echo $title; ?></a></span>
					<span class="desc"><?php echo $desc; ?></span>
				</div>
			</div>
		<?php } } } }else{
			echo "<p>You aren't registered for any webinars.<br><a href='".get_post_type_archive_link('webinar')."'>Browse recorded and future webinars &raquo;</a></p>";
		} ?>
</div>