<?= form_open(current_url(),
    [
        'id' => 'list-form',
        'role' => 'form',
        'method' => 'POST',
    ]
); ?>

<div class="list-table table-responsive">
    <table class="table table-striped mb-0 border-bottom">
        <thead>
        <?= $this->makePartial('$/igniter/orderdashboard/widgets/groupedlists/list_head') ?>
        </thead>
        <tbody>
        <?php if (count($records)) { ?>
            <?= $this->makePartial('$/igniter/orderdashboard/widgets/groupedlists/list_body') ?>
        <?php }
        else { ?>
            <tr>
                <td colspan="99" class="text-center"><?= $emptyMessage; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?= form_close(); ?>

<?= $this->makePartial('$/igniter/orderdashboard/widgets/groupedlists/list_pagination') ?>

<?php if ($showSetup) { ?>
    <?= $this->makePartial('$/igniter/orderdashboard/widgets/groupedlists/list_setup') ?>
<?php } ?>
