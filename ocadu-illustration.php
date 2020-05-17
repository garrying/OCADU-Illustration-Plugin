<?php
/*
Plugin Name: OCAD U Illustration
Plugin URI: http://www.ocaduillustration.com
Description: Brings support to WP5 for Illustrator post types.
Author: Garry Ing
Version: 1.0
Author URI: https://garrying.com
*/

// Register Illustrator post type.

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
      'show_admin_column' => true,
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
  if( is_admin() && 'Add title' == $input && 'illustrator' == $post_type )
    return 'Enter First Name, Followed by Last Name';
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
    <input type="url" id="illu_sites" name="illu_sites" placeholder="Include https://" value="
      <?php
        if ( illustrator_get_custom_field( 'illu_sites' ) )
          echo illustrator_get_custom_field( 'illu_sites' );
        else
          echo 'https://'
      ?>
      " style="width:100%">
  </p>
  <p>
    <label for="illu_sites_2">Website</label><br />
    <input type="url" id="illu_sites_2" name="illu_sites_2" placeholder="Include https://" value="<?php echo illustrator_get_custom_field( 'illu_sites_2' ); ?>" style="width:100%">
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

// Add AMP support

add_action( 'amp_init', 'illustrator_amp_add_review_cpt' );
function illustrator_amp_add_review_cpt() {
  add_post_type_support( 'illustrator', AMP_QUERY_VAR );
}

// Append gallery to AMP post content

add_action( 'pre_amp_render_post', 'illustrator_amp_add_custom_actions' );

function illustrator_amp_add_custom_actions() {
  add_filter( 'the_content', 'illustrator_amp_add_featured_image' );
}

function illustrator_amp_add_featured_image( $content ) {
  if ( has_post_thumbnail() ) {
    $gallery_shortcode = '[gallery]';
    $content = $gallery_shortcode . $content;
  }
  return $content;
}

// Modify AMP JSON

add_filter( 'amp_post_template_metadata', 'illustrator_amp_modify_json_metadata', 10, 2 );

function illustrator_amp_modify_json_metadata( $metadata, $post ) {
  $metadata['@type'] = 'NewsArticle';
  $metadata['headline'] = get_post_meta( $post->ID, 'illu_title', true );
  $metadata['author']['name'] = $post->post_title;
  return $metadata;
}

// AMP Analytics

add_filter( 'amp_post_template_analytics', 'illustrator_amp_add_custom_analytics' );

function illustrator_amp_add_custom_analytics( $analytics ) {
  if ( ! is_array( $analytics ) ) {
    $analytics = array();
  }
  // https://developers.google.com/analytics/devguides/collection/amp-analytics/
  $analytics['ocaduillustration-googleanalytics'] = array(
    'type' => 'googleanalytics',
    'attributes' => array(
      // 'data-credentials' => 'include',
    ),
    'config_data' => array(
      'vars' => array(
        'account' => "UA-16173154-1"
      ),
      'triggers' => array(
        'trackPageview' => array(
          'on' => 'visible',
          'request' => 'pageview',
        ),
      ),
    ),
  );
  return $analytics;
}

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

// Custom columns for illustrator display

add_filter('manage_illustrator_posts_columns', 'posts_columns', 5);
add_action('manage_illustrator_posts_custom_column', 'posts_custom_columns', 5, 2);

function posts_columns( $defaults ){
  $defaults['post_thumbs'] = __('Featured Image');
  $defaults['post_email'] = __('Email');
  $defaults['post_site'] = __('Website');
  $new = array();

  foreach( $defaults as $key=>$value ) {
    if( $key=='title' ) {
      $new['post_thumbs'] = $tags;
    }
    if( $key=='date' ) {
      $new['post_site'] = $tags;
    }
    if( $key=='date' ) {
      $new['post_email'] = $tags;
    }
    $new[$key]=$value;
  }

  return $new;
}

function posts_custom_columns( $column_name, $id ){
  switch ( $column_name ) {
    case 'post_thumbs' :
      $thumb_id = get_post_thumbnail_id();
      $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'thumbnail', true);
      $thumb_url = $thumb_url_array[0];
      echo '<a href="'. get_edit_post_link( $id ) .'">';
      echo "<img width='100' height='100' src='".$thumb_url."' />";
      echo '</a>';
      break;
    case 'post_email' :
      echo get_post_meta($id, 'illu_email', true);
      break;
    case 'post_site' :
      $cf = esc_attr(get_post_meta( get_the_ID(), 'illu_sites', true ));
      echo '<a href="' . $cf . '" target="_blank">' . $cf . '</a>';
      break;
  }
}

// Activate WordPress Maintenance Mode

function wp_maintenance_mode(){
  if ( file_exists( ABSPATH . '.maintenance' ) ) {
    if(!current_user_can('edit_themes') || !is_user_logged_in()){
      wp_die(
        __( 'Briefly unavailable for scheduled maintenance. Check back in a minute.' ),
        __( 'Maintenance' ),
        503
      );
    }
  }
}

add_action('get_header', 'wp_maintenance_mode');

?>