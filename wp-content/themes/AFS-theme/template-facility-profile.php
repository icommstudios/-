<?php
/*
Template Name: Facility Profile
*/
?>

<div class="row">
  <div class="col-lg-9">
<?php get_template_part('templates/facility/content', 'facility-profile'); ?>
<?php get_template_part('templates/facility/content', 'facility-messages'); ?>
<?php get_template_part('templates/facility/content', 'facility-jobs'); ?>
<?php get_template_part('templates/facility/content', 'facility-membership'); ?>
<?php get_template_part('templates/facility/content', 'facility-endorsements'); ?>
<?php get_template_part('templates/facility/content', 'facility-medialibrary'); ?>
  </div>
  <div class="col-lg-3">
  	<?php get_template_part('templates/facility/content', 'facility-sidebar'); ?>
  </div>
</div>