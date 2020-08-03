<?php

namespace Igniter\OrderDashboard\Components;

use System\Classes\BaseComponent;

class PreviewOrder extends BaseComponent
{
    public function defineProperties()
    {
        return [];
    }

    public function onRun() {
        // Do something when the component is loaded by a page or layout
        $this->addJs('js/orderpreview.js', 'orderpreview-js');  
       
    }
}
