<?php
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');

// start widget classes


class Switcher_Widget extends WP_Widget {

    function __construct() {
        $widget_ops = array(
            'classname' => 'txwt_ls_widget_class',
            'description' => __('Transifex  language Switcher.', 'txwt')
        );
        $this->WP_Widget('txwt_multilingual_ls', __('Transifex Language Switcher', 'txwt'), $widget_ops);
    }

    function form($instance) {
        $defaults = array(
            'title' => __('Choose Language', 'txwt'),
            'lang_switcher_type' => 'dd'
        );
        $txwt_ls_types = array('dd' => 'Drop Down', 'hor' => 'Horizontal');

        $instance = wp_parse_args((array) $instance, $defaults);
        $title = strip_tags($instance['title']);
        $ls_type = $instance['lang_switcher_type'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p>
            <label for="<?php echo $this->get_field_id('lang_switcher_type'); ?>"><?php _e('Type:'); ?></label>
            <select name="<?php echo $this->get_field_name('lang_switcher_type'); ?>" id="<?php echo $this->get_field_id('lang_switcher_type'); ?>" class="widefat">
                <?php foreach ($txwt_ls_types as $type => $name): ?>
                    <option value="<?php echo $type ?>"<?php selected($instance['lang_switcher_type'], $type); ?>><?php echo $name ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['lang_switcher_type'] = in_array($new_instance['lang_switcher_type'], array('dd', 'hor')) ? $new_instance['lang_switcher_type'] : $old_instance['lang_switcher_type'];
        return $instance;
    }

    function widget($args, $instance) {
        extract($args);
        $ls_type = $instance['lang_switcher_type'];
        echo $before_widget;
        $title = apply_filters('widget_title', $instance['title']);
        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }
        if ($ls_type == 'dd') {
            echo '<div class="txwt-widget-dd_switcher">';
            do_action('drop_down_switcher');
            echo '</div>';
        } else {
            echo '<div class="txwt-widget-hor_switcher">';
            do_action('hor_switcher');
            echo '</div>';
        }

        echo $after_widget;
    }

}

function txwt_widgets_init() {
    register_widget('Switcher_Widget');
}

add_action('widgets_init', 'txwt_widgets_init');
?>