---
description: Default layout

'[session]':
    security: all

'[staticMenu mainMenu]':
    code: main-menu

---
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= App::getLocale(); ?>">
<head>
    <?= partial('head'); ?>
</head>
<body class="<?= $this->page->bodyClass; ?>" style="background-color: #FC6C35;">

    <header class="header">
        <?= partial('header', [ 'hideNav' => true]); ?>
    </header>

    <main role="main">
        <div id="page-wrapper">
            <?= page(); ?>
        </div>
    </main>

    <div id="notification">
        <?= partial('flash'); ?>
    </div>
    <?= partial('eucookiebanner'); ?>
    <?= partial('scripts'); ?>
</body>
</html>