<?php

namespace Igniter\Api\Controllers;

use AdminMenu;

// Local Import
use Igniter\Api\Services\TastyJwt;
use Igniter\Api\Services\TastyJson;
use Igniter\Api\Vendor\Stripe\StripeInfo;

// Libary Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DateTime;

require_once(__DIR__ . '/../vendor/stripe/init.php');
/**
 * Infos Admin Controller
 */

class Infos extends \Admin\Classes\AdminController {

    private $modelConfig = [
        'user' => 'Admin\Models\Customers_model',
        'location' => 'Admin\Models\Locations_model',
        'locationable' => 'Igniter\Api\Models\Locationable',
        'address' => 'Admin\Models\Addresses_model',
        'category' => 'Admin\Models\Categories_model',
        'menuCategory' => 'Admin\Models\Menu_categories_model',
        'menu' => 'Admin\Models\Menus_model',
        'locationArea' => 'Admin\Models\Location_areas_model',
        'menuOption' => 'Admin\Models\Menu_item_options_model',
        'coupon' => 'Admin\Models\Coupons_model',
        'couponHistory' => 'Admin\Models\Coupons_history_model',
        'order' => 'Admin\Models\Orders_model',
        'orderTotal' => 'Igniter\Api\Models\OrderTotal',
        'orderMenu' => 'Igniter\Api\Models\OrderMenu',
        'orderMenuOption' => 'Igniter\Api\Models\OrderMenuOption',
        'status' => 'Admin\Models\Statuses_model',
        'favorite' => 'Igniter\Api\Models\Favourite',
        'page' => 'Igniter\Api\Models\Page',
        'customerSetting' => 'Igniter\Api\Models\CustomerSetting',
    ];

    private $userModel;
    private $locationModel;
    private $locationAreaModel;
    private $addressModel;
    private $categoryModel;
    private $menuCategoryModel;
    private $menuOptionModel;
    private $menuModel;
    private $couponModel;
    private $couponHistoryModel;
    private $locationableModel;
    private $orderModel;
    private $orderTotalModel;
    private $orderMenuModel;
    private $orderMenuOptionModel;
    private $statusModel;
    private $favoriteModel;
    private $pageModel;
    private $customerSettingModel;

    private $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    private $stripe;

    public function __construct() {
        parent::__construct();
        $this->userModel = new $this->modelConfig['user'];
        $this->locationModel =  new $this->modelConfig['location'];
        $this->locationAreaModel = new $this->modelConfig['locationArea'];
        $this->addressModel = new $this->modelConfig['address'];
        $this->categoryModel = new $this->modelConfig['category'];
        $this->menuCategoryModel = new $this->modelConfig['menuCategory'];
        $this->menuOptionModel = new $this->modelConfig['menuOption'];
        $this->menuModel = new $this->modelConfig['menu'];
        $this->couponModel = new $this->modelConfig['coupon'];
        $this->couponHistoryModel = new $this->modelConfig['couponHistory'];
        $this->locationableModel = new $this->modelConfig['locationable'];
        $this->orderModel = new $this->modelConfig['order'];
        $this->orderTotalModel = new $this->modelConfig['orderTotal'];
        $this->orderMenuModel = new $this->modelConfig['orderMenu'];
        $this->orderMenuOptionModel = new $this->modelConfig['orderMenuOption'];
        $this->statusModel = new $this->modelConfig['status'];
        $this->favoriteModel = new $this->modelConfig['favorite'];
        $this->pageModel = new $this->modelConfig['page'];
        $this->customerSettingModel = new $this->modelConfig['customerSetting'];

        $this->stripe = new \Stripe\StripeClient(config('api.stripe_key_test_secret'));
    }

