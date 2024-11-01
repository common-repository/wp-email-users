<?php

/**
 * *SMTP code
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

add_action('admin_enqueue_scripts', 'tsweu_enqueue_script_smtp');

if (!function_exists('tsweu_enqueue_script_smtp')) {
    function tsweu_enqueue_script_smtp()
    {
        $actual_link = $_SERVER['REQUEST_URI'];
        if (strpos($actual_link, 'weu_send_email') || strpos($actual_link, 'weu-template') || strpos($actual_link, 'weu-smtp-config') || strpos($actual_link, 'weu_email_setting') || strpos($actual_link, 'weu_email_auto_config') || strpos($actual_link, 'weu-manage-list') || strpos($actual_link, 'weu_custom_role') || strpos($actual_link, 'weu_sent_emails') || strpos($actual_link, 'weu-list-editor&listname')) {
            wp_enqueue_script('wp-smtp-drag', plugins_url('js/jquery.tablednd.0.7.min.js', __FILE__), array(), '0.7', false);
        }
    }
}

if (!function_exists('tsweu_smtp_config_page')) {
    function tsweu_smtp_config_page()
    {
        global $current_user, $wpdb, $wp_roles;
        $table_name = $wpdb->prefix . 'weu_smtp_conf';
        if (isset($_POST['weu_smtp_insert'])) {
            $weu_temp_options                = array();
            $weu_temp_options["smtp_status"] = sanitize_text_field($_POST['stat_smtp']);

            update_option("weu_smtp_data_options", $weu_temp_options);
            $smtp_port = sanitize_text_field($_POST['weu_smtp_port']);
            if ($smtp_port == "") {
                $smtp_port = 25;
            }
            $smtp_host       = sanitize_text_field($_POST['weu_smtp_host']);
            $smtp_security   = sanitize_text_field($_POST['weu_smtp_smtpsecure']);
            $smtp_username   = sanitize_text_field($_POST['weu_smtp_username']);
            $smtp_password   = sanitize_text_field($_POST['weu_smtp_password']);
            $smtp_mail_limit = sanitize_text_field($_POST['smtp_mail_limit']);
            if ($smtp_mail_limit == "") {
                $smtp_mail_limit = 10000;
            }
            $smtp_from_name = sanitize_text_field($_POST['weu_smtp_name']);
            $smtp_from_mail = sanitize_email($_POST['weu_smtp_mail']);
            $count          = $wpdb->get_var("SELECT COUNT(conf_id) FROM $table_name");
            $priority_count = $count + 1;
            $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`smtp_host`, `smtp_smtpsecure`, `smtp_username`,`smtp_password`,`smtp_port`,`smtp_mail_limit`,`smtp_priority`,`smtp_status`,`smtp_from_name`,`smtp_from_email`) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", $smtp_host, $smtp_security, $smtp_username, $smtp_password, $smtp_port, $smtp_mail_limit, $priority_count, 1, $smtp_from_name, $smtp_from_mail));
            echo '<div id="message" class="updated notice is-dismissible"><p>SMTP Configuration saved successfully!</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        } elseif (isset($_POST['weu_smtp_update'])) {
            $smtp_id   = sanitize_text_field($_POST['weu_smtp_edit']);
            $smtp_port = sanitize_text_field($_POST['weu_smtp_port']);
            if ($smtp_port == "") {
                $smtp_port = 25;
            }
            $smtp_host       = sanitize_text_field($_POST['weu_smtp_host']);
            $smtp_security   = sanitize_text_field($_POST['weu_smtp_smtpsecure']);
            $smtp_username   = sanitize_text_field($_POST['weu_smtp_username']);
            $smtp_password   = sanitize_text_field($_POST['weu_smtp_password']);
            $smtp_mail_limit = absint($_POST['smtp_mail_limit']);
            if ($smtp_mail_limit == "") {
                $smtp_mail_limit = 10000;
            }
            $smtp_from_name = sanitize_text_field($_POST['weu_smtp_name']);
            $smtp_from_mail = sanitize_email($_POST['weu_smtp_mail']);
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET `smtp_host` = %s, `smtp_smtpsecure`= %s, `smtp_port`= %s, `smtp_username`= %s, `smtp_password`= %s, `smtp_mail_limit`= %s, `smtp_from_name`= %s, `smtp_from_email`= %s WHERE `conf_id` = " . $smtp_id . ";", $smtp_host, $smtp_security, $smtp_port, $smtp_username, $smtp_password, $smtp_mail_limit, $smtp_from_name, $smtp_from_mail));
            echo '<div id="message" class="updated notice is-dismissible"><p>SMTP Configuration updated successfully!</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        $weu_temp      = get_option('weu_smtp_data_options');
        $temp_port     = isset($weu_temp['smtp_port']) ? sanitize_text_field($weu_temp['smtp_port']) : '';
        $temp_host     = isset($weu_temp['smtp_host']) ? sanitize_text_field($weu_temp['smtp_host']) : '';
        $temp_username = isset($weu_temp['smtp_username']) ? sanitize_text_field($weu_temp['smtp_username']) : '';
        $temp_password = isset($weu_temp['smtp_password']) ? sanitize_text_field($weu_temp['smtp_password']) : '';
?>
        <div id="centeredmenu">
            <div class="wrap">
                <h2>WP Email Users - SMTP Configuration</h2>
            </div>
            <ul class="w3-navbar w3-black">
                <li class="current"><a href="javascript:void(0)" onclick="openCity('London')">Configure SMTP</a></li>
                <li><a href="javascript:void(0)" onclick="openCity('Paris')">List of SMTP</a></li>
            </ul>
        </div>
<?php
        echo '<div id="London" class="w3-container city">';

        if (isset($_POST['submit_edit'])) {

            if (!isset($_POST['edit_smtp_nonce']) || !wp_verify_nonce($_POST['edit_smtp_nonce'], 'edit_smtp')) {
                print 'Sorry, Please refresh page and try again.';
                exit;
            } else {

                global $current_user, $wpdb, $wp_roles;
                $edit_conf_id = sanitize_text_field($_POST['submit_edit']);
                $table_name   = $wpdb->prefix . 'weu_smtp_conf';
                $myrows       = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name where conf_id=%s", $edit_conf_id));
                foreach ($myrows as $user) {

                    echo '<form action="#" method="post" name="wau_smtp_form" onsubmit="return validation_smtp()" class="wau_form"><table id="" class="form-table" >
                    <tbody>';

                    echo '<input type="hidden" name="weu_smtp_edit" value="' . $edit_conf_id . '" >';

                    echo '<tr>
                    <th>From Name <font color="red">*</font></th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_name" value="' . $user->smtp_from_name . '" class="wau_boxlen" id="weu_smtp_name" autocomplete="off" ></td>
                    </tr>';

                    echo '<tr>
                    <th>From Email <font color="red">*</font></th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_mail" onblur="myFunction2()" value="' . $user->smtp_from_email . '" class="wau_boxlen" id="weu_smtp_mail" autocomplete="off" ></td></tr>';

                    echo '<tr>
                    <th>SMTP Host <font color="red">*</font></th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_host" value="' . $user->smtp_host . '" class="wau_boxlen" id="wau_from_name" ></td></tr>';
                    echo '<tr><th>Type of Encryption : <font color="red">*</font> &nbsp;</th>
                    <td style="width: 170px"><input type="radio" name="weu_smtp_smtpsecure" value="" ';
                    if ($user->smtp_smtpsecure == '')
                        echo 'checked';
                    echo '> None &nbsp;</td>
                    <td style="width: 170px"><input type="radio" name="weu_smtp_smtpsecure" value="ssl" ';
                    if ($user->smtp_smtpsecure == 'ssl')
                        echo 'checked';
                    echo '> SSL </td>
                    <td style="width: 170px"><input type="radio" name="weu_smtp_smtpsecure" value="tls" ';
                    if ($user->smtp_smtpsecure == 'tls')
                        echo 'checked';
                    echo '> TLS </td></tr>';

                    echo '<tr>
                    <th>SMTP Port <font color="red">*</font></th> <td colspan="2"><input type="number" name="weu_smtp_port" value="' . $user->smtp_port . '" class="wau_boxlen" id="wau_port" ></td></tr>';
                    echo '<tr><th>SMTP Username</th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_username" value="' . $user->smtp_username . '" class="wau_boxlen" id="wau_uname" ></td></tr>';
                    echo '<tr><th>SMTP Password</th> <td colspan="2"><input type="password" style="width: 100%;" name="weu_smtp_password" value="' . $user->smtp_password . '" class="wau_boxlen" id="wau_pass" ></td></tr>';
                    echo '<tr><th>SMTP Mail Limit</th> <td colspan="2"><input type="text" style="width: 100%;" name="smtp_mail_limit" value="' . $user->smtp_mail_limit . '" class="wau_boxlen" id="wau_limit" ></td></tr></tbody></table>';
                    echo '<div><input type="submit" value="Update Configuration" name="weu_smtp_update" class="button button-hero button-primary"></div></form>';
                }
            }
        } else {

            echo '<form action="#" method="post" id="wau_smtp_form" name="wau_smtp_form" class="wau_form" onsubmit="return validation_smtp()" ><table id="" class="form-table" ><tbody>';

            $smtp_status_option = get_option('weu_smtp_data_options');
            $temp_status        = $smtp_status_option['smtp_status'];

            echo '<input type="hidden" value="' . $temp_status . '" name="stat_smtp" class="wau_boxlen" id="wau_smtp_status12" >';

            echo '<tr id="smtp_enable6">
            <th>From Name <font color="red">*</font></th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_name" class="wau_boxlen" id="weu_smtp_name" onfocus="focus_function()" autocomplete="off" required ></td></tr>';

            echo '<tr id="smtp_enable7">
            <th>From Email <font color="red">*</font></th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_mail" class="wau_boxlen" onblur="myFunction2()" id="weu_smtp_mail" autocomplete="off" required ></td></tr>';

            echo '<tr id="smtp_enable">
            <th>SMTP Host <font color="red">*</font></th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_host" class="wau_boxlen" id="weu_smtp_host" autocomplete="off" required ></td></tr>';

            echo '<tr id="smtp_enable1">
            <th>Type of Encryption : <font color="red">*</font> &nbsp;</th>
            <td style="width: 170px"><input type="radio" name="weu_smtp_smtpsecure" value="" checked> None &nbsp;</td>
            <td style="width: 170px"><input type="radio" name="weu_smtp_smtpsecure" value="ssl" > SSL </td>
            <td style="width: 170px"><input type="radio" name="weu_smtp_smtpsecure" value="tls" > TLS </td></tr>';

            echo '<tr id="smtp_enable2">
            <th>SMTP Port <font color="red">*</font></th> <td colspan="2"><input type="number" name="weu_smtp_port" class="wau_boxlen" id="wau_port" autocomplete="off" required ></td></tr>
            <tr id="smtp_enable3">
            <th>SMTP Username <font color="red">*</font></th> <td colspan="2"><input type="text" style="width: 100%;" name="weu_smtp_username" class="wau_boxlen" id="wau_uname" autocomplete="off" required ></td>
            </tr>
            <tr id="smtp_enable4">
            <th>SMTP Password <font color="red">*</font></th> <td colspan="2"><input type="password" style="width: 100%;" name="weu_smtp_password" class="wau_boxlen" id="wau_pass" autocomplete="off" required ></td>
            </tr>
            <tr id="smtp_enable5">
            <th>SMTP Mail Limit</th> <td colspan="2"><input type="text" style="width: 100%;" name="smtp_mail_limit" class="wau_boxlen" id="wau_limit" onkeypress="return event.charCode >= 48 && event.charCode <= 57" min="1" autocomplete="off" ><p><i style="color:#ff0000;">* </i>Leave blank if you don\'t want to set limit. <small> Default Limit will set to 10000.</small></p></td>
            </tr></tbody></table>';

            echo '<div><input type="submit" value="Save Configuration" name="weu_smtp_insert" class="button button-hero button-primary" ></div></form>';

            echo '</div>';
        }

        global $current_user, $wpdb, $wp_roles;
        $table_name = $wpdb->prefix . 'weu_smtp_conf';
        $count      = $wpdb->get_var("SELECT COUNT(conf_id) FROM " . $table_name);
        if ($count >= 1) {
            $myrows = $wpdb->get_results("SELECT * FROM $table_name ORDER BY `smtp_priority`=0, `smtp_priority`");

            echo '<div id="Paris" class="w3-container city" style="display:none">';

            echo '<div class="wrap"><h2> List Of All SMTP Configurations</h2></div><p>Here you will find the list of all SMTP Configurations which are set through this (WP Email Users) plugin.</p><p>Please <strong>Drag & Drop</strong> the row to change the priority. </p>';
            //echo '<div class="tableDemo"><div id="debugArea" style="float: right">&nbsp;</div>';
            echo '<table id="table-2" class="display alluser_datatable smtp_table" cellspacing="0" width="100%">
            <thead>
            <tr style="text-align:left">
            <th>Sr. No.</th>
            <th>From Name<font color="red">*</font></th>
            <th>From Email<font color="red">*</font></th>
            <th>Host<font color="red">*</font></th>
            <th>Security<font color="red">*</font></th>
            <th>Port<font color="red">*</font></th>
            <th>Username<font color="red">*</font></th>
            <th>Password<font color="red">*</font></th>
            <th>Mails Sent/Daily Limit</th>
            <th>Enable/Disable</th>
            <th>Edit</th>
            <th>Delete</th>
            </tr>
            </thead>
            <tbody>';
            $count = 0;
            foreach ($myrows as $user) {
                $count++;
                $security = $user->smtp_smtpsecure;
                if ($security == "") {
                    $status_result = "None";
                } else {
                    $status_result = $security;
                }
                if ($user->smtp_status == 1) {
                    echo '<tr style="text-align:left" id="' . $user->conf_id . '">';
                } else {
                    echo '<tr style="text-align:left" id="">';
                }
                if ($user->smtp_status == 1) {
                    echo '<td><span id="getDetail">' . $user->smtp_priority . '</span></td>';
                } else {
                    echo '<td><span id="getDetail">--</span></td>';
                }
                echo '<td><span id="getDetail">' . $user->smtp_from_name . '</span></td>';
                echo '<td><span id="getDetail">' . $user->smtp_from_email . '</span></td>';
                echo '<td><span id="getDetail">' . $user->smtp_host . '</span></td>';
                echo '<td><span >' . $status_result . '</span></td>';
                echo '<td><span >' . $user->smtp_port . '</span></td>';
                echo '<td><span >' . $user->smtp_username . '</span></td>';
                echo '<td><span >*****</span></td>';
                echo '<td><span >' . $user->smtp_mails_used . "/" . $user->smtp_mail_limit . '</span></td>';
                if ($user->smtp_status == 0) {
                    echo '<td><form action="" method="post">';
                    wp_nonce_field("enable_smtp", "enable_smtp_nonce");
                    echo '<div class="tooltip"><button class="edit-smtp-enable" name="' . $user->conf_id . '" type="submit" value="" ><span class="dashicons dashicons-admin-settings"></span></button><span class="tooltiptext">Enable it!</span></div><input type="hidden" name="submit-enable" value="' . $user->conf_id . '" ></form></td>';
                } else {
                    echo '<td><form action="" method="post">';
                    wp_nonce_field("enable_smtp", "enable_smtp_nonce");

                    echo '<div class="tooltip"><button class="edit-smtp-disable" name="' . $user->conf_id . '" type="submit" value="" ><span class="dashicons dashicons-admin-settings"></span></button><span class="tooltiptext">Disable it!</span></div><input type="hidden" name="submit-enable" value="' . $user->conf_id . '" ></form></td>';
                }

                echo '<td><form action="" method="post">';
                wp_nonce_field("edit_smtp", "edit_smtp_nonce");
                echo '<button class="edit-smtp-conf" name="' . $user->conf_id . '" type="submit" value="" ><span class="dashicons dashicons-edit"></span></button><input type="hidden" name="submit_edit" value="' . $user->conf_id . '" ></form></td>';

                echo '<td><form action="" method="post">';
                wp_nonce_field("delete_smtp", "delete_smtp_nonce");
                echo '<button class="delete-smtp-conf" name="' . $user->conf_id . '" type="submit" value="" ><span class="dashicons dashicons-trash"></span></button><input type="hidden" name="submit-delete-smtp" value="' . $user->conf_id . '" ></form></td>';
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        }
    }
    if (isset($_POST['submit-delete-smtp'])) {
        if (!isset($_POST['delete_smtp_nonce'])) {
            print 'Sorry, Please refresh page and try again.';
            exit;
        } else {
            $table_name        = $wpdb->prefix . 'weu_smtp_conf';
            $local_weu_sent_id = sanitize_text_field($_POST['submit-delete-smtp']);
            $mylink            = $wpdb->delete($table_name, array(
                'conf_id' => $local_weu_sent_id
            ), array(
                '%d'
            ));
            if (null !== $mylink) {
                echo '<div id="message" class="updated notice is-dismissible"><p>SMTP has been successfully deleted.</p></div>';
            } else {
                echo '<div class="error"><p>SMTP is not deleted.</p></div>';
            }
        }
    }
    if (isset($_POST['submit-enable'])) {
        if (!isset($_POST['enable_smtp_nonce'])) {
            print 'Sorry, Please refresh page and try again.';
            exit;
        } else {
            global $wpdb;
            $table_name     = $wpdb->prefix . 'weu_smtp_conf';
            $smtp_status_id = sanitize_text_field($_POST['submit-enable']);
            $p_count        = $smtp_status_id + 1;
            $i              = 1;
            $myrows2        = $wpdb->get_results($wpdb->prepare("SELECT smtp_status FROM $table_name where conf_id =%s", $smtp_status_id));
            foreach ($myrows2 as $user2) {
                $status = $user2->smtp_status;
            }

            if ($status == "1") {
                $count          = $wpdb->get_var("SELECT COUNT(conf_id) FROM " . $table_name);
                $rows_affected  = $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET smtp_status = %s, smtp_priority = %s where conf_id = %s;", 0, 0, $smtp_status_id));
                $rows_affected1 = $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET smtp_priority = %s where conf_id = %s and smtp_status=%s;", $i, $smtp_status_id, 1));
                $i++;
            } elseif ($status == "0") {
                $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(conf_id) FROM " . $table_name . " where smtp_status=%s", 1));

                if ($count > 0) {
                    $priority = $count + 1;
                    echo "string1" . $priority;
                } else {
                    $priority = 1;
                    echo "string2" . $priority;
                }

                $rows_affected2 = $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET smtp_status = %s, smtp_priority=%s where conf_id = %s;", 1, $priority, $smtp_status_id));
            }

            echo '<div id="message" class="updated notice is-dismissible"><p>SMTP status has been updated successfully.</p></div>';
        }
    }
}
