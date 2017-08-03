<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once 'core/classes/cron.php';

//callback function
function cart_product_activate_plugin()
{

    global $wpdb;
    $activation_date = date('Y-m-d');
    update_option('cart-product-feed-installation-date', $activation_date);

    $table_name = $wpdb->prefix . "cp_feeds";
    $sql = "
		        CREATE TABLE $table_name (
		      `id` bigint(20) NOT NULL AUTO_INCREMENT,
		      `category` varchar(250) NOT NULL,
		      `remote_category` varchar(1000) NOT NULL,
		      `filename` varchar(250) NOT NULL,
		      `url` varchar(500) NOT NULL,
		      `type` varchar(50) NOT NULL DEFAULT 'google',
		      `own_overrides` int(10),
		      `feed_overrides` text,
		      `product_count` int,
		      `feed_errors` text,
		      `feed_title` varchar(250),
		      `feed_type` INT(10) DEFAULT '0',
		      `product_details` BLOB,
		      `miinto_country_code` VARCHAR(5) DEFAULT NULL,
		PRIMARY KEY (`id`)
    )";
    //)" ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $table_name = $wpdb->prefix . "cpf_custom_products";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "
      CREATE TABLE $table_name (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `product_title` longtext,
                  `category_name` varchar(255) DEFAULT NULL,
                  `product_type` varchar(255) DEFAULT NULL,
                  `product_attributes` text DEFAULT NULL,
                  `product_variation_ids` varchar(255) DEFAULT NULL,
                  `remote_category` longtext,
                  `category` int(11) DEFAULT NULL,
                  `product_id` int(11) DEFAULT NULL,
                  `own_overides` int(11) DEFAULT NULL,
                  `feed_overides` blob,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `cpf_custom` (`category`,`remote_category`(255),`product_id`)
            )";
        $wpdb->query($sql);
    } else {
        $drop_sql = "
        ALTER TABLE  $table_name 
        DROP  INDEX remote_cat 
        ";
        $wpdb->query($drop_sql);

        $alter_engine = "ALTER TABLE  $table_name Engine InnoDB";
        $wpdb->query($alter_engine);
    }

    $table_name = $wpdb->prefix . "cpf_custom_products";
    $sql = "
        CREATE TABLE IF NOT EXISTS $table_name 
        (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `product_title` longtext,
              `category_name` varchar(255) DEFAULT NULL,
              `product_type` varchar(255) DEFAULT NULL,
              `product_attributes` text DEFAULT NULL,
              `product_variation_ids` varchar(255) DEFAULT NULL,
              `remote_category` longtext,
              `category` int(11) DEFAULT NULL,
              `product_id` int(11) DEFAULT NULL,
              `own_overides` int(11) DEFAULT NULL,
              `feed_overides` blob,
              PRIMARY KEY (`id`),
              UNIQUE KEY `cpf_custom` (`category`,`remote_category`(255),`product_id`),
              )";
}


function cart_product_deactivate_plugin()
{

    $next_refresh = wp_next_scheduled('update_cartfeeds_hook');
    if ($next_refresh)
        wp_unschedule_event($next_refresh, 'update_cartfeeds_hook');

}