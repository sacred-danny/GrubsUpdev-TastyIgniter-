---
title: 'main::lang.home.title'
permalink: /
description: ''
layout: home

'[slider]':
    code: home-slider

'[featuredItems]':
    items: ['6', '7', '8', '9']
    limit: 3
    itemsPerRow: 3
    itemWidth: 400
    itemHeight: 300

---
<?= component('slider'); ?>

<?= partial('holdingMessage'); //component('localSearch'); ?>

<?= component('featuredItems'); ?>