<?php

//Get current user type id
function fv_get_current_user_type_id ( $user_id = null ) {
	//Get user_id
	if ( !$user_id ) {
		$user_id =  get_current_user_id();
	}
	if ( !$user_id ) return false;
	
	$user_type = get_user_meta( $user_id, SF_Users::USER_TYPE_META_KEY, true);
	$user_type_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
	
	if ( $user_type == SF_Users::USER_TYPE_FACILITY ) {
		//$facility_id = SF_Factility::get_facility_id_for_user( $user_id );
		return array('user_type' => SF_Users::USER_TYPE_FACILITY, 'user_type_id' => $user_type_id);
	} elseif ( $user_type == SF_Users::USER_TYPE_CONTRACTOR ) {
		//$contractor_id = SF_Contractor::get_contractor_id_for_user( $user_id );
		return array('user_type' => SF_Users::USER_TYPE_CONTRACTOR, 'user_type_id' => $user_type_id);
	}
	return false;
}

//Get user_id for account (contractor or facility) id
function fv_get_user_id_for_account ( $id = null ) {
	if ( !$id ) {
		return;
	}
	if ( get_post_type( $id ) == SF_Contractor::POST_TYPE ) {
		return SF_Contractor::get_user_id_for_contractor($id);
	} elseif ( get_post_type( $id ) == SF_Facility::POST_TYPE ) {
		return SF_Facility::get_user_id_for_facility($id);
	}
	return false;
}

//Get membership type
function fv_get_facility_membership_type( $id = null, $include_expired = false ) {
	if ( !$id ) return;
	
	$type = SF_Facility::get_field($id, 'membership_type');
	if ( $include_expired ) {
		return $type;
	} 
	$exp = SF_Facility::get_field($id, 'membership_expiration');
	if ( $type && time() < ($exp + 86400) ) { //expiration +1 day leniency
		return $type;
	} else {
		return false;
	}
}
function fv_get_contractor_membership_type( $id = null, $include_expired = false ) {
	if ( !$id ) return;
	
	$type = SF_Contractor::get_field($id, 'membership_type');
	if ( $include_expired ) {
		return $type;
	} 
	$exp = SF_Contractor::get_field($id, 'membership_expiration');
	if ( $type && time() < ($exp + 86400) ) { //expiration +1 day leniency
		return $type;
	} else {
		return false;
	}
}

//Get membership addons
function fv_get_facility_membership_addons( $id = null ) {
	if ( !$id ) return;
	
	//Loop existing data
	$addon_data_list = SF_Facility::get_field_multiple($id, 'membership_addon_data');
	if ( !empty($addon_data_list) ) {
		foreach ( $addon_data_list as $addon_key => $addon_list ) {
			
			if ( $addon_list['addon'] && time() < ($addon_list['expiration'] + 86400) ) { //expiration +1 day leniency
				//Get data from system
				$addon_data_list[$addon_key]['addon_data'] = SF_Users::$facility_membership_types[$addon_list['addon']];
			} else {
				//Expired, remove from list
				unset($addon_data_list[$addon_key]);
			}
		}
	}
	if ( $addon_data_list && !empty($addon_data_list) ) {
		return $addon_data_list;
	} else {
		return false;
	}
}
function fv_get_contractor_membership_addons( $id = null ) {
	if ( !$id ) return;
	
	//Loop existing data
	$addon_data_list = SF_Contractor::get_field_multiple($id, 'membership_addon_data');
	if ( !empty($addon_data_list) ) {
		foreach ( $addon_data_list as $addon_key => $addon_list ) {
			if ( $addon_list['addon'] && time() < ($addon_list['expiration'] + 86400) ) { //expiration +1 day leniency
				//Get data from system
				$addon_data_list[$addon_key]['addon_data'] = SF_Users::$contractor_membership_types[$addon_list['addon']];
			} else {
				//Expired, remove from list
				unset($addon_data_list[$addon_key]);
			}
		}
	}
	if ( $addon_data_list && !empty($addon_data_list) ) {
		return $addon_data_list;
	} else {
		return false;
	}
}
function fv_get_facility_membership_addon_categories( $id = null ) {
	if ( !$id ) return;
	
	//if has valid membership
	$membership = fv_get_facility_membership_type($id);
	if ( $membership ) {
		$number_categories = 3; //start with categories
	} else {
		return 1; //all are permitted 1
	}
	
	//Loop existing data
	$addon_data_list = fv_get_facility_membership_addons($id);
	if ( !empty($addon_data_list) ) {
		foreach ( $addon_data_list as $addon_key => $addon_list ) {
			$addon_cat_data = SF_Users::$facility_membership_types[$addon_list['addon']]['data'];
			$number_categories = $number_categories + intval($addon_cat_data);
		}
	}
	return $number_categories;
}
function fv_get_contractor_membership_addon_categories( $id = null ) {
	if ( !$id ) return;
	
	//if has valid membership
	$membership = fv_get_contractor_membership_type($id);
	if ( $membership ) {
		$number_categories = 3; //start with categories
	} else {
		return 1; //all are permitted 1
	}
	
	//Loop existing data
	$addon_data_list = fv_get_contractor_membership_addons($id);
	if ( !empty($addon_data_list) ) {
		foreach ( $addon_data_list as $addon_key => $addon_list ) {
			$addon_cat_data = SF_Users::$contractor_membership_types[$addon_list['addon']]['data'];
			$number_categories = $number_categories + intval($addon_cat_data);
		}
	}
	return $number_categories;
}



