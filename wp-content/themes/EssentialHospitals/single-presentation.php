<?php get_header(); ?>

<?php  $speakerIMG = wp_get_attachment_url( get_post_thumbnail_id(472) );   ?>
<div id="featured-img" class="education webinar archive" style="background-image:url(<?php echo $speakerIMG ?>);">
	<div class="container">
		<div id="featured-intro">
				<h3><span>EDUCATION</span><br/>Presentations</h3>
		</div>
	</div>
</div>
<div id="contentWrap" class="education webinar archive">
	<a id="prevbtn" title="Show previous"> </a>
	<a id="nextbtn" title="Show more"> </a>
	<div class="gutter">
		<div class="container">
			<?php
				if(has_nav_menu('primary-menu')){
					$defaults = array(
						'theme_location'  => 'primary-menu',
						'menu'            => 'primary-menu',
						'container'       => 'div',
						'container_class' => '',
						'container_id'    => 'pageNav',
						'menu_class'      => 'quality',
						'menu_id'         => '',
						'echo'            => true,
						'fallback_cb'     => 'wp_page_menu',
						'before'          => '',
						'after'           => '',
						'link_before'     => '',
						'link_after'      => '',
						'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
						'depth'           => 2,
						'walker'          => ''
					); wp_nav_menu( $defaults );
				}
			?>
			<div id="breadcrumbs">
				<ul>
					<li><a href="<?php echo home_url(); ?>">Home</a>
						<?php
						$defaults = array(
						'theme_location'  => 'primary-menu',
						'menu'            => 'primary-menu',
						'container'       => '',
						'container_class' => '',
						'container_id'    => '',
						'menu_class'      => 'menu',
						'menu_id'         => '',
						'echo'            => true,
						'fallback_cb'     => 'wp_page_menu',
						'before'          => '',
						'after'           => '',
						'link_before'     => '',
						'link_after'      => '',
						'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
						'depth'           => 0,
						'walker'          => ''
					); wp_nav_menu( $defaults ); ?>
					</li>
				</ul>

				<a href="<?php echo site_url('/feed/?post_type=presentation'); ?>" target="_blank">
					<div id="rssFeedIcon" class="education">
						Subscribe
					</div>
				</a>

			</div>

			<div id="postFeatured">
				<div class="eightteen columns filters-presentations">
					<!-- begin Category Filters !-->
	 				<span> FILTER BY ››</span>
	 				<div id="red_btn" data-filter="action" class="filter_btn-presentations ">
	 					<img src="http://essentialhospitals.org/wp-content/themes/EssentialHospitals/images/policy.png">
	 					<span>Action</span>
	 				</div>
	 				<div id="green_btn" data-filter="quality" class="filter_btn-presentations ">
	 					<img src="http://essentialhospitals.org/wp-content/themes/EssentialHospitals/images/quality.png">
	 					<span>Quality</span>
	 				</div>
	 				<div id="gray_btn" data-filter="education" class="filter_btn-presentations ">
	 					<img src="http://essentialhospitals.org/wp-content/themes/EssentialHospitals/images/edu.png">
	 					<span>Education</span>
	 				</div>
	 				<div id="blue_btn" data-filter="institute" class="filter_btn-presentations ">
	 					<img src="http://essentialhospitals.org/wp-content/themes/EssentialHospitals/images/inst.png">
	 					<span>Institute</span>
	 				</div>
					<div id="blue_btn" data-filter="*" class="filter_btn-presentations ">
						<span>All Sections</span>
					</div>
	 				<!-- end Category Filters !-->

	 				
	 			</div>
			</div>


			<div id="contentPrimary">
				<div class="graybar"></div>
				<div class="gutter clearfix">


					

					<span class="filterby">Filter By Event >></span>
					<select name="pres-eventFilter" id="pres-eventFilter">
						<option value="*">Select Event</option>
						<?php 
						$args = array( 'posts_per_page' => -1, 'post_type'=> 'events', 'orderby' => 'title' );

						$myposts = get_posts( $args );
						foreach ( $myposts as $post ) : setup_postdata( $post ); ?>
							<option value="<?php echo $post->ID;?>"><?php the_title(); ?></option>
	 
						<?php endforeach; 

						wp_reset_postdata(); ?>
						<option value="*">Show All</option>
					</select>

					<!--
					<span class="filterby">Filter By Month >></span>
					<select name="presMonth" id="pres-monthFilter">
						<option value="*">Select Month</option>
						<option value="1">January</option>
						<option value="2">February</option>
						<option value="3">March</option>
						<option value="4">April</option>
						<option value="5">May</option>
						<option value="6">June</option>
						<option value="7">July</option>
						<option value="8">August</option>
						<option value="9">September</option>
						<option value="10">October</option>
						<option value="11">November</option>
						<option value="12">December</option>
						<option value="*">Show all Months</option>
					</select>
					-->



					<span class="filterby">Search Presentations >></span> 
					<form id="presentationSearch" class="eventsSearch">
						<input type="text" id="psearch" placeholder="search" />
						<input type="submit" id="psubmit" value="Search" />
					</form>

					<div>
												<p><span style="border:none;" class="lock-icon"></span>A padlock symbol denotes presentations available only to members of America’s Essential Hospitals. If you are a member and would like to access these presentations, please sign in to the website to unlock these presentations and additional members-only content.


						</p>
						<p>Don't see a presentatasdfion? Contact <a href="mailto:amallory@essentialhospitals.org">amallory@essentialhospitals.org</a></p>
					</div>
				</div>
			</div>

			<div id="contentSecondary"  style="width:830px;">
			 
				<div class="gutter clearfix">
						<div id="postBox" class="clearfix">
								<div id="fader" class="clearfix scrollable events">
									<div id="loader-gif"> Loading more presentations</div>
									<div class="items">

										<div class="item">
											 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
		 	$presentation = get_the_ID();
		 	endwhile; endif;
		 

												$post = get_post($presentation);
		$title = $post->post_title;
		$link = get_post_meta($presentation,'file',true);
		$intro = get_post_meta($presentation,'description',true);
		$speaker = get_post_meta($presentation,'speaker',true);
		$event = get_post_meta($presentation,'event',true);
		$post_status = get_post_status($presentation);

		$section = get_post_meta($event,'section',true);
		$date = get_post_meta($event,'date',true);
		$audience = get_post_meta($event,'audience',true);
		$pres_date = get_post_meta($presentation,'date',true);
		$date = date( 'F j, Y', $date );
		$pres_date = date( 'F j, Y | h:i a T', $pres_date );



		$member_access = false;

		if(is_user_logged_in()){
			 
			$uid = get_current_user_id(); 
			$member = get_user_meta($uid,'aeh_member_type',true);
			if($member == 'hospital'){
				$member_access = true;
			}
			else{
				$member_access = false;
			}
			

		}



		if($audience != NULL || $audience != ''){
			$audience = get_term_by('ID', $audience, 'audience')->name;
		}

		if($post_status == 'private'){
			$lockicon = "<span style='vertical-align:middle;border:none;' class='lock-icon'>
							<img src='".get_template_directory_uri()."/images/lockwebinar.png'>
						</span>";
		}else{
			$lockicon = '';
		}



		
		if($section == 'action'){
			$theme = 'redd';
		}elseif($section == 'quality'){
			$theme = 'greenn';
		}elseif($section == 'institute'){
			$theme = 'bluee';
		}else{
			$theme = 'grayy';
		}
		if($section == ''){
			$section = 'education';
		}
		$event_title = get_the_title( $event ); 

 




		$output = '<div class="post long columns '.$theme.' '.$post->post_type.' wide" style="position: absolute; left: 5px; top: 5px;">
									<div class="graybarright"></div>
									<div class="item-bar">
									<div class="item-icon" style="padding-top: 15px;">
										<img src="http://mlinson.staging.wpengine.com/wp-content/themes/EssentialHospitals/images/icon-'.$section.'.png">
									</div>
									<div class="item-content">
										<div class="item-header">
											';
											if($member_access == false && $lockicon != ''){
												$output.='<h2>'.$lockicon.' '.$title .'</h2>';
											}
											else{
												$output.='<h2><a class="'.$theme.'" target="_blank" href="'.wp_get_attachment_url($link).'">'.$lockicon.' '.$title .'</a></h2>';
												
											}
											
											

								$output .=	'<span class="item-date">'.$pres_date.'</span><br>
											<span class="'.$theme.'"><a href="'. get_permalink($event) .'">'.$event_title.'</a></span>
										</div>
										'.$intro.'
									</div>
									<div class="item-tags">';
				    				$tags = get_the_terms($presentation,'post_tag');
					    					if($tags){
					    						$cnt = 0;
					    						$tag_output = '';
					    						foreach($tags as $tag)
					    						{
						    						$tagLink = get_term_link($tag->term_id,'post_tag');
						    						$tagSlug = $tag->slug;
						    						$tagSlug = str_replace('-',' ', $tagSlug);
						    						 

							    					$tag_output .= "<a href='".$tagLink."'>".$tagSlug."</a>, ";
							    					$cnt++;
							    				}
						    				} 



						    				$tag_output = rtrim($tag_output,', ');
 
						    				$output .= $tag_output;

				    			$output .= '</div>
									<div class="bot-border"></div>
								</div>
							</div>';
		echo $output; ?>

										</div>
									</div>
								</div>
						</div>
				</div>
			</div>


		</div>
	</div>
</div>

<?php get_footer(); ?>
