<?php
if (!defined('ABSPATH'))
    exit;
if (!function_exists('tsweu_admin_page')) {
    function tsweu_admin_page()
    {
        global $current_user, $wpdb, $wp_roles;
        $user_roles = $current_user->roles;
        $roles      = $wp_roles->get_names();
        $bcc_data   = array();

        $cron_job_data = get_option('cron_job_status');
        $get_roles     = get_option('enable_plugin_for_other_roles');
        $user          = wp_get_current_user();
        $get_user_role = $user->roles;

        foreach ($get_user_role as $display_get_user_role) {
            if (in_array($display_get_user_role, $get_roles) || $display_get_user_role == 'administrator') {
                if (current_user_can($display_get_user_role)) {
                    $weu_tempOptions = get_option('weu_smtp_data_options');
                    if (!empty($_POST['ea_user_name_bcc'])) {
                        $ea_user = (isset($_POST['ea_user_name_bcc'])) ? sanitize_text_field($_POST['ea_user_name_bcc']) : "";
                        for ($j = 0; $j < $ea_user; $j++) {
                            $user_new = $ea_user[$j];
                            array_push($bcc_data, sanitize_text_field($_POST[$user_new]));
                        }
                    }
                    if (!empty($_POST['csv_file_name_bcc'])) {
                        $csv_file_name = (isset($_POST['csv_file_name_bcc'])) ? sanitize_text_field($_POST['csv_file_name_bcc']) : "";
                        for ($l = 0; $l < count($csv_file_name); $l++) {
                            $table_name_bcc = $wpdb->prefix . 'weu_subscribers';
                            $csv_fname_bcc  = $csv_file_name[$l];
                            $myrows_bcc     = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name_bcc WHERE list =%s", $csv_fname_bcc));
                            foreach ($myrows_bcc as $line) {
                                $email_BCC = sanitize_email($line->email);
                                array_push($bcc_data, $email_BCC);
                            }
                        }
                    }
                    if (!empty($_POST['user_role_bcc'])) {
                        $userrole_bcc[] = (isset($_POST['user_role_bcc'])) ? sanitize_text_field($_POST['user_role_bcc']) : "";
                        for ($k = 0; $k < count($userrole_bcc); $k++) {
                            $args          = array(
                                'role' => $userrole_bcc[$k]
                            );
                            $str_brk       = explode(' ', $args['role']);
                            $str_join      = join('_', $str_brk);
                            $str_join      = strtolower($str_join);
                            $args          = array(
                                'role' => $str_join
                            );
                            $wau_grp_users = get_users($args);
                            for ($m = 0; $m < count($wau_grp_users); $m++) {
                                $emails_bcc = sanitize_email($wau_grp_users[$m]->data->user_email);
                                array_push($bcc_data, $emails_bcc);
                            }
                        }
                    }
                    if (!empty($_POST['ea_user_group_bcc'])) {
                        $eaug[] = (isset($_POST['ea_user_group_bcc'])) ? sanitize_text_field($_POST['ea_user_group_bcc']) : "";
                        for ($a = 0; $a < count($eaug); $a++) {
                            $group_id_bcc   = $eaug[$a];
                            $table_name_bcc = $wpdb->prefix . 'groups_user_group';
                            $myrow_new      = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM $table_name_bcc WHERE group_id =%s", $group_id_bcc));
                            foreach ($myrow_new as $row) {
                                foreach ($row as $user_id) {
                                    $myrow_new1[] = get_user_by("ID", $row->user_id);
                                }
                            }
                            foreach ($myrow_new1 as $line) {
                                $email_bcc = sanitize_email($line->user_email);
                                array_push($bcc_data, $email_bcc);
                            }
                        }
                    }
                    $bcc_data = array_filter($bcc_data, "tsweu_is_unsubscribe_arr");
                    if ($weu_tempOptions['smtp_status'] == 'no') {
                        $wau_status      = 2;
                        $unsubscribe_flg = 0;
                        $wau_too         = array();
                        $count           = 1;
                        if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'csv') {
                            if (!isset($_POST['wp_email_users_nonce']) || !wp_verify_nonce($_POST['wp_email_users_nonce'], 'wp_send_mail')) {
                                print 'Sorry, Please refresh page and try again.';
                                exit;
                            } else {
                                $counter    = 0;
                                $temp_key   = isset($_POST['wau_temp_name']) ? sanitize_text_field($_POST['wau_temp_name']) : '';
                                $chk_val    = sanitize_text_field($_POST['save_temp']);
                                $table_name = $wpdb->prefix . 'email_user';
                                if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                                    $sql = "CREATE TABLE $table_name(
                                        id int(11) NOT NULL AUTO_INCREMENT,
                                        template_key varchar(20) NOT NULL,
                                        template_value longtext NOT NULL,
                                        status varchar(20) NOT NULL,
                                        temp_subject varchar(20) NOT NULL,
                                        UNIQUE KEY id(id));";
                                    $rs  = $wpdb->query($sql);
                                }
                                if ($chk_val == 2) {
                                    if ($_POST['wau_mailcontent'] != "") {
                                        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)", $temp_key, $_POST['wau_mailcontent'], 'template', sanitize_text_field($_POST['wau_sub'])));
                                    } else {
?>
                                        <div id="message" class="updated notice is-dismissible error">
                                            <p> Sorry,your message has not sent as Message was empty.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                                        </div>
                                    <?php
                                        header("Refresh: 3;");
                                        return false;
                                    }
                                }
                                $cfn[] = (isset($_POST['csv_file_name'])) ? sanitize_text_field($_POST['csv_file_name']) : "";
                                for ($j = 0; $j < count($cfn); $j++) {
                                    $from_email = sanitize_email($_POST['wau_from_email']);
                                    $from_name  = sanitize_text_field($_POST['wau_from_name']);
                                    $csv_to     = array();
                                    $headers[]  = 'From: ' . $from_name . ' <' . $from_email . ' >';
                                    $headers[]  = 'Content-Type: text/html; charset="UTF-8"';
                                    $table_name = $wpdb->prefix . 'weu_subscribers';
                                    $csv_fname  = $cfn[$j];
                                    $myrows     = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE list =%s", $csv_fname));
                                    foreach ($myrows as $line) {
                                        $email = sanitize_email($line->email);
                                        array_push($csv_to, $email);
                                        $user_id     = intval($line->id);
                                        $list        = $line->list;
                                        $Sender_name = sanitize_text_field($line->name);
                                        $mail_body   = $_POST['wau_mailcontent'];
                                        $subject     = sanitize_text_field($_POST['wau_sub']);
                                        $body        = wp_kses_stripslashes($mail_body);
                                        $weu_arconf_buff     = get_option('weu_ar_config_options');
                                        $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                                        $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                                        $unsubscribe_link    = add_query_arg(array(
                                            'id' => $user_id,
                                            'email' => $email,
                                            'list' => $list
                                        ), trim($unsbscribe_url, " "));
                                        $subscribe_link      = add_query_arg(array(
                                            'id' => $user_id,
                                            'email' => $email,
                                            'list' => $list
                                        ), trim($subscribe_url, " "));
                                        $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                                        $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
                                        $replace             = array(
                                            $Sender_name,
                                            $email,
                                            get_option('blogname'),
                                            $unsubscribe_link_ht,
                                            $subscribe_link_ht
                                        );
                                        $find                = array(
                                            '[[first-name]]',
                                            '[[user-email]]',
                                            '[[site-title]]',
                                            '[[unsubscribe-link]]',
                                            '[[subscribe-link]]'
                                        );
                                        $mail_body           = str_replace($find, $replace, $_POST['wau_mailcontent']);
                                        $subject             = sanitize_text_field($_POST['wau_sub']);
                                        $body                = wp_kses_stripslashes($mail_body);
                                        $from_email          = sanitize_email($_POST['wau_from_email']);
                                        $from_name           = sanitize_text_field($_POST['wau_from_name']);
                                        $Sender_name_user = 0;
                                        if (empty($from_name)) {
                                            $Sender_name_user++;
                                        }
                                        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                                        $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                                        foreach ($bcc_data as $value) {
                                            $headers[] = 'Bcc:' . $value;
                                        }
                                        $wau_status      = '';
                                        $unsubscribe_flg = 0;
                                        $image_id        = rand();
                                        if (get_option('weu_track_mail') == 'yes') {
                                            $trackImage      = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="0" height="0" alt="."style="display: none;" />';
                                            $body            = $body . "" . $trackImage;
                                        }
                                        if (!tsweu_isUnsubscribe($user_id, $email) && $Sender_name_user == 0) {
                                            add_action("phpmailer_init", "EmbedImageInMail");
                                            $wau_status = wp_mail($email, $subject, $body, $headers);
                                            remove_action("phpmailer_init", "EmbedImageInMail");
                                        } else {
                                            $unsubscribe_flg = 0;
                                        }
                                        $get_sent_type         = "List";
                                        $get_subject           = $subject;
                                        $get_body              = $body;
                                        $get_from_name         = $from_name;
                                        $get_from_email        = $from_email;
                                        $get_user_role         = sanitize_text_field($_POST['rbtn']);
                                        $get_status            = $wau_status;
                                        $get_current_date      = current_time('mysql');
                                        $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                                        tsweu_setup_activation_data();
                                        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $email, $image_id));
                                    }
                                }
                            }
                        }
                        if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'group') {
                            if (!isset($_POST['wp_email_users_nonce']) || !wp_verify_nonce($_POST['wp_email_users_nonce'], 'wp_send_mail')) {
                                print 'Sorry, Please refresh page and try again.';
                                exit;
                            } else {
                                $temp_key   = isset($_POST['wau_temp_name']) ? sanitize_text_field($_POST['wau_temp_name']) : '';
                                $chk_val    = sanitize_text_field($_POST['save_temp']);
                                $table_name = $wpdb->prefix . 'email_user';
                                if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                                    $sql = "CREATE TABLE $table_name(
                                            id int(11) NOT NULL AUTO_INCREMENT,
                                            template_key varchar(20) NOT NULL,
                                            template_value longtext NOT NULL,
                                            status varchar(20) NOT NULL,
                                            temp_subject varchar(20) NOT NULL,
                                            UNIQUE KEY id(id));";
                                    $rs  = $wpdb->query($sql);
                                }
                                if ($chk_val == 2) {
                                    if ($_POST['wau_mailcontent'] != "") {
                                        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)", $temp_key, $_POST['wau_mailcontent'], 'template', sanitize_text_field($_POST['wau_sub'])));
                                    } else {
                                    ?>
                                        <div id="message" class="updated notice is-dismissible error">
                                            <p> Sorry,your message has not sent as Message was empty.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                                        </div>
                                    <?php
                                        header("Refresh: 3;");
                                        return false;
                                    }
                                }
                                $Sender_name_user = 0;
                                $esug[] = (isset($_POST['ea_user_group'])) ? sanitize_text_field($_POST['ea_user_group']) : "";
                                for ($j = 0; $j < count($esug); $j++) {
                                    $group_id   = $esug[$j];
                                    $table_name = $wpdb->prefix . 'groups_user_group';
                                    $myrow_new  = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM $table_name WHERE group_id =%s", $group_id));

                                    foreach ($myrow_new as $row) {
                                        foreach ($row as $user_id) {
                                            $myrow_new1[] = get_user_by("ID", $row->user_id);
                                        }
                                    }
                                    foreach ($myrow_new1 as $line) {
                                        $email       = $line->user_email;
                                        $email       = sanitize_email($email);
                                        $user_id     = intval($line->ID);
                                        $userdata    = get_user_by('id', $user_id);
                                        $user_val    = get_user_meta($user_id);
                                        $Sender_name = sanitize_text_field($line->user_login);
                                        $mail_body   = $_POST['wau_mailcontent'];
                                        $subject     = sanitize_text_field($_POST['wau_sub']);
                                        $body        = wp_kses_stripslashes($mail_body);
                                        $weu_arconf_buff     = get_option('weu_ar_config_options');
                                        $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                                        $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                                        $unsubscribe_link    = add_query_arg(array(
                                            'id' => $user_id,
                                            'email' => $email
                                        ), trim($unsbscribe_url, " "));
                                        $subscribe_link      = add_query_arg(array(
                                            'id' => $user_id,
                                            'email' => $email
                                        ), trim($subscribe_url, " "));
                                        $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                                        $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";

                                        $replace    = array(
                                            $user_val['nickname'][0],
                                            $user_val['first_name'][0],
                                            $user_val['last_name'][0],
                                            get_option('blogname'),
                                            $Sender_name,
                                            $email,
                                            get_option('blogname'),
                                            $unsubscribe_link_ht,
                                            $subscribe_link_ht,
                                            $userdata->data->user_login
                                        );
                                        $find       = array(
                                            '[[user-nickname]]',
                                            '[[first-name]]',
                                            '[[last-name]]',
                                            '[[site-title]]',
                                            '[[first-name]]',
                                            '[[user-email]]',
                                            '[[site-title]]',
                                            '[[unsubscribe-link]]',
                                            '[[subscribe-link]]',
                                            '[[display-name]]'
                                        );
                                        $mail_body  = str_replace($find, $replace, $_POST['wau_mailcontent']);
                                        $subject    = sanitize_text_field($_POST['wau_sub']);
                                        $body       = wp_kses_stripslashes($mail_body);
                                        $from_email = sanitize_email($_POST['wau_from_email']);
                                        $from_name  = sanitize_text_field($_POST['wau_from_name']);
                                        if (empty($from_name)) {
                                            $Sender_name_user++;
                                        }
                                        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                                        $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                                        foreach ($bcc_data as $value) {
                                            $headers[] = 'Bcc:' . $value;
                                        }
                                        $wau_status      = '';
                                        $unsubscribe_flg = 0;
                                        $image_id        = rand();
                                        if (get_option('weu_track_mail') == 'yes') {
                                            $trackImage      = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="."style="display: none;" />';
                                            $body            = $body . "" . $trackImage;
                                        }
                                        if (!tsweu_isUnsubscribe($user_id, $email) && $Sender_name_user == 0) {
                                            add_action("phpmailer_init", "EmbedImageInMail");
                                            $wau_status = wp_mail($email, $subject, $body, $headers);
                                            remove_action("phpmailer_init", "EmbedImageInMail");
                                        } else {
                                            $unsubscribe_flg = 0;
                                        }
                                        $get_sent_type         = 'Normal Group';
                                        $get_subject           = $subject;
                                        $get_body              = $body;
                                        $get_from_name         = $from_name;
                                        $get_from_email        = $from_email;
                                        $get_user_role         = $_POST['rbtn'];
                                        $get_status            = $wau_status;
                                        $get_current_date      = current_time('mysql');
                                        $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                                        tsweu_setup_activation_data();
                                        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $email, $image_id));
                                    }
                                }
                            }
                        }
                        if ($wau_status == 1 || $unsubscribe_flg == 1) {
                            echo '<script>swal({
                              type: "success",
                              title: "Mail has been sent successfully",
                              showConfirmButton: true,
                            });</script>';
                        } elseif ($wau_status == 0) {
                            echo '<script>swal({
                              type: "error",
                              title: "Sorry,your mail has not sent.",
                              showConfirmButton: true,
                            
                            });</script>';
                        }
                    } else if ($weu_tempOptions['smtp_status'] == 'yes') {
                        $wau_status = 2;
                        $table_name = $wpdb->prefix . 'weu_smtp_conf';
                        $myrows     = $wpdb->get_results("SELECT smtp_last_mail_time FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` ORDER BY `smtp_last_mail_time` DESC limit 1");
                        foreach ($myrows as $user) {
                            $date_db     = $user->smtp_last_mail_time;
                            $todays_date = date('Y-m-d');
                        }
                        if ($date_db < $todays_date) {
                            $table_name = $wpdb->prefix . 'weu_smtp_conf';
                            $execut     = $wpdb->query($wpdb->prepare("UPDATE $table_name SET smtp_mails_used = %s", 0));
                        }
                        $unsubscribe_flg = 0;
                        $wau_too         = array();
                        if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'csv') {
                            if (!isset($_POST['wp_email_users_nonce']) || !wp_verify_nonce($_POST['wp_email_users_nonce'], 'wp_send_mail')) {
                                print 'Sorry, Please refresh page and try again.';
                                exit;
                            } else {
                                $temp_key   = isset($_POST['wau_temp_name']) ? sanitize_text_field($_POST['wau_temp_name']) : '';
                                $chk_val    = sanitize_text_field($_POST['save_temp']);
                                $table_name = $wpdb->prefix . 'email_user';
                                if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                                    $sql = "CREATE TABLE $table_name(
                                        id int(11) NOT NULL AUTO_INCREMENT,
                                        template_key varchar(20) NOT NULL,
                                        template_value longtext NOT NULL,
                                        status varchar(20) NOT NULL,
                                        temp_subject varchar(20) NOT NULL,
                                        UNIQUE KEY id(id)
                                    );";
                                    $rs  = $wpdb->query($sql);
                                }
                                if ($chk_val == 2) {
                                    if ($_POST['wau_mailcontent'] != "") {
                                        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)", $temp_key, $_POST['wau_mailcontent'], 'template', sanitize_text_field($_POST['wau_sub'])));
                                    } else {
                                    ?>
                                        <div id="message" class="updated notice is-dismissible error">
                                            <p> Sorry,your message has not sent as Message was empty.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                                        </div>
                                    <?php
                                        header("Refresh: 3;");
                                        return false;
                                    }
                                }
                                $csv_to = array();
                                $csfn[] = (isset($_POST['csv_file_name'])) ? sanitize_text_field($_POST['csv_file_name']) : "";
                                for ($j = 0; $j < count($csfn); $j++) {
                                    $table_name = $wpdb->prefix . 'weu_subscribers';
                                    $csv_fname  = $csfn[$j];
                                    $myrows     = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE list =%s", $csv_fname));
                                    foreach ($myrows as $line) {
                                        array_push($csv_to, $line);
                                    }
                                }
                                $num_to_reach   = count($csv_to);
                                $array1         = array();
                                $array_bcc_list = array();
                                $table_name1    = $wpdb->prefix . 'weu_smtp_conf';
                                $table_name     = $wpdb->prefix . 'weu_smtp_conf';
                                $myrows         = $wpdb->get_results("SELECT * FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` AND smtp_priority != 0 ORDER BY `smtp_priority` ASC");
                                $myrows12       = $wpdb->get_results("SELECT * FROM $table_name1 WHERE  `smtp_mails_used` <= `smtp_mail_limit` AND smtp_priority != 0 ORDER BY `smtp_priority` ASC");
                                foreach ($myrows as $user) {
                                    $array1[$user->conf_id] = $user->smtp_mail_limit - $user->smtp_mails_used;
                                }
                                $i     = 0;
                                $incre = 0;
                                foreach ($array1 as $key => $value) {
                                    $myrows1 = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `conf_id` =%s", $key));
                                    foreach ($myrows1 as $user1) {
                                        if (version_compare(get_bloginfo('version'), '5.5-alpha', '<')) {
                                            $mail = new PHPMailer(true);
                                        } else {
                                            $mail = new PHPMailer\PHPMailer\PHPMailer();
                                        }

                                        $mail->IsSMTP();
                                        $mail->Host       = $user1->smtp_host;
                                        $mail->SMTPAuth   = true;
                                        $mail->Host       = $user1->smtp_host;
                                        $mail->Port       = $user1->smtp_port;
                                        $mail->Username   = $user1->smtp_username;
                                        $mail->Password   = $user1->smtp_password;
                                        $mail->SMTPSecure = $user1->smtp_smtpsecure;
                                        $x                = 0;
                                        while ($value > 0 && $num_to_reach > 0) {
                                            $conf_id    = $user1->conf_id;
                                            $from_email = sanitize_email($user1->smtp_from_email);
                                            $from_name  = $user1->smtp_from_name;
                                            $mails_used = $user1->smtp_mails_used;
                                            ++$x;
                                            $mails_used          = $mails_used + $x;
                                            $email               = sanitize_email($csv_to[$i]->email);
                                            $user_id             = $csv_to[$i]->ID;
                                            $list                = $csv_to[$i]->list;
                                            $Sender_name         = $csv_to[$i]->name;
                                            $subject             = sanitize_text_field($_POST['wau_sub']);
                                            $weu_arconf_buff     = get_option('weu_ar_config_options');
                                            $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                                            $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                                            $unsubscribe_link    = add_query_arg(array(
                                                'id' => $user_id,
                                                'email' => $email,
                                                'list' => $list
                                            ), trim($unsbscribe_url, " "));
                                            $subscribe_link      = add_query_arg(array(
                                                'id' => $user_id,
                                                'email' => $email,
                                                'list' => $list
                                            ), trim($subscribe_url, " "));
                                            $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                                            $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
                                            $replace             = array(
                                                $Sender_name,
                                                $email,
                                                get_option('blogname'),
                                                $unsubscribe_link_ht,
                                                $subscribe_link_ht
                                            );
                                            $find                = array(
                                                '[[first-name]]',
                                                '[[user-email]]',
                                                '[[site-title]]',
                                                '[[unsubscribe-link]]',
                                                '[[subscribe-link]]'
                                            );
                                            $mail_body           = str_replace($find, $replace, $_POST['wau_mailcontent']);
                                            $subject             = sanitize_text_field($_POST['wau_sub']);
                                            $body                = wp_kses_stripslashes($mail_body);

                                            $Sender_name_user = 0;
                                            if (empty($from_name)) {
                                                $Sender_name_user++;
                                            }
                                            $headers[]  = 'From: ' . $from_name . ' <' . $from_email . '>';
                                            $headers[]  = 'Content-Type: text/html; charset="UTF-8"';
                                            $wau_status = '';
                                            $unsubscribe_flg = 0;
                                            $image_id        = rand();
                                            if (get_option('weu_track_mail') == 'yes') {
                                                $trackImage      = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." style="display: none;"/>';
                                                $body            = $body . "" . $trackImage;
                                            }
                                            foreach ($bcc_data as $maildata) {
                                                $headers[] = 'Bcc:' . $maildata;
                                            }
                                            if (!tsweu_isUnsubscribe($user_id, $email) && $Sender_name_user == 0) {
                                                add_action("phpmailer_init", "EmbedImageInMail");
                                                $wau_status       = wp_mail($email, $subject, $body, $headers);
                                                remove_action("phpmailer_init", "EmbedImageInMail");
                                                $get_current_date = date('Y-m-d');
                                                $execut           = $wpdb->query($wpdb->prepare("UPDATE $table_name SET smtp_mails_used = %s, smtp_last_mail_time = %s WHERE conf_id = %s", $mails_used, $get_current_date, $conf_id));
                                            } else {
                                                $unsubscribe_flg = 1;
                                            }
                                            $get_sent_type         = "List";
                                            $get_subject           = $subject;
                                            $get_body              = $body;
                                            $get_from_name         = $from_name;
                                            $get_from_email        = $from_email;
                                            $get_user_role         = $_POST['rbtn'];
                                            $get_status            = $wau_status;
                                            $get_current_date      = current_time('mysql');
                                            $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                                            tsweu_setup_activation_data();
                                            $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`, `image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $email, $image_id));
                                            $i++;
                                            $value--;
                                            $num_to_reach--;
                                        }
                                    }
                                }
                                if ($wau_status == 1 || $unsubscribe_flg == 1) {
                                    echo '<script>swal({
                              type: "success",
                              title: "Mail has been sent successfully",
                              showConfirmButton: true,
                       
                            });</script>';
                                } elseif ($wau_status == 0 || $unsubscribe_flg == 0) {
                                    echo '<script>swal({
                              type: "error",
                              title: "Sorry,your mail has not sent.",
                              showConfirmButton: true,
                         
                            });</script>';
                                }
                            }
                        }


                        if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'group') {
                            if (!isset($_POST['wp_email_users_nonce']) || !wp_verify_nonce($_POST['wp_email_users_nonce'], 'wp_send_mail')) {
                                print 'Sorry, Please refresh page and try again.';
                                exit;
                            } else {
                                $temp_key   = isset($_POST['wau_temp_name']) ? sanitize_text_field($_POST['wau_temp_name']) : '';
                                $chk_val    = isset($_POST['save_temp']) ? sanitize_text_field($_POST['save_temp']) : '';
                                $table_name = $wpdb->prefix . 'email_user';
                                if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                                    $sql = "CREATE TABLE $table_name(
                                        id int(11) NOT NULL AUTO_INCREMENT,
                                        template_key varchar(20) NOT NULL,
                                        template_value longtext NOT NULL,
                                        status varchar(20) NOT NULL,
                                        temp_subject varchar(20) NOT NULL,
                                        UNIQUE KEY id(id)
                                        );";
                                    $rs  = $wpdb->query($sql);
                                }
                                if ($chk_val == 2) {
                                    if ($_POST['wau_mailcontent'] != "") {
                                        $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)", $temp_key, $_POST['wau_mailcontent'], 'template', sanitize_text_field($_POST['wau_sub'])));
                                    } else {
                                    ?>
                                        <div id="message" class="updated notice is-dismissible error">
                                            <p> Sorry,your message has not sent as Message was empty.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                                        </div>
                                    <?php
                                        header("Refresh: 3;");
                                        return false;
                                    }
                                }
                                $wau_to = array();
                                $ea_ug = isset($_POST['ea_user_group']) ? sanitize_text_field($_POST['ea_user_group']) : "";
                                for ($a = 0; $a < count($ea_ug); $a++) {
                                    $group_id   = $ea_ug[$a];
                                    $table_name = $wpdb->prefix . 'groups_user_group';
                                    $myrow_new  = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM $table_name WHERE group_id =%s", $group_id));
                                    foreach ($myrow_new as $row) {
                                        foreach ($row as $user_id) {
                                            $myrow_new1[] = get_user_by("ID", $row->user_id);
                                        }
                                    }

                                    foreach ($myrow_new1 as $line) {
                                        $email = sanitize_email($line->user_email);
                                        array_push($wau_to, $email);
                                    }
                                }

                                $num_to_reach = count($wau_to);
                                $array1       = array();
                                $table_name1  = $wpdb->prefix . 'weu_smtp_conf';
                                $table_name   = $wpdb->prefix . 'weu_smtp_conf';
                                $myrows       = $wpdb->get_results("SELECT * FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` AND smtp_priority != 0 ORDER BY `smtp_priority` ASC");
                                $myrows12     = $wpdb->get_results("SELECT * FROM $table_name1 WHERE  `smtp_mails_used` <= `smtp_mail_limit` AND smtp_priority != 0 ORDER BY `smtp_priority` ASC");
                                foreach ($myrows as $user) {
                                    $array1[$user->conf_id] = $user->smtp_mail_limit - $user->smtp_mails_used;
                                }
                                $i     = 0;
                                $incre = 0;

                                foreach ($array1 as $key => $value) {
                                    $myrows1 = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `conf_id` =%s", $key));
                                    foreach ($myrows1 as $user1) {
                                        if (version_compare(get_bloginfo('version'), '5.5-alpha', '<')) {
                                            $mail = new PHPMailer(true);
                                        } else {
                                            $mail = new PHPMailer\PHPMailer\PHPMailer();
                                        }
                                        $mail->IsSMTP();
                                        $mail->Host       = $user1->smtp_host;
                                        $mail->SMTPAuth   = true;
                                        $mail->Host       = $user1->smtp_host;
                                        $mail->Port       = $user1->smtp_port;
                                        $mail->Username   = $user1->smtp_username;
                                        $mail->Password   = $user1->smtp_password;
                                        $mail->SMTPSecure = $user1->smtp_smtpsecure;
                                        $x                = 0;

                                        while ($value > 0 && $num_to_reach > 0) {

                                            $conf_id    = $user1->conf_id;
                                            $from_email = sanitize_email($user1->smtp_from_email);
                                            $from_name  = $user1->smtp_from_name;
                                            $mails_used = $user1->smtp_mails_used;
                                            ++$x;
                                            $mails_used          = $mails_used + $x;
                                            $email               = sanitize_email($wau_to[$i]);
                                            $userdata            = get_user_by("email", $email);
                                            $user_id             = $userdata->ID;
                                            $list                = $userdata->list;
                                            $Sender_name         = $userdata->name;
                                            $subject             = sanitize_text_field($_POST['wau_sub']);
                                            $weu_arconf_buff     = get_option('weu_ar_config_options');
                                            $unsbscribe_url      = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
                                            $subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : '';
                                            $unsubscribe_link    = add_query_arg(array(
                                                'id' => $user_id,
                                                'email' => $email,
                                                'list' => $list
                                            ), trim($unsbscribe_url, " "));
                                            $subscribe_link      = add_query_arg(array(
                                                'id' => $user_id,
                                                'email' => $email,
                                                'list' => $list
                                            ), trim($subscribe_url, " "));
                                            $unsubscribe_link_ht = "<a href=" . esc_url($unsubscribe_link) . ">unsubscribe</a>";
                                            $subscribe_link_ht   = "<a href=" . esc_url($subscribe_link) . ">subscribe</a>";
                                            $replace             = array(
                                                $Sender_name,
                                                $email,
                                                get_option('blogname'),
                                                $unsubscribe_link_ht,
                                                $subscribe_link_ht
                                            );
                                            $find                = array(
                                                '[[first-name]]',
                                                '[[user-email]]',
                                                '[[site-title]]',
                                                '[[unsubscribe-link]]',
                                                '[[subscribe-link]]'
                                            );
                                            $mailc = $_POST['wau_mailcontent'];
                                            $mail_body           = str_replace($find, $replace, $mailc);
                                            $subject             = sanitize_text_field($_POST['wau_sub']);
                                            $body                = wp_kses_stripslashes($mail_body);

                                            $Sender_name_user = 0;
                                            if (empty($from_name)) {
                                                $Sender_name_user++;
                                            }
                                            $headers[]  = 'From: ' . $from_name . ' <' . $from_email . '>';
                                            $headers[]  = 'Content-Type: text/html; charset="UTF-8"';
                                            $wau_status = '';

                                            $unsubscribe_flg = 0;
                                            $image_id        = rand();
                                            if (get_option('weu_track_mail') == 'yes') {
                                                $trackImage      = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="." style="display: none;"/>';
                                                $body            = $body . "" . $trackImage;
                                            }
                                            foreach ($bcc_data as $mailbcc) {
                                                $headers[] = 'Bcc:' . $mailbcc;
                                            }
                                            if (!tsweu_isUnsubscribe($user_id, $email) && $Sender_name_user == 0) {
                                                add_action("phpmailer_init", "EmbedImageInMail");
                                                $wau_status       = wp_mail($email, $subject, $body, $headers);
                                                remove_action("phpmailer_init", "EmbedImageInMail");
                                                $get_current_date = date('Y-m-d');
                                                $execut           = $wpdb->query($wpdb->prepare("UPDATE $table_name SET smtp_mails_used = %s, smtp_last_mail_time = %s WHERE conf_id = %s", $mails_used, $get_current_date, $conf_id));
                                            } else {
                                                $unsubscribe_flg = 1;
                                            }
                                            $get_sent_type         = "group";
                                            $get_subject           = $subject;
                                            $get_body              = $body;
                                            $get_from_name         = $from_name;
                                            $get_from_email        = $from_email;
                                            $get_user_role         = sanitize_text_field($_POST['rbtn']);
                                            $get_status            = $wau_status;
                                            $get_current_date      = current_time('mysql');
                                            $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                                            tsweu_setup_activation_data();
                                            $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`, `image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $email, $image_id));
                                            $i++;
                                            $value--;
                                            $num_to_reach--;
                                        }
                                    }
                                }
                                if ($wau_status == 1 || $unsubscribe_flg == 1) {
                                    echo '<script>swal({
                              type: "success",
                              title: "Mail has been sent successfully",
                              showConfirmButton: true,
                       
                            });</script>';
                                } elseif ($wau_status == 0 || $unsubscribe_flg == 0) {
                                    echo '<script>swal({
                              type: "error",
                              title: "Sorry,your mail has not sent.",
                              showConfirmButton: true,
                         
                            });</script>';
                                }
                            }
                        }
                    }
                    $wau_to = array();
                    if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'user') {
                        if (!isset($_POST['wp_email_users_nonce']) || !wp_verify_nonce($_POST['wp_email_users_nonce'], 'wp_send_mail')) {
                            print 'Sorry, Please refresh page and try again.';
                            exit;
                        } else {
                            $eaun = intval($_POST['ea_user_name']) ? $_POST['ea_user_name'] : "";
                            for ($j = 0; $j < count($eaun); $j++) {
                                $user = $eaun[$j];
                                array_push($wau_to, sanitize_text_field($_POST[$user]));
                                $wau_to = array_filter($wau_to, "tsweu_is_unsubscribe_arr");
                            }
                        }
                    } elseif (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'role') {
                        if (!isset($_POST['wp_email_users_nonce']) || !wp_verify_nonce($_POST['wp_email_users_nonce'], 'wp_send_mail')) {
                            print 'Sorry, Please refresh page and try again.';
                            exit;
                        } else {
                            $ur = isset($_POST['user_role']) ? $_POST['user_role'] : "";
                            for ($k = 0; $k < count($ur); $k++) {
                                $args          = array(
                                    'role' => $ur[$k]
                                );
                                $str_brk       = explode(' ', $args['role']);
                                $str_join      = join('_', $str_brk);
                                $str_join      = strtolower($str_join);
                                $args          = array(
                                    'role' => $str_join
                                );
                                $wau_grp_users = get_users($args);
                                for ($m = 0; $m < count($wau_grp_users); $m++) {
                                    $emails = sanitize_email($wau_grp_users[$m]->data->user_email);
                                    array_push($wau_to, $emails);
                                }
                            }
                            $wau_to = array_filter($wau_to, "tsweu_is_unsubscribe_arr");
                        }
                    }
                    global $wpdb;
                    $wau_status      = 2;
                    $unsubscribe_flg = 0;
                    $wau_too         = array();
                    if (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'user' || isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'role') {
                        if (!isset($_POST['wp_email_users_nonce']) || !wp_verify_nonce($_POST['wp_email_users_nonce'], 'wp_send_mail')) {
                            print 'Sorry, Please refresh page and try again.';
                            exit;
                        } else {
                            $temp_key   = isset($_POST['wau_temp_name']) ? sanitize_text_field($_POST['wau_temp_name']) : '';
                            $chk_val    = sanitize_text_field($_POST['save_temp']);
                            $table_name = $wpdb->prefix . 'email_user';
                            if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                                $sql = "CREATE TABLE $table_name(
                                        id int(11) NOT NULL AUTO_INCREMENT,
                                        template_key varchar(20) NOT NULL,
                                        template_value longtext NOT NULL,
                                        status varchar(20) NOT NULL,
                                        temp_subject varchar(20) NOT NULL,
                                        UNIQUE KEY id(id));";
                                $rs  = $wpdb->query($sql);
                            }
                            if ($chk_val == 2) {
                                if ($_POST['wau_mailcontent'] != "") {
                                    $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)", $temp_key, $_POST['wau_mailcontent'], 'template', sanitize_text_field($_POST['wau_sub'])));
                                } else {
                                    ?>
                                    <div id="message" class="updated notice is-dismissible error">
                                        <p> Sorry,your message has not sent as Message was empty.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                                    </div>
                    <?php
                                    header("Refresh: 3;");
                                    return false;
                                }
                            }
                            if ($weu_tempOptions['smtp_status'] == 'no') {
                                $wau_status = 0;
                                for ($j = 0; $j < count($wau_to); $j++) {
                                    $curr_email_data = get_user_by('email', $wau_to[$j]);
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
                                    $subject             = sanitize_text_field($_POST['wau_sub']);
                                    $body                = wp_kses_stripslashes($mail_body);
                                    $from_email          = sanitize_email($_POST['wau_from_email']);
                                    $from_name           = sanitize_text_field($_POST['wau_from_name']);
                                    $Sender_name_user = 0;
                                    if (empty($from_name)) {
                                        $Sender_name_user++;
                                    }
                                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                                    $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                                    foreach ($bcc_data as $value) {
                                        $headers[] = 'Bcc:' . $value;
                                    }
                                    $wau_status      = 0;
                                    $unsubscribe_flg = 0;
                                    $image_id        = rand();
                                    if (get_option('weu_track_mail') == 'yes') {
                                        $trackImage      = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="5" height="5" alt="." style="display: none;" style="display: none;"/>';
                                        $body            = $body . "" . $trackImage;
                                    }
                                    if ($cron_job_data['cron_job'] == 'yes' && (isset($_POST['rbtn']) && sanitize_text_field($_POST['rbtn']) == 'user')) {
                                        $mail_body_cron = array(
                                            'mail_content' => $_POST['wau_mailcontent'],
                                            'subject' => $subject,
                                            'from_email' => $from_email,
                                            'from_name' => $from_name,
                                            'bccmail' => $bcc_data
                                        );
                                        update_option("cron_all_data", $mail_body_cron);
                                        update_option("cron_mail", $wau_to);
                                        update_option("cron_value", sanitize_text_field($_POST['cron_number']));
                                        tsweu_do_this_hourly();
                                        break;
                                    } else {

                                        if (!tsweu_isUnsubscribe($user_id, $wau_to) && $Sender_name_user == 0) {
                                            add_action("phpmailer_init", "EmbedImageInMail");
                                            // $wau_status = wp_mail($wau_to, $subject, $body, $headers);
                                            remove_action("phpmailer_init", "EmbedImageInMail");
                                        } else {
                                            $unsubscribe_flg = 1;
                                        }
                                    }

                                    $get_sent_type         = "Normal";
                                    $get_subject           = $subject;
                                    $get_body              = $body;
                                    $get_from_name         = $from_name;
                                    $get_from_email        = $from_email;
                                    $get_user_role         = sanitize_text_field($_POST['rbtn']);
                                    $get_status            = $wau_status;
                                    $get_current_date      = current_time('mysql');
                                    $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                                    tsweu_setup_activation_data();
                                    $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`, `to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $wau_to[$j], $image_id));
                                }

                                foreach($wau_to as $wt){
                                    $wau_status = wp_mail($wt, $subject, $body, $headers);
                                } 
                                // $wau_status = wp_mail($wau_to, $subject, $body, $headers);
                            } else if ($weu_tempOptions['smtp_status'] == 'yes') {
                                $num_to_reach = count($wau_to);
                                $headerarray  = array();
                                $ikey         = 0;
                                $list_data_1  = 0;
                                $table_name = $wpdb->prefix . 'weu_smtp_conf';
                                $myrows     = $wpdb->get_results("SELECT smtp_last_mail_time FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` ORDER BY `smtp_last_mail_time` DESC limit 1");
                                $array1     = array();
                                $table_name = $wpdb->prefix . 'weu_smtp_conf';
                                $myrows     = $wpdb->get_results("SELECT * FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` AND smtp_priority != 0 ORDER BY `smtp_priority` ASC");
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
                                            $subject         = sanitize_text_field($_POST['wau_sub']);
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
                                            $subject             = sanitize_text_field($_POST['wau_sub']);
                                            $body                = wp_kses_stripslashes($mail_body);
                                            foreach ($bcc_data as $value) {
                                                $headers[] = 'Bcc:' . $value;
                                            }
                                            $Sender_name_user = 0;
                                            if (!empty($from_name) && count(array($from_name)) == 0) {
                                                $Sender_name_user++;
                                            }
                                            $headers[]       = 'From: ' . $from_name . ' <' . $from_email . '>';
                                            $headers[]       = 'Content-Type: text/html; charset="UTF-8"';
                                            $unsubscribe_flg = 0;
                                            $image_id        = rand();
                                            if (get_option('weu_track_mail') == 'yes') {
                                                $trackImage      = '<img border="0" src=' . plugin_dir_url(__FILE__) . 'trackemail.php/?image_id=' . $image_id . ' width="1" height="1" alt="."style="display: none;" style="display: none;" />';
                                                $body            = $body . "" . $trackImage;
                                            }
                                            if (!tsweu_isUnsubscribe($user_id, $wau_to[$i]) && $Sender_name_user == 0) {
                                                add_action("phpmailer_init", "EmbedImageInMail");
                                                $wau_status       = wp_mail($wau_to[$i], $subject, $body, $headers);
                                                remove_action("phpmailer_init", "EmbedImageInMail");
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
                                            $get_user_role         = sanitize_text_field($_POST['rbtn']);
                                            $get_status            = $wau_status;
                                            $get_current_date      = current_time('mysql');
                                            $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
                                            tsweu_setup_activation_data();
                                            $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_sent_email . "`(`weu_sent_type`, `weu_email_subject`, `weu_email_body`, `weu_from_name`, `weu_from_email`, `weu_to_type`, `weu_status`, `weu_sent_date_time`,`to_email`,`image_id`) VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%d)", $get_sent_type, $get_subject, $get_body, $get_from_name, $get_from_email, $get_user_role, $get_status, $get_current_date, $wau_to[$i], $image_id));
                                            $i++;
                                            $value--;
                                            $num_to_reach--;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (($wau_status == 1 && ($cron_job_data['cron_job'] == 'no') || $wau_status == 1 && ($cron_job_data['cron_job'] == 'yes' && sanitize_text_field($_POST['rbtn']) != 'user'))) {
                        echo '<script> swal({
                              type: "success",
                              title: "Mail has been sent successfully",
                              showConfirmButton: true,
                            
                            });</script>';
                    } elseif (((($wau_status == 0 || $unsubscribe_flg == 1) && $cron_job_data['cron_job'] == 'no') && (($wau_status == 0 || $unsubscribe_flg == 1) && ($cron_job_data['cron_job'] == 'yes' && sanitize_text_field($_POST['rbtn']) != 'user'))) || $wau_status == '') {
                        echo '<script>swal({
                              type: "error",
                              title: "Sorry,your mail has not sent.",
                              showConfirmButton: true,
                            
                            });</script>';
                    }
                    if ($cron_job_data['cron_job'] == 'yes' && $wau_status == 0) {

                        echo '<script> swal({
                              type: "success",
                              title: "Cron has been set successfully",
                              showConfirmButton: true,
                            
                            });</script>';
                    }

                    $wau_users = get_users();
                    ?>
                    <div class='wrap'>
                        <h2> WP Email Users - Send Email </h2>
                        <p>Send email to individual as well as group of users.</p>
                        <form name="myform" id="myForm" class="wau_form" method="POST" action="#" onsubmit="return validation()">
                            <?php wp_nonce_field('wp_send_mail', 'wp_email_users_nonce'); ?>
                            <table id="" class="form-table">
                                <tbody>
                                    <?php
                                    $weu_tempOptions = get_option('weu_smtp_data_options');
                                    if ($weu_tempOptions['smtp_status'] == 'yes') {
                                        $table_name = $wpdb->prefix . 'weu_smtp_conf';
                                        $myrows     = $wpdb->get_results("SELECT * FROM $table_name WHERE `smtp_mails_used` != `smtp_mail_limit` and `smtp_status` != '0' ORDER BY  `smtp_priority` ASC limit 1");
                                        $count      = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE `smtp_mails_used` != `smtp_mail_limit` and `smtp_status` != '0' ORDER BY  `smtp_priority` ASC limit 1");
                                        if ($count == 0) {
                                    ?>
                                            <h4 style='color:#ff0000;text-align: -webkit-center;'>
                                                <blink>Hey buddy, Your daily SMTP Mail limit has been exceeded or you have disabled all SMTP's. Please turn off SMTP setting or try after 12.00am in case limit exceeds.</blink>
                                            </h4>
                                            <?php
                                            $table_name = $wpdb->prefix . 'weu_smtp_conf';
                                            $myrows     = $wpdb->get_results("SELECT smtp_last_mail_time FROM $table_name WHERE  `smtp_mails_used` <= `smtp_mail_limit` ORDER BY `smtp_last_mail_time` DESC limit 1");
                                            foreach ($myrows as $user) {
                                                $date_db     = $user->smtp_last_mail_time;
                                                $todays_date = date('Y-m-d');
                                            }
                                            if ($date_db < $todays_date) {
                                                $table_name = $wpdb->prefix . 'weu_smtp_conf';
                                                $execut     = $wpdb->query($wpdb->prepare("UPDATE $table_name SET smtp_mails_used = %s", 0));
                                                header("Refresh: 1;");
                                            }
                                        }
                                        if ($count != 0) {
                                            foreach ($myrows as $user) {
                                            ?>
                                                <tr>
                                                    <th>From Name <font color="red">*</font>
                                                    </th>
                                                    <td colspan="1">
                                                        <input type="text" name="wau_from_name" value="<?php echo $user->smtp_from_name; ?>" class="wau_boxlen" id="wau_from_name" readonly onblur="myFunction2()">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>From Email <font color="red">*</font>
                                                    </th>
                                                    <td colspan="2">
                                                        <input type="text" name="wau_from_email" value="<?php echo $user->smtp_from_email; ?>" class="wau_boxlen" id="wau_from" onblur="myFunction()" readonly>
                                                    </td>
                                            <?php
                                            }
                                        }
                                    } else {
                                            ?>
                                                <tr>
                                                    <th>From Name <font color="red">*</font>
                                                    </th>
                                                    <td colspan="2"><input type="text" name="wau_from_name" value="<?php echo $current_user->display_name; ?> " class="wau_boxlen" id="wau_from_name" onblur="myFunction2()" required="required" maxlength="64" placeholder="Enter From Name"></td>
                                                </tr>
                                                <tr>
                                                    <th>From Email <font color="red">*</font>
                                                    </th>
                                                    <td colspan="2"><input type="email" name="wau_from_email" value="<?php echo $current_user->user_email; ?>" class="wau_boxlen" required="required" placeholder="Enter From Email" maxlength="254" id="wau_from" onblur="myFunction()"></td>
                                                </tr>
                                                <tr>
                                                    <th>Send Email To <font color=red>*</font> &nbsp;</th>
                                                    <td style="width: 224px"><input type="radio" name="rbtn" id="user_role" onclick="radioFunction()" value="user" checked> User &nbsp;</td>
                                                    <td style="width: 224px"><input type="radio" name="rbtn" id="r_role" onclick="radioFunction()" value="role"> Role </td>
                                                    <td style="width: 224px"><input type="radio" name="rbtn" id="check_list" onclick="radioFunction()" value="csv"> List </td>
                                                <?php
                                            }
                                            if (is_plugin_active('groups/groups.php')) {
                                                ?>
                                                    <td id="grp" style="width: 224px"><input type="radio" name="rbtn" id="g_group" onclick="radioFunction()" value="group"> Groups </td>
                                                <?php
                                            } else {
                                                ?>
                                                    <td id="grp" style="width: 224px"><input type="radio" name="rbtn" id="g_group" onclick="radioFunction()" value="group" disabled> Groups </td>
                                                <?php } ?>
                                                </tr>
                                                <tr class="wau_user_toggle">
                                                    <th></th>
                                                    <td colspan="3">
                                                        <table id="Mail_user_table" class="display alluser_datatable" cellspacing="0" width="100%">
                                                            <thead>
                                                                <tr style="text-align:left">
                                                                    <th style="text-align:center"><input name="select_all" value="1" id="example-select-all" class="select-all" type="checkbox"></th>
                                                                    <th>Display Name</th>
                                                                    <th>Email</th>
                                                                    <?php
                                                                    $weu_arconf_buff = array();
                                                                    $weu_arconf_buff = get_option('weu_ar_config_options');
                                                                    if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                                    ?>
                                                                        <th>BP Group</th>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                foreach ($wau_users as $user) {
                                                                ?>
                                                                    <tr style="text-align:left">
                                                                        <td style="text-align:center">
                                                                            <input type="checkbox" name="ea_user_name[]" value="<?php echo $user->ID; ?>" class="checkbox chk_user">
                                                                        </td>
                                                                        <td><span id="getDetail"><?php echo $user->display_name; ?></span></td>
                                                                        <td><span><?php echo $user->user_email; ?></span></td>
                                                                        <?php
                                                                        if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                                            $table_name = $wpdb->prefix . 'bp_groups_members';
                                                                            $g_id       = $user->ID;
                                                                            $group_id   = $wpdb->get_var($wpdb->prepare("SELECT group_id FROM $table_name WHERE is_confirmed =%s and user_id=%s", '1', $g_id));
                                                                            $table_name = $wpdb->prefix . 'bp_groups';
                                                                            if ($group_id != 0) {
                                                                                $myrows = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_name WHERE id =%s", $group_id));
                                                                        ?>
                                                                                <td><?php echo $myrows; ?></td>
                                                                            <?php
                                                                            } else {
                                                                            ?>
                                                                                <td>--</td>
                                                                        <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </tr>
                                                                <?php
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                        <?php
                                                        foreach ($wau_users as $user) {
                                                        ?>
                                                            <input type="hidden" name="<?php echo $user->ID; ?>" value="<?php echo $user->user_email; ?>">
                                                        <?php
                                                        }
                                                        ?>
                                                        <table id="list_user_table" class="display allcsv_datatable" cellspacing="0" width="100%">
                                                            <thead>
                                                                <tr style="text-align:left">
                                                                    <th style="text-align:center"><input name="select_all_csv" value="1" id="example-csv-select-all" class="select-all" type="checkbox"></th>
                                                                    <th>Subscriber List</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $myrows = get_option('weu_subscriber_lists');
                                                                if (empty($myrows))
                                                                    $myrows = array('default');
                                                                foreach ($myrows as $csv_file) {
                                                                ?>
                                                                    <tr style="text-align:left">
                                                                        <td style="text-align:center">
                                                                            <input type="checkbox" name="csv_file_name[]" value="<?php echo $csv_file; ?>" class="checkbox1 chk_list">
                                                                        </td>
                                                                        <td><span id="getDetail"><?php echo $csv_file; ?></span></td>
                                                                    </tr>
                                                                <?php
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <?php
                                                foreach ($myrows as $csv_file) {
                                                ?>
                                                    <input type="hidden" name="<?php echo $csv_file; ?>" value="<?php echo $csv_file; ?>">
                                                <?php
                                                }
                                                global $wpdb;
                                                $table_name = $wpdb->prefix . 'groups_group';
                                                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) { // WordPress database error: [Table 'groups_group' doesn't exist] Start
                                                    $wprow = $wpdb->get_results("SELECT * FROM $table_name");
                                                }
                                                ?>
                                                <tr class="group_toggle">
                                                    <th></th>
                                                    <td colspan="3">
                                                        <table id="example4" class="display allgrp_datatable" cellspacing="0" width="100%">
                                                            <thead>
                                                                <tr style="text-align:left">
                                                                    <th style="text-align:center"><input name="select_all" value="1" id="example-group-select-all" class="select-all" type="checkbox"></th>
                                                                    <th>Group Name</th>
                                                                    <?php
                                                                    $weu_arconf_buff = array();
                                                                    $weu_arconf_buff = get_option('weu_ar_config_options');
                                                                    if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                                    ?>
                                                                        <th>BP Group</th>
                                                                    <?php } ?>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                if (is_array($wprow) || is_object($wprow)) {
                                                                    foreach ($wprow as $user) {
                                                                ?>
                                                                        <tr style="text-align:left">
                                                                            <td style="text-align:center"><input type="checkbox" name="ea_user_group[]" value="<?php echo $user->group_id; ?>" class="checkbox2 chk_user"></td>
                                                                            <td><span id="getDetail"><?php echo $user->name; ?></span></td>
                                                                            <?php
                                                                            if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                                                $table_name = $wpdb->prefix . 'bp_groups_members';
                                                                                $g_id = $user->ID;
                                                                                $group_id = $wpdb->get_var($wpdb->prepare("SELECT group_id FROM $table_name WHERE is_confirmed =%s and user_id=%s", '1', $g_id));
                                                                                $table_name = $wpdb->prefix . 'bp_groups';
                                                                                if ($group_id != 0) {
                                                                                    $myrows = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_name WHERE id =%s", $group_id));
                                                                            ?>
                                                                                    <td><?php echo $myrows; ?></td>
                                                                                <?php
                                                                                } else {
                                                                                ?>
                                                                                    <td>--</td>
                                                                        <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        </tr>
                                                                    <?php
                                                                }
                                                                    ?>
                                                            </tbody>
                                                        </table>
                                                        <?php
                                                        $mail_content = "";
                                                        ?>
                                                <tr id="wau_user_role" style="display:none">
                                                    <th>Select Roles</th>
                                                    <td colspan="3">
                                                        <select name="user_role[]" multiple class="wau_boxlen" id="wau_role">
                                                            <option value="" selected disabled>-- Select Role --</option>
                                                            <?php
                                                            foreach ($roles as $value) {
                                                            ?>
                                                                <option><?php echo $value; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Send Email To Bcc &nbsp;</th>
                                                    <td style="width: 224px"><input type="radio" name="rbtn1" id="user_role" onclick="radioFunction1()" value="user" checked> User &nbsp;</td>
                                                    <td style="width: 224px"><input type="radio" name="rbtn1" id="r_role" onclick="radioFunction1()" value="role"> Role </td>
                                                    <td style="width: 224px"><input type="radio" name="rbtn1" id="check_list" onclick="radioFunction1()" value="csv"> List </td>
                                                    <?php
                                                    if (is_plugin_active('groups/groups.php')) {
                                                    ?>
                                                        <td id="grp1" style="width: 224px"><input type="radio" name="rbtn1" id="g_group1" onclick="radioFunction1()" value="group"> Groups </td>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <td id="grp1" style="width: 224px"><input type="radio" name="rbtn1" id="g_group1" onclick="radioFunction1()" value="group" disabled> Groups </td>
                                                    <?php
                                                    }
                                                    ?>
                                                </tr>
                                                <tr class="wau_user_toggle1">
                                                    <th></th>
                                                    <td colspan="3">
                                                        <table id="User_email_bcc_table" class="display alluser_datatable" cellspacing="0" width="100%">
                                                            <thead>
                                                                <tr style="text-align:left">
                                                                    <th style="text-align:center"><input name="select_all" value="1" id="example-select-all_bcc" class="select-all_bcc" type="checkbox"></th>
                                                                    <th>Dispaly Name</th>
                                                                    <th>Email</th>
                                                                    <?php
                                                                    $weu_arconf_buff = array();
                                                                    $weu_arconf_buff = get_option('weu_ar_config_options');
                                                                    if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                                    ?>
                                                                        <th>BP Group</th>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                foreach ($wau_users as $user) {
                                                                ?>
                                                                    <tr style="text-align:left">
                                                                        <td style="text-align:center">
                                                                            <input type="checkbox" name="ea_user_name_bcc[]" value="<?php echo $user->ID; ?>" class="checkbox_bcc chk_user">
                                                                        </td>
                                                                        <td><span id="getDetail"><?php echo $user->display_name; ?></span></td>
                                                                        <td><span><?php echo $user->user_email; ?></span></td>
                                                                        <?php
                                                                        if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                                            $table_name = $wpdb->prefix . 'bp_groups_members';
                                                                            $g_id = $user->ID;
                                                                            $group_id = $wpdb->get_var($wpdb->prepare("SELECT group_id FROM $table_name WHERE is_confirmed =%s and user_id=%s", '1', $g_id));
                                                                            $table_name = $wpdb->prefix . 'bp_groups';
                                                                            if ($group_id != 0) {
                                                                                $myrows = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_name WHERE id =%s", $group_id));
                                                                        ?>
                                                                                <td><?php echo $myrows; ?></td>
                                                                            <?php
                                                                            } else {
                                                                            ?>
                                                                                <td>--</td>
                                                                        <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                    </td>
                                                </tr>
                                                </tr>
                                            <?php
                                                                }
                                            ?>
                                </tbody>
                            </table>
                            <tr class="list_bcc">
                                <th></th>
                                <td colspan="3">
                                    <?php
                                    foreach ($wau_users as $user) {
                                    ?>
                                        <input type="hidden" name="<?php echo $user->ID; ?>" value="<?php echo $user->user_email; ?>">
                                    <?php
                                    }
                                    ?>
                                    <table id="list_bcc" class="display allcsv_datatable" cellspacing="0" width="100%">
                                        <thead>
                                            <tr style="text-align:left">
                                                <th style="text-align:center"><input name="select_all_csv" value="1" id="example-csv-select-all_bcc" class="select-all_bcc" type="checkbox"></th>
                                                <th>Subscriber List</th>
                                            </tr>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                $myrows = get_option('weu_subscriber_lists');
                                if (empty($myrows))
                                    $myrows = array('default');
                                foreach ($myrows as $csv_file) {
                                ?>
                                    <tr style="text-align:left">
                                        <td style="text-align:center">
                                            <input type="checkbox" name="csv_file_name_bcc[]" value="<?php echo $csv_file; ?>" class="checkbox_list chk_list">
                                        </td>
                                        <td><span id="getDetail"><?php echo $csv_file; ?></span></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                            </table>
                            </td>
                            </tr>
                            <?php
                            foreach ($myrows as $csv_file) {
                            ?>
                                <input type="hidden" name="<?php echo $csv_file; ?>" value="<?php echo $csv_file; ?>">
                            <?php
                            }
                            ?>
                            </tr>
                            </td>
                            <tr>
                                <?php
                                $table_name1 = $wpdb->prefix . 'groups_group';
                                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name1'") == $table_name1) { // WordPress database error: [Table 'groups_group' doesn't exist] Start
                                    $wprow1 = $wpdb->get_results("SELECT * FROM $table_name");
                                }
                                ?>
                            <tr class="group_toggle_bcc">
                                <th></th>
                                <td colspan="3">
                                    <table id="group_bcc" class="display allgrp_datatable" cellspacing="0" width="100%">
                                        <thead>
                                            <tr style="text-align:left">
                                                <th style="text-align:center"><input name="select_all" value="1" id="example-group-select-all_bcc" class="select-all" type="checkbox"></th>
                                                <th>Group Name</th>
                                                <?php
                                                $weu_arconf_buff = array();
                                                $weu_arconf_buff = get_option('weu_ar_config_options');
                                                if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                ?>
                                                    <th>BP Group</th>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (is_array($wprow1) || is_object($wprow1)) {
                                                foreach ($wprow1 as $user) {
                                            ?>
                                                    <tr style="text-align:left">';
                                                        <td style="text-align:center">
                                                            <input type="checkbox" name="ea_user_group_bcc[]" value="<?php echo $user->group_id; ?>" class="checkbox3 chk_user">
                                                        </td>
                                                        <td><span id="getDetail"><?php echo $user->name; ?></span></td>
                                                        <?php
                                                        if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes') {
                                                            $table_name1 = $wpdb->prefix . 'bp_groups_members';
                                                            $g_id = $user->ID;
                                                            $group_id = $wpdb->get_var($wpdb->prepare("SELECT group_id FROM $table_name WHERE is_confirmed =%s and user_id=%s", '1', $g_id));
                                                            $table_name = $wpdb->prefix . 'bp_groups';
                                                            if ($group_id != 0) {
                                                                $myrows = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_name WHERE id =%s", $group_id));
                                                        ?>
                                                                <td><?php echo $myrows; ?></td>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <td>--</td>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                </td>
                            </tr>
                    <?php
                                                }
                                            }
                    ?>
                    </tbody>
                    </table>
                    <?php
                    $mail_content = "";
                    ?>
                    <tr id="wau_user_role1" style="display:none">
                        <th>Select Roles</th>
                        <td colspan="3"><select name="user_role_bcc[]" multiple class="wau_boxlen" id="wau_role">
                                <option value="" selected disabled>-- Select Role --</option>
                                <?php
                                foreach ($roles as $value) {
                                ?>
                                    <option><?php echo $value; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                    $timestamp = wp_next_scheduled('WP_mail_event');
                    $cron_status = get_option('cron_job_status');
                    if ($cron_status['cron_job'] == 'yes') {
                    ?>
                        <tr id='cron'>
                            <th>Enter Cron Batch<font color=red>*</font> &nbsp;</th>
                            <td style="width: 224px"><input type="number" id="cron_btn" min="1" placeholder="Enter Cron Batch" name="cron_number">
                            </td>
                            <td>
                                <center>Your next cron schedule</center>
                            </td>
                            <td>
                                <center>
                                    <p class='double'>
                                        <?php get_date_from_gmt(date('Y-m-d H:i:s', $timestamp)); ?>
                                    </p>
                                </center>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <input type="hidden" name="save_temp" id="save_temp">
                    <tr>
                        <th>Template Options <font color=red>*</font> &nbsp;</th>
                        <td style="width: 224px"><input type="radio" id="rdb2" type="radio" name="toggler" value="2" onclick="checkFunction()" checked> Choose Existing Template </td>
                        <td style="width: 224px"><input type="radio" id="rdb1" type="radio" name="toggler" value="1" onclick="checkFunction()">Create New Template &nbsp;</td>
                        <td style="width: 224px"><input type="radio" id="rdb3" type="radio" name="toggler" value="3" onclick="checkFunction()"> Disable </td>
                    </tr>
                    <?php
                    $table_name = $wpdb->prefix . 'email_user';
                    $myrows = $wpdb->get_results($wpdb->prepare("SELECT id, template_key, template_value FROM $table_name WHERE status = %s", 'template'));
                    $weu_arconf_buff = array();
                    $weu_arconf_buff = get_option('weu_sample_template');
                    $template_1 = $weu_arconf_buff['sample_template_1'];
                    $template_2 = $weu_arconf_buff['sample_template_2'];
                    $ar_conf_page = admin_url("admin.php?page=weu_email_auto_config");
                    ?>
                    <tr id="blk-1" class="toHide1">
                        <th>Select Template <font color="red">*</font>
                        </th>
                        <td colspan="3">
                            <select autocomplete="off" id="wau_template" name="mail_template[]" onchange="onchangeload()" class="wau-template-selector" style="width:100%">
                                <option selected="selected" value="">- Select -</option>
                                <option value="<?php echo $template_1; ?>" data-id="-1" id="wau_template_t1"> Default Template - 1 </option>
                                <option value="<?php echo $template_2; ?>" data-id="0" id="wau_template_t2"> Default Template - 2 </option>
                                <?php
                                for ($i = 0; $i < count($myrows); $i++) { ?>
                                    <option value="<?php
                                                    echo htmlspecialchars($myrows[$i]->template_value, ENT_QUOTES, 'UTF-8');
                                                    ?>" data-id="<?php echo htmlspecialchars($myrows[$i]->id, ENT_QUOTES, 'UTF-8'); ?>"><?php
                                                                                                                                        echo $myrows[$i]->template_key;
                                                                                                                                        ?> </option>
                                <?php
                                }
                                ?>
                            </select>
                            <input id="title" name="template_id" size="30" type="text" class="criteria_rate " value="" readonly="readonly" hidden />
                        </td>
                    </tr>
                    <tr>
                        <th>Subject <font color="red">*</font>
                        </th>
                        <td colspan="3"><input type="text" name="wau_sub" class="wau_boxlen" id="sub_valid" placeholder="write your email subject here" required></td>
                    </tr>
                    <tr id="blk-2" class="toHide" style="display: none;">
                        <th>Template Name <font color="red">*</font>
                        </th>
                        <td colspan="3">
                            <input type="text" name="wau_temp_name" class="wau_boxlen" id="temp_name_req" placeholder="write template name here">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top"><label for="wau_mailcontent">Message</label></th>
                        <td colspan="3">
                            <div id="msg" class="wau_boxlen" name="wau_mailcontent">
                                <?php
                                wp_editor($mail_content, "wau_mailcontent", array(
                                    'wpautop' => false,
                                    'media_buttons' => true
                                ));
                                ?>
                            </div>
                            Please use shortcode placeholder as below.</br>
                            <p id="nickname"><b> [[user-nickname]] : </b>use this placeholder to display user nickname</p>
                            <p><b> [[first-name]] : </b>use this placeholder to display user first name </p>
                            <p id="lname"><b> [[last-name]] : </b>use this placeholder to display user last name </p>
                            <p><b> [[site-title]] : </b>use this placeholder to display your site title</p>
                            <p>
                            <p id="dname"><b> [[display-name]] : </b>use this placeholder for display name</p>
                            <p><b> [[user-email]] : </b>use this placeholder to display user email</p>
                            <p>
                            <p id="slink"><b> [[subscribe-link]] : </b>use this placeholder to display subscribe link in email. Please make sure you configure subscribe page link <a href=' . esc_url($ar_conf_page) . '>here</a> before using this.</p>
                            <p><b> [[unsubscribe-link]] : </b>use this placeholder to display unsubscribe link in email. Please make sure you configure unsubscribe page link <a href=' . esc_url($ar_conf_page) . '>here</a> before using this.</p>
                            <p><b> [[user-avatar]] : </b>use this placeholder to display user avatar in email</p>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <?php
                        if ($count != 0) {
                        ?>
                            <td colspan="3">
                                <div><input type="submit" value="Send" class="button button-hero button-primary" id="weu_send"></div>
                            </td>
                        <?php
                        } else {
                        ?>
                            <td colspan="3">
                                <div><input type="submit" value="Send" class="button button-hero button-primary" id="weu_send" disabled></div>
                            </td>
                        <?php
                        }
                        ?>
                    </tr>
                    </tbody>
                    </table>
                        </form>
                    </div>

<?php
                }
                // }//
            } //
        } //
    }

    if (!function_exists('EmbedImageInMail')) {
        function EmbedImageInMail()
        {
            global $phpmailer;
            $body = $phpmailer->Body; //Email content send to mailer
            $uploadDir = wp_upload_dir(); //Wordpress upload directory
            $baseUploadUrl = $uploadDir['baseurl'];
            $baseUploadPath = $uploadDir['basedir'];
            // get all img tags
            preg_match_all('/<img.*?>/', $body, $matches);
            if (!isset($matches[0])) {
                return;
            }
            // foreach tag, create the cid and embed image
            $i = 1;
            foreach ($matches[0] as $img) {
                // make cid
                $id = 'img' . ($i++);
                // replace image web path with local path
                preg_match('/src=\'(.*?)\'/', $img, $m);
                if (empty($m)) {
                    preg_match('/src="(.*?)"/', $img, $m);
                }
                if (!isset($m[1])) {
                    continue;
                }
                if (strpos($m[1], get_bloginfo('url')) === false) {
                    continue;
                }
                // $m[1] holds the url of image
                $uploadPathSegment = str_ireplace($baseUploadUrl, '', $m[1]);
                $uploadPathSegment = rtrim($uploadPathSegment, '/');
                $completeImagePath = $baseUploadPath . $uploadPathSegment;
                // add
                $phpmailer->AddEmbeddedImage($completeImagePath, $id, 'attachment', 'base64');
                $body = str_replace($img, '<img class="prodImage" alt="Product Image" src="cid:' . $id . '"/>', $body);
            }
            $phpmailer->Body = $body;
        }
    }
}
