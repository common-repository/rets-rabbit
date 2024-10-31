<?php
add_action('admin_post_retsrabbit-search', 'retsrabbit_handle_search'); // If the user is logged in
add_action('admin_post_nopriv_retsrabbit-search', 'retsrabbit_handle_search'); // If the user in not logged in
add_action('updated_option', 'retsrabbit_clear_transients', 10, 3);

function retsrabbit_handle_search() {
    global $wpdb;
    $params = array();
    if(isset($_POST)) {
        //pull out the rets-specific search fields. Each
        //one will have "rets:" prepended to the field name
        foreach($_POST as $key => $value){
            if(stripos($key, 'rets:') !== false) {
                $new_key = str_replace('rets:', '', $key);

                if(is_array($value)) {
                    $val = implode("|", $value);
                } else {
                    $val = $value;//sanitize_text_field($value);
                }

                //even though we don't send blank values to the RR API, save them anyway so
                //we can populate the search form correctly on the results page
                $params[$new_key] = $val;
            }
        }

        $search_page_id = get_option('rr-search-results-page');

        $limit = ((isset($_POST['limit']) && $_POST['limit'] != '') ? $_POST['limit'] : get_option('rr-results-per-page', 10));
        $results_page = (isset($_POST['results_page']) ? $_POST['results_page'] : get_permalink($search_page_id));
        $orderby = ((isset($_POST['orderby']) && $_POST['orderby'] != '') ? $_POST['orderby'] : "");
        $sort_order = ((isset($_POST['sort_order']) && $_POST['sort_order'] != '') ? $_POST['sort_order'] : "");
        $sort_option = ((isset($_POST['sort_option']) && $_POST['sort_option'] != '') ? $_POST['sort_option'] : "");

        $data = array(
            'params'         => $params,
            'limit'          => $limit,
            'result_page'    => $results_page,
            'orderby'        => $orderby,
            'sort_order'     => $sort_order,
            'sort_option'    => $sort_option,
            'page'           => 1
        );

        $key = md5(serialize($data));

        if(sizeof($params) > 0) {
            $table_name = $wpdb->prefix . 'retsrabb_search';
            $wpdb->insert(
            	$table_name,
            	array(
            		'search_key' => "$key",
            		'search_value' => serialize($data),
            	)
            );
            //we save the search parameters and redirect to the results page
            //the results page actually hits the RR API and runs the search

            wp_redirect($results_page."?key=".$key);
            exit;
        }
    }
}

function retsrabbit_clear_transients($option, $oldvalue, $_newvalue) {
    if($option == "rr-client-id"){
        delete_transient('retsrabbit-access-code');
        delete_transient('rr-metadata');
    }
}
?>
