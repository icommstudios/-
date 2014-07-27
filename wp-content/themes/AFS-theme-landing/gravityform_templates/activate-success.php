<?php

global $gw_activate_template;

extract( $gw_activate_template->result );

$url = is_multisite() ? get_blogaddress_by_id( (int) $blog_id ) : home_url('', 'http');
$user = new WP_User( (int) $user_id );

?>

<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

  <!--[if lt IE 8]><div class="alert alert-warning"><?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'roots'); ?></div><![endif]-->

  <div class="wrap container" role="document">
    <div class="content row">
      <div class="main <?php echo roots_main_class(); ?>" role="main">
        <h2><?php _e('Your account is now active!'); ?></h2>

<div id="signup-welcome">
    <p><span class="h3"><?php _e('Username:'); ?></span> <?php echo $user->user_login ?></p>
    <p><span class="h3"><?php _e('Password:'); ?></span> <?php echo $password; ?></p>
</div>

<?php if ( $url != network_home_url('', 'http') ) : ?>
    <p class="view"><?php printf( __('Your account is now activated. <a href="%1$s">View your site</a> or <a href="%2$s">Log in</a>'), $url, $url . 'wp-login.php' ); ?></p>
<?php else: ?>
    <p class="view"><?php printf( __('Your account is now activated. <a href="%1$s">Log in</a> or go back to the <a href="%2$s">homepage</a>.' ), network_site_url('wp-login.php', 'login'), network_home_url() ); ?></p>
<?php endif; ?>
      </div><!-- /.main -->
      <?php if (roots_display_sidebar()) : ?>
        <aside class="sidebar <?php echo roots_sidebar_class(); ?>" role="complementary">
          <?php include roots_sidebar_path(); ?>
        </aside><!-- /.sidebar -->
      <?php endif; ?>
    </div><!-- /.content -->
  </div><!-- /.wrap -->

  <div class="navbar-fixed-bottom">
      <div class="container">
        <a class="footer-brand" href="<?php echo home_url(); ?>/"><?php bloginfo('name'); ?> <span>connecting qualified contractors with facilities</span></a>
      </div>
  </div>
  <?php get_template_part('templates/footer'); ?>

</body>
</html>