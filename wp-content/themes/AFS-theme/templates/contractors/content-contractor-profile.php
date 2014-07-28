<?php global $contractor_id, $fields, $user_fields; 
//determine membership status type
$membership_type = fv_get_contractor_membership_type($contractor_id);
$categories_permitted = fv_get_contractor_membership_addon_categories($contractor_id);
$is_membership_active = ( $membership_type ) ? true : false;

?>
<form id="fv_profile_edit" class="contractor-edit" role="form" method="post">
<input type="hidden" name="fv_profile_edit" value="contractor" />
<?php wp_nonce_field( 'fv_profile_edit_nonce', 'fv_profile_edit_nonce' ); ?> 
<section class="photos clearfix">
  <h3>Photos</h3>
  <div class="photo-group">
<?php
  //Loop photos (loops again at bottom of this template for modal dialogs)
  $photo_attachments = SF_Contractor::load_attachments($contractor_id);
  if ( $photo_attachments ) {
  foreach ($photo_attachments  as $attachment) : 
  ?>
    <div class="col-sm-6 col-md-3">
      <a href="#" title="Click to edit" class="" data-toggle="modal" data-target="#photoEditModal<?php echo $attachment->ID; ?>"><img class="img-thumbnail" width="180" src="<?php echo wp_get_attachment_thumb_url($attachment->ID); ?>" alt="Edit" /></a>
   </div>
   
<?php endforeach; 
  } ?>
    
  <div class="col-sm-6 col-md-3">
    <a href="#" title="Click to upload" class="click-upload" data-toggle="modal" data-target="#photoUploadModal"><span><i class="fa fa-camera"></i>click to upload</span><img class="img-thumbnail" src="../assets/img/transparent-placeholder.png" alt="Upload" /></a>
  </div>
