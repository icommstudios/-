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
      <nav class="pull-right" role="navigation">
        <?php
          if (has_nav_menu('top_nav')) :
            wp_nav_menu(array('theme_location' => 'top_nav', 'menu_class' => 'top-nav'));
          endif;
        ?>
      </nav>
    </div>
    </div>
    <div class="col-lg-12 container">
      <a class="logo pull-left" href="<?php echo home_url(); ?>/"><img src="/2013/assets/img/AFS-logo.png" alt="Alliance Facility Solutions" /></a>
      <div class="pull-right second-nav">
        <a href="#">Login</a> | <a href="#">Register</a> | <a href="#">Search</a></li>
      </div>
    </div>
  </div>
</header>