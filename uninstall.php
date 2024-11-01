<?php

/**
 * * removed data on plugin uninstall
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
} else {
    delete_option('weu_smtp_data_options');
    delete_option('weu_ar_config_options');
    delete_option('weu_new_user_register');
    delete_option('weu_new_post_publish');
    delete_option('weu_password_reset');
    delete_option('weu_new_comment_post');
    delete_option('weu_user_role_changed');
    global $wpdb, $table_prefix;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}email_user");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}weu_smtp_conf");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}weu_sent_email");
}
