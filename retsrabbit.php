<?php
/*
Plugin Name: Rets Rabbit
Plugin URI: http://retsrabbit.com/wordpress
Description: Plugin to integrate the real estate cloud service, Rets Rabbit, with Wordpress.
Version: 1.0.8
Author: Patrick Pohler
Author URI: http://www.anecka.com
*/

function add_retsrabbit_query_vars( $vars ){
    $vars = array_merge($vars, array("rr_page", "rr_limit", "mls_id"));
    return $vars;
}

function rr_fixpaginationampersand($link) {
    return str_replace('#038;', '&', $link);
}

add_filter('paginate_links', 'rr_fixpaginationampersand');

add_filter( 'query_vars', 'add_retsrabbit_query_vars' );

include_once(dirname(__FILE__).'/rr_install.php');
require_once(dirname(__FILE__).'/rr_adapter.php');
require_once(dirname(__FILE__).'/rr_shortcodes.php');
require_once(dirname(__FILE__).'/rr_actions.php');
include_once(dirname(__FILE__).'/settings.php');

register_activation_hook(__FILE__, 'retsrabbit_update_check' );
register_activation_hook( __FILE__, 'rr_install' );
?>
