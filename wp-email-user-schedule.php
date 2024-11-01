<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
$rbtn        = sanitize_text_field($_POST['rbtn']);
$radioButton = isset($rbtn) ? $rbtn : '';
$wau_to      = array();
if ($radioButton == 'user') {
    $userName = sanitize_text_field($_POST['ea_user_name']);
    for ($j = 0; $j < count($userName); $j++) {
        $user = $userName[$j];
        array_push($wau_to, sanitize_text_field($_POST[$user]));
    }
} elseif ($radioButton == 'role') {
    $urole = sanitize_text_field($_POST['user_role']);
    for ($k = 0; $k < count($urole); $k++) {
        $args          = array(
            'role' => $urole[$k]
        );
        $wau_grp_users = get_users($args); //get all users
        for ($m = 0; $m < count($wau_grp_users); $m++) {
            array_push($wau_to, $wau_grp_users[$m]->data->user_email);
        }
    }
} elseif ($radioButton == 'csv') {
    $csv_id = sanitize_text_field($_POST['csv_file_id']);
    for ($j = 0; $j < count($csv_id); $j++) {
        $myrows = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_email_user WHERE status =%s and id=%s", 'csv', $csv_id[$j]));
        $mixed  = unserialize($myrows[0]->template_value);
        $csv    = $csv_id[$j];
        $csv_to = array();
        foreach ($mixed as $line) {
            list($name, $last, $email) = explode(',', $line);
            array_push($csv_to, $email);
        }
    }
}
global $wpdb;
$schedule_mail            = array();
$fr_name                  = sanitize_text_field($_POST['wau_from_name']);
$from                     = sanitize_text_field($_POST['wau_from']);
$sub                      = sanitize_text_field($_POST['wau_sub']);
$content                  = sanitize_text_field($_POST['wau_mailcontent']);
$schedule_mail['name']    = isset($fr_name) ? $fr_name : '';
$schedule_mail['from']    = isset($from) ? $from : '';
$schedule_mail['subject'] = isset($sub) ? $sub : '';
$schedule_mail['body']    = isset($content) ? $content : '';
update_option('updated_option_table', $schedule_mail);
update_option('updated_option_table1', $wau_to);
update_option('updated_option_table2', $csv_to);
global $current_user, $wpdb;
$user_roles = $current_user->roles;
if ($user_roles[0] == 'administrator') {
    if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'csv') {
        $file_id = sanitize_text_field($_POST['csv_file_id']);
        for ($j = 0; $j < count($file_id); $j++) {
            $myrows = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_email_user WHERE status =%s and id=%s", 'csv', $file_id[$j]));
            $mixed  = unserialize($myrows[0]->template_value);
            $csv    = $file_id[$j];
            $csv_to = array();
            foreach ($mixed as $line) {
                list($name, $last, $email) = explode(',', $line);
                array_push($csv_to, $email);
            }
            $mail_body  = isset($_POST['wau_mailcontent']) ? $_POST['wau_mailcontent'] : "";
            $subject    = sanitize_text_field($_POST['wau_sub']);
            $body       = wp_kses_stripslashes($mail_body);
            $from_email = sanitize_email($_POST['wau_from']);
            $from_name  = sanitize_text_field($_POST['wau_from_name']);
            $headers[]  = 'From: ' . $from_name . ' <' . $from_email . '>';
            $headers[]  = 'Content-Type: text/html; charset="UTF-8"';
            $wau_status = wp_mail($csv_to, $subject, $body, $headers);
        }
    }
    $wau_to = array();
    if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'user') {
        $uname = sanitize_text_field($_POST['ea_user_name']);
        for ($j = 0; $j < count($uname); $j++) {
            $user = $uname[$j];
            array_push($wau_to, sanitize_text_field($_POST[$user]));
        }
    } elseif (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'role') {
        $urole         = sanitize_text_field($_POST['user_role']);
        for ($k = 0; $k < count($urole); $k++) {
            $args          = array(
                'role' => $urole[$k]
            );
            $wau_grp_users = get_users($args); //get all users
            for ($m = 0; $m < count($wau_grp_users); $m++) {
                array_push($wau_to, $wau_grp_users[$m]->data->user_email);
            }
        }
    }
    /* Send mail to user using wp_mail */
    global $wpdb;
    $wau_status = 2;
    $wau_too    = array();
    if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'user' || isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'role') {
        $temp_key   = sanitize_text_field($_POST['wau_sub']); // subject as a key
        $chk_val    = sanitize_text_field($_POST['temp']); // save template checkbox val
        $table_name = $wpdb->prefix . 'email_user';
        tsweu_setup_activation_data();
        if ($chk_val == 1)
            $mail_body = sanitize_text_field($_POST['wau_mailcontent']);
        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`) VALUES (%s,%s,%s)", $temp_key, sanitize_text_field($mail_body), 'template'));

        for ($j = 0; $j < count($wau_to); $j++) {
            $curr_email_data = get_user_by('email', $wau_to[$j]);
            $user_id         = $curr_email_data->ID;
            $user_info       = get_userdata($user_id);
            $user_val        = get_user_meta($user_id);
            array_push($wau_too, $user_info->display_name);
            $replace    = array(
                $user_val['nickname'][0],
                $user_val['first_name'][0],
                $user_val['last_name'][0],
                get_option('blogname'),
                $wau_too[$j],
                $wau_to[$j]
            );
            $find       = array(
                '[[user-nickname]]',
                '[[first-name]]',
                '[[last-name]]',
                '[[site-title]]',
                '[[display-name]]',
                '[[user-email]]'
            );
            $m_content  = isset($_POST['wau_mailcontent']) ? $_POST['wau_mailcontent'] : "";
            $mail_body  = str_replace($find, $replace, $m_content);
            $subject    = sanitize_text_field($_POST['wau_sub']);
            $body       = wp_kses_stripslashes($mail_body);
            $from_email = sanitize_email($_POST['wau_from']);
            $from_name  = sanitize_text_field($_POST['wau_from_name']);
            $headers[]  = 'From: ' . $from_name . ' <' . $from_email . '>';
            $headers[]  = 'Content-Type: text/html; charset="UTF-8"';
            $wau_status = wp_mail($wau_to[$j], $subject, $body, $headers);
        } // for ends
    }
}
