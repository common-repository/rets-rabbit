<?php
/*
* This is an example template for a property search form. To search on a property field, prepend "rets:" in the form field's name, followed by
* the name of the RETS property field for your board.
*
* Note that "PropertyType" and "ListPrice" are just examples, the RETS property fields are different from board to board, you can use
* the "Metadata" menu option under "Rets Rabbit" in the Wordpress admin to find the fields specfic to your local real estate board.
*
* The $form_data array will contain values for the previous search, you can use this to save the user's search settings between requests.
*/
?>
<form class="search-form" method="post" action='<?= get_admin_url() ?>admin-post.php'> <!-- Required! DO NOT MODIFY -->
    <input type="hidden" name="action" value="retsrabbit-search"> <!-- Required! DO NOT MODIFY -->
    <div>
        <label for="propertyType">Property Type:</label>
        <select id="propertyType" name="rets:PropertyType">
            <option value="Residential" <?php if ( isset($form_data['PropertyType']) && $form_data['PropertyType'] == 'Resedential'): ?> selected="selected" <?php endif; ?>>Residential</option>
            <option value="Commercial" <?php if ( isset($form_data['PropertyType']) && $form_data['PropertyType'] == "Commercial"): ?> selected="selected" <?php endif; ?>>Commercial</option>
            <option value="Lots & Acreage" <?php if ( isset($form_data['PropertyType']) && $form_data['PropertyType'] == "Lots & Acreage"): ?> selected="selected" <?php endif; ?>>Lots & Acreage</option>
            <option value="Residential Lease" <?php if ( isset($form_data['PropertyType']) && $form_data['PropertyType'] == "Residential Lease"): ?> selected="selected" <?php endif; ?>>Residential Lease</option>
            <option value="'Multi-Family'" <?php if ( isset($form_data['PropertyType']) && $form_data['PropertyType'] == "Multi-Family"): ?> selected="selected" <?php endif; ?>>Multi-Family</option>
        </select>
    </div>
    <div>
        <label for="listPrice">List Price:</label>
        <select name="rets:ListPrice" id="listPrice">
            <option value="" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == ""): ?> selected="selected" <?php endif; ?>>Any</option>
            <option value="30000-300000">30,000-300,000</option>
            <option value="100000-" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "100000-"): ?> selected="selected" <?php endif; ?>>Less than $100,000</option>
            <option value="100001-150000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "100001-150000"): ?> selected="selected" <?php endif; ?>>$100,000 - $150,000</option>
            <option value="150001-200000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "150001-200000"): ?> selected="selected" <?php endif; ?>>$150,000 - $200,000</option>
            <option value="200001-250000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "200001-250000"): ?> selected="selected" <?php endif; ?>>$200,000 - $250,000</option>
            <option value="250001-300000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "250001-300000"): ?> selected="selected" <?php endif; ?>>$250,000 - $300,000</option>
            <option value="300001-350000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "300001-350000"): ?> selected="selected" <?php endif; ?>>$300,000 - $350,000</option>
            <option value="350001-400000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "350001-400000"): ?> selected="selected" <?php endif; ?>>$350,000 - $400,000</option>
            <option value="400001-450000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "400001-450000"): ?> selected="selected" <?php endif; ?>>$400,000 - $450,000</option>
            <option value="450001-500000" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "450001-500000"): ?> selected="selected" <?php endif; ?>>$450,000 - $500,000</option>
            <option value="500000+" <?php if ( isset($form_data['ListPrice']) && $form_data['ListPrice'] == "500000+"): ?> selected="selected" <?php endif; ?>>$500,000+</option>
        </select>
    </div>
    <!--
    <div>
        <Label>MLS number</label>
        <input type="text" name="rets:Matrix_Unique_ID:nocase" value="51209969">
    </div>
-->
    <?php
    /*
    * The "orderby" and "sort_order" input fields give the user control of how the results are sorted. Each <option> under the "orderby" dropdown correpsonds to a
    * RETS property field in your board you'd like to sort on (check the "Metadata" menu option under the "Rets Rabbit" menu in the Wordpress admin for field names).
    * The "sort_order" radio buttons control if the results are sorted in ascending, or descending order.
    *
    */
    ?>
    <div>
        <label for="orderby">Order By:</label>
        <select name="orderby" id="orderby">
            <option value="" <?php if ( isset($form_data['orderby']) && $form_data['orderby'] == ""): ?> selected="selected" <?php endif; ?>>None</option>
            <option value="ListPrice" <?php if ( isset($form_data['orderby']) && $form_data['orderby'] == "ListPrice"): ?> selected="selected" <?php endif; ?>>Price</option>
        </select>
        <input type="radio" group="sort_order" name="sort_order" <?php if ( isset($form_data['sort_order']) && $form_data['sort_order'] == "desc"): ?> checked <?php endif; ?> value="desc">Descending
        <input type="radio" group="sort_order" name="sort_order" <?php if ( isset($form_data['sort_order']) && $form_data['sort_order'] == "asc"): ?> checked <?php endif; ?> value="asc">Ascending
        <input type="text" name="sort_option" value="numeric">
        <input type="text" name="limit" value="10">
    </div>
    <div>
        <input type="submit" value="Search" name="search" id="search" />
    </div>
</form>
