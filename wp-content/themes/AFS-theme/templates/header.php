<header class="banner" role="banner">
  <div>
    <div class="top-nav-bar">
    <div class="col-lg-12 container text-right">
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
    <div class="col-lg-12 container">
      <a class="logo" href="<?php echo home_url(); ?>/"><img src="/2013/assets/img/AFS-logo.png" alt="Alliance Facility Solutions" /></a>
      <div class="pull-right second-nav">
        <a href="http://facilityvendor.com/2013/login/">Login</a> | <a href="http://facilityvendor.com/2013/register/">Register</a> | <a href="http://facilityvendor.com/2013/search/">Search</a>
      </div>
      <div class="pull-right second-nav-logged hidden">
        <div class="username">
        <i class="fa fa-user"></i>hello [username]
        </div>
        <span class="text-center"><a href="#">my account</a> | <a href="#">logout</a></span>
      </div>
    </div>
  </div>
</header>