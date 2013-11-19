<?php
/*
Template Name: Contractor Profile
*/
?>
<div class="row">
  <div class="col-lg-9">
<?php get_template_part('templates/contractors/content', 'contractor-profile'); ?>
<?php get_template_part('templates/contractors/content', 'contractor-messages'); ?>
<?php get_template_part('templates/contractors/content', 'contractor-membership'); ?>
<?php get_template_part('templates/contractors/content', 'contractor-endorsements'); ?>
<?php get_template_part('templates/contractors/content', 'contractor-medialibrary'); ?>
  </div>
  <div class="col-lg-3">
  	<?php get_template_part('templates/contractors/content', 'contractor-sidebar'); ?>
  </div>
</div>