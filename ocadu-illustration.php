<?php
/**
 * Plugin Name: OCAD U Illustration
 * Plugin URI: https://www.ocaduillustration.com
 * Description: Custom Illustrator post type, taxonomy, and admin UI for OCAD U's Illustration Program.
 * Version: 1.1.0
 * Author: Garry Ing
 * Author URI: https://garrying.com
 * Requires at least: 6.5
 * Requires PHP: 8.3
 * Tested up to: 6.8
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: ocadu-illustration
 * Update URI: false
 *
 * @package OCADUIllustration
 * @author  Garry Ing
 * @since   1.0.0
 **/

if (!defined("ABSPATH")) {
  exit();
}

// Register Illustrator post type.

add_action("init", "ocaduillustration_register_post_types");

/**
 * Register a custom post type called "illustrator".
 */
function ocaduillustration_register_post_types()
{
  register_post_type("illustrator", [
    "labels" => [
      "name" => __("Illustrators", "ocadu-illustration"),
      "singular_name" => __("Illustrator", "ocadu-illustration"),
      "add_new" => __("Add New", "ocadu-illustration"),
      "add_new_item" => __("Add New Illustrator", "ocadu-illustration"),
      "edit" => __("Edit", "ocadu-illustration"),
      "edit_item" => __("Edit Illustrator", "ocadu-illustration"),
      "new_item" => __("New Illustrator", "ocadu-illustration"),
      "view" => __("View Illustrator", "ocadu-illustration"),
      "view_item" => __("View Illustrator", "ocadu-illustration"),
      "search_items" => __("Search Illustrators", "ocadu-illustration"),
      "not_found" => __("No Illustrators Found", "ocadu-illustration"),
      "not_found_in_trash" => __(
        "No Illustrators found in Trash",
        "ocadu-illustration",
      ),
      "parent" => __("Parent Illustrator", "ocadu-illustration"),
    ],
    "public" => true,
    "has_archive" => true,
    "menu_position" => 0,
    "menu_icon" => "dashicons-id",
    "show_in_rest" => true,
    "rest_base" => "illustrator",
    "rest_controller_class" => "WP_REST_Posts_Controller",
    "supports" => ["title", "editor", "excerpt", "thumbnail", "custom-fields"],
    "rewrite" => [
      "slug" => "illustrators",
      "with_front" => false,
    ],
    "show_in_graphql" => true,
    "graphql_single_name" => "illustrator",
    "hierarchical" => true,
    "graphql_plural_name" => "illustrators",
  ]);

  $labels = [
    "name" => _x(
      "Graduating Years",
      "taxonomy general name",
      "ocadu-illustration",
    ),
    "singular_name" => _x(
      "Grad Year",
      "taxonomy singular name",
      "ocadu-illustration",
    ),
    "search_items" => __("Search Years", "ocadu-illustration"),
    "all_items" => __("All Years", "ocadu-illustration"),
    "parent_item" => __("Parent Year", "ocadu-illustration"),
    "parent_item_colon" => __("Parent Year:", "ocadu-illustration"),
    "edit_item" => __("Edit Year", "ocadu-illustration"),
    "update_item" => __("Update Year", "ocadu-illustration"),
    "add_new_item" => __("Add New Graduating Year", "ocadu-illustration"),
    "new_item_name" => __("New Year Label", "ocadu-illustration"),
    "menu_name" => __("Graduating Years", "ocadu-illustration"),
  ];

  register_taxonomy("gradyear", "illustrator", [
    "hierarchical" => true,
    "labels" => $labels,
    "show_admin_column" => true,
    "public" => true,
    "show_ui" => true,
    "show_in_rest" => true,
    "query_var" => true,
    "rewrite" => [
      "slug" => "year",
      "with_front" => true,
      "hierarchical" => true,
    ],
    "show_in_graphql" => true,
    "graphql_single_name" => "gradyear",
    "graphql_plural_name" => "gradyears",
  ]);
}

// Set custom meta fields.

add_action("admin_init", "ocaduillustration_register_meta_box");

/**
 * Init a space for custom fields.
 */
