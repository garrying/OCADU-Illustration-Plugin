<?php
/**
 * Plugin Name: OCAD U Illustration
 * Plugin URI: https://www.ocaduillustration.com
 * Description: Brings support to WP5 for Illustrator post types.
 * Author: Garry Ing
 * Version: 1.0.0
 * Author URI: https://garrying.com
 *
 * @package OCADUIllustration
 * @author Garry Ing
 * @since 1.0.0
 **/

// Register Illustrator post type.

add_action( 'init', 'create_my_post_types' );

/**
 * Register a custom post type called "illustrator".
 */
function create_my_post_types() {
	register_post_type(
		'illustrator',
		array(
			'labels'                => array(
				'name'               => __( 'Illustrators' ),
				'singular_name'      => __( 'Illustrator' ),
				'add_new'            => __( 'Add New' ),
				'add_new_item'       => __( 'Add New Illustrator' ),
				'edit'               => __( 'Edit' ),
				'edit_item'          => __( 'Edit Illustrator' ),
				'new_item'           => __( 'New Illustrator' ),
				'view'               => __( 'View Illustrator' ),
				'view_item'          => __( 'View Illustrator' ),
				'search_items'       => __( 'Search Illustrators' ),
				'not_found'          => __( 'No Illustrators Found' ),
				'not_found_in_trash' => __( 'No Illustrators found in Trash' ),
				'parent'             => __( 'Parent Illustrator' ),
			),
			'public'                => true,
			'has_archive'           => true,
			'menu_position'         => 0,
			'menu_icon'             => 'dashicons-id',
			'show_in_rest'          => true,
			'rest_base'             => 'illustrators-api',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'               => array(
				'slug'       => 'illustrators',
				'with_front' => false,
			),
		)
	);

	$labels = array(
		'name'              => _x( 'Graduating Years', 'taxonomy general name' ),
		'singular_name'     => _x( 'Grad Year', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Years' ),
		'all_items'         => __( 'All Years' ),
		'parent_item'       => __( 'Parent Year' ),
		'parent_item_colon' => __( 'Parent Year:' ),
		'edit_item'         => __( 'Edit Year' ),
		'update_item'       => __( 'Update Year' ),
		'add_new_item'      => __( 'Add New Graduating Year' ),
		'new_item_name'     => __( 'New Year Label' ),
		'menu_name'         => __( 'Graduating Years' ),
	);

	register_taxonomy(
		'gradyear',
		'illustrator',
		array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_admin_column' => true,
			'public'            => true,
			'show_ui'           => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'         => 'year',
				'with_front'   => true,
				'hierarchical' => true,
			),
		)
	);
}

// Replace placeholder for title field.

add_filter( 'gettext', 'custom_enter_title' );

/**
 * Override the title field.
 *
 * @param  Number $input The title input field.
 */
function custom_enter_title( $input ) {
	global $post_type;
	if ( is_admin() && 'Add title' === $input && 'illustrator' === $post_type ) {
		return 'Enter First Name, Followed by Last Name';
	}
	return $input;
}

// Set custom meta fields.

add_action( 'admin_init', 'admin_init' );

/**
 * Init a space for custom fields.
 */
function admin_init() {
	add_meta_box( 'credits_meta', 'Illustrator Details', 'illustrator_meta', 'illustrator', 'side', 'high' );
}

/**
 * Helper function for getting custom field values.
 *
 * @param  Number $value The custom field getter.
 */
function illustrator_get_custom_field( $value ) {
	global $post;
	$custom_field = get_post_meta( $post->ID, $value, true );
	if ( ! empty( $custom_field ) ) {
		return is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );
	}
	return false;
}

/**
 * Custom meta fields.
 *
 * @param  Number $post The post ID.
 */
function illustrator_meta( $post ) {
	?>
	<p>
	<label for="illu_title">Thesis Title</label><br />
	<textarea id="illu_title" name="illu_title" style="width:100%"><?php echo esc_textarea( illustrator_get_custom_field( 'illu_title' ) ); ?></textarea>
	</p>
	<p>
	<label for="illu_email">Email Address</label><br />
	<input type="email" id="illu_email" name="illu_email" value="<?php echo esc_html( illustrator_get_custom_field( 'illu_email' ) ); ?>" style="width:100%">
	</p>
	<p>
	<label for="illu_sites">Website</label><br />
	<input type="url" id="illu_sites" name="illu_sites" placeholder="Include https://" value="
		<?php
		if ( illustrator_get_custom_field( 'illu_sites' ) ) {
			echo esc_url( illustrator_get_custom_field( 'illu_sites' ) );
		} else {
			echo 'https://';
		}
		?>
		" style="width:100%">
	</p>
	<p>
	<label for="illu_sites_2">Website</label><br />
	<input type="url" id="illu_sites_2" name="illu_sites_2" placeholder="Include https://" value="<?php echo esc_url( illustrator_get_custom_field( 'illu_sites_2' ) ); ?>" style="width:100%">
	</p>
	<p>
	<label for="illu_phone">Telephone</label><br />
	<input type="tel" id="illu_phone" name="illu_phone" placeholder="Example: (416) 123-4567" value="<?php echo esc_html( illustrator_get_custom_field( 'illu_phone' ) ); ?>" style="width:100%">
	</p>
	<?php wp_nonce_field( '_ocaduillustration_nonce', '_ocaduillustration_process' ); ?>
	<?php
}

