<?php
if (!defined('ABSPATH'))
    exit;
if (!function_exists('tsweu_post_published_notification')) {
    function tsweu_post_published_notification($ID, $post)
    {
        $post_publish = get_option('post_publish');
        if ($post_publish == '') {
            update_option('post_publish', $ID);
            if ($post->post_status == 'publish') {
                $current_user = wp_get_current_user();
                $wau_status   = "";
                $from_email   = $current_user->user_email;
                $user         = new WP_User($current_user->ID);
                if (!empty($user->roles) && is_array($user->roles)) {
                    foreach ($user->roles as $role)
                        $role;
                }
                $from_name = $current_user->display_name;
                global $wpdb;
                $author      = $post->post_author;
                $Sender_name = get_the_author_meta('display_name', $author);
                $email       = get_the_author_meta('user_email', $author);
                $title       = $post->post_title;
                $post_type   = $post->post_type;
                $permalink   = get_permalink($ID);
                $edit        = get_edit_post_link($ID, '');
                $subject_def = 'Published: ' . $title;
                $subject     = get_option('weu_new_post_publish', $subject_def);
                global $wpdb;
                $table_name = $wpdb->prefix . 'weu_user_notification';
                $myrows     = $wpdb->get_results($wpdb->prepare("SELECT template_value, email_for,email_by,email_value FROM $table_name WHERE email_for =%s", 'New Post Publish'));
                $tomail         = array();
                if ($myrows[0]->email_by == 'role') {
                    $role_res = unserialize($myrows[0]->email_value);
                    for ($k = 0; $k < count($role_res); $k++) {
                        $args          = array(
                            'role' => $role_res[$k]
                        );
                        $wau_grp_users = get_users($args);
                        for ($m = 0; $m < count($wau_grp_users); $m++) {
                            array_push($tomail, $wau_grp_users[$m]->data->user_email);
                        }
                    }
                } else {
                    $tomail = unserialize($myrows[0]->email_value);
                }
                array_push($wau_too, $wau_grp_users->display_name);
                $user_val            = get_user_meta($ID);
                $userdata            = get_user_by('id', $ID);
                $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                $unsubscribe_link    = add_query_arg(array(
                    'id' => $ID,
                    'email' => $email
                ), trim($unsbscribe_url, " "));
                $subscribe_link      = add_query_arg(array(
                    'id' => $ID,
                    'email' => $email
                ), trim($subscribe_url, " "));
                $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
                $user_val            = get_user_meta($ID);
                $replace             = array(
                    get_option('blogname'),
                    $from_name,
                    $email,
                    $subscribe_link_ht,
                    $unsubscribe_link_ht,
                    $user_val['nickname'][0],
                    $user_val['first_name'][0],
                    $user_val['last_name'][0],
                );
                $find                = array(
                    '[[site-title]]',
                    '[[username]]',
                    '[[useremail]]',
                    '[[subscribe-link]]',
                    '[[unsubscribe-link]]',
                    '[[display-name]]',
                    '[[first-name]]',
                    '[[last-name]]',
                );
                $message = str_replace($find, $replace, $myrows[0]->template_value);
                $message .= sprintf('View: %s', $permalink);
                $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                $headers[]  = 'From: ' . $from_name . ' <' . $from_email . ' >';
                foreach ($tomail as $email_to) {
                    $image_id              = rand();
                    if (get_option('weu_track_mail') == 'yes') {
                        $trackImage            = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." style="display: none;"/>';
                        $message               = $message . "" . $trackImage;
                    }
                    $wau_status            = wp_mail($email_to, $subject, $message, $headers);
                    $get_sent_type         = "New Post Publish";
                    $get_subject           = $subject;
                    $get_body              = $message;
                    $get_from_name         = $from_name;
                    $get_from_email        = $from_email;
                    $get_user_role         = $role;
                    $get_status            = $wau_status;
                    $get_current_date      = current_time('mysql');
                    $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                    tsweu_setup_activation_data();
                    $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`,`to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $email_to, $image_id));
                }
            }
        } else {
            update_option('post_publish', "");
        }
    }
}
if (!function_exists('tsweu_show_message_function')) {
    function tsweu_show_message_function($comment_id, $user_id)
    {
        $wau_status   = "";
        $current_user = wp_get_current_user();
        $from_email   = $current_user->user_email;
        $user         = new WP_User($current_user->ID);
        if (!empty($user->roles) && is_array($user->roles)) {
            foreach ($user->roles as $role)
                $role;
        }
        $from_name = $current_user->display_name;
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_user_notification';
        $myrows     = $wpdb->get_results($wpdb->prepare("SELECT template_value, email_for,email_by,email_value FROM $table_name WHERE email_for = %s", 'New Comment Post'));

        $to         = array();
        if ($myrows[0]->email_by == 'role') {
            $role_res = unserialize($myrows[0]->email_value);
            for ($k = 0; $k < count($role_res); $k++) {
                $args          = array(
                    'role' => $role_res[$k]
                );
                $wau_grp_users = get_users($args);
                for ($m = 0; $m < count($wau_grp_users); $m++) {
                    array_push($to, $wau_grp_users[$m]->data->user_email);
                }
            }
        } else {
            $to = unserialize($myrows[0]->email_value);
        }

        $user_val            = get_user_meta($user_id);
        $userdata            = get_user_by('id', $user_id);
        $the_comment         = get_comment($comment_id);
        $post                = get_post($the_comment->comment_post_ID);
        $post_author_id      = $post->post_author;
        $post_author_details = get_user_by('id', $post_author_id);
        $to_author           = $post_author_details->data->user_email;
        if (!in_array($to_author, $to)) {
            array_push($to, $to_author);
        }
        $subject_cm_def        = sprintf('comment : "%s" ' . "\n\n", $post->post_title);
        $subject               = get_option('weu_new_comment_post', $subject_cm_def);
        $unsbscribe_url        = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
        $subscribe_url         = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
        $unsubscribe_link      = add_query_arg(array(
            'id' => $user_id,
            'email' => $to_author
        ), trim($unsbscribe_url, " "));
        $subscribe_link        = add_query_arg(array(
            'id' => $user_id,
            'email' => $to_author
        ), trim($subscribe_url, " "));
        $unsubscribe_link_ht   = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
        $subscribe_link_ht     = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
        $replace               = array(
            $post->post_title,
            $the_comment->comment_author,
            $the_comment->comment_author_email,
            $the_comment->comment_content,
            get_option('blogname'),
            $unsubscribe_link_ht,
            $subscribe_link_ht,
        );
        $find                  = array(
            '[[title]]',
            '[[username]]',
            '[[useremail]]',
            '[[Comment]]',
            '[[site-title]]',
            '[[unsubscribe-link]]',
            '[[subscribe-link]]',
        );
        $message               = str_replace($find, $replace, $myrows[0]->template_value);
        foreach ($to as $email_to) {
            $image_id              = rand();
            if (get_option('weu_track_mail') == 'yes') {
                $trackImage            = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." style="display: none;" />';
                $message               = $message . "" . $trackImage;
            }
            $headers[]  = 'From: ' . $from_name . ' <' . $from_email . ' >';
            $headers[]             = 'Content-Type: text/html; charset="UTF-8"';
            $wau_status            = wp_mail($email_to, $subject, $message, $headers);
            $get_sent_type         = "New Comment Publish";
            $get_subject           = $subject;
            $get_body              = $message;
            $get_from_name         = $from_name;
            $get_from_email        = $from_email;
            $get_user_role         = $role;
            $get_status            = $wau_status;
            $get_current_date      = current_time('mysql');
            $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
            tsweu_setup_activation_data();
            $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`,`to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $email_to, $image_id));
        }
    }
}
if (!function_exists('tsweu_registration_send')) {
    function tsweu_registration_send($user_id)
    {
        $current_user = wp_get_current_user();
        $from_email   = $current_user->user_email;
        $user         = new WP_User($current_user->ID);
        if (!empty($user->roles) && is_array($user->roles)) {
            foreach ($user->roles as $role)
                $role;
        }
        $from_name = $current_user->display_name;
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_user_notification';
        $myrows     = $wpdb->get_results($wpdb->prepare("SELECT template_value, email_for,email_by,email_value FROM $table_name WHERE email_for =%s", 'New User Register'));
        if (count($myrows) != 0) {
            $to = array();
            if ($myrows[0]->email_by == 'role') {
                $role_res = unserialize($myrows[0]->email_value);
                for ($k = 0; $k < count($role_res); $k++) {
                    $args          = array(
                        'role' => $role_res[$k]
                    );
                    $wau_grp_users = get_users($args);
                    for ($m = 0; $m < count($wau_grp_users); $m++) {
                        array_push($to, $wau_grp_users[$m]->data->user_email);
                    }
                }
            } else {
                $to = unserialize($myrows[0]->email_value);
            }
            $user_val = get_user_meta($user_id);
            $userdata  = get_user_by('id', $user_id);
            $useremail = $userdata->data->user_email;
            array_push($to, $useremail);
            $subject             = get_option('weu_new_user_register', 'Welcome to Wordpress');
            $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
            $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
            $unsubscribe_link    = add_query_arg(array(
                'id' => $user_id,
                'email' => $useremail
            ), trim($unsbscribe_url, " "));
            $subscribe_link      = add_query_arg(array(
                'id' => $user_id,
                'email' => $useremail
            ), trim($subscribe_url, " "));
            $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
            $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
            $replace             = array(
                $userdata->data->user_login,
                get_option('blogname'),
                $useremail,
                $unsubscribe_link_ht,
                $subscribe_link_ht,
                $user_val['nickname'][0],
                $user_val['first_name'][0],
                $user_val['last_name'][0],
                $userdata->data->user_login,
                $useremail
            );
            $find                = array(
                '[[username]]',
                '[[site-title]]',
                '[[useremail]]',
                '[[unsubscribe-link]]',
                '[[subscribe-link]]',
                '[[user-nickname]]',
                '[[first-name]]',
                '[[last-name]]',
                '[[display-name]]',
                '[[user-email]]'
            );
            $body                = str_replace($find, $replace, $myrows[0]->template_value);
            $headers[]           = 'Content-Type: text/html; charset="UTF-8"';
            $headers[]  = 'From: ' . $from_name . ' <' . $from_email . ' >';
            $to                  = array_filter($to, "tsweu_is_unsubscribe_arr");
            foreach ($to as $email_to) {
                $image_id              = rand();
                if (get_option('weu_track_mail') == 'yes') {
                    $trackImage            = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." style="display: none;"/>';
                    $body                  = $body . "" . $trackImage;
                }
                $wau_status            = wp_mail($email_to, $subject, $body, $headers);
                $get_sent_type         = "New User Register";
                $get_subject           = $subject;
                $get_body              = $body;
                $get_from_name         = $from_name;
                $get_from_email        = $from_email;
                $get_user_role         = $role;
                $get_status            = $wau_status;
                $get_current_date      = current_time('mysql');
                $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                tsweu_setup_activation_data();
                $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`,`to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $email_to, $image_id));
            }
        }
    }
}

