<?php

declare(strict_types=1);

namespace App\Traits\Request;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait RequestService
{
    public function request($method, $requestUrl, $formParams = [], $headers = [])
    {
        if (!$this->baseUri || !$this->secret) {
            return response()->json(['error' => 'Upstream service is not configured'], 503);
        }
        $headers['Authorization'] = $this->secret;
        $options = ['headers' => $headers];
        $options[in_array(strtoupper($method), ['GET', 'HEAD']) ? 'query' : 'form_params'] = $formParams;
        try {
            $upstream = Http::timeout(15)->withOptions(['connect_timeout' => 5])->withoutRedirecting()
                ->send($method, rtrim($this->baseUri, '/') . '/' . ltrim($requestUrl, '/'), $options);
        } catch (ConnectionException $exception) {
            return response()->json(['error' => 'Upstream service unavailable'], 502);
        }
        if (!$upstream->successful()) {
            $status = $upstream->status();
            return response()->json(['error' => 'Upstream request failed'], $status >= 400 && $status <= 599 ? $status : 502);
        }
        return response($upstream->body(), $upstream->status())->header('Content-Type', 'application/json');
    }

    public function index()
    {
        return $this->request(request()->method(), $this->getLinkNoApi(), request()->query());
    }

    public function show($id)
    {
        return $this->request(request()->method(), $this->getUriNoIdAndApi() . rawurlencode((string) $id), request()->query());
    }

    public function store(Request $request)
    {
        return $this->request($request->method(), $this->getLinkNoApi(),
            array_merge($request->all(), ['created_by' => auth()->id(), 'updated_by' => auth()->id()]));
    }

    public function update(Request $request, $id)
    {
        $data = $request->except('created_by');
        return $this->request($request->method(), $this->getUriNoIdAndApi() . rawurlencode((string) $id),
            array_merge($data, ['updated_by' => auth()->id()]));
    }

    public function destroy($id)
    {
        return $this->request(request()->method(), $this->getUriNoIdAndApi() . rawurlencode((string) $id));
    }
}
