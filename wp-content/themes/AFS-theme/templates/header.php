<header class="banner" role="banner">
  <div>
    <div class="top-nav-bar">
    <div class="container text-right">
      <ul class="social-nav pull-right">
        <li><a href=""><i class="fa fa-facebook"></i></a></li>
        <li><a href=""><i class="fa fa-twitter"></i></a></li>
        <li><a href=""><i class="fa fa-linkedin"></i></a></li>
        <li><a href=""><i class="fa fa-google-plus"></i></a></li>
      </ul>
      <nav role="navigation">
        <?php
          if (has_nav_menu('top_nav')) :
            wp_nav_menu(array('theme_location' => 'top_nav', 'menu_class' => 'top-nav'));
          endif;
        ?>
      </nav>
    </div>
    </div>
    <div class="container">
      <a class="logo" href="<?php echo home_url(); ?>/"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/NAFVA-logo.png" alt="Alliance Facility Solutions" /></a>
      <?php if ( is_user_logged_in() ) : 
	  	$current_user = get_userdata(get_current_user_id());
	  	?>
      <div class="pull-right second-nav-logged">
        <div class="username">
        	<i class="fa fa-user"></i>hello <?php echo $current_user->user_email; ?>
        </div>
        <span class="text-center"><a href="<?php echo SF_Users::user_profile_url(); ?>">my account</a> | <a href="<?php echo wp_logout_url(); ?>">logout</a></span>
      </div>
      
      <?php else : ?>
       <div class="pull-right second-nav">
        <a href="<?php echo home_url(SF_Users::LOGIN_PATH); ?>">Login</a> | <a href="<?php echo home_url(SF_Users::REGISTER_PATH); ?>">Register</a> | <a href="<?php echo home_url('search'); ?>">Search</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</header>