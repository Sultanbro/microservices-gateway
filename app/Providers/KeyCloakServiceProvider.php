<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Http\Services\Authenticate\KeyCloakService;
use App\Http\Services\Authenticate\KeyCloakServiceInterface;
class KeyCloakServiceProvider extends ServiceProvider
{
    public function register() { $this->app->bind(KeyCloakServiceInterface::class, KeyCloakService::class); }
}
