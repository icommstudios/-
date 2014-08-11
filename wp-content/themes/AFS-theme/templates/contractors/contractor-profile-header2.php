<?php global $contractor_id; 
$categories_permitted = fv_get_contractor_membership_addon_categories($contractor_id); 
//Message count
if ( $contractor_id ) {
	$message_count = SF_Message::get_message_ids_sent_to($contractor_id);
	$project_count = SF_Project::get_project_ids_for_contractor($contractor_id);
}
?>
<div class="hero blue-hero">
	<div class="container">
		<h1><?php echo get_the_title($contractor_id); ?></h1><small><i>posted by <?php echo get_post_meta($contractor_id, '_name', true); ?></i></small><span class="rating-stars">
        <?php
		$star_rating = fv_get_contractor_star_rating($contractor_id);
		if ( $star_rating ) {
			$star_rating_ii = 0;
			while ( $star_rating_ii < $star_rating ) {
				$star_rating_ii++;
				?>
                <span class="fa fa-star white-star"></span>
                <?php
			}
		}
		?>
        </span>
		<div class="posting-tags">
			<?php
            $types = wp_get_object_terms( $contractor_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
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
            $location = get_post_meta($contractor_id, '_location', true); 
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
            <li><a href="<?php echo add_query_arg(array('action' => 'jobs'), get_permalink()); ?>"><?php if ( $project_count ) : ?><span class="counter-bubble"><?php echo count($project_count); ?><?php endif; ?></span>jobs</a></li>
			<li><a href="<?php echo add_query_arg(array('action' => 'endorsements'), get_permalink()); ?>">endorsements/ratings</a></li>
			<li><a href="<?php echo add_query_arg(array('action' => 'messages'), get_permalink()); ?>"><?php if ( $message_count ) : ?><span class="counter-bubble"><?php echo count($message_count); ?><?php endif; ?></span>messages</a></li>
			<li><a href="<?php echo add_query_arg(array('action' => 'medialibrary'), get_permalink()); ?>">media library</a></li>
			<?php if ( empty($_GET['action']) || $_GET['action'] == 'profile' ) : ?><li class="col-lg-3 save-btn-li"><a class="save-btn" href="#">save</a></li><?php endif; ?>
		</ul>
	</nav>
</div>