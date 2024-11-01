<?php
if (!defined('ABSPATH'))
    exit;
require_once('wp-email-functions.php');
if (!function_exists('tsweu_email_setting')) {
    function tsweu_email_setting()
    {
        echo "<div class='wrap'>";
        echo "<h2>Autoresponder Email Settings </h2>";
        echo "</div>";
        echo '</br>';
        echo '<form name="autoresponder" class="wau_form" method="POST" action="#" onsubmit="return validation_auto()">';
        echo '<table id="" class="form-table" >';
        echo '<tbody>';
        global $wpdb;
        $table_name = $wpdb->prefix . 'email_user';
        $myrows     = $wpdb->get_results($wpdb->prepare("SELECT id, template_key, template_value FROM $table_name WHERE status =%s", 'template'));
        echo '<tr>';
        echo '<th>Send Email For <font color="red">*</font></th>';
        echo '<td colspan="3"><select name="user_email[]" class="wau_boxlen mail_for" id="email_role" >';
        echo '<option value="" >-- Select --</option>';
        echo '<option value="1-New User Register"> New User Register </option>';
        echo '<option value="2-New Post Publish" > New Post Publish </option>';
        echo '<option value="3-New Comment Post" > New Comment Post </option>';
        echo '<option value="4-Password Reset" > Password Reset </option>';
        echo '<option value="5-User Role Changed" > User Role Changed </option>';
        echo '</select></td>';
        echo '</tr>';
        $wau_users    = get_users();
        $ar_conf_page = admin_url("admin.php?page=weu_email_auto_config");
        echo '<tr id="drop_hide">';
        echo "<th>Send Email To <font color=red>*</font>&nbsp;</th>";
        echo '<td style="width: 224px"><input type="radio" name="rbtn_respond" id="user_role_email" onclick="radioFunction_responder()" value="user" checked > User &nbsp;</td>';
        echo '<td style="width: 224px"><input type="radio" name="rbtn_respond" id="r_role_email" onclick="radioFunction_responder()" value="role"> Role </td>';
        echo "</tr>";
        echo '<tr class="wau_user_toggle"><th></th><td colspan="3" class="weu_display">';
        echo '<table id="User_autoresponder_table" class="display alluser_datatable" cellspacing="0" width="100%">';
        tsweu_autoresponder_selected_user();
        echo '</table>';
        echo '</td></tr>';
        foreach ($wau_users as $user) {
            echo '<input type="hidden" name="' . $user->ID . '" value="' . $user->user_email . '">';
        }
        echo '<tr id="wau_user_responder" style="display:none">';
        echo '<th>Select Roles <font color="red">*</font></th>';
        echo '<td colspan="3"><select name="user_role[]" multiple class="wau_boxlen" id="wau_role" >';
        tsweu_autoresponder_selected_user_role();
        echo '</select></td>';
        echo '</tr>';
        echo '<tr>';
        $weu_arconf_buff = array();
        $weu_arconf_buff = get_option('weu_sample_template');
        $template_1      = $weu_arconf_buff['sample_template_1'];
        $template_2      = $weu_arconf_buff['sample_template_2'];
        $template_3      = $weu_arconf_buff['new_user_register'];
        $template_4      = $weu_arconf_buff['new_comment'];
        $template_5      = $weu_arconf_buff['new_post'];
        $template_6      = $weu_arconf_buff['new_password'];
        $template_7      = $weu_arconf_buff['user_role_changed'];
        echo '</tr>';
        echo '<tr id="old_template">';
        echo '<th >Template <font color="red">*</font></th>
    <td colspan="3"><select autocomplete="off" id="wau_template_single" name="mail_template[]" onchange="jsFunction()" class="wau-template-selector" style="width:100%; height: 50px ">

    <option selected="selected" disabled>-Select Template-</option>
    <option value="' . $template_1 . '" data-id="-1" > Default Template - 1 </option>
    <option value="' . $template_2 . '" data-id="0"> Default Template - 2 </option>
    <option selected="selected" disabled>-Select Template-</option>';
        echo '<option value=" ' . $template_3 . '" data-id="A"> New User Register </option>';
        echo '<option value=" ' . $template_5 . '" data-id="B"> New Post Publish </option>';
        echo '<option value=" ' . $template_4 . '" data-id="C"> New Comment Post </option>';
        echo '<option value=" ' . $template_6 . '" data-id="D"> Password Reset </option>';
        echo '<option value=" ' . $template_7 . '" data-id="E"> User Role Changed </option>';
        echo '<option disabled>------ User Created Template------ </option>';
        for ($i = 0; $i < count($myrows); $i++) {
?>
            <option value="<?php
                            echo htmlspecialchars($myrows[$i]->template_value, ENT_QUOTES, 'UTF-8');
                            ?>" data-id="<?php
                                            echo htmlspecialchars($myrows[$i]->id, ENT_QUOTES, 'UTF-8');
                                            ?>"><?php
                                                echo $myrows[$i]->template_key;
                                                ?> </option>

<?php
        }
        echo '</tr>';
        echo '</select><input id="title" name="template_id" size="30" type="text" class="criteria_rate " value=""  readonly="readonly" hidden/></td>';
        $mail_content = "";
        echo '</tr>';
        echo '<tr id="temp_subject">';
        echo '<th>Template Subject <font color="red">*</font></th>';
        echo '<td colspan="3"><input type="text" name="wau_temp_sub" class="wau_boxlen" id="weu_temp_sub" placeholder="Your Template Subject here"></td>';
        echo '</tr>';
        echo '<th scope="row" valign="top"><label for="weu_show_area">Message</label></th>';
        echo '<td colspan="3">';
        echo '<div id="msg" class="wau_boxlen" name="weu_show_area">';
        wp_editor($mail_content, "weu_show_area", array(
            'wpautop' => false,
            'media_buttons' => true
        ));
        echo '</div><p>Please make sure you turned autoresponder emails on <a href=' . esc_url($ar_conf_page) . '>here</a></p>';
        echo "Please use shortcode placeholder as below.</br>";
        echo '<p id="nickname"><b> [[user-nickname]] : </b>use this placeholder to display user nickname</p> 

    <p><b> [[username]] : </b>use this placeholder to display user first name  </p>

    <p id="lname"><b> [[last-name]] :  </b>use this placeholder to display user last name </p>

    <p><b> [[site-title]] : </b>use this placeholder to display your site title</p>

    <p> <b> [[password]] : </b>use for reset password </b></p>
    <p><p id="dname"><b> [[display-name]] : </b>use this placeholder for display name</p>

    <p><b> [[user-email]] : </b>use this placeholder to display user email</p>
<p><p id="slink"><b> [[subscribe-link]] : </b>use this placeholder to display subscribe link in email. Please make sure you configure subscribe page link <a href=' . esc_url($ar_conf_page) . '>here</a> before using this.</p>
    <p><b> [[unsubscribe-link]] : </b>use this placeholder to display unsubscribe link in email. Please make sure you configure unsubscribe page link <a href=' . esc_url($ar_conf_page) . '>here</a> before using this.</p>

    ';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '<input type="submit" value="Save" style="margin-left: 30%;" class="button button-hero button-primary" name="save_template">  ';
        echo '</form>';
    }
}

