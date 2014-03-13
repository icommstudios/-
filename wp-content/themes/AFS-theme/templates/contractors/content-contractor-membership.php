<?php global $contractor_id, $fields; ?>

<h3>membership type</h3>

	<?php if ( $fields['membership_type'] && time() < $fields['membership_expiration'] ) : ?>
    	
        <button class="btn append large orange"><i class="fa fa-thumbs-up border-right"></i>Paid Subscriber</button>
        <p>Your subscription renews on <?php echo date('m/d/Y', $fields['membership_expiration']); ?> for <?php echo '$'.SF_Users::$contractor_membership_types[$fields['membership_type']]['cost']; ?>. <a target="_blank" href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_manage-paylist">manage your subscription at Paypal.</a></p>
      
       <?php 
	   //Check for addons
	   echo 'categories: '.fv_get_contractor_membership_addon_categories ($contractor_id);
	   
	   $existing_addons = fv_get_contractor_membership_addons($contractor_id);
	   if ( $existing_addons ) {
		   	echo '<h4>active add-ons</h4>';
			foreach ( $existing_addons as $addon) {
				if ( $addon['addon'] && time() < $addon['expiration'] ) {
					$addon_label = SF_Users::$contractor_membership_types[$addon['addon']]['description'];
					?>
					Add-on: <?php echo $addon_label; ?> | Expires: <?php echo date('m-d-Y', $addon['expiration']); ?> | <a target="_blank" href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_manage-paylist">manage at Paypal.</a></p>
				   <?php
				}
			}
		   
	   } ?>
       
       <h4>purchase membership add-ons</h4>
       <?php 
	   //Show addons
	   foreach ( SF_Users::$contractor_membership_types as $each_key => $each ) : 
	   	if ( $each['type'] == 'addon' ) :
	   		?>
       		<a href="<?php echo add_query_arg(array('member_addon' => $each_key), $_SERVER['REQUEST_URI']); ?>" class="btn large"><?php echo $each['description']; ?></a>
	   <?php endif;
	   endforeach; ?>
        
       <p><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/paypal-logo.png"></p>  
      
         <?php if ( isset($_GET['member_addon']) ) : ?>
         	
            <?php
			 $new_membership = trim($_GET['member_addon']);
			 $button = '<button class="btn append large orange"><i class="fa fa-thumbs-up border-right"></i>'.'$'.SF_Users::$contractor_membership_types[$new_membership]['cost'].'/yr. Add-on</button>';
			 $label = SF_Users::$contractor_membership_types[$new_membership]['description'];
         	 $subscribe_form = SF_Users::get_new_contractor_subscription_form(get_current_user_id(), $contractor_id, $button, $new_membership);
			 ?>
             
			 <!-- membership Modal -->
            <div class="modal fade" id="addonMembershipModal" tabindex="-1" role="dialog" aria-labelledby="addonMembershipModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="addonMembershipModalLabel">Upgrade Your Membership</h4>
                  </div>
              <div class="modal-body">
               	 <p>Add the following Add-on to your membership:</p>
                	<ul class="list-group">
                        <li class="list-group-item"><?php echo $label; ?></li>
                        <li class="list-group-item">Increased Exposure</li>
                    </ul>
                <?php echo $subscribe_form; ?>
                
                  </div>
                  <div class="modal-footer">
                    <p><img src="../assets/img/paypal-logo.png"></p>
                  </div>
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
            
            <script type="text/javascript">
				$(document).ready(function(){
					$('#addonMembershipModal').modal('show');
				});
			</script>
             
             
         <?php endif; ?>
        
<?php else : ?>
    	
	<?php
    //Get the subscribe button
    $new_membership = 'C1'; //main
    $button = '<button class="btn append large orange"><i class="fa fa-thumbs-up border-right"></i>'.'$'.SF_Users::$contractor_membership_types[$new_membership]['cost'].'/yr. Paid Membership</button>';
    $subscribe_form = SF_Users::get_new_contractor_subscription_form(get_current_user_id(), $contractor_id, $button, $new_membership);
    ?>
    <a href="" class="btn large" data-toggle="modal" data-target="#membershipModal">Free Account | Click to Upgrade!</a>
    <p><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/paypal-logo.png"></p>
    
    <!-- membership Modal -->
    <div class="modal fade" id="membershipModal" tabindex="-1" role="dialog" aria-labelledby="membershipModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="membershipModalLabel">Upgrade Your Membership</h4>
          </div>
      <div class="modal-body">
        <p>Upgrade to our paid annual membership and enjoy the following benefits:</p>
        <ul class="list-group">
                <li class="list-group-item">3 Listing Categories</li>
                <li class="list-group-item">Increased Exposure</li>
            </ul>
        <?php echo $subscribe_form; ?>
        
          </div>
          <div class="modal-footer">
            <p><img src="../assets/img/paypal-logo.png"></p>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    
<?php endif; ?>