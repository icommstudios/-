<?php

define( 'WP_INSTALLING', true );

global $current_site;

// include GF User Registration functionality
require_once(GFUser::get_base_path() . '/includes/signups.php');

GFUserSignups::prep_signups_functionality();

do_action( 'activate_header' );

function do_activate_header() {
    do_action( 'activate_wp_head' );
}
add_action( 'wp_head', 'do_activate_header' );

function wpmu_activate_stylesheet() {
    ?>
    <style type="text/css">
        form { margin-top: 2em; }
        #submit, #key { width: 90%; font-size: 24px; }
        #language { margin-top: .5em; }
        .error { background: #f66; }
        span.h3 { padding: 0 8px; font-size: 1.3em; font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif; font-weight: bold; color: #333; }
    </style>
    <?php
}
add_action( 'wp_head', 'wpmu_activate_stylesheet' );

get_template_part('templates/head'); ?>

<div class="wrap container" role="document">
    <div class="content row">
        <div class="main <?php echo roots_main_class(); ?> text-center" role="main">
        <p><img alt="" src="/assets/img/AFS-logo.png" class="img-responsive" /></p>
    <?php if ( empty($_GET['key']) && empty($_POST['key']) ) { ?>

        <h2><?php _e('Activation Key Required') ?></h2>
        <form name="activateform" id="activateform" method="post" action="<?php echo network_site_url('?page=gf_activation'); ?>">
            <p>
                <label for="key"><?php _e('Activation Key:') ?></label>
                <br /><input type="text" name="key" id="key" value="" size="50" />
            </p>
            <p class="submit">
                <input id="submit" type="submit" name="Submit" class="submit" value="<?php esc_attr_e('Activate') ?>" />
            </p>
        </form>

    <?php } else {

        $key = !empty($_GET['key']) ? $_GET['key'] : $_POST['key'];
        $result = GFUserSignups::activate_signup($key);
        if ( is_wp_error($result) ) {
            if ( 'already_active' == $result->get_error_code() || 'blog_taken' == $result->get_error_code() ) {
                $signup = $result->get_error_data();
                ?>
                <h2><?php _e('Your account is now active!'); ?></h2>
                <?php
                echo '<p class="lead-in">';
                if ( $signup->domain . $signup->path == '' ) {
                    printf( __('Congratulations, your account has been activated. We will contact you when the site is ready to launch!'), network_site_url( 'wp-login.php', 'login' ), $signup->user_login, $signup->user_email, network_site_url( 'wp-login.php?action=lostpassword', 'login' ) );
                } else {
                    printf( __('Congratulations, your site at <a href="%1$s">%2$s</a> is active. We will contact you when the site is ready to launch!'), 'http://' . $signup->domain, $signup->domain, $signup->user_login, $signup->user_email, network_site_url( 'wp-login.php?action=lostpassword' ) );
                }
                echo '</p>';
            } else {
                ?>
                <h2><?php _e('An error occurred during the activation'); ?></h2>
                <?php
                echo '<p>'.$result->get_error_message().'</p>';
            }
        } else {
            extract($result);
            $url = is_multisite() ? get_blogaddress_by_id( (int) $blog_id) : home_url('', 'http');
            $user = new WP_User( (int) $user_id);
            ?>
            <h2><?php _e('Your account is now active!'); ?></h2>

            <div id="signup-welcome">
                <p><span class="h3"><?php _e('Username:'); ?></span> <?php echo $user->user_login ?></p>
                <p><span class="h3"><?php _e('Password:'); ?></span> <?php echo $password; ?></p>
            </div>
            
            <?php if ( $url != network_home_url('', 'http') ) : ?>
                <p class="view"><?php printf( __('Congratulations, your account is now activated. We will contact you when the site is ready to launch!'), $url, $url . 'wp-login.php' ); ?></p>
            <?php else: ?>
                <p class="view"><?php printf( __('Congratulations, your account is now activated. We will contact you when the site is ready to launch!' ), network_site_url('wp-login.php', 'login'), network_home_url() ); ?></p>
            <?php endif;
        }
    }
    ?>
</div>
</div>
</div>
<div class="navbar-fixed-bottom">
      <div class="container">
        <a class="footer-brand" href="<?php echo home_url(); ?>/"><?php bloginfo('name'); ?> <span>connecting qualified contractors with facilities</span></a>
      </div>
  </div>
<script type="text/javascript">
    var key_input = document.getElementById('key');
    key_input && key_input.focus();
</script>