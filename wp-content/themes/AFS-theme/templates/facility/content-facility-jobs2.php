<?php global $facility_id, $project_fields, $proposal_fields ?>
<?php
//determine membership status type
$membership_type = fv_get_facility_membership_type($facility_id);
$categories_permitted = fv_get_facility_membership_addon_categories($facility_id);
$is_membership_active = ( $membership_type ) ? true : false;
?>

<?php if ( isset($_GET['job_edit']) ) : ?>

	<?php if ($_GET['job_edit'] == '' ) : ?>
    <h3>Create a Job Posting</h3>
    <?php else : ?>
    <h3>Edit this Job Posting</h3>
    <?php endif; ?>
    
    <form id="fv_project_edit" role="form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="fv_project_edit" value="<?php echo $_GET['job_edit']; ?>" />
        <?php wp_nonce_field( 'fv_project_edit_nonce', 'fv_project_edit_nonce' ); ?> 
    
        <div class="form-group">
            <input placeholder="project name" name="post_title" value="<?php echo $project_fields['post_title']; ?>" class="full-width">
        </div>
        <div class="form-group">
            <input placeholder="location (city, state)" name="_location" value="<?php echo $project_fields['location']; ?>" class="full-width">
        </div>
        <div class="form-group">
            <input placeholder="location zipcode" name="_location_zip" value="<?php echo $project_fields['location_zip']; ?>" class="full-width">
        </div>
        <div class="form-group">
            <input placeholder="budget" name="_budget" value="<?php echo $project_fields['budget']; ?>" class="full-width">
        </div>
        <div class="form-group">
            <input placeholder="deadline" name="_deadline" value="<?php echo $project_fields['deadline']; ?>" id="deadline" class="full-width">
        </div>
        <div class="form-group">
            <textarea placeholder="project description" name="post_content" class="full-width" rows="5"><?php echo esc_textarea($project_fields['post_content']); ?></textarea>
        </div>
        
        <?php if ( !empty($_GET['job_edit']) ) : ?>
        <div class="form-group">
            <p><strong>Job Photos</strong></p>
            <?php
                //Loop photos
                $photo_attachments = SF_Project::load_attachments($_GET['job_edit']);
                if ( $photo_attachments ) {
				$featured_attachment_id = get_post_meta( $_GET['job_edit'], '_thumbnail_id', true);
                foreach ($photo_attachments as $attachment) : 
					$attachment_name_array = explode('/',wp_get_attachment_url($attachment->ID));
					$attachment_name = $attachment_name_array[sizeof($attachment_name_array) - 1];
					$attachment_name = ( $attachment_name ) ? $attachment_name : $attachment->post_title;
                ?>
                <div class="attachment_item">
                  
                 <div><a class="attachment_title" target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>"><?php echo $attachment_name; ?></a></div>
                  <input type="radio"  <?php echo ($featured_attachment_id == $attachment->ID) ? 'checked="checked"' : ''; ?> value="<?php echo $attachment->ID; ?>" id="set_as_featured_<?php echo $attachment->ID; ?>" name="set_as_featured" /> <label for="set_as_featured_<?php echo $attachment->ID; ?>"></label><span class="field-meta">set as main image &nbsp; &nbsp; &nbsp; </span> 
                 <div class="custom-checkbox">
                 	<input type="checkbox" value="<?php echo $attachment->ID; ?>" id="delete_attachment_<?php echo $attachment->ID; ?>" name="delete_attachment[]" /> <label for="delete_attachment_<?php echo $attachment->ID; ?>"></label><span class="field-meta">delete this file</span>
                 </div>
                 
               </div>
             
            <?php endforeach; 
        } ?>
        </div>
        <?php endif; ?>
            
    	
        <div class="form-group">
        	<p><strong>Upload Photos</strong></p>
            <input type="file" name="upload_file" size="40">
            <a id="add_more_uploads" href="#"><i class="fa fa-plus-circle"></i> add photos</a>
        </div>
        <div class="form-group">
            <p><strong>Type of Job?</strong> (you may select <?php echo $categories_permitted; ?> based on your membership)</p>
            <?php
               //taxonomy is type category (so use ids for field value) 
                $types = get_terms( array( SF_Taxonomies::JOB_TYPE_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
                foreach ( $types as $type ) : ?>
                <div class="custom-checkbox">
                      <input type="checkbox" value="<?php echo $type->term_id; ?>" id="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY.'_'.$type->term_id; ?>" name="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY; ?>[]" <?php echo (in_array($type->term_id, $project_fields[SF_Taxonomies::JOB_TYPE_TAXONOMY])) ? 'checked="checked"' : ''; ?> />
                      <label for="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY.'_'.$type->term_id; ?>"></label><span class="field-meta"><?php echo $type->name; ?></span>
                </div>
             <?php endforeach; ?>
        </div>
        <div class="form-group">
            <?php if ($_GET['job_edit'] == '' ) : ?>
            <input type="submit" value="create job" name="create job" class="orange">
            <?php else : ?>
            <input type="submit" value="save job" name="save job" class="orange">
            <?php endif; ?>
        </div>
    </form>
    
    
<!-- Handle forms -->
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
	
	//Add more uploads
	$("#add_more_uploads").on("click", function (e) {
		e.preventDefault();
		
		var uploads_div = $(this).closest( "div.form-group" );
		var cloneCount = uploads_div.children('input').size();
		
		var cloned = uploads_div.find('input[name=upload_file]').clone();
		cloned.attr('name', 'upload_file'+ cloneCount++); //set new input name
		cloned.val(null); //remove any selected file value
		$(cloned).insertAfter(this);
		
	});
	
	//Set form data for Replys
	$("a.do_file_delete").on("click", function () {
		 var attachment_id = $(this).data('attachmentid');
		 var attachment_div = $(this).closest( ".attachment_item" );
		 var attachment_title = article.find('.attachment_title').html();
		
		 //Set modal label
		 $("#contactModal #contactModalLabel").html( 'Reply to ' + reply_name );
		 //Set form fields
		 $("#contactModal input[name=to]").val( reply_to );
		 $("#contactModal input[name=reply_message_id]").val( reply_message_id );
		 $("#contactModal input[name=post_title]").val( 'RE: ' + reply_title );
		 $("#contactModal input[name=post_content]").val( '' ); //set blank
	});
	
});
</script>
    
