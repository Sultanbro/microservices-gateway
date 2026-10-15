<?php
namespace App\Http\Services\Authenticate;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class KeyCloakService implements KeyCloakServiceInterface
{
    public function getUserInfo($token)
    {
        $url = config('services.keycloak.userinfo_url');
        if (!$url) return false;
        try {
            $response = Http::withToken($token)->acceptJson()->timeout(10)->withoutRedirecting()->get($url);
        } catch (ConnectionException $exception) {
            return false;
        }
        $subject = $response->json('sub');
        return $response->successful() && is_string($subject) && $subject !== '' && !isset($response['error']);
    }
}
