<?php
/*
Plugin Name: Guest Posts Plugin
Description: A plugin to allow users to submit and manage guest posts.
Version: 1.0
Author: Your Name
*/



function wpdocs_theme_name_scripts() {
    wp_enqueue_style( 'ranvir', plugins_url( 'css/custom.css', __FILE__ ), array(), '1.0.0', 'all' );
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );
function wpdocs_enqueue_plugin_admin_style() {
    wp_enqueue_style( 'plugin-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', array(), '1.0.0', 'all' );
}
add_action( 'admin_enqueue_scripts', 'wpdocs_enqueue_plugin_admin_style' );
add_shortcode('guest_post_form', 'guest_post_form_shortcode');

function guest_post_form_shortcode() {
    ob_start(); ?>
  <form id="guest-post-form" method="post">
    <div class="title-wrap">
        <div class="title-text">
    <label for="gp_title">Title:</label></div>
    <div class="title-input">
    <input type="text" id="gp_title" name="gp_title" class="gp-input" required ><br>
</div>
</div>
<div class="content-wrap">
    <div class="content-text">
    <label for="gp_content">Content:</label>
</div>
    <div class="content-input">
    <textarea id="gp_content" name="gp_content" class="gp-textarea" required ></textarea><br>
</div>
</div>
<div class="author-wrap">
    <div class="author-text">
    <label for="gp_author">Author Name:</label>
</div><div class="author-input">
    <input type="text" id="gp_author" name="gp_author" class="gp-input" required ><br>
</div>
</div>
<div class="email-wrap">
    <div class="email-text">
    <label for="gp_email">Email:</label>
    </div>
    <div class="email-input">
    <input type="email" id="gp_email" name="gp_email" class="gp-input" required ><br>
    </div>
    </div>
<div class="submit-wrap">
    <input type="submit" name="gp_submit" value="Submit" class="gp-submit">
</div>
</form>

    <?php
    return ob_get_clean();
}
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
function gp_add_admin_menu() {
    add_menu_page('Guest Posts', 'Guest Posts', 'manage_options', 'guest-posts', 'gp_guest_posts_page');
}
add_action('admin_menu', 'gp_add_admin_menu');

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

function gp_reject_post() {
    if (isset($_GET['post_id']) && current_user_can('manage_options')) {
        $post_id = (int)$_GET['post_id'];
        wp_delete_post($post_id);
    }
    wp_redirect(admin_url('admin.php?page=guest-posts'));
    exit;
}
add_action('admin_post_gp_reject_post', 'gp_reject_post');
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