function ocaduillustration_register_meta_box()
{
  add_meta_box(
    "credits_meta",
    "Illustrator Details",
    "ocaduillustration_render_meta_box",
    "illustrator",
    "side",
    "high",
  );
}

/**
 * Helper function for getting custom field values.
 *
 * @param  int    $post_id The post ID.
 * @param  string $value   The custom field key.
 */
function ocaduillustration_get_custom_field($post_id, $value)
{
  $custom_field = get_post_meta($post_id, $value, true);
  if (!empty($custom_field)) {
    return is_array($custom_field)
      ? stripslashes_deep($custom_field)
      : stripslashes(wp_kses_decode_entities($custom_field));
  }
  return false;
}

/**
 * Custom meta fields.
 *
 * @param  WP_Post $post The post object.
 */
function ocaduillustration_render_meta_box($post)
{
  ?>
  <p>
  <button type="button" id="ocadu-upload-btn" class="button button-primary button-large">Upload Work</button>
  <span id="ocadu-image-count" style="margin-left: 8px;">
    <?php
    $image_count = count(get_attached_media("image", $post->ID));
    echo esc_html(
      $image_count .
        " " .
        _n("image", "images", $image_count, "ocadu-illustration") .
        " uploaded",
    );
    ?>
  </span>
  </p>
  <script>
  jQuery( function( $ ) {
      $( '#ocadu-upload-btn' ).on( 'click', function( e ) {
          e.preventDefault();
          var frame = wp.media( {
              title: 'Upload Images',
              frame: 'post',
              state: 'insert',
              multiple: true
          } );
          frame.on( 'open', function() {
              frame.views.get( '.media-frame-router' )[0]
                  .views.first().$el
                  .find( '[data-id="upload"]' )
                  .trigger( 'click' );
          } );
          frame.open(); // must be after the event listener
      } );
  } );
  </script>
  <p>
  <label for="illu_title">Thesis Title</label><br />
  <textarea id="illu_title" name="illu_title" style="width:100%"><?php echo esc_textarea(
    ocaduillustration_get_custom_field($post->ID, "illu_title"),
  ); ?></textarea>
  </p>
  <p>
  <label for="illu_email">Email Address</label><br />
  <input type="email" id="illu_email" name="illu_email" value="<?php echo esc_html(
    ocaduillustration_get_custom_field($post->ID, "illu_email"),
  ); ?>" style="width:100%">
  </p>
  <p>
  <label for="illu_sites">Website</label><br />
  <input type="url" id="illu_sites" name="illu_sites" placeholder="Include https://" value="<?php echo esc_url(
    ocaduillustration_get_custom_field($post->ID, "illu_sites"),
  ); ?>" style="width:100%">
  </p>
  <p>
  <label for="illu_sites_2">Website Secondary</label><br />
  <input type="url" id="illu_sites_2" name="illu_sites_2" placeholder="Include https://" value="<?php echo esc_url(
    ocaduillustration_get_custom_field($post->ID, "illu_sites_2"),
  ); ?>" style="width:100%">
  </p>
  <p>
  <label for="illu_phone">Telephone</label><br />
  <input type="tel" id="illu_phone" name="illu_phone" placeholder="Example: (416) 123-4567" value="<?php echo esc_html(
    ocaduillustration_get_custom_field($post->ID, "illu_phone"),
  ); ?>" style="width:100%">
  </p>
  <?php if (get_the_terms($post->ID, "gradyear")): ?>
    <p>
      <label for="illu_related">Related Work</label><br />
        <?php
        $class_year = get_the_terms($post->ID, "gradyear")[0]->slug;
        $ocaduillustration_args = [
          "post_type" => "illustrator",
          "posts_per_page" => -1,
          "tax_query" => [
            // phpcs:ignore
            [
              "taxonomy" => "gradyear",
              "field" => "slug",
              "terms" => $class_year,
            ],
          ],
        ];
        $ocaduillustration_illustrators = get_posts($ocaduillustration_args);
        ?>
        <select name="illu_related" id="illu_related">
          <option value="">--Select a related post--</option>
          <?php if ($ocaduillustration_illustrators):
            foreach ($ocaduillustration_illustrators as $illustrator):

              setup_postdata($illustrator);
              $selected = "";
              if (
                get_post_meta($post->ID, "illu_related", true) ===
                trim($illustrator->ID)
              ) {
                $selected = "selected";
              }
              ?>
              <?php if (trim($illustrator->ID) !== $post->ID): ?>
                <option value="<?php echo esc_attr(
                  $illustrator->ID,
                ); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html(
  $illustrator->post_title,
); ?> ● <?php echo esc_html(
   get_post_meta($illustrator->ID, "illu_title", true),
 ); ?></option>
              <?php endif; ?>
              <?php
            endforeach;
            wp_reset_postdata();
          endif; ?>
        </select>
    </p>
  <?php endif; ?>
  <?php wp_nonce_field(
    "_ocaduillustration_nonce",
    "_ocaduillustration_process",
  ); ?>
  <?php
}

