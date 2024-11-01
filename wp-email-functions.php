<?php
if (!defined('ABSPATH'))
    exit;

/**
 * *Validate Unsubscribers/Subscribers It Works
 */

if (!function_exists('tsweu_isUnsubscribe')) {
    function tsweu_isUnsubscribe($userId, $userEmail)
    {
        $userEmail = array_map(function ($v) {
            return "'" . esc_sql($v) . "'";
        }, $userEmail);
        $userEmail = implode(',', $userEmail);

        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_unsubscriber';
        $myrows     = $wpdb->get_row("SELECT * FROM $table_name WHERE uid = $userId AND email IN ($userEmail)");

        if (!empty($myrows) && count(array($myrows)) != 0) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * * User Unsubscribe It Works
 * 
 */

if (!function_exists('tsweu_userUnsubscibe')) {
    function tsweu_userUnsubscibe($id, $email, $list)
    {
        $weu_arconf_buff          = get_option('weu_ar_config_options');
        $unubscribe_url           = isset($weu_arconf_buff['rbtn_user_unsubscribe_url']) ? $weu_arconf_buff['rbtn_user_unsubscribe_url'] : '';
        $unsubscribe_link         = add_query_arg(array(
            'id' => $id,
            'email' => $email,
            'list' => $list
        ), $unubscribe_url);
        $unsubscribe_link_details = '<a href="' . esc_url($unsubscribe_link) . '">Unsubscribe</a>';
        return $unsubscribe_link_details;
    }
}

/**
 * *User check by array
 * 
 */
if (!function_exists('tsweu_is_unsubscribe_arr')) {
    function tsweu_is_unsubscribe_arr($emails_arr)
    {
        global $wpdb;
        $sent_to_emails = array();
        $table_name     = $wpdb->prefix . 'weu_unsubscriber';
        $unsubscribers  = $wpdb->get_results("SELECT `email` FROM $table_name");
        if (!empty($emails_arr)) {
            $count = count(array($emails_arr));
        } else {
            $count = 0;
        }
        for ($i = 0; $i < $count; $i++) {
            if (!in_array($emails_arr[$i], $unsubscribers)) {
                array_push($sent_to_emails, $emails_arr[$i]);
            }
        }
        return $sent_to_emails;
    }
}

/**
 * * Sent Emails
 */

if (!function_exists('tsweu_sent_emails')) {
    function tsweu_sent_emails()
    {
        require_once('wp-email-sentmailTable.php');

        $table = new SentEmailTable();

        $message = '';
        if ('delete' === $table->current_action()) {
            $message = '<div class="updated below-h2" id="message"><p>Email has been deleted successfully</p></div>';
        }
?>
        <div class="wrap">
            <h2>List Of All Sent Emails </h2>
            <p>Here you will find the list of all Sent Emails which are sent through this (WP Email Users) plugin.</p>
            <?php echo $message; ?>
            <form id="sentemails-table" method="POST">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                <?php
                $table->prepare_items();
                $table->display(); ?>
            </form>

        </div>
    <?php
    }
}

if (!function_exists('tsweu_setup_activation_data')) {
    function tsweu_setup_activation_data()
    {
        global $wpdb, $table_prefix; //Fixed
        $table_name = $wpdb->prefix . 'email_user';
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name(

            id int(11) NOT NULL AUTO_INCREMENT,

            template_key varchar(20) NOT NULL,

            template_value longtext NOT NULL,

            status varchar(20) NOT NULL,

            temp_subject varchar(500) NOT NULL,

            UNIQUE KEY id(id)

            );";
            $rs  = $wpdb->query($sql);
        }

        $table_name_notifi = $wpdb->prefix . 'weu_user_notification';
        if ($wpdb->get_var("show tables like '$table_name_notifi'") != $table_name_notifi) {
            $sql = "CREATE TABLE $table_name_notifi(

            id int(11) NOT NULL AUTO_INCREMENT,

            template_id int(11) NOT NULL,

            template_value longtext NOT NULL,

            email_for varchar(20) NOT NULL,

            email_by varchar(20) NOT NULL,

            email_value longtext NOT NULL,

            UNIQUE KEY id(id)

            );";
            $rs2 = $wpdb->query($sql);
        }
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_notifi");
        if ($count == 0) {
            $admin_email = get_option('admin_email');
            $admin_email = serialize($admin_email);
            for ($i = 1; $i <= 5; $i++) {
                $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_notifi . "`(`template_id`, `email_value`) VALUES (%d,%s)
                    ", $i, $admin_email));
            }
        } else {
        }
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_notifi");
        if ($count == 0) {
            $admin_email = get_option('admin_email');
            $admin_email = serialize($admin_email);
            for ($i = 1; $i <= 5; $i++) {
                $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name_notifi . "`(`template_id`, `email_value`) VALUES (%d,%s)", $i, $admin_email));
            }
        } else {
        }
        /*EMAIL SENT TABLE STARTS*/
        $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
        if ($wpdb->get_var("show tables like '$table_name_sent_email'") != $table_name_sent_email) {
            $sql = "CREATE TABLE $table_name_sent_email(

            weu_sent_id INT unsigned NOT NULL AUTO_INCREMENT,

            weu_sent_type VARCHAR(25),

            weu_email_subject varchar(100) NOT NULL,

            weu_email_body longtext NOT NULL,

            weu_from_name varchar(50) NOT NULL,

            weu_from_email varchar(50) NOT NULL,

            weu_to_type varchar(25) NOT NULL,

            weu_status int(1) NOT NULL,

            weu_sent_date_time datetime NOT NULL default '0000-00-00 00:00:00',

            to_email varchar(1000) NOT NULL,

            weu_seen int(1) NOT NULL,

            weu_seen_count int(100) NOT NULL,

            image_id bigint(255) NOT NULL,

            PRIMARY KEY (weu_sent_id)

            );";
            $rs3 = $wpdb->query($sql);
        } else {
            $wpdb->get_results("SHOW COLUMNS FROM $table_name_sent_email LIKE 'to_email'");
            $to_email = $wpdb->num_rows;
            if ($to_email == 0) {
                $wpdb->query("ALTER TABLE $table_name_sent_email ADD to_email VARCHAR(1000) NOT NULL;");
            }
            $wpdb->get_results("SHOW COLUMNS FROM $table_name_sent_email LIKE 'weu_seen'");
            $weu_seen = $wpdb->num_rows;
            if ($weu_seen == 0) {
                $wpdb->query("ALTER TABLE $table_name_sent_email ADD weu_seen int(1) NOT NULL;");
            }
            $wpdb->get_results("SHOW COLUMNS FROM $table_name_sent_email LIKE 'weu_seen_count'");
            $weu_seen_count = $wpdb->num_rows;
            if ($weu_seen_count == 0) {
                $wpdb->query("ALTER TABLE $table_name_sent_email ADD weu_seen_count int(100) NOT NULL;");
            }
            $wpdb->get_results("SHOW COLUMNS FROM $table_name_sent_email LIKE 'image_id'");
            $image_id = $wpdb->num_rows;
            if ($image_id == 0) {
                $wpdb->query("ALTER TABLE $table_name_sent_email ADD image_id bigint(255) NOT NULL;");
            }
        }

        /*EMAIL SENT TABLE ENDS*/
        $table_name_subscribe = $wpdb->prefix . 'weu_subscribers';
        if ($wpdb->get_var("show tables like '$table_name_subscribe'") != $table_name_subscribe) {
            $sql = "CREATE TABLE $table_name_subscribe(
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            list varchar(100) NOT NULL,
            status int(11) NOT NULL,
            authtoken int(11) NOT NULL,
            datetime datetime NOT NULL default '0000-00-00 00:00:00',
            UNIQUE KEY id(id));";
            $rs4 = $wpdb->query($sql);
        }
        $table_name_unsubscribe = $wpdb->prefix . 'weu_unsubscriber';
        if ($wpdb->get_var("show tables like '$table_name_unsubscribe'") != $table_name_unsubscribe) {
            $sql = "CREATE TABLE $table_name_unsubscribe(
            id int(11) NOT NULL AUTO_INCREMENT,
            uid int(30) NOT NULL,
            email varchar(100) NOT NULL,
            list varchar(100) NOT NULL,
            datetime datetime NOT NULL,
            UNIQUE KEY id(id));";
            $rs5 = $wpdb->query($sql);
        }
        $table_name_group = $wpdb->prefix . 'weu_group27';
        if ($wpdb->get_var("show tables like '$table_name_group'") != $table_name_group) {
            $sql = "CREATE TABLE $table_name_group(

            id int(11) NOT NULL AUTO_INCREMENT,

            user_name varchar(100) NOT NULL,

            group_name varchar(100) NOT NULL,

            email varchar(100) NOT NULL,

            datetime datetime NOT NULL default '0000-00-00 00:00:00',

            UNIQUE KEY id(id)

            );";
            $rs  = $wpdb->query($sql);
        }
        $weu_temp_smtp = get_option('weu_smtp_data_options');
        if (empty($weu_temp_smtp)) {
            $weu_smtp                = array();
            $weu_smtp["smtp_status"] = "no";
            $option                  = "weu_smtp_data_options";
            add_option($option, $weu_smtp);
        }
        $weu_arconf_buff = get_option('weu_ar_config_options');
        if (empty($weu_arconf_buff)) {
            $weu_temp_config                                  = array();
            $weu_temp_config["weu_arconfig_user_reg"]         = "off";
            $weu_temp_config["weu_arconfig_post_pub"]         = "off";
            $weu_temp_config["weu_arconfig_comment_pub"]      = "off";
            $weu_temp_config["weu_arconfig_pass_reset"]       = "off";
            $weu_temp_config["weu_arconfig_role_change"]      = "off";
            $weu_temp_config["weu_arconfig_buddypress"]       = "no";
            $weu_temp_config["rbtn_user_unsubscribe_url"]     = "";
            $weu_temp_config["rbtn_user_unsubscribe_success"] = "";
            $weu_temp_config["rbtn_user_unsubscribe_failure"] = "";
            $option                                           = "weu_ar_config_options";
            add_option($option, $weu_temp_config);
        }
        $weu_arconf = get_option('weu_subscriber_lists');
        if (empty($weu_arconf_buff)) {
            $weu_temp_list   = array();
            $weu_temp_list[] = "default";
            $options         = "weu_subscriber_lists";
            add_option($options, $weu_temp_list);
        }
        $table_name_conf = $wpdb->prefix . 'weu_smtp_conf';
        if ($wpdb->get_var("show tables like '$table_name_conf'") != $table_name_conf) {
            $sql = "CREATE TABLE $table_name_conf(
            `conf_id` int(11) NOT NULL AUTO_INCREMENT,
            `smtp_from_name` varchar(50) NOT NULL,
            `smtp_from_email` varchar(100) NOT NULL,
            `smtp_host` varchar(1000) NOT NULL,
            `smtp_smtpsecure` varchar(20) NOT NULL,
            `smtp_port` varchar(20) NOT NULL,
            `smtp_username` varchar(100) NOT NULL,
            `smtp_password` varchar(200) NOT NULL,
            `smtp_mail_limit` int(11) NOT NULL,
            `smtp_priority` int(11) NOT NULL,
            `smtp_mails_used` int(11) NOT NULL,
            `smtp_last_mail_time` varchar(50) NOT NULL,
            `smtp_status` int(1) NOT NULL,
            PRIMARY KEY (`conf_id`));";
            $rs5 = $wpdb->query($sql);
        }
        $weu_temp_subject = get_option('weu_sample_template_subject');
        if (empty($weu_temp_subject)) {
            $weu_template_sub                      = array();
            $weu_template_sub["new_user_register"] = "New User has been registered on your website.";
            $weu_template_sub["new_comment"]       = "New Comment on your website.";
            $weu_template_sub["new_post"]          = "New Post has been published on your website.";
            $weu_template_sub["new_password"]      = "User has reset password on website.";
            $weu_template_sub["sample_template_1"] = "Sample Template Subject.";
            $weu_template_sub["sample_template_2"] = "Sample Template Subject.";
            $weu_template_sub["user_role_changed"] = "User role has been changed on your website.";
            $option_template                       = "weu_sample_template_subject";
            add_option($option_template, $weu_template_sub);
        }
        $weu_temp_smtp = get_option('weu_sample_template');
        if (empty($weu_sample_template)) {
            $weu_template_content                      = array();
            $weu_template_content["new_user_register"] = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title></title><style></style></head><body><table  align='center' cellpadding='0' cellspacing='0' height='100%' width='100' id='bodyTable' style='background-color: #F1F1F1;color: ;border: 2px solid gray;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='600' id='emailContainer' style='margin:15px;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailHeader'><tr><td align='center' valign='top'><h1>Welcome<h1><hr></td></tr><tr><td> Hi there,</td></tr><tr><td style='margin-left: 10px;'></td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailBody' style='background-color: green;color: white;text-align: justify' ><tr><td align='left' valign='top'>New User has registered on [[site-title]] with following credentials,</br>User Name: [[username]] </br> Email: [[useremail]]</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailFooter' style='background-color: black;color: white'><tr><td align='center' valign='top' >Thanks ( [[site-title]] )</td></tr></table></td></tr></table></td></tr></table></body></html>";
            $weu_template_content["new_comment"]       = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title></title><style></style></head><body><table  align='center' cellpadding='0' cellspacing='0' height='100%' width='100' id='bodyTable' style='background-color: #F1F1F1;color: ;border: 2px solid gray;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='600' id='emailContainer' style='margin:15px;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailHeader'><tr><td align='center' valign='top'><h1>Welcome<h1><hr></td></tr><tr><td> Hi there,</td></tr><tr><td style='margin-left: 10px;'></td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailBody' style='background-color: green;color: white;text-align: justify' ><tr><td align='left' valign='top'>User has commented on [[site-title]] with following credentials,</br>User Name: [[username]] </br> Email: [[useremail]]</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailFooter' style='background-color: black;color: white'><tr><td align='center' valign='top' >Thanks ( [[site-title]] )</td></tr></table></td></tr></table></td></tr></table></body></html>";
            $weu_template_content["new_post"]          = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title></title><style></style></head><body><table  align='center' cellpadding='0' cellspacing='0' height='100%' width='100' id='bodyTable' style='background-color: #F1F1F1;color: ;border: 2px solid gray;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='600' id='emailContainer' style='margin:15px;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailHeader'><tr><td align='center' valign='top'><h1>Welcome<h1><hr></td></tr><tr><td> Hi there,</td></tr><tr><td style='margin-left: 10px;'></td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailBody' style='background-color: green;color: white;text-align: justify' ><tr><td align='left' valign='top'>User has posted on [[site-title]] with following credentials,</br>User Name: [[username]] </br> Email: [[useremail]]</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailFooter' style='background-color: black;color: white'><tr><td align='center' valign='top' >Thanks ( [[site-title]] )</td></tr></table></td></tr></table></td></tr></table></body></html>";
            $weu_template_content["new_password"]     = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title></title><style></style></head><body><table  align='center' cellpadding='0' cellspacing='0' height='100%' width='100' id='bodyTable' style='background-color: #F1F1F1;color: ;border: 2px solid gray;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='600' id='emailContainer' style='margin:15px;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailHeader'><tr><td align='center' valign='top'><h1>Welcome<h1><hr></td></tr><tr><td> Hi there,</td></tr><tr><td style='margin-left: 10px;'></td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailBody' style='background-color: green;color: white;text-align: justify' ><tr><td align='left' valign='top'>You have successfully reset your password [[site-title]] with following credentials,</br></br></br>User Name: [[username]] </br> password:[[password]]</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailFooter' style='background-color: black;color: white'><tr><td align='center' valign='top' >Thanks ( [[site-title]] )</td></tr></table></td></tr></table></td></tr></table></body></html>";
            $weu_template_content["sample_template_1"] = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title></title><style></style></head><body><table  align='center' cellpadding='0' cellspacing='0' height='100%' width='100' id='bodyTable' style='background-color: #F1F1F1;color: ;border: 2px solid gray;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='600' id='emailContainer' style='margin:15px;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailHeader'><tr><td align='center' valign='top'><h1>Simple HTML email template<h1><hr></td></tr><tr><td> Hi there,</td></tr><tr><td style='margin-left: 10px;'>Send a simple HTML email with a basic design.</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailBody' style='background-color: green;color: white;text-align: justify' ><tr><td align='left' valign='top'>This is where my body content goes.Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailFooter' style='background-color: black;color: white'><tr><td align='center' valign='top' >This is where my footer content goes.</td></tr></table></td></tr></table></td></tr></table></body></html>";
            $weu_template_content["sample_template_2"] = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title></title><style></style></head><body><table  align='center' cellpadding='0' cellspacing='0' height='100%' width='100' id='bodyTable' style='background-color: #F1F1F1;color: ;border: 2px solid gray;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='600' id='emailContainer' style='margin:15px;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailHeader'><tr><td align='center' valign='top'><h1>Simple HTML email template<h1><hr></td></tr><tr><td> Hi there,</td></tr><tr><td style='margin-left: 10px;'>Send a simple HTML email with a basic design.</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailBody' style='background-color: green;color: white;text-align: justify' ><tr><td align='left' valign='top'>This is where my body content goes.Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailFooter' style='background-color: black;color: white'><tr><td align='center' valign='top' >This is where my footer content goes.</td></tr></table></td></tr></table></td></tr></table></body></html>";
            $weu_template_content["user_role_changed"] = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title></title><style></style></head><body><table  align='center' cellpadding='0' cellspacing='0' height='100%' width='100' id='bodyTable' style='background-color: #F1F1F1;color: ;border: 2px solid gray;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='600' id='emailContainer' style='margin:15px;'><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailHeader'><tr><td align='center' valign='top'><h1>Welcome<h1><hr></td></tr><tr><td> Hi there,</td></tr><tr><td style='margin-left: 10px;'></td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailBody' style='background-color: green;color: white;text-align: justify' ><tr><td align='left' valign='top'>Your role has been changed to [[new role]] for  [[site-title]],</br></br></br>User Name: [[username]] </br> Email: [[useremail]]</td></tr></table></td></tr><tr><td align='center' valign='top'><table border='0' cellpadding='20' cellspacing='0' width='100%' id='emailFooter' style='background-color: black;color: white'><tr><td align='center' valign='top' >Thanks ( [[site-title]] )</td></tr></table></td></tr></table></td></tr></table></body></html>";
            $option_template = "weu_sample_template";
            add_option($option_template, $weu_template_content);
        }
    }
}

