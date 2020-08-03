<?php
$mealtimes = $menuItem->mealtimes;
$special = $menuItem->special;
$mealtimeNotAvailable = !$menuItem->isAvailable($location->orderDateTime());
$specialActive = ($special AND $special->active());
$menuHasOptions = $menuItem->hasOptions();
$menuPrice = $specialActive ? $special->getMenuPrice($menuItem->menu_price) : $menuItem->menu_price;
$mealtimeTitles = [];
foreach ($menuItem->mealtimes ?? [] as $mealtime) {
    $mealtimeTitles[] = sprintf(lang('igniter.local::default.text_mealtime'),
        $mealtime->mealtime_name, $mealtime->start_time, $mealtime->end_time
    );
}
// Get the large image associated with the thumbnail
// e.g. /assets/media/attachments/public/5ee/c90/8bb/5eec908bbc863647113446.jpg
$thumb = $menuItem->getMedia('thumb');
$firstOnly = true;
 
$menuItemUrl = '#';
foreach ($thumb as $item) {
    if ($firstOnly) {
        if ($item instanceof Igniter\Flame\Database\Attach\Media) {
            $baseUrl = $item->getPublicPath(); // Config::get('system.assets.attachment.path');
            $menuItemUrl = $baseUrl . $item->getPartitionDirectory() . '/' . $item->getAttribute('name');
            $firstOnly = false;
        }        
    }
}

?>
<div id="menu<?= $menuItem->menu_id; ?>" class="menu-item">
    <div class="d-flex flex-row">
        <?php
         if ($showMenuImages == 1 AND $menuItem->hasMedia('thumb')) { ?>
            <div
                class="menu-thumb align-self-start mr-3"
                style="width: <?= $menuImageWidth ?>px;"
            >
            <a href="<?php echo $menuItemUrl; //$menuItem->menu_image_url;?>" data-toggle="lightbox">
                <img
                    class="img-rounded"
                    alt="<?= $menuItem->menu_name; ?>"
                    src="<?= $menuItem->getThumb([
                        'width' => $menuImageWidth,
                        'height' => $menuImageHeight,
                        'fit' => 'crop'
                    ]); ?>"
                >
                    </a>
            </div>
        <?php } ?>

        <div class="menu-content flex-grow-1 mr-3">
            <h6 class="menu-name"><?= e($menuItem->menu_name); ?></h6>
            <p class="menu-desc text-muted mb-0">
                <?= nl2br($menuItem->menu_description); ?>
            </p>
        </div>
        <div class="menu-detail align-self-start col-3 text-right p-0">
            <?php if ($specialActive AND ($specialDaysRemaining = $special->daysRemaining()) > 0) { ?>
                <?php
                $specialDaysText = sprintf(lang('igniter.local::default.text_end_elapsed'), $specialDaysRemaining);
                ?>
                <span class="menu-meta text-muted">
                    <i class="fa fa-star text-warning pr-sm-1" title="<?= $specialDaysText; ?>"></i>
                </span>
            <?php } ?>

            <span class="menu-price pr-sm-3">
                <b><?= $menuPrice > 0 ? currency_format($menuPrice) : lang('main::lang.text_free'); ?></b>
            </span>

            <span class="menu-button">
                <button
                    class="btn btn-light btn-sm btn-cart<?= $mealtimeNotAvailable ? ' disabled' : '' ?>"
                    <?php if (!$mealtimeNotAvailable) { ?>
                        <?php if ($menuHasOptions) { ?>
                            data-cart-control="load-item"
                            data-menu-id="<?= $menuItem->menu_id; ?>"
                            data-quantity="<?= $menuItem->minimum_qty; ?>"
                        <?php } else { ?>
                            data-request="<?= $updateCartItemEventHandler; ?>"
                            data-request-data="menuId: '<?= $menuItem->menu_id; ?>', quantity: '<?= $menuItem->minimum_qty; ?>'"
                            data-replace-loading="fa fa-spinner fa-spin"
                        <?php } ?>
                    <?php } else { ?>
                        title="<?= implode("\r\n", $mealtimeTitles); ?>"
                    <?php } ?>
                >
                    <i class="fa fa-<?= $mealtimeNotAvailable ? 'clock-o' : 'plus' ?>"></i>
                </button>
            </span>
        </div>
    </div>
</div>
