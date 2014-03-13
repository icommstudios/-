<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<?php get_template_part('templates/contractors/content', 'contractor-posting'); ?> 
<?php endwhile; ?>