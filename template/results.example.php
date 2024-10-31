<style>
    .row {display:block;}
    .row h3, .row h4 {margin-top:5px;margin-bottom:5px;}
    .col2 {width:24%;display:inline-block;vertical-align:top;}
    .col4 {width:74%;display:inline-block;vertical-align:top;}
</style>

<?php
$detail_page_id = get_option('rr-detail-page');
setlocale(LC_MONETARY, 'en_US');

foreach($results as $result) : ?>
<div class="row">
    <div class="col2">
        <?php foreach($result['photos'] as $photo) :?>
            <img src="<?= $photo['url'] ?>">
        <?php endforeach; ?>
    </div>
    <div class="col4">
        <h3><a href="<?= add_query_arg('mls_id', $result['mls_id'], get_permalink($detail_page_id)) ?>"><?= $result['StreetNumber'].' '.$result['StreetName'] ?></a>
            <span class="text-muted"><?= $result['City']?>, TX</span></h3>
        <h4><?= money_format('%(#10n', $result['ListPrice']);?></h4>
        <p><?=$result['PublicRemarks']?></p>
        <ul class="list-unstyled">
            <li><i class="fa fa-fw fa-th"></i> <?= $result['SqFtTotal']?> Ft<sup>2</sup>
            </li>
            <li><i class="fa fa-fw fa-columns"></i> <?= $result['BedsTotal']?> Beds</li>
            <li><i class="fa fa-fw fa-female"></i> <?= $result['BathsTotal']?> Bathrooms</li>
        </ul>
        <p><a href="<?= add_query_arg('mls_id', $result['mls_id'], get_permalink($detail_page_id)) ?>" class="btn btn-primary">View More »</a>
        </p>
    </div>
</div>
<?php endforeach;?>
