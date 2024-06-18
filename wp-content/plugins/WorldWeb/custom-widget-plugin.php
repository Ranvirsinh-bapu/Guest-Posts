<?php
/*
Plugin Name: Custom Widget Plugin
Description: A custom widget plugin with settings and widget page.
Version: 1.0
Author: Your Name
*/

class Custom_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'custom_widget',
            __('CWP Setting & Widget page custom Widget', 'text_domain'),
            array('description' => __('A Custom Widget', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
       
        echo '<div style="background:' . $instance['background_color'] . ';">';
        echo '<p>' . $instance['content'] . '</p>';
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $content = !empty($instance['content']) ? $instance['content'] : '';
        $background_color = !empty($instance['background_color']) ? $instance['background_color'] : '#ffffff';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('content'); ?>"><?php _e('Content:'); ?></label> 
            <textarea class="widefat" id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>"><?php echo esc_attr($content); ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('background_color'); ?>"><?php _e('Background Color:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('background_color'); ?>" name="<?php echo $this->get_field_name('background_color'); ?>" type="text" value="<?php echo esc_attr($background_color); ?>">
        </p>
        <?php 
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['content'] = (!empty($new_instance['content'])) ? strip_tags($new_instance['content']) : '';
        $instance['background_color'] = (!empty($new_instance['background_color'])) ? strip_tags($new_instance['background_color']) : '#ffffff';

        if (empty($instance['title']) || empty($instance['content'])) {
            return $old_instance;
        }

        if (!preg_match('/^#[a-f0-9]{6}$/i', $instance['background_color'])) {
            return $old_instance;
        }

        return $instance;
    }
}

function register_custom_widget() {
    register_widget('Custom_Widget');
}
add_action('widgets_init', 'register_custom_widget');

add_action('admin_menu', 'custom_plugin_add_admin_menu');
add_action('admin_init', 'custom_plugin_settings_init');

function custom_plugin_add_admin_menu() {
    add_menu_page('WP Setting & Widget page Settings', 'WP Setting & Widget page', 'manage_options', 'custom_plugin', 'custom_plugin_options_page');
}

function custom_plugin_settings_init() {
    register_setting('pluginPage', 'custom_plugin_settings', 'custom_plugin_validate_settings');

    add_settings_section(
        'custom_plugin_pluginPage_section',
        __('WP Setting & Widget page', 'wordpress'),
        'custom_plugin_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'custom_plugin_text_field_0',
        __('Title:', 'wordpress'),
        'custom_plugin_text_field_0_render',
        'pluginPage',
        'custom_plugin_pluginPage_section'
    );

    add_settings_field(
        'custom_plugin_text_field_1',
        __('Description: ', 'wordpress'),
        'custom_plugin_text_field_1_render',
        'pluginPage',
        'custom_plugin_pluginPage_section'
    );
    add_settings_field(
        'custom_plugin_text_field_2',
        __('Editor content: ', 'wordpress'),
        'custom_plugin_text_field_2_render',
        'pluginPage',
        'custom_plugin_pluginPage_section'
    );
    add_settings_field(
        'custom_plugin_text_field_3',
        __('Date: ', 'wordpress'),
        'custom_plugin_text_field_3_render',
        'pluginPage',
        'custom_plugin_pluginPage_section'
    );
    add_settings_field(
        'custom_plugin_text_field_4',
        __('Image: ', 'wordpress'),
        'custom_plugin_text_field_4_render',
        'pluginPage',
        'custom_plugin_pluginPage_section'
    );
    add_settings_field(
        'custom_plugin_text_field_5',
        __('Color Picker: ', 'wordpress'),
        'custom_plugin_text_field_5_render',
        'pluginPage',
        'custom_plugin_pluginPage_section'
    );
}

function custom_plugin_text_field_1_render() {
    $options = get_option('custom_plugin_settings');
    ?>
    <textarea name='custom_plugin_settings[custom_plugin_text_field_1]' rows='5' cols='50'><?php echo isset($options['custom_plugin_text_field_1']) ? esc_attr($options['custom_plugin_text_field_1']) : ''; ?></textarea>
    <?php
}

function custom_plugin_text_field_3_render() {
    $options = get_option('custom_plugin_settings');
    ?>
    <input type='date' name='custom_plugin_settings[custom_plugin_text_field_3]' value='<?php echo isset($options['custom_plugin_text_field_3']) ? esc_attr($options['custom_plugin_text_field_3']) : ''; ?>'>
    <?php
}

function custom_plugin_text_field_4_render() {
    $options = get_option('custom_plugin_settings');
    $image_url = isset($options['custom_plugin_text_field_4']) ? esc_attr($options['custom_plugin_text_field_4']) : '';
    ?>
    <input type='text' id='custom_plugin_image_url' name='custom_plugin_settings[custom_plugin_text_field_4]' value='<?php echo $image_url; ?>'>
    <input type='button' id='custom_plugin_upload_button' class='button-secondary' value='Upload Image'>
    <script>
    jQuery(document).ready(function($) {
        $('#custom_plugin_upload_button').click(function(e) {
            e.preventDefault();
            var custom_uploader = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Select'
                },
                multiple: false
            });
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#custom_plugin_image_url').val(attachment.url);
            });
            custom_uploader.open();
        });
    });
    </script>
    <?php
}


