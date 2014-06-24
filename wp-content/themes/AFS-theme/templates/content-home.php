<div class="img-hero overflow home-hero img-hero-bar">
	<h1>Welcome to the National Alliance of Facility Vendors Association, where we make it easy for facility owners and managers to find qualified, licensed contractors.</h1>
</div>
<div class="hero orange-hero overflow">
	<div class="container">
	<div class="row">
		<div class="col-sm-4"><a href="<?php echo home_url('search'); //.'?s=&contractors_filter=1&distance=50' add_query_arg (array('precheck_contractor' => 1), home_url('search')); ?>" class="btn x-large white blue-text centered">Search for Vendors</a></div>
		<div class="col-sm-4"><img class="centered handshake-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/handshake.png" /></div>
		<div class="col-sm-4"><a href="<?php echo home_url('search'); //.'?s=&job_postings_filter=1&distance=50' add_query_arg (array('precheck_job' => 1), home_url('search')); ?>" class="btn x-large white purple-text centered">Search for Facility Jobs</a></div>
	</div>
	</div>
</div>
<?php
//Build args
	$args = array(
		'post_type' => array(SF_Project::POST_TYPE, SF_Contractor::POST_TYPE),
		'posts_per_page' => 10,
		'order' => 'DESC',
		'orderby' => 'date',
	);
	$recent_query = new WP_Query( $args );
	?>
<div class="news-ticker overflow">
	<div class="container">
	<div class="row">
	<span>Recent Activity</span>
	<div class="ticker-container">
	<ul class="ticker">
     <?php if ( $recent_query->have_posts() ) : ?>
     	<?php $count; while ( $recent_query->have_posts() ) : $recent_query->the_post(); $count++; 
			$listing_icon = ( get_post_type ( get_the_ID() ) == SF_Project::POST_TYPE ) ? '<i class="fa fa-pencil-square-o"></i>' : '<i class="fa fa-briefcase"></i>';
			$listing_label = get_the_title( get_the_ID() );
			$listing_location = get_post_meta ( get_the_ID(), '_location', true);
			$listing_date = get_the_time ( 'm-d-Y', get_the_ID() );
			//Add to label
			$listing_label .= ( $listing_location ) ? ' | '.$listing_location : '';
			$listing_label .= ( $listing_date ) ? ' | '.$listing_date : '';
		?>
		<li><a href="<?php echo get_permalink( get_the_ID() ); ?>" class="<?php echo get_post_type ( get_the_ID() ); ?>"><?php echo $listing_icon; ?> <?php echo $listing_label; ?></a></li>
        <?php endwhile; ?>
	<?php endif; ?>
    </ul>
	</div>
	</div>
	</div>
</div>

<div class="container text-center">
	<section>
	<h2>How Alliance Works</h2>
	<div class="row">
		<div class="col-sm-3"><a href="<?php echo home_url(SF_Users::REGISTER_PATH); ?>" class="round-icon-blocks"><i class="fa fa-user round-icons"></i><p>Register an Account</p></a></div>
		<div class="col-sm-3"><a href="<?php echo home_url('search'); ?>" class="round-icon-blocks"><i class="fa fa-th-list round-icons"></i><p>Post or Search</p></a></div>
		<div class="col-sm-3"><a href="#" class="round-icon-blocks"><i class="fa fa-check-square-o round-icons"></i><p>Award or Accept</p></a></div>
		<div class="col-sm-3"><a href="#" class="round-icon-blocks"><i class="fa fa-star round-icons"></i><p>Review & Repeat!</p></a></div>
	</div>
	</section>
	<section>
	<div class="row">
		<div class="col-sm-12">
			<a href="#" class="round-icon-blocks"><i class="fa fa-envelope append round-icons"></i>
			<h2>Refer a friend or colleague to Alliance Facility Solutions.</h2>
		    </a>
			<!-- Begin MailChimp Signup Form -->
			<div id="mc_embed_signup">
			<form action="http://facilityvendor.us7.list-manage.com/subscribe/post?u=b9dc1c9eb8e5323e8c5b52587&amp;id=fd136013ac" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				
			<div class="mc-field-group">
				<div class="addon-icon centered">
					<span class="add-on"><i class="fa fa-envelope"></i></span>
					<input type="email" value="enter valid email" name="EMAIL" class="required lg-input email clear-val" id="mce-EMAIL">
				</div>
				<input type="submit" value="send invitation" name="subscribe" id="mc-embedded-subscribe" class="btn large orange subscribe">
			</div>
				<div id="mce-responses" class="clear">
					<div class="response" id="mce-error-response" style="display:none"></div>
					<div class="response" id="mce-success-response" style="display:none"></div>
				</div>
			</form>
			</div>

			<!--End mc_embed_signup-->
			<p class="ghosted-note centered">We will send an invitation email to your friend or colleague to confirm their interest.  Thanks for spreading the love.</p>
		</div>
	</div>
	</section>
</div>