<?php

class SF_Message extends SF_FV {
	
	const POST_TYPE = 'fv_message';
	const REWRITE_SLUG = 'message';

	private static $instances;

	private static $meta_keys = array(
		'to_id' => '_to_id', //string
		'from_id' => '_from_id', //string
		'from_user_id' => '_from_user_id', //string
		'type' => '_type', // string
		'open_date' => '_open_date', //string
		'archive_date' => '_archive_date', //string
		'related_project_id' => '_related_project_id', //string
		'related_project_action' => '_related_project_action', //string
		'related_proposal_id' => '_related_proposal_id', //string
	);

	public static function init() {
		
		// Register Post type
		add_action( 'init', array( get_class(), 'register_message_type') );
		
		// Meta Boxes
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'));
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
	}

	public static function register_message_type() {
		
		 $labels = array(
			'name'               => 'Message',
			'singular_name'      => 'Message',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Message',
			'edit_item'          => 'Edit Message',
			'new_item'           => 'New Message',
			'all_items'          => 'All Messages',
			'view_item'          => 'View Message',
			'search_items'       => 'Search Messages',
			'not_found'          => 'No Messages found',
			'not_found_in_trash' => 'No Messages found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Messages'
		  );
		
		  $args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => self::REWRITE_SLUG, 'with_front' => false ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor' ),
			//'menu_icon' 		 => SF_FV_URL."/assets/charity-icon.png"
		  );
		