if (!function_exists('tsweu_autoresponder_selected_user_role')) {
    function tsweu_autoresponder_selected_user_role()
    {
        global $wpdb;
        $html        = '';
        $filen_id = (isset($_POST['filename_id'])) ? $_POST['filename_id'] : "";
        $filename_id = substr($filen_id, 2);
        $table_name  = $wpdb->prefix . 'weu_user_notification';
        $myrows      = $wpdb->get_results($wpdb->prepare("SELECT email_value FROM " . $table_name . " WHERE email_for = %s", $filename_id));
        $array       = array();
        foreach ($myrows as $key => $value) {
            $unserialize_array = unserialize($value->email_value);
        }
        foreach ($unserialize_array as $key => $value) {
            if ($value == 'Administrator') {
                $flag1 = 1;
                $flag6 = 6;
            }
            if ($value == 'Subscriber') {
                $flag2 = 2;
                $flag6 = 6;
            }
            if ($value == 'Contributor') {
                $flag3 = 3;
                $flag6 = 6;
            }
            if ($value == 'Author') {
                $flag4 = 4;
                $flag6 = 6;
            }
            if ($value == 'Editor') {
                $flag5 = 5;
                $flag6 = 6;
            }
        }
        $f6 = (isset($flag6)) ? intval($flag6) : "";
        if ($f6 == 6) {
            $html .= '<option value="" disabled >-- Select Role --</option>';
        } else {
            $html .= '<option value="" selected disabled >-- Select Role --</option>';
        }
        $f1 = (isset($flag1)) ? intval($flag1) : "";
        if ($f1 == 1) {
            $html .= '<option value="Administrator" selected="selected"> Administrator </option>';
        } else {
            $html .= '<option value="Administrator"> Administrator </option>';
        }
        $f2 = (isset($flag2)) ? intval($flag2) : "";
        if ($f2 == 2) {
            $html .= '<option value="Subscriber" selected="selected"> Subscriber </option>';
        } else {
            $html .= '<option value="Subscriber"> Subscriber </option>';
        }
        $f3 = (isset($flag3)) ? intval($flag3) : "";
        if ($f3 == 3) {
            $html .= '<option value="Contributor" selected="selected"> Contributor </option>';
        } else {
            $html .= '<option value="Contributor"> Contributor </option>';
        }
        $f4 = (isset($flag4)) ? intval($flag4) : "";
        if ($f4 == 4) {
            $html .= '<option value="Author" selected="selected"> Author </option>';
        } else {
            $html .= '<option value="Author"> Author </option>';
        }
        $f5 = (isset($flag5)) ? intval($flag5) : "";
        if ($f5 == 5) {
            $html .= '<option value="Editor" selected="selected"> Editor </option>';
        } else {
            $html .= '<option value="Editor"> Editor </option>';
        }
        echo $html;
    }
}

