<?php

namespace Igniter\Api\Services;

// Libary Import
use Illuminate\Support\Facades\Hash;
use Igniter\Flame\Traits\Singleton;
use Illuminate\Support\Facades\Log;

/**
 * TastyJwt -- customized JWT plugin Service
 */
class TastyJwt {
    use Singleton;

    public function makeJwtToken() {

    }

    public function makeHashPassword($password) {
        return Hash::make($password);
    }

    public function makeToken($user) {
        $userString = $user->customer_id . $user->email;
        return Hash::make($userString);
    }

    public function validatePasswrod($user, $password) {
        return Hash::check($password, $user->password);
    }

    public function validateToken($request) {
        $userModelClass = 'Admin\Models\Customers_model';
        $userModel = new $userModelClass;
        if ($request->bearerToken() == null) {
            return 0;
        }
        $user = $userModel->where('remember_token', $request->bearerToken())->first();
        if ($user) {
            return 1;
        }
        return 0;
    }
}