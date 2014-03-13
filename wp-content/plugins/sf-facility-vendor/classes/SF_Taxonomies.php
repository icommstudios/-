<?php
//Note: This class should be loaded after all post types are loaded so Taxonomies can be assigned
class SF_Taxonomies extends SF_FV {
	
	const JOB_TYPE_TAXONOMY = 'fv_job_type';
	const JOB_SKILL_TAXONOMY = 'fv_job_skill';
	const JOB_LOCATION_TAXONOMY = 'fv_job_location';

	private static $instances = array();

	public static function init() {
		
		// Register Post type
		add_action( 'init', array( get_class(), 'register_taxonomies') );

	}

	public static function register_taxonomies() {
		 
		// register Type (category)
		register_taxonomy(
			self::JOB_TYPE_TAXONOMY,
			array(SF_Facility::POST_TYPE, SF_Contractor::POST_TYPE, SF_Project::POST_TYPE),
			array(
				'label' => __( 'Job Type' ),
				'rewrite' => array( 'slug' => 'job-type' ),
				'with_front' => TRUE,
				'hierarchical' => true,
			)
		);

		// register skills (tags)
		/*
		register_taxonomy(
			self::JOB_SKILL_TAXONOMY,
			array(SF_Facility::POST_TYPE, SF_Project::POST_TYPE),
			array(
				'label' => __( 'Job Skills' ),
				'rewrite' => array( 'slug' => 'job-skills' ),
				'with_front' => TRUE,
				'hierarchical' => FALSE,
			)
		);
		*/
		// register locations (tags)
		/*
		register_taxonomy(
			self::JOB_LOCATION_TAXONOMY,
			array(SF_Project::POST_TYPE),
			array(
				'label' => __( 'Location' ),
				'rewrite' => array( 'slug' => 'job-location' ),
				'with_front' => TRUE,
				'hierarchical' => FALSE,
			)
		);
		*/
		
	}


	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 * Get instance
	 */
	public static function get_instance( $id = 0 ) {
		if ( !$id ) {
			return NULL;
		}
		if ( !isset( self::$instances[$id] ) || !self::$instances[$id] instanceof self ) {
			self::$instances[$id] = new self( $id );
		}
		return self::$instances[$id];
	}

}
