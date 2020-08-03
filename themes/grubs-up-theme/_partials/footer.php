<div class="footer pt-md-5 pb-md-5">
    
    <?= partial('mobile_banner'); ?>

    <div class="container">
        <div class="row" style="margin-top: 5px;">
            <?php foreach ($footerMenu->menuItems() as $navItem) { ?>
                <div class="col">
                    <div class="footer-links">
                        <h6 class="footer-title d-none d-sm-block"><?= lang($navItem->title); ?></h6>
                        <ul>
                            <?php foreach ($navItem->items as $item) { ?>
                                <li>
                                    <a href="<?= $item->url; ?>"><?= e(lang($item->title)); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>

            <div class="col">
                <div class="social-bottom">
                    <h6 class="footer-title d-none d-sm-block"><?= lang('igniter.orderdashboard::default.text_follow_us'); ?></h6>
                    <?= partial('social_icons', ['socialIcons' => $this->theme->social]); ?>
                </div>
            </div>

            <?php if (has_component('newsletter')) { ?>
                <!-- <div class="col-sm-3 mt-3 mt-sm-0">
                    <div id="newsletter-box">
                        <h5 class="mb-4"><?php // echo lang('igniter.frontend::default.newsletter.text_subscribe'); ?></h5>
                        <?php // partial('newsletter::subscribe-form'); ?>
                    </div>
                </div> -->
            <?php } ?>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col">
                <hr class="mb-1">
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row pb-2">
            <div class="col p-2 text-center">
                <?= sprintf(
                    lang('main::lang.site_copyright'),
                    date('Y'),
                    setting('site_name'),
                    lang('system::lang.system_name')
                ).lang('igniter.orderdashboard::default.system_powered'); ?>
            </div>
        </div>
    </div>
</div>