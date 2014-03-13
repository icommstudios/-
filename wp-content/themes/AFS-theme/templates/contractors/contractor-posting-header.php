<?php 
$contractor_id = get_the_ID();
$categories_permitted = fv_get_contractor_membership_addon_categories($contractor_id); 
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