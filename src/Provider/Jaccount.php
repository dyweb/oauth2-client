<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken as AccessToken;

class Jaccount extends AbstractProvider
{
    public $scopes = array(
        ''
    );

    public $responseType = 'json';
    public function urlAuthorize()
    {
        return 'https://jaccount.sjtu.edu.cn/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://jaccount.sjtu.edu.cn/oauth2/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.sjtu.edu.cn/v1/me/profile?' . http_build_query([
                'access_token' => $token->accessToken
            ]);
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = ((array) $response)["entities"][0];
        $school_name = '上海交通大学';

        $uid = $response->account;
        $name = $response->name;
        $student_number = $response->code;

        $user = new User;
        $user->exchangeArray(array(
            'uid' => $uid,
            'name' => $name,
            'school_name' => $school_name,
            'student_number' => $student_number,
            'code' => $student_number
        ));
        return $user;
    }

    public function getUserUid(AccessToken $token)
    {
        static $response = null;

        if ($response == null) {
            $client = $this->getHttpClient();
            $client->setBaseUrl('https://api.sjtu.edu.cn/v1/me/profile?access_token=' . $token);
            $request = $client->get()->send();
            $response = json_decode($request->getBody());
        }

        return $this->userUid($response, $token);
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $token->uid = $response->account;
        return $response->account;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->account . "@sjtu.edu.cn";
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->name;
    }
}