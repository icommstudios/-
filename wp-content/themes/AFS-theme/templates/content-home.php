<div class="img-hero img-hero-bar">
	<h1>Welcome to Alliance Facility Solutions, where we make it easy for facility owners and managers to find qualified, licensed contractors.</h1>
</div>
<div class="container-overflow">
<?php while (have_posts()) : the_post(); ?>
  <?php the_content(); ?>
  <?php wp_link_pages(array('before' => '<nav class="pagination">', 'after' => '</nav>')); ?>
<?php endwhile; ?>
</div>