if (!function_exists('tsweu_my_password_reset')) {
    function tsweu_my_password_reset($user, $new_pass)
    {
        $current_user = get_option('admin_email');
        $from_email   = get_option('admin_email');
        $from_name = get_option('blogname');
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_user_notification';
        $myrows     = $wpdb->get_results($wpdb->prepare("SELECT template_value, email_for,email_by,email_value FROM $table_name WHERE email_for =%s", 'Password Reset'));
        $Sender_name = $user->data->user_login;
        $to  = $user->data->user_email;
        $role = $user->data->roles;
        $wau_status  = "";
        $subject     = get_option('weu_password_reset', 'Password Changed');
        $replace     = array(
            $Sender_name,
            get_option('blogname'),
            $new_pass
        );
        $find   = array(
            '[[username]]',
            '[[site-title]]',
            '[[password]]'
        );

        $image_id    = rand();
        if (get_option('weu_track_mail') == 'yes') {
            $trackImage            = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." style="display: none;" />';
            $body             =  $trackImage;
        }
        $headers[]  = 'From: ' . $from_name . ' <' . $from_email . ' >';
        $headers[]   = 'Content-Type: text/html; charset="UTF-8"';
        $body        = str_replace($find, $replace, $myrows[0]->template_value);
        if (!tsweu_isUnsubscribe($user->ID, $to)) {
            $wau_status  = wp_mail($to, $subject, $body, $headers);
        }
        $get_sent_type         = "Password Reset";
        $get_subject           = $subject;
        $get_body              = $body;
        $get_from_name         = $from_name;
        $get_from_email        = $from_email;
        $get_user_role         = $role;
        $get_status            = $wau_status;
        $get_current_date      = current_time('mysql');
        $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
        tsweu_setup_activation_data();
        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`,`to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $to, $image_id));
    }
}

