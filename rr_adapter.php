<?php
require 'vendor/autoload.php';

use Anecka\retsrabbit\RetsRabbitClient as Client;

class rr_adapter {
    private $access_token;
    private $base_template;
    private $total_records;
    private $perpage = 10;

    function __construct() {
        $this->base_template = get_option('rr-templates');
        $this->access_token = get_transient('retsrabbit-access-code');

        if($this->access_token == "") {
            $client = new Client();
            $client_id = get_option('rr-client-id');
            $client_secret = get_option('rr-client-secret');

            $this->_getAPIEndpoint($client);

            if($client_id && $client_secret) {
                $client->getAccessCode($client_id, $client_secret);
                $this->access_token = $client->access_token;

                set_transient('retsrabbit-access-code', $this->access_token, 1 * HOUR_IN_SECONDS);
            }
        }

    }

    public function metadata($server_hash = "") {
        $client = new Client($this->access_token);
        $this->_getAPIEndpoint($client);

        if($this->access_token) {
            $servers = $client->getServers();

            if(sizeof($servers) > 0) {
                if($server_hash == "")
                    $server_hash = $servers[0]->server_hash;

                $metadata = get_transient("rr-metadata");

                if($metadata == null) {
                    $metadata = $client->getServerMetadata($server_hash);

                    set_transient("rr-metadata", $metadata);
                }
                return $metadata;
            }
        }


        return array();
    }

    public function get_servers() {
        $client = new Client($this->access_token, true);
        $this->_getAPIEndpoint($client);

        $servers = $client->getServers();
        return $servers;
    }

    public function get_listing($mls_id, $server_num, $num_photos = -1, $cache = false, $cache_duration = 4) {
        $client = new Client($this->access_token, true);
        $this->_getAPIEndpoint($client);

        $servers = $client->getServers();
        if(isset($servers[$server_num])) {
            $server_hash = $servers[$server_num]['server_hash']; //dangerous, need to adjust this
        } else {
            $server_hash = $servers[0]['server_hash'];
        }
        $response = null;
        $query_key = "";

        if($cache) {
            $key = "$server_hash:$mls_id";
            $query_key = hash('sha256', serialize($key));

            $response = get_transient($query_key);
        }

        if($response == null) {
            $response = $client->getListing($server_hash, $mls_id);

            if($cache) {
                set_transient($query_key, $response, $cache_duration * HOUR_IN_SECONDS);
            }
        }

        $variable = array();

        if($response != null) {
            $variable['mls_id'] = $response['mls_id'];
            foreach($response['fields'] as $key => $field) {
                $variable[$key] = $field;
            }

            $variable = $this->_photo_array_builder($response, $variable, $num_photos);
        }

        return $variable;
    }

    public function run_search($params, $limit, $num_photos, $server_num, $orderby = "", $sort_order = "", $sort_option = "", $cache = false, $cache_duration = 4) {
        $client = new Client($this->access_token, true);
        $this->_getAPIEndpoint($client);

        $servers = $client->getServers();
        if(isset($servers[$server_num])) {
            $server_hash = $servers[$server_num]['server_hash']; //dangerous, need to adjust this
        } else {
            $server_hash = $servers[0]['server_hash'];
        }

        $response = null;
        $query_key = "";

        $query = $this->_prepare_query($params, $limit, $orderby, $sort_order, $sort_option);

        if($cache) {
            $query_key = hash('sha256', $server_hash.serialize($query));

            $response = get_transient($query_key);
        }

        if($response == null) {
            $response = $client->getSearchListings($server_hash, $query);

            if($cache) {
                set_transient($query_key, $response, $cache_duration * HOUR_IN_SECONDS);
            }
        }

        $variables = array();

        $this->total_records = $response['total_records'];

        $variables['total_records'] = $this->total_records;
        $variables['params'] = $params;
        $results = [];

        if (is_array($response['results']) || is_object($response['results'])){
            foreach($response['results'] as $listing) {
                $fields = array();
                $variable = array();

                $variable['mls_id'] = $listing['mls_id'];
                foreach($listing['fields'] as $key => $field) {
                    $variable[$key] = $field;
                }

               $variable = $this->_photo_array_builder($listing, $variable, $num_photos);
                $results[] = $variable;
            }
        }

        $variables['results'] = $results;

        return $variables;
    }