    public function menu(Request $request) {
        if (TastyJwt::instance()->validateToken($request) == 0) {
            abort(401, lang('igniter.api::lang.auth.alert_token_expired'));
        }
        try {
            $currentWeekDay = ((int)date('w') + 7 - 1) % 7;
            $currentTime = date('H:i');
            $locationArea = $this->locationAreaModel->where('area_id', $request['user']['areaId'])->first();
            if ($locationArea) {
                if($locationArea->conditions[0]['amount'] == '0.00' && $locationArea->conditions[0]['total'] == '0.00') {
                    $delivery = 'Free on all orders';
                } else {
                    if($locationArea->conditions[0]['total'] == '0.00') {
                        $delivery = '£' . $locationArea->conditions[0]['amount'] . ' on all orders';
                    } else {
                        $delivery = '£' . $locationArea->conditions[0]['amount'] . ' below ' . '£' . $locationArea->conditions[0]['total'];
                    }
                }
            } else { 
                $delivery = 'Free on all orders';
            }
            if ($request['user']['locationId'] == '') {
                abort(400, lang('igniter.api::lang.location.alert_not_correct_location'));
            }

            $location = $this->locationModel->where('location_id', $request['user']['locationId'])->first();
                

            $titleOpenTime = $titleOpenTimeColor = $openTime = '';
            $openingTimes = $location['options']['hours']['opening']['flexible'];
            for ($i = $currentWeekDay; $i < $currentWeekDay + count($openingTimes); $i++) {
                if($openingTimes[$i % 7]['status'] != 0) {
                    if ($i == $currentWeekDay) {
                        if ($currentTime >= $openingTimes[$i % 7]['open'] && $currentTime <= $openingTimes[$i % 7]['close']) {
                            $hour = (int)explode(':', $openingTimes[$i % 7]['open'])[0];
                            $minute = (int)explode(':', $openingTimes[$i % 7]['open'])[1];
                            $titleOpenTime = "We're Open";
                            $titleOpenTimeColor = "green";

                            $openTime = (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : (($hour < 10) ? ('0' . $hour) : $hour)) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : 'AM') . ' - ';
                            $hour = (int)explode(':', $openingTimes[$i % 7]['close'])[0];
                            $minute = (int)explode(':', $openingTimes[$i % 7]['close'])[1];
                            $openTime = $openTime . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : (($hour < 10) ? ('0' . $hour) : $hour)) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : 'AM');
                            break;
                        } else if($currentTime <= $openingTimes[$i % 7]['open']) {
                            $hour = (int)explode(':', $openingTimes[$i % 7]['open'])[0];
                            $minute = (int)explode(':', $openingTimes[$i % 7]['open'])[1];
                            $titleOpenTime = "Opening Today";
                            $titleOpenTimeColor = "green";
                            $openTime = (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : (($hour < 10) ? ('0' . $hour) : $hour)) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : 'AM') . ' - ';
                            $hour = (int)explode(':', $openingTimes[$i % 7]['close'])[0];
                            $minute = (int)explode(':', $openingTimes[$i % 7]['close'])[1];
                            $openTime = $openTime . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : (($hour < 10) ? ('0' . $hour) : $hour)) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : 'AM');
                            break;
                        } else if($currentTime >= $openingTimes[$i % 7]['close']) {
                            continue;
                            break;
                        }
                    } else {
                        $hour = (int)explode(':', $openingTimes[$i % 7]['open'])[0];
                        $minute = (int)explode(':', $openingTimes[$i % 7]['open'])[1];
                        $titleOpenTime = "Opening " . $this->weekDays[$i % 7] . " " . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : (($hour < 10) ? ('0' . $hour) : $hour)) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : 'AM');
                        $titleOpenTimeColor = "red";
                        $openTime = (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : (($hour < 10) ? ('0' . $hour) : $hour)) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : 'AM') . ' - ';
                        $hour = (int)explode(':', $openingTimes[$i % 7]['close'])[0];
                        $minute = (int)explode(':', $openingTimes[$i % 7]['close'])[1];
                        $openTime = $openTime . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : (($hour < 10) ? ('0' . $hour) : $hour)) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : 'AM');
                        break;
                    }
                }
            }

            $specailsCategoryId = $this->categoryModel->where('permalink_slug', 'specials')->first()->category_id;


            $customSpecials = $this->menuCategoryModel::with(array('menu'=>function($query){
                $query->where('menu_status', 1);
            }))->where('category_id', $specailsCategoryId)->get();
            $specials = array();
            foreach ($customSpecials as $special) {
                if ($this->locationableModel->where('locationable_type', 'menus')->where('locationable_id', $special->menu_id)->where('location_id', $request['user']['locationId'])->first()) {
                    $menu = $this->menuModel->where('menu_id', $special->menu_id)->where('menu_status', 1)->first();
                    if ($menu) {
                        $thumb=$menu->getMedia('thumb');
                        $firstOnly = true;
                        $menuItemUrl = '#';
                        foreach ($thumb as $item) {
                            if ($firstOnly) {
                                    $baseUrl = $item->getPublicPath(); // Config::get('system.assets.attachment.path');
                                    $menuItemUrl = $baseUrl . $item->getPartitionDirectory() . '/' . $item->getAttribute('name');
                                    $firstOnly = false;
                            }
                        }
                    
                        $special['menu']['menu_image_url'] = $menuItemUrl;

                        array_push($specials, $special);
                    }
                }
            }

            $categories = $this->categoryModel->where('category_id', '<>', $specailsCategoryId)->orderBy('priority', 'ASC')->get();
            $categoryDetails = $this->categoryModel::with(array('menus'=>function($query){
                $query->where('menu_status', 1);
            }))->where('category_id', '<>', $specailsCategoryId)->orderBy('priority', 'ASC')->get();
            foreach($categoryDetails as $detail) {

                 foreach($detail['menus'] as $menu) {
                     $thumb=$menu->getMedia('thumb');
                     $firstOnly = true;
                     $menuItemUrl = '#';
                     foreach ($thumb as $item) {
                         if ($firstOnly) {
                                 $baseUrl = $item->getPublicPath(); // Config::get('system.assets.attachment.path');
                                 $menuItemUrl = $baseUrl . $item->getPartitionDirectory() . '/' . $item->getAttribute('name');
                                 $firstOnly = false;
                         }
                     }
                    
                     $menu->menu_image_url = $menuItemUrl;
                    
                 }
             }
            $allCoupons = $this->couponModel->get();
            $coupons = array();
            foreach ($allCoupons as $value) {
                $coupon = [
                    'couponId' => $value->coupon_id,
                    'code' => $value->code,
                    'type' => $value->type,
                    'minTotal' => $value->min_total,
                    'discount' => $value->discount,
                ];
                array_push($coupons, $coupon);
            }

            $response = [
                'locationId' => $location->location_id,
                'locationName' => $location->location_name,
                'delivery' => $delivery ,
                'titleOpenTime' => $titleOpenTime,
                'titleOpenTimeColor' => $titleOpenTimeColor,
                'openTime' => $openTime,
                'specials' => $specials,
                'categories' => $categories,
                'categoryDetails' => $categoryDetails,
                'deliveryAmount' => ($locationArea) ? $locationArea->conditions[0]['amount'] : 0,
                'deliveryTotal' => ($locationArea) ? $locationArea->conditions[0]['total'] : 0,
                'offerDelivery' => ($location['options']['offer_delivery'] == 0) ? false : true,
                'offerCollection' => ($location['options']['offer_collection'] == 0) ? false : true,
                'coupons' => $coupons
            ];
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function validateCoupon(Request $request) {
        $user = $this->userModel->where('customer_id', $request['userId'])->first();
        $coupon = $this->couponModel->where('coupon_id', $request['couponId'])->first();
        if ($coupon->hasReachedMaxRedemption())
            abort(400, lang('igniter.api::lang.coupon.not_allowed_coupon'));
        if ($user && $coupon->customerHasMaxRedemption($user))
            abort(400, lang('igniter.api::lang.coupon.not_allowed_coupon'));
        return 'true';
    }

    public function menuDetail(Request $request) {
        if (TastyJwt::instance()->validateToken($request) == 0) {
            abort(401, lang('igniter.api::lang.auth.alert_token_expired'));
        }
        try {
            $menu = $this->menuModel->where('menu_id', $request->id)->first();;
            $thumb=$menu->getMedia('thumb');
            $firstOnly = true;
            $menuItemUrl = '#';
            foreach ($thumb as $item) {
                if ($firstOnly) {
                    $baseUrl = $item->getPublicPath(); // Config::get('system.assets.attachment.path');
                    $menuItemUrl = $baseUrl . $item->getPartitionDirectory() . '/' . $item->getAttribute('name');
                    $firstOnly = false;
                }
            }
            $menu->menu_image_url = $menuItemUrl;
            $response['menu'] = $menu;
            $favorite = $this->favoriteModel->where('customer_id', $request['userId'])->where('menu_id', $request['id'])->first();
            if ($favorite) {
                $response['menu']['isFavorite'] = true;
            } else {
                $response['menu']['isFavorite'] = false;
            }
            $response['options'] = $this->menuOptionModel::with('option_values')->with('option')->where('menu_id', $request->id)->get();
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function getCheckOutTime(Request $request) {
        if (TastyJwt::instance()->validateToken($request) == 0) {
            abort(401, lang('igniter.api::lang.auth.alert_token_expired'));
        }
        try {
            
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            $response['savedCards'] = \Stripe\PaymentMethod::all([
              'customer' => $stripe_customer_id,
              'type' => 'card',
            ]);
            $intent = $this->stripe->setupIntents->create([
              'customer' => $stripe_customer_id,
            ]);
            $response['clientSecret'] = $intent->client_secret;
            $locationArea = $this->locationAreaModel->where('area_id', $request['user']['areaId'])->first();

            if ($locationArea) {
                $location = $this->locationModel->where('location_id', $locationArea->location_id)->first();
            } else {
                $location = $this->locationModel->where('location_id', $request['user']['locationId'])->first();
            }
            $response['delivery'] = array();
            $response['offerDelivery'] = ($location['options']['offer_delivery'] == 0) ? false : true;
            if ($location['options']['offer_delivery'] == true) {
                $deliveryTimeInterval = $location['options']['delivery_time_interval'];
                $deliveryLeadTime = $location['options']['delivery_lead_time'];

                $currentDate = date('Y-m-d', strtotime("+" . $deliveryLeadTime . " minutes"));
                $currentTime = date('H:i', strtotime("+" . $deliveryLeadTime . " minutes"));
                $currentWeekDay = ((int)date('w', strtotime("+" . $deliveryLeadTime . " minutes")) + 7 - 1) % 7;
                
                $deliveryTimes = $location['options']['hours']['delivery']['flexible'];
                
                $currentHour = (float)explode(':', $currentTime)[0];
                $currentMinute = (float)explode(':', $currentTime)[1];

                for ($i = $currentWeekDay; $i < $currentWeekDay + count($deliveryTimes); $i++) {
                    if($deliveryTimes[$i % 7]['status'] != 0) {
                        $date = [
                            'id' => count($response['delivery']),
                            'date' => date('Y-m-d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                            'day' => date('d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                            'weekDay' => $this->weekDays[$i % 7],
                            'times' => array()
                        ];
                       
                        $openHour = (float)explode(':', $deliveryTimes[$i % 7]['open'])[0];
                        $openMinute = (float)explode(':', $deliveryTimes[$i % 7]['open'])[1];

                        $closeHour = (float)explode(':', $deliveryTimes[$i % 7]['close'])[0];
                        $closeMinute = (float)explode(':', $deliveryTimes[$i % 7]['close'])[1];

                        if ($i == $currentWeekDay) {
                            if ($currentTime <= $deliveryTimes[$i % 7]['open']) {
                                $currentHour = $openHour;
                                $currentMinute = $openMinute;
                            } else {
                                for($j = 0; $j <= 60; $j += $deliveryTimeInterval)
                                {
                                    if($currentMinute < $j)
                                    {
                                        $currentMinute = ($j >= 60) ? '00' : $j;
                                        if ($j >= 60)
                                            $currentHour += 1;
                                        break;
                                    }
                                }
                            }
                        } else {
                            $currentHour = $openHour;
                            $currentMinute = $openMinute;
                        }

                        $currentHour += ($currentMinute / 60);
                        $closeHour += ($closeMinute / 60);

                        $hourDelta = $deliveryTimeInterval / 60;
                        for ($j = $currentHour; $j < $closeHour - $hourDelta; $j += $hourDelta) {

                            $orderMin = (int)(($j - (int)$j) * 60);
                            $orderHour = $j;
                            if($orderMin % 5 != 0)
                            {
                                $orderMin += (5 - ($orderMin % 5));
                            }
                            if($orderMin >= 60)
                            {
                                $orderMin = 0;
                                $orderHour += 1;
                            }
                            $k = $j + $hourDelta;
                            $showMin = (int)(($k - (int)$k) * 60);
                            $showHour = $k;
                            if($showMin % 5 != 0)
                            {
                                $showMin += (5 - ($showMin % 5));
                            }
                            if($showMin >= 60)
                            {
                                $showMin = 0;
                                $showHour += 1;
                            }

                            $temp = [
                                'orderTime' => (int)$orderHour . ':' . (($orderMin < 10) ? ('0' . $orderMin) : $orderMin),
                                'showTime' => (int)$orderHour . ':' . (($orderMin < 10) ? ('0' . $orderMin) : $orderMin) . ' - ' .(int)$showHour . ':' . (($showMin < 10) ? ('0' . $showMin) : $showMin),
                            ];
                            array_push($date['times'], $temp);
                        }

                        if(count($date['times']) > 0)
                            array_push($response['delivery'], $date);
                    }
                }
            }
            $response['pickup'] = array();
            $response['offerCollection'] = ($location['options']['offer_collection'] == 0) ? false : true;
            if ($location['options']['offer_collection'] == true) {
                $collectionTimeInterval = $location['options']['collection_time_interval'];
                $collectionLeadTime = $location['options']['collection_lead_time'];

                $currentDate = date('Y-m-d', strtotime("+" . $collectionLeadTime . " minutes"));
                $currentTime = date('H:i', strtotime("+" . $collectionLeadTime . " minutes"));
                $currentWeekDay = ((int)date('w', strtotime("+" . $collectionLeadTime . " minutes")) + 7 - 1) % 7;
                $pickUpTimes = $location['options']['hours']['collection']['flexible'];

                $currentHour = (float)explode(':', $currentTime)[0];
                $currentMinute = (float)explode(':', $currentTime)[1];
                for ($i = $currentWeekDay; $i < $currentWeekDay + count($pickUpTimes); $i++) {
                    if($pickUpTimes[$i % 7]['status'] != 0) {
                        $date = [
                            'id' => count($response['pickup']),
                            'date' => date('Y-m-d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                            'day' => date('d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                            'weekDay' => $this->weekDays[$i % 7],
                            'times' => array()
                        ];

                        $openHour = (float)explode(':', $pickUpTimes[$i % 7]['open'])[0];
                        $openMinute = (float)explode(':', $pickUpTimes[$i % 7]['open'])[1];

                        $closeHour = (float)explode(':', $pickUpTimes[$i % 7]['close'])[0];
                        $closeMinute = (float)explode(':', $pickUpTimes[$i % 7]['close'])[1];

                        if ($i == $currentWeekDay) {
                            if ($currentTime <= $pickUpTimes[$i % 7]['open']) {
                                $currentHour = $openHour;
                                $currentMinute = $openMinute;
                            } else {
                                for($j = 0; $j <= 60; $j += $collectionTimeInterval)
                                {
                                    if($currentMinute < $j)
                                    {
                                        $currentMinute = ($j >= 60) ? '00' : $j;
                                        if ($j >= 60)
                                            $currentHour += 1;
                                        break;
                                    }
                                }
                            }
                        } else {
                            $currentHour = $openHour;
                            $currentMinute = $openMinute;
                        }

                        $currentHour += ($currentMinute / 60);
                        $closeHour += ($closeMinute / 60);
                        $hourDelta = $collectionTimeInterval / 60;
                        for ($j = $currentHour; $j <= $closeHour - $hourDelta; $j += $hourDelta) {

                            $orderMin = (int)(($j - (int)$j) * 60);
                            $orderHour = $j;
                            if($orderMin % 5 != 0)
                            {
                                $orderMin += (5 - ($orderMin % 5));
                            }
                            if($orderMin >= 60)
                            {
                                $orderMin = 0;
                                $orderHour += 1;
                            }
                            $k = $j + $hourDelta;
                            $showMin = (int)(($k - (int)$k) * 60);
                            $showHour = $k;
                            if($showMin % 5 != 0)
                            {
                                $showMin += (5 - ($showMin % 5));
                            }
                            if($showMin >= 60)
                            {
                                $showMin = 0;
                                $showHour += 1;
                            }

                            $temp = [
                                'orderTime' => (int)$orderHour . ':' . (($orderMin < 10) ? ('0' . $orderMin) : $orderMin),
                                'showTime' => (int)$orderHour . ':' . (($orderMin < 10) ? ('0' . $orderMin) : $orderMin) . ' - ' .(int)$showHour . ':' . (($showMin < 10) ? ('0' . $showMin) : $showMin),
                            ];
                            array_push($date['times'], $temp);
                        }

                        if(count($date['times']) > 0)
                            array_push($response['pickup'], $date);
                    }
                }
            }
            
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function getSavedCard(Request $request) {
        try {
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            $response = \Stripe\PaymentMethod::all([
              'customer' => $stripe_customer_id,
              'type' => 'card',
            ]);

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function deleteCard(Request $request) {
        try {
            $this->stripe->paymentMethods->detach(
              $request['paymentMethodId'],
              []
            );
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            $response = \Stripe\PaymentMethod::all([
              'customer' => $stripe_customer_id,
              'type' => 'card',
            ]);

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function makePaymentIntent(Request $request) {
        try {
            $payment_intent = null;
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            \Stripe\PaymentIntent::create([
                'amount' => $request['amount'],
                'currency' => 'gbp',
                'customer' => $stripe_customer_id,
                'payment_method' => $request['paymentMethodId'],
                'off_session' => true,
                'confirm' => true,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            // Error code will be authentication_required if authentication is needed
            $payment_intent_id = $e->getError()->payment_intent->id;
            $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
        }
        return $payment_intent;
    }

    public function verifyPayment(Request $request) {
        try {
            $user = $this->userModel->where('customer_id', $request['customer_id'])->first();
            $customerSetting = $this->customerSettingModel->where('customer_id', $user->customer_id)->first();
            $locationId = $request['location_id'];
            $addressId = '';
            if ($locationId != '') {
                if (count($user->addresses) > 0)
                    $addressId = $user->addresses[0]->address_id;
            }
            $order = [
                'customer_id' => $request->customer_id,
                'total_items' => $request->total_items,
                'payment' => $request->payment,
                'comment' => $request->comment,
                'order_type' => $request->order_type,
                'status_id' => $request->status_id,
                'order_time' => $request->order_time,
                'order_date' => $request->order_date,
                'order_total' => $request->order_total,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'telephone' => $user->telephone,
                'email' => $user->email,
                'address_id' => $addressId,
                'location_id' => $locationId,
                'processed' => 1,
                'date_added' => new DateTime(),
                'date_modified' => new DateTime(),
            ];
            if ($this->orderModel->insertOrIgnore($order)) {
                $currentOrder = $this->orderModel->where('customer_id', $request->customer_id)->orderBy('order_id', 'desc')->first();

                $coupon = $this->couponModel->where('coupon_id', $request->coupon_id)->first();

                if ($coupon) {
                    $couponHistory = [
                        'coupon_id' => $request->coupon_id,
                        'order_id' => $currentOrder->order_id,
                        'customer_id' => $request->customer_id,
                        'code' => $coupon->code,
                        'min_total' => $coupon->min_total,
                        'amount' => $request->discount_amount,
                        'date_used' => new DateTime(),
                        'status' => 1,
                    ];
                    $this->couponHistoryModel->insertOrIgnore($couponHistory);
                }
                
                $orderTotalData = [
                    'order_id' => $currentOrder->order_id,
                    'code' => 'delivery',
                    'title' => 'Delivery',
                    'value' => $request['order']['delivery'],
                    'priority' => 1
                ];
                $this->orderTotalModel->insertOrIgnore($orderTotalData);
                $orderTotalData = [
                    'order_id' => $currentOrder->order_id,
                    'code' => 'subtotal',
                    'title' => 'Sub Total',
                    'value' => $request['order']['total_price'],
                    'priority' => 0
                ];
                $this->orderTotalModel->insertOrIgnore($orderTotalData);
                $orderTotalData = [
                    'order_id' => $currentOrder->order_id,
                    'code' => 'total',
                    'title' => 'Order Total',
                    'value' => $request['order']['current_price'],
                    'priority' => 127
                ];
                $this->orderTotalModel->insertOrIgnore($orderTotalData);

                foreach ($request['order']['items'] as $menu) {
                    $orderMenuData = [
                        'order_id' => $currentOrder->order_id,
                        'menu_id' => $menu['menu_id'],
                        'name' => $menu['name'],
                        'quantity' => $menu['quantity'],
                        'price' => $menu['price'],
                        'subtotal' => $menu['subtotal'],
                        'comment' => $menu['comment'],
                    ];
                    if ($this->orderMenuModel->insertOrIgnore($orderMenuData)) {
                        $currentOrderMenu = $this->orderMenuModel->where('order_id', $currentOrder->order_id)->orderBy('order_menu_id', 'desc')->first();
                        foreach ($menu['extras'] as $extra) {
                            $optionData = [
                                'order_id' => $currentOrder->order_id,
                                'menu_id' => $extra['menu_id'],
                                'order_option_name' => $extra['order_option_name'],
                                'order_option_price' => $extra['order_option_price'],
                                'order_menu_id' => $currentOrderMenu->order_menu_id,
                                'order_menu_option_id' => $extra['order_menu_option_id'],
                                'menu_option_value_id' => $extra['menu_option_value_id'],
                                'quantity' => $extra['quantity']
                            ];
                            $this->orderMenuOptionModel->insertOrIgnore($optionData);
                        }
                    }
                }
                return 'true';
            }
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return 'false';
    }

    public function getOrders(Request $request) {
        if (TastyJwt::instance()->validateToken($request) == 0) {
            abort(401, lang('igniter.api::lang.auth.alert_token_expired'));
        }
        try {
            $orders = $this->orderModel->where('customer_id', $request['user']['id'])->orderBy('date_added', 'DESC')->limit(5)->get();
            foreach ($orders as $order) {
                $order['status_name'] = $this->statusModel->where('status_id', $order->status_id)->first()->status_name;
                $order['date'] = date('d/m/y', strtotime($order->date_added));
            }
            $response['orders'] = $orders;

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function addFavorites(Request $request) {
        try {
            $favorite = $this->favoriteModel->where('customer_id', $request['userId'])->where('menu_id', $request['id'])->first();
            if (!$favorite) {
                $requestFavorite = [
                    'customer_id' => $request['userId'],
                    'menu_id' => $request['id'],
                ];
                if ($this->favoriteModel->insertOrIgnore($requestFavorite)) {
                    return 'true';
                }
            } else {
                if ($this->favoriteModel->where('customer_id', $request['userId'])->where('menu_id', $request['id'])->delete()) {
                    return 'false';
                }
            }

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return 'false';
    }

    public function getFavorites(Request $request) {
        if (TastyJwt::instance()->validateToken($request) == 0) {
            abort(401, lang('igniter.api::lang.auth.alert_token_expired'));
        }
        try {
            $favoriteIds = $this->favoriteModel->where('customer_id', $request['user']['id'])->get();
            $favorites = array();
            foreach ($favoriteIds as $favorite) {
                $menu = $this->menuModel->where('menu_id', $favorite->menu_id)->first();
                $thumb=$menu->getMedia('thumb');
                $firstOnly = true;
                $menuItemUrl = '#';
                foreach ($thumb as $item) {
                    if ($firstOnly) {
                        $baseUrl = $item->getPublicPath(); // Config::get('system.assets.attachment.path');
                        $menuItemUrl = $baseUrl . $item->getPartitionDirectory() . '/' . $item->getAttribute('name');
                        $firstOnly = false;
                    }
                }
                $menu->menu_image_url = $menuItemUrl;
                $menu['isFavorite'] = true;
                array_push($favorites, $menu);
            }
            return $favorites;

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return array();
    }

    public function getPolicy(Request $Request) {
        $response['content'] = $this->pageModel->where('permalink_slug', 'policy')->first()->content;
        return $response;
    }

    public function getTerms(Request $Request) {
        $response['content'] = $this->pageModel->where('permalink_slug', 'terms-and-conditions')->first()->content;
        return $response;
    }

    public function getStripeInfo(Request $Request) {
        StripeInfo::instance()->getInfo();
    }
}