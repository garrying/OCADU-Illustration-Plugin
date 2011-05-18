<?php
/*
Plugin Name: OCADU Illustration
Plugin URI: http://www.ocaduillustration.com
Description: Brings support to WP3 for things like Event and Illustrator post types.
Author: Garry Ing
Version: 1.0
Author URI: http://garrying.com
*/

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
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite' => array( 'slug' => 'illustrators', 'with_front' => false ),
		)
	);
	
	register_post_type( 'event',
		array(
			'labels' => array(
				'name' => __( 'Events' ),
				'singular_name' => __( 'Event' ),
				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add New Event' ),
				'edit' => __( 'Edit' ),
				'edit_item' => __( 'Edit Event' ),
				'new_item' => __( 'New Event' ),
				'view' => __( 'View Event' ),
				'view_item' => __( 'View Event' ),
				'search_items' => __( 'Search Events' ),
				'not_found' => __( 'No Events Found' ),
				'not_found_in_trash' => __( 'No Events found in Trash' ),
				'parent' => __( 'Parent Event' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_position' => 1,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite' => array( 'slug' => 'events', 'with_front' => false ),
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

add_filter('gettext','custom_enter_title');

function custom_enter_title( $input ) {

    global $post_type;

    if( is_admin() && 'Enter title here' == $input && 'illustrator' == $post_type )
        return 'Enter First Name, Followed by Last Name';

    if( is_admin() && 'Enter title here' == $input && 'event' == $post_type )
        return 'Enter Event Title';

    return $input;
}

add_action("admin_init", "admin_init");
 
function admin_init(){
  add_meta_box("credits_meta", "Illustrator Details", "illustrator_meta", "illustrator", "side", "high");
}
  
function illustrator_meta() {
  global $post;
  $custom = get_post_custom($post->ID);
  $illu_email = $custom["illu_email"][0];
  $illu_sites = $custom["illu_sites"][0];
  $illu_sites_2 = $custom["illu_sites_2"][0];
  $illu_phone = $custom["illu_phone"][0];
  $illu_title = $custom["illu_title"][0];
  ?>
	<p><label>Thesis Title:</label><br />
  <textarea name="illu_title"><?php echo $illu_title; ?></textarea></p>
  <p><label>Email Address:</label><br />
  <textarea name="illu_email"><?php echo $illu_email; ?></textarea></p>
  <p><label>Website:</label><br />
  <textarea name="illu_sites"><?php echo $illu_sites; ?></textarea><br />
<small>Include http://</small></p>
  <p><label>Website:</label><br />
  <textarea name="illu_sites_2"><?php echo $illu_sites_2; ?></textarea><br />
<small>Include http://</small></p>
  <p><label>Telephone:</label><br />
  <textarea name="illu_phone"><?php echo $illu_phone; ?></textarea><br />
<small>Example: (416) 123-4567</p>
  <?php
}

add_action('save_post', 'save_details');

function save_details(){
  global $post;
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	    return $post->ID;
	}
  update_post_meta($post->ID, "illu_email", $_POST["illu_email"]);
  update_post_meta($post->ID, "illu_sites", $_POST["illu_sites"]);
  update_post_meta($post->ID, "illu_sites_2", $_POST["illu_sites_2"]);
  update_post_meta($post->ID, "illu_phone", $_POST["illu_phone"]);
  update_post_meta($post->ID, "illu_title", $_POST["illu_title"]);

}