if (!function_exists('tsweu_autoresponder_selected_user')) {
    function tsweu_autoresponder_selected_user()
    {
        global $wpdb;
        $wau_users   = get_users();
        $html        = '';
        $filen_id = (isset($_POST['filename_id'])) ? $_POST['filename_id'] : "";
        if (isset($filen_id)) {
            $filename_id = substr($filen_id, 2);
            $table_name  = $wpdb->prefix . 'weu_user_notification';
            $myrows      = $wpdb->get_results($wpdb->prepare("SELECT email_value FROM " . $table_name . " WHERE email_for = %s", $filename_id));
            $array       = array();
            foreach ($myrows as $key => $value) {
                $unserialize_array[] = unserialize($value->email_value);
            }
        }
        $html = '<thead>';
        $html .= '<tr style="text-align:left"> <th style="text-align:center" ><input name="select_all" value="1" id="example-responder" class="select-all" type="checkbox"></th>';
        $html .= '<th>Display name <font color="red">*</font></th>';
        $html .= '<th>Email <font color="red">*</font></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        foreach ($wau_users as $user) {
            $html .= '<tr style="text-align:left">';
            $flag = 0;
            if (!empty($unserialize_array)) {
                foreach ($unserialize_array as $key => $value) {
                    if ($value == $user->user_email) {
                        $flag = 1;
                    }
                }
            }
            if ($flag == 1) {
                $html .= '<td style="text-align:center"><input type="checkbox" name="ea_user_name[]" value="' . $user->ID . '" class="select-all_auto" checked></td>';
            } else {
                $html .= '<td style="text-align:center"><input type="checkbox" name="ea_user_name[]" value="' . $user->ID . '" class="select-all_auto"></td>';
            }
            $html .= '<td><span id="getDetail">' . $user->display_name . '</span></td>';
            $html .= '<td><span >' . $user->user_email . '</span></td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        echo $html;
    }
    $rbtn_respond = (isset($_POST['rbtn_respond'])) ?  sanitize_text_field($_POST['rbtn_respond']) : "";
    if (isset($rbtn_respond) && $rbtn_respond != '') {
        $wau_to = array();
        if (isset($rbtn_respond) && $rbtn_respond == 'user') {
            $user_arr[] = sanitize_text_field($_POST['ea_user_name']);
            if (isset($user_arr)) {
                for ($j = 0; $j < count($user_arr); $j++) {
                    $user = $user_arr[$j];
                    array_push($wau_to, sanitize_text_field($_POST[$user]));
                }
                $get_role_value = serialize($wau_to);
            }
        } elseif (isset($rbtn_respond) && $rbtn_respond == 'role') {
            $user_roles[] = sanitize_text_field($_POST['user_role']);
            for ($k = 0; $k < count($user_roles); $k++) {
                $group_role    = array(
                    'role' => $user_roles[$k]
                );
                $wau_grp_users = get_users($group_role);
                for ($m = 0; $m < count($wau_grp_users); $m++) {
                    array_push($wau_to, $wau_grp_users[$m]->data->user_email);
                }
            }
            $get_role_value = serialize($user_roles);
        }
        $user_mail        = sanitize_text_field($_POST['user_email']);
        $user_email_count = isset($user_mail) ? $user_mail : '';
        for ($k = 0; $k < count($user_email_count); $k++) {
            $email_by_val = array(
                'role' => $user_mail[$k]
            );
        }
        $event_val     = $email_by_val['role'];
        $event_val     = explode("-", $event_val);
        $template_user = sanitize_text_field($_POST['mail_template']);
        if (isset($template_user)) {
            for ($k = 0; $k < count(array($template_user)); $k++) {
                $temp_url = array(
                    'role' => $template_user[$k]
                );
            }
        }
        tsweu_setup_activation_data();
        global $wpdb;
        $table_name  = $wpdb->prefix . 'weu_user_notification';
        $myrows      = $wpdb->get_results("SELECT template_id FROM $table_name");
        $all_temp_id = array();
        for ($i = 0; $i < count($myrows); $i++) {
            array_push($all_temp_id, $myrows[$i]->template_id);
        }
        $temp_sub = sanitize_text_field($_POST['wau_temp_sub']);
        switch ($event_val[0]) {
            case 1:
                if (isset($temp_sub) && $temp_sub != '') {
                    update_option('weu_new_user_register', $temp_sub);
                }
                break;
            case 2:
                if (isset($temp_sub) && $temp_sub != '') {
                    update_option('weu_new_post_publish', $temp_sub);
                }
                break;
            case 3:
                if (isset($temp_sub) && $temp_sub != '') {
                    update_option('weu_new_comment_post', $temp_sub);
                }
                break;
            case 4:
                if (isset($temp_sub) && $temp_sub != '') {
                    update_option('weu_password_reset', $temp_sub);
                }
                break;
            case 5:
                if (isset($temp_sub) && $temp_sub != '') {
                    update_option('weu_user_role_changed', $temp_sub);
                }
                break;
        }
        $show_area   = sanitize_text_field($_POST['weu_show_area']);
        $respond_btn = sanitize_text_field($_POST['rbtn_respond']);
        if (in_array($event_val[0], $all_temp_id)) {
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET `template_value` = %s, `email_for` = %s, `email_by` = %s, `email_value` = %s WHERE `template_id` = " . $event_val[0] . ";", $show_area, $event_val[1], $respond_btn, $get_role_value));
            echo '<div id="message" class="updated notice is-dismissible"><p>setting updated successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        } else {
            $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`template_id`,`template_value`,`email_for`,`email_by`,`email_value`) VALUES (%d,%s,%s,%s,%s)", $event_val[0], $show_area, $event_val[1], $respond_btn, $get_role_value));
            echo '<div id="message" class="updated notice is-dismissible"><p>setting saved successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
    }
}
