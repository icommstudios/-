<?php

class SF_Search extends SF_FV {
	
	const DEBUG = FALSE;
	static $query_instance;
	static $search_radius_api_key;
	static $search_radius_api_endpoint;
	
	public static function init() {
	
		if ( is_admin() ) return; 
		
		//Filter the search query
		add_filter( 'pre_get_posts', array(get_class(), 'filter_search_query'), 99, 1);
		add_filter( 'posts_where', array( get_class(), 'filter_search_where' ), 99, 2 );
		add_filter( 'posts_join', array( get_class(), 'filter_search_join' ) );
		add_filter( 'posts_request', array( get_class(), 'filter_search_distinct' ) );
		
		//Filter wp search template
		add_filter('template_include', array(get_class(), 'search_template'), 10, 1);
		
		//Setup search radius API
		self::$search_radius_api_key = get_option('fv_search_radius_api'); //API key
		self::$search_radius_api_endpoint = 'http://zipcodedistanceapi.redline13.com/rest/'.self::$search_radius_api_key.'/radius.json'; //Add to endpoint: /55555/25/mile';

		
	}
	
	//Filter search query vars
	public function filter_search_query($query) {
		
		// Make sure a blank serach is treated as a search
		if (isset($_GET['s']) && empty($_GET['s']) && $query->is_main_query()){
			$query->is_search = true;
			$query->is_home = false;
		}
		if ( $query->is_search && $query->is_main_query() && !empty($query->query_vars['s']) ) {
			
			//$query->set('search_term', $query->query_vars['s'] ); //set our custom search term
			$query->set('search_location', $_REQUEST['location'] );
			$query->set('search_job_postings_filter', (bool)$_REQUEST['job_postings_filter'] );
			$query->set('search_contractors_filter', (bool)$_REQUEST['contractors_filter'] );
			$query->set('search_distance', $_REQUEST['distance'] );
			$query->set('search_category', $_REQUEST['category'] );
			
			$query->set('search_sort_by_first', $_REQUEST['sort_by_first'] );
			$query->set('search_sort_by_second', $_REQUEST['sort_by_second'] );
			
			//$query->set('s', '' ); //unset the wordpress "s"
			/*
			$query->set('meta_query', array(
				array(
					'key' => '_location_zip',
					'value' => $_REQUEST['location'],
					'compare' => 'LIKE'
				)
			));
			*/
	
		}
		
		return $query;
	}
	
	// Build the search where  query
	public function filter_search_where( $where, $wp_query ) {
		global $wpdb;
		
		if ( !$wp_query->is_search() ) return $where;
		
		//Save query instance (used in other later filters)
		self::$query_instance = &$wp_query;
		
		if ( !self::$query_instance->query_vars['s'] ) return $where;
		$search_terms = explode(' ', self::$query_instance->query_vars['s']);
		
		//IMPORTANT - Start WHERE query over from scratch
		//var_dump($where);
		//die();
		$where = '';
		$where_in_post_type = '';
		$search = '';
		
		//Setup where - limit post types
		if ( self::$query_instance->query_vars['search_job_postings_filter'] ) {
			$where_in_post_type .= "'".SF_Project::POST_TYPE."',";
		}
		if ( self::$query_instance->query_vars['search_contractors_filter'] ) {
			$where_in_post_type .= "'".SF_Contractor::POST_TYPE."'";
		}
		$where_in_post_type = rtrim($where_in_post_type, ',');
		if ( !empty( $where_in_post_type ) ) {
			$where = " AND $wpdb->posts.post_type IN (".$where_in_post_type.") AND ($wpdb->posts.post_status = 'publish')";
		} else {
			//no post types, so make a result that won't return anything
			$where = "AND $wpdb->posts.ID < 0";
		}
		
		// Where category (if a category should be used as a filter - as opposed to a search option below)
		if ( self::$query_instance->query_vars['search_category'] ) {
			$where .= " AND ( tter.slug = '".self::$query_instance->query_vars['search_category']."' AND ttax.taxonomy = '".SF_Taxonomies::JOB_TYPE_TAXONOMY."' ) ";
		}
		
		// Where Location (meta field = _location_zip )
		if ( self::$query_instance->query_vars['search_location'] && self::$query_instance->query_vars['search_distance'] != 'nationwide' ) {
			//Get  list of zipcodes
			$lookup_zip = intval(self::$query_instance->query_vars['search_location']);
			$lookup_distance = intval(self::$query_instance->query_vars['search_distance']); //miles
			$search_zip_codes = self::get_zip_codes_search_radius($lookup_zip, $lookup_distance);
			
			//Where meta terms
			$where_and = '';
			$add_where = '';
			$where_meta = array();
			
			//Add our searched zip to the list
			$where_meta[] = array( 'meta_key' => '_location_zip', 'meta_value' => self::$query_instance->query_vars['search_location'] );
			
			//Loop and prepare all zip codes to add all the other zips returned with in the radius
			if ( !empty($search_zip_codes) ) {
				foreach ( $search_zip_codes as $zip ) {
					if ( $zip['zip_code'] && strlen( $zip['zip_code'] >= 5) ) {
						$where_meta[] = array( 'meta_key' => '_location_zip', 'meta_value' => intval($zip['zip_code']));
					} 
				}
			}
			//Build where query
			foreach ( $where_meta as $each_where_meta ) {
				$meta_key = addslashes_gpc( $each_where_meta['meta_key'] );
				$meta_value = addslashes_gpc( $each_where_meta['meta_value'] );
				if ( $meta_key && $meta_value ) {
					$add_where .= " $where_and ( $wpdb->postmeta.meta_key = '$meta_key' AND $wpdb->postmeta.meta_value LIKE '%$meta_value%' )";
					$where_and = ' OR ';
				}
			}
			if ( !empty( $add_where ) ) {
				$where .= " AND ( $add_where )"; 
			}
		}
		
		//Now setup the Searches for the term from various fields
		
		//Search title
		$searchand = '';
		$add_search = '';
		foreach ( $search_terms as $term ) {
			$term = addslashes_gpc( $term );
			$add_search .= " $searchand ($wpdb->posts.post_title LIKE '%$term%')";
			$searchand = ' AND ';
		}
		if ( !empty( $add_search ) ) {
			if ( !empty( $search ) ) { //we already have a search group
				$search .= " OR ( $add_search ) "; 
			} else {
				$search .= " ( $add_search ) ";	
			}
		}
		
		//Search taxonomy terms
		$searchand = '';
		$add_search = '';
		foreach ( $search_terms as $term ) {
			$term = addslashes_gpc( $term );
			$add_search .= " $searchand (tter.name LIKE '%$term%')";
			$searchand = ' AND ';
		}
		if ( !empty( $add_search ) ) {
			if ( !empty( $search ) ) { //we already have a search group
				$search .= " OR ( $add_search ) "; 
			} else {
				$search .= " ( $add_search ) ";	
			}
		}
		
		
		//do we have anything to search
		if ( !empty( $search ) ) {
			$where .= " AND ( "; //Start search AND wrapper
			$where .= " ( $search ) ";	
			$where .= " ) ";	//End serach AND wrapper
		}
	
		
		if ( self::DEBUG ) error_log('new search query: '.$where);
		return $where;
	}
	
