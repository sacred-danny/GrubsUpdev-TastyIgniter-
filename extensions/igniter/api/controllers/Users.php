<?php

namespace Igniter\Api\Controllers;

use AdminMenu;

// Local Import
use Igniter\Api\Services\TastyJwt;


// Libary Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use ApplicationException;
use Exception;
use Geocoder;
use Location;
use DateTime;

require_once(__DIR__ . '/../vendor/stripe/init.php');
/**
 * Users Controller
 */
class Users extends \Admin\Classes\AdminController {

    private $modelConfig = [
        'user' => 'Admin\Models\Customers_model',
        'location' => 'Admin\Models\Locations_model',
        'address' => 'Admin\Models\Addresses_model',
        'customerGroup' => 'Admin\Models\Customer_groups_model',
        'locationArea' => 'Admin\Models\Location_areas_model',
        'customerSetting' => 'Igniter\Api\Models\CustomerSetting',
        'customerPush' => 'Igniter\Api\Models\CustomerPush',
    ];

    private $userModel;
    private $locationModel;
    private $customerGroupModel;
    private $locationAreaModel;
    private $customerSettingModel;
    private $customerPushModel;
    private $stripeConfig;
    private $stripe;

    public function __construct() {
        parent::__construct();
        $this->userModel = new $this->modelConfig['user'];
        $this->locationModel =  new $this->modelConfig['location'];
        $this->locationAreaModel = new $this->modelConfig['locationArea'];
        $this->customerSettingModel = new $this->modelConfig['customerSetting'];
        $this->customerGroupModel = new $this->modelConfig['customerGroup'];
        $this->customerPushModel = new $this->modelConfig['customerPush'];
        if ($this->customerGroupModel->where('group_name', 'App User')->get()->count() == 0) {
            $this->customerGroupModel->insertOrIgnore([
                'group_name' => 'App User',
                'description' => '',
                'approval' => 0
            ]);
        }
        $this->stripe = new \Stripe\StripeClient(config('api.stripe_key_test_secret'));
    }

    public function makeUserResponse($user) {
        $response['token'] = $user->remember_token;
        $deliveryAddress = '';
        $isPush = 0;
        $customerSetting = $this->customerSettingModel->where('customer_id', $user->customer_id)->first();
        if ($customerSetting) {
            $isPush = $customerSetting->push_status;
        }

        if (count($user->addresses) > 0)
            $deliveryAddress = $user->addresses[0]->address_1 . ' ' . $user->addresses[0]->address_2 . ', ' . $user->addresses[0]->postcode;
        $customerSetting = $this->customerSettingModel->where('customer_id', $user->customer_id)->first();
        $areaId = $customerSetting ? $customerSetting->area_id : '';
        $locationId = $customerSetting ? $customerSetting->location_id : '';
        $stripeCustomerId = $customerSetting ? $customerSetting->stripeCustomerId : '';
        
        $response['user'] = [
            'id' => $user->customer_id,
            'email' => $user->email,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'telephone' => $user->telephone,
            'locationId' => $locationId,
            'areaId' => $areaId,
            'deliveryAddress' => $deliveryAddress,
            'stripeCustomerId' => $stripeCustomerId,
            'isFacebook' => ($user->isFacebook == true) ? true : false,
            'isPush' => ($isPush == 0) ? false : true
        ];

        $locations = $this->locationModel->where('location_status', 1)->get();

        $isLocationExist = false;

        foreach ($locations as $location) {
            if ($locationId == $location->location_id) {
                $isLocationExist = true;
                if ($location['options']['offer_delivery'] == false) {
                    $response['user']['areaId'] = '';
                    $response['user']['deliveryAddress'] = '';
                }
            }
        }
        if($isLocationExist == false) {
            $response['user']['locationId'] = '';
            $response['user']['areaId'] = '';
            $response['user']['deliveryAddress'] = '';
        }

        return $response;
    }

