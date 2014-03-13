<?php global $facility_id; ?>
<h3>media library</h3>

<p><a href="#" title="Click to upload" class="" data-toggle="modal" data-target="#fileUploadModal">Upload a new file</a></p>
<?php
//Loop files
$file_attachments = SF_Facility::load_file_attachments($facility_id);
if ( $file_attachments ) {
	foreach ($file_attachments  as $attachment) : 
		$attachment_name_array = explode('/',wp_get_attachment_url($attachment->ID));
		$attachment_name = $attachment_name_array[sizeof($attachment_name_array) - 1];
		$attachment_name = ( $attachment_name ) ? $attachment_name : $attachment->post_title;
	?>
    <article class="content-item general">
        <h4 class="content-title"><a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>"><?php echo $attachment_name; ?></a></h4>
      <div>
        <p><a href="#" title="Click to edit" class="" data-toggle="modal" data-target="#fileEditModal<?php echo $attachment->ID; ?>">edit</a> or <a href="#" title="Click to edit" class="" data-toggle="modal" data-target="#fileEditModal<?php echo $attachment->ID; ?>">delete</a></p>
      </div>
    </article>
     <!-- Modal -->
    <div class="modal fade" id="fileEditModal<?php echo $attachment->ID; ?>" tabindex="-1" role="dialog" aria-labelledby="fileEditModalLabel<?php echo $attachment->ID; ?>" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
         <form class="fv_profile_edit_modify_file" role="form" method="post">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="fileUploadModalLabel<?php echo $attachment->ID; ?>">Edit File</h4>
          </div>
          <div class="modal-body">
          
           <input type="hidden" name="fv_profile_edit_modify_file" value="facility" />
           <input type="hidden" name="upload_attachment_id" value="<?php echo $attachment->ID; ?>" />
           <input type="hidden" name="upload_action" value="edit" />
           <?php wp_nonce_field( 'fv_profile_edit_modify_file_nonce', 'fv_profile_edit_modify_file_nonce' ); ?>
           
           <div><strong>File:</strong></div>
           <a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>"><?php echo $attachment_name; ?></a>
           
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
  
<?php 
	endforeach; 
} ?>

<!-- Modal -->
<div class="modal fade" id="fileUploadModal" tabindex="-1" role="dialog" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
     <form id="fv_profile_edit_upload_file" role="form" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="fileUploadModalLabel">Upload File</h4>
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
        <button type="submit" id="fileUploadModalSubmit" class="btn btn-primary">Upload</button>
      </div>
     </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Handle form submit -->
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){

	//Submit photo upload modal
	$("#fileUploadModalSubmit").bind('click', function(e) {
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