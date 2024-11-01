<?php

/**
 * Plugin Name: WP Email Users
 * Plugin URI:  http://www.techspawn.com
 * Description: WP Email Users send mail to individual user or group of users.
 * Version: 1.7.6
 * Author: techspawn1
 * Author URI: http://www.techspawn.com
 * License: GPL2
 */
/*  Copyright 2016-2017  Techspawn  (email : sales@techspawn.com)

This program is free software; you can redistribute it and/or modify

it under the terms of the GNU General Public License as published by

the Free Software Foundation; either version 2 of the License, or

(at your option) any later version.

This program is distributed in the hope that it will be useful,

but WITHOUT ANY WARRANTY; without even the implied warranty of

MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

GNU General Public License for more details.

You should have received a copy of the GNU General Public License

along with this program; if not, write to the Free Software

Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
 * *Make sure we don't expose any information if called directly
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I am just a plugin, not much I can do when called directly.';
    exit;
}

/**
 * * Define and includes required plugin files
 */

define('WP_EMAIL_USERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_EMAIL_USERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once('wp-autoresponder-email-settings.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once('wp-email-user-ajax.php');
require_once('wp-email-user-ajax-subscribe.php');
require_once('wp-email-user-template.php');
require_once('wp-email-user-smtp.php');
require_once('wp-selected-user-ajax.php');
require_once('wp-autoresponder-email-configure.php');
require_once('wp-email-widget.php');
require_once('wp-email-shortcode.php');
require_once('wp-smtp-priority-ajax.php');
require_once('wp-cron-function.php');
require_once('wp-email-functions.php');
require_once('wp-email-autorespond-functions.php');
require_once('wp-selected-user-ajax.php');
require_once('wp-email-user-manage-list.php');
require_once('wp-send-mail.php');
require_once('wp-send-email-user-ajax.php');

/**
 * * Loading PHPMAiler as per wordperss version
 */

if (version_compare(get_bloginfo('version'), '5.5-alpha', '<')) {
    require_once ABSPATH . WPINC . '/class-phpmailer.php';
    require_once ABSPATH . WPINC . '/class-smtp.php';
} else {
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
}

/**
 * * Enqueue script on init action
 */

add_action('init', 'tsweu_load_enqueue_scripts');
if (!function_exists('tsweu_load_enqueue_scripts')) {
    function tsweu_load_enqueue_scripts()
    {
        wp_enqueue_script('jquery');
    }
}

/**
 * * setting link in plugin admin area
 */

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tsweu_setting_link');
if (!function_exists('tsweu_setting_link')) {
    function tsweu_setting_link($links)
    {
        $mylinks = array(
            '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=weu_email_auto_config')) . '">Settings</a>'
        );
        return array_merge($links, $mylinks);
    }
}

/**
 * * Ajax calls for autoresponder selected user
 */

add_action('wp_ajax_weu_autoresponder_selected_user', 'weu_autoresponder_selected_user');
add_action('wp_ajax_weu_autoresponder_selected_user_role', 'weu_autoresponder_selected_user_role');

/**
 * * Enqueue admin scripts
 */

add_action('admin_enqueue_scripts', 'tsweu_enqueue_admin_script');
if (!function_exists('tsweu_enqueue_admin_script')) {
    function tsweu_enqueue_admin_script()
    {
        $actual_link = esc_url($_SERVER['REQUEST_URI']);
        if (strpos($actual_link, 'weu_send_email') || strpos($actual_link, 'weu-template') || strpos($actual_link, 'weu-smtp-config') || strpos($actual_link, 'weu_email_setting') || strpos($actual_link, 'weu_email_auto_config') || strpos($actual_link, 'weu-manage-list') || strpos($actual_link, 'weu_custom_role') || strpos($actual_link, 'weu_sent_emails') || strpos($actual_link, 'weu-list-editor&listname')  || strpos($actual_link, 'weu-list-editor&listname')) {

            wp_enqueue_script('wp-email-user-datatable-script', plugins_url('js/jquery.dataTables.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-sweet-alert-script', plugins_url('js/sweetalert.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-email-user-script', plugins_url('js/email-admin.js', __FILE__), array(), '1.0.0', false);
            wp_localize_script('wp-email-user-script', 'wp_email_users', array("ajaxurl" => admin_url("admin-ajax.php"), '_ajax_nonce' => wp_create_nonce('wp-email-user-script-nonce')));

            wp_enqueue_style('wp-email-user-datatable-style', plugins_url('css/jquery.dataTables.min.css', __FILE__));
            wp_enqueue_style('wp-email-user-style', plugins_url('css/style.css', __FILE__));
            wp_enqueue_style('wp-sweet-alert-style', plugins_url('css/sweetalert.css', __FILE__));

            wp_enqueue_script('popperjs', plugins_url('js/popper.min.js', __FILE__));
            wp_enqueue_style('multiselect-bootstrap_css', plugins_url('css/bootstrap.min.css', __FILE__));
            wp_enqueue_script('multiselect-bootstrap_jsselectpicker', plugins_url('js/bootstrap.bundle.js', __FILE__), array('jquery'));

            wp_enqueue_style('multiselect-css.selectpicker', plugins_url('css/bootstrap-select.min.css', __FILE__));
            wp_enqueue_script('multiselect.jsselectpicker', plugins_url('js/bootstrap-select.min.js', __FILE__), array('jquery'));
        }
    }
}

/**
 * * Setup plugin data on plugin activation
 */
register_activation_hook(__FILE__, 'tsweu_setup_activation_data');

/**
 * * Setup cron options on plugin activation
 */
register_activation_hook(__FILE__, 'tsweu_setup_cron_options');

if (!function_exists('tsweu_setup_cron_options')) {
    function tsweu_setup_cron_options()
    {
        $crondata = array(
            'cron_job' => "no",
            'cron_time' => ""
        );
        update_option("cron_job_status", $crondata);
        update_option("weu_track_mail", 'yes');
    }
}

/**
 * * Removed plugin data on plugin deactivation
 */

register_deactivation_hook(__FILE__, 'tsweu_deactivation_hook');
if (!function_exists('tsweu_deactivation_hook')) {
    function tsweu_deactivation_hook()
    {
        wp_clear_scheduled_hook('WP_mail_event');
        delete_option("cron_all_data");
        delete_option("cron_mail");
        delete_option("cron_mail_send");
    }
}

// *enable plugin for custom user start
add_option('enable_plugin_for_other_roles');

$default_role_db = get_option('enable_plugin_for_other_roles');

$get_default_user_role = array('administrator');

if ($default_role_db == NULL) {
    update_option('enable_plugin_for_other_roles', $get_default_user_role);
}

/**
 * * on plugin load check for plugin updates
 */

add_action('plugins_loaded', 'tsweu_checkfor_update');

if (!function_exists('tsweu_checkfor_update')) {
    function tsweu_checkfor_update()
    {
        global $plugin_version;
        if (get_site_option('plugin_version') != $plugin_version)
            tsweu_plugin_updates();
    }
}

/**
 * * Plugin update function
 */

if (!function_exists('tsweu_plugin_updates')) {
    function tsweu_plugin_updates()
    {
        global $wpdb, $plugin_version;
        delete_option('weu_sample_template');
        tsweu_setup_activation_data();
    }
}