		 register_post_type( self::POST_TYPE, $args );
		
	}


	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 * Get the message instance for the user
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
	 * Get the message ids for the account ( facility or contractor )
	 */
	// Note: returns multiple ids
	public static function get_message_ids_sent_to( $id = 0 ) {
		
		if ( !is_numeric( $id ) ) {
			$id = 0; // not valid user id, so error
		}
		
		$message_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['to_id'] => $id ) );

		if ( empty( $message_ids ) ) {
			return FALSE;
		} 

		return $message_ids;
	}
	
	public static function get_message_ids_sent_from( $id = 0 ) {
		
		if ( !is_numeric( $id ) ) {
			$id = 0; // not valid user id, so error
		}
		
		$message_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['from_id'] => $id ) );

		if ( empty( $message_ids ) ) {
			return FALSE;
		} 

		return $message_ids;
	}

	/**
	 * Create a new message
	 * Note: Returns wp_error if wp_insert_post fails
	 */
	public static function new_message( $from_user_id = '', $from_id = '', $to_id = '', $data = array() ) {
		if ( empty($to_id) ) {
			return NULL; // prevent an message being created without a proper user associated.
		}
		if ( $data['title'] ) {
			$post = array(
				'post_title' => $data['title'],
				'post_name' => sanitize_title($data['title']),
				'post_status' => 'publish',
				'post_type' => self::POST_TYPE,
				'post_content' => $data['content'],
			);
		} else { // create a post with generated title
			$post = array(
				'post_title' => self::__( 'Message' ).' '.$_SERVER['REMOTE_ADDR'],
				'post_status' => 'publish',
				'post_type' => self::POST_TYPE,
				'post_content' => $data['content'],
			);
		}
		$id = wp_insert_post( $post );
		if ( !is_wp_error( $id ) ) {
			update_post_meta( $id, self::$meta_keys['from_user_id'], $from_user_id );
			update_post_meta( $id, self::$meta_keys['from_id'], intval($from_id) );
			update_post_meta( $id, self::$meta_keys['to_id'], intval($to_id) );
			if ( $data['related_project_id'] ) {
				update_post_meta( $id, self::$meta_keys['related_project_id'], intval($data['related_project_id']) );
			}
			if ( $data['related_project_action'] ) {
				update_post_meta( $id, self::$meta_keys['related_project_action'], stripslashes($data['related_project_action']) );
			}
			if ( $data['type'] ) {
				update_post_meta( $id, self::$meta_keys['type'], stripslashes($data['type']) );
			} else {
				update_post_meta( $id, self::$meta_keys['type'], 'message' ); //normal message
			}
			if ( $data['related_proposal_id'] ) {
				update_post_meta( $id, self::$meta_keys['related_proposal_id'], intval($data['related_proposal_id']) );
			}
			
			//Send message notification, unless the directive has been set to not email 
			if ( isset($data['do_not_email']) && $data['do_not_email'] == true ) {
				//Do not email
				
			} else {
				
				//Send message notification email
				$message_user_id = fv_get_user_id_for_account( $to_id );
				$message_user = get_userdata ( $message_user_id );
				$message_link = add_query_arg( array( 'action' => 'messages' ), SF_Users::user_profile_url($message_user_id));
				$email_replace_keys = array('user_email' => $message_user->user_email, 'site_name' => get_option('blogname'), 'site_url' => home_url(), 'message_link' => $message_link, 'message_title' => $data['title'] );
				$email_data = array(
					'to_email' => $message_user->user_email,
					'from_email' => self::$notification_from_email,
					'from_name' => self::$notification_from_name,
					'subject' => self::build_email_subject('message_notification', $email_replace_keys),
					'content' => self::build_email_content('message_notification', $email_replace_keys),
					'is_html' => self::$notification_format_is_html
				);
				
				$result = SF_FV::send_email($email_data);
			}
		}
		return $id;
	}


	/**
	 * If the current query is for the message post type
	 */
	public static function is_message_query() {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == self::POST_TYPE ) {
			return TRUE;
		}
		return FALSE;
	}
	
	
	/**
	 * Get all message posts
	 */
	public static function get_messages() {
		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
		$messages = new WP_Query( $args );
		return $messages->posts;
	}
	
	
	/**
	 * Meta boxes
	 */
	
	public static function add_meta_boxes() {
		add_meta_box('fv_message_fields', 'Message', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'normal', 'default');
		add_meta_box('fv_message_status', 'Status', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'side', 'high');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'fv_message_fields':
				self::show_message_meta_box($post, $metabox);
				break;
			case 'fv_message_status':
				self::show_status_meta_box($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}
	
	private static function show_status_meta_box( $post, $metabox ) {
		$to_id = self::get_field($post->ID, 'to_id');
		$from_id = self::get_field($post->ID, 'from_id');
		$type = self::get_field($post->ID, 'type');
		$related_project_id = self::get_field($post->ID, 'related_project_id');
		$related_proposal_id = self::get_field($post->ID, 'related_proposal_id');

		?>
        <p>
            <label><?php _e( 'Sent To:' ); ?> <strong><?php echo get_the_title($to_id).' (ID: '.$to_id.')'; ?></strong></label>
        </p>
        <p>
            <label><?php _e( 'Sent From:' ); ?> <strong><?php echo get_the_title($from_id).' (ID: '.$from_id.')'; ?></strong></label>
        </p>
        <p>
            <label><?php _e( 'Message Type:' ); ?> <strong><?php echo $type; ?></strong></label>
        </p>
    	<?php if ( $related_project_id ) : ?>
        <p>
            <label><?php _e( 'Related Project ID:' ); ?> <strong><?php echo get_the_title($related_project_id).' (ID: '.$related_project_id.')'; ?></strong></label>
        </p>
        <?php
		endif;
		if ( $related_proposal_id ) : ?>
        <p>
            <label><?php _e( 'Related Proposal ID:' ); ?> <strong><?php echo get_the_title($related_proposal_id).' (ID: '.$related_proposal_id.')'; ?></strong></label>
        </p>
        <?php
		endif;
	}
	
	private static function show_message_meta_box( $post, $metabox ) {
		
		?>
        <p>
            <label for="<?php echo self::$meta_keys['to_id'] ?>"><?php _e('To ID'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['to_id'] ?>" id="<?php echo self::$meta_keys['to_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'to_id'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['from_id'] ?>"><?php _e('From ID'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['from_id'] ?>" id="<?php echo self::$meta_keys['from_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'from_id'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['type'] ?>"><?php _e('Type'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['type'] ?>" id="<?php echo self::$meta_keys['type'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'type'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['open_date'] ?>"><?php _e('Open Date (timestamp)'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['open_date'] ?>" id="<?php echo self::$meta_keys['open_date'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'open_date'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['related_project_id'] ?>"><?php _e('Related Project ID'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['related_project_id'] ?>" id="<?php echo self::$meta_keys['related_project_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'related_project_id'))); ?>">
        </p>
         <p>
            <label for="<?php echo self::$meta_keys['related_project_action'] ?>"><?php _e('Related Project Action'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['related_project_action'] ?>" id="<?php echo self::$meta_keys['related_project_action'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'related_project_action'))); ?>">
        </p> 
        <p>
            <label for="<?php echo self::$meta_keys['related_proposal_id'] ?>"><?php _e('Related Proposal ID'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['related_proposal_id'] ?>" id="<?php echo self::$meta_keys['related_proposal_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'related_proposal_id'))); ?>">
        </p> 
		<?php
	}

	public static function save_meta_boxes( $post_id, $post ) {
	
		// only continue if it's an message post
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
		/*
		if ( isset( $_POST[self::$meta_keys['budget']] ) ) {
			update_post_meta($post_id, self::$meta_keys['budget'], stripslashes($_POST[self::$meta_keys['budget']]) );
		}
		*/
	}
	
	public static function get_field( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], true );
		}
		return $value;
	}
	
	public static function save_field( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
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
		
		if ( isset( $_POST[self::$meta_keys['related_project_action']] ) ) {
			update_post_meta($post_id, self::$meta_keys['related_project_action'], stripslashes($_POST[self::$meta_keys['related_project_action']]) );
		}
		
	}

}