//Get all fields
function fv_get_facility_fields( $id = null ) {
	if ( !$id ) return array();
	
	//Load fields
	$fields = array();
	$meta_keys = SF_Facility::field_keys();
	foreach ( $meta_keys as $meta_key_name => $meta_key_value ) {
		$fields[$meta_key_name] = get_post_meta($id, $meta_key_value, TRUE);
	}
	return $fields;
}
function fv_get_contractor_fields( $id = null ) {
	if ( !$id ) return array();
	
	//Load fields
	$fields = array();
	$meta_keys = SF_Contractor::field_keys();
	foreach ( $meta_keys as $meta_key_name => $meta_key_value ) {
		$fields[$meta_key_name] = get_post_meta($id, $meta_key_value, TRUE);
	}
	return $fields;
}
function fv_get_project_fields( $id = null ) {
	if ( !$id ) return array();
	
	//Load fields
	$fields = array();
	$meta_keys = SF_Project::field_keys();
	foreach ( $meta_keys as $meta_key_name => $meta_key_value ) {
		$fields[$meta_key_name] = get_post_meta($id, $meta_key_value, TRUE);
	}
	return $fields;
}

//Get star rating
function fv_get_facility_star_rating( $id = null ) {
	if ( !$id ) return;
	
	$args = array(
		'post_id' => $id, // use post_id, not post_ID
		'status' => 'approve',
		'meta_key' => '_rating',
		'meta_value' => '',
	);
	$comments = get_comments($args);
	$rating = 0; //start as 0 star
	$rating_total = 0;
	$rating_count = 0;
	if ( $comments ) {
		foreach ( $comments as $c ) {
			$rating_count++;
			$rating_total += $c->meta_value;
		}
		if ( $rating_total ) {
			$rating = round($rating_total / $rating_count);
		}
		return $rating;
	} else {
		return 0;
	}
}
function fv_get_contractor_star_rating( $id = null ) {
	if ( !$id ) return;
	
	$args = array(
		'post_id' => $id, // use post_id, not post_ID
		'status' => 'approve',
		'meta_key' => '_rating',
		'meta_value' => '',
	);
	$comments = get_comments($args);
	$rating = 1; //start as 1 star
	$rating_total = 0;
	$rating_count = 0;
	if ( $comments ) {
		foreach ( $comments as $c ) {
			$rating_count++;
			$rating_total += $c->meta_value;
			
		}
		if ( $rating_total ) {
			$rating = round($rating_total / $rating_count);
		}
		return $rating;
	} else {
		return 1;
	}
}

//Determine if facility or contractor is quality verified
function fv_get_facility_quality_verified( $id = null, $fields = null ) {
	//If we already have fields, don't look up again
	if ( empty($fields) ) {
		$fields = fv_get_facility_fields( $id );
	}
	//Determine if quality verified
	$quality_verified = array();
	if ( !empty( $fields['name'] ) && !empty( $fields['company'] ) && !empty( $fields['email'] ) ) {
		$quality_verified['profile'] = 70;
	}
	if ( has_post_thumbnail( $id ) ) {
		$quality_verified['photo'] = 10;
	}
	$membership_type = fv_get_facility_membership_type( $id );
	if ( $membership_type ) {
		if ( !empty( $fields['website'] ) ) {
			$quality_verified['website'] = 10;
		}
		if ( !empty( $fields['bbb_url'] ) ) {
			$quality_verified['bbb_url'] = 10;
		}
	}
	//Count completed
	$quality_verified['completed'] = 0;
	foreach ( $quality_verified as $each ) {
		$quality_verified['completed']	+= $each;
	}
	return $quality_verified;
}

function fv_get_contractor_quality_verified( $id = null, $fields = null ) {
	//If we already have fields, don't look up again
	if ( empty($fields) ) {
		$fields = fv_get_contractor_fields( $id );
	}
	//Determine if quality verified
	$quality_verified = array();
	if ( !empty( $fields['name'] ) && !empty( $fields['company'] ) && !empty( $fields['email'] ) ) {
		$quality_verified['profile'] = 60;
	}
	if ( has_post_thumbnail( $id ) ) {
		$quality_verified['photo'] = 10;
	}
	$membership_type = fv_get_contractor_membership_type( $id );
	if ( $membership_type ) {
		if ( !empty( $fields['website'] ) ) {
			$quality_verified['website'] = 10;
		}
		if ( !empty( $fields['bbb_url'] ) ) {
			$quality_verified['bbb_url'] = 10;
		}
		if ( !empty( $fields['contractor_license'] ) ) {
			$quality_verified['contractor_license'] = 10;
		}
	}
	//Count completed
	$quality_verified['completed'] = 0;
	foreach ( $quality_verified as $each ) {
		$quality_verified['completed']	+= $each;
	}
	return $quality_verified;
}

//Project status
function fv_get_project_status( $id = null, $fields = null ) {
	//If we already have fields, don't look up again
	if ( empty($fields) ) {
		$fields = fv_get_project_fields( $id );
	}
	//Determine status
	if ( !empty( $fields['proposal_id'] ) )  {
		if ( time() < $fields['deadline'] ) {
			if ( !empty($fields['endorsement_id'] ) )  {
				return 'endorsed';
			} else {
				return 'complete';
			}
		} else {
			return 'awarded';
		}
	} else {
		return 'new';
	}
	return 'new';
}
