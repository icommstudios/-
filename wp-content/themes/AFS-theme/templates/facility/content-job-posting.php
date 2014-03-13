<?php
$project_id = get_the_ID();
$facility_id = SF_Project::get_facility_id_for_project( $project_id ); 
$categories_permitted = fv_get_facility_membership_addon_categories($facility_id);
if ( $facility_id ) {
	$facility_name = get_post_meta($facility_id, '_name', true);
	$facility_listing = get_the_title($facility_id);
	$quality_verified = fv_get_facility_quality_verified ( $facility_id );
} else {
	$facility_name = '';
	$facility_listing = '';
	$quality_verified = false;
}
?>
<div class="row">
  <div class="col-lg-8">
      <div class="visible-xs">
    <a class="btn large full-width purple" href="#" data-toggle="modal" data-target="#contactModal">contact <?php echo ($facility_name) ? $facility_name : $facility_listing; ?></a>
	<?php if ( $quality_verified['completed'] && $quality_verified['completed'] == 100 ) : ?>
   	<span class="certified-badge"><i class="fa fa-trophy"></i>Gold Member Certified</span>
    <?php endif; ?>
    <hr />
  </div>
<ul class="list-inline details-meta">
  <li><i class="fa fa-calendar"></i> <?php echo get_the_time('m/d/Y', get_the_ID()); ?></li>
  <li><i class="fa fa-money"></i> $<?php echo str_replace('$', '', get_post_meta(get_the_ID(), '_budget', true)); ?></li> 
  <li><i class="fa fa-clock-o"></i> Deadline is <?php echo get_post_meta(get_the_ID(), '_deadline', true); ?></li> 
</ul>
<?php if ( has_post_thumbnail() ) : ?>
<div class="featured-img">
  <?php the_post_thumbnail('img_750'); ?>
</div>
<?php endif; ?>
<?php the_content(); ?>
<hr>
<div class="photo-group">
<h4>Project Photos</h4>
  <?php
	//Loop photos
	$photo_attachments = SF_Project::load_attachments($project_id);
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
<div class="col-lg-4 hidden-xs">
  <div>
    <a class="btn large full-width purple" href="#" data-toggle="modal" data-target="#contactModal">contact <?php echo ($facility_name) ? $facility_name : $facility_listing; ?></a>
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
		'post_type' => SF_Project::POST_TYPE,
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
        <h4 class="modal-title" id="contactModalLabel">Contact <?php echo ($facility_name) ? $facility_name : $facility_listing; ?></h4>
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

<!-- Handle form submit -->
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
	<?php if ( isset($_GET['msg_form']) ) : ?>
	//Trigger Contact lightbox
	$('#contactModal').modal('show');
	<?php endif; ?>
});
</script>