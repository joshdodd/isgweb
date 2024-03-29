<?php get_header();
while ( have_posts() ) : the_post();
$speakerIMG = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>
<div id="featured-img" class="education" style="background-image:url(<?php echo $speakerIMG; ?>);">
	<div class="container">
		<div id="featured-intro">
				<h3><?php the_field('bannerTitle'); ?></h3>
				<h4><?php the_field('bannerDescription'); ?></h4>
			<?php endwhile; // end of the loop. ?>
		</div>
	</div>
</div>

<div id="contentWrap" class="education landing ehu super-special-page">
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

				<a href="<?php echo site_url('/feed/?post_type=webinar'); ?>" target="_blank">
					<div id="rssFeedIcon" class="education">
						Subscribe
					</div>
				</a>

			</div>

			<div id="contentPrimary">
				<div class="graybar"></div>
				<div class="graybarX"></div>
					<div class="gutter clearfix">
						<div id="institutePostBox" class="eduoverride">

						<div class="stamp first">
							<?php if(!is_user_logged_in()){ ?>
							<div class="panel signin">
								<div class="gutter">


									<h2>Dashboard Sign-In</h2>
									
									<?php get_template_part('partial/login','smallform'); ?>
 

								</div>
							</div>
							<?php }else{
								$currentUser = get_current_user_id();
								$user_info = get_userdata($currentUser);
								$user_avatar = get_avatar($currentUser);
							?>


							<div class="panel signedin">

								<?php include(locate_template('/membernetwork/module-dashProfile.php')); ?>
								<!--
								<div id="userProfile">
									<div class="gutter clearfix">
										<div id="userAvatar" class="clearfix">
											<div class="group-memberavatar">
												<span class="group-membername"><?php echo $user_info->user_firstname.' ' .$user_info->user_lastname; ?></span>
												<a href="<?php echo get_permalink(274); ?>"><?php echo $user_avatar; ?></a>
											</div>
										</div>
										<div id="userInfo">
											<span class="uinfo fName"><?php echo $user_info->user_firstname; ?></span>
											<span class="uinfo jobTitle"><?php echo $user_info->job_title; ?></span>
											<span class="uinfo org"><?php echo $user_info->employer; ?></span>

											<span class="uinfo email"><a href="mailto:<?php echo $user_info->user_email; ?>"><?php echo $user_info->user_email; ?></a></span>
										</div>
										<div id="userMeta">
											<ul class="social">
												<?php if ($user_info->linkedin) { ?>
													<li class="linkedin"><a href="<?php echo $user_info->linkedin; ?>">linkedin</a></li>
												<?php } ?>
												<?php if ($user_info->twitter) { ?>
													<li class="twitter"><a href="http://www.twitter.com/<?php echo $user_info->twitter; ?>">@<?php echo $user_info->twitter; ?></a></li>
												<?php } ?>
												<?php if ($user_info->facebook) { ?>
													<li class="facebook"><a href="<?php echo $user_info->facebook; ?>">facebook</a></li>
												<?php } ?>
												<li class="mail"><a href="mailto:<?php echo $user_info->user_email; ?>">mail</a></li>
											</ul>
											<span class="edit"><a href="<?php echo get_permalink(274); ?>?a=edit">[edit profile]</a></span>
										</div>
									</div>
								</div>
								-->
							</div>
							<?php } ?>

							<div class="panel ask">
							<div class="gutter clearfix">
								<h2>Ask a Question</h2>
								<p>Contact our team with questions or suggestions regarding education and training opportunities.</p>
								<?php echo do_shortcode('[formidable id=6]'); ?>
							</div>
						</div>
						</div>

						<div class="stamp pad">
							<!--  Upcoming Events Box -->
							<div class="panel grey">
								<div class="item-icon grayy">Upcoming Events
									<img src="<?php bloginfo('template_directory'); ?>/images/icon-education.png" />
								</div>
								<?php
								$today = mktime(0, 0, 0, date('n'), date('j'));
								$args = array(
									'post_type' => 'events',
									'posts_per_page' => 3,
									'order' => 'asc',
									'post_status' => 'publish',
									'meta_query'  => array(
										array(
											'key' => 'date',
											'value' => $today,
											'compare' => '>=' 
										)
									),
									'orderby' => 'meta_value',
									'meta_key' => 'date',
								);
								$query = new WP_Query($args);

								if ( $query->have_posts() ) { while ( $query->have_posts() ) { 
									$query->the_post();
									$postType = get_field('section');
									$date = get_post_meta( get_the_ID(), 'date', 'true');
									//check post type and apply a color
									if($postType =='policy'){
										$postColor = 'redd';
									}else if($postType =='quality'){
										$postColor = 'greenn';
									}else if($postType =='education'){
										$postColor = 'grayy';
									}else if($postType =='institute'){
										$postColor = 'bluee';
									}else{
										$postColor = 'redd';
									} ?>
									<div class="entry webinar">
										<div class="gutter clearfix">
											<div class="entry-content">
												<p>
													<div class="title <?php echo $postColor; ?>">
														<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
													</div> 
													<span class="date"><?php echo date('M j, Y', $date);?>
													</span> | 
													<span class="excerpt"><?php $exc = get_the_excerpt(); echo substr($exc, 0, 50); ?>
													</span>
												</p>
											</div>
										</div>
									</div>
								<?php }
								echo '<a class="readmore" href="'.get_post_type_archive_link('events').'/?timeFilter=future">All Upcoming Events &raquo;</a>';
								} ?>
							</div>
							<!--  END Events Box -->
	

							<!--  Upcoming Webinars Box -->
						 
							<div class="panel grey">
								<div class="item-icon grayy">Upcoming Webinars
									<img src="<?php bloginfo('template_directory'); ?>/images/icon-education.png" />
								</div>
								<?php
									$today = mktime(0, 0, 0, date('n'), date('j'));
									$args = array(
										'post_type' => 'webinar',
										'posts_per_page' => 4,
										'order' => 'asc',
										'post_status' => 'all',
										'meta_query'  => array(
											array(
												'key' => 'webinar_date',
												'value' => $today, 
												'compare' => '>=' 
											)
										),
										'orderby' => 'meta_value',
										'meta_key' => 'webinar_date',
									);
								$query = new WP_Query($args);
								if ( $query->have_posts() ) { while ( $query->have_posts() ) { $query->the_post();
								$postType = get_the_terms($post, 'webinartopics');
								$typeArr = array();
								if($postType){
									foreach($postType as $type){
										array_push($typeArr, $type->slug);
									}
								}
								//check post type and apply a color
								if(in_array('policy', $typeArr)){
									$postColor = 'redd';
								}else if(in_array('quality', $typeArr)){
									$postColor = 'greenn';
								}else if(in_array('education', $typeArr)){
									$postColor = 'grayy';
								}else if(in_array('institute', $typeArr)){
									$postColor = 'bluee';
								}else{
									$postColor = 'grayy';
								} ?>
									<div class="entry webinar">
										<div class="gutter clearfix">
											<div class="entry-content">
												<p>
													<div class="title <?php echo $postColor; ?>">
														<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
													</div>  
													<span class="date"><?php echo date('M j, Y', get_field('webinar_date')); ?>
													</span> |
													<span class="excerpt"><?php $exc = get_the_excerpt(); echo substr($exc, 0, 50); ?>
													</span>
												</p>
											</div>
										</div>
									</div>
								<?php }
								echo '<a class="readmore" href="'.get_post_type_archive_link('webinar').'/?timeFilter=future">All Upcoming Webinars &raquo;</a>';
								} ?>
							</div>
							<!--  END Upcoming Webinars Box -->
							

						</div>

						<?php while ( have_posts() ) : the_post(); ?>
						<div class="stamp post grayy education about wide long columns">
							<div class="graybarright"></div>
							<div class="item-bar"></div>
							<div class="item-icon grayy">About Essential Hospitals Education
								<img src="<?php bloginfo('template_directory'); ?>/images/icon-education.png" />
							</div>
							<div class="item-content">
								<?php the_content(); ?>
							</div>
							<div class="bot-border"></div>
						</div>
						<?php endwhile; ?>

						<div class="panel post short archweb">
							<div class="graybarright"></div>
							<div class="item-bar"></div>
							<div class="item-icon grayy">Archived Webinars
								<img src="<?php bloginfo('template_directory'); ?>/images/icon-education.png" />
							</div>
							<?php $today = mktime(0, 0, 0, date('n'), date('j'));
							$args = array(
								'post_type' => 'webinar',
								'posts_per_page' => 3,
								'order' => 'desc',
								'post_status' => 'all',
								'meta_query'  => array(
									array(
										'key' => 'webinar_date',
										'value' => $today,
										'compare' => '<='
									)
								),
								'orderby' => 'meta_value',
								'meta_key' => 'webinar_date',
							);
							$query = new WP_Query($args);
							if ( $query->have_posts() ) { while ( $query->have_posts() ) { $query->the_post();
							$postType = get_the_terms($post, 'webinartopics');
							$typeArr = array();
							if($postType){
								foreach($postType as $type){
									array_push($typeArr, $type->slug);
								}
							}
							//check post type and apply a color
							if(in_array('policy', $typeArr)){
								$postColor = 'redd';
							}else if(in_array('quality', $typeArr)){
								$postColor = 'greenn';
							}else if(in_array('education', $typeArr)){
								$postColor = 'grayy';
							}else if(in_array('institute', $typeArr)){
								$postColor = 'bluee';
							}else{
								$postColor = 'bluee';
							} ?>
								<div class="entry webinar">
									<div class="gutter clearfix">
										<div class="entry-content">
											<p><div class="title <?php echo $postColor; ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div><span class="excerpt"><?php $exc = get_the_excerpt(); echo substr($exc, 0, 50); ?></span></p>
										</div>
									</div>
								</div>
							<?php }
							echo '<a class="readmore" href="'.get_post_type_archive_link('webinar').'/?timeFilter=publish">All Recorded Webinars &raquo;</a>';
							} ?>
						<div class="bot-border"></div>
						</div>


						<?php
							$args = array(
								'post_type' => 'alert',
								'posts_per_page'=> 6,
								'orderby'   => 'date',
								'order'     => 'asc',
								'tax_query' => array(
									'relation' => 'AND',
									array(
										'taxonomy' => 'category',
										'field'    => 'slug',
										'terms'    => array('announcements'),
										'operator' => 'IN'
									),
									array(
										'taxonomy' => 'category',
										'field'    => 'slug',
										'terms'    => array( 'education' ),
										'operator' => 'IN'
									),
									array(
										'taxonomy' => 'category',
										'field'    => 'slug',
										'terms'    => array( 'updates' ),
										'operator' => 'NOT IN'
									)
								)
							);
							$query = new WP_Query($args);
							if ( $query->have_posts() ) { while ( $query->have_posts() ) { $query->the_post(); ?>
								<div class="panel announcement education-alert grey post fluid">
									<div class="item-icon grayy">Updates
										<img src="<?php bloginfo('template_directory'); ?>/images/icon-education.png" />
									</div>
									<div class="gutter">
										<h2><a href="<?php the_field('link'); ?>"><?php the_field('heading'); ?></a></h2>
										<a href="<?php the_field('link'); ?>"><?php the_field('label'); ?> &raquo;</a>
									</div>
									<div class="bot-border"></div>
								</div>
						<?php } } wp_reset_query(); ?>



						<?php
						$layoutArray = array('tall','short');
						$args = array(
							'post_type' => array('policy','institute','quality'),
							'meta_query' => array(
								'relation' => 'OR',
								array(
									'key' => 'sticky_topic',
									'value' => 'ehu',
									'compare' => 'LIKE'
								),
								array(
									'key' => 'sticky_topic',
									'value' => 'education',
									'compare' => 'LIKE'
								)
							)
						);
						$query = new WP_Query($args);
						if ( $query->have_posts() ) { while ( $query->have_posts() ) { $query->the_post();
							$rand_key = array_rand($layoutArray, 1);
							$postType = get_post_type( get_the_ID() );

							//check post type and apply a color
							if($postType == 'policy'){
								$postColor = 'redd';
							}else if($postType == 'quality'){
								$postColor = 'greenn';
							}else if($postType == 'education'){
								$postColor = 'grayy';
							}else if($postType == 'institute'){
								$postColor = 'bluee';
							}else{
								$postColor = 'bluee';
							}
						?>
								<div class="post long columns <?php echo $postColor; ?> <?php echo get_post_type( get_the_ID() ); ?> <?php echo $layoutArray[$rand_key]; ?>">
								<div class="graybarright"></div>
					  			<div class="item-bar"></div>
				    			<div class="item-icon">
				    				<?php $terms = wp_get_post_terms(get_the_ID(), 'series');
				    					if($terms){
					    					$termLink = get_term_link($terms[0], 'series');
						    				echo "<a href='".$termLink."'>".$terms[0]->name."</a>";
					    				}
				    				?>
				    				<img src="<?php bloginfo('template_directory'); ?>/images/icon-education.png" />
				    				<img src="<?php bloginfo('template_directory'); ?>/images/icon-<?php echo get_post_type( get_the_ID() ); ?>.png" />
				    			</div>
				    			<div class="item-content">
					    			<div class="item-header">
					    				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					    				<span class="item-date"><?php the_time('M j, Y'); ?> ||</span>
					    				<span class="item-author"><?php the_author(); ?></span>
					    			</div>
					    			<p><?php $exc = get_the_excerpt(); echo $exc; ?><a class="more" href="<?php the_permalink(); ?>"> view more » </a>
					    			</p>
					    			<div class="item-tags">
					    				<?php the_tags(' ',' ',' '); ?>
					    			</div>
					    		</div>
					    		<div class="bot-border"></div>
					  		</div>

						<?php } } ?>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>