<?php get_header(); ?>

	<?php if($post->post_parent || wp_list_pages("title_li=&child_of=".$post->ID."&echo=0")):?>
	<div id="subnav" class="fix">
		<ul>
			<?php
			if($post->post_parent) $children = wp_list_pages("title_li=&child_of=".$post->post_parent."&echo=0");
			else 	$children = wp_list_pages("title_li=&child_of=".$post->ID."&echo=0&sort_column=menu_order");
			if ($children) { echo $children;} 
			?>
		</ul>
	</div><!-- /sub nav -->
	<?php endif;?>

  <div id="content">
    
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  
    <div class="post fix" id="post-<?php the_ID(); ?>">
        <h2 class="posttitle"><?php the_title(); ?></h2>
		
		<div class="entry">
		<?php the_content('<p>Continue reading &raquo;</p>'); ?>
		<?php link_pages('<p><strong>Pages:</strong> ', '</p>', 'number'); ?>
		<?php edit_post_link('Edit', '<p>', '</p>'); ?>
		</div><!--/entry -->
	
	</div><!--/post -->
	
		<?php endwhile; endif; ?>
</div></div><?php get_sidebar(); ?></div><div id="cred"><div class="designer fix"><a class="pagelines" href="http://www.pagelines.com/themes/" title="Pagelines Theme Designs"><a href="http://www.pagelines.com" alt="pagelines.com">Pagelines Design</a> +</div><div class="wordpress-link"><a href="http://wordpress.org" alt="Wordpress">Wordpress</a></div></div></div><?php get_footer(); ?>