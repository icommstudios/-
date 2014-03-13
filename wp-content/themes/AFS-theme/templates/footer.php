<footer class="footer-info container" role="contentinfo">
  <div class="row">
    <div class="col-lg-12">
      <?php dynamic_sidebar('sidebar-footer'); ?>
      <p>&copy; <?php echo date('Y'); ?> AFS, inc. | <a href="<?php echo home_url(SF_Users::REGISTER_PATH); ?>">Register for an account</a></p>
    </div>
  </div>
</footer>

<!-- Tiny Nav Javascript -->
<script>
  $(function () {
    $(".top-nav").tinyNav();
  });
</script>

<!-- PrettyPhoto -->
<script type="text/javascript" charset="utf-8">
  $(document).ready(function(){
    $("a[rel='prettyPhoto']").prettyPhoto();
  });
</script>

<?php wp_footer(); ?>