<?php
/*
Plugin Name: OCAD U Illustration
Plugin URI: http://www.ocaduillustration.com
Description: Brings support to WP4 for things like Event and Illustrator post types.
Author: Garry Ing
Version: 1.0
Author URI: http://garrying.com
*/

// Register Illustrator/Event post types.

add_action( 'init', 'create_my_post_types' );

function create_my_post_types() {
  register_post_type( 'illustrator',
    array(
      'labels' => array(
        'name' => __( 'Illustrators' ),
        'singular_name' => __( 'Illustrator' ),
        'add_new' => __( 'Add New' ),
        'add_new_item' => __( 'Add New Illustrator' ),
        'edit' => __( 'Edit' ),
        'edit_item' => __( 'Edit Illustrator' ),
        'new_item' => __( 'New Illustrator' ),
        'view' => __( 'View Illustrator' ),
        'view_item' => __( 'View Illustrator' ),
        'search_items' => __( 'Search Illustrators' ),
        'not_found' => __( 'No Illustrators Found' ),
        'not_found_in_trash' => __( 'No Illustrators found in Trash' ),
        'parent' => __( 'Parent Illustrator' ),
      ),
      'public' => true,
      'has_archive' => true,
      'menu_position' => 0,
      'menu_icon' => 'dashicons-id',
      'show_in_rest' => true,
      'rest_base' => 'illustrators-api',
      'rest_controller_class' => 'WP_REST_Posts_Controller',
      'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
      'rewrite' => array( 'slug' => 'illustrators', 'with_front' => false ),
    )
  );

  $labels = array(
    'name' => _x( 'Graduating Years', 'taxonomy general name' ),
    'singular_name' => _x( 'Grad Year', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Years' ),
    'all_items' => __( 'All Years' ),
    'parent_item' => __( 'Parent Year' ),
    'parent_item_colon' => __( 'Parent Year:' ),
    'edit_item' => __( 'Edit Year' ), 
    'update_item' => __( 'Update Year' ),
    'add_new_item' => __( 'Add New Graduating Year' ),
    'new_item_name' => __( 'New Year Label' ),
    'menu_name' => __( 'Graduating Years' ),
  );

  register_taxonomy( 'gradyear', 'illustrator', 
    array( 
      'hierarchical' => true, 
      'labels' => $labels,
      'public' => true,
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'year', 'with_front' => true, 'hierarchical' => true ),
    ) 
  );
}

// Replace placeholder for title field.

add_filter('gettext','custom_enter_title');

function custom_enter_title( $input ) {
  global $post_type;
  if( is_admin() && 'Enter title here' == $input && 'illustrator' == $post_type )
    return 'Enter First Name, Followed by Last Name';
  if( is_admin() && 'Enter title here' == $input && 'event' == $post_type )
    return 'Enter Event Title';
  return $input;
}

// Set custom meta fields.

add_action( "admin_init", "admin_init" );

function admin_init() {
  add_meta_box( "credits_meta", "Illustrator Details", "illustrator_meta", "illustrator", "side", "high" );
}

function illustrator_get_custom_field( $value ) {
  global $post;
    $custom_field = get_post_meta( $post->ID, $value, true );
    if ( !empty( $custom_field ) )
      return is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );
  return false;
}

function illustrator_meta( $post ) {
  ?>
  <p>
    <label for="illu_title">Thesis Title</label><br />
    <textarea id="illu_title" name="illu_title" style="width:100%"><?php echo illustrator_get_custom_field( 'illu_title' ); ?></textarea>
  </p>
  <p>
    <label for="illu_email">Email Address</label><br />
    <input type="email" id="illu_email" name="illu_email" value="<?php echo illustrator_get_custom_field( 'illu_email' ); ?>" style="width:100%">
  </p>
  <p>
    <label for="illu_sites">Website</label><br />
    <input type="url" id="illu_sites" name="illu_sites" placeholder="Include http://" value="<?php echo illustrator_get_custom_field( 'illu_sites' ); ?>" style="width:100%">
  </p>
  <p>
    <label for="illu_sites_2">Website</label><br />
    <input type="url" id="illu_sites_2" name="illu_sites_2" placeholder="Include http://" value="<?php echo illustrator_get_custom_field( 'illu_sites_2' ); ?>" style="width:100%">
  </p>
  <p>
    <label for="illu_phone">Telephone</label><br />
    <input type="tel" id="illu_phone" name="illu_phone" placeholder="Example: (416) 123-4567" value="<?php echo illustrator_get_custom_field( 'illu_phone' ); ?>" style="width:100%">
  </p>
  <?php
}

// Save meta fields.

function save_details( $post_id ){
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
  if ( isset( $_POST["illu_email"] ) )
    update_post_meta( $post_id, "illu_email", $_POST["illu_email"] );
  if ( isset( $_POST["illu_sites"] ) )
    update_post_meta( $post_id, "illu_sites", $_POST["illu_sites"] );
  if ( isset( $_POST["illu_sites_2"] ) )
    update_post_meta( $post_id, "illu_sites_2", $_POST["illu_sites_2"] );
  if ( isset( $_POST["illu_phone"] ) )
    update_post_meta( $post_id, "illu_phone", $_POST["illu_phone"] );
  if ( isset( $_POST["illu_title"] ) )
    update_post_meta( $post_id, "illu_title", $_POST["illu_title"] );
}

add_action( 'save_post', 'save_details' );

// Extending WP-API with querying media based on post parent.

add_filter( 'query_vars', function( $vars ){
  $vars[] = 'post_parent';
  return $vars;
});

// Add REST API support to an already registered post type.

function illustrator_rest_support() {
  global $wp_post_types;
  //be sure to set this to the name of your post type!
  $post_type_name = 'illustrator';
  if( isset( $wp_post_types[ $post_type_name ] ) ) {
      $wp_post_types[$post_type_name]->show_in_rest = true;
      $wp_post_types[$post_type_name]->rest_base = $post_type_name;
      $wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
  }
}

add_action( 'init', 'illustrator_rest_support', 25 );

?>