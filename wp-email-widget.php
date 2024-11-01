<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class tsweu_subscribe_widget_register extends WP_Widget
{

	function __construct()
	{

		$widget_ops = array('classname' => 'widget_text weu-widget', 'description' => __('WP Email Users Subscribe Form'), 'wp-email-users');

		parent::__construct('wp-email-users', __('WP Email Users Subscribe', 'wp-email-users'), $widget_ops);
	}

	function widget($args, $instance)
	{

		extract($args, EXTR_SKIP);

		$weu_ftitle = apply_filters('widget_title', empty($instance['weu_ftitle']) ? '' : $instance['weu_ftitle'], $instance, $this->id_base);

		$weu_fdesc	= $instance['weu_fdesc'];

		$weu_fname	= $instance['weu_fname'];

		$weu_fgroup	= $instance['weu_fgroup'];

		// Validation messages

		$weu_ajaxurl = admin_url('admin-ajax.php');

		wp_register_script('weu-widget-notices', plugins_url('js/subscribe-validate.js', __FILE__), '', '', true);

		wp_enqueue_script('weu-widget-notices');

		$weu_select_params = array(

			'weu_email_notice'      => 'Please enter email address.',

			'weu_incorrect_email'	=> 'Please provide a valid email address.',

			'weu_success_message'   => 'Subscribed successfully.',

			'weu_email_exists'     	=> 'Email Address already exists.',

			'weu_error'     		=> 'Oops.. Unexpected error occurred.',

			'weu_invalid_email' 	=> 'Invalid email address.',

			'weu_try_later' 		=> 'Please try after some time.',

			'weu_ajax_url'			=> $weu_ajaxurl

		);

		wp_localize_script('weu-widget-notices', 'weu_widget_notices', $weu_select_params);

		echo $args['before_widget'];

		if (!empty($weu_ftitle)) {

			echo $args['before_title'] . $weu_ftitle . $args['after_title'];
		}

		// display widget method

		$url = home_url();

?>

		<div>

			<form class="weu_widget_form">

				<?php if ($weu_fdesc <> "") { ?>

					<div class="weu_caption"><?php echo $weu_fdesc; ?></div>

				<?php } ?>

				<div class="weu_msg"><span id="weu_msg"></span></div>

				<?php

				if ($weu_fname == "YES") { ?>

					<div class="weu_lablebox"><?php _e('Name', 'wp-email-users'); ?></div>

					<div class="weu_textbox">

						<input class="weu_textbox_class" name="weu_txt_name" id="weu_txt_name" value="" maxlength="225" type="text">

					</div>

				<?php } ?>

				<div class="weu_lablebox"><?php _e('Email *', 'wp-email-users'); ?></div>

				<div class="weu_textbox">

					<input class="weu_textbox_class" name="weu_txt_email" id="weu_txt_email" onkeypress="if(event.keyCode==13) weu_submit_page('<?php echo $url; ?>')" value="" maxlength="225" type="text">

				</div>

				<div class="weu_button">

					<input class="weu_textbox_button" name="weu_txt_button" id="weu_txt_button" onClick="return weu_submit_page('<?php echo $url; ?>')" value="<?php _e('Subscribe', 'wp-email-users'); ?>" type="button">

				</div>

				<?php if ($weu_fname != "YES") { ?>

					<input name="weu_txt_name" id="weu_txt_name" value="" type="hidden">

				<?php } ?>

				<input name="weu_txt_group" id="weu_txt_group" value="<?php echo $weu_fgroup; ?>" type="hidden">

			</form>

		</div>

	<?php

		echo $args['after_widget'];
	}


	function update($new_instance, $old_instance)
	{

		$instance = $old_instance;

		$instance['weu_ftitle'] 	= (!empty($new_instance['weu_ftitle'])) ? strip_tags($new_instance['weu_ftitle']) : '';

		$instance['weu_fdesc'] 	= (!empty($new_instance['weu_fdesc'])) ? strip_tags($new_instance['weu_fdesc']) : '';

		$instance['weu_fname'] 	= (!empty($new_instance['weu_fname'])) ? strip_tags($new_instance['weu_fname']) : '';

		$instance['weu_fgroup'] 	= (!empty($new_instance['weu_fgroup'])) ? strip_tags($new_instance['weu_fgroup']) : '';

		return $instance;
	}
	function form($instance)
	{

		$defaults = array(

			'weu_ftitle' => '',

			'weu_fdesc' 	=> '',

			'weu_fname' 	=> '',

			'weu_fgroup' 	=> ''

		);

		$instance 		= wp_parse_args((array) $instance, $defaults);

		$weu_ftitle 	= $instance['weu_ftitle'];

		$weu_fdesc 		= $instance['weu_fdesc'];

		$weu_fname 		= $instance['weu_fname'];

		$weu_fgroup 	= $instance['weu_fgroup'];

		$all_sublist 	= get_option('weu_subscriber_lists');

		if (empty($all_sublist)) $all_sublist = array('default');

	?>

		<p>

			<label for="<?php echo $this->get_field_id('weu_ftitle'); ?>"><?php echo __('Widget Title', 'wp-email-users'); ?></label>

			<input class="widefat" id="<?php echo $this->get_field_id('weu_ftitle'); ?>" name="<?php echo $this->get_field_name('weu_ftitle'); ?>" type="text" value="<?php echo $weu_ftitle; ?>" />

		</p>

		<p>

			<label for="<?php echo $this->get_field_id('weu_fname'); ?>"><?php echo __('Display Name Field', 'wp-email-users'); ?></label>

			<select class="widefat" id="<?php echo $this->get_field_id('weu_fname'); ?>" name="<?php echo $this->get_field_name('weu_fname'); ?>">

				<option value="YES" <?php if ($weu_fname == 'YES') {
										echo 'selected="selected"';
									} ?>>Yes</option>

				<option value="NO" <?php if ($weu_fname == 'NO') {
										echo 'selected="selected"';
									} ?>>No</option>

			</select>

		</p>

		<p>

			<label for="<?php echo $this->get_field_id('weu_fdesc'); ?>"><?php echo __('Short Description', 'wp-email-users'); ?></label>

			<input class="widefat" id="<?php echo $this->get_field_id('weu_fdesc'); ?>" name="<?php echo $this->get_field_name('weu_fdesc'); ?>" type="text" value="<?php echo $weu_fdesc; ?>" />

		</p>

		<p>

			<label for="<?php echo $this->get_field_id('weu_fgroup'); ?>"><?php echo __('Subscriber List', 'wp-email-users'); ?></label>

			<select class="widefat" id="<?php echo $this->get_field_id('weu_fgroup'); ?>" name="<?php echo $this->get_field_name('weu_fgroup'); ?>">

				<?php foreach ($all_sublist as $s_list) { ?>

					<option value="<?php echo $s_list; ?>" <?php if ($weu_fgroup == $s_list) {
																echo 'selected="selected"';
															} ?>> <?php echo $s_list; ?> </option>

				<?php } ?>

			</select>

		</p>

<?php

	}
}

if (!function_exists('tsweu_subscribe_widget_register_call')) {
	function tsweu_subscribe_widget_register_call()
	{
		register_widget('tsweu_subscribe_widget_register');
	}
}
add_action('widgets_init', 'tsweu_subscribe_widget_register_call');
