<?php
$orderDateTime = $location->orderDateTime();
$orderTimeIsAsap = false; // Disable ASAP time $location->orderTimeIsAsap();
$deliveryInterval = $locationCurrent->getDeliveryTimeAttribute('blah');
$collectionInterval = $locationCurrent->getCollectionTimeAttribute('blah');
if($location->orderTypeIsDelivery()) {
    $intervalMinutes = $deliveryInterval;
} else {
    $intervalMinutes = $collectionInterval;
}
$intervalEndHour = Carbon\Carbon::parse($orderDateTime)->addMinutes($intervalMinutes);

?>
<button
        class="btn btn-light btn-timepicker btn-block text-truncate text-red"
        type="button"
        id="orderTimePicker"
        data-toggle="collapse"
        data-target="#timepickerBox"
    >
        <i class="fa fa-clock-o"></i>&nbsp;&nbsp;
        <b><?=
            ($orderTimeIsAsap)
                ? lang('igniter.local::default.text_asap')
                : $orderDateTime->isoFormat('ddd h:mma') . ' - ' . $intervalEndHour->isoFormat('h:mma');
            ?></b>
    </button>

    <div id="timepickerBox" class="collapse" aria-labelledby="orderTimePicker">

        <form
            class="pt-3"
            role="form"
            data-request="<?= $timeSlotEventHandler; ?>"
        >
            <input type="hidden" data-timepicker-control="type" value="<?= $orderTimeIsAsap ? 'asap' : 'later' ?>">
            <div class="form-group row no-gutters">
                <div class="col-sm-6 col-xs-6 pr-md-1 ">
                <select
                    class="form-control"
                    data-timepicker-control="date"
                    data-timepicker-label="<?= lang('igniter.local::default.label_date'); ?>"
                    data-timepicker-selected="<?= $orderDateTime ? $orderDateTime->format('Y-m-d') : '' ?>"
                ></select>
                </div>
                <div class="col-sm-6 col-xs-6 pl-md-1">
                <select
                    class="form-control mt-2 mt-md-0"
                    data-timepicker-control="time"
                    data-timepicker-interval="45"
                    data-timepicker-label="<?= lang('igniter.local::default.label_time'); ?>"
                    data-timepicker-selected="<?= $orderDateTime ? $orderDateTime->format('H:i') : '' ?>"
                ></select>
            </div>
            <button
                type="button"
                class="btn btn-primary text-nowrap mx-auto mt-2"
                data-timepicker-submit
                data-attach-loading
            >
                <?= sprintf(lang('igniter.local::default.label_choose_order_time'), $location->orderTypeIsDelivery()
                    ? lang('igniter.local::default.text_delivery')
                    : lang('igniter.local::default.text_collection'));
                ?>
            </button>
        </form>
   </div>
</div>