</div>
</section>
<hr>
<section class="overview clearfix">
<h3>Company Overview</h3>
<textarea class="full-width" placeholder="description" name="post_content" rows="10"><?php echo esc_textarea($fields['post_content']); ?></textarea>
</section>
<hr>
<section class="business-basics clearfix">
<h3>Business Basics</h3>
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
</section>
<hr>
<section class="products-services clearfix">
<h3>Products &amp Services</h3>
        <div class="form-group">
          <h4>New Checkboxes (Going to Need Daniels Assistance)</h4>
          <p><strong>Note:</strong> You may select <?php echo $categories_permitted; ?> based on your membership.</p>
          <?php
           //taxonomy is type category (so use ids for field value) 
            $types = get_terms( array( SF_Taxonomies::JOB_TYPE_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all', 'parent'=> 0 ) );
            foreach ( $types as $type ) : ?>
            <div class="custom-checkbox">
                  <input type="checkbox" value="<?php echo $type->term_id; ?>" id="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY.'_'.$type->term_id; ?>" name="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY; ?>[]" <?php echo (in_array($type->term_id, $fields[SF_Taxonomies::JOB_TYPE_TAXONOMY])) ? 'checked="checked"' : ''; ?> />
                  <label for="<?php echo SF_Taxonomies::JOB_TYPE_TAXONOMY.'_'.$type->term_id; ?>"></label><span class="field-meta"><?php echo $type->name; ?></span>
            </div>
          <?php endforeach; ?>
           
          </div>
          <hr>
     <div class="form-group">
      <h4>Old Dropdowns</h4>
      <?php
    //taxonomy is type category (so use ids for field value) 
    $types = get_terms( array( SF_Taxonomies::JOB_TYPE_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
    
    $post_terms = wp_get_object_terms( $contractor_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'ids' ) );
      
    //selected categories ( merge term categories with the selected category data )
    $selected_categories = $fields['category_data']; //set category_data as first
    //merge with post terms
    if ( !empty( $post_terms ) ) {
      foreach ( $post_terms as $catkey => $cat_term_id) {
        if ( !in_array($cat_term_id, $fields['category_data']) ) {
          $selected_categories[] = $cat_term_id;
        }
      }
    } 
    ?>
        <?php 
    //Number of categories
    if ( $categories_permitted ) :
      
      $cat_ii = 1;
      while ($cat_ii <= $categories_permitted ) :
      ?>
           <div class="skillset">
            <div>
              <p>Industry Type & Vendor Categories (<?php echo $cat_ii; ?> of <?php echo $categories_permitted; ?>) </p>
                    <div class="form-group">
                        <label class="custom-select" for="industry_type">
                        <?php 
              $select_name = SF_Taxonomies::JOB_TYPE_TAXONOMY.'[]';
              $select_id = 'reference-selectid-'.$cat_ii;
              $select_cat_index = $cat_ii-1;
              $selected_term = $selected_categories[$select_cat_index];
              $none_option = ( $cat_ii == 1 ) ? '-- select primary contractor category --' : '-- select another contractor category --';
              wp_dropdown_categories( array( 'taxonomy' => SF_Taxonomies::JOB_TYPE_TAXONOMY, 'name' => $select_name, 'id' => $select_id, 'selected' => $selected_term, 'class' => 'full-width', 'show_option_none' => $none_option, 'hierarchical' => true, 'hide_empty' => false )); 
              ?>
                      </label>
                     
                      <?php
            //Verfied category? (is it in the terms)
            if ( $fields[SF_Taxonomies::JOB_TYPE_TAXONOMY] && in_array($selected_term, $post_terms) ) {
            ?>
                          <span class="verified"><i class="fa fa-thumbs-up"></i> References verified, good job.</span>
                     <?php
            } else {
            ?>
                          <span class="un-verified"><i class="fa fa-thumbs-down"></i> <a class="open-ReferenceModal" href="#" data-select_cat_index="<?php echo $select_cat_index; ?>" data-select_id="<?php echo $select_id; ?>">3 industry specific references are required to be found in search results for each industry listed!</a></span>
            <?php
            } 
            ?>
                      </div>
            </div>
           </div>
            <?php 
      $cat_ii++;
      endwhile;
    endif;
    ?>
      
      </div>
</section>
<hr>
<section class="vendor-profile clearfix">
<h3>Vendor Profile</h3>
<div class="form-group">
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
      <div class="form-group">
          <input placeholder="Year Established?" name="_years_of_experience" value="<?php echo $fields['years_of_experience']; ?>"class="full-width">
          </div>
        <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="contractor license & state" name="_contractor_license" value="<?php echo $fields['contractor_license']; ?>" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
        <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="insurance name & account #" name="_insurance_account" value="<?php echo $fields['insurance_account']; ?>" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
        
    </div>

    <div class="col-lg-6">
      
        <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="website url" name="_website" value="<?php echo $fields['website']; ?>" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
         <div class="form-group">
          <input <?php echo ($is_membership_active) ? '' : 'disabled="disabled"'; ?> placeholder="better businees bureau profile" value="<?php echo $fields['bbb_url']; ?>" name="_bbb_url" class="full-width <?php echo ($is_membership_active) ? '' : 'disabled'; ?>">
        </div>
    </div>
</section>
<hr>
<section class="afs-account clearfix">
<h3>AFS Account</h3>
    <div class="col-lg-6">
         <div class="form-group">
        <input type="email" placeholder="email/username" name="user_email" value="<?php echo (isset($_POST['user_email'])) ? $_POST['user_email'] : $user_fields->user_email; ?>" class="full-width">
        </div>
        <div class="form-group">
        <input type="password" name="password" placeholder="enter password" value="" class="full-width" autocomplete="off" readonly onfocus="$(this).removeAttr('readonly');">
        </div>
        <div class="form-group">
        <input type="password" name="confirm-password" placeholder="confirm password" value="" class="full-width" autocomplete="off" readonly onfocus="$(this).removeAttr('readonly');">
        </div>
   
        <div class="form-group">
        <div class="custom-checkbox">
                <input type="checkbox" value="None" name="_hide_profile" id="hide_profile" <?php echo ((bool)$fields['hide_profile']) ? 'checked="checked"' : ''; ?> />
                <label for="hide_profile"></label><span class="field-meta">hide my profile</span>
        </div>
        </div>
      </div>
</section>
</form>

<!-- References Modal (NEW) -->
<div class="modal fade" id="referenceModal" tabindex="-1" role="dialog" aria-labelledby="referenceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="reference_upload_form" role="form" method="post">
      	<input type="hidden" name="fv_profile_edit_category_reference" value="contractor" />
        <?php wp_nonce_field( 'fv_profile_edit_category_reference_nonce', 'fv_profile_edit_category_reference_nonce' ); ?>
      	<input type="hidden" class="field_reference_term_id" name="reference_term_id" value="">
        <input type="hidden" class="category_data_index" name="category_data_index" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="referenceModalLabel">Industry Title Here</h4>
        </div>
         <div class="modal-body">
          <p>Please provide 3 references to be listed in this category. Remember, we will verify your information!</p>
          <div class="reference">
            <strong>Reference 1</strong>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="name_company[0]" placeholder="Contact Company Name">
                </div>
                <div class="form-group">
                  <input type="text" name="name_contact[0]" placeholder="Contact Person">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="email_address[0]" placeholder="Contact Email Address">
                </div>
                <div class="form-group">
                  <input type="text" name="phone[0]" placeholder="Contact Phone #">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="work_type[0]" placeholder="Type of Work">
                </div>
                <div class="form-group">
                  <input type="text" name="industry_type[0]" placeholder="Type of Industry">
                </div>
              </div>
            </div>
          </div>
          <div class="reference">
            <strong>Reference 2</strong>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="name_company[1]" placeholder="Contact Company Name">
                </div>
                <div class="form-group">
                  <input type="text" name="name_contact[1]" placeholder="Contact Person">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="email_address[1]" placeholder="Contact Email Address">
                </div>
                <div class="form-group">
                  <input type="text" name="phone[1]" placeholder="Contact Phone #">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="work_type[1]" placeholder="Type of Work">
                </div>
                <div class="form-group">
                  <input type="text" name="industry_type[1]" placeholder="Type of Industry">
                </div>
              </div>
            </div>
          </div>
          <div class="reference">
            <strong>Reference 3</strong>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="name_company[2]" placeholder="Contact Company Name">
                </div>
                <div class="form-group">
                  <input type="text" name="name_contact[2]" placeholder="Contact Person">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="email_address[2]" placeholder="Contact Email Address">
                </div>
                <div class="form-group">
                  <input type="text" name="phone[2]" placeholder="Contact Phone #">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="work_type[2]" placeholder="Type of Work">
                </div>
                <div class="form-group">
                  <input type="text" name="industry_type[2]" placeholder="Type of Industry">
                </div>
              </div>
            </div>
          </div>
          <div class="additional-references">
          </div>
          <p class="add-more-references"><a href="#" class="btn-add-more-references"><i class="fa fa-plus-circle"></i> add more references</a></p>
         </div>
         <div class="modal-footer">
          <button type="submit" id="referenceSubmit" class="btn btn-primary">Submit References</button>
         </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
  //Loop existing references and build modals
  if ( $fields['category_references'] ) {
  foreach ($fields['category_references'] as $existing_ref_term_id => $existing_ref) : 
  ?>
  <div class="modal fade" id="referenceModal-term<?php echo $existing_ref_term_id; ?>" tabindex="-1" role="dialog" aria-labelledby="referenceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="reference_upload_form" role="form" method="post">
      	<input type="hidden" name="fv_profile_edit_category_reference" value="contractor" />
        <?php wp_nonce_field( 'fv_profile_edit_category_reference_nonce', 'fv_profile_edit_category_reference_nonce' ); ?>
      	<input type="hidden" class="field_reference_term_id" name="reference_term_id" value="">
        <input type="hidden" class="category_data_index" name="category_data_index" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="referenceModalLabel">Industry Title Here</h4>
        </div>
         <div class="modal-body">
          <p>Please provide 3 references to be listed in this category. Remember, we will verify your information!</p>
          <?php 
		  $ref_count = ( sizeof( $existing_ref ) > 3 ) ? sizeof($existing_ref) : 3; 
		  $ref_ii = 0;
		  while ($ref_ii < $ref_count) {
			  ?>
          <div class="reference">
            <strong>Reference <?php echo ($ref_ii + 1); ?></strong>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="name_company[<?php echo $ref_ii; ?>]" placeholder="Contact Company Name" value="<?php echo ($existing_ref[$ref_ii]['name_company']) ? $existing_ref[$ref_ii]['name_company'] : ''; ?>">
                </div>
                <div class="form-group">
                  <input type="text" name="name_contact[<?php echo $ref_ii; ?>]" placeholder="Contact Person" value="<?php echo ($existing_ref[$ref_ii]['phone']) ? $existing_ref[$ref_ii]['name_contact'] : ''; ?>">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="email_address[<?php echo $ref_ii; ?>]" placeholder="Contact Email Address" value="<?php echo ($existing_ref[$ref_ii]['email_address']) ? $existing_ref[$ref_ii]['email_address'] : ''; ?>">
                </div>
                <div class="form-group">
                  <input type="text" name="phone[<?php echo $ref_ii; ?>]" placeholder="Contact Phone #" value="<?php echo ($existing_ref[$ref_ii]['phone']) ? $existing_ref[$ref_ii]['phone'] : ''; ?>">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <input type="text" name="work_type[<?php echo $ref_ii; ?>]" placeholder="Type of Work" value="<?php echo ($existing_ref[$ref_ii]['work_type']) ? $existing_ref[$ref_ii]['work_type'] : ''; ?>">
                </div>
                <div class="form-group">
                  <input type="text" name="industry_type[<?php echo $ref_ii; ?>]" placeholder="Type of Industry" value="<?php echo ($existing_ref[$ref_ii]['industry_type']) ? $existing_ref[$ref_ii]['industry_type'] : ''; ?>">
                </div>
              </div>
            </div>
          </div>
          <?php
		  	 $ref_ii++;
		  }
		  ?>
          <div class="additional-references">
          </div>
          <p class="add-more-references"><a href="#" class="btn-add-more-references"><i class="fa fa-plus-circle"></i> add more references</a></p>
         </div>
         <div class="modal-footer">
          <button type="submit" id="referenceSubmit" class="btn btn-primary">Submit References</button>
         </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
  <?php
  endforeach;
  }
  ?>

<script type="text/javascript">
$(document).ready(function(){
	//Set the term_id on reference modal open
	$(document).on("click", ".open-ReferenceModal", function (e) {
		e.preventDefault();
		
		 var select_id = $(this).data('select_id');
		 //Get selected
		 var term_id = $('#' + select_id +' :selected').val();
		 var category_data_index = $(this).data('select_cat_index');
		 //Get the modal to open
		 var open_ref_modal = '#referenceModal-term' + term_id;
		 if ( $(open_ref_modal).length > 0 ) {
			//found existing ref modal
		 } else {
			//new
			open_ref_modal =  '#referenceModal';
		 }
		 if ( term_id > 0 ) {
			var term_title = $('#' + select_id +' :selected').text();
			$(open_ref_modal + " .field_reference_term_id").val( term_id );
			$(open_ref_modal + " .category_data_index").val( category_data_index );
			//set the title for the modal
		 	$(open_ref_modal + " .modal-title").html( term_title );
			//Open the modal
			$(open_ref_modal).modal('show'); 
		 } else {
			alert('You must select a category first.'); 
			return false;
		 }
	});
	//Add new reference in modal
	var countref = 3;
	$('.btn-add-more-references').on('click', function() {
		countref++;
		
		var ref_html = '<div class="reference">'
		+ '<strong>Reference ' + countref + ' </strong>'
		+ '<div class="row">'
        + '      <div class="col-lg-6">'
        + '        <div class="form-group">'
        + '          <input type="text" name="name_company[' + countref + ']" placeholder="Contact Company Name">'
        + '        </div>'
        + '        <div class="form-group">'
        + '          <input type="text" name="name_contact[' + countref + ']" placeholder="Contact Person">'
        + '        </div>'
        + '      </div>'
        + '      <div class="col-lg-6">'
        + '        <div class="form-group">'
        + '          <input type="text" name="email_address[' + countref + ']" placeholder="Contact Email Address">'
        + '        </div>'
        + '        <div class="form-group">'
        + '          <input type="text" name="phone[' + countref + ']" placeholder="Contact Phone #">'
        + '        </div>'
        + '      </div>'
        + '      <div class="col-lg-6">'
        + '        <div class="form-group">'
        + '          <input type="text" name="work_type[' + countref + ']" placeholder="Type of Work">'
        + '        </div>'
        + '        <div class="form-group">'
        + '          <input type="text" name="industry_type[' + countref + ']" placeholder="Type of Industry">'
        + '        </div>'
        + '      </div>'
        + '    </div>'
	  	+ '</div>';
		$('.additional-references').append(ref_html);
		
	});
	
	
	 
});
</script>

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
        <h4 class="modal-title" id="photoUploadModalFeaturedLabel">Upload Logo</h4>
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
        <h4 class="modal-title" id="photoEditModalLabelFeatured">Edit Logo</h4>
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

<?php
  //Loop photos (AGAIN) - Modal dialgs
  $photo_attachments = SF_Contractor::load_attachments($contractor_id);
  if ( $photo_attachments ) {
  foreach ($photo_attachments  as $attachment) : 
  ?>
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

