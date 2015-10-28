<?php
$SQL_FROM = $wpdb->users;
$SQL_WHERE = 'display_name';
$searchq = stripslashes($_GET['q']);
$getRecord_sql = "SELECT * FROM {$SQL_FROM} WHERE {$SQL_WHERE} LIKE '%{$searchq}%' LIMIT 5";
$rows = $wpdb->get_results($getRecord_sql);
if(strlen($searchq)>0)
{
	echo "<ul>";
	if ($wpdb->num_rows)
	{
		foreach($rows as $row)
		$thisuser = get_userdata($row->ID);
		{
			if($row->ID != $user_ID) //Don't let users message themselves
			{
				?>
				<li><a href="#" onClick="fillText('<?php echo $thisuser->user_firstname; ?> <?php echo $thisuser->user_lastname; ?>', '<?php echo $thisuser->user_login; ?>');return false;"><?php echo $thisuser->user_firstname; ?> <?php echo $thisuser->user_lastname; ?></a></li>
				<?php
			}
		}
	}
	else
		echo "<li>".__("No Matches Found", "cartpaujpm")."</li>";
	echo "</ul>";
}
die();
?>
