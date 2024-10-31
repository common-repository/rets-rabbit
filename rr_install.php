<?php
global $rr_version;
$rr_version = "1.2";

function rr_install() {
    global $wpdb, $rr_version;
    $table_name = $wpdb->prefix."retsrabb_search";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      search_key varchar(200) DEFAULT '' NOT NULL,
      search_value text NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function retsrabbit_update_check() {
    global $rr_version;
    if(get_site_option('rr_db_version') == "" || get_site_option('rr_db_version') != $rr_version) {
        rr_install();
    }
}

add_action( 'plugins_loaded', 'retsrabbit_update_check' );
?>
