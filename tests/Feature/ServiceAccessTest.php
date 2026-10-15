<?php
namespace Tests\Feature;
use App\Http\Middleware\AuthenticateAccess;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ServiceAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Route::get('/test-service-access', fn () => response()->json(['ok' => true]))->middleware(AuthenticateAccess::class);
        config(['services.gateway.allowed_secrets' => ['demo-service-secret']]);
    }
    public function test_valid_service_secret_is_accepted(): void
    {
        $this->withHeader('Authorization', 'demo-service-secret')->getJson('/test-service-access')->assertOk();
    }
    public function test_missing_and_wrong_service_secret_are_rejected(): void
    {
        $this->getJson('/test-service-access')->assertUnauthorized();
        $this->withHeader('Authorization', 'wrong')->getJson('/test-service-access')->assertUnauthorized();
    }
    public function test_empty_allowlist_denies_access(): void
    {
        config(['services.gateway.allowed_secrets' => []]);
        $this->withHeader('Authorization', 'demo-service-secret')->getJson('/test-service-access')->assertUnauthorized();
    }
}