<?php elseif ( isset($_GET['job_delete']) ) : ?>

    <h3>Delete a Job Posting</h3>
    
    <form id="fv_project_delete" role="form" method="post">
        <input type="hidden" name="fv_project_delete" value="<?php echo $_GET['job_delete']; ?>" />
        <?php wp_nonce_field( 'fv_project_delete_nonce', 'fv_project_delete_nonce' ); ?> 
    
        <p>Are you sure you want to delete this Job and all of it's details? Warning: This cannot be undone.</p>
        
        <h4 class="content-title"><a href="#"><?php echo get_the_title($_GET['job_delete']); ?></a></h4>
        
        <div class="form-group">
          <input type="submit" value="delete this job" name="delete this job" class="orange">
        </div>
    </form>
    
    
<?php elseif ( isset($_GET['prop_accept']) ) : ?>
   	
    <?php
	$proposal_contractor_id = SF_Proposal::get_contractor_id_for_proposal($_GET['prop_accept']);
	$proposal_project_id = SF_Proposal::get_project_id_for_proposal($_GET['prop_accept']);
	$proposal_project_facility_id = SF_Project::get_facility_id_for_project( $proposal_project_id );
	$categories_permitted = fv_get_facility_membership_addon_categories($proposal_project_facility_id);
	if ( $proposal_project_facility_id ) {
		$author_link = '<span><strong>'.get_the_title($proposal_project_facility_id).'</strong></span>';
	} else {
		$author_link = '<span><strong>Unknown</strong></a>';
	}
	$proposal_project_location = get_post_meta($proposal_project_id, '_location', true); 
	?>
    
   	<h3>Accept a Job Proposal</h3>
    
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
    
    <h5>Proposal Details:</h5>
    
    <form id="fv_proposal_accept" role="form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="fv_proposal_accept" value="<?php echo $_GET['prop_accept']; ?>" />
        <input type="hidden" name="proposal_message_id" value="<?php echo $_GET['prop_m']; ?>" />
        <input type="hidden" name="proposal_project_id" value="<?php echo $_GET['prop_p']; ?>" />
        <?php wp_nonce_field( 'fv_proposal_accept_nonce', 'fv_proposal_accept_nonce' ); ?> 
        
        <div class="form-group">
        	Contractor: <?php echo get_the_title($proposal_contractor_id); ?>
        </div>
        <div class="form-group">
        	Estimate: <?php echo $proposal_fields['estimate']; ?>
        </div>
        <div class="form-group">
        	<div>Proposal Description:</div>
            <?php echo esc_textarea($proposal_fields['post_content']); ?>
        </div>
        
        <div class="form-group">
          	<input type="submit" value="accept proposal" name="accept proposal" class="orange">
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
	$categories_permitted = fv_get_facility_membership_addon_categories($facility_id);
    //Load Projects for this Facility
    $projects = SF_Project::get_project_ids_for_facility($facility_id);
    ?>
    <h3>jobs</h3>
    <p><a href="<?php echo add_query_arg(array('job_edit'=>''), $_SERVER['REQUEST_URI']); ?>">Create Job</a></p>
    <?php 
        if ( $projects ) : 
          foreach ( $projects as $project_id ) : ?>
          	<?php
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
            $location = get_post_meta($project_id, '_location', true); 
            if ( $location ) {
            ?>
            <span class="label label-primary label-location"><?php echo $location; ?></span>
            <?php 
            }
            if ( $project_status ) {
            ?>
            <span class="label label-success"><?php echo $project_status; ?></span>
            <?php 
            }
			?>
            
      </div>
      	<?php 
		if ( $project_status == 'new' ) {
		?>
        <p><a href="<?php echo add_query_arg(array('job_edit'=>$project_id), $_SERVER['REQUEST_URI']); ?>">edit</a> or <a href="<?php echo add_query_arg(array('job_delete'=>$project_id), $_SERVER['REQUEST_URI']); ?>">delete</a></p>
        <?php } else { ?>
        <p><a href="<?php echo add_query_arg(array('prop_view'=>$project_proposal_id), $_SERVER['REQUEST_URI']); ?>">view proposal</a> </p>
        <?php } ?>
      </div>
      
    </article>
    
    <?php  endforeach; ?>
    
    <?php else : ?>
    <p>You have no jobs.</p>
    <?php
        endif; //End if has projects
    ?>
    
<?php endif; ?>
