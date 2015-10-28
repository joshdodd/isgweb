<div style="display:none;">
	<?php global $wp_query; global $query_string;
 ?>
</div>
<div id="postBox" class="clearfix stream-type">

	<div id="fader" class="clearfix scrollable">
	<div id="loader-gif"> Loading more articles</div>
			<div class="items">
			<?php
 
			query_posts( $query_string . '&post_type=any' );
			if ( have_posts() ) while ( have_posts() ) : the_post();
				$postType = get_post_type( get_the_ID() );
				$presType = false;

				if($postType == 'presentation'){
					$presType = true;
					$event = get_post_meta(get_the_ID(),'event',true);
					$file_link = get_post_meta(get_the_ID(),'file',true);
					$postType = get_post_meta($event,'section',true);
					
				}

				if($postType == 'general'){
					$postType = $term_meta['section'];
				}
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
					$postColor = 'redd';
					$seriesType = "general";
				}
			?>

			<div class="post long columns <?php echo $postColor; ?>  <?php echo $postType; ?> ">
				<div class="graybarright"></div>
	  			<div class="item-bar"></div>
    			<div class="item-icon">
    				<?php $terms = wp_get_post_terms(get_the_ID(), 'series');
    					if($terms){
	    					$termLink = get_term_link($terms[0], 'series');
		    				echo "<a href='".$termLink."'>".$terms[0]->name."</a>";
	    				}
    				?>
					<?php if($postType != 'post'){ ?>
	    				<img src="<?php bloginfo('template_directory'); ?>/images/icon-<?php echo $postType; ?>.png" />
    				<?php } ?>
    			</div>
    			<div class="item-content">
	    			<div class="item-header">
	    				<h2><a href="<?php if(get_field('link_to_media')){the_field('uploaded_file');}elseif($presType){echo wp_get_attachment_url($file_link);}else{the_permalink();} ?>"><?php the_title(); ?></a></h2>
	    				<span class="item-date"><?php the_time('M j, Y'); ?> ||</span>
	    				<?php if(!$presType){ ?><span class="item-author"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>/?prof=article"><?php the_author(); ?></a></span> <?php  }?>
	    			</div>
	    			<?php if(get_field('link_to_media')){ ?>
						<a href="<?php the_field('uploaded_file'); ?>"><img src="<?php bloginfo('template_directory'); ?>/images/<?php echo $postType; ?>-doc.png" /></a>
					<?php }else{ ?>
						<p><?php


						$exc = get_the_excerpt();
						$line=$exc;
						if (preg_match('/^.{1,100}\b/s', $exc, $match))
						{
						    $line=$match[0];
						}

						 $long_exc = get_field('long_excerpt');

						 if($long_exc == '')
						 	echo $exc;
						 else
						 	echo $long_exc;



						 ?>

					</p><a class="more" href="<?php if($presType){echo wp_get_attachment_url($file_link);}else{the_permalink();}  ?>"> view more » </a>
					<?php } ?>
	    			<div class="item-tags">
	    				<?php $tags = get_the_terms(get_the_ID(),'post_tag');
			    					if($tags){
			    						$cnt = 0;
			    						foreach($tags as $tag)
			    						{
				    						$tagLink = get_term_link($tag->term_id,'post_tag');
				    						$tagSlug = $tag->slug;
				    						$tagSlug = str_replace('-',' ', $tagSlug);
				    						if ($cnt != 0) echo ", ";
					    					echo "<a href='".$tagLink."'>".$tagSlug."</a>";
					    					$cnt++;
					    				}
				    				}?>
	    			</div>
	    		</div>
	    		<div class="bot-border"></div>
	  		</div>


		<?php endwhile; wp_reset_query();?>
			</div>
	</div>
</div>