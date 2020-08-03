<div id="local-box">
    <div class="panel local-search">
        <div class="panel-body">
            <div class="row">
                <?php if (!$hideSearch) { ?>
                    <div class="col-sm-12">
                        <?= partial('@searchbar'); ?>
                    </div>
                <?php } ?>
            </div>
            <?php if (
                $location->requiresUserPosition()
                AND $location->userPosition()->hasCoordinates()
                AND !$location->checkDeliveryCoverage()
            ) { ?>
                <span class="help-block"><?= lang('igniter.local::default.text_delivery_coverage'); ?></span>
            <?php } ?>
        </div>
    </div>

    <?php if ($location->current()) { ?>
        <div class="panel panel-local">
            <div class="panel-body">
                <div class="row boxes">
                    <div class="box-one col-sm-6">
                        <?= partial('@box_one'); ?>
                    </div>
                    <div class="box-divider d-block d-sm-none"></div>
                    <div class="box-two col-sm-6">
                        <?= partial('@box_two'); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