	//Add taxonomy to join (so its searchable)
	public function filter_search_join( $join ) {
		global $wpdb;
		
		// IMPORTANT Join query string is not started from scratch
		
		//Add Post Meta Join
		if ( self::$query_instance->query_vars['search_location'] && self::$query_instance->query_vars['search_distance'] != 'nationwide') {
			if ( stripos( $join, "INNER JOIN $wpdb->postmeta") === false ) { //If meta table is already in Join
				$join .= " INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)";
			}
		}
		
		//Add Taxonomy Join
		if ( !empty( self::$query_instance->query_vars['s'] ) ) {

			// if we're searching for categories
			//$on[] = "ttax.taxonomy = 'category'";
			//$on[] = "ttax.taxonomy = 'post_tag'";
			
			
			// if we're searching custom taxonomies
			$all_taxonomies = get_object_taxonomies( SF_Project::POST_TYPE );
			foreach ( $all_taxonomies as $taxonomy ) {
				if ( $taxonomy == 'post_tag' || $taxonomy == 'category' )
					continue; //Don't search these built in wordpress taxonomies
				$on[] = "ttax.taxonomy = '".addslashes( $taxonomy )."'";
			}
			
			// build our final string
			$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';

			$join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
		}
		
		return $join;
	}
	
	//Make results distinct to avoid duplicates
	public function filter_search_distinct( $query ) {
		global $wpdb;
		if ( !empty( self::$query_instance->query_vars['s'] ) ) {
			if ( strstr( $query, 'DISTINCT' ) ) {}
			else {
				$query = str_replace( 'SELECT', 'SELECT DISTINCT', $query );
			}
		}
		return $query;
	}
	
	//Show search results template
	public function search_template($template)   {    
		global $wp_query;   
		
		if ( $wp_query->is_search() ) {
			$new_template = locate_template('templates/content-search.php');  
			if ( $new_template ) {
				return $new_template;
			}
		}
	 	return $template;
	}
	
	//Search radius API
	public function get_zip_codes_search_radius ( $zip, $miles ) {
		
		//Search api
		$api_url = trailingslashit(self::$search_radius_api_endpoint).intval($zip).'/'.intval($miles).'/mile'; //Add to endpoint: /55555/25/mile';
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_URL, $api_url);
	
		$response = curl_exec( $ch );	
		$resultContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close( $ch );
		
		$response_array = array();
		if ( $response ) {
			$response_array = json_decode($response, true);
			if ( isset($response_array['zip_codes']) && !empty($response_array['zip_codes']) ) {
				return $response_array['zip_codes'];
			}
		}
		//echo curl_errno($ch);
		return false;

	}

	
}