    public function getLocation(Request $request) {
        $response = [];
        $locations = $this->locationModel->where('location_status', 1)->get();

        foreach ($locations as $location) {
            $temp = [
                'locationId' => $location->location_id,
                'locationName' => $location->location_name,
                'offerDelivery' => ($location['options']['offer_delivery'] == 0) ? false : true,
                'offerCollection' => ($location['options']['offer_collection'] == 0) ? false : true,
            ];
            array_push($response, $temp);
        }
        return $response;
    }

    public function signUp(Request $request) {
        // Encode user password with Hash
        $request['password'] = TastyJwt::instance()->makeHashPassword($request['password']);
        
        $customerGroupId = $this->customerGroupModel->where('group_name', 'App User')->first()->customer_group_id;
        if ($request['userId']) {
            $user = $this->userModel->where('customer_id', $request['userId'])->first();
            $token = TastyJwt::instance()->makeToken($user);
            $requestUser = [
                'first_name' => $request['firstName'],
                'last_name' => $request['lastName'],
                'telephone' => $request['telephone'],
                'email' => $request['email'],
                'remember_token' => $token,
                'password' => $request['password'],
                'date_added' => new DateTime(),
                'status' => 1,
            ];

            if ($this->userModel->where('customer_id', $request['userId'])->update($requestUser))
            {
                if ($request['fcmToken'])
                {
                    $push = $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->first();
                    if ($push) {
                        $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->update(['device_token' => $request['fcmToken']]);
                    } else
                    {
                        $setting = [
                            'customer_id' => $user->customer_id,
                            'device_token' => $request['fcmToken'],
                            'device_type' => $request['deviceType'],
                        ];
                        $this->customerPushModel->insertOrIgnore($setting);
                    }
                }
                $user = $this->userModel->where('customer_id', $request['userId'])->first();
                return $this->makeUserResponse($user);
            }
        }

        $user = $this->userModel->where('email', $request['email'])->first();
        if($request['isFacebook']) {
            $stripe_customer = $this->stripe->customers->create([
                'name' => $request['firstName'],
                'email' => $request['email'],
            ]);
            $requestUser = [
                'first_name' => $request['firstName'],
                'last_name' => null,
                'telephone' => null,
                'email' => $request['email'],
                'date_added' => new DateTime(),
                'customer_group_id' => $customerGroupId,
                'status' => 1,
            ];
        } else {
            $stripe_customer = $this->stripe->customers->create([
                'name' => $request['firstName'] . ' ' . $request['lastName'],
                'email' => $request['email'],
            ]);
            $requestUser = [
                'first_name' => $request['firstName'],
                'last_name' => $request['lastName'],
                'telephone' => $request['telephone'],
                'email' => $request['email'],
                'password' => $request['password'],
                'date_added' => new DateTime(),
                'customer_group_id' => $customerGroupId,
                'status' => 1,
            ];
        }
        if (!$user) {
            if ($this->userModel->insertOrIgnore($requestUser)) {
                $user = $this->userModel->where('email', $request['email'])->first();
                $token = TastyJwt::instance()->makeToken($user);
                if ($this->userModel->where('email', $request['email'])->update(['remember_token' => $token]))
                {
                    $user = $this->userModel->where('email', $request['email'])->first();
                    $setting = [
                        'customer_id' => $user->customer_id,
                        'stripe_customer_id' => $stripe_customer->id,
                        'area_id' => null,
                        'push_status' => 1
                    ];
                    $this->customerSettingModel->insertOrIgnore($setting);
                    if ($request['fcmToken'])
                    {
                        $setting = [
                            'customer_id' => $user->customer_id,
                            'device_token' => $request['fcmToken'],
                            'device_type' => $request['deviceType'],
                        ];
                        $this->customerPushModel->insertOrIgnore($setting);
                    }
                    return $this->makeUserResponse($user);
                }
            }
        } else {
            if($request['isFacebook']) {
                $token = TastyJwt::instance()->makeToken($user);
                if ($this->userModel->where('email', $request['email'])->update(['remember_token' => $token]))
                {
                    $user = $this->userModel->where('email', $request['email'])->first();
                    $user['isFacebook'] = true;
                    if ($request['fcmToken'])
                    {
                        $push = $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->first();
                        if ($push) {
                            $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->update(['device_token' => $request['fcmToken']]);
                        } else
                        {
                            $setting = [
                                'customer_id' => $user->customer_id,
                                'device_token' => $request['fcmToken'],
                                'device_type' => $request['deviceType'],
                            ];
                            $this->customerPushModel->insertOrIgnore($setting);
                        }
                    }
                    return $this->makeUserResponse($user);
                }
            }
            abort(400, lang('igniter.api::lang.auth.alert_user_duplicated'));
        }
        abort(400, lang('igniter.api::lang.auth.alert_signup_failed'));
    }

