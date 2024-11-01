<?php
if (!function_exists('tsweu_template')) {
	function tsweu_template()
	{
?>
		<div id="centeredmenu">
			<?php
			echo '<h1>Template Manager form</h1>';
			?>
			<ul class="w3-navbar w3-black">
				<li class="current"><a href="javascript:void(0)" onclick="openCity('London')">Create / Update Template</a></li>
				<li><a href="javascript:void(0)" onclick="openCity('Paris')">List Templates</a></li>
			</ul>
		</div>
		<?php
		echo '<div id="London" class="w3-container city">';
		echo '<div class="wrap">';
		echo '<h2> WP Email Template Editor</h2>
	</div>';
		echo '<form name="myform" class="wau_form" method="POST" action="#" onsubmit="return validation_template_manager()">';
		echo '<table id="" class="form-table" >';
		wp_nonce_field('wp_new_template', 'wp_template_nonce');
		echo '<tbody>';
		global $wpdb;
		$table_name = $wpdb->prefix . 'email_user';
		$myrows = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'template' ");

		$weu_arconf_buff = array();

		$weu_arconf_buff = get_option('weu_sample_template');

		$template_1 = $weu_arconf_buff['sample_template_1'];

		$template_2 = $weu_arconf_buff['sample_template_2'];

		echo '<tr>';
		echo '<th>Select Option <font color="red">*</font></th><td colspan="1"><label><input type="radio" name="template_radio" value="1" id="clear_content" onclick="checktemplate()"> Create Own</label>
	<label><input type="radio" name="template_radio" checked="checked" value="2" onclick="checktemplate()">Edit Existing</label></td></tr>';
		echo '<tr id="old_template">';
		echo '<th>Template <font color="red">*</font></th><td colspan="1"><select autocomplete="off" id="wau_template_single" name="mail_template[]" onchange="jsFunction()" class="wau-template-selector" style="width:100%; height: 50px ">
	<option selected="selected" disabled>-Select Template-</option>
	<option value="' . $template_1 . '" data-id="-1" > Default Template - 1 </option>
	<option value="' . $template_2 . '" data-id="0"> Default Template - 2 </option>';

		for ($i = 0; $i < count($myrows); $i++) {
		?>
			<option value="<?php echo htmlspecialchars($myrows[$i]->template_value, ENT_QUOTES, 'UTF-8') ?>" data-id="<?php echo htmlspecialchars($myrows[$i]->id, ENT_QUOTES, 'UTF-8') ?>"><?php echo $myrows[$i]->template_key; ?> </option>
		<?php
		}
		echo '</select><input id="title" name="template_id" size="30" type="text" class="criteria_rate " value=""  readonly="readonly" hidden/></td>';
		$mail_content = "";
		echo '</tr>';
		echo '<tr id="save_as_new">';
		echo '<th>Save as New? <font color="red">*</font></th><td colspan="1"><label><input type="radio" name="new_template" value="1" checked="checked" onclick="check_new_template()">Yes</label><label><input type="radio" name="new_template" value="2" onclick="check_new_template()">No</label></td></tr>';
		echo '<tr id="temp_name_id">';
		echo '<th>Template Name <font color="red">*</font></th>';
		echo '<td><input type="text" name="wau_temp" class="wau_boxlen" id="weu_temp_name" placeholder="Your Template Name here"></td>';
		echo '</tr>';
		echo '<tr id="temp_subject">';
		echo '<th>Template Subject <font color="red">*</font></th>';
		echo '<td><input type="text" name="wau_temp_sub" class="wau_boxlen" id="weu_temp_sub" placeholder="Your Template Subject here"></td>';
		echo '</tr>';
		echo '<th scope="row" valign="top"><label for="weu_show_area">Message</label></th>';
		echo '<td colspan="2">';
		echo '<div id="msg" class="wau_boxlen" name="weu_show_area">';
		wp_editor($mail_content, "weu_show_area", array('wpautop' => false, 'media_buttons' => true));
		echo '</div></td>';
		echo '<tr>';
		echo '<th></th>';
		echo '<td>';
		$ar_conf_page = admin_url("admin.php?page=weu_email_auto_config");
		echo "Please use shortcode placeholder as below.</br>";

		echo '<p id="nickname"><b> [[user-nickname]] : </b>use this placeholder to display user nickname</p> 

	<p><b> [[first-name]] : </b>use this placeholder to display user first name  </p>

	<p id="lname"><b> [[last-name]] :  </b>use this placeholder to display user last name </p>

	<p><b> [[site-title]] : </b>use this placeholder to display your site title</p>

	<p><p id="dname"><b> [[display-name]] : </b>use this placeholder for display name</p>

	<p><b> [[user-email]] : </b>use this placeholder to display user email</p>

	<p><b> [[unsubscribe-link]] : </b>use this placeholder to display unsubscribe link in email. Please make sure you configure unsubscribe page link <a href=' . esc_url($ar_conf_page) . '>here</a> before using this.</p>

	<p><p id="slink"><b> [[subscribe-link]] : </b>use this placeholder to display subscribe link in email. Please make sure you configure subscribe page link <a href=' . esc_url($ar_conf_page) . '>here</a> before using this.</p>';
		echo '</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';

		echo '<input type="submit" value="Save Template" style="margin-left: 220px;" class="button button-hero button-primary" name="save_template">  ';
		echo '</form></div>';

		global $current_user, $wpdb, $wp_roles;
		$table_name = $wpdb->prefix . 'email_user';
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $table_name);
		if ($count >= 1) {
			$myrows = $wpdb->get_results("SELECT * FROM " . $table_name);

			echo '<div id="Paris" class="w3-container city" style="display:none">';

			echo '<div class="wrap"><h2> List Of All Templates</h2></div><p>Here you will find the list of all Templates which are set through this (WP Email Users) plugin.</p>';
			echo '<table id="example_temp" class="display alluser_datatable" cellspacing="0" width="100%">
		<thead>
			<tr style="text-align:left">
				<th>Sr. No.</th>
				<th>Template Name</th>
				<th style="text-align: center;">Preview Trmplate</th>
				<th style="text-align: center;">Delete</th>
			</tr>
		</thead>
		<tbody>';
			$count = 0;
			$index = 0;
			foreach ($myrows as $user) {
				$count++;

				++$index;
				echo '<td><span id="getDetail">' . $index . '</span></td>';
				echo '<td><span id="getDetail">' . $user->template_key . '</span></td>';
				echo '<td><form action="" method="post">';
				wp_nonce_field("template_preview", "template_preview_nonce");
				echo '<input name="template_prev" type="text" id="template_prev_id_"' . $user->id . '" value=' . $user->id . ' hidden>';
				echo '<div id="msg" class="wau_boxlen" name="wau_mailcontent" style="display:none;">';

				wp_editor($user->template_value, "weu_show_area", array('wpautop' => false, 'media_buttons' => true));

				echo '</div>';
				echo '<button name="' . $user->id . '" id="' . $user->id . '" class="template_prev_class" data-popup-open="popup-1" ><span class="dashicons dashicons-visibility" onclick="popup_template(' . $user->id . ')"></span></button></form></td>'; //pass value to popup_template() function. 

				echo '<td><form action="" method="post">';
				wp_nonce_field("delete_smtp", "delete_smtp_nonce");
				echo '<button class="delete-temp-conf" name="' . $user->id . '" type="submit" value="" ><span class="dashicons dashicons-trash"></span></button><input type="hidden" name="submit-delete-temp" value="' . $user->id . '" ></form></td>';
				echo '</tr>';
			}
			echo '</tbody></table></div>';
		?>
			<div class="popup" data-popup="popup-1">
				<div class="popup-inner">
					<h2>Here's Your Template.</h2>
					<div id='template_value'></div>
					<a class="popup-close" data-popup-close="popup-1" href="#">x</a>
				</div>
			</div>
<?php
		} else {

			echo '<div class="wrap"><h2  style="margin: 335px;">No Template available, Please create custom template.</h2></div>'; //template empty message.

		}
	}
	if (isset($_POST['submit-delete-temp'])) {
		if (!isset($_POST['delete_smtp_nonce'])) {
			print 'Sorry, Please refresh page and try again.';
			exit;
		} else {
			$table_name = $wpdb->prefix . 'email_user';
			$local_weu_sent_id = sanitize_text_field($_POST['submit-delete-temp']);
			$mylink = $wpdb->delete($table_name, array('id' => $local_weu_sent_id), array('%d'));
			if (null !== $mylink) {
				echo '<div id="message" class="updated notice is-dismissible"><p>Template has been successfully deleted.</p></div>';
			} else {
				echo '<div class="error"><p>Template is not deleted.</p></div>';
			}
		}
	}
	if (isset($_POST['save_template']) && sanitize_text_field($_POST['save_template']) == 'Save Template') {
		if (!isset($_POST['wp_template_nonce'])) {
			print 'Sorry, Please refresh page and try again.';
			exit;
		} else {
			$table_name = $wpdb->prefix . 'email_user';
			$template_option = sanitize_text_field($_POST['template_radio']);
			if ($template_option == 1) {
				tsweu_setup_activation_data();
				$temp_name = isset($_POST['wau_temp']) ? sanitize_text_field($_POST['wau_temp']) : "";
				$temp_sub = isset($_POST['wau_temp_sub']) ? sanitize_text_field($_POST['wau_temp_sub']) : "";
				$message = isset($_POST['weu_show_area']) ? $_POST['weu_show_area'] : "";
				if ($message != "") {
					$template_result = $wpdb->query($wpdb->prepare(
						"INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)",
						$temp_name,
						$message,
						'template',
						$temp_sub
					));
				} else {
					$template_result = 2;
				}
			} elseif ($template_option == 2) {
				$temp_sub = sanitize_text_field($_POST['wau_temp_sub']);
				$new_template = $_POST['new_template'];
				if ($new_template == 1) {
					$temp_name = sanitize_text_field($_POST['wau_temp']);
					$message = sanitize_text_field($_POST['weu_show_area']);
					//echo "NAME-".$temp_name."-MSG-".$message."-SUB-".$temp_sub;
					if ($message != "") {
						$template_result = $wpdb->query($wpdb->prepare(
							"INSERT INTO `" . $table_name . "`(`template_key`, `template_value`, `status`,`temp_subject`) VALUES (%s,%s,%s,%s)",
							$temp_name,
							$message,
							'template',
							$temp_sub
						));
					} else {
						$template_result = 2;
					}
				} else {
					$template_id = sanitize_text_field($_POST['template_id']);
					if ($template_id == "-1") {
						$message = sanitize_text_field($_POST['weu_show_area']);
						if ($message != "") {
							update_option('sample_template_1', $message);
							$template_result = 1;
						} else {
							$template_result = 2;
						}
					} elseif ($template_id == "0") {
						$message = sanitize_text_field($_POST['weu_show_area']);
						if ($message != "") {
							update_option('sample_template_2', $message);
							$template_result = 1;
						} else {
							$template_result = 2;
						}
					} elseif ($template_id > 0) {

						$message = sanitize_text_field($_POST['weu_show_area']);

						if ($message != "") {
							$template_result = $wpdb->query($wpdb->prepare("UPDATE `$table_name` SET `template_value` = %s, `temp_subject` = %s where `id` = %s;", $message, $temp_sub, $template_id));
						} else {
							$template_result = 2;
						}
					}
				}
			}
			tsweu_setup_activation_data();

			if ($template_result == 1) {
				echo '<div id="message1" class="updated notice is-dismissible"><p>Your Template has been Saved.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			} elseif ($template_result == 0) {

				echo '<div id="message2" class="updated notice is-dismissible error"><p> Sorry,your template has not saved.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			} elseif ($template_result == 2) {
				echo '<div id="message2" class="updated notice is-dismissible error"><p> Sorry,your template has not saved as Message field was blank.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}
		}
	}
}
/**
 * *new function for getting value in popup template 
 */

add_action('wp_ajax_tempFunction', 'tsweu_tempFunction');
if (!function_exists('tsweu_tempFunction')) {
	function tsweu_tempFunction()
	{
		$newValue = (isset($_POST['newValue'])) ? sanitize_text_field($_POST['newValue']) : '';
		global $wpdb;
		$table_name = $wpdb->prefix . 'email_user';
		$mylink = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $newValue ");
		echo $mylink[0]->template_value;
		die();
	}
}
