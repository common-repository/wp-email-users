<?php
if (!defined('ABSPATH'))
    exit;
add_action('wp_ajax_weu_send_mail_selected_users', 'tsweu_send_mail_function1');
if (!function_exists('tsweu_send_mail_function1')) {
    function tsweu_send_mail_function1()
    {
        global $wpdb;
        $wau_status      = 2;
        $unsubscribe_flg = 0;
        $weu_tempOptions = get_option('weu_smtp_data_options');
        $wau_too         = array();
        $table_name      = $wpdb->prefix . 'weu_smtp_conf';
        $wau_to[]        = sanitize_text_field($_POST['wau_to']);
        $temp_id         = sanitize_text_field($_POST['temp_id']);
        $wau_to          = array_filter($wau_to, "weu_is_unsubscribe_array");
        $temp_key        = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        $chk_val         = sanitize_text_field($_POST['save_temp']);
        $subject         = sanitize_text_field($_POST['subject']);
        $table_name      = $wpdb->prefix . 'email_user';
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name(
                id int(11) NOT NULL AUTO_INCREMENT,
                template_key varchar(20) NOT NULL,
                template_value longtext NOT NULL,
                status varchar(20) NOT NULL,
                UNIQUE KEY id(id));";
            $rs  = $wpdb->query($sql);
        }
        if ($chk_val == 2) {
            if ($_POST['wau_mailcontent'] != "") {
                $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)", $temp_key, $_POST['wau_mailcontent'], 'template', $subject));
            } else {
                $status = 1;
                echo $status;
            }
        } else if ($_POST['wau_mailcontent'] != "") {
            $wpdb->query($wpdb->prepare("update `" . $table_name . "` set `temp_subject` = %s where id = %s", $subject, $temp_id));
        }
        if ($weu_tempOptions['smtp_status'] == 'no') {
            for ($j = 0; $j < count($wau_to); $j++) {
                $curr_email_data = get_userdata('email', $wau_to[$j]);
                $user_id         = $curr_email_data->ID;
                $user_info       = get_userdata($user_id);
                $user_val        = get_user_meta($user_id);
                $list            = 'Test';
                $weu_arconf_buff = get_option('weu_ar_config_options');
                $mail_to         = sanitize_email($wau_to[$j]);
                array_push($wau_too, $user_info->display_name);
                $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                $unsubscribe_link    = add_query_arg(array(
                    'id' => $user_id,
                    'email' => $mail_to,
                    'list' => $list
                ), trim($unsbscribe_url, " "));
                $subscribe_link      = add_query_arg(array(
                    'id' => $user_id,
                    'email' => $mail_to,
                    'list' => $list
                ), trim($subscribe_url, " "));
                $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
                $avatar = get_avatar($wau_to[$j], 32);
                $replace             = array(
                    $user_val['nickname'][0],
                    $user_val['first_name'][0],
                    $user_val['last_name'][0],
                    get_option('blogname'),
                    $wau_too[$j],
                    $mail_to,
                    $unsubscribe_link_ht,
                    $subscribe_link_ht,
                    $avatar
                );
                $find                = array(
                    '[[user-nickname]]',
                    '[[first-name]]',
                    '[[last-name]]',
                    '[[site-title]]',
                    '[[display-name]]',
                    '[[user-email]]',
                    '[[unsubscribe-link]]',
                    '[[subscribe-link]]',
                    '[[user-avatar]]'
                );
                $mail_body           = str_replace($find, $replace, $_POST['wau_mailcontent']);
                $subject             = sanitize_text_field($_POST['subject']);
                $body                = wp_kses_stripslashes($mail_body);
                $from_email          = sanitize_email($_POST['from_email']);
                $from_name           = sanitize_text_field($_POST['from_name']);
                $headers[]          = 'From: ' . $from_name . ' <' . $from_email . '>';
                $headers[]          = 'Content-Type: text/html; charset="UTF-8"';
                $wau_status          = '';
                $unsubscribe_flg     = 0;
                $image_id            = rand();
                $trackImage          = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." />';
                $body                = $body . "" . $trackImage;
                if (!tsweu_isUnsubscribe_user($user_id, $wau_to[$j])) {
                    $wau_status = wp_mail($wau_to[$j], $subject, $body, $headers);
                } else {
                    $unsubscribe_flg = 1;
                }
                $get_sent_type         = "Normal";
                $get_subject           = $subject;
                $get_body              = $body;
                $get_from_name         = $from_name;
                $get_from_email        = $from_email;
                $get_user_role         = sanitize_text_field($_POST['user_role']);
                $get_status            = $wau_status;
                $get_current_date      = current_time('mysql');
                $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                tsweu_setup_activation_data();
                $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $wau_to[$j], $image_id));
            }
        } else if ($weu_tempOptions['smtp_status'] == 'yes') {
            $num_to_reach = count($wau_to);
            $table_name   = $wpdb->prefix . 'weu_smtp_conf';
            $myrows       = $wpdb->get_results("SELECT smtp_last_mail_time FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` ORDER BY `smtp_last_mail_time` DESC limit 1");
            $array1       = array();
            $table_name   = $wpdb->prefix . 'weu_smtp_conf';
            $myrows       = $wpdb->get_results("SELECT * FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` AND smtp_priority != 0 ORDER BY `smtp_priority` ASC");
            foreach ($myrows as $user) {
                $array1[$user->conf_id] = $user->smtp_mail_limit - $user->smtp_mails_used;
            }
            $i = 0;
            foreach ($array1 as $key => $value) {
                $myrows1 = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `conf_id` = %s", $key));
                foreach ($myrows1 as $user1) {
                    if (version_compare(get_bloginfo('version'), '5.5-alpha', '<')) {
                        $mail = new PHPMailer(true);
                    } else {
                        $mail = new PHPMailer\PHPMailer\PHPMailer();
                    }
                    $mail->IsSMTP();
                    $mail->Host       = $user1->smtp_host;
                    $mail->SMTPAuth   = true;
                    $mail->Port       = $user1->smtp_port;
                    $mail->Username   = $user1->smtp_username;
                    $mail->Password   = $user1->smtp_password;
                    $mail->SMTPSecure = $user1->smtp_smtpsecure;
                    $x                = 0;
                    while ($value > 0 && $num_to_reach > 0) {
                        $conf_id    = $user1->conf_id;
                        $from_email = $user1->smtp_from_email;
                        $from_name  = $user1->smtp_from_name;
                        $mails_used = $user1->smtp_mails_used;
                        ++$x;
                        $mails_used      = $mails_used + $x;
                        $curr_email_data = get_user_by('email', $wau_to[$i]);
                        $user_id         = $curr_email_data->ID;
                        $user_info       = get_userdata($user_id);
                        $user_val        = get_user_meta($user_id);
                        $subject         = sanitize_text_field($_POST['subject']);
                        $list            = 'Test';
                        $weu_arconf_buff = get_option('weu_ar_config_options');
                        array_push($wau_too, $user_info->display_name);
                        $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                        $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                        $unsubscribe_link    = add_query_arg(array(
                            'id' => $user_id,
                            'email' => $wau_to[$i],
                            'list' => $list
                        ), trim($unsbscribe_url, " "));
                        $subscribe_link      = add_query_arg(array(
                            'id' => $user_id,
                            'email' => $wau_to[$i],
                            'list' => $list
                        ), trim($subscribe_url, " "));
                        $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                        $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
                        $replace             = array(
                            $user_val['nickname'][0],
                            $user_val['first_name'][0],
                            $user_val['last_name'][0],
                            get_option('blogname'),
                            $wau_too[$i],
                            $wau_to[$i],
                            $unsubscribe_link_ht,
                            $subscribe_link_ht
                        );
                        $find                = array(
                            '[[user-nickname]]',
                            '[[first-name]]',
                            '[[last-name]]',
                            '[[site-title]]',
                            '[[display-name]]',
                            '[[user-email]]',
                            '[[unsubscribe-link]]',
                            '[[subscribe-link]]'
                        );
                        $mail_body           = str_replace($find, $replace, $_POST['wau_mailcontent']);
                        $subject             = sanitize_text_field($_POST['subject']);
                        $body                = wp_kses_stripslashes($mail_body);
                        $headers[]          = 'From: ' . $from_name . ' <' . $from_email . '>';
                        $headers[]          = 'Content-Type: text/html; charset="UTF-8"';
                        $wau_status          = '';
                        $unsubscribe_flg     = 0;
                        $image_id            = rand();
                        $trackImage          = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." />';
                        $body                = $body . "" . $trackImage;
                        if (!tsweu_isUnsubscribe_user($user_id, $wau_to[$i])) {
                            $wau_status       = wp_mail($wau_to[$i], $subject, $body, $headers);
                            $table_name       = $wpdb->prefix . 'weu_smtp_conf';
                            $get_current_date = date('Y-m-d');
                            $execut           = $wpdb->query($wpdb->prepare("UPDATE $table_name SET smtp_mails_used = %s, smtp_last_mail_time = %s WHERE conf_id = %s", $mails_used, $get_current_date, $conf_id));
                        } else {
                            $unsubscribe_flg = 1;
                        }
                        $get_sent_type         = "Normal";
                        $get_subject           = $subject;
                        $get_body              = $body;
                        $get_from_name         = $from_name;
                        $get_from_email        = $from_email;
                        $get_user_role         = sanitize_text_field($_POST['user_role']);
                        $get_status            = $wau_status;
                        $get_current_date      = current_time('mysql');
                        $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                        tsweu_setup_activation_data();
                        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $wau_to[$i], $image_id));
                        $i++;
                        $value--;
                        $num_to_reach--;
                    }
                }
            }
        }
        if ($wau_status == 1 || $unsubscribe_flg == 1) {
            $status = 2;
            echo $status;
        } elseif ($wau_status == 0) {
            $status = 3;
            echo $status;
        }
        wp_die();
    }
}

