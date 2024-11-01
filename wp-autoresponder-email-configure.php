<?php
if (!defined('ABSPATH'))
   exit;
if (!function_exists('tsweu_email_auto_config')) {
   function tsweu_email_auto_config()
   {

      if (isset($_POST['save_ar_config']) && sanitize_text_field($_POST['save_ar_config']) == 'Save Changes') {
         if (!isset($_POST['autoresponder_nonce_field']) || !wp_verify_nonce($_POST['autoresponder_nonce_field'], 'autoresponder_action')) {
            print 'Sorry, Please refresh page and try again.';
            exit;
         } else {
            $weu_smtp_status                = array();
            $smtp_status                    = sanitize_text_field($_POST['weu_smtp_status']);
            $weu_smtp_status["smtp_status"] = isset($smtp_status) ? $smtp_status : '';
            update_option("weu_smtp_data_options", $weu_smtp_status);
            $weu_track_mail                 = sanitize_text_field($_POST['weu_track_mail']);
            $weu_track_mail_setting         = isset($weu_track_mail) ? $weu_track_mail : '';
            update_option("weu_track_mail", $weu_track_mail_setting);
            $weu_temp_config                                  = array();
            $new_user                                         = sanitize_text_field($_POST['rbtn_new_user_register']);
            $new_post                                         = sanitize_text_field($_POST['rbtn_new_post_publish']);
            $new_comment                                      = sanitize_text_field($_POST['rbtn_new_comment_publish']);
            $new_password                                     = sanitize_text_field($_POST['rbtn_password_reset']);
            $new_role                                         = sanitize_text_field($_POST['rbtn_user_role_changed']);
            if (isset($_POST['rbtn_show_buddypress_group'])) {
               $new_bp_group                                  = sanitize_text_field($_POST['rbtn_show_buddypress_group']);
            }
            $unsubscribe_url                                  = sanitize_text_field($_POST['rbtn_user_unsubscribe_url']);
            $subscribe_url                                    = sanitize_text_field($_POST['rbtn_user_subscribe_url']);
            $unsubscribe_success                              = sanitize_text_field($_POST['rbtn_user_unsubscribe_success']);
            $unsubscribe_failure                              = sanitize_text_field($_POST['rbtn_user_unsubscribe_failure']);
            $weu_temp_config["weu_arconfig_user_reg"]         = isset($new_user) ? $new_user : '';
            $weu_temp_config["weu_arconfig_post_pub"]         = isset($new_post) ? $new_post : '';
            $weu_temp_config["weu_arconfig_comment_pub"]      = isset($new_comment) ? $new_comment : '';
            $weu_temp_config["weu_arconfig_pass_reset"]       = isset($new_password) ? $new_password : '';
            $weu_temp_config["weu_arconfig_role_change"]      = isset($new_role) ? $new_role : '';
            $weu_temp_config["weu_arconfig_buddypress"]       = isset($new_bp_group) ? $new_bp_group : '';
            $weu_temp_config["rbtn_user_unsubscribe_url"]     = isset($unsubscribe_url) ? $unsubscribe_url : '';
            $weu_temp_config["rbtn_user_subscribe_url"]       = isset($subscribe_url) ? $subscribe_url : '';
            $weu_temp_config["rbtn_user_unsubscribe_success"] = isset($unsubscribe_success) ? $unsubscribe_success : '';
            $weu_temp_config["rbtn_user_unsubscribe_failure"] = isset($unsubscribe_failure) ? $unsubscribe_failure : '';
            update_option("weu_ar_config_options", $weu_temp_config);

            if (isset($_POST['cron_time'])) {
               $cron_arry = array(
                  'cron_job' => sanitize_text_field($_POST['cron_job']),
                  'cron_time' => sanitize_text_field($_POST['cron_time'])
               );
               update_option("cron_job_status", $cron_arry);
            }

            if (sanitize_text_field($_POST['cron_job']) == 'no') {
               wp_clear_scheduled_hook('WP_mail_event');
            }

            if (sanitize_text_field($_POST['cron_time']) == 'hourly') {
               wp_clear_scheduled_hook('WP_mail_event');
               if (!wp_next_scheduled('WP_mail_event')) {
                  wp_schedule_event(time(), 'hourly', 'WP_mail_event');
               }
            } elseif (sanitize_text_field($_POST['cron_time']) == 'daily') {
               wp_clear_scheduled_hook('WP_mail_event');
               if (!wp_next_scheduled('WP_mail_event')) {
                  wp_schedule_event(time(), 'daily', 'WP_mail_event');
               }
            } elseif (sanitize_text_field($_POST['cron_time']) == 'twicedaily') {
               wp_clear_scheduled_hook('WP_mail_event');
               if (!wp_next_scheduled('WP_mail_event')) {
                  wp_schedule_event(time(), 'twicedaily', 'WP_mail_event');
               }
            } else {
               wp_clear_scheduled_hook('WP_mail_event');
            }
            $cron_job_delete = isset($_POST['cron_job_delete']) ? sanitize_text_field($_POST['cron_job_delete']) : "";
            if (isset($cron_job_delete)) {
               if ($cron_job_delete == 'yes') {
                  delete_option("cron_all_data");
                  delete_option("cron_mail");
                  delete_option("cron_mail_send");
               }
            }
            echo '<div id="message" class="updated notice is-dismissible"><p>Changes successfully saved.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
         }
      }
      $weu_arconf_buff          = array();
      $weu_arconf_buff          = get_option('weu_ar_config_options');
      $rbtn_unsubscribe_success = sanitize_text_field($weu_arconf_buff['rbtn_user_unsubscribe_success']);
      $rbtn_unsubscribe_url     = sanitize_text_field($weu_arconf_buff['rbtn_user_unsubscribe_url']);
      $rbtn_subscribe_url       = isset($weu_arconf_buff['rbtn_user_subscribe_url']) ? $weu_arconf_buff['rbtn_user_subscribe_url'] : "";
      $rbtn_unsubscribe_failure = sanitize_text_field($weu_arconf_buff['rbtn_user_unsubscribe_failure']);
      $unubscribe_url           = isset($rbtn_unsubscribe_url) ? $rbtn_unsubscribe_url : '';
      $subscribe_url            = isset($rbtn_subscribe_url) ? $rbtn_subscribe_url : '';
      $unubscribe_success       = isset($rbtn_unsubscribe_success) ? $rbtn_unsubscribe_success : '';
      $unubscribe_failure       = isset($rbtn_unsubscribe_failure) ? $rbtn_unsubscribe_failure : '';
?>
      <div class='wrap'>
         <h2> WP Email Users Settings</h2>
      </div>
      </br>
      <form name="autoresponder" class="wau_form" method="POST" action="#" onsubmit="return validation_responder()">
         <?php
         wp_nonce_field('autoresponder_action', 'autoresponder_nonce_field');
         ?>
         <table id="" class="form-table">
            <tbody>
               <tr>
                  <th>
                     <h3 style="margin: 0;">Autoresponder Email Manage</h3>
                  </th>
                  <td style="width: 224px">
                  </td>
               </tr>
               <tr>
                  <th>New User Register</th>
                  <td style="width: 40%;">
                     <input type="radio" name="rbtn_new_user_register" class="new_user_register" value="on" <?php
                                                                                                            if (isset($weu_arconf_buff['weu_arconfig_user_reg']) && $weu_arconf_buff['weu_arconfig_user_reg'] == 'on')
                                                                                                               echo 'checked';
                                                                                                            ?>> On &nbsp;
                  </td>
                  <td style="width: 25%;">
                     <input type="radio" name="rbtn_new_user_register" class="new_user_register" value="off" <?php
                                                                                                               if (isset($weu_arconf_buff['weu_arconfig_user_reg']) && $weu_arconf_buff['weu_arconfig_user_reg'] == 'off')
                                                                                                                  echo 'checked';
                                                                                                               ?>> Off
                  </td>
               </tr>
               <tr id="drop_hide">
                  <th>
                     New Post Publish
                  </th>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_new_post_publish" class="user_role_email" value="on" <?php
                                                                                                         if (isset($weu_arconf_buff['weu_arconfig_post_pub']) && $weu_arconf_buff['weu_arconfig_post_pub'] == 'on')
                                                                                                            echo 'checked';
                                                                                                         ?>> On &nbsp;
                  </td>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_new_post_publish" class="user_role_email" value="off" <?php
                                                                                                            if (isset($weu_arconf_buff['weu_arconfig_post_pub']) && $weu_arconf_buff['weu_arconfig_post_pub'] == 'off')
                                                                                                               echo 'checked';
                                                                                                            ?>> Off
                  </td>
               </tr>
               <tr id="drop_hide">
                  <th>
                     New Comment Post
                  </th>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_new_comment_publish" class="user_role_email" value="on" <?php
                                                                                                            if (isset($weu_arconf_buff['weu_arconfig_comment_pub']) && $weu_arconf_buff['weu_arconfig_comment_pub'] == 'on')
                                                                                                               echo 'checked';
                                                                                                            ?>> On &nbsp;
                  </td>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_new_comment_publish" class="user_role_email" value="off" <?php
                                                                                                               if (isset($weu_arconf_buff['weu_arconfig_comment_pub']) && $weu_arconf_buff['weu_arconfig_comment_pub'] == 'off')
                                                                                                                  echo 'checked';
                                                                                                               ?>> Off
                  </td>
               </tr>
               <tr>
                  <th>Password Reset</th>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_password_reset" class="user_role_email" value="on" <?php
                                                                                                         if (isset($weu_arconf_buff['weu_arconfig_pass_reset']) && $weu_arconf_buff['weu_arconfig_pass_reset'] == 'on')
                                                                                                            echo 'checked';
                                                                                                         ?>> On &nbsp;
                  </td>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_password_reset" class="user_role_email" value="off" <?php
                                                                                                         if (isset($weu_arconf_buff['weu_arconfig_pass_reset']) && $weu_arconf_buff['weu_arconfig_pass_reset'] == 'off')
                                                                                                            echo 'checked';
                                                                                                         ?>> Off
                  </td>
               </tr>
               <tr>
                  <th>User Role Changed</th>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_user_role_changed" class="user_role_email" value="on" <?php
                                                                                                            if (isset($weu_arconf_buff['weu_arconfig_role_change']) && $weu_arconf_buff['weu_arconfig_role_change'] == 'on')
                                                                                                               echo 'checked';
                                                                                                            ?>> On &nbsp;
                  </td>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_user_role_changed" class="user_role_email" value="off" <?php
                                                                                                            if (isset($weu_arconf_buff['weu_arconfig_role_change']) && $weu_arconf_buff['weu_arconfig_role_change'] == 'off')
                                                                                                               echo 'checked';
                                                                                                            ?>> Off
                  </td>
               </tr>
               <tr>
                  <th>
                     <h3 style="margin: 0;">Unsubscribe Setting</h3>
                  </th>
                  <td style="width: 224px">
                  </td>
               </tr>
               <tr>
                  <th>Enter Unsubscribe confirmation page URL (without http/https)</th>
                  <td style="width: 450px">
                     <input type="text" style="width: 100%;" name="rbtn_user_unsubscribe_url" class="user_role_email" placeholder="Enter unsubscribe confirmation page url" value="<?php
                                                                                                                                                                                    echo $unubscribe_url;
                                                                                                                                                                                    ?>"> &nbsp;
                  </td>
               </tr>
               <tr>
                  <th>Unsubscribe Success Text</th>
                  <td style="width: 450px">
                     <input type="text" style="width: 100%;" name="rbtn_user_unsubscribe_success" class="user_role_email" placeholder="Enter unsubscribe success text" value="<?php
                                                                                                                                                                              echo $unubscribe_success;
                                                                                                                                                                              ?>"> &nbsp;
                  </td>
               </tr>
               <tr>
                  <th>Unsubscribe Failure Text</th>
                  <td style="width: 450px">
                     <input type="text" style="width: 100%;" name="rbtn_user_unsubscribe_failure" class="user_role_email" placeholder="Enter unsubscribe failure text" value="<?php
                                                                                                                                                                              echo $unubscribe_failure;
                                                                                                                                                                              ?>"> &nbsp;
                  </td>
               </tr>
               <tr>
                  <th>Enter Subscribe confirmation page URL (without http/https)</th>
                  <td style="width: 450px">
                     <input type="text" style="width: 100%;" name="rbtn_user_subscribe_url" class="user_role_email" placeholder="Enter subscribe confirmation page url" value="<?php
                                                                                                                                                                                 echo $subscribe_url;
                                                                                                                                                                                 ?>"> &nbsp;
                  </td>
               </tr>
               <tr id="drop_hide">
                  <?php
                  global $wpdb;
                  $table_name = $wpdb->prefix . 'bp_groups';
                  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) { // WordPress database error: [Table 'assignment1.wp_bp_groups' doesn't exist] Start

                     $myrows     = $wpdb->get_results("SELECT * FROM $table_name");
                     if (count($myrows) > 0) {
                  ?>
               <tr>
                  <th>
                     <h3 style="margin: 0;">Buddy Press Group Setting</h3>
                  </th>
                  <td style="width: 224px">
                  </td>
               </tr>
               <tr>
                  <th>Show Buddy Press Group</th>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_show_buddypress_group" classcron_job_delete="user_role_email" value="yes" <?php
                                                                                                                              if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'yes')
                                                                                                                                 echo 'checked';
                                                                                                                              ?>> Yes &nbsp;
                  </td>
                  <td style="width: 224px">
                     <input type="radio" name="rbtn_show_buddypress_group" class="user_role_email" value="no" <?php
                                                                                                               if (isset($weu_arconf_buff['weu_arconfig_buddypress']) && $weu_arconf_buff['weu_arconfig_buddypress'] == 'no')
                                                                                                                  echo 'checked';
                                                                                                               ?>> No
                  </td>
            <?php
                     }
                  }
            ?>
               </tr>
               <tr>
                  <th>Use Cron Jobs</th>
                  <?php
                  $cron_job_data = get_option('cron_job_status');
                  ?>
                  <td style="width: 170px">
                     <input type="radio" id="cronselect" name="cron_job" value="yes" <?php
                                                                                       if ($cron_job_data['cron_job'] == 'yes')
                                                                                          echo 'checked';
                                                                                       ?>> Yes &nbsp;
                  </td>
                  <td style="width: 170px">
                     <input type="radio" id="cronselect" name="cron_job" value="no" <?php
                                                                                    if ($cron_job_data['cron_job'] == 'no')
                                                                                       echo 'checked';
                                                                                    ?>> No
                  </td>
               </tr>
               <tr>
                  <th>Select cron execution schedule</th>
                  <td>
                     <select name="cron_time" id="cron_time">
                        <option value="hourly" <?php
                                                if ($cron_job_data['cron_time'] == 'hourly')
                                                   echo 'selected';
                                                ?>>Hourly
                        </option>
                        <option value="twicedaily" <?php
                                                   if ($cron_job_data['cron_time'] == 'twicedaily')
                                                      echo 'selected';
                                                   ?>>Twice Daily
                        </option>
                        <option value="daily" <?php
                                                if ($cron_job_data['cron_time'] == 'daily')
                                                   echo 'selected';
                                                ?>>Daily
                        </option>
                     </select>
                  </td>
                  <td style="width: 170px">
                     <input type="checkbox" name="cron_job_delete" value="yes"> Delete Old Cron Setting
                  </td>
               </tr>
               <tr>
                  <th>
                     <h3 style="margin: 0;">SMTP Setting</h3>
                  </th>
                  <td style="width: 224px"></td>
               </tr>
               <tr>
                  <th>Use SMTP</th>
                  <?php
                  $smtp_status_option = get_option('weu_smtp_data_options');
                  $temp_status        = $smtp_status_option['smtp_status'];
                  ?>
                  <td style="width: 170px">
                     <input type="radio" name="weu_smtp_status" value="yes" <?php
                                                                              if ($temp_status == 'yes')
                                                                                 echo 'checked';
                                                                              ?>> Yes &nbsp;
                  </td>
                  <td style="width: 170px">
                     <input type="radio" name="weu_smtp_status" value="no" <?php
                                                                           if ($temp_status == 'no')
                                                                              echo 'checked';
                                                                           ?>> No
                  </td>
               </tr>
               <!-- Tracking mail setting -->
               <tr>
                  <th>
                     Track Mail Image
                  </th>
                  <?php
                  $track_mail_setting = get_option('weu_track_mail');
                  ?>
                  <td style="width: 170px">
                     <input type="radio" name="weu_track_mail" value="yes" <?php
                                                                           if ($track_mail_setting == 'yes')
                                                                              echo 'checked';
                                                                           ?>> Yes &nbsp;
                  </td>
                  <td style="width: 170px">
                     <input type="radio" name="weu_track_mail" value="no" class="red-tooltip" data-toggle="tooltip" data-placement="bottom" title="After disabling the Tracking image you'll not able to get seen count for the any email.!" <?php
                                                                                                                                                                                                                                             if ($track_mail_setting == 'no')
                                                                                                                                                                                                                                                echo 'checked';
                                                                                                                                                                                                                                             ?>> No
                  </td>
               </tr>

               <!-- End traking mail setting -->

               <!-- enable plugin for other roles start -->
               <?php
               $user          = wp_get_current_user();
               $get_user_role = $user->roles;

               foreach ($get_user_role as $display_get_user_role) {
                  if ($display_get_user_role == 'administrator') {
               ?>
                     <tr>
                        <th>
                           <h3>Enable Plugin For Other Roles</h3>
                        </th>
                     </tr>
                     <tr>
                        <th>Select Multiple User Roles</th>
                        <td>
                           <?php
                           global $wp_roles;
                           $roles     = $wp_roles->get_names();
                           $get_roles = get_option('enable_plugin_for_other_roles');
                           ?>
                           <select multiple="multiple" data-live-search="true" class="selectpicker" name="select_multiple_roles[]">
                              <?php
                              foreach ($roles as $role) {
                              ?>
                                 <option value="<?php
                                                echo strtolower($role);
                                                ?>" <?php
                                                      if (in_array(strtolower($role), $get_roles) || strtolower($role) == 'administrator') {
                                                         echo "selected";
                                                      }
                                                      ?>><?php
                                                   echo strtolower($role);
                                                   ?></option>
                              <?php
                              }
                              ?>
                           </select>
                     <?php
                  }
               }
                     ?>
                        </td>
                     </tr>
                     <!-- enable plugin for other roles end -->
            </tbody>
         </table>
         <input type="submit" value="Save Changes" class="button button-hero button-primary" name="save_ar_config">
      </form>
<?php
      // enable plugin for other roles start
      if (isset($_POST['select_multiple_roles'])) {
         $add_custom_role = sanitize_text_field($_POST['select_multiple_roles']);
      }
      if (isset($_POST['save_ar_config'])) {
         update_option('enable_plugin_for_other_roles', $add_custom_role);
      }
      // enable plugin for other roles end
   }
}