if (!function_exists('tsweu_role_changed')) {
    function tsweu_role_changed($user_id, $new_role, $old_role)
    {
        if (!empty($old_role)) {
            if ($old_role[0] != '') {
                $wau_status   = "";
                $current_user = wp_get_current_user();
                $userdata     = get_user_by('id', $user_id);
                $from_email   = $current_user->user_email;
                $user         = new WP_User($current_user->ID);
                if (!empty($user->data->roles) && is_array($user->data->roles)) {
                    foreach ($user->data->roles as $role)
                        $role;
                }
                $from_name = $current_user->display_name;
                global $wpdb;
                $table_name          = $wpdb->prefix . 'weu_user_notification';
                $myrows              = $wpdb->get_results("SELECT template_value, email_for,email_by,email_value FROM $table_name WHERE email_for = 'User Role Changed' ");
                $user                = get_user_by('id', $user_id);
                $user_val            = get_user_meta($user_id);
                $to                  = $user->data->user_email;
                $subject             = get_option('weu_user_role_changed', 'User Role Changed!');
                $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                $unsubscribe_link    = add_query_arg(array(
                    'id' => $user_id,
                    'email' => $to
                ), trim($unsbscribe_url, " "));
                $subscribe_link      = add_query_arg(array(
                    'id' => $user_id,
                    'email' => $to
                ), trim($subscribe_url, " "));
                $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
                $replace             = array(
                    $old_role[0],
                    $new_role,
                    get_option('blogname'),
                    $userdata->data->user_login,
                    $to,
                    $unsubscribe_link_ht,
                    $subscribe_link_ht,
                    $user_val['nickname'][0],
                    $user_val['first_name'][0],
                    $user_val['last_name'][0],
                    $userdata->data->user_login,
                    $to
                );
                $find                = array(
                    '[[old role]]',
                    '[[new role]]',
                    '[[site-title]]',
                    '[[username]]',
                    '[[useremail]]',
                    '[[unsubscribe-link]]',
                    '[[subscribe-link]]',
                    '[[user-nickname]]',
                    '[[first-name]]',
                    '[[last-name]]',
                    '[[display-name]]',
                    '[[user-email]]'
                );
                $message             = str_replace($find, $replace, $myrows[0]->template_value);
                $headers[]           = 'Content-Type: text/html; charset="UTF-8"';
                $headers[]  = 'From: ' . $from_name . ' <' . $from_email . ' >';
                $image_id            = rand();
                if (get_option('weu_track_mail') == 'yes') {
                    $trackImage          = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." style="display: none;" />';
                    $message             = $message . "" . $trackImage;
                }
                if (!tsweu_isUnsubscribe($user_id, $to)) {
                    $wau_status = wp_mail($to, $subject, $message, $headers);
                }
                $get_sent_type         = "User Role Changed";
                $get_subject           = $subject;
                $get_body              = $message;
                $get_from_name         = $from_name;
                $get_from_email        = $from_email;
                $get_user_role         = $role;
                $get_status            = $wau_status;
                $get_current_date      = current_time('mysql');
                $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                tsweu_setup_activation_data();
                $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`,`to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $to, $image_id));
            }
        }
    }
}

