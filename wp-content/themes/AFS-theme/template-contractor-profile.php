<?php
/*
Template Name: Contractor Profile
*/
?>
<div class="row">
  <div class="col-lg-9">
<?php if ( !isset($_GET['action']) || $_GET['action'] == 'profile') get_template_part('templates/contractors/content', 'contractor-profile'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'messages') get_template_part('templates/contractors/content', 'contractor-messages'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'jobs') get_template_part('templates/contractors/content', 'contractor-jobs'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'membership') get_template_part('templates/contractors/content', 'contractor-membership'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'endorsements') get_template_part('templates/contractors/content', 'contractor-endorsements'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'medialibrary') get_template_part('templates/contractors/content', 'contractor-medialibrary'); ?>
  </div>
  <div class="col-lg-3">
  	<?php get_template_part('templates/contractors/content', 'contractor-sidebar'); ?>
  </div>
</div>