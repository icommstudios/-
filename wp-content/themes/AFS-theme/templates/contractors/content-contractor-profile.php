<?php global $contractor_id, $fields, $user_fields; 
//determine membership status type
$membership_type = fv_get_contractor_membership_type($contractor_id);
$categories_permitted = fv_get_contractor_membership_addon_categories($contractor_id);
$is_membership_active = ( $membership_type ) ? true : false;

?>
<form id="fv_profile_edit" role="form" method="post">
<input type="hidden" name="fv_profile_edit" value="contractor" />
<?php wp_nonce_field( 'fv_profile_edit_nonce', 'fv_profile_edit_nonce' ); ?> 
<article class="clearfix">
	<?php 
    $featured_thumb = get_the_post_thumbnail($contractor_id, 'img_400', array('class' => 'img-thumbnail'));
    if ($featured_thumb ) {
        ?>
        <a href="#" title="Click to edit" data-toggle="modal" data-target="#photoEditModalFeatured"><?php echo $featured_thumb; ?></a>
        <?php
    } else {
        ?>
        
      <div class="img-thumbnail">
        <a href="#" title="Click to upload" class="click-upload" data-toggle="modal" data-target="#photoUploadModalFeatured"><span><i class="fa fa-camera"></i>click to upload</span><img class="img-thumbnail" src="../assets/img/transparent-placeholder.png" alt="Upload" /></a>
      </div>
     
    <?php } ?>
