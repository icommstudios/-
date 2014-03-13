<?php
global $facility_id, $user_fields;

//Get messages
$messages_to_id = $facility_id;
//Build args
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
	'post_type' => SF_Message::POST_TYPE,
	'posts_per_page' => 10,
	'paged' => $paged,
	'order' => 'DESC',
	'orderby' => 'date',
	'meta_query' => array(
		array(
		'key'     => '_to_id',
		'value'   => $messages_to_id,
		'compare' => '='
		)
	)
);
$custom_query = new WP_Query( $args );
?>
<h3>messages</h3>
<?php if ( empty($messages_to_id) || !$custom_query->have_posts() ) : ?>
	<p>No messages.</p>
<?php else : ?>
	<?php $count; while ( $custom_query->have_posts() ) : $custom_query->the_post(); $count++; ?>
    <?php
		$message_id = get_the_ID();
	  	$from_id = get_post_meta($message_id, '_from_id', true);
		$type = get_post_meta($message_id, '_type', true);
		$related_project_id = get_post_meta($message_id, '_related_project_id', true);
		$related_project_action = get_post_meta($message_id, '_related_project_action', true);
		//Type of message
		if ($type == '' || $type == 'message') {
	  	?>
     <article class="content-item general msg_message">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary"><?php echo wp_trim_excerpt(get_post_field('post_content', $message_id)); ?></p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo get_permalink($from_id); ?>" rel="author" class="fn"><?php echo get_the_title($from_id); ?></a></p>
      </div>
      <a href="#" data-replyto="<?php echo $from_id; ?>" data-replymsgid="<?php echo $message_id; ?>" data-toggle="modal" data-target="#contactModal" class="btn msg_reply">reply</a>
    </article>
		<?php
		} elseif ($type == 'project_invite') {
	  	?>
     <article class="content-item general msg_project_invite">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary">Congrats, you've been invited to a project.</p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo get_permalink($from_id); ?>" rel="author" class="fn"><?php echo get_the_title($from_id); ?></a></p>
      </div>
      <span style="display: none;" class="related_project_title"><?php echo get_the_title($related_project_id); ?></span>
      <?php if ( $related_project_action == 'project_accept' ) : ?>
      	<span class="btn btn-success">accepted</span>
      <?php elseif ( $related_project_action == 'project_decline' ) : ?>
      	<span class="btn btn-danger">declined</span>
      <?php else : ?>
          <a href="#" data-replyto="<?php echo $from_id; ?>" data-replymsgid="<?php echo $message_id; ?>" data-project="<?php echo $related_project_id; ?>" data-toggle="modal" data-target="#projectAcceptModal" class="btn btn-success msg_accept">accept</a>
          <a href="#" data-replyto="<?php echo $from_id; ?>" data-replymsgid="<?php echo $message_id; ?>" data-project="<?php echo $related_project_id; ?>" data-toggle="modal" data-target="#projectDeclineModal" class="btn btn-danger msg_deny">deny</a>
      <?php endif; //end if already accepted or declined ?>
    </article>
    <?php
		} elseif ($type == 'project_decline') {
	  	?>
     <article class="content-item general msg_project_decline">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary">project has been declined.</p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo get_permalink($from_id); ?>" rel="author" class="fn"><?php echo get_the_title($from_id); ?></a></p>
      </div>
      <a href="#" data-replyto="<?php echo $from_id; ?>" data-replymsgid="<?php echo $message_id; ?>" data-toggle="modal" data-target="#contactModal" class="btn msg_reply">reply</a>
    </article>
    <?php
		} elseif ($type == 'project_accept') {
	  ?>
     <article class="content-item general msg_project_accept">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary">project has been accepted.</p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo get_permalink($from_id); ?>" rel="author" class="fn"><?php echo get_the_title($from_id); ?></a></p>
      </div>
      <a href="#" data-replyto="<?php echo $from_id; ?>" data-replymsgid="<?php echo $message_id; ?>" data-toggle="modal" data-target="#contactModal" class="btn msg_reply">reply</a>
    </article>
    <?php
        } elseif ($type == 'proposal_send') {
			//has proposal id
			$related_proposal_id = get_post_meta($message_id, '_related_proposal_id', true);
			$related_project_status = fv_get_project_status( $related_project_id );
      ?>
     <article class="content-item general msg_project_accept">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary">content to be provided by client....</p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo get_permalink($from_id); ?>" rel="author" class="fn"><?php echo get_the_title($from_id); ?></a></p>
      </div>
      <?php if ( $related_project_status == 'new' ) : ?>
     <a href="<?php echo add_query_arg(array('action' => 'jobs', 'prop_accept' => $related_proposal_id, 'prop_p' => $related_project_id), SF_Users::user_profile_url()); ?>" class="btn">assign this contractor</a>
     <?php endif; ?>
 	 <a href="#" data-replyto="<?php echo $from_id; ?>" data-replymsgid="<?php echo $message_id; ?>" data-toggle="modal" data-target="#contactModal" class="btn msg_reply">reply</a>   </article>
    <?php
		} elseif ($type == 'project_completed') {
	 ?>
     <article class="content-item general msg_project_completed">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary">project is complete.</p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo get_permalink($from_id); ?>" rel="author" class="fn"><?php echo get_the_title($from_id); ?></a></p>
      </div>
      <a href="<?php echo add_query_arg(array('action' => 'endorsements', 'add_endorsement' => $related_project_id), SF_Users::user_profile_url()); ?>" class="btn">Click here to describe your experience</a>
    </article>
     <?php
		} elseif ($type == 'welcome') {
	 ?>
     <article class="content-item general msg_welcome">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary">Welcome to NAFVA.com.</p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo home_url(); ?>" rel="author" class="fn"><?php echo get_bloginfo( 'name' ); ?></a></p>
      </div>
    </article>
	<?php
		} else {
	?>
     <article class="content-item general msg_message">
        <h4 class="content-title"><a href="#"><?php echo get_the_title($message_id); ?></a></h4>
        <p class="content-summary"><?php echo wp_trim_excerpt(get_post_field('post_content', $message_id)); ?></p>
      <div class="content-meta">
        <p class="author">from <a href="<?php echo get_permalink($from_id); ?>" rel="author" class="fn"><?php echo get_the_title($from_id); ?></a></p>
      </div>
      <a href="#" data-replyto="<?php echo $from_id; ?>" data-replymsgid="<?php echo $message_id; ?>" data-toggle="modal" data-target="#contactModal" class="btn msg_reply">reply</a>
      <span>unkonwn message type</span>
    </article>
    <?php } 
 	endwhile;
  endif; //End if has messages
