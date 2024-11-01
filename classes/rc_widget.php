<?php

if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly.
}


// Creating the widget
class rc_widget extends WP_Widget
{

    function __construct()
    {
        parent::__construct(
// Base ID of your widget
            'rc_widget',
            __('RatingChamp Widget', 'rc_widget_domain'),
            array('description' => __('The widget integrates the RatingChamp badge in your website', 'rc_widget_domain'),)
        );
    }

// widget front-end
    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];
            echo '<!-- RatingChampWidget START -->
            <div class="RatingChampWidget" data-lang="">
            <img src="'.RC_PLUGIN_URL.'/images/loading.gif" alt="loading..." /><br />
            <a href="http://RatingChamp.de" rel="nofollow" target="_blank">RatingChamp geprüfter Shop</a>
            </div>
            <!-- RatingChampWidget END -->
            ';
        echo $args['after_widget'];
    }

// Widget Backend
    public function form($instance)
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('RatingChamp', 'rc_widget_domain');
        }
// Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

// Register and load the widget
function wpb_load_widget()
{
    register_widget('rc_widget');
    if ( is_active_widget( false, false, 'rc_widget', true ) ) {
        add_action('wp_enqueue_scripts', 'load_w_script');
        function load_w_script() {
            $options = get_option( 'ratingchamp_settings' );
            wp_enqueue_script('RatingChamp', '//widget.ratingchamp.com/js/'.$options['ratingchamp_text_field_2'].'.js', array(), '', true);
        }
    }
}

add_action('widgets_init', 'wpb_load_widget');
