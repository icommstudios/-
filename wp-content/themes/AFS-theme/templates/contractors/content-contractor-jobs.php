<?php global $contractor_id, $proposal_fields; 
//Contractor sends proposals
?>

<?php if ( isset($_GET['prop_edit']) ) : ?>

	<?php
	$proposal_project_id = $_GET['prop_p'];
	$proposal_project_facility_id = SF_Project::get_facility_id_for_project( $proposal_project_id );
	$categories_permitted = fv_get_facility_membership_addon_categories($proposal_project_facility_id);
	if ( $proposal_project_facility_id ) {
		$author_link = '<span><strong>'.get_the_title($proposal_project_facility_id).'</strong></span>';
	} else {
		$author_link = '<span><strong>Unknown</strong></a>';
	}
	$proposal_project_location = get_post_meta($proposal_project_id, '_location', true); 
	?>

	<?php if ($_GET['prop_edit'] == '' ) : ?>
    <h3>Proposal Submission Form</h3>
    <?php else : ?>
    <h3>Edit this Job Proposal</h3>
    <?php endif; ?>
    
    <h5>Job Details:</h5>
    
    <?php if ( $proposal_project_id ) : ?>
    <article class="content-item job-post">
        <time><?php echo get_the_time('m/d/Y', $proposal_project_id); ?></time>
        <h4 class="content-title"><a href="<?php echo get_permalink($proposal_project_id); ?>"><?php echo get_the_title($proposal_project_id); ?></a></h4>
        <p class="content-summary"><?php echo wp_trim_excerpt(get_post_field('post_content', $proposal_project_id)); ?></p>
      <div class="content-meta">
        <div class="tags">
          <?php
            $types = wp_get_object_terms( $proposal_project_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
            if ( $types ) {
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
            if ( $proposal_project_location ) {
            ?>
            <span class="label label-primary label-location"><?php echo $proposal_project_location; ?></span>
            <?php 
            }
            ?>
      </div>
      </div>
    </article>
    
    <?php endif; ?>
    
    <hr>
    
    <h5>Please submit your proposal details below and be as detailed as possible.</h5>
    
    <form id="fv_proposal_edit" role="form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="fv_proposal_edit" value="<?php echo $_GET['prop_edit']; ?>" />
        <input type="hidden" name="proposal_message_id" value="<?php echo $_GET['prop_m']; ?>" />
        <input type="hidden" name="proposal_project_id" value="<?php echo $_GET['prop_p']; ?>" />
        <?php wp_nonce_field( 'fv_proposal_edit_nonce', 'fv_proposal_edit_nonce' ); ?> 
    	
        <div class="form-group">
            <input placeholder="estimate in $" name="_estimate" value="<?php echo ($_POST['_estimate']) ? stripslashes($_POST['_estimate']) : $proposal_fields['estimate']; ?>" class="full-width">
        </div>
        <div class="form-group">
            <textarea placeholder="proposal description, please be detailed" name="post_content" class="full-width" rows="5"><?php echo ($_POST['post_content']) ? esc_textarea(stripslashes($_POST['post_content'])) : esc_textarea($proposal_fields['post_content']); ?></textarea>
        </div>
        
        <div class="form-group">
            <?php if ($_GET['prop_edit'] == '' ) : ?>
            <input type="submit" value="send proposal" name="send proposal" class="orange">
            <?php else : ?>
            <input type="submit" value="save proposal" name="save proposal" class="orange">
            <?php endif; ?>
        </div>
    </form>
    
<?php elseif ( isset($_GET['prop_delete']) ) : ?>

    <h3>Delete a Job Proposal</h3>
    
    <form id="fv_proposal_delete" role="form" method="post">
        <input type="hidden" name="fv_proposal_delete" value="<?php echo $_GET['prop_delete']; ?>" />
        <?php wp_nonce_field( 'fv_proposal_delete_nonce', 'fv_proposal_delete_nonce' ); ?> 
    
        <p>Are you sure you want to delete this Job Proposal and all of it's details? Warning: This cannot be undone.</p>
        
        <h4 class="content-title"><a href="#"><?php echo get_the_title($_GET['prop_delete']); ?></a></h4>
        
        <div class="form-group">
          <input type="submit" value="delete this proposal" name="delete this proposal" class="orange">
        </div>
    </form>

<?php elseif ( isset($_GET['prop_view']) ) : ?>

	<?php
	$proposal_contractor_id = SF_Proposal::get_contractor_id_for_proposal($_GET['prop_view']);
	$proposal_project_id = SF_Proposal::get_project_id_for_proposal($_GET['prop_view']);
	$proposal_project_facility_id = SF_Project::get_facility_id_for_project( $proposal_project_id );
	$categories_permitted = fv_get_facility_membership_addon_categories($proposal_project_facility_id);
	if ( $proposal_project_facility_id ) {
		$author_link = '<span><strong>'.get_the_title($proposal_project_facility_id).'</strong></span>';
	} else {
		$author_link = '<span><strong>Unknown</strong></a>';
	}
	$proposal_project_location = get_post_meta($proposal_project_id, '_location', true); 
	
	?>
    
    <h3>View a Job Proposal</h3>
    
    <h5>Job Details:</h5>
    
    <?php if ( $proposal_project_id ) : ?>
    <article class="content-item job-post">
        <time><?php echo get_the_time('m/d/Y', $proposal_project_id); ?></time>
        <h4 class="content-title"><a href="<?php echo get_permalink($proposal_project_id); ?>"><?php echo get_the_title($proposal_project_id); ?></a></h4>
        <p class="content-summary"><?php echo wp_trim_excerpt(get_post_field('post_content', $proposal_project_id)); ?></p>
      <div class="content-meta">
        <div class="tags">
          <?php
            $types = wp_get_object_terms( $proposal_project_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
            if ( $types ) {
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
            if ( $proposal_project_location ) {
            ?>
            <span class="label label-primary label-location"><?php echo $proposal_project_location; ?></span>
            <?php 
            }
            ?>
      </div>
      </div>
    </article>
    
    <?php endif; ?>
    
    <hr>
   
   	<h5>Proposal Details:</h5>
     
    <div class="form-group">
        Contractor: <?php echo get_the_title($proposal_contractor_id); ?>
    </div>
    <div class="form-group">
        Estimate: <?php echo get_post_meta($_GET['prop_view'], '_estimate', TRUE); ?>
    </div>
    <div class="form-group">
        <div>Proposal Description:</div>
        <?php echo get_post_field('post_content', $_GET['prop_view'], 'raw'); ?>
    </div>
 
<?php else: ?>

	<?php
    //Load Projects for this Contractor
    $projects = SF_Project::get_project_ids_for_contractor($contractor_id);
    ?>
    <h3>jobs</h3>
    <?php 
        if ( $projects ) : 
          foreach ( $projects as $project_id ) : 
            $facility_id = SF_Project::get_facility_id_for_project( $project_id );
            $categories_permitted = fv_get_facility_membership_addon_categories($facility_id);
            if ( $facility_id ) {
                $author_link = '<span><strong>'.get_the_title($facility_id).'</strong></span>';
            } else {
                $author_link = '<span><strong>Unknown</strong></a>';
            }
            $location = get_post_meta($project_id, '_location', true); 
          
			//get status
			$project_status = fv_get_project_status( $project_id );
			$project_proposal_id = get_post_meta($project_id, '_proposal_id', true); 
			?>
    <article class="content-item job-post">
        <time><?php echo get_the_time('m/d/Y', $project_id); ?></time>
        <h4 class="content-title"><a href="<?php echo get_permalink($project_id); ?>"><?php echo get_the_title($project_id); ?></a></h4>
        <p class="content-summary"><?php echo wp_trim_excerpt(get_post_field('post_content', $project_id)); ?></p>
      <div class="content-meta">
        <div class="tags">
          <?php
            $types = wp_get_object_terms( $project_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
            if ( $types ) {
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
            if ( $location ) {
            ?>
            <span class="label label-primary label-location"><?php echo $location; ?></span>
            <?php 
            }
            ?>
            
      </div>
       
        <?php 
		if ( $project_status == 'new' ) {
		?>
         <p class="author">posted by <?php echo '<span><strong>'.$author_link.'</strong></a>'; ?></p>
        <?php } else { ?>
        <p><a href="<?php echo add_query_arg(array('prop_view'=>$project_proposal_id), $_SERVER['REQUEST_URI']); ?>">view proposal</a> </p>
        <?php } ?>
      </div>
    </article>
    
    	<?php endforeach; ?>
    
    <?php else : ?>
    
    <p>You have no jobs.</p>
    
    <?php endif; //End if has projects
    ?>

<?php endif; ?>