?>
 <hr />
    
<?php if ($custom_query->max_num_pages > 1) : ?>
	<?php wp_pagination($custom_query); ?>
<?php endif; ?>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="fv_message_send" role="form" method="post" action="<?php echo add_query_arg(array('msg_form' => 1), $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="fv_message_send" value="1" />
        <input type="hidden" name="to" value="<?php echo esc_attr(stripslashes($_POST['to'])); ?>" />
        <input type="hidden" name="reply_message_id" value="<?php echo esc_attr(stripslashes($_POST['reply_message_id'])); ?>" />
        <?php wp_nonce_field( 'fv_message_send_nonce', 'fv_message_send_nonce' ); ?> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="contactModalLabel">Contact</h4>
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
         <a class="btn large" data-dismiss="modal" href="#">cancel</a>
       </div>
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Project Accept Modal -->
<div class="modal fade" id="projectAcceptModal" tabindex="-1" role="dialog" aria-labelledby="projectAcceptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="fv_project_message_send" role="form" method="post" action="<?php echo add_query_arg(array('msg_form' => 2), $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="fv_message_send" value="1" />
        <input type="hidden" name="to" value="<?php echo esc_attr(stripslashes($_POST['to'])); ?>" />
        <input type="hidden" name="related_project_id" value="<?php echo esc_attr(stripslashes($_POST['related_project_id'])); ?>" />
        <input type="hidden" name="type" value="project_accept" />
        <input type="hidden" name="reply_message_id" value="<?php echo esc_attr(stripslashes($_POST['reply_message_id'])); ?>" />
        <?php wp_nonce_field( 'fv_message_send_nonce', 'fv_message_send_nonce' ); ?> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="projectAcceptModalLabel">Accept Job</h4>
      </div>
      <div class="modal-body">
         <p class="modal-title" id="displayProjectAcceptTitle">You are accepting this Project</p>
      </div>
      <div class="modal-body">
        <textarea class="full-width" rows="6" name="post_content" placeholder="type your message"><?php echo esc_textarea(stripslashes($_POST['post_content'])); ?></textarea>
      </div>
      <div class="modal-footer">
        <div class="btn-group">
         <input type="submit" class="btn large" name="submit_message" value="send">
         <a class="btn large" data-dismiss="modal" href="#">cancel</a>
       </div>
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Project Accept Modal -->
<div class="modal fade" id="projectDeclineModal" tabindex="-1" role="dialog" aria-labelledby="projectDeclineModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="fv_project_message_send" role="form" method="post" action="<?php echo add_query_arg(array('msg_form' => 3), $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="fv_message_send" value="1" />
        <input type="hidden" name="to" value="<?php echo esc_attr(stripslashes($_POST['to'])); ?>" />
        <input type="hidden" name="related_project_id" value="<?php echo esc_attr(stripslashes($_POST['related_project_id'])); ?>" />
        <input type="hidden" name="type" value="project_decline" />
        <input type="hidden" name="reply_message_id" value="<?php echo esc_attr(stripslashes($_POST['reply_message_id'])); ?>" />
        <?php wp_nonce_field( 'fv_message_send_nonce', 'fv_message_send_nonce' ); ?> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="projectDeclineModalLabel">Decline Job</h4>
      </div>
      <div class="modal-body">
         <p class="modal-title" id="displayProjectDeclineTitle">You are declining this Project</p>
      </div>
      <div class="modal-body">
        <textarea class="full-width" rows="6" name="post_content" placeholder="type your message"><?php echo esc_textarea(stripslashes($_POST['post_content'])); ?></textarea>
      </div>
      <div class="modal-footer">
        <div class="btn-group">
         <input type="submit" class="btn large" name="submit_message" value="send">
         <a class="btn large" data-dismiss="modal" href="#">cancel</a>
       </div>
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Handle form submit -->
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
	<?php if ( isset($_GET['msg_form']) && $_GET['msg_form'] == 1 ) : ?>
	//Trigger Contact lightbox
	$('#contactModal').modal('show');
	<?php elseif ( isset($_GET['msg_form']) && $_GET['msg_form'] == 2 ) : ?>
	//Trigger Contact lightbox
	$('#projectAcceptModal').modal('show');
	<?php elseif ( isset($_GET['msg_form']) && $_GET['msg_form'] == 3 ) : ?>
	//Trigger Contact lightbox
	$('#projectDeclineModal').modal('show');
	<?php endif; ?>
	
	//Set form data for Replys
	$("a.msg_reply").on("click", function () {
		 var reply_to = $(this).data('replyto');
		 var reply_message_id = $(this).data('replymsgid');
		 var article = $(this).closest( "article" );
		 var reply_title = article.find('h4.content-title a').html();
		 var reply_name = article.find('.author a').html();
		 //Set modal label
		 $("#contactModal #contactModalLabel").html( 'Reply to ' + reply_name );
		 //Set form fields
		 $("#contactModal input[name=to]").val( reply_to );
		 $("#contactModal input[name=reply_message_id]").val( reply_message_id );
		 $("#contactModal input[name=post_title]").val( 'RE: ' + reply_title );
		 $("#contactModal input[name=post_content]").val( '' ); //set blank
	});
	
	//Set form data for Project Accept
	$("a.msg_accept").on("click", function () {
		 var reply_to = $(this).data('replyto');
		 var reply_message_id = $(this).data('replymsgid');
		 var project_id = $(this).data('project');
		 var reply_to = $(this).data('replyto');
		 var article = $(this).closest( "article" );
		 var project_title = article.find('.related_project_title').html();
		 var reply_name = article.find('.author a').html();
		 //Set modal label
		 $("#projectAcceptModal #displayProjectAcceptTitle").html( 'I accept this Job: ' + project_title );
		 //Set form fields
		 $("#projectAcceptModal input[name=to]").val( reply_to );
		 $("#projectAcceptModal input[name=reply_message_id]").val( reply_message_id );
		 $("#projectAcceptModal input[name=post_content]").val( '' );
		 $("#projectAcceptModal input[name=related_project_id]").val( project_id );
	});
	
	//Set form data for Project Decline
	$("a.msg_deny").on("click", function () {
		 var reply_to = $(this).data('replyto');
		 var reply_message_id = $(this).data('replymsgid');
		 var project_id = $(this).data('project');
		 var reply_to = $(this).data('replyto');
		 var article = $(this).closest( "article" );
		 var project_title = article.find('.related_project_title').html();
		 var reply_name = article.find('.author a').html();
		 //Set modal label
		 $("#projectDeclineModal #displayProjectDeclineTitle").html( 'I decline this Job: ' + project_title );
		 //Set form fields
		 $("#projectDeclineModal input[name=to]").val( reply_to );
		 $("#projectDeclineModal input[name=reply_message_id]").val( reply_message_id );
		 $("#projectDeclineModal input[name=post_content]").val( '' );
		 $("#projectDeclineModal input[name=related_project_id]").val( project_id );
	});
});
</script>