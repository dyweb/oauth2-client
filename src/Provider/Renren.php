<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken as AccessToken;

class Renren extends AbstractProvider
{
    public $scopes = array(
        ''
    );

    public $responseType = 'json';
    public function urlAuthorize()
    {
        return 'https://graph.renren.com/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://graph.renren.com/oauth/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.renren.com/v2/user/get?' . http_build_query([
            'access_token' => $token->accessToken
        ]);
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;
        $education = $response['response']->education;
        if (is_array($education) AND count($education) > 0) {
            $school_name = $education[0]->name;
        } else {
            $school_name = '';
        }
        $uid = $response['response']->id;
        $name = $response['response']->name;
        $user = new User;
        $user->exchangeArray(array(
            'uid' => $uid,
            'name' => $name,
            'school_name' => $school_name
        ));
        return $user;
    }

    public function getUserUid(AccessToken $token)
    {
        static $response = null;

        if ($response == null) {
            $client = $this->getHttpClient();
            $client->setBaseUrl('https://graph.qq.com/oauth2.0/me?access_token=' . $token);
            $request = $client->get()->send();
            if (preg_match('/callback\((.+?)\)/', $request->getBody(), $match)) {
                $response = json_decode($match[1]);
            }
        }

        return $this->userUid($response, $token);
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $token->uid = $response->openid;
        return $response->openid;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->name;
    }
}