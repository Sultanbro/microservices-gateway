<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GatewayTest extends TestCase
{
    private int $status = 200;
    private int $identityStatus = 200;
    private bool $disconnected = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('users', function ($table) { $table->id(); $table->string('username'); });
        Schema::create('user_tokens', function ($table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->string('access_token');
        });
        DB::table('users')->insert(['id' => 7, 'username' => 'demo']);
        DB::table('user_tokens')->insert(['user_id' => 7, 'access_token' => 'demo-access']);
        config(['services.keycloak.userinfo_url' => 'https://identity.example.invalid/userinfo',
            'services.document_designer.base_uri' => 'https://documents.example.invalid',
            'services.document_designer.secret' => 'demo-service-secret',
            'services.mycent-quotation.base_uri' => 'https://quotation.example.invalid',
            'services.mycent-quotation.secret' => 'demo-quotation-secret']);
        Http::fake(function ($request) {
            if ($request->url() === 'https://identity.example.invalid/userinfo') {
                return Http::response(['sub' => 'demo-user'], $this->identityStatus);
            }
            if ($this->disconnected) throw new ConnectionException('Private address and credentials');
            return Http::response($this->status >= 400 ? ['private' => 'internal-service-details'] : ['ok' => true], $this->status);
        });
        $this->withToken('demo-access');
    }

    public function test_list_forwards_query_and_service_credentials(): void
    {
        $this->getJson('/api/handbook?page=2')->assertOk()->assertJsonPath('ok', true);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://documents.example.invalid/handbook')
            && $request['page'] == 2 && $request->hasHeader('Authorization', 'demo-service-secret'));
    }

    public function test_creation_preserves_status_and_prevents_author_spoofing(): void
    {
        $this->status = 201;
        $this->postJson('/api/handbook', ['name' => 'Demo', 'created_by' => 999, 'updated_by' => 999])->assertCreated();
        Http::assertSent(fn ($request) => $request->url() === 'https://documents.example.invalid/handbook'
            && (int) $request['created_by'] === 7 && (int) $request['updated_by'] === 7 && $request['name'] === 'Demo');
    }

    public function test_patch_keeps_method_and_resource_id(): void
    {
        $this->patchJson('/api/handbook/42', ['name' => 'Updated', 'created_by' => 999])->assertOk();
        Http::assertSent(fn ($request) => $request->url() === 'https://documents.example.invalid/handbook/42'
            && $request->method() === 'PATCH' && (int) $request['updated_by'] === 7 && !isset($request['created_by']));
    }

    public function test_delete_preserves_empty_204_response(): void
    {
        $this->status = 204;
        $this->deleteJson('/api/handbook/42')->assertNoContent();
        Http::assertSent(fn ($request) => $request->url() === 'https://documents.example.invalid/handbook/42' && $request->method() === 'DELETE');
    }

    public function test_nested_route_and_quotation_select_correct_upstream(): void
    {
        $this->postJson('/api/handbook/joint', ['name' => 'Demo'])->assertOk();
        $this->postJson('/api/full-editor', ['name' => 'Demo'])->assertOk();
        Http::assertSent(fn ($request) => $request->url() === 'https://documents.example.invalid/handbook/joint');
        Http::assertSent(fn ($request) => $request->url() === 'https://quotation.example.invalid/full-editor'
            && $request->hasHeader('Authorization', 'demo-quotation-secret'));
    }

    public function test_upstream_errors_do_not_expose_internal_details(): void
    {
        $this->status = 422;
        $this->postJson('/api/handbook', [])->assertUnprocessable()->assertExactJson(['error' => 'Upstream request failed']);
    }

    public function test_network_failure_is_a_controlled_502(): void
    {
        $this->disconnected = true;
        $this->getJson('/api/handbook')->assertStatus(502)->assertExactJson(['error' => 'Upstream service unavailable']);
    }

    public function test_missing_configuration_returns_503(): void
    {
        config(['services.document_designer.secret' => '']);
        $this->getJson('/api/handbook')->assertStatus(503);
        Http::assertSentCount(1);
    }

    public function test_unknown_token_does_not_contact_any_service(): void
    {
        $this->withToken('unknown')->getJson('/api/handbook')->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_identity_failure_does_not_reach_upstream(): void
    {
        $this->identityStatus = 503;
        $this->getJson('/api/handbook')->assertUnauthorized();
        Http::assertSentCount(1);
    }

    public function test_upstream_redirect_is_not_exposed_as_success(): void
    {
        $this->status = 302;
        $this->getJson('/api/handbook')->assertStatus(502);
    }
}
