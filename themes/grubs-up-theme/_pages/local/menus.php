---
title: 'main::lang.local.menus.title'
permalink: '/:location?local/menus/:category?'
description: ''
layout: local

'[localMenu]':
    isGrouped: 1
    menusPerPage: 200
    showMenuImages: 1
    menuImageWidth: 80
    menuImageHeight: 80

---
<?= partial('nav/local_tabs', ['activeTab' => 'menus']); ?>

<div class="panel">
    <div class="d-block d-sm-none">
        <div class="panel-body categories">
            <?= component('categories'); ?>
        </div>
    </div>

    <?= component('localMenu') ?>
</div>