/**
 * Save meta fields.
 *
 * @param  int $post_id The post ID.
 */
function ocaduillustration_save_details($post_id)
{
  if (!isset($_POST["_ocaduillustration_process"])) {
    return;
  }
  if (
    !wp_verify_nonce(
      sanitize_key($_POST["_ocaduillustration_process"]),
      "_ocaduillustration_nonce",
    )
  ) {
    return;
  }
  if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
    return;
  }
  if (!current_user_can("edit_post", $post_id)) {
    return;
  }
  if (isset($_POST["illu_email"])) {
    update_post_meta(
      $post_id,
      "illu_email",
      sanitize_email(wp_unslash($_POST["illu_email"])),
    );
  }
  if (isset($_POST["illu_sites"])) {
    update_post_meta(
      $post_id,
      "illu_sites",
      esc_url_raw(wp_unslash($_POST["illu_sites"])),
    );
  }
  if (isset($_POST["illu_sites_2"])) {
    update_post_meta(
      $post_id,
      "illu_sites_2",
      esc_url_raw(wp_unslash($_POST["illu_sites_2"])),
    );
  }
  if (isset($_POST["illu_phone"])) {
    update_post_meta(
      $post_id,
      "illu_phone",
      sanitize_text_field(wp_unslash($_POST["illu_phone"])),
    );
  }
  if (isset($_POST["illu_title"])) {
    update_post_meta(
      $post_id,
      "illu_title",
      sanitize_text_field(wp_unslash($_POST["illu_title"])),
    );
  }
  if (isset($_POST["illu_related"])) {
    update_post_meta(
      $post_id,
      "illu_related",
      sanitize_text_field(wp_unslash($_POST["illu_related"])),
    );
  }
}

add_action("save_post", "ocaduillustration_save_details");

// Extending WP-API with querying media based on post parent.

add_filter("query_vars", function ($vars) {
  $vars[] = "post_parent";
  return $vars;
});

// Custom columns for illustrator display.

add_filter(
  "manage_illustrator_posts_columns",
  "ocaduillustration_posts_columns",
  5,
);
add_action(
  "manage_illustrator_posts_custom_column",
  "ocaduillustration_posts_custom_columns",
  5,
  2,
);

/**
 * Expose illustrator meta fields in the REST API.
 */
add_action("rest_api_init", function () {
  $meta_fields = [
    "illu_title" => "string",
    "illu_email" => "string",
    "illu_sites" => "string",
    "illu_sites_2" => "string",
  ];

  foreach ($meta_fields as $key => $type) {
    register_post_meta("illustrator", $key, [
      "show_in_rest" => true,
      "single" => true,
      "type" => $type,
      "auth_callback" => fn() => current_user_can("edit_posts"),
    ]);
  }
});

/**
 * Change default columns for illustrator post type.
 *
 * @param  array $defaults The column array.
 */
function ocaduillustration_posts_columns($defaults)
{
  $new = [];
  foreach ($defaults as $key => $value) {
    if ("title" === $key) {
      $new["post_thumbs"] = __("Featured Image", "ocadu-illustration");
    }
    $new[$key] = $value;
    if ("title" === $key) {
      $new["post_email"] = __("Email", "ocadu-illustration");
      $new["post_site"] = __("Website", "ocadu-illustration");
      $new["post_name"] = __("Permalink", "ocadu-illustration");
      $new["post_images"] = __("Images", "ocadu-illustration");
    }
  }
  return $new;
}

