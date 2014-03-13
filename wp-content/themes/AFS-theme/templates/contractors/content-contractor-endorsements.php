<?php global $contractor_id; ?>
<?php
//Setup endorsement
if ( $_GET['add_endorsement'] ) {
	
	//Is project ready for endorsement?
	$project_id = (int)$_GET['add_endorsement'];
	$deadline = SF_Project::get_field( $project_id, 'deadline');
	$project_facility_id = SF_Project::get_facility_id_for_project($project_id);
	$project_contractor_id = SF_Project::get_contractor_id_for_project($project_id);
	if ( time() >= strtotime( $deadline ) ) {
		$project_finished = true;
	} else {
		$project_finished = false;
	}
	$existing_endorsement = SF_Project::get_field( $project_id, 'endorsement_id_by_contractor');
}
?>
<?php if ( $project_id ) : ?>

	<?php if ( ($project_contractor_id != $contractor_id) ) : //check logged in user ?>
    
    	<p>You are not authorized to complete this Endorsement form.</p>
    
    <?php elseif ( !$project_finished ) : ?>
    
    	<p>Project is not yet finished. Endorsement form not yet available.</p>
    
	<?php elseif ( !empty($existing_endorsement) ) : ?>
    
     	<p>Project endorsement has already been completed.</p>
        
	<?php elseif ( $project_finished && $project_contractor_id ) : ?>
    
	<h2>Here's your chance to praise a job well done or provide constructive feedback.</h2>
   
    <form role="form" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
    <input type="hidden" name="_related_project_id" value="<?php echo $project_id; ?>" />
    
    <p>Please rate your experience with <strong><?php echo get_the_title($project_facility_id); ?></strong>. (1 is bad, 5 is great)</p>
    
    <div class="form-group">
        <div>
            <input type="radio" name="_rating" <?php echo ($_POST['_rating'] == '1') ? 'checked="checked"' : ''; ?> value="1" class="" />
            <span class="rating-stars">
              <span class="fa fa-star"></span>
            </span>
        </div>
        <div>
            <input type="radio" name="_rating" <?php echo ($_POST['_rating'] == '2') ? 'checked="checked"' : ''; ?> value="2" class="" />
            <span class="rating-stars">
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
            </span>
        </div>
        <div>
            <input type="radio" name="_rating" <?php echo ($_POST['_rating'] == '3') ? 'checked="checked"' : ''; ?> value="3" class="" />
            <span class="rating-stars">
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
            </span>
        </div>
        <div>
            <input type="radio" name="_rating" <?php echo ($_POST['_rating'] == '4') ? 'checked="checked"' : ''; ?> value="4" class="" />
            <span class="rating-stars">
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
            </span>
        </div>
        <div>
            <input type="radio" name="_rating" <?php echo ($_POST['_rating'] == '5') ? 'checked="checked"' : ''; ?> value="5" class="" />
            <span class="rating-stars">
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
            </span>
        </div>
    </div>
    
    <hr />
    
    <p>Please answer these questions describing your experience.</p>
    <ol>
        <li>Did the Vendor listen well, understand fully your needs and satisfy your request?</li>
     <li>Did the vendor communicate effectively. (scopes, materials cost, lead times, start times, or project completion)</li>
     <li>Was vendor timely on pricing, scheduling of project or delivery of goods and service?</li>
     <li>Did Vendor provide desired results?</li>
     <li>Were there any disruptions to the flow of operations?</li>
     <li>Was work 100% completed, and are you satisfied with the product or service.</li>
     <li>Quality of Service or Product?</li>
     <li>Was this a good value in your opinion?</li>
     <li>Would you use this vendor again?</li>
    <li>Would you recommend this vendor to another Facility professional?</li>
    </ol>
    
    <div class="form-group">
    	<textarea name="comment" class="full-width" rows="3"></textarea>
    </div>
    <div class="form-group">
         <input type="submit" value="submit" name="submit" class="orange">
    </div>
    <?php comment_id_fields($project_facility_id); ?>
    <?php do_action('comment_form', $project_facility_id); ?>
    </form>
    
  <?php else : ?>
  
   <p>Endorsement form not available.</p>
   
  <?php endif; ?>
  
<?php else: ?>
    
    <h3>endorsements & ratings</h3>
    <?php
	$offset = ($_GET['offset']) ? $_GET['offset'] : 0;
    $args = array(
        'status' => 'approve',
       // 'offset' => $offset,
		'number' => 99,
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
    }
    ?>

<?php endif; ?>