function custom_plugin_text_field_5_render() {
    $options = get_option('custom_plugin_settings');
    ?>
    <input type='color' name='custom_plugin_settings[custom_plugin_text_field_5]' value='<?php echo isset($options['custom_plugin_text_field_5']) ? esc_attr($options['custom_plugin_text_field_5']) : '#ffffff'; ?>'>
    <?php
}

function custom_plugin_validate_settings($input) {
    $validated = array();
  
    if (isset($input['custom_plugin_text_field_0'])) {
        $validated['custom_plugin_text_field_0'] = sanitize_text_field($input['custom_plugin_text_field_0']);
    } else {
        add_settings_error('custom_plugin_settings', 'invalid-text-field-0', 'Text Field 1 is required');
    }
    
   
    if (isset($input['custom_plugin_text_field_1'])) {
        $validated['custom_plugin_text_field_1'] = sanitize_text_field($input['custom_plugin_text_field_1']);
    } else {
        add_settings_error('custom_plugin_settings', 'invalid-text-field-1', 'Text Field 2 is required');
    }

    return $validated;
}

function custom_plugin_text_field_0_render() {
    $options = get_option('custom_plugin_settings');
    ?>
    <input type='text' name='custom_plugin_settings[custom_plugin_text_field_0]' value='<?php echo isset($options['custom_plugin_text_field_0']) ? esc_attr($options['custom_plugin_text_field_0']) : ''; ?>'>
    <?php
}

function custom_plugin_text_field_2_render() {
    $options = get_option('custom_plugin_settings');
    $content = isset($options['custom_plugin_text_field_2']) ? $options['custom_plugin_text_field_2'] : '';
    $editor_id = 'custom_plugin_text_field_2_editor'; 
    
   
    $settings = array(
        'textarea_name' => 'custom_plugin_settings[custom_plugin_text_field_2]',
        'textarea_rows' => 10, 
        'wpautop' => true 
    );

    wp_editor($content, $editor_id, $settings);
}


function custom_plugin_settings_section_callback() {
    echo __(' ', 'wordpress');
}

function custom_plugin_options_page() {
    ?>
    <form action='options.php' method='post'>
        <h2>WP Setting & Widget page</h2>
        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>
    </form>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="reset_settings">
        <input type="hidden" name="reset" value="reset">
        <?php submit_button('Reset All Settings', 'secondary'); ?>
    </form>
    <?php
}

add_action('admin_post_reset_settings', 'custom_plugin_reset_settings');

function custom_plugin_reset_settings() {
    if (isset($_POST['reset']) && $_POST['reset'] === 'reset') {
        
        $default_settings = array(
            'custom_plugin_text_field_0' => '',
            'custom_plugin_text_field_1' => '',
        );
        update_option('custom_plugin_settings', $default_settings);

        
        wp_redirect(add_query_arg(array('page' => 'custom_plugin', 'reset' => 'true'), admin_url('admin.php')));
        exit;
    }
}

add_action('admin_notices', 'custom_plugin_admin_notices');

function custom_plugin_admin_notices() {
    if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
        echo '<div class="notice notice-success is-dismissible">
                 <p>Settings have been reset to default.</p>
             </div>';
    }
}
?>
