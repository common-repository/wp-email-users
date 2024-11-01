<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('wp_ajax_weu_smtp_priority_action_1', 'tsweu_smtp_priority_function');

if (!function_exists('tsweu_smtp_priority_function')) {
	function tsweu_smtp_priority_function()
	{
		$nonce_data = (isset($_POST['nonce_ajax'])) ? sanitize_text_field($_POST['nonce_ajax']) : "";
		check_ajax_referer('wp-email-user-script-nonce', $nonce_data);
		global $wpdb;

		$table_name = $wpdb->prefix . 'weu_smtp_conf';

		$data1 = sanitize_text_field($_POST['data_raw']);

		$blast = explode('table-2[]=', $data1);

		$count = 0;

		foreach ($blast as $key => $dataBlast) {

			if ($key != 0) {

				$blast1 = str_replace('&', '', $dataBlast);

				$myrows = $wpdb->query($wpdb->prepare("UPDATE `" . $table_name . "` SET smtp_priority = %s where conf_id =%s", $count, $blast1));
			}

			$count++;
		}

		wp_die();
	}
}
