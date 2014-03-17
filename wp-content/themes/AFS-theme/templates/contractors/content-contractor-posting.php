<?php
$contractor_id = get_the_ID();
$membership_type = fv_get_contractor_membership_type($contractor_id);
$categories_permitted = fv_get_contractor_membership_addon_categories($contractor_id);
$fields = fv_get_contractor_fields($contractor_id);
$quality_verified = fv_get_contractor_quality_verified ( $contractor_id, $fields );
$featured_thumb = get_the_post_thumbnail($contractor_id, 'img_400', array('class' => ''));
//Current user
$user_type_data = fv_get_current_user_type_id();
?>
<div class="row">
   <div class="col-lg-8">
      <div class="visible-xs">
        <a class="btn large full-width blue" href="#" data-toggle="modal" data-target="#contactModal">contact <?php echo $fields['name']; ?></a>
        <?php
        //Is current logged in user a facility 
        if ( $user_type_data['user_type'] == SF_Users::USER_TYPE_FACILITY ) : ?> 
        <a class="btn large full-width blue" href="#" data-toggle="modal" data-target="#inviteJobModal">invite <?php echo $fields['name']; ?> to a Job</a>
        <?php endif; ?>
        <?php if ( $quality_verified['completed'] && $quality_verified['completed'] == 100 ) : ?>
        <span class="certified-badge"><i class="fa fa-trophy"></i>Gold Member Certified</span>
        <?php endif; ?>
        <hr />
      </div>

<?php
//Which page to show
if ( isset( $_GET['endorsements'] ) ) : ?>
	<h3>endorsements & ratings</h3>
    <?php
	//Get endorsements (comments)
	$args = array(
		'status' => 'approve',
		'number' => '99',
		'post_id' => $contractor_id, 
	);
	$comments = get_comments($args);
	if ( $comments ) {
		foreach($comments as $comment) :
			//Get commenter's name
			$commentor_name = $comment->comment_author;
			$commentor_link = $commentor_name;
			if ( $comment->user_id ) {
				$user_type_data = fv_get_current_user_type_id($comment->user_id);
				if ( $user_type_data['user_type_id'] ) {
					$commentor_name = get_the_title( $user_type_data['user_type_id'] );
					$commentor_link = $commentor_name;
					if ( $user_type_data['user_type'] == SF_Users::USER_TYPE_CONTRACTOR ) {
						$commentor_link = '<a href="'.get_permalink( $user_type_data['user_type_id'] ).'">'.$commentor_name.'</a>';
					}
				}
			}
			
			//Get rating
			$rating = get_comment_meta($comment->comment_ID, '_rating', true);
			$rating = ( intval($rating) > 0 ) ? intval($rating) : 1;
			//Get project
			$related_project_id = get_comment_meta($comment->comment_ID, '_related_project_id', true);
			$related_project_link = ( $related_project_id ) ? get_permalink( $related_project_id ) : '';
			?>
		 <article class="content-item general">
			<span class="rating-stars">
			<?php 
			//Rating
			$rating_ii = 1;
			while($rating_ii <= $rating ) {
				?>
				<span class="fa fa-star white-star"></span>
				<?php
				$rating_ii++;
			}
			?>
			</span>
			<h4 class="content-title"><?php echo $commentor_link; ?></h4>
			<p class="content-summary"><?php echo $comment->comment_content; ?></p>
			<?php if ( $related_project_link) : ?>
				<div class="content-meta">
					<p class="author">related to project <a href="<?php echo $related_project_link; ?>" rel="author" class="fn"><?php echo get_the_title( $related_project_id ); ?></a>
					</p>
				</div>
			<?php endif; ?>
		</article>
			<?php
		endforeach;
	
	} else {
		?>
        <p>No endorsements available.</p>
        <?php	
	}
	?>

</div>

<?php else : //show content ?>


<ul class="list-inline details-meta">
	<?php if ( $fields['years_of_experience'] ) : ?><li><?php echo $fields['years_of_experience']; ?> yrs. experience</li><?php endif; ?>
	<?php if ( $fields['email'] ) : ?><li><i class="fa fa-envelope-o"></i> <a href="mailto:<?php echo $fields['email']; ?>"><?php echo $fields['email']; ?></a></li><?php endif; ?>
	<?php if ( $fields['phone'] ) : ?><li><i class="fa fa-phone"></i> <a href="tel:<?php echo $fields['phone']; ?>"><?php echo $fields['phone']; ?></a></li><?php endif; ?>
	<?php if ( $fields['hours'] ) : ?><li><i class="fa fa-clock-o"></i><?php echo $fields['hours']; ?></li><?php endif; ?>