/**
 * Save meta fields.
 *
 * @param  Number $post_id The post ID.
 */
function save_details( $post_id ) {
	if ( ! isset( $_POST['_ocaduillustration_process'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['_ocaduillustration_process'] ), '_ocaduillustration_nonce' ) ) {
		return $post->ID;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( isset( $_POST['illu_email'] ) ) {
		update_post_meta( $post_id, 'illu_email', sanitize_email( wp_unslash( $_POST['illu_email'] ) ) );
	}
	if ( isset( $_POST['illu_sites'] ) ) {
		update_post_meta( $post_id, 'illu_sites', esc_url_raw( wp_unslash( $_POST['illu_sites'] ) ) );
	}
	if ( isset( $_POST['illu_sites_2'] ) ) {
		update_post_meta( $post_id, 'illu_sites_2', esc_url_raw( wp_unslash( $_POST['illu_sites_2'] ) ) );
	}
	if ( isset( $_POST['illu_phone'] ) ) {
		update_post_meta( $post_id, 'illu_phone', sanitize_text_field( wp_unslash( $_POST['illu_phone'] ) ) );
	}
	if ( isset( $_POST['illu_title'] ) ) {
		update_post_meta( $post_id, 'illu_title', sanitize_text_field( wp_unslash( $_POST['illu_title'] ) ) );
	}
}

add_action( 'save_post', 'save_details' );

// Extending WP-API with querying media based on post parent.

add_filter(
	'query_vars',
	function( $vars ) {
		$vars[] = 'post_parent';
		return $vars;
	}
);

/**
 * Add REST API support to an already registered post type.
 */
function illustrator_rest_support() {
	global $wp_post_types;
	// be sure to set this to the name of your post type!
	$post_type_name = 'illustrator';
	if ( isset( $wp_post_types[ $post_type_name ] ) ) {
		$wp_post_types[ $post_type_name ]->show_in_rest          = true;
		$wp_post_types[ $post_type_name ]->rest_base             = $post_type_name;
		$wp_post_types[ $post_type_name ]->rest_controller_class = 'WP_REST_Posts_Controller';
	}
}

add_action( 'init', 'illustrator_rest_support', 25 );

// Custom columns for illustrator display.

add_filter( 'manage_illustrator_posts_columns', 'posts_columns', 5 );
add_action( 'manage_illustrator_posts_custom_column', 'posts_custom_columns', 5, 2 );

/**
 * Change default columns for illustrator post type.
 *
 * @param  Array $defaults The column array.
 */
function posts_columns( $defaults ) {
	$defaults['post_thumbs'] = __( 'Featured Image' );
	$defaults['post_email']  = __( 'Email' );
	$defaults['post_site']   = __( 'Website' );
	$new                     = array();

	foreach ( $defaults as $key => $value ) {
		if ( 'title' === $key ) {
			$new['post_thumbs'] = $tags;
		}
		if ( 'date' === $key ) {
			$new['post_site'] = $tags;
		}
		if ( 'date' === $key ) {
			$new['post_email'] = $tags;
		}
		$new[ $key ] = $value;
	}

	return $new;
}

/**
 * Add feature image to admin view.
 *
 * @param  String  $column_name The column ID.
 * @param  Integer $id          The post ID.
 */
function posts_custom_columns( $column_name, $id ) {
	switch ( $column_name ) {
		case 'post_thumbs':
			$thumb_id        = get_post_thumbnail_id();
			$thumb_url_array = wp_get_attachment_image_src( $thumb_id, 'thumbnail', true );
			$thumb_url       = $thumb_url_array[0];
			echo '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">';
			echo "<img width='100' height='100' src='" . esc_url( $thumb_url ) . "' />";
			echo '</a>';
			break;
		case 'post_email':
			echo esc_attr( get_post_meta( $id, 'illu_email', true ) );
			break;
		case 'post_site':
			echo esc_url( get_post_meta( get_the_ID(), 'illu_sites', true ) );
			break;
	}
}

/**
 * Activate WordPress Maintenance Mode.
 */
function wp_maintenance_mode() {
	if ( file_exists( ABSPATH . '.maintenance' ) ) {
		if ( ! current_user_can( 'edit_themes' ) || ! is_user_logged_in() ) {
			wp_die(
				esc_html( __( 'Briefly unavailable for scheduled maintenance. Check back in a minute.' ) ),
				esc_html( __( 'Maintenance' ) ),
				503
			);
		}
	}
}

add_action( 'get_header', 'wp_maintenance_mode' );

?>