    public function signIn(Request $request) {
        $user = $this->userModel->where('email', $request['email'])->first();
        if ($user) {
            if (TastyJwt::instance()->validatePasswrod($user, $request['password'])) {
                $newToken = TastyJwt::instance()->makeToken($user);
                if ($this->userModel->where('email', $request['email'])->update(['remember_token' => $newToken])) {
                    $user = $this->userModel->where('email', $request['email'])->first();
                    if ($request['fcmToken'])
                    {
                        $push = $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->first();
                        if ($push) {
                            $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->update(['device_token' => $request['fcmToken']]);
                        } else
                        {
                            $setting = [
                                'customer_id' => $user->customer_id,
                                'device_token' => $request['fcmToken'],
                                'device_type' => $request['deviceType'],
                            ];
                            $this->customerPushModel->insertOrIgnore($setting);
                        }
                    }
                    if ($user->status == 0) {
                        abort(400, lang('igniter.api::lang.auth.alert_status_disabled'));
                    }
                    return $this->makeUserResponse($user);
                }
                abort(400, lang('igniter.api::lang.auth.alert_user_not_exist'));
            } else {
                abort(400, lang('igniter.api::lang.auth.alert_user_not_exist'));
            }
        } else {
            abort(400, lang('igniter.api::lang.auth.alert_user_not_exist'));
        }
    }

    public function forgotPassword(Request $request) {

    }

    public function validateToken(Request $request) {
        return TastyJwt::instance()->validateToken($request);
    }

    public function setAddress(Request $request) {
        try {
            $userLocation = $this->geocodeSearchQuery($request->address['postcode']);
            $areaId = "";
            $nearByLocation = Location::searchByCoordinates($userLocation->getCoordinates())->first(function ($location) use ($userLocation) {
                if ($area = $location->searchDeliveryArea($userLocation->getCoordinates())) {
                    Location::updateNearbyArea($area);
                    return $area;
                }
            });
            if (!$nearByLocation) {
                abort(400, lang('igniter.api::lang.location.alert_not_correct_location'));
            }
            if($nearByLocation->location_id != $request->user['locationId']) {
                abort(400, lang('igniter.api::lang.location.alert_not_correct_location'));
            }
            if ($nearByLocation->searchDeliveryArea($userLocation->getCoordinates())) {
                $areaId = $nearByLocation->searchDeliveryArea($userLocation->getCoordinates())->area_id;
            }
            if ($this->customerSettingModel->where('customer_id', $request->user['id'])->first()) {
                $this->customerSettingModel->where('customer_id', $request->user['id'])->update(['area_id' => $areaId]);
            }
            
            $user = $this->userModel->where('customer_id', $request->user['id'])->first();
            // Convert fieldNmae for database
            $address = [
                'customer_id' => $request->user['id'],
                'address_1' => $request->address['address1'],
                'address_2' => $request->address['address2'],
                'city' => $request->address['city'],
                'country_id' => $request->address['countryId'],
                'postcode' => $request->address['postcode'],
                'state' => $request->address['state'],
            ];

            if (count($user->addresses) == 0) {
                $user->addresses()->insertOrIgnore($address);
            } else {
                $user->addresses()->update($address);
            }

            $user = $this->userModel->where('customer_id', $request->user['id'])->first();

            $response = $this->makeUserResponse($user);
            return $response;
    
        } catch (Exception $ex) {
            // abort(400, lang('igniter.api::lang.location.alert_invalid_search_query'));
            abort(400, lang('igniter.api::lang.location.alert_not_correct_location'));
        }
    }


