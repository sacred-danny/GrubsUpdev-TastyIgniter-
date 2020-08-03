<?php 
// $records->locationTimeSlots injected from OrderDashboard/Widgets/GroupedLists.php
$today = date('Y-m-d'); 
?>
<tr class="groupedLocationHeader">
    <td colspan=999>
        <span class="title">ASAP</span>
    </td>
</tr>
<?php foreach ($records as $record) {  
    if($record->orderInSlot) {
        continue;
    }
    ?>
    <tr>
        <?php if ($showDragHandle) { ?>
            <td class="list-action">
                <div class="btn btn-handle">
                    <i class="fa fa-bars"></i>
                </div>
            </td>
        <?php } ?>

        <?php if ($showCheckboxes) { ?>
            <td class="list-action"> 
                <div class="custom-control custom-checkbox">
                    <input
                        type="checkbox"
                        id="<?= 'checkbox-'.$record->getKey() ?>"
                        class="custom-control-input"
                        value="<?= $record->getKey(); ?>" name="checked[]"
                    />
                    <label class="custom-control-label" for="<?= 'checkbox-'.$record->getKey() ?>">&nbsp;</label>
                </div>
            </td>
        <?php } ?>

        <?php foreach ($columns as $key => $column) { ?>
            <?php if ($column->type != 'button') continue; ?>
            <td class="list-action <?= $column->cssClass ?>">
                <?= $this->makePartial('$/igniter/orderdashboard/widgets/groupedlists/list_button', ['record' => $record, 'column' => $column]) ?>
            </td>
        <?php } ?>

        <?php $index = $url = 0; ?>
        <?php foreach ($columns as $key => $column) { ?>
            <?php $index++; ?>
            <?php if ($column->type == 'button') continue; ?>
            <td
                class="list-col-index-<?= $index ?> list-col-name-<?= $column->getName() ?> list-col-type-<?= $column->type ?> <?= $column->cssClass ?>"
            >
                <?= $this->getColumnValue($record, $column) ?>
            </td>
        <?php } ?>

        <?php if ($showFilter) { ?>
            <td class="list-setup">&nbsp;</td>
        <?php } ?>

        <?php if ($showSetup) { ?>
            <td class="list-setup">&nbsp;</td>
        <?php } ?>
    </tr>
<?php
}

foreach ($records->locationTimeSlots as $key => $locationTimes) {     
?>
<tr class="groupedLocationHeader">
    <td colspan=999>
        <span class="title"><?=$key?></span>
    </td>
</tr>
<?php

foreach($locationTimes['hours'] as $date => $hourSlots) {
    if($date == $today) {
        foreach($hourSlots as $hour => $hourFormatted) {  
            $ordersInSlot = false;
            foreach($records as $record) {
                if($record->attributes['order_date'] == $today && $record->attributes['order_time']== $hour) {
                    $ordersInSlot = true;
                }
            }
            if($ordersInSlot) {
    ?>
    <tr class="groupedTimeSlotHeader">
        <td colspan=999><span class="title"><?=$hour?></span></td>
    </tr>
    <?php
        // Orders in time slots
            $ordersFound = 0;
            foreach($records as &$record) {
                if($record->attributes['order_date'] == $today && $record->attributes['order_time']== $hour) {
                    $ordersFound++;
                    $record->orderInSlot = true;
                    ?>
                    <tr class="grouped-order-item">
                        <?php if ($showDragHandle) { ?>
                            <td class="list-action">
                                <div class="btn btn-handle">
                                    <i class="fa fa-bars"></i>
                                </div>
                            </td>
                        <?php } ?>

                        <?php if ($showCheckboxes) { ?>
                            <td class="list-action"> 
                                <div class="custom-control custom-checkbox">
                                    <input
                                        type="checkbox"
                                        id="<?= 'checkbox-'.$record->getKey() ?>"
                                        class="custom-control-input"
                                        value="<?= $record->getKey(); ?>" name="checked[]"
                                    />
                                    <label class="custom-control-label" for="<?= 'checkbox-'.$record->getKey() ?>">&nbsp;</label>
                                </div>
                            </td>
                        <?php } ?>

                        <?php foreach ($columns as $key => $column) { ?>
                            <?php if ($column->type != 'button') continue; ?>
                            <td class="list-action <?= $column->cssClass ?>">
                                <?= $this->makePartial('$/igniter/orderdashboard/widgets/groupedlists/list_button', ['record' => $record, 'column' => $column]) ?>
                            </td>
                        <?php } ?>

                        <?php $index = $url = 0; ?>
                        <?php foreach ($columns as $key => $column) { ?>
                            <?php $index++; ?>
                            <?php if ($column->type == 'button') continue; ?>
                            <td
                                class="list-col-index-<?= $index ?> list-col-name-<?= $column->getName() ?> list-col-type-<?= $column->type ?> <?= $column->cssClass ?>"
                            >
                                <?= $this->getColumnValue($record, $column) ?>
                            </td>
                        <?php } ?>

                        <?php if ($showFilter) { ?>
                            <td class="list-setup">&nbsp;</td>
                        <?php } ?>

                        <?php if ($showSetup) { ?>
                            <td class="list-setup">&nbsp;</td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            }
            if($ordersFound == 0) {
                ?>
        <tr class="">
                <td colspan=999>&nbsp;No orders yet</td>
            </tr>
                <?php
            }
        }
    }
}

}
}


?>
