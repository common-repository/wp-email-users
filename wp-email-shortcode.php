<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

add_shortcode('wp-email-users-unsubscribe', 'tsweu_unsubscribe_user_scode');

if (!function_exists('tsweu_unsubscribe_user_scode')) {
    function tsweu_unsubscribe_user_scode($atts)
    {
        $weu_arconf_buff    = get_option('weu_ar_config_options');
        $unubscribe_success = isset($weu_arconf_buff['rbtn_user_unsubscribe_success']) ? $weu_arconf_buff['rbtn_user_unsubscribe_success'] : '';
        $unubscribe_failure = isset($weu_arconf_buff['rbtn_user_unsubscribe_failure']) ? $weu_arconf_buff['rbtn_user_unsubscribe_failure'] : '';
        tsweu_setup_activation_data();
        global $wpdb;
        //INSERT INSERT CODE FOR UNSUBSCRIBED USERS
        $get_current_date        = current_time('mysql');
        $get_user_id             = intval($_GET['id']);
        $get_email               = sanitize_email($_GET['email']);
        $table_name_unsubscriber = $wpdb->prefix . 'weu_unsubscriber';
        $table_name              = $wpdb->prefix . 'weu_subscribers';
        tsweu_setup_activation_data();
        /*INSERT DETAILS OF UNSUBSCRIBERS*/
        $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name_unsubscriber WHERE email =%s", $get_email));
        if (!$rows_avail) {
            $results = $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_unsubscriber . "`(`uid`, `email`, `datetime`) VALUES (%d,%s,%s)", $get_user_id, $get_email, $get_current_date));
            $status  = $wpdb->query($wpdb->prepare("DELETE FROM " . $table_name . " WHERE `email`=%s", $get_email)); // delete query for delete UNSUBSCRIBERS from SUBSCRIBERS data base.
            if ($results == "false") {
                return $unubscribe_failure;
            } else {
                return $unubscribe_success;
            }
        } else {
            return "Already Usubscribed";
        }
    }
}

add_shortcode('wp-email-users-subscribe', 'tsweu_subscribe_user_scode');

if (!function_exists('tsweu_subscribe_user_scode')) {
    function tsweu_subscribe_user_scode($atts)
    {
        $list        = $atts['list'];
        $get_user_id = intval($_GET['id']);
        $user_info   = get_userdata($get_user_id);
        $username    = $user_info->user_login;
        $get_email   = sanitize_email($_GET['email']);
        global $wpdb;
        $table_name              = $wpdb->prefix . 'weu_subscribers';
        $sub_name                = sanitize_text_field($username);
        $sub_email               = sanitize_text_field($get_email);
        $sub_list                = sanitize_text_field($list);
        $weu_subname             = isset($sub_name) ? $sub_name : '';
        $weu_subemail            = isset($sub_email) ? $sub_email : '';
        $weu_sublist             = isset($sub_list) ? $sub_list : '';
        $table_name_unsubscriber = $wpdb->prefix . 'weu_unsubscriber';
        $random_token            = rand(1000000, 9999999);
        $curr_date               = current_time('mysql');
        tsweu_setup_activation_data();
        $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email =%s", $weu_subemail));
        $status     = $wpdb->query($wpdb->prepare("DELETE FROM " . $table_name_unsubscriber . " WHERE `email`=%s", $get_email));
        if (!$rows_avail) {
            $status = $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`name`, `email`, `list`, `authtoken`, `datetime`) VALUES (%s,%s,%s,%d,%s)", $weu_subname, $weu_subemail, $weu_sublist, $random_token, $curr_date));
            if ($status == 1) {
                return "YOU HAVE SUCCESSFULLY SUBSCRIBED..";
            } else {
                return "FAILED TO SUBSCRIBED..";
            }
        } else {
            return "Already SUBSCRIBED..";
        }
    }
}

add_shortcode('wp-email-users-group', 'tsweu_group_user_scode');

if (!function_exists('tsweu_group_user_scode')) {
    function tsweu_group_user_scode($atts)
    {
        $list        = $atts['list'];
        $get_user_id = intval($_GET['id']);
        $user_info   = get_userdata($get_user_id);
        $username    = $user_info->user_login;
        $get_email   = sanitize_email($_GET['email']);
        global $wpdb;
        $table_name   = $wpdb->prefix . 'weu_subscribers';
        $sub_name     = sanitize_text_field($username);
        $sub_email    = sanitize_text_field($get_email);
        $sub_list     = sanitize_text_field($list);
        $weu_subname  = isset($sub_name) ? $sub_name : '';
        $weu_subemail = isset($sub_email) ? $sub_email : '';
        $weu_sublist  = isset($sub_list) ? $sub_list : '';
        $random_token = rand(1000000, 9999999);
        $curr_date    = current_time('mysql');
        tsweu_setup_activation_data();
        $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email =%s", $weu_subemail));
        if (!$rows_avail) {
            $status = $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`user_name`, `email`, `datetime`) VALUES (%s,%s,%s)", $weu_subname, $weu_subemail, $weu_sublist, $random_token, $curr_date));
            if ($status == 1) {
                return "YOU HAVE SUCCESSFULLY SUBSCRIBED..";
            } else {
                return "FAILED TO SUBSCRIBED..";
            }
        } else {
            return "Already SUBSCRIBED..";
        }
    }
}
