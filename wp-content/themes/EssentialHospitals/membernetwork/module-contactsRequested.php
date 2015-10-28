<div class="panel" id="PendingConnections">
	<h2 class="heading">Requests Sent</h2>
	<div class="gutter">
		<div id="pending-contacts">
	<?php global $wpdb;
			$curID = get_current_user_id();
			//echo $curID;
			//Get contacts for current user
			$contacts = $wpdb->get_results("SELECT user_id, friend_id
											FROM wp_aeh_connections
											WHERE user_ID = $curID
											AND consent_date = 0");
			//print_r($contacts);
			//Create ID array from contacts; exclude currentuser ID
			$myCont = array();
			foreach($contacts as $contact){
				array_push($myCont, $contact->friend_id);
			}

			if(count($myCont) > 0){ ?>

			<?php	//print_r($myCont);
				//Loop through IDs
				foreach($myCont as $user){
					$uData = get_userdata($user);
					$fName = $uData->first_name;
					$lName = $uData->last_name;
					$staff = get_usermeta($user,'aeh_staff');
					$ava = get_avatar($user, 96); ?>
					<div class="pending-contact">
						<a class="member-icon" href="<?php echo get_permalink(276); ?>?member=<?php echo $user; ?>">
							<div class="group-memberavatar friendedmeicon">
								<?php if($staff == 'Y'){echo '<div class="hospMem"></div>';}?>
								<span class="group-membername"><?php echo $fName.' '.$lName;?></span>
								<?php echo $ava; ?>
							</div>
						</a>
					</div>
			<?php } ?>
		</div>

	<?php }else{echo "<p>You haven't sent any requests</p>";} ?>
	</div>
</div>