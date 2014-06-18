<?php if (!is_search()) : ?>
  <div class="alert alert-warning">
    <?php _e('Type a search term above or choose a category below.', 'roots'); ?>
  </div>
  
  <?php
	$top_categories = get_terms( SF_Taxonomies::JOB_TYPE_TAXONOMY, array(
		'orderby'    => 'name',
		'hide_empty' => 0,
		'parent' => 0
	 ) );
	 
	 $search_keyword = ($_GET['s']) ? $_GET['s'] : ' ';
	 $search_link = add_query_arg( array('s' => $search_keyword ), site_url()); 
	 
	 ?>
  
   <div class="search-by-category">
        <h3>Search by Category</h3>
        <div class="row">
        	<?php
			 if ( $top_categories && !is_wp_error($top_categories) ) {
		 		foreach ( $top_categories as $top_c ) {
					?>
              <ul class="col-md-3">
                <li class="main-cat"><a href="<?php echo add_query_arg( array('category' => $top_c->slug), $search_link); ?>"><?php echo $top_c->name; ?></a></li>
                <?php
					$sub_categories = get_terms( SF_Taxonomies::JOB_TYPE_TAXONOMY, array(
						'orderby'    => 'name',
						'hide_empty' => 0,
						'parent' => $top_c->term_id
					 ) );
					 if ( $sub_categories && !is_wp_error($sub_categories) ) {
						 ?>
                        
						 <?php
		 			 	foreach ( $sub_categories as $sub_c ) {
							?>
                			<li><a href="<?php echo add_query_arg( array('category' => $sub_c->slug), $search_link); ?>"><?php echo substr($sub_c->name, strpos($sub_c->name, ':') + 2); ?></a>
                            <?php
							$sub_categories_1 = get_terms( SF_Taxonomies::JOB_TYPE_TAXONOMY, array(
								'orderby'    => 'name',
								'hide_empty' => 0,
								'parent' => $sub_c->term_id
							 ) );
							if ( $sub_categories_1 && !is_wp_error($sub_categories_1) ) {
								?>
                               <ul>
                               <?php
		 			 			foreach ( $sub_categories_1 as $sub_c_1 ) {
									?>
                                    <li><a href="<?php echo add_query_arg( array('category' => $sub_c_1->slug), $search_link); ?>"><?php echo substr($sub_c_1->name, strpos($sub_c_1->name, ':') + 2); ?></a>
                                   <?php
									$sub_categories_2 = get_terms( SF_Taxonomies::JOB_TYPE_TAXONOMY, array(
										'orderby'    => 'name',
										'hide_empty' => 0,
										'parent' => $sub_c_1->term_id
									 ) );
									 if ( $sub_categories_2 && !is_wp_error($sub_categories_2) ) {
										?>
									   <ul>
									   <?php
										foreach ( $sub_categories_2 as $sub_c_2 ) {
											?>
											<li><a href="<?php echo add_query_arg( array('category' => $sub_c_2->slug), $search_link); ?>"><?php echo substr($sub_c_2->name, strpos($sub_c_2->name, ':') + 2); ?></a></li>
											<?php
										} 
										?>
                                       </ul>
                                	<?php
									 }
									 ?>
        
                               <?php
                               }
							   ?>
                              </ul> 
                              <?php
                           }
						}
						?>
                       
                       <?php 
					 }
					 ?>
                </ul>    
                 <?php
				} //end foreach top_categories
			 }
			 ?>
        </div>
    </div>

    <hr>

  
  
<?php endif; ?>

<?php if ( is_search()) : ?>

	<?php if (!have_posts()) : ?>
      <div class="alert alert-warning">
        <?php _e('Sorry, no results were found.', 'roots'); ?>
      </div>
    <?php endif; ?>
    
    <?php while (have_posts()) : the_post(); ?>
    
    <?php 
    $post_type = get_post_type(); 
    $post_type_class = $post_type;
    $author_link = get_the_author_link();
	$categories_permitted = 1;
    if ( $post_type == SF_Project::POST_TYPE ) {
        $post_type_class = 'job-post';
        $facility_id = SF_Project::get_facility_id_for_project(get_the_ID());
		$categories_permitted = fv_get_contractor_membership_addon_categories($facility_id); 
		if ( $facility_id ) {
			$author_link = '<span><strong>'.get_the_title($facility_id).'</strong></span>';
		} else {
			$author_link = '<span><strong>Unknown</strong></a>';
		}
       	
    } elseif ( $post_type == SF_Contractor::POST_TYPE ) {
        $post_type_class = 'contractor-post';
		$author_name = get_post_meta(get_the_ID(), '_name', true);
		$author_link = '<a href="'.get_permalink(get_the_ID()).'" rel="author" class="fn">'.get_the_title(get_the_ID()).'</a>';
		$categories_permitted = fv_get_contractor_membership_addon_categories(get_the_ID()); 
		
    } elseif ( $post_type == SF_Facility::POST_TYPE ) {
        $post_type_class = 'facility-post';
		$author_name = get_post_meta(get_the_ID(), '_name', true);
		$author_link = '<span><strong>'.get_the_title(get_the_ID()).'</strong></a>';
		$categories_permitted = fv_get_facility_membership_addon_categories(get_the_ID()); 
    }
    ?>
    <article class="content-item <?php echo $post_type_class; ?>">
        <time><?php echo get_the_date('m/d/Y', get_the_ID() ); ?></time>
        <h3 class="content-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="content-summary"><?php echo get_the_excerpt(); ?></p>
      <div class="content-meta">
        <div class="tags">
            <?php
            $types = wp_get_object_terms( get_the_ID(), SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
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
            $location = get_post_meta( get_the_ID(), '_location', true); 
            if ( $location ) {
            ?>
            <span class="label label-primary label-location"><?php echo $location; ?></span>
            <?php 
            }
            ?>
      </div>
        <p class="author">posted by <?php echo $author_link; ?></p>
      </div>
    </article>
      
    <?php endwhile; ?>
    
    <hr />
    
    <?php if ($wp_query->max_num_pages > 1) : ?>
      	<?php wp_pagination(); ?>
    <?php endif; ?>
	
<?php endif; //End if is search ?>