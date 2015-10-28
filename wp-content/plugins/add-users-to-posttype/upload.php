<?php
// Our include
	define('WP_USE_THEMES', false);
	require_once('../../../wp-load.php');
//$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
$fn='x.csv';
//$fn = ($_SERVER['HTTP_X_FILENAME']) ;
if ($fn) {

	// AJAX call
	file_put_contents(
		'uploads/' . $fn,
		file_get_contents('php://input')
	);
	//echo "$fn uploaded";


	$array = array();
	$file = fopen("php://input","r");

	while(! feof($file))
	 {
	  $data[] = fgetcsv($file);
	 }

	fclose($file);

	//Get location of EMAIL column in csv
	for ($i = 0; $i < count($data[0]); $i++) {
	     if (strcasecmp($data[0][$i], 'email') == 0)
	     	$email_col = $i;
	}

	 //Create array of emails
	for ($i = 1; $i < count($data); $i++) {
	    $emails [$i] = $data[$i][$email_col];
	}



	$_SESSION['lines'] = $emails;

	foreach($emails as $email){
		$user = get_user_by( 'email', $email );
		$uFname = $user->first_name;
		$uLname = $user->last_name;
		$uEmail = $user->user_email;
		$uId = $user->ID;
		$uAva = get_avatar($uId, 60);
		$uBio = $user->description;
		$uLogin = $user->user_login;

		if($user){
			$output .= "<li class='user' id='user_2'>
						<input id='currentuser_2_id' name='currentuser_2_id' type='hidden' value='$uId'>

						<h3>$uFname $uLname :  $uLogin </h3>
						<div class='deleteWrapper'>
							<a href='javascript:DeleteClientUserUser('2');' class='deleteLink button'>Remove Member</a>
						</div>

						<div class='currentuser_image'>
							$uAva
						</div>
						<div class='currentuser_userinfo'>
							<p><span class='lablel'>Email:</span> $uEmail</p>
						</div>
						<ul class='currentuser_bio'>
							<li>
								<label>Biographical Info</label>
								<textarea id='currentuser_2_info' name='currentuser_2_info'>$uDesc</textarea>
							</li>
						</ul>
						<div style='clear:both;'>&nbsp;</div>
					</li>";
		}
	}
	echo $output;

	exit();

}
else {

	// form submit
	$files = $_FILES['fileselect'];
	$_SESSION['lines'] = "Ffff";

$_SESSION['lines']	= $_SERVER;
	foreach ($files['error'] as $id => $err) {

		if ($err == UPLOAD_ERR_OK) {
			$fn = $files['name'][$id];
			move_uploaded_file(
				$files['tmp_name'][$id],
				'uploads/' . $fn
			);
			echo "<p>File $fn uploaded.</p>";
		}
	}

	print_r($_SESSION['lines'] );
	echo "<pre>";
	print_r($_SERVER);
	echo "</pre>";

}