$weu_arconf_sett   = array();
$weu_arconf_sett   = get_option('weu_ar_config_options');
$temp_ar_rc        = isset($weu_arconf_sett['weu_arconfig_role_change']) ? $weu_arconf_sett['weu_arconfig_role_change'] : '';
$temp_ar_ppub      = isset($weu_arconf_sett['weu_arconfig_post_pub']) ? $weu_arconf_sett['weu_arconfig_post_pub'] : '';
$temp_ar_cpub      = isset($weu_arconf_sett['weu_arconfig_comment_pub']) ? $weu_arconf_sett['weu_arconfig_comment_pub'] : '';
$temp_ar_pas_reset = isset($weu_arconf_sett['weu_arconfig_pass_reset']) ? $weu_arconf_sett['weu_arconfig_pass_reset'] : '';
$temp_ar_ureg      = isset($weu_arconf_sett['weu_arconfig_user_reg']) ? $weu_arconf_sett['weu_arconfig_user_reg'] : '';
if ($temp_ar_rc == '' || $temp_ar_rc == 'on') {
    add_action('set_user_role', 'tsweu_role_changed', 10, 3);
}
if ($temp_ar_cpub == '' || $temp_ar_cpub == 'on') {
    add_action('comment_post', 'tsweu_show_message_function', 10, 2);
}
if ($temp_ar_pas_reset == '' || $temp_ar_pas_reset == 'on') {
    add_action('password_reset', 'tsweu_my_password_reset', 10, 2);
}
if ($temp_ar_ppub == '' || $temp_ar_ppub == 'on') {
    add_action('publish_post', 'tsweu_post_published_notification', 10, 2);
}
if ($temp_ar_ureg == '' || $temp_ar_ureg == 'on') {
    add_action('user_register', 'tsweu_registration_send', 10, 1);
}
add_action('admin_menu', 'tsweu_register_menu');