</ul>

<?php if ( $featured_thumb ) : ?> 
<div class="featured-img">
	<?php echo $featured_thumb; ?>
</div>
<?php endif; ?>

<?php the_content(); ?>
<hr>
<h4>Contractor Details</h4>
<ul class="nav nav-tabs" id="myTab">
  <li class="active"><a href="#general" data-toggle="tab">General</a></li>
  <?php if (!empty($membership_type)) : ?><li><a href="#licenses" data-toggle="tab">Licenses & Accreditations</a></li><?php endif; ?>
  <?php if (!empty($membership_type)) : ?><li><a href="#ratings" data-toggle="tab">Ratings & Referrals</a></li><?php endif; ?>
  <li><a href="#photos" data-toggle="tab">Photos</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane active" id="general">
  	<ul class="col-lg-3 fa-ul">
  		<?php if ( $fields['name'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['name']; ?></li><?php endif; ?>
  		<?php if ( $fields['company'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['company']; ?></li><?php endif; ?>
  		<?php if ( $fields['phone'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['phone']; ?></li><?php endif; ?>
  		<?php if ( $fields['email'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['email']; ?></li><?php endif; ?>
  		<?php if ( $fields['location'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['location']; ?></li><?php endif; ?>
  	</ul>
  	<ul class="col-lg-3 fa-ul">
  		<?php if ( $fields['hours'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['hours']; ?></li><?php endif; ?>
  		<?php if ( $fields['years_of_experience'] ) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['years_of_experience']; ?> yrs. experience</li><?php endif; ?>
  		<?php if ( !empty($membership_type) && $fields['website']) : ?><li><i class="fa-li fa fa-square"></i><?php echo $fields['website']; ?></li><?php endif; ?>
  	</ul>
  	<ul class="col-lg-5 skillsets">
  		<li class="head">Core skills</li>
        <?php
            $types = wp_get_object_terms( $contractor_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'all' ));
            if ( $types ) {
				$cat_count = 0;
                foreach ($types as $type) {
                    //$link = get_term_link( $type, SF_Taxonomies::JOB_TYPE_TAXONOMY );
					$cat_count++;
					if ( $cat_count <= $categories_permitted ) {
                    ?>
                    <li><span class="label label-primary"><?php echo $type->name; ?></span></li>
                    <?php
					}
                }
            }
		?>
  	</ul>
  </div>
  <?php if ( !empty($membership_type) ) : ?>
  <div class="tab-pane" id="licenses">
  	<?php if ( $fields['contractor_license'] ) : ?>
    <div class="col-lg-4 text-center">
         <i class="fa fa-credit-card"></i>
        <a href="#"><?php echo $fields['contractor_license']; ?></a>
      <span>General Contractors License & State</span>
      
    	</li>
    </div>
    <?php endif; ?>
    <?php if ( $fields['bbb_url'] ) : ?>
    <div class="col-lg-4 text-center">
      
        <i class="fa fa-check-square-o"></i>
        <a href="<?php echo $fields['bbb_url']; ?>" target="_blank">View BBB Profile</a>
      <span>Better Business Bureau Accredited</span>
      
    	</li>
    </div>
    <?php endif; ?>
    <?php if ( $fields['insurance_account'] ) : ?>
    <div class="col-lg-4 text-center">
         <i class="fa fa-clipboard"></i>
         <a href="#"><?php echo $fields['insurance_account']; ?></a>
      <span>Insurance Name & Account Number</span>
      
    	</li>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <?php if ( !empty($membership_type) ) : ?>
  
  	<div class="tab-pane" id="ratings">
    <?php
	//Get endorsements (comments)
	$args = array(
		'status' => 'approve',
		'number' => '2',
		'post_id' => $contractor_id, 
	);
	$comments = get_comments($args);
	if ( $comments ) {
		echo '<ul>';
		foreach($comments as $comment) :
			//Get commenter's name
			$commentor_name = $comment->comment_author;
			if ( $comment->user_id ) {
				$user_type_data = fv_get_current_user_type_id($comment->user_id);
				if ( $user_type_data['user_type_id'] ) {
					$commentor_name = get_the_title( $user_type_data['user_type_id'] );
				}
			}
			//Get rating
			$rating = get_comment_meta($comment->comment_ID, '_rating', true);
			$rating = ( intval($rating) > 0 ) ? intval($rating) : 1;
			//Get project
			$related_project_id = get_comment_meta($comment->comment_ID, '_related_project_id', true);
			$related_project_link = ( $related_project_id ) ? get_permalink( $related_project_id ) : '';
			?>
		 <li>
			<span class="rating-stars">
			<?php 
			//Rating
			$rating_ii = 1;
			while($rating_ii <= $rating ) {
				?>
				<span class="fa fa-star white-star"></span>
				<?php
				$rating_ii++;
			}
			?>
			</span>
			<h4 class="content-title"><a href="#"><?php echo $commentor_name; ?></a></h4>
			<p class="content-summary"><?php echo $comment->comment_content; ?></p>
			<?php if ( $related_project_link) : ?>
				<div class="content-meta">
					<p class="author">related to project <a href="<?php echo $related_project_link; ?>" rel="author" class="fn"><?php echo get_the_title( $related_project_id ); ?></a>
					</p>
				</div>
			<?php endif; ?>
		</li>
			<?php
		endforeach;
		echo '</ul>';
		echo '<a href="'.add_query_arg(array('endorsements' => '1'), get_permalink($contractor_id)).'">Read all</a>';
	} else {
		?>
        <p>No endorsements available.</p>
        <?php	
	}
	?>
    </div>
  <?php endif; ?>

    <div class="tab-pane" id="photos">
          <div class="photo-group">
    <?php
        //Loop photos
        $photo_attachments = SF_Contractor::load_attachments($contractor_id);
        if ( $photo_attachments ) {
        foreach ($photo_attachments as $attachment) : 
            $attachment_name_array = explode('/',wp_get_attachment_url($attachment->ID));
            $attachment_name = $attachment_name_array[sizeof($attachment_name_array) - 1];
            $attachment_name = ( $attachment_name ) ? $attachment_name : $attachment->post_title;
        ?>
      <div class="col-sm-6 col-md-3">
        <a href="<?php echo wp_get_attachment_url($attachment->ID); ?>" rel="prettyPhoto" title="<?php echo $attachment_name; ?>"><img class="img-thumbnail" src="<?php echo wp_get_attachment_thumb_url($attachment->ID); ?>" alt="<?php echo $attachment_name; ?>" /></a>
      </div>
      <?php endforeach;
        } ?>
    </div>
  </div>
  
</div>
<div class="clearfix"></div>
</div>

<?php endif; //end which page to show else ?>

<div class="col-lg-4 hidden-xs">
  <div>
    <a class="btn large full-width blue" href="#" data-toggle="modal" data-target="#contactModal">contact <?php echo $fields['name']; ?></a>
    <?php
	//Is current logged in user a facility 
	if ( $user_type_data['user_type'] == SF_Users::USER_TYPE_FACILITY ) : ?> 
    <a class="btn large full-width blue" href="#" data-toggle="modal" data-target="#inviteJobModal">invite <?php echo $fields['name']; ?> to a Job</a>
    <?php endif; ?>
    <?php if ( $quality_verified['completed'] && $quality_verified['completed'] == 100 ) : ?>
    <span class="certified-badge"><i class="fa fa-trophy"></i>Gold Member Certified</span>
    <?php endif; ?>
    <hr />
  </div>
  
  <?php
  //Get related
 	$add_taxonomy_args = array();
  	$related_types = wp_get_object_terms( get_the_ID(), SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'ids' ));
	if ( $related_types ) {
	  	$add_taxonomy_args = array_merge($add_taxonomy_args, array(
					'taxonomy' => SF_Taxonomies::JOB_TYPE_TAXONOMY,
					'field' => 'id',
					'terms' => $related_types,
					'operator' => 'IN'
				));
	} 
	/*
	$related_skills = wp_get_object_terms( get_the_ID(), SF_Taxonomies::JOB_SKILL_TAXONOMY, array( 'fields' => 'slugs' ));
	if ( $related_skills ) {
	 	$add_taxonomy_args = array_merge($add_taxonomy_args, array(
				'taxonomy' => SF_Taxonomies::JOB_SKILL_TAXONOMY,
				'field' => 'slug',
				'terms' => $related_skills,
				'operator' => 'IN'
			));
		
	} 
	*/
	//Build args
	$args = array(
		'post_type' => SF_Contractor::POST_TYPE,
		'posts_per_page' => 10,
		'post__not_in' => array( get_the_ID() ),
	);
	
	if ( $add_taxonomy_args ) {
		$args = array_merge($args, array( 'tax_query' => array( 'relation' => 'OR', $add_taxonomy_args) ) );
	}
	
	$related_query = new WP_Query( $args );
	?>
    <?php if ( $related_query->have_posts() ) : ?>
  <div class="related-posts">
    <h3>Related Items</h3>
    <ul>
    <?php $count; while ( $related_query->have_posts() ) : $related_query->the_post(); $count++; $zebra = ($count % 2) ? ' blue-text' : ' purple-text'; ?>
      <li>
        <time><?php the_date(); ?></time>
        <h4 class="posting-title <?php echo $zebra; ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <p class="posting-summary"><?php echo get_the_excerpt(); ?></p>
      </li>
      <?php endwhile; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="fv_message_send" role="form" method="post" action="<?php echo add_query_arg(array('msg_form' => 1), $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="fv_message_send" value="1" />
        <input type="hidden" name="to" value="<?php echo $facility_id; ?>" />
        <?php wp_nonce_field( 'fv_message_send_nonce', 'fv_message_send_nonce' ); ?> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="contactModalLabel">How to proceed with <?php echo ($facility_name) ? $facility_name : $facility_listing; ?></h4>
      </div>
      <div class="modal-body">
         <input placeholder="message title" name="post_title" value="<?php echo esc_attr(stripslashes($_POST['post_title'])); ?>" class="full-width">
      </div>
      <div class="modal-body">
        <textarea class="full-width" rows="6" name="post_content" placeholder="type your message"><?php echo esc_textarea(stripslashes($_POST['post_content'])); ?></textarea>
      </div>
      <div class="modal-footer">
        <div class="btn-group">
         <input type="submit" class="btn large" name="submit_message" value="send">
         <a class="btn large" href="<?php echo add_query_arg(array('action' => 'messages'), SF_Users::user_profile_url()); ?>">go to messages</a>
       </div>
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
//Is current logged in user a facility 
if ( $user_type_data['user_type'] == SF_Users::USER_TYPE_FACILITY ) : ?> 
<!-- Job invite Modal -->
<div class="modal fade" id="inviteJobModal" tabindex="-1" role="dialog" aria-labelledby="inviteJobModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="fv_invite_message_send" role="form" method="post" action="<?php echo add_query_arg(array('msg_form' => 2), $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="fv_message_send" value="1" />
        <input type="hidden" name="to" value="<?php echo $contractor_id; ?>" />
        <input type="hidden" name="type" value="project_invite" />
        <?php wp_nonce_field( 'fv_message_send_nonce', 'fv_message_send_nonce' ); ?> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="inviteJobModalLabel">Invite <?php echo $fields['name']; ?> to Job</h4>
      </div>
      <div class="modal-body">
        <p><strong>Invite this contractor to review of your jobs</strong></p>
        <div class="form-group">
        <label class="custom-select">
            <select name="related_project_id" class="full-width">
             <option value="">select a job</option>
            <?php
            //Load Projects for this Facility
            $projects = SF_Project::get_project_ids_for_facility($user_type_data['user_type_id']);
            if ( $projects ) : 
              foreach ( $projects as $project_id ) : 
			  	//Has proposal assigned
				$proposal = SF_Project::get_field($project_id, 'proposal_id');
				if ( empty($proposal) ) {
				  ?>
				  <option value="<?php echo $project_id; ?>"><?php echo get_the_title( $project_id ); ?></option>
				  <?php 
				}
			  endforeach; 
             endif; ?>
            </select>
        </label>
        </div>
      </div>
      <div class="modal-footer">
        <div class="btn-group">
         <input type="submit" class="btn large" name="submit_message" value="send">
         <a class="btn large" href="<?php echo add_query_arg(array('action' => 'messages'), SF_Users::user_profile_url()); ?>">go to messages</a>
       </div>
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php endif; ?>

<!-- Handle form submit -->
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
	<?php if ( isset($_GET['msg_form']) && $_GET['msg_form'] == 1 ) : ?>
	//Trigger Contact lightbox
	$('#contactModal').modal('show');
	<?php endif; ?>
	<?php if ( isset($_GET['msg_form']) && $_GET['msg_form'] == 2 ) : ?>
	//Trigger Contact lightbox
	$('#inviteJobModal').modal('show');
	<?php endif; ?>
});
</script>