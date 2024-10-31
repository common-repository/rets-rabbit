<?php
add_action('admin_menu', 'retsrabbit_admin_menu');


function retsrabbit_admin_menu() {
    //add_options_page('Rets Rabbit Plugin Options', 'Rets Rabbit', 'manage_options', 'retsrabbit', 'retsrabbit_account_options');
    add_menu_page('Rets Rabbit Settings', 'Rets Rabbit', 'manage_options', 'retsrabbit', 'retsrabbit_account_options');

    add_submenu_page('retsrabbit', 'Rets Rabbit Metadata', 'Metadata', 'manage_options', 'retsrabbit-metadata', 'retsrabbit_metadata');

    add_action('admin_init', 'retsrabbit_register_settings');

    add_action('wp_loaded', 'retsrabbit_build_pills');
}

function retsrabbit_register_settings() {
    register_setting('rr-settings-group', 'rr-api-endpoint');
    register_setting('rr-settings-group', 'rr-client-id');
    register_setting('rr-settings-group', 'rr-client-secret');
    register_setting('rr-settings-group', 'rr-templates');
    register_setting('rr-settings-group', 'rr-detail-page');
    register_setting('rr-settings-group', 'rr-search-results-page');
    register_setting('rr-settings-group', 'rr-results-per-page');
}

function retsrabbit_account_options() {
    if( !current_user_can('manage_options') ) {
        wp_die( __('You do not have permissions to access this page.'));
    }

    $pages = get_pages();

    $search_page_id = get_option('rr-search-results-page');
    $detail_page_id = get_option('rr-detail-page');
?>

    <h2>Rets Rabbit Settings</h2>
    <form method="post" action="options.php">
        <?php
        settings_fields('rr-settings-group');
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Client Id</th>
                <td><input type="text" class="regular-text" name="rr-client-id" value="<?php echo get_option('rr-client-id'); ?>"></td>
            </tr>
            <tr valign="top">
                <th scope="row">Client Secret</th>
                <td><input type="text" class="regular-text" name="rr-client-secret" value="<?php echo get_option('rr-client-secret'); ?>"></td>
            </tr>
            <tr valign="top">
                <th scope="row">Custom API Endpoint (optional)</th>
                <td><input type="text" class="regular-text" name="rr-api-endpoint" value="<?php echo get_option('rr-api-endpoint'); ?>"></td>
            </tr>
            <tr valign="top">
                <th scope="row">Template Location</th>
                <td><input type="text" class="regular-text" name="rr-templates" value="<?php echo get_option('rr-templates', 'wp-content/plugins/retsrabbit/template/'); ?>"></td>
            </tr>
        </table>
        <hr>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Detail Page</th>
                <td>
                    <select name="rr-detail-page">
                        <option value="">None</option>
                        <?php foreach($pages as $page) :?>
                            <option <? if ($page->ID == $detail_page_id) : ?> selected <? endif; ?> value="<?= $page->ID ?>"><?= $page->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Search Results Page</th>
                <td>
                    <select name="rr-search-results-page">
                        <option value="">None</option>
                        <?php foreach($pages as $page) :?>
                            <option <? if ($page->ID == $search_page_id) : ?> selected <? endif; ?> value="<?= $page->ID ?>"><?= $page->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Default Results Per Page</th>
                <td>
                    <input type="text" name="rr-results-per-page" value="<?= get_option('rr-results-per-page', 10) ?>">
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
<?php

}