if (!function_exists('tsweu_is_unsubscribe_array')) {
    function tsweu_is_unsubscribe_array($emails_arr)
    {
        global $wpdb;
        $sent_to_emails = array();
        $table_name     = $wpdb->prefix . 'weu_unsubscriber';
        $unsubscribers  = $wpdb->get_results("SELECT `email` FROM $table_name");
        for ($i = 0; $i < count($emails_arr); $i++) {
            if (!in_array($emails_arr[$i], $unsubscribers)) {
                array_push($sent_to_emails, $emails_arr[$i]);
            }
        }
        return $sent_to_emails;
    }
}

if (!function_exists('tsweu_isUnsubscribe_user')) {
    function tsweu_isUnsubscribe_user($userId, $userEmail)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_unsubscriber';
        $myrows     = $wpdb->get_row("SELECT * FROM $table_name WHERE uid = '$userId' AND email = '$userEmail'");
        if (count($myrows) != 0) {
            return true;
        } else {
            return false;
        }
    }
}

add_action('wp_ajax_weu_send_mail_subject', 'tsweu_mail_subject_function');

if (!function_exists('tsweu_mail_subject_function')) {
    function tsweu_mail_subject_function()
    {
        global $wpdb;
        $template_id     = sanitize_text_field($_POST['template_id']);
        $weu_tempOptions = get_option('weu_sample_template_subject');

        if ($template_id == "-1") {
            echo $weu_tempOptions['sample_template_1'];
        } elseif ($template_id == "0") {
            echo $weu_tempOptions['sample_template_2'];
        } else {
            $table_name = $wpdb->prefix . 'email_user';
            $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT temp_subject FROM $table_name WHERE id =%s", $template_id));
            echo $rows_avail;
        }
        wp_die();
    }
}

add_action('wp_ajax_weu_send_mail_subject_temp_page', 'tsweu_mail_subject_temp_page_function');

if (!function_exists('tsweu_mail_subject_temp_page_function')) {
    function tsweu_mail_subject_temp_page_function()
    {
        global $wpdb;
        $template_id     = sanitize_text_field($_POST['template_id']);
        $weu_tempOptions = get_option('weu_sample_template_subject');
        if ($template_id == "-1") {
            echo $weu_tempOptions['sample_template_1'];
        } elseif ($template_id == "0") {
            echo $weu_tempOptions['sample_template_2'];
        } elseif ($template_id == "A") {
            echo $weu_tempOptions['new_user_register'];
        } elseif ($template_id == "C") {
            echo $weu_tempOptions['new_comment'];
        } elseif ($template_id == "B") {
            echo $weu_tempOptions['new_post'];
        } elseif ($template_id == "D") {
            echo $weu_tempOptions['new_password'];
        } elseif ($template_id == "E") {
            echo $weu_tempOptions['user_role_changed'];
        } else {
            $table_name = $wpdb->prefix . 'email_user';
            $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT temp_subject FROM $table_name WHERE id =%s", $template_id));
            echo $rows_avail;
        }
        wp_die();
    }
}
