<?php
class retsrabbit_shortcodes {

    static function listings($atts, $content = null, $tag = null) {

        global $wp_query;
        $template = (isset($atts['template']) ? $atts['template'] : 'listings.php');
        $limit = intVal(isset($atts['limit']) ? $atts['limit'] : get_option('rr-results-per-page', 10));
        $num_photos = intVal(isset($atts['num_photos']) ? $atts['num_photos'] : -1);
        $paginate = (isset($atts['paginate']) ? $atts['paginate'] === 'true' : true);
        $orderby = (isset($atts['orderby']) ? $atts['orderby'] : "");
        $sort_order = (isset($atts['sort_order']) ? $atts['sort_order'] : "");
        $cache = (isset($atts['cache']) ? $atts['cache'] : false);
        $cache_duration = (isset($atts['cache_duration']) ? $atts['cache_duration'] : 4);
        $server_num = intval((isset($atts['server_num']) ? $atts['server_num'] : 0));
        $params = json_decode($atts['params'], true);
        
        foreach($params as $key => $param) {
            if(strpos($key, ":escapedash") > -1) {
                unset($params[$key]);
                $key = str_replace(":escapedash", "", $key);

                if (strpos($param, "|") > -1) {
                    $values = explode("|", $param);
                    $param_array = [];
                    foreach ($values as $value) {
                        $param_array[] = "'" . $value . "'";
                    }
                    $params[$key] = implode("|", $param_array);
                } else {
                    $params[$key] = "'" . $param . "'";
                }
            }
        }


        $rr_adapter = new rr_adapter();
        $results = $rr_adapter->run_search($params, $limit, $num_photos, $server_num, $orderby, $sort_order, $cache, $cache_duration);
        //$results = $response->results;
        return $rr_adapter->parse($results, $template, $paginate, $limit);
    }

    static function listing($atts, $content = null, $tag) {

        global $wp_query;
        $template = (isset($atts['template']) ? $atts['template'] : 'detail.php');
        $num_photos = (isset($atts['num_photos']) ? $atts['num_photos'] : -1);
        $cache = (isset($atts['cache']) ? $atts['cache'] : false);
        $cache_duration = (isset($atts['cache_duration']) ? $atts['cache_duration'] : 4);
        $server_num = intval((isset($atts['server_num']) ? $atts['server_num'] : 0));


        if(!$mls_id = get_query_var('mls_id'))
            $mls_id = $atts['mls_id'];

        $rr_adapter = new rr_adapter();
        $result = $rr_adapter->get_listing($mls_id, $server_num, $num_photos, $cache, $cache_duration);
        return $rr_adapter->parse_single($result, $template);
    }

    static function breadcrumb_search ($atts, $content = null, $tag) {
        global $wp_query;
        $city = get_query_var('city', '');
        $subdivision = get_query_var('subdivision', '');

        $params = [];

        if($city !== ''){
            $city = urldecode($city);
            $params['LIST_39'] = $city;
        }

        if($subdivision !== ''){
            $subdivision = urldecode($subdivision);
            if(strpos($subdivision, '-') !== false){
                $subdivision = "\'".$subdivision."\'";
            }
            $params['LIST_131:startswith'] = $subdivision;
        }

        $atts['params'] = json_encode($params);

        return self::listings($atts, $content, $tag);
    }

    static function search_form($atts, $content = null, $tag = null) {
        global $wp_query, $wpdb;
        //global $wpdb;
        $template = (isset($atts['template']) ? $atts['template'] : 'search-form.php');
        $rr_adapter = new rr_adapter();

        $key = (isset($_GET['key']) ? $_GET['key'] : '');
        $query_params = "";
        if($key != "") {
            $query_params = $wpdb->get_var("SELECT search_value FROM {$wpdb->prefix}retsrabb_search WHERE search_key = '$key'");
            $query_params = unserialize($query_params);
        }

        return $rr_adapter->generate_form($template, $query_params);
    }

    static function search_results($atts, $content = null, $tag = null) {
        global $wp_query, $wpdb;
        $template = (isset($atts['template']) ? $atts['template'] : 'results.php');
        $limit = (isset($atts['limit']) ? $atts['limit'] : get_option('rr-results-per-page', 10));
        $num_photos = (isset($atts['num_photos']) ? $atts['num_photos'] : 1);
        $paginate = (isset($atts['paginate']) ? $atts['paginate'] === 'true' : true);
        $cache = (isset($atts['cache']) ? $atts['cache'] : false);
        $cache_duration = (isset($atts['cache_duration']) ? $atts['cache_duration'] : 4);
        $server_num = intval((isset($atts['server_num']) ? $atts['server_num'] : 0));

        $key = (isset($_GET['key']) ? $_GET['key'] : '');
        $query_params = "";
        if($key != "") {
            $query_params = $wpdb->get_var("SELECT search_value FROM {$wpdb->prefix}retsrabb_search WHERE search_key = '$key'");
            $query_params = unserialize($query_params);
        }

        $params = $query_params['params'];
        $orderby = $query_params['orderby'];
        $sort_order = $query_params['sort_order'];
        $limit = $query_params['limit'];
        $sort_option = $query_params['sort_option'];

        if($params != null && sizeof($params) > 0) {
            $rr_adapter = new rr_adapter();
            $results = $rr_adapter->run_search($params, $limit, $num_photos, $server_num, $orderby, $sort_order, $sort_option, $cache, $cache_duration);

            return $rr_adapter->parse($results, $template, $paginate, $limit);
        } else {
            return "";
        }
    }
}

add_shortcode("retsrabbit-listings", array("retsrabbit_shortcodes", "listings"));

add_shortcode('retsrabbit-listing', array("retsrabbit_shortcodes", "listing"));

add_shortcode("retsrabbit-search-form", array("retsrabbit_shortcodes", "search_form"));

add_shortcode("retsrabbit-search-results", array("retsrabbit_shortcodes", "search_results"));

add_shortcode("retsrabbit-breadcrumb-search", array("retsrabbit_shortcodes", "breadcrumb_search"));
?>
