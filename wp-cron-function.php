<?php

/**
 * * Setting cron on hourly basis
 */

add_action('WP_mail_event', 'tsweu_do_this_hourly');

if (!function_exists('tsweu_do_this_hourly')) {
    function tsweu_do_this_hourly()
    {
        global $wpdb;
        $cron_set = get_option('cron_job_status');
        if ($cron_set['cron_job'] == 'yes') {
            $all_cron_data = get_option('cron_all_data');
            $user_values   = get_option('cron_mail');
            $cronValue     = get_option('cron_value');
            $count         = count($user_values);
            $i             = 0;
            $j             = get_option('cron_value');
            if ($user_values != NULL) {
                $display_array = $count / $j;
                for ($count = 0; $count < $display_array; $count++) {
                    $slice_array[] = array_slice($user_values, $i, $j);
                    $i += $j;
                    $array[] = array(
                        $slice_array
                    );
                }
                update_option('cron_mail_send', $slice_array);
                delete_option('cron_mail');
            }
            $get_all_users_db = get_option('cron_mail_send');
            $wau_too          = array();
            if (!empty($get_all_users_db)) {
                $sendarray = $get_all_users_db[0];
                foreach ($sendarray as $mail) {
                    $curr_email_data = get_user_by('email', $mail);
                    $user_id         = $curr_email_data->ID;
                    $user_info       = get_userdata($user_id);
                    $user_val        = get_user_meta($user_id);
                    $list            = 'Test';
                    $weu_arconf_buff = get_option('weu_ar_config_options');
                    $mail_to         = sanitize_email($mail);
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
                    $replace             = array(
                        $user_val['nickname'][0],
                        $user_val['first_name'][0],
                        $user_val['last_name'][0],
                        get_option('blogname'),
                        $wau_too,
                        $mail_to,
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
                    $mail_body           = str_replace($find, $replace, $all_cron_data['mail_content']);
                    $subject             = sanitize_text_field($all_cron_data['subject']);
                    $body                = wp_kses_stripslashes($mail_body);
                    $from_email          = sanitize_email($all_cron_data['from_email']);
                    $from_name           = sanitize_text_field($all_cron_data['from_name']);

                    $Sender_name_user = 0;
                    if (empty($from_name)) {
                        $Sender_name_user++;
                    }
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                    foreach ($all_cron_data['bccmail'] as $value) {
                        $headers[] = 'Bcc:' . $value;
                    }
                    $image_id   = rand();
                    if (get_option('weu_track_mail') == 'yes') {
                        $trackImage = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="5" height="5" alt="." style="display: none;" style="display: none;"/>';
                        $body       = $body . "" . $trackImage;
                    }
                    if ($Sender_name_user == 0) {
                        $sent = wp_mail($mail, $subject, $body, $headers);
                    }
                    $get_sent_type         = "Cron schedule";
                    $get_subject           = $subject;
                    $get_body              = $body;
                    $get_user_role         = 'user';
                    $get_from_name         = $from_name;
                    $get_from_email        = $from_email;
                    $get_status            = $sent;
                    $get_current_date      = current_time('mysql');
                    $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                    $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $mail, $image_id));
                }
            }
            array_shift($get_all_users_db);
            update_option('cron_mail_send', $get_all_users_db);
        }
    }
}
