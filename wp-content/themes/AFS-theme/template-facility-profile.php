<?php
/*
Template Name: Facility Profile
*/
?>

<div class="row">
  <div class="col-lg-9">
<?php if ( !isset($_GET['action']) || $_GET['action'] == 'profile') get_template_part('templates/facility/content', 'facility-profile'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'messages') get_template_part('templates/facility/content', 'facility-messages'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'jobs') get_template_part('templates/facility/content', 'facility-jobs'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'membership') get_template_part('templates/facility/content', 'facility-membership'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'endorsements') get_template_part('templates/facility/content', 'facility-endorsements'); ?>
<?php if ( isset($_GET['action']) && $_GET['action'] == 'medialibrary') get_template_part('templates/facility/content', 'facility-medialibrary'); ?>
  </div>
  <div class="col-lg-3">
  	<div class="alert alert-success">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<strong>sidebar content or notifications can go here...
</div>
  </div>
</div>