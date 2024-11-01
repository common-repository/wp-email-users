<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
add_action('wp_ajax_nopriv_weu_subscribe_users_nl', 'tsweu_subscribe_users_12854');
add_action('wp_ajax_weu_subscribe_users_nl', 'tsweu_subscribe_users_12854');

if (!function_exists('tsweu_subscribe_users_12854')) {
    function tsweu_subscribe_users_12854()
    {
        global $wpdb;
        $table_name   = $wpdb->prefix . 'weu_subscribers';
        $sub_name     = sanitize_text_field($_POST['sub_name']);
        $sub_email    = sanitize_text_field($_POST['sub_email']);
        $sub_list     = sanitize_text_field($_POST['sub_list']);
        $weu_subname  = isset($sub_name) ? $sub_name : '';
        $weu_subemail = isset($sub_email) ? $sub_email : '';
        $weu_sublist  = isset($sub_list) ? $sub_list : '';
        $random_token = rand(1000000, 9999999);
        $curr_date    = current_time('mysql');
        tsweu_setup_activation_data();
        $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email =%s", $weu_subemail));
        if (!$rows_avail) {
            $status = $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`name`, `email`, `list`, `authtoken`, `datetime`) VALUES (%s,%s,%s,%d,%s)", $weu_subname, $weu_subemail, $weu_sublist, $random_token, $curr_date));
            if ($status == 1) {
                echo "success";
            } else {
                echo "fail";
            }
        } else {
            echo "exist";
        }
        wp_die();
    }
}
