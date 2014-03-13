<div class="dash_section charity_dash_section">
	
    <h2 class="section_heading background_alt gb_ff"><?php echo get_the_title($charity_id); ?>: <?php gb_e('Recent Donations'); ?> <a class="section_heading_link font_x_small alt_link"  href="<?php gb_charity_purchases_report_url($charity_id) ?>" title="<?php gb_e('View all Donations'); ?>"><?php gb_e('See All&#63;'); ?></a></h2>
		
    
    <?php
	
		//Filter args
		$filter_args = array(
			'order' => 'DESC', 
			'orderby' => 'date', 
			'posts_per_page' => 5, // return this many
			
		);

       	$purchases = GB_SF_Charities::get_purchase_by_charity_filter( $charity_id, $filter_args );

       	if ( !empty( $purchases ) ) {
            ?>
            <table class="purchase_table vouchers_table gb_table purchases"><!-- Begin .gb_table -->

                <thead>
                    <tr>
                        <th class="purchase_deal_title th_voucher"><?php gb_e('Date'); ?></th>
                        <th class="th_status"><?php gb_e('Donation Amount'); ?></th>
                    </tr>
                </thead>
                
                <tbody>
                <?php
               foreach ( $purchases as $purchase_id ) :
			   		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
                    ?>
                    <tr>
                    	<td class="purchase_deal_title">
                            <?php
                               echo date( 'F j\, Y H:i:s', get_the_time( 'U', $purchase_id ) );
                            ?>
                        </td>
                        <td class="">
                          <?php echo gb_get_formatted_money( GB_SF_Charities::get_purchase_charity_donation_amount($purchase) ); ?>
                        </td>
                       
                    </tr>
                    <?php
                endforeach;
                ?>
                </tbody>
            </table><!-- End .gb_table -->
            <?php if ($vouchers->found_posts > 5): ?>
                <p><?php gb_e('This is a summary of your most recent donations.'); ?></p>
            <?php endif ?>

            <?php
        } else {
            ?>
                <p><?php gb_e('You have not received any donations yet.'); ?></p>
            <?php
        }

    ?>
   
</div>