function retsrabbit_metadata() {
    $rr_adapter = new rr_adapter();
    $table_data = array();

    $servers = $rr_adapter->get_servers();
    $x = 0;
    $filter_html = "";
    $filter_html .= "<h2>Select Servers</h2>";
    $filter_html .= "<form method='POST' action=''>";
    $filter_html .= "<label for='server'>Servers</label>";
    $filter_html .= "<select name='server' id='server'>";

    $selected_server = (isset($_POST['server']) ? $_POST['server'] : '');
    foreach ($servers as $server) {
        $selected = "";
        if($selected_server == $server['server_hash']) {
            $selected = " selected ";
        }
        $filter_html .= "<option value='".$server['server_hash']."'".$selected.">".$x." - ".$server['name']."</option>";
        $x++;
    }
    $filter_html .= "</select>";
    $filter_html .= "<input type='submit' value='Submit'>";
    $filter_html .= "</form>";

    echo($filter_html);

    $types = $rr_adapter->metadata($selected_server);

    if(sizeof($types) == 0)
    {
        $table_data[] = array("<p><h2>No metadata has been found! Please check your Rets Rabbit credentials on the Rets Rabbit Settings Page.</h2></p>");
    }

    foreach ($types as $type) {

        if($type->Resource == "Property") {

            $table_data[] = array("<h2><em>resource</em> {$type->Resource}</h2>&nbsp;&nbsp;&nbsp;[<a class='hide-table' href='#".$type->Resource."'>show/hide</a>]");

            //classes
            foreach ($type->Data as $class) {
                $table_data[] = array("<h3><em>class</em> {$class->ClassName} : {$class->Description}</h3>&nbsp;&nbsp;&nbsp;<!--[<a class='hide-rows' href='#' data-row='".$class->ClassName."'>show/hide</a>]-->");

                $fields = $class->Fields;
                if($fields && sizeof($fields) > 0) {
                    foreach ($fields as $field) {
                        $table_data[] = array("<span class='".$class->ClassName."'><em>field</em> {$field->SystemName} ({$field->DataType}) : {$field->LongName}");

                        if(isset($field->LookupValues)) {
                            $values = $field->LookupValues;
                            foreach($values as $value) {
                                $table_data[] = array("<span class='".$class->ClassName."' style='margin-left:20px;'><em>value</em> {$value->LongValue}");
                            }
                        }
                    }
                }
            }
            //objects (images)
            $object_types = $type->Objects;
            foreach ($object_types as $type) {
                $table_data[] = array("<em>object</em> <h3>{$type->ObjectType}</h3> <strong>described as \"{$type->Description}\"</strong>");
            }
        }

    }
    echo("<table>");
    foreach($table_data as $row) {
        echo("<tr><td>{$row[0]}</td></tr>");
    }
    echo("</table>");

    ?>
    <!--
    <script type="text/javascript">
    jQuery(function() {
        jQuery("table").children('tbody').hide();

        jQuery(".hide-table").click(function() {
            jQuery(this).closest('table').children('tbody').toggle(800);
        });

        jQuery('.hide-rows').click(function() {
            //$(this).closest('tbody').children('tr:not(:first)').toggle(300);
            var class_id = $(this).attr("data-row");
            jQuery('.' + class_id).closest('tr').toggle(800);
        });
    });
    </script>
-->
    <?php
}

function build_pills($params) {
    $pills = '';

    if(sizeof($params)){
        $rr_adapter = new rr_adapter();
        $table_data = array();

        $types = $rr_adapter->metadata();

        if(sizeof($types)){
            //go through all the search params
            foreach($params as $key => $value){
                if($value !== ''){
                    $key = str_replace(":startswith", "", $key);
                    $key = str_replace(":indexof", "", $key);
                    //go through all types
                    foreach ($types as $type) {
                        //only hit property type
                        if($type->Resource == "Property") {
                            //go through the classes
                            foreach ($type->Data as $class) {
                                $fields = $class->Fields;
                                if($fields && sizeof($fields) > 0) {
                                    //go through all fields of the class
                                    foreach ($fields as $field) {
                                        if($field->SystemName === $key){
                                            $pills .= '<div class="search-pill">';

                                            //get rid of escaped quotes due to hyphen in string
                                            if($value[0] === '\\'){
                                                $value = substr($value, 0, -2);
                                                $value = substr($value, 2, strlen($value));
                                            }
                                            $pills .= $field->LongName.': '.$value;
                                            $pills .= '</div>';
                                            break 3;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $pills;
}
?>
