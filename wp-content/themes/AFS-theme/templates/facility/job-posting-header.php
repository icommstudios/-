<?php 
global $post; 
$facility_id = SF_Project::get_facility_id_for_project( $post->ID ); 
$categories_permitted = fv_get_facility_membership_addon_categories($facility_id);
?>
<div class="hero purple-hero">
	<div class="container">
		<h1><?php echo get_the_title($post->ID); ?></h1><small><i><?php 
			
			if ( $facility_id ) {
				echo 'posted by '.get_the_title($facility_id); 
			}
			?></i></small>
		<div class="posting-tags">
			<?php
            $types = wp_get_object_terms( $post->ID, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
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
            $location = get_post_meta($post->ID, '_location', true); 
            if ( $location ) {
            ?>
            <span class="label label-primary label-location"><?php echo $location; ?></span>
            <?php 
            }
            ?>
		</div>
	</div>
</div>