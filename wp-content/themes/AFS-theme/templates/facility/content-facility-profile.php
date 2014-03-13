<?php global $facility_id, $fields, $user_fields;
//determine membership status type
$membership_type = fv_get_facility_membership_type($facility_id);
$categories_permitted = fv_get_facility_membership_addon_categories($facility_id);
$is_membership_active = ( $membership_type ) ? true : false;

?>
<form id="fv_profile_edit" role="form" method="post">
<input type="hidden" name="fv_profile_edit" value="facility" />
<?php wp_nonce_field( 'fv_profile_edit_nonce', 'fv_profile_edit_nonce' ); ?> 
<article class="clearfix">
	<?php 
    $featured_thumb = get_the_post_thumbnail($facility_id, 'img_400', array('class' => 'img-thumbnail'));
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
  <li><a href="#facility-profile" data-toggle="tab">Facility Profile</a></li>
  <li><a href="#facility-photos" data-toggle="tab">Facility Photos</a></li>
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
         </div
        ><div class="form-group">
          <input placeholder="location (city,state)" name="_location" value="<?php echo $fields['location']; ?>" class="full-width">
          </div>
<?php
      /*         <div class="form-group">
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
  <div class="tab-pane clearfix" id="facility-profile">
  	<div class="col-lg-12">
        <div class="form-group">
        	 <p><strong>Facility Name (Listing name):</strong></p>
              <input placeholder="listing name" name="post_title" value="<?php echo $fields['post_title']; ?>"class="full-width">
        </div>
        <div class="form-group">
          <p><strong>Type of Facility Managed? (you may select <?php echo $categories_permitted; ?> based on your membership)</strong></p>
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
          
          <?php
		  /*
          <div class="form-group">
            <p><strong>I need a contractor with the following skills:</strong></p>
           <?php
           //taxonomy is type tags (so use slugs for field value) 
            $skills = get_terms( array( SF_Taxonomies::JOB_SKILL_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
            foreach ( $skills as $skill ) : ?>
            <div class="custom-checkbox">
                  <input type="checkbox" value="<?php echo $skill->slug; ?>" id="<?php echo SF_Taxonomies::JOB_SKILL_TAXONOMY.'_'.$skill->term_id; ?>" name="<?php echo SF_Taxonomies::JOB_SKILL_TAXONOMY; ?>[]" <?php echo (in_array($skill->slug, $fields[SF_Taxonomies::JOB_SKILL_TAXONOMY])) ? 'checked="checked"' : ''; ?> />
                  <label for="<?php echo SF_Taxonomies::JOB_SKILL_TAXONOMY.'_'.$skill->term_id; ?>"></label><span class="field-meta"><?php echo $skill->name; ?></span>
            </div>
          <?php endforeach; ?>
          </div>
		  */
		  ?>
     </div>
      <div class="col-lg-6">
      <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="website url" name="_website" value="<?php echo $fields['website']; ?>" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
      </div>
      </div>
      <div class="col-lg-6">
       <?php
      /* <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="better businees bureau profile" value="<?php echo $fields['bbb_url']; ?>" name="_bbb_url" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
      </div>
      */
      ?>
      </div>
    </div>
<div class="tab-pane clearfix" id="facility-photos">
    <div class="photo-group">
<?php
  //Loop photos
  $photo_attachments = SF_Facility::load_attachments($facility_id);
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
          
           <input type="hidden" name="fv_profile_edit_modify_file" value="facility" />
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
</form>

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
       <input type="hidden" name="fv_profile_edit_upload_file" value="facility" />
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
       <input type="hidden" name="fv_profile_edit_upload_file" value="facility" />
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
       <input type="hidden" name="fv_profile_edit_modify_file" value="facility" />
       <input type="hidden" name="upload_attachment_id" value="<?php echo get_post_thumbnail_id($facility_id); ?>" />
       <input type="hidden" name="upload_type" value="featured" />
       <input type="hidden" name="upload_action" value="edit" />
       <?php wp_nonce_field( 'fv_profile_edit_modify_file_nonce', 'fv_profile_edit_modify_file_nonce' ); ?> 
       
       <div><strong>Photo:</strong></div>
       <?php echo get_the_post_thumbnail($facility_id, 'thumbnail', array('class' => 'img-thumbnail')); ?>
       
       <p><strong>File description:</strong>
       <input placeholder="type a description" name="upload_file_label" value="<?php echo esc_attr(get_post_field('post_content', get_post_thumbnail_id($facility_id))); ?>" class="full-width">
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