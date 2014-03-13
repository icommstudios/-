<?php global $contractor_id, $fields, $user_fields; 
//Is quality verified
$quality_verified = fv_get_contractor_quality_verified ( $contractor_id, $fields);
?>
<div class="text-center">
    <strong>Become Quality Verified!</strong>
    <div class="progress">
      <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo (int)$quality_verified['completed']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo ($quality_verified['completed'] >= 15 ) ? (int)$quality_verified['completed'] : 15; ?>%;">
        <span class="percentage"><?php echo (int)$quality_verified['completed']; ?>%</span>
      </div>
    </div>
    <?php if ( $quality_verified['completed'] && $quality_verified['completed'] == 100 ) : ?>
    <a href="#"  data-toggle="modal" data-target="#verified-info-Modal">more info</a>
    <?php else: ?>
    <a href="#" data-toggle="modal" data-target="#tasksModal">see remaining tasks</a> | <a href="#"  data-toggle="modal" data-target="#verified-info-Modal">more info</a>
    <?php endif; ?>
</div>

<!-- Tasks Modal -->
<div class="modal fade" id="tasksModal" tabindex="-1" role="dialog" aria-labelledby="contactModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="ModalLabel">Complete Your Profile to become Quality Verified!</h4>
      </div>
  <div class="modal-body">
  	<p>Remaining tasks:</p>
        <ul class="list-group">
        	<?php if ( empty($quality_verified['profile'])) : ?><li class="list-group-item">Basic Profile Info <span class="badge">60%</span></li><?php endif; ?>
			<?php if ( empty($quality_verified['photo'])) : ?><li class="list-group-item">Add Photos <span class="badge">10%</span></li><?php endif; ?>
			<?php if ( empty($quality_verified['website'])) : ?><li class="list-group-item">Biz Web Address <span class="badge">10%</span></li><?php endif; ?>
			<?php if ( empty($quality_verified['bbb_url'])) : ?><li class="list-group-item">BBB link <span class="badge">10%</span></li><?php endif; ?>
			<?php if ( empty($quality_verified['contractor_license'])) : ?><li class="list-group-item">Contractor Lic. <span class="badge">10%</span></li><?php endif; ?>
        </ul>
      </div>
      <div class="modal-footer">
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Verified Info Modal -->
<div class="modal fade" id="verified-info-Modal" tabindex="-1" role="dialog" aria-labelledby="contactModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title text-center" id="ModalLabel"><span class="certified-badge"><i class="fa fa-trophy"></i></span> Why should I become Quality Verified?</h4>
      </div>
  <div class="modal-body">
        <p>Quality verification is our most prestigious award.  Once verified, we will award you with a seal on your profile that lets everyone know your achievement.</p>
      </div>
      <div class="modal-footer">
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->