/**
 * Add feature image to admin view.
 *
 * @param  string $column_name The column ID.
 * @param  int    $id          The post ID.
 */
function ocaduillustration_posts_custom_columns($column_name, $id)
{
  switch ($column_name) {
    case "post_thumbs":
      $thumb_id = get_post_thumbnail_id($id);
      $thumb_url_array = wp_get_attachment_image_src(
        $thumb_id,
        "thumbnail",
        true,
      );
      $thumb_url = $thumb_url_array[0];
      echo '<a href="' . esc_url(get_edit_post_link($id)) . '">';
      echo "<img width='100' height='100' src='" . esc_url($thumb_url) . "' />";
      echo "</a>";
      break;
    case "post_email":
      echo esc_attr(get_post_meta($id, "illu_email", true));
      break;
    case "post_site":
      echo esc_url(get_post_meta($id, "illu_sites", true));
      break;
    case "post_name":
      echo esc_attr(get_post_field("post_name", get_post()));
      break;
    case "post_images":
      $count = count(get_attached_media("image", $id));
      echo esc_html($count);
      break;
  }
}

/**
 * Activate WordPress Maintenance Mode.
 */
function ocaduillustration_maintenance_mode()
{
  if (file_exists(ABSPATH . ".maintenance")) {
    if (!current_user_can("manage_options") || !is_user_logged_in()) {
      wp_die(
        esc_html(
          __(
            "Briefly unavailable for scheduled maintenance. Check back in a minute.",
            "ocadu-illustration",
          ),
        ),
        esc_html(__("Maintenance", "ocadu-illustration")),
        503,
      );
    }
  }
}

add_action("get_header", "ocaduillustration_maintenance_mode");

/**
 * Set the default term for a new post.
 *
 * @param  int     $post_id The post ID.
 * @param  WP_Post $post    The post.
 */
function ocaduillustration_set_default_term($post_id, $post)
{
  if ("illustrator" === $post->post_type) {
    $taxonomy = "gradyear";
    $existing_terms = wp_get_object_terms($post_id, $taxonomy);
    if (empty($existing_terms)) {
      $term_args = [
        "taxonomy" => $taxonomy,
        "order" => "DESC",
        "parent" => 0,
        "number" => 1,
        "hide_empty" => false,
      ];
      $recent_term = get_terms($term_args);
      if (!empty($recent_term) && !is_wp_error($recent_term)) {
        wp_set_object_terms(
          $post_id,
          $recent_term[0]->term_id,
          $taxonomy,
          true,
        );
      }
    }
  }
}

add_action("save_post", "ocaduillustration_set_default_term", 10, 2);

/**
 * Set default sort order for illustrator post type to publish date descending.
 *
 * @param WP_Query $query The query object.
 */
function ocaduillustration_default_sort($query)
{
  if (
    !is_admin() ||
    !$query->is_main_query() ||
    "illustrator" !== $query->get("post_type")
  ) {
    return;
  }
  if (!isset($_GET["orderby"])) {
    $query->set("orderby", "date");
    $query->set("order", "DESC");
  }
}

add_action("pre_get_posts", "ocaduillustration_default_sort");

/**
 * Enqueue admin styles for illustrator post type.
 *
 * @param string $hook The current admin page hook.
 */
function ocaduillustration_admin_styles($hook)
{
  if ("edit.php" !== $hook) {
    return;
  }
  if (!isset($_GET["post_type"]) || "illustrator" !== $_GET["post_type"]) {
    return;
  }
  wp_add_inline_style(
    "wp-admin",
    "#the-list td, #the-list th { padding: 4px 4px; }
     #the-list td.post_thumbs img { width: 40px; height: 40px; }
     .column-post_thumbs { width: 60px; }",
  );
}

add_action("admin_enqueue_scripts", "ocaduillustration_admin_styles");