    public function setLocation(Request $request) {
        try {
            if ($this->customerSettingModel->where('customer_id', $request->user['id'])->first()) {
                $this->customerSettingModel->where('customer_id', $request->user['id'])->update(['location_id' => $request->locationId, 'area_id' => null]);
                $user = $this->userModel->where('customer_id', $request->user['id'])->first();
                $user->addresses()->delete();
                $location = $this->locationModel->where('location_id', $request->locationId)->first();
                $response = [
                    'locationId' => $location->location_id,
                    'locationName' => $location->location_name,
                    'offerDelivery' => ($location['options']['offer_delivery'] == 0) ? false : true,
                    'offerCollection' => ($location['options']['offer_collection'] == 0) ? false : true,
                ];
                return $response;
            } else {
                $user = $this->userModel->where('customer_id', $request->user['id'])->first();
                $customerSetting = $this->customerSettingModel->where('customer_id', $user->customer_id)->first();
                if (!$customerSetting) {
                    $stripe_customer = $this->stripe->customers->create([
                        'name' => $request['firstName'],
                        'email' => $request['email'],
                    ]);
                    $setting = [
                        'customer_id' => $user->customer_id,
                        'stripe_customer_id' => $stripe_customer->id,
                        'area_id' => null,
                        'push_status' => 1
                    ];
                    $this->customerSettingModel->insertOrIgnore($setting);
                }
                if ($request['fcmToken'] != '')
                {
                    $push = $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->first();
                    if ($push) {
                        $this->customerPushModel->where('customer_id', $user->customer_id)->where('device_type', $request['deviceType'])->update(['device_token' => $request['fcmToken']]);
                    } else
                    {
                        $setting = [
                            'customer_id' => $user->customer_id,
                            'device_token' => $request['fcmToken'],
                            'device_type' => $request['deviceType'],
                        ];
                        $this->customerPushModel->insertOrIgnore($setting);
                    }
                }
                $this->customerSettingModel->where('customer_id', $request->user['id'])->update(['location_id' => $request->locationId, 'area_id' => null]);
                $user = $this->userModel->where('customer_id', $request->user['id'])->first();
                $user->addresses()->delete();
                $location = $this->locationModel->where('location_id', $request->locationId)->first();
                $response = [
                    'locationId' => $location->location_id,
                    'locationName' => $location->location_name,
                    'offerDelivery' => ($location['options']['offer_delivery'] == 0) ? false : true,
                    'offerCollection' => ($location['options']['offer_collection'] == 0) ? false : true,
                ];
                return $response;
            }
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
    }

    public function geocodeSearchQuery($searchQuery)
    {
        $collection = Geocoder::geocode($searchQuery);

        if (!$collection OR $collection->isEmpty()) {
            // throw new ApplicationException(lang('igniter.api::lang.location.alert_invalid_search_query'));
            throw new ApplicationException(lang('igniter.api::lang.location.alert_not_correct_location'));
        }

        $userLocation = $collection->first();
        if (!$userLocation->hasCoordinates()) {
            // throw new ApplicationException(lang('igniter.api::lang.location.alert_invalid_search_query'));
            throw new ApplicationException(lang('igniter.api::lang.location.alert_not_correct_location'));
        }

        Location::updateUserPosition($userLocation);
        return $userLocation;
    }

    public function pushStatus(Request $request) {
        try {
            if($this->customerSettingModel->where('customer_id', $request->user['id'])->first()) {
                $this->customerSettingModel->where('customer_id', $request->user['id'])->update(['push_status' => $request->isPush]);
                return 'true';
            }

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return 'false';
    }
}
