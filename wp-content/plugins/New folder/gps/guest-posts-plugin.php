<?php
/*
Plugin Name: Guest Posts Plugin
Description: A plugin to allow users to submit and manage guest posts.
Version: 1.0
Author: Your Name
*/



function wpdocs_theme_name_scripts() {
	wp_enqueue_style( 'ranvir', get_stylesheet_uri() . '/css/custom.css' , array(), '1.0.0', true);
	// wp_enqueue_script( 'script-name', get_template_directory_uri() . '/js/example.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );

add_shortcode('guest_post_form', 'guest_post_form_shortcode');

function guest_post_form_shortcode() {
    ob_start(); ?>
  <form id="guest-post-form" method="post">
    <label for="gp_title">Title:</label>
    <input type="text" id="gp_title" name="gp_title" class="gp-input" required><br>

    <label for="gp_content">Content:</label>
    <textarea id="gp_content" name="gp_content" class="gp-textarea" required></textarea><br>

    <label for="gp_author">Author Name:</label>
    <input type="text" id="gp_author" name="gp_author" class="gp-input" required><br>

    <label for="gp_email">Email:</label>
    <input type="email" id="gp_email" name="gp_email" class="gp-input" required><br>

    <input type="submit" name="gp_submit" value="Submit" class="gp-submit">
</form>

    <?php
    return ob_get_clean();
}

// Register the custom post type
function gp_register_custom_post_type() {
    register_post_type('guest_posts', [
        'labels' => [
            'name' => 'Guest Posts',
            'singular_name' => 'Guest Post',
        ],
        'public' => true,
        'has_archive' => true,
    ]);
}
add_action('init', 'gp_register_custom_post_type');

// Handle form submission
function gp_handle_form_submission() {
    if (isset($_POST['gp_submit'])) {
        $title = sanitize_text_field($_POST['gp_title']);
        $content = sanitize_textarea_field($_POST['gp_content']);
        $author = sanitize_text_field($_POST['gp_author']);
        $email = sanitize_email($_POST['gp_email']);
        
        if (!empty($title) && !empty($content) && !empty($author) && is_email($email)) {
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => $content,
                'post_type' => 'guest_posts',
                'post_status' => 'pending',
            ]);

            if ($post_id) {
                add_post_meta($post_id, 'gp_author_name', $author);
                add_post_meta($post_id, 'gp_author_email', $email);
            }
        }
    }
}
add_action('template_redirect', 'gp_handle_form_submission');
// Add admin menu
function gp_add_admin_menu() {
    add_menu_page('Guest Posts', 'Guest Posts', 'manage_options', 'guest-posts', 'gp_guest_posts_page');
}
add_action('admin_menu', 'gp_add_admin_menu');

// Display the admin page
function gp_guest_posts_page() {
    global $wpdb;
    $posts_per_page = 10;
    $paged = isset($_GET['paged']) ? (int)$_GET['paged'] : 1;
    $offset = ($paged - 1) * $posts_per_page;

    $guest_posts = new WP_Query([
        'post_type' => 'guest_posts',
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,
        'post_status' => 'pending',
    ]);

    if ($guest_posts->have_posts()) {
        echo '<table>';
        echo '<tr><th>Title</th><th>Author</th><th>Submission Date</th><th>Actions</th></tr>';
        while ($guest_posts->have_posts()) {
            $guest_posts->the_post();
            $author_name = get_post_meta(get_the_ID(), 'gp_author_name', true);
            echo '<tr>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . esc_html($author_name) . '</td>';
            echo '<td>' . get_the_date() . '</td>';
            echo '<td>';
            echo '<a href="' . admin_url('admin-post.php?action=gp_approve_post&post_id=' . get_the_ID()) . '">Approve</a> | ';
            echo '<a href="' . admin_url('admin-post.php?action=gp_reject_post&post_id=' . get_the_ID()) . '">Reject</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        // Pagination
        $total_posts = $guest_posts->found_posts;
        $total_pages = ceil($total_posts / $posts_per_page);
        if ($total_pages > 1) {
            $current_page = max(1, get_query_var('paged'));
            echo '<div class="pagination">';
            echo paginate_links([
                'base' => add_query_arg('paged', '%#%'),
                'format' => '?paged=%#%',
                'current' => $current_page,
                'total' => $total_pages,
            ]);
            echo '</div>';
        }
    } else {
        echo '<p>No guest posts found.</p>';
    }

    wp_reset_postdata();
}
// Approve guest post
function gp_approve_post() {
    if (isset($_GET['post_id']) && current_user_can('manage_options')) {
        $post_id = (int)$_GET['post_id'];
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish',
        ]);
    }
    wp_redirect(admin_url('admin.php?page=guest-posts'));
    exit;
}
add_action('admin_post_gp_approve_post', 'gp_approve_post');

// Reject guest post
function gp_reject_post() {
    if (isset($_GET['post_id']) && current_user_can('manage_options')) {
        $post_id = (int)$_GET['post_id'];
        wp_delete_post($post_id);
    }
    wp_redirect(admin_url('admin.php?page=guest-posts'));
    exit;
}
add_action('admin_post_gp_reject_post', 'gp_reject_post');

// Create custom table upon plugin activation
function gp_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_post_meta';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        meta_key varchar(255) NOT NULL,
        meta_value longtext NOT NULL,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        KEY meta_key (meta_key(191))
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'gp_create_custom_table');
function add_custom_post_meta($post_id, $meta_key, $meta_value) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_post_meta';

    $wpdb->insert(
        $table_name,
        [
            'post_id'    => $post_id,
            'meta_key'   => $meta_key,
            'meta_value' => maybe_serialize($meta_value)
        ],
        [
            '%d',
            '%s',
            '%s'
        ]
    );

    return $wpdb->insert_id;
}
function get_custom_post_meta($post_id, $meta_key = '', $single = false) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_post_meta';

    if ($meta_key == '') {
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM $table_name WHERE post_id = %d",
            $post_id
        ), ARRAY_A);
        $meta = [];
        foreach ($result as $row) {
            $meta[$row['meta_key']][] = maybe_unserialize($row['meta_value']);
        }
        return $meta;
    } else {
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $table_name WHERE post_id = %d AND meta_key = %s",
            $post_id, $meta_key
        ));
        if ($result !== null) {
            $result = maybe_unserialize($result);
            return $single ? $result : [$result];
        }
        return $single ? '' : [];
    }
}
function update_custom_post_meta($post_id, $meta_key, $meta_value) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_post_meta';

    if ($wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND meta_key = %s",
        $post_id, $meta_key
    )) > 0) {
        $wpdb->update(
            $table_name,
            ['meta_value' => maybe_serialize($meta_value)],
            ['post_id' => $post_id, 'meta_key' => $meta_key],
            ['%s'],
            ['%d', '%s']
        );
    } else {
        add_custom_post_meta($post_id, $meta_key, $meta_value);
    }
}
function delete_custom_post_meta($post_id, $meta_key, $meta_value = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_post_meta';

    if ($meta_value == '') {
        $wpdb->delete(
            $table_name,
            ['post_id' => $post_id, 'meta_key' => $meta_key],
            ['%d', '%s']
        );
    } else {
        $wpdb->delete(
            $table_name,
            ['post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => maybe_serialize($meta_value)],
            ['%d', '%s', '%s']
        );
    }
}
add_filter('get_post_metadata', 'custom_get_post_meta_filter', 10, 4);
function custom_get_post_meta_filter($check, $object_id, $meta_key, $single) {

    remove_filter('get_post_metadata', 'custom_get_post_meta_filter', 10, 4);
    
    $value = get_custom_post_meta($object_id, $meta_key, $single);


    add_filter('get_post_metadata', 'custom_get_post_meta_filter', 10, 4);
    
    if ($value !== '' && $value !== []) {
        return $value;
    }

    return $check;
}

add_filter('add_post_metadata', 'custom_add_post_meta_filter', 10, 5);
function custom_add_post_meta_filter($check, $object_id, $meta_key, $meta_value, $unique) {
    add_custom_post_meta($object_id, $meta_key, $meta_value);
    return true; 
}

add_filter('update_post_metadata', 'custom_update_post_meta_filter', 10, 5);
function custom_update_post_meta_filter($check, $object_id, $meta_key, $meta_value, $prev_value) {
    update_custom_post_meta($object_id, $meta_key, $meta_value);
    return true; 
}

add_filter('delete_post_metadata', 'custom_delete_post_meta_filter', 10, 5);
function custom_delete_post_meta_filter($check, $object_id, $meta_key, $meta_value, $delete_all) {
    delete_custom_post_meta($object_id, $meta_key, $meta_value);
    return true; 
}
