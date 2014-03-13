<?php
/**
 * Custom functions
 */

//Set up theme changes
if ( function_exists( 'add_theme_support' ) ) { 
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 180, 180, true ); // default Post Thumbnail dimensions (cropped)

	// additional image sizes
	// delete the next line if you do not need additional image sizes
	add_image_size( 'img_400', 400, 9999 ); //400 pixels wide (and unlimited height)
	add_image_size( 'img_750', 750, 9999 ); //400 pixels wide (and unlimited height)
}

//Remove admin bar from non-admins
if ( !is_admin() && !current_user_can( 'edit_posts' ) ) {
	show_admin_bar( false );
}

//Shorten excerpt
function custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
function new_excerpt_more( $more ) {
	return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');

// shorten text
function shorten_length($text = '', $length = 50) {
	if ( strlen( $text) > $length) {
		$text = trim(substr($text, 0, $length)).'...';
	}
	
	return $text;
}

//Pagination
function wp_pagination($this_query = null) {
	global $wp_query;
	if ( !$this_query ) {
		$this_query = $wp_query;
	}
	$big = 12345678;
	$page_format = paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => $this_query->max_num_pages,
		'type'  => 'array'
	) );
	if( is_array($page_format) ) {
		$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
		echo '<ul class="pagination">';
		echo '<li><span>'. $paged . ' of ' . $this_query->max_num_pages .'</span></li>';
		foreach ( $page_format as $page ) {
				echo "<li>$page</li>";
		}
	   echo '</ul>';
	}
}
