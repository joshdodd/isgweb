<?php  /* Template Name: Member Network - Registration */
get_header();
?>

<div id="membernetwork">
	<div class="container">
		<h1 class="title"><span class="grey">Essential Hospitals</span> Member Network</h1>
		<div id="registrationcontent" class="group">
			<div class="gutter clearfix">
				<h2 class='heading'>Registration</h2>
				<div id="loginregister" class="floatleft onehalf">
					<div id="memberReg">
						 
							<div class="gutter">
								<?php //echo do_shortcode('[wp-members page="register"]'); ?>

								<iframe src='https://isgweb.essentialhospitals.org/ISGweb/Profile/CreateNewUser.aspx?iWebContinuePage=%2fISGweb%2fProfile%2fEditProfile.aspx' isgwebsite="1" name="ISGwebContainer" id="ISGwebContainer" marginwidth="1" marginheight="0" frameborder="0" vspace="0" hspace="0" scrolling="no" width="100%" style="overflow:hidden; height: 1000px; display:block;"> Sorry, your browser doesn't support iframes. </iframe> 
 

							</div>
					 
					</div>
				</div>
				<div class="floatleft onehalf" id="reg-cont">
					<div class="gutter">
						<?php 
							if ( have_posts() ) {
								while ( have_posts() ) {
									the_post(); 
									the_content(); 
								} // end while
							} // end if
							?>
				 
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_footer('sans'); ?>