    public function parse($results, $template, $paginate = true, $perpage = 10) {
        ob_start();
        include($this->base_template.$template);
        $returned = ob_get_contents();
        ob_end_clean();

        if($paginate)
            $returned .= $this->_pagination(intVal( intVal($this->total_records) / intval($perpage) ));

        return $returned;
    }

    public function parse_single($result, $template) {
        ob_start();
        include($this->base_template.$template);
        $returned = ob_get_contents();
        ob_end_clean();
        return $returned;
    }

    public function generate_form($template, $query_params = null) {
        $form_data = null;

        if($query_params) {
            $form_data = array(
                'orderby' => $query_params['orderby'],
                'sort_order' => $query_params['sort_order'],
                'limit'     => $query_params['limit'],
                'sort_option' => $query_params['sort_option']
            );

            foreach($query_params['params'] as $param => $value) {
                if(strpos($value, "|") !== FALSE) {
                    $form_data[$param] = explode("|", $value);
                } else {
                    $form_data[$param] = $value;
                }
            }
        }

        ob_start();
        include($this->base_template.$template);
        $returned = ob_get_contents();
        ob_end_clean();
        return $returned;
    }

    private function _prepare_query($parameters, $limit, $orderby, $sort_order, $sort_option) {
        $query = array();
        if($parameters != null) {
            foreach($parameters as $key => $value) {
                //keys/fields to ignore and not send the RR API, also don't send any blank values
                $value = str_replace("\'", "'", $value);
                if($value != '' && strtolower($key) != 'limit' && strtolower($key) != 'num_photos'
                    && strtolower($key) != 'search_segment' && strtolower($key) != 'short_code') {
                    $query[$key] = $value;
                }
            }
        }

        $query['limit'] = $limit;
        $query['offset'] = $this->_get_offset($limit);
        $query['orderby'] = $orderby;
        $query['sort_order'] = $sort_order;
        $query['sort_option'] = $sort_option;

        return $query;
    }

    private function _get_offset($perpage) {
        $current_page = get_query_var('paged');
        return ($current_page == 0 ? 0 : ($perpage * ($current_page - 1)));
    }

    private function _photo_array_builder($listing, $variable, $num_photos = -1){
        $x = 0;
        $photos = array();

        foreach($listing['photos'] as $photo) {
            $photo['photo_count'] = $x;
            //if num_photos == -1 add photo
            if($num_photos == -1) $photos[] = $photo;

            //if num_photos greater than -1 and x is less than num_photos add photo
            if($num_photos > -1 && $x < $num_photos) $photos[] = $photo;

            $x++;
        }

        if(count($photos) > 0)
            $variable['has_photos'] = true;
        else
            $variable['has_photos'] = false;

        $variable['photos'] = $photos;

        return $variable;
    }

    private function _getAPIEndpoint(&$client) {
        $api_endpoint = get_option('rr-api-endpoint');
        if($api_endpoint != '' && $api_endpoint != null) {
            if(substr($api_endpoint, -1) != '/')
                $api_endpoint .= '/';

            $client->setEndpoint($api_endpoint);
        }
    }

    private function _pagination($total_pages) {

        $current_page = 1;

        if ( $total_pages > 1 )  {
            // Get the current page
            if ( !$current_page = get_query_var('paged') )
                 $current_page = 1;
            // Structure of “format” depends on whether we’re using pretty permalinks
           $permalinks = get_option('permalink_structure');
           $format = empty( $permalinks ) ? '&paged=%#%' : 'page/%#%/';
           $big = 999999999;

           return paginate_links(array(
                 'base' =>  str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ), //get_pagenum_link(1) . '%#%',
                 'format' =>  $format,
                 'current' => $current_page,
                 'total' => $total_pages,
                 'mid_size' => 2,
                 'type' => 'list'
           ));
        }
    }
}

?>
