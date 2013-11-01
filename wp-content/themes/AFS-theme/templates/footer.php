<footer class="footer-info container" role="contentinfo">
  <div class="row">
    <div class="col-lg-12">
      <?php dynamic_sidebar('sidebar-footer'); ?>
      <p>&copy; <?php echo date('Y'); ?> AFS, inc. | <a href="#">Register for an account</a></p>
    </div>
  </div>
</footer>

<!-- Tiny Nav Javascript -->
<script>
  $(function () {
    $(".top-nav").tinyNav();
  });
</script>

<?php wp_footer(); ?>