 <?php
    $menuItems = $model->getOrderMenus();
    $menuItemsOptions = $model->getOrderMenuOptions();
    $orderTotals = $model->getOrderTotals();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <title>Print Order</title>
</head>
<body>

<div class="purchase-invoice" style='font-family: arial; font-size: 10pt; width: 76mm; max-width: 80mm; margin: 2mm'>
  <div class="header-info">
      <div align="center">
          <img style="width: 250px;" src="<?php echo 'https://grubsupdev.com/assets/logo/gu-logo.png'; // uploads_url(setting('site_logo')); ?>"/>
      </div>
      <div class="customer-info">
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;">Order : #<b><?= $model->order_id; ?></b></p>
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;"><?= $model->first_name; ?> <?= $model->last_name; ?> <?= $model->telephone ?></p>
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;"><?= $model->email; ?></p>
      </div>
      <div class="order-info" style="width:100%;padding:0;margin-bottom: 10px;">
          <table style="border-spacing: 0; border: 1px solid #000000; margin-top: 10px; width: 100%; padding: 10px;">
              <thead>
                  <tr>
                      <td><strong style="text-transform: uppercase; font-size: 13pt;"><?php if ($model->isDeliveryType()) { echo "DELIVERY"; } if ($model->isCollectionType()) { echo "COLLECTION"; } ?></strong></td>
                  </tr>
                  <tr>
                      <td>
                          <?php if ($model->isDeliveryType()) { echo "Delivery"; } if ($model->isCollectionType()) { echo "Collection"; } ?> Time : <b> <?= $model->order_date->setTimeFromTimeString($model->order_time)->format(setting('date_format').' - '.setting('time_format')); ?></b>
                      </td>
                  </tr>
                  <tr>
                      <td>
                         <b>Address:</b> <?= $model->formatted_address; ?>
                      </td>
                  </tr>
                  <tr>
                      <td>
                         <b>Telephone:</b> <?= $model->telephone; ?>
                      </td>
                  </tr>
                  <!-- <tr>
                      <td>
                          <?php // echo $model->payment_method->name; ?>
                      </td>
                  </tr>-->
                  <tr>
                      <td>
                          <b>Instructions:</b>
                          <?= $model->comment; ?>
                      </td>
                  </tr>
              </thead>
          </table>
      </div>
      <div class="order-items"> 

            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        <table style="border-spacing: 0; border: 1px solid #000000; margin-top: 10px; width: 100%; padding: 10px;">
                            <thead>
                            <tr>
                                <th width="2%"></th>
                                <th width="80%" align="left">
                                    <b>Particulars</b>
                                </th>
                                <th width="20%"><b><?= lang('admin::lang.orders.column_price'); ?></b></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($menuItems as $menuItem) { ?>
                                <tr>
                                    <td style="vertical-align: top;"><?= $menuItem->quantity; ?>x</td>
                                    <td width="80%"><?= $menuItem->name; ?><br/>
                                        <?php if ($menuItemOptions = $menuItemsOptions->get($menuItem->order_menu_id)) { ?>
                                            <div>
                                                <?php foreach ($menuItemOptions as $menuItemOption) { ?>
                                                    <small>
                                                        <?= $menuItemOption->order_option_name; ?>
                                                        =
                                                        <?= currency_format($menuItemOption->order_option_price); ?>
                                                    </small><br>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                        <?php if (!empty($menuItem->comment)) { ?>
                                            <div>
                                                <small><b><?= $menuItem->comment; ?></b></small>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td style="vertical-align: top;" width="20%"><?= currency_format($menuItem->price); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>                            
                        </table>
                        <table style="border-spacing: 0; border-top: 1px solid #000000; border-bottom: 1px solid #000000;margin-top: 10px; width: 100%; padding: 10px;">
                            <tfoot>
                            <?php $totalCount = 1; ?>
                            <?php foreach ($orderTotals as $total) { ?>
                                <?php if ($model->isCollectionType() AND $total->code == 'delivery') continue; ?>
                                <?php $thickLine = ($total->code == 'order_total' OR $total->code == 'total'); ?>
                                <tr style="border-bottom: 1px solid black;">
                                    <td><?= $total->title; ?></td>
                                    <td align="right"><?= currency_format($total->value); ?></td>
                                </tr>
                                <?php $totalCount++; ?>
                            <?php } ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>


      </div>
  </div>
  <div align="center">
      <?= lang('admin::lang.orders.text_invoice_thank_you'); ?>
  </div>
</div>

      </body>
</html>