if (!function_exists('tsweu_register_menu')) {
    function tsweu_register_menu()
    {
        global $current_user;
        $user_roles    = $current_user->roles;
        $get_roles     = get_option('enable_plugin_for_other_roles');
        $user          = wp_get_current_user();
        $get_user_role = $user->roles;
        foreach ($get_user_role as $display_get_user_role) {
            if (in_array($display_get_user_role, $get_roles) || $display_get_user_role == 'administrator') {
                if (current_user_can($display_get_user_role)) {
                    add_menu_page(
                        'WP Email Users page',
                        'WP Email Users',
                        'manage_options',
                        'weu-admin-page',
                        'tsweu_admin_page',
                        'dashicons-email-alt'
                    );
                    add_submenu_page(
                        'weu-admin-page',
                        'Send Email',
                        'Send Email',
                        'manage_options',
                        'weu_send_email',
                        'tsweu_admin_page'
                    );
                    add_submenu_page(
                        'weu-admin-page',
                        'WP Template page',
                        'Template Manager',
                        'manage_options',
                        'weu-template',
                        'tsweu_template'
                    );
                    add_submenu_page(
                        'weu-admin-page',
                        'SMTP Config',
                        'SMTP Configuration',
                        'manage_options',
                        'weu-smtp-config',
                        'tsweu_smtp_config_page'
                    );
                    add_submenu_page(
                        'weu-admin-page',
                        'WP Autoresponder Send',
                        'Send Autoresponder Email',
                        'manage_options',
                        'weu_email_setting',
                        'tsweu_email_setting'
                    );
                    add_submenu_page(
                        'weu-admin-page',
                        'WP Autoresponder Manage',
                        'Settings',
                        'manage_options',
                        'weu_email_auto_config',
                        'tsweu_email_auto_config'
                    );
                    add_submenu_page(
                        'weu-admin-page',
                        'List Manager',
                        'List Manager',
                        'manage_options',
                        'weu-manage-list',
                        'tsweu_admin_manage_list'
                    );
                    // cadd custom roles start
                    add_submenu_page(
                        'weu-admin-page',
                        'Add Custom Roles',
                        'Add Custom Roles',
                        'manage_options',
                        'weu_custom_role',
                        'tsweu_custom_role'
                    );
                    // cadd custom roles end
                    add_submenu_page(
                        'weu-admin-page',
                        'List Of Sent Emails',
                        'Sent Emails',
                        'manage_options',
                        'weu_sent_emails',
                        'tsweu_sent_emails'
                    );
                    add_submenu_page(
                        NULL,
                        'List Editor',
                        'List Editor',
                        'manage_options',
                        'weu-list-editor',
                        'tsweu_list_editor'
                    );
                    remove_submenu_page('weu-admin-page', 'weu-admin-page');
                }
            }
        }
    }
}
// add custom user role start
if (!function_exists('tsweu_custom_role')) {
    function tsweu_custom_role()
    {
        global $post;
        $admin_caps = get_role('administrator')->capabilities;
        foreach ($admin_caps as $key => $value) {
            $name                 = str_replace('_', ' ', $key);
            $capName              = ucfirst($name);
            $allCapbilities[$key] = $capName;
        }
    ?>
        <form name="form" method="post">
            <?php wp_nonce_field('form_action', 'form_nonce_field'); ?>
            <h3>Add Custom User Role</h3>
            <hr>
            Enter Role Name :
            <input type="text" name="custom_role" placeholder="Add User Role"> &nbsp;&nbsp;
            <br><br>
            <label>Select Capabilities</label><!--  &nbsp;&nbsp;<br><br> -->
            <select multiple data-live-search="true" class="selectpicker" name="select_multiple_roles_capabilities[]">
                <?php
                foreach ($allCapbilities as $current_user_caps_display) {
                ?>
                    <option value="<?php
                                    echo $current_user_caps_display;
                                    ?>"><?php
                                        echo $current_user_caps_display;
                                        ?></option>
                <?php
                }
                ?>
            </select>
            <br><br>
            <input type="submit" name="submit_role" class="button button-hero button-primary" value="Add User Role">
            <br><br>
        </form>

        <?php
        if (isset($_POST['submit_role']) && wp_verify_nonce($_POST['form_nonce_field'], 'form_action')) {
            global $wp_roles;
            $caplist        = array();
            $customUserRole = (isset($_POST['custom_role'])) ? sanitize_text_field($_POST['custom_role']) : "";
            if (!empty($_POST['select_multiple_roles_capabilities'])) {
                $smrc[] = sanitize_text_field($_POST['select_multiple_roles_capabilities']);
                foreach ($smrc as $check) {
                    array_push($caplist, $check);
                }
            }
            $userRoles   = $wp_roles->role_names;
            $newUserRole = str_replace(' ', '_', $customUserRole);
            $newUserRole = strtolower($newUserRole);
            if (($newUserRole != '' && $customUserRole != '') && !(array_key_exists($newUserRole, $userRoles))) {
                add_role($newUserRole, $customUserRole, array(
                    'read' => true
                ));
                foreach ($caplist as $cap) {
                    $role      = get_role($newUserRole);
                    $rolecap   = str_replace(' ', '_', $cap);
                    $roolecaps = strtolower($rolecap);
                    $role->add_cap($roolecaps);
                }
        ?>
                <script type="text/javascript">
                    swal({
                        type: "success",
                        title: "User Role created successfully",
                        showConfirmButton: true,
                    });
                </script>
                <style>
                    div.updated {
                        display: none;
                    }
                </style>
            <?php
            } else {
            ?>
                <script type="text/javascript">
                    swal({
                        type: "error",
                        title: "User Role creation failed",
                        showConfirmButton: true,
                    });
                </script>
                <style>
                    div.updated {
                        display: none;
                    }
                </style>
<?php
            }
        }
    }
}
