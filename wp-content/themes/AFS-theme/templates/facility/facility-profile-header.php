<?php global $facility_id; 
$categories_permitted = fv_get_facility_membership_addon_categories($facility_id); 
//Message count
if ( $facility_id ) {
	$message_count = SF_Message::get_message_ids_sent_to($facility_id);
	$project_count = SF_Project::get_project_ids_for_facility($facility_id);
	//var_dump($project_count);
}
?>
<div class="hero purple-hero">
	<div class="container">
		<h1><?php echo get_the_title($facility_id); ?></h1><small><i>posted by <?php echo get_post_meta($facility_id, '_name', true); ?></i></small>
		<div class="posting-tags">
			<?php
            $types = wp_get_object_terms( $facility_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
            if ( $types ) {
                $cat_count = 0;
                foreach ($types as $type) {
                    //$link = get_term_link( $type, SF_Taxonomies::JOB_TYPE_TAXONOMY );
					$cat_count++;
					if ( $cat_count <= $categories_permitted ) {
                    ?>
                    <span class="label label-primary"><?php echo $type->name; ?></span>
                    <?php
					}
                }
            }
            //If location
            $location = get_post_meta($facility_id, '_location', true); 
            if ( $location ) {
            ?>
            <span class="label label-primary label-location"><?php echo $location; ?></span>
            <?php 
            }
            ?>
		</div>
	</div>
</div>
<div class="profile-nav">
	<nav class="container" role="navigation">
		<ul class="nav nav-pills">
			<li><a href="<?php echo add_query_arg(array('action' => 'profile'), get_permalink()); ?>">edit profile</a></li>
			<li><a href="<?php echo add_query_arg(array('action' => 'membership'), get_permalink()); ?>">membership</a></li>
			<li><a href="<?php echo add_query_arg(array('action' => 'jobs'), get_permalink()); ?>"><?php if ( $project_count ) : ?><span class="counter-bubble"><?php echo count($project_count); ?><?php endif; ?></span>post a job</a></li>
			<li><a href="<?php echo add_query_arg(array('action' => 'endorsements'), get_permalink()); ?>">endorsements/ratings</a></li>
			<li><a href="<?php echo add_query_arg(array('action' => 'messages'), get_permalink()); ?>"><?php if ( $message_count ) : ?><span class="counter-bubble"><?php echo count($message_count); ?><?php endif; ?></span>messages</a></li>
			<!-- <li><a href="<?php echo add_query_arg(array('action' => 'medialibrary'), get_permalink()); ?>">media library</a></li> -->
			<?php if ( empty($_GET['action']) || $_GET['action'] == 'profile' ) : ?><li class="col-lg-3 save-btn-li"><a class="save-btn" href="#">save</a></li><?php endif; ?>
		</ul>
	</nav>
</div>