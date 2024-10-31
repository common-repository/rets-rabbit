<style>
    .row {display:block;}
    .row h1, .row h2, .row h3, .row h4 {margin-top:5px;margin-bottom:5px;}
    .col2 {width:24%;display:inline-block;vertical-align:top;}
    .col4 {width:74%;display:inline-block;vertical-align:top;}
    .entry-title {display:none;}
</style>
<?
setlocale(LC_MONETARY, 'en_US');
?>
<div class="row">
    <h1><?= $result['StreetNumber'].' '.$result['StreetName'] ?> <?= $result['City']?>, TX</h1>
    <h2><?= money_format('%(#10n', $result['ListPrice']);?></h2>
</div>
<div class="row">
    <div class="gallery">
        <?php foreach($result['photos'] as $photo) :?>
            <div><img src="<?= $photo['url'] ?>"></div>
        <?php endforeach; ?>
    </div>
</div>
<div class="row">
        <p><?=$result['PublicRemarks']?></p>
        <ul class="list-unstyled">
            <li><i class="fa fa-fw fa-th"></i> <strong>Size:</strong><?= $result['SqFtTotal']?> Ft<sup>2</sup></li>
            <li><i class="fa fa-fw fa-columns"></i> <strong>Beds:</strong><?= $result['BedsTotal']?> </li>
            <li><i class="fa fa-fw fa-female"></i> <strong>Bathrooms:</strong><?= $result['BathsTotal']?> </li>
            <li><i class="fa fa-fw fa-calendar"></i> <strong>Year Built:</strong> <?= $result['YearBuilt']?></li>
            <?php if($result['ParkingSpacesGarage']) : ?>
                <li><i class="fa fa-fw fa-truck"></i> <strong>Garage:</strong> <?= $result['ParkingSpacesGarage']?> Spots</li>
            <?php endif;?>
            <li><i class="fa fa-fw fa-signal"></i> <strong>Features:</strong> <?= $result['InteriorFeatures']?></li>
            <li><i class="fa fa-fw fa-fire"></i> <strong>Heating Type:</strong> <?= $result['Heating']?></li>
            <li>MLSNumber MLS: <?= $result['MLSNumber']?></li>
            <li>Unique Matrix ID: <?= $result['Matrix_Unique_ID'] ?></li>
        </ul>
</div>

<script>
jQuery(document).ready(function(){
    jQuery('.gallery').slick({
        autoplay:true,
        autoplaySpeed:3000
    });
});
</script>
