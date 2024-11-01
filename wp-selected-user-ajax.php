<?php
if (!defined('ABSPATH'))
    exit;
add_action('wp_ajax_weu_selected_users_1', 'tsweu_callbackfunction_select');
add_action('wp_ajax_weu_selected_users_temp', 'tsweu_callbackfunction_select2');
add_action('wp_ajax_weu_selected_users_sub', 'tsweu_callbackfunction_select_sub');

if (!function_exists('tsweu_callbackfunction_select')) {
    function tsweu_callbackfunction_select()
    {
        global $wpdb;
        $table_name       = $wpdb->prefix . 'weu_user_notification';
        $table_name_users = $wpdb->prefix . 'users';
        $data             = $_POST['data_raw'];
        $data1            = $_POST['data_raw'];
        $myrows           = $wpdb->get_results("SELECT `email_value` from `" . $table_name . "` WHERE template_id = $data[0]");
        $datas            = unserialize($myrows[0]->email_value);
        $users_ids        = array();
        $users_data       = array();
        if (is_array($datas)) {
            foreach ($datas as $value) {
                $myrows_users = $wpdb->get_results("SELECT `ID` FROM `" . $table_name_users . "` WHERE `user_email` = $value");
                foreach ($myrows_users as $users) {
                    array_push($users_ids, $users->ID);
                }
            }
        } else {
            $myrows_users = $wpdb->get_results("SELECT `ID` FROM `" . $table_name_users . "` WHERE  `user_email` = '$datas'");
            foreach ($myrows_users as $users) {
                array_push($users_ids, $users->ID);
            }
        }

        echo json_encode($users_ids);
        wp_die();
    }
}

if (!function_exists('tsweu_callbackfunction_select2')) {
    function tsweu_callbackfunction_select2()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_user_notification';
        $data1      = $_POST['data_raw'];
        $user_temp  = $wpdb->get_results("select template_value from `" . $table_name . "` where template_id =" . $data1[0]);
        $temp_Data  = $user_temp[0]->template_value;
        echo $temp_Data;
        wp_die();
    }
}

if (!function_exists('tsweu_callbackfunction_select_sub')) {
    function tsweu_callbackfunction_select_sub()
    {
        $data1 = sanitize_text_field($_POST['data_raw']);
        switch ($data1[0]) {
            case 1:
                $subject = get_option('weu_new_user_register');
                break;
            case 2:
                $subject = get_option('weu_new_post_publish');
                break;
            case 3:
                $subject = get_option('weu_new_comment_post');
                break;
            case 4:
                $subject = get_option('weu_password_reset');
                break;
            case 5:
                $subject = get_option('weu_user_role_changed');
                break;
        }
        echo $subject;
        wp_die();
    }
}
