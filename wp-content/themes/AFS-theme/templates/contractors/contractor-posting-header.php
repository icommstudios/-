<?php 
$contractor_id = get_the_ID();
$categories_permitted = fv_get_contractor_membership_addon_categories($contractor_id);
$featured_thumb = get_the_post_thumbnail($contractor_id, array(200,200), array('class' => 'img-thumbnail'));
$fields = fv_get_contractor_fields($contractor_id);
?>
<div class="hero blue-hero contractor">
	<div class="container">
        <?php if ( $featured_thumb ) : ?> 
        <div class="featured-img logo">
            <?php echo $featured_thumb; ?>
        </div>
            <?php else: ?>
        <div class="featured-img logo">
        <img src="http://placehold.it/200x200">
        </div>
        <?php endif; ?>
        <div class="title-block">
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
        </div>

            <div class="business-basics">
                <div class="col-md-6" >
                    <ul class="fa-ul">
                        <?php if ( $fields['name'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['name']; ?></li><?php endif; ?>
                        <?php if ( $fields['company'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['company']; ?></li><?php endif; ?>
                        <?php if ( $fields['location'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['location']; ?></li><?php endif; ?>
                        <?php if ( $fields['phone'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['phone']; ?></li><?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-6" >
                    <ul class="fa-ul">
                        <?php if ( $fields['email'] ) : ?><li><i class="fa-li fa fa-square"></i><a href="mailto:<?php echo $fields['email']; ?>"><?php echo $fields['email']; ?></a></li><?php endif; ?>
                        <?php if ( $fields['hours'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['hours']; ?></li><?php endif; ?>
                        <?php if ( $fields['years_of_experience'] ) : ?><li><i class="fa-li fa fa-square"></i> Established in <?php echo $fields['years_of_experience']; ?></li><?php endif; ?>
                        <?php if ( !empty($membership_type) && $fields['website']) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['website']; ?></li><?php endif; ?>
                    </ul>
                </div>
            </div>

    </div>
</div>