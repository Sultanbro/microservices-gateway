<?php
namespace App\Http\Services\Authenticate;
interface KeyCloakServiceInterface { public function getUserInfo($token); }