</article>
<article class="clearfix">
<h4>Description</h4>
<textarea class="full-width" placeholder="description" name="post_content" rows="10"><?php echo esc_textarea($fields['post_content']); ?></textarea>
</article>
<article>
<h4>Profile Info</h4>
<ul class="nav nav-tabs" id="myTab">
  <li class="active"><a href="#afs-account" data-toggle="tab">Username/Password</a></li>
  <li><a href="#personal-profile" data-toggle="tab">Business Basics</a></li>
  <li><a href="#contractor-profile" data-toggle="tab">Vendor Profile</a></li>
  <li><a href="#project-photos" data-toggle="tab">Project Photos</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane active clearfix" id="afs-account">
    <div class="col-lg-6">
         <div class="form-group">
        <input type="email" placeholder="email/username" name="user_email" value="<?php echo (isset($_POST['user_email'])) ? $_POST['user_email'] : $user_fields->user_email; ?>" class="full-width">
        </div>
        <div class="form-group">
        <input type="password" name="password" placeholder="enter password" value="" class="full-width">
        </div>
        <div class="form-group">
        <input type="password" name="confirm-password" placeholder="confirm password" value="" class="full-width">
        </div>
   
        <div class="form-group">
        <div class="custom-checkbox">
                <input type="checkbox" value="None" name="_hide_profile" id="hide_profile" <?php echo ((bool)$fields['hide_profile']) ? 'checked="checked"' : ''; ?> />
                <label for="hide_profile"></label><span class="field-meta">hide my profile</span>
        </div>
        </div>
      </div>
  </div>
  <div class="tab-pane clearfix" id="personal-profile">
    <div class="col-lg-6">
        <div class="form-group">
          <input placeholder="name" name="_name" value="<?php echo $fields['name']; ?>" class="full-width">
        </div>
        <div class="form-group">
          <input placeholder="title" name="_title" value="<?php echo $fields['title']; ?>" class="full-width">
        </div>
        <div class="form-group">
          <input type="email" placeholder="email" name="_email" value="<?php echo $fields['email']; ?>" class="full-width">
        </div>
        <div class="form-group">
            <input type="hours" placeholder="available hours" value="<?php echo $fields['hours']; ?>" name="_hours" class="full-width">
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
          <input placeholder="company name" name="_company" value="<?php echo $fields['company']; ?>"class="full-width">
        </div>
        <div class="form-group">
          <input placeholder="phone" name="_phone" value="<?php echo $fields['phone']; ?>" class="full-width">
        </div>
        <div class="form-group">
          <input placeholder="location zip code" name="_location_zip" value="<?php echo $fields['location_zip']; ?>" class="full-width">
        </div>
        <div class="form-group">
          <input placeholder="location (city,state)" name="_location" value="<?php echo $fields['location']; ?>" class="full-width">
        </div>
        <?php
      /*  <div class="form-group">
            <label class="custom-select">
            <select class="full-width" name="_criminal_history" placeholder="do you have criminal history?">
                    <option value="" <?php echo ($fields['criminal_history'] == '') ? 'selected="selected"' : ''; ?>>do you have criminal history?</option>
                    <option value="yes" <?php echo ($fields['criminal_history'] == 'yes') ? 'selected="selected"' : ''; ?>>yes</option>
                    <option value="no" <?php echo ($fields['criminal_history'] == 'no') ? 'selected="selected"' : ''; ?>>no</option>
            </select>
          </label>
        </div> */
        ?>
    </div>

  </div>
  <div class="tab-pane clearfix" id="contractor-profile">
  	<div class="form-group">
       <p><strong>Contractor Name (Listing name):</strong></p>
              <input placeholder="listing name" name="post_title" value="<?php echo $fields['post_title']; ?>"class="full-width">
    </div>
    <?php
	/*
    <div class="form-group">
         <p><strong>Do you have prior facility experience in any of these fields?</strong></p>
          <?php
           //taxonomy is type category (so use ids for field value) 
            $types = get_terms( array( SF_Taxonomies::JOB_TYPE_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
            foreach ( $types as $type ) : ?>
            <div class="custom-checkbox">
                  <input type="checkbox" value="<?php echo $type->term_id; ?>" id="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY.'_'.$type->term_id; ?>" name="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY; ?>[]" <?php echo (in_array($type->term_id, $fields[SF_Taxonomies::JOB_TYPE_TAXONOMY])) ? 'checked="checked"' : ''; ?> />
                  <label for="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY.'_'.$type->term_id; ?>"></label><span class="field-meta"><?php echo $type->name; ?></span>
            </div>
          <?php endforeach; ?>
            
    </div>
	*/
	?>
    
    <div class="col-lg-6">
    	<?php
		//taxonomy is type category (so use ids for field value) 
		$types = get_terms( array( SF_Taxonomies::JOB_TYPE_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
			
		//selected categories ( get the primary category ) 
		$selected_categories = array();
		if ( !empty($fields['category_data'][0]) && in_array($fields['category_data'][0], $fields[SF_Taxonomies::JOB_TYPE_TAXONOMY]) ) {
			$selected_categories[] = $fields['category_data'][0]; //set primary as first
			foreach ( $fields[SF_Taxonomies::JOB_TYPE_TAXONOMY] as $catkey => $cat) {
				if ( $cat != $fields['category_data'][0] ) {
					$selected_categories[] = $cat;
				}
			}
		} else {
			$selected_categories = $fields[SF_Taxonomies::JOB_TYPE_TAXONOMY]; //use order in category list
		}
		
		?>
        <?php 
		//Number of categories
		if ( $categories_permitted ) :
			$cat_ii = 1;
			while ($cat_ii <= $categories_permitted ) :
			?>
        <div class="form-group">
            <label class="custom-select">
            <select class="full-width" name="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY; ?>[]">
            <option value=""><?php echo ( $cat_ii == 1 ) ? 'primary' : 'another'; ?> contractor category</option>
            <?php
			//taxonomy is type category (so use ids for field value) 
			$types = get_terms( array( SF_Taxonomies::JOB_TYPE_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
            foreach ( $types as $type ) : ?>
            <option <?php echo ($type->term_id == $selected_categories[$cat_ii-1]) ? 'selected="selected"' : ''; ?> value="<?php echo $type->term_id; ?>"><?php echo $type->name; ?></option>
          	<?php endforeach; ?>
            </select>
          </label>
        </div>
        <?php 
			$cat_ii++;
			endwhile;
		endif;
		?>
        
    </div>

    <div class="col-lg-6">
    	<div class="form-group">
            <label class="custom-select" for="_years_of_experience">
            <select name="_years_of_experience" class="full-width">
                <option value="" <?php echo ($fields['years_of_experience'] == '') ? 'selected="selected"' : ''; ?>>years of experience</option>
                <option value="1" <?php echo ($fields['years_of_experience'] == '1') ? 'selected="selected"' : ''; ?>>1 year</option>
                <option value="2" <?php echo ($fields['years_of_experience'] == '2') ? 'selected="selected"' : ''; ?>>2 years</option>
                <option value="3" <?php echo ($fields['years_of_experience'] == '3') ? 'selected="selected"' : ''; ?>>3 years</option>
                <option value="4" <?php echo ($fields['years_of_experience'] == '4') ? 'selected="selected"' : ''; ?>>4 years</option>
                <option value="5" <?php echo ($fields['years_of_experience'] == '5') ? 'selected="selected"' : ''; ?>>5 years</option>
                <option value="6" <?php echo ($fields['years_of_experience'] == '6') ? 'selected="selected"' : ''; ?>>6 years</option>
                <option value="7" <?php echo ($fields['years_of_experience'] == '7') ? 'selected="selected"' : ''; ?>>7 years</option>
                <option value="8" <?php echo ($fields['years_of_experience'] == '8') ? 'selected="selected"' : ''; ?>>8 years</option>
                <option value="9" <?php echo ($fields['years_of_experience'] == '9') ? 'selected="selected"' : ''; ?>>9 years</option>
                <option value="10-15" <?php echo ($fields['years_of_experience'] == '10-15') ? 'selected="selected"' : ''; ?>>10-15 years</option>
                <option value="16-20" <?php echo ($fields['years_of_experience'] == '16-20') ? 'selected="selected"' : ''; ?>>16-20 years</option>
                <option value="20-30" <?php echo ($fields['years_of_experience'] == '20-30') ? 'selected="selected"' : ''; ?>>20-30 years</option>
                <option value="30+" <?php echo ($fields['years_of_experience'] == '30+') ? 'selected="selected"' : ''; ?>>30+ years</option>
             </select>
          </label>
          </div>
        <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="contractor license & state" name="_contractor_license" value="<?php echo $fields['contractor_license']; ?>" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
        <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="insurance name & account #" name="_insurance_account" value="<?php echo $fields['insurance_account']; ?>" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
        <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="website url" name="_website" value="<?php echo $fields['website']; ?>" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
         <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="better businees bureau profile" value="<?php echo $fields['bbb_url']; ?>" name="_bbb_url" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
    </div>
  </div>
  <div class="tab-pane clearfix" id="project-photos">
    <div class="photo-group">
<?php
  //Loop photos
  $photo_attachments = SF_Contractor::load_attachments($contractor_id);
  if ( $photo_attachments ) {
  foreach ($photo_attachments  as $attachment) : 
  ?>
    <div class="col-sm-6 col-md-3">
      <a href="#" title="Click to edit" class="" data-toggle="modal" data-target="#photoEditModal<?php echo $attachment->ID; ?>"><img class="img-thumbnail" width="180" src="<?php echo wp_get_attachment_thumb_url($attachment->ID); ?>" alt="Edit" /></a>
   </div>
   <!-- Modal -->
    <div class="modal fade" id="photoEditModal<?php echo $attachment->ID; ?>" tabindex="-1" role="dialog" aria-labelledby="photoEditModalLabel<?php echo $attachment->ID; ?>" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
         <form class="fv_profile_edit_modify_file" role="form" method="post">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="photoUploadModalLabel<?php echo $attachment->ID; ?>">Edit Photo</h4>
          </div>
          <div class="modal-body">
          
           <input type="hidden" name="fv_profile_edit_modify_file" value="contractor" />
           <input type="hidden" name="upload_attachment_id" value="<?php echo $attachment->ID; ?>" />
           <input type="hidden" name="upload_action" value="edit" />
           <?php wp_nonce_field( 'fv_profile_edit_modify_file_nonce', 'fv_profile_edit_modify_file_nonce' ); ?> 
           
           <div><strong>Photo:</strong></div>
           <img class="img-thumbnail" src="<?php echo wp_get_attachment_thumb_url($attachment->ID); ?>" alt="Edit" />
           
           <p><strong>File description:</strong>
           <input placeholder="type a description" name="upload_file_label" value="<?php echo esc_attr($attachment->post_content); ?>" class="full-width">
           </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger pull-left">Delete this File</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
         </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
<?php endforeach; 
  } ?>
    
  <div class="col-sm-6 col-md-3">
    <a href="#" title="Click to upload" class="click-upload" data-toggle="modal" data-target="#photoUploadModal"><span><i class="fa fa-camera"></i>click to upload</span><img class="img-thumbnail" src="../assets/img/transparent-placeholder.png" alt="Upload" /></a>
  </div>
</div>

  </div>
</div>
</article>
<article>
<h4>Core Skills: Industries & Vendor Categories</h4>
<div class="skillset">
<div>
  <p>Industry Type</p>
        <div class="form-group">
            <label class="custom-select" for="industry_type">
            <select name="industry_type" class="full-width">
                <option value="option 1">option 1</option>
                <option value="option 2">option 2</option>
                <option value="option 3">option 3</option>
                <option value="option 4">option 4</option>
             </select>
          </label>
          <span class="un-verified"><i class="fa fa-thumbs-down"></i> <a href="#" data-toggle="modal" data-target="#referenceModal">Please provide 3 references to be listed in this category!</a></span>
          </div>
</div>
<p>Vendor Categories</p>
<div class="row">
<div class="col-lg-6">
        <div class="form-group">
            <label class="custom-select" for="vendor_cat">
            <select name="vendor_cat" class="full-width">
                <option value="option 1">option 1</option>
                <option value="option 2">option 2</option>
                <option value="option 2 Sub">-- option 2 Sub</option>
                <option value="option 3">option 3</option>
                <option value="option 4">option 4</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
             </select>
          </label>
          </div>
</div>
<div class="col-lg-6">
          <div class="form-group">
            <label class="custom-select" for="vendor_cat">
            <select name="vendor_cat" class="full-width">
                <option value="option 1">option 1</option>
                <option value="option 2">option 2</option>
                <option value="option 2 Sub">-- option 2 Sub</option>
                <option value="option 3">option 3</option>
                <option value="option 4">option 4</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
             </select>
          </label>
          </div>
</div>
</div>
</div>
<div class="skillset">
<div>
  <p>Industry Type</p>
        <div class="form-group">
            <label class="custom-select" for="industry_type">
            <select name="industry_type" class="full-width">
                <option value="option 1">option 1</option>
                <option value="option 2">option 2</option>
                <option value="option 3">option 3</option>
                <option value="option 4">option 4</option>
             </select>
          </label>
          <span class="verified"><i class="fa fa-thumbs-up"></i> References Verified</span>
          </div>
</div>
<p>Vendor Categories</p>
<div class="row">
<div class="col-lg-6">
        <div class="form-group">
            <label class="custom-select" for="vendor_cat">
            <select name="vendor_cat" class="full-width">
                <option value="option 1">option 1</option>
                <option value="option 2">option 2</option>
                <option value="option 2 Sub">-- option 2 Sub</option>
                <option value="option 3">option 3</option>
                <option value="option 4">option 4</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
             </select>
          </label>
          </div>
</div>
<div class="col-lg-6">
          <div class="form-group">
            <label class="custom-select" for="vendor_cat">
            <select name="vendor_cat" class="full-width">
                <option value="option 1">option 1</option>
                <option value="option 2">option 2</option>
                <option value="option 2 Sub">-- option 2 Sub</option>
                <option value="option 3">option 3</option>
                <option value="option 4">option 4</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
                <option value="option 4 Sub">-- option 4 Sub</option>
             </select>
          </label>
          </div>
</div>
</div>
</div>
</article>
</form>

<!-- References Modal -->
<div class="modal fade" id="referenceModal" tabindex="-1" role="dialog" aria-labelledby="referenceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="reference_upload_form" role="form" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="photoUploadModalLabel">Industry Title Here</h4>
        </div>
         <div class="modal-body">
          <p>Please provide 3 references to be listed in this category. Remember, we will verify your information!</p>
          <div class="reference">
            <strong>Reference 1</strong>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="name_company" id="name_company" value="Name/Company">
                </div>
                <div class="form-group">
                  <input type="text" name="phone" id="phone" value="Phone">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="email_address" id="email_address" value="Email Address">
                </div>
                <div class="form-group">
                  <input type="text" name="work_location" id="work_location" value="Work Location/City">
                </div>
              </div>
            </div>
          </div>
          <div class="reference">
            <strong>Reference 2</strong>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="name_company" id="name_company" value="Name/Company">
                </div>
                <div class="form-group">
                  <input type="text" name="phone" id="phone" value="Phone">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="email_address" id="email_address" value="Email Address">
                </div>
                <div class="form-group">
                  <input type="text" name="work_location" id="work_location" value="Work Location/City">
                </div>
              </div>
            </div>
          </div>
          <div class="reference">
            <strong>Reference 3</strong>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="name_company" id="name_company" value="Name/Company">
                </div>
                <div class="form-group">
                  <input type="text" name="phone" id="phone" value="Phone">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="email_address" id="email_address" value="Email Address">
                </div>
                <div class="form-group">
                  <input type="text" name="work_location" id="work_location" value="Work Location/City">
                </div>
              </div>
            </div>
          </div>
          <p class="add-more-references"><a href="#"><i class="fa fa-plus-circle"></i> add more references</a></p>
         </div>
         <div class="modal-footer">
          <button type="submit" id="referenceSubmit" class="btn btn-primary">Submit References</button>
         </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal -->
<div class="modal fade" id="photoUploadModal" tabindex="-1" role="dialog" aria-labelledby="photoUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
     <form id="fv_profile_edit_upload_file" role="form" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="photoUploadModalLabel">Upload Photo</h4>
      </div>
      <div class="modal-body">
       <input type="hidden" name="fv_profile_edit_upload_file" value="contractor" />
       <input type="hidden" name="upload_action" value="upload" />
       <?php wp_nonce_field( 'fv_profile_edit_upload_file_nonce', 'fv_profile_edit_upload_file_nonce' ); ?> 
       <strong>Select a file to upload:</strong>
       <input type="file" name="upload_file" id="upload_file">
       <strong>File description:</strong>
       <input placeholder="type a description" name="upload_file_label" value="" class="full-width">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" id="photoUploadModalSubmit" class="btn btn-primary">Upload</button>
      </div>
     </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Featured Image - Upload - Modal -->
<div class="modal fade" id="photoUploadModalFeatured" tabindex="-1" role="dialog" aria-labelledby="photoUploadModalFeaturedLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
     <form id="fv_profile_edit_upload_file" role="form" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="photoUploadModalFeaturedLabel">Upload Primary Photo</h4>
      </div>
      <div class="modal-body">
       <input type="hidden" name="fv_profile_edit_upload_file" value="contractor" />
       <input type="hidden" name="upload_type" value="featured" />
       
       <?php wp_nonce_field( 'fv_profile_edit_upload_file_nonce', 'fv_profile_edit_upload_file_nonce' ); ?> 
       <strong>Select a file to upload:</strong>
       <input type="file" name="upload_file" id="upload_file">
       <strong>File description:</strong>
       <input placeholder="type a description" name="upload_file_label" value="" class="full-width">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" id="photoUploadModalSubmit" class="btn btn-primary">Upload</button>
      </div>
     </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Featured Image - Edit - Modal -->
<div class="modal fade" id="photoEditModalFeatured" tabindex="-1" role="dialog" aria-labelledby="photoEditModalLabelFeatured" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
     <form class="fv_profile_edit_modify_file" role="form" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="photoEditModalLabelFeatured">Edit Primary Photo</h4>
      </div>
      <div class="modal-body">
       <input type="hidden" name="fv_profile_edit_modify_file" value="contractor" />
       <input type="hidden" name="upload_attachment_id" value="<?php echo get_post_thumbnail_id($contractor_id); ?>" />
       <input type="hidden" name="upload_type" value="featured" />
       <input type="hidden" name="upload_action" value="edit" />
       <?php wp_nonce_field( 'fv_profile_edit_modify_file_nonce', 'fv_profile_edit_modify_file_nonce' ); ?> 
       
       <div><strong>Photo:</strong></div>
       <?php echo get_the_post_thumbnail($contractor_id, 'thumbnail', array('class' => 'img-thumbnail')); ?>
       
       <p><strong>File description:</strong>
       <input placeholder="type a description" name="upload_file_label" value="<?php echo esc_attr(get_post_field('post_content', get_post_thumbnail_id($contractor_id))); ?>" class="full-width">
       </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pull-left">Delete this File</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
     </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- Handle form submit -->
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
	//Submit edit fields
	$(".profile-nav a.save-btn").bind('click', function(e) {
		e.preventDefault();
		$('#fv_profile_edit').submit();
	});
	
	//Submit photo upload modal
	$("#photoUploadModalSubmit").bind('click', function(e) {
		e.preventDefault();
		$('#fv_profile_edit_upload_file').submit();
	});
	//Each Edit photo modal
	$(".fv_profile_edit_modify_file .btn-primary").bind('click', function(e) {
		e.preventDefault();
		var form = $(this).closest("form");
		form.submit();
	});
	//Each Delete photo modal
	$(".fv_profile_edit_modify_file .btn-danger").bind('click', function(e) {
		e.preventDefault();
		var form = $(this).closest("form");
		var upload_action = $('input[name="upload_action"]', form);
		upload_action.val('delete');
		form.submit();
	});
});
</script>

