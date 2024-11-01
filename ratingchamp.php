<?php
/**
 * Plugin Name: RatingChamp
 * Plugin URI: http://www.RatingChamp.com/
 * Description: Integration of RatingChamp in your WordPress Installation
 * Version: 1.0.0
 * Author: 4tfm
 * Author URI: http://www.RatingChamp.com/

 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'RC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'RC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once( RC_PLUGIN_PATH . 'classes/rc_widget.php' );

add_action( 'admin_menu', 'ratingchamp_add_admin_menu' );
add_action( 'admin_init', 'ratingchamp_settings_init' );


function ratingchamp_add_admin_menu(  ) {

    add_options_page( 'RatingChamp', 'RatingChamp', 'manage_options', 'ratingchamp', 'ratingchamp_options_page' );

}


function ratingchamp_settings_init(  ) {

    register_setting( 'pluginPage', 'ratingchamp_settings' );

    add_settings_section(
        'ratingchamp_pluginPage_section',
        __( 'RatingChamp API Setup', 'wordpress' ),
        'ratingchamp_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'ratingchamp_select_field_0',
        __( 'Order status for transfer', 'wordpress' ),
        'ratingchamp_select_field_0_render',
        'pluginPage',
        'ratingchamp_pluginPage_section'
    );

    add_settings_field(
        'ratingchamp_text_field_2',
        __( 'RatingChamp API Account-Key', 'wordpress' ),
        'ratingchamp_text_field_2_render',
        'pluginPage',
        'ratingchamp_pluginPage_section'
    );

    add_settings_field(
        'ratingchamp_text_field_3',
        __( 'RatingChamp API Password', 'wordpress' ),
        'ratingchamp_text_field_3_render',
        'pluginPage',
        'ratingchamp_pluginPage_section'
    );


}


function ratingchamp_select_field_0_render(  ) {

    $options = get_option( 'ratingchamp_settings' );

    ?>
    <select name='ratingchamp_settings[ratingchamp_select_field_0]'>

        <?php
        $status_list = wc_get_order_statuses();

        foreach($status_list as $k => $v){
            echo '<option value="'.$k.'" '. selected( $options['ratingchamp_select_field_0'], $k,false ) .'>'.$v.'</option>';
        }
        ?>


    </select>

    <?php

}


function ratingchamp_text_field_2_render(  ) {

    $options = get_option( 'ratingchamp_settings' );
    ?>
    <input type='text' name='ratingchamp_settings[ratingchamp_text_field_2]' value='<?php echo $options['ratingchamp_text_field_2']; ?>'>
    <?php

}


function ratingchamp_text_field_3_render(  ) {

    $options = get_option( 'ratingchamp_settings' );
    ?>
    <input type='text' name='ratingchamp_settings[ratingchamp_text_field_3]' value='<?php echo $options['ratingchamp_text_field_3']; ?>'>
    <?php

}

function ratingchamp_settings_section_callback(  ) {

    echo __( 'You find your RatingChamp account details in the the <a href="http://cp.ratingchamp.com/" target="_blank"> control panel</a> (Configuration => Shop information)<br />If you not yet have an account, <a href="https://ratingchamp.com/pricing/" target="_blank">sign up for our free plan</a>', 'wordpress' );

}



function ratingchamp_options_page(  ) {

    ?>
    <form action='options.php' method='post'>

        <h2>RatingChamp</h2>

        <?php
        settings_fields( 'pluginPage' );
        do_settings_sections( 'pluginPage' );
        submit_button();
        ?>

    </form>
    <?php

}

function notifiy_ratingchamp($order_id){


    $order = new WC_Order( $order_id );
    $options = get_option( 'ratingchamp_settings' );


    if($order->post->post_status == $options['ratingchamp_select_field_0'] ){


        // status:

        $name = explode(' ',$order->get_formatted_billing_full_name());
        $name_tmp = array_reverse($name);

        $firstname = str_replace($name_tmp['0'],' ', implode(' ',$name));
        $lastname = $name_tmp['0'];


        $options = get_option( 'ratingchamp_settings' );

        $order_data = array();
        $order_data['merchant_orders_id']= $order->id;
        $order_data['customers_firstname']= $firstname;
        $order_data['customers_lastname']= $lastname;

        $order_data['customers_gender']= 'none';
        $order_data['customers_email_address']= $order->billing_email;
        // ToDo: get language... (maybe: plugin-option for default or get WP-Lang)
        $order_data['orders_language']='de';

        $data_string = json_encode($order_data);

        $ch = curl_init('https://api.ratingchamp.com/api/public/order');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $options['ratingchamp_text_field_2'] . ":" . $options['ratingchamp_text_field_3']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);

        return $result;
    }

}

add_action('woocommerce_order_status_changed', 'notifiy_ratingchamp', 10, 1);

?>