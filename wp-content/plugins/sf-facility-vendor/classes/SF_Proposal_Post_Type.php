<?php

class SF_Proposal extends SF_FV {
	
	const POST_TYPE = 'fv_proposal';
	const REWRITE_SLUG = 'proposal';
	
	const CACHE_PREFIX = 'fv_proposal';
	const CACHE_GROUP = 'fv_proposal';

	protected $user_id;
	private static $instances;

	private static $meta_keys = array(
		'estimate' => '_estimate', // string
		'facility_id' => '_facility_id', //string
		'contractor_id' => '_contractor_id', //string
		'project_id' => '_project_id', //string
		'message_id' => '_message_id', //string
		'proposal_status' => '_proposal_status', //string (accepted)
		'user_id' => '_user_id', //string
	);

	public static function init() {
		
		// Register Post type
		add_action( 'init', array( get_class(), 'register_proposal_type') );
		
		// Meta Boxes
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'));
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		
		//Caches
		add_action( 'update_post_meta', array( get_class(), 'maybe_clear_id_cache' ), 10, 4 );
		add_action( 'added_post_meta', array( get_class(), 'maybe_clear_id_cache' ), 10, 4 );
		add_action( 'delete_post_meta', array( get_class(), 'maybe_clear_id_cache' ), 10, 4 );

	}

	public static function register_proposal_type() {
		
		 $labels = array(
			'name'               => 'Proposal',
			'singular_name'      => 'Proposal',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Proposal',
			'edit_item'          => 'Edit Proposal',
			'new_item'           => 'New Proposal',
			'all_items'          => 'All Proposals',
			'view_item'          => 'View Proposal',
			'search_items'       => 'Search Proposals',
			'not_found'          => 'No Proposals found',
			'not_found_in_trash' => 'No Proposals found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Proposals'
		  );
		
		  $args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => self::REWRITE_SLUG, 'with_front' => TRUE ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail'),
			//'menu_icon' 		 => SF_FV_URL."/assets/charity-icon.png"
		  );
		
		 register_post_type( self::POST_TYPE, $args );
		
	}


	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 * Get the proposal instance for the user
	 */
	public static function get_instance( $id = 0 ) {
		if ( !$id )
			return NULL;
		
		if ( !isset( self::$instances[$id] ) || !self::$instances[$id] instanceof self )
			self::$instances[$id] = new self( $id );

		if ( !isset( self::$instances[$id]->post->post_type ) )
			return NULL;
		
		if ( self::$instances[$id]->post->post_type != self::POST_TYPE )
			return NULL;
		
		return self::$instances[$id];
	}
	
	/**
	 * Get the proposal ids for the user
	 */
	// Note: returns multiple ids
	public static function get_proposal_ids_for_user( $user_id = 0 ) {
		if ( $user_id == -1 ) { // anonymous or temp id
			return FALSE;
		}
		if ( !is_numeric( $user_id ) ) {
			$user_id = 0; // not valid user id, so error
		}
		if ( !$user_id ) {
			$user_id = (int)get_current_user_id();
		}
		// first lookup cache cache
		if ( $proposal_id = self::get_id_cache( $user_id ) ) {
			return $proposal_id;
		}
		$proposal_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['user_id'] => $user_id ) );

		if ( empty( $proposal_ids ) ) {
			return FALSE;
		} else {
			$proposal_id = $proposal_ids[0];
		}

		self::set_id_cache( $user_id, $proposal_id );

		return $proposal_id;
	}
	
	/**
	 * Get the proposal ids for the facility
	 */
	// Note: returns multiple ids
	public static function get_proposal_ids_for_facility( $facility_id = 0 ) {
		
		if ( !is_numeric( $facility_id ) ) {
			$facility_id = 0; // not valid user id, so error
			return FALSE;
		}
		
		$proposal_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['facility_id'] => $facility_id ) );

		if ( empty( $proposal_ids ) ) {
			return FALSE;
		}

		return $proposal_ids;
	}
	/**
	 * Get the proposal ids for the contractor
	 */
	// Note: returns multiple ids
	public static function get_proposal_ids_for_contractor( $contractor_id = 0 ) {
		
		if ( !is_numeric( $contractor_id ) ) {
			$facility_id = 0; // not valid user id, so error
			return FALSE;
		}
		
		$proposal_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['contractor_id'] => $contractor_id ) );

		if ( empty( $proposal_ids ) ) {
			return FALSE;
		}

		return $proposal_ids;
	}

	public static function get_user_id_for_proposal( $proposal_id ) {
		if ( isset( self::$instances[$proposal_id] ) ) {
			return self::$instances[$proposal_id]->user_id;
		} else {
			$user_id = get_post_meta( $proposal_id, self::$meta_keys['user_id'], TRUE );
			return $user_id;
		}
	}
	
	public static function get_facility_id_for_proposal( $proposal_id ) {
		if ( isset( self::$instances[$proposal_id] ) ) {
			return self::$instances[$proposal_id]->facility_id;
		} else {
			$user_id = get_post_meta( $proposal_id, self::$meta_keys['facility_id'], TRUE );
			return $user_id;
		}
	}
	
	public static function get_contractor_id_for_proposal( $proposal_id ) {
		if ( isset( self::$instances[$proposal_id] ) ) {
			return self::$instances[$proposal_id]->contractor_id;
		} else {
			$user_id = get_post_meta( $proposal_id, self::$meta_keys['contractor_id'], TRUE );
			return $user_id;
		}
	}
	
	public static function get_project_id_for_proposal( $proposal_id ) {
		if ( isset( self::$instances[$proposal_id] ) ) {
			return self::$instances[$proposal_id]->project_id;
		} else {
			$user_id = get_post_meta( $proposal_id, self::$meta_keys['project_id'], TRUE );
			return $user_id;
		}
	}

	/**
	 * Create a new proposal
	 * Note: Returns wp_error if wp_insert_post fails
	 */
	public static function new_proposal( $user_id, $contractor_id = '', $project_id = '', $title = '', $data = array()) {
		if ( empty( $title ) &&  $contractor_id ) {
			$title = get_the_title($contractor_id).self::__( ' Proposal' );
		}
		if ( empty($title) && $user_id ) {
			$user = get_userdata( $user_id );
			if ( !is_a( $user, 'WP_User' ) ) { // prevent an proposal being created without a proper user_id associated.
				return NULL;
			}
			$title = $user->user_login;
			if ( $user->user_nicename ) {
				$title = $user->user_nicename;
			}
			
		}
		if ( $title ) {
			$post = array(
				'post_title' => $title,
				'post_name' => sanitize_title($title),
				'post_status' => 'publish',
				'post_type' => self::POST_TYPE,
			);
		} else { // create a post with generated title
			$post = array(
				'post_title' => self::__( 'Proposal' ).' '.$_SERVER['REMOTE_ADDR'],
				'post_status' => 'publish',
				'post_type' => self::POST_TYPE,
			);
		}
		$id = wp_insert_post( $post );
		if ( !is_wp_error( $id ) ) {
			update_post_meta( $id, self::$meta_keys['user_id'], $user_id );
			update_post_meta( $id, self::$meta_keys['contractor_id'], $contractor_id );
			update_post_meta( $id, self::$meta_keys['project_id'], $project_id );
			update_post_meta( $id, self::$meta_keys['message_id'], $data['message_id'] );
		}
		return $id;
	}



	/**
	 * When the user_id post meta for an proposal is updated, flush the cache for old and new
	 * Hooked into wp actions:
	 * update_post_meta (update_{$meta_type}_meta)
	 * added_post_meta (added_{$meta_type}_meta)
	 * delete_post_meta (delete_{$meta_type}_meta)
	 */
	public static function maybe_clear_id_cache( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( $meta_key == self::$meta_keys['user_id'] && get_post_type($object_id) == self::POST_TYPE ) {
			if ( $meta_value ) { // this might be empty on a delete_post_meta
				self::clear_id_cache($meta_value);
			}
			$old_value = get_post_meta($object_id, $meta_key);
			if ( $old_value != $meta_value ) { // this is probably the same on added_post_meta
				self::clear_id_cache($old_value);
			}
		}
	}

	/**
	 * Get the proposal ID cache for the user or facility
	 */
	private static function get_id_cache( $id ) {
		return (int)wp_cache_get(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * Set the proposal ID cache for the user or facility
	 */
	private static function set_id_cache( $id, $proposal_id ) {
		wp_cache_set(self::CACHE_PREFIX.'_id_'.$id, (int)$proposal_id, self::CACHE_GROUP);
	}

	/**
	 * Delete the proposal ID cache for the user or facility
	 */
	private static function clear_id_cache( $id ) {
		wp_cache_delete(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * If the current query is for the proposal post type
	 */
	public static function is_proposal_query() {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == self::POST_TYPE ) {
			return TRUE;
		}
		return FALSE;
	}
	
	
	/**
	 * Get all proposal posts
	 */
	public static function get_proposals() {
		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
		$proposals = new WP_Query( $args );
		return $proposals->posts;
	}
	
	
	/**
	 * Meta boxes
	 */
	
	public static function add_meta_boxes() {
		add_meta_box('fv_proposal_fields', 'Proposal', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'normal', 'default');
		add_meta_box('fv_proposal_status', 'Status', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'side', 'high');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'fv_proposal_fields':
				self::show_proposal_meta_box($post, $metabox);
				break;
			case 'fv_proposal_status':
				self::show_status_meta_box($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}
	
	private static function show_status_meta_box( $post, $metabox ) {
		$user_id = self::get_field($post->ID, 'user_id');
		
		$proposal_status = self::get_field($post->ID, 'proposal_status');
		
		$project_id = self::get_field($post->ID, 'project_id');
		$project_title = ($project_id) ? get_the_title($project_id).' (ID: '.$project_id.')' : __( 'No Project' );
		
		$contractor_id = self::get_field($post->ID, 'contractor_id');
		$contractor_title = ($contractor_id) ? get_the_title($contractor_id).' (ID: '.$contractor_id.')' : __( 'No Contractor' );
		?>
        <p>
            <label><?php _e( 'Proposal Status:' ); ?> <strong><?php echo $proposal_status; ?></strong></label>
            <input type="hidden" name="<?php echo self::$meta_keys['proposal_status'] ?>" id="<?php echo self::$meta_keys['proposal_status'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'proposal_status'))); ?>">
        </p>
        <p>
            <label><?php _e( 'Project:' ); ?>  <strong><?php echo $project_title; ?></strong></label>
            <input type="hidden" name="<?php echo self::$meta_keys['project_id'] ?>" id="<?php echo self::$meta_keys['project_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'project_id'))); ?>">
            <input type="hidden" name="<?php echo self::$meta_keys['user_id'] ?>" id="<?php echo self::$meta_keys['user_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'user_id'))); ?>">
        </p>
        <p>
            <label><?php _e( 'Contractor:' ); ?>  <strong><?php echo $contractor_title; ?></strong></label>
            <input type="hidden" name="<?php echo self::$meta_keys['contractor_id'] ?>" id="<?php echo self::$meta_keys['contractor_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'contractor_id'))); ?>">
        </p>
        <?php
	}
	
	private static function show_proposal_meta_box( $post, $metabox ) {
		
		?>
        <p>
            <label for="<?php echo self::$meta_keys['estimate'] ?>"><?php _e('Estimate'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['estimate'] ?>" id="<?php echo self::$meta_keys['estimate'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'estimate'))); ?>"><em>type numbers only, no currency sign ( $ )</em>
        </p>
         <p>
            <label for="<?php echo self::$meta_keys['proposal_status'] ?>"><?php _e('Proposal Status'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['proposal_status'] ?>" id="<?php echo self::$meta_keys['proposal_status'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'proposal_status'))); ?>"><em>possible values: accepted</em>
        </p>
 
		<?php
	}

	public static function save_meta_boxes( $post_id, $post ) {
	
		// only continue if it's an proposal post
		if ( $post->post_type != self::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
			return;
		}
		if (empty($_POST)) {
			return;	
		}
		
		self::save_meta_box($post_id, $post);
	}

	private static function save_meta_box( $post_id, $post ) {
		//Save all post form fields
		if ( isset( $_POST[self::$meta_keys['estimate']] ) ) {
			update_post_meta($post_id, self::$meta_keys['estimate'], stripslashes($_POST[self::$meta_keys['estimate']]) );
		}
		if ( isset( $_POST[self::$meta_keys['proposal_status']] ) ) {
			update_post_meta($post_id, self::$meta_keys['proposal_status'], stripslashes($_POST[self::$meta_keys['proposal_status']]) );
		}
	}
	
	public static function get_field( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], true );
		}
		return $value;
	}
	
	public static function save_field( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = update_post_meta( $post_id, self::$meta_keys[$meta_key], $new_value );
		}
		return $value;
	}
	
	public static function save_field_multiple( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = add_post_meta( $post_id, self::$meta_keys[$meta_key], $new_value );
		}
		return $value;
	}
	
	public static function get_field_multiple( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], false );
		}
		return $value;
	}
	
	/* Template / Public functions */
	
	public static function field_keys() {
		return self::$meta_keys;
	}
	
	public static function load_form_fields($post_id) {
		$fields = array();
		
		//Load post content and title
		if ( $post_id && !isset( $_POST['post_content'] ) ) {
			$fields['post_content'] = get_post_field('post_content', $post_id, 'raw');
		} else {
			$fields['post_content'] = stripslashes($_POST['post_content']) ;
			
		}
		if ( $post_id && !isset( $_POST['post_title'] ) ) {
			$fields['post_title'] = get_the_title( $post_id );
		} else {
			$fields['post_title'] = stripslashes($_POST['post_title']) ;
		}
		
		//Load fields
		foreach ( self::$meta_keys as $meta_key_name => $meta_key_value ) {
			if ( $post_id && !isset( $_POST[$meta_key_value] ) ) {
				$fields[$meta_key_name] = get_post_meta($post_id, $meta_key_value, TRUE);
			} else {
				$fields[$meta_key_name] = stripslashes($_POST[$meta_key_value]);
			}
		}
	
		return $fields;
	}
	
	public static function save_form_fields($post_id) {
		
		//Save post content
		$update_post = '';
		if ( isset( $_POST['post_content'] ) ) {
			$allowed_tags = wp_kses_allowed_html( 'post' );
			$_POST['post_content'] = wp_kses( $_POST['post_content'], $allowed_tags );
			$update_post['post_content'] = $_POST['post_content'];
		}
		if ( isset( $_POST['post_title'] ) ) {
			$update_post['post_title'] = $_POST['post_title'];
		}
		if ( $update_post ) {
			global $wpdb;
			$update_post = stripslashes_deep( $update_post );
			$wpdb->update( $wpdb->posts, $update_post, array( 'ID' => $post_id ) );	
		}
		
		if ( isset( $_POST[self::$meta_keys['estimate']] ) ) {
			update_post_meta($post_id, self::$meta_keys['estimate'], stripslashes($_POST[self::$meta_keys['estimate']]) );
		}
		
	}
	
	public static function load_attachments($post_id, $include_featured = false) {
		//Load photos - attachments to this post
		if ( !$include_featured ) {
			$featured_thumb_id = get_post_meta( $post_id, '_thumbnail_id', true);	
		}
		$args = array(
			  'post_type' => 'attachment',
			  'post_mime_type' => 'image',
			  'numberposts' => -1,
			  'post_status' => null,
			  'post_parent' => $post_id,
			  'post__not_in' => array($featured_thumb_id),
			  'orderby' => 'date',
			  'order' => 'DESC'
			  );
		
		$attachments = get_posts($args);
		
		return $attachments;	
	}
	
	public static function load_file_attachments($post_id) {
		//Get all photo ides
		$args = array(
		  'post_type' => 'attachment',
		  'post_mime_type' => 'image',
		  'numberposts' => -1,
		  'post_status' => null,
		  'post_parent' => $post_id,
		  'fields' => 'ids'
		  );
		$images = get_posts($args);	  
		//Load all attachments that are not in the list of image type attachments
		$args = array(
			  'post_type' => 'attachment',
			  'numberposts' => -1,
			  'post_status' => null,
			  'post_parent' => $post_id,
			  'post__not_in' => (array)$images,
			  'orderby' => 'date',
			  'order' => 'DESC'
			  );
		
		$attachments = get_posts($args);
		
		return $attachments;	
	}
	
	public static function save_attachment($post_id, $file, $post_data = array(), $set_featured = false) {
		if ( !is_array($post_data) ) $post_data = array(); //ensure the built in post data isn't remove by this not being an array
		
		//require the needed files
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			
		//then loop over the files that were sent and store them using  media_handle_upload();
		$error = '';
		if (!empty($_FILES) && !empty($_FILES[$file]['name']) ) {
			
			if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
				$error = self::__("Upload error: ").$_FILES[$file]['error'];
				//die();
			}
			$attach_id = media_handle_upload( $file, $post_id, $post_data );
			
			//If featured
			if ( $attach_id && $set_featured == true) {
				update_post_meta( $post_id, '_thumbnail_id', $attach_id );	
			}
			
		} else {
			$error = self::__("Upload error: No file was selected");
		}
		if ( $error ) {
			return array('result' => false, 'error' => $error);
		} else {
			return array('result' => true, 'attach_id' => $attach_id);
		}
	}

}
