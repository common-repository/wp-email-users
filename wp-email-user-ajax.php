<?php
if (!defined('ABSPATH'))
    exit;
$temp_name = isset($_POST['temp_key']) ? sanitize_text_field($_POST['temp_key']) : '';
$csv_name  = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
if ($csv_name == 'edit' || $csv_name == 'delete' || $csv_name == 'update') {
    add_action('wp_ajax_weu_my_csv_action', 'tsweu_csv_action_callback');
    if (!function_exists('tsweu_csv_action_callback')) {
        function tsweu_csv_action_callback()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'email_user';
            $csvlist    = sanitize_text_field($_POST['csv_file_title']);
            $del_key    = sanitize_text_field($_POST['del_csv']);
            if ($del_key == 'del_csv') {
                $myrows = $wpdb->query($wpdb->prepare("DELETE FROM `" . $table_name . "` where id =%s", $csvlist));
                $data   = 'File Deleted';
                echo $data;
            }
            $edit_key = sanitize_text_field($_POST['edit_csv']);
            if ($edit_key == 'edit_csv') {
                $myrows = $wpdb->get_results($wpdb->prepare("SELECT id,template_value FROM `" . $table_name . "` where id =%s", $csvlist));
                $data   = unserialize($myrows[0]->template_value);
                echo "<table border='4' id='show_table' style='width:93%; text-align: center;'><th>First Name</th><th>Last Name</th><th>Email</th>";
                foreach ($data as $line) {
                    list($name, $last, $email) = explode(',', $line);
                    echo "<tr><td>" . $name . "</td>";
                    echo "<td>" . $last . "</td>";
                    echo "<td>" . $email . "</td></tr>";
                }
                echo "</table>";
            }
            $update_val = sanitize_text_field($_POST['update_val']);
            $update_key = sanitize_text_field($_POST['update_csv']);
            if ($update_key == 'update_csv') {
                $myrows = $wpdb->query($wpdb->prepare("UPDATE `" . $table_name . "` SET template_value = %s where id =%s", $update_val, $csvlist));
                $data   = $myrows;
                echo $data;
            }
            wp_die();
        }
    }
}

add_action('wp_ajax_weu_my_action', 'tsweu_tem_action_callback');

if (!function_exists('tsweu_tem_action_callback')) {
    function tsweu_tem_action_callback()
    {
        global $wpdb;
        $table_name   = $wpdb->prefix . 'email_user';
        $temp         = sanitize_text_field($_POST['filetitle']);
        $temp_del_key = sanitize_text_field($_POST['temp_del_key']);
        if ($temp_del_key == 'delete_temp') {
            $myrows = $wpdb->query($wpdb->prepare("DELETE FROM `" . $table_name . "` where id =%s", $temp));
            $data   = 'Template Deleted';
        }
        $selected_template = sanitize_text_field($_POST['sel_temp_key']);
        if ($selected_template == 'select_temp') {
            $myrows = $wpdb->get_results($wpdb->prepare("SELECT template_value FROM `" . $table_name . "` where id =%s", $temp));
            $data   = $myrows[0]->template_value;
        }
        echo $data;
        wp_die();
    }
}
