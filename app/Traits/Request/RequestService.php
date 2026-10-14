<?php

declare(strict_types = 1);

namespace App\Traits\Request;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

trait RequestService
{
    /**
     * @param       $method
     * @param       $requestUrl
     * @param array $formParams
     * @param array $headers
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $requestUrl, $formParams = [], $headers = [])
    {
        $client = new Client([
            'base_uri' => $this->baseUri
        ]);

        if (isset($this->secret)) {
            $headers['Authorization'] = $this->secret;
        }

        $response = $client->request($method, $requestUrl,
            [
                'form_params' => $formParams,
                'headers' => $headers
            ]
        );

        return $response->getBody()->getContents();
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        try {
            return $this->successResponse($this->request($this->getMethod(), $this->getLinkNoApi()));
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show($id)
    {
        try {
            return $this->successResponse($this->request($this->getMethod(), $this->getUriNoIdAndApi()."$id"));
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(Request $request)
    {
        try {
            return $this->successResponse($this->request($this->getMethod(), $this->getLinkNoApi(), $request->all() + ['created_by' => auth()->id(), 'updated_by' => auth()->id(),]));
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(Request $request, $id)
    {
        try {
            return $this->successResponse($this->request($this->getMethod(), $this->getUriNoIdAndApi()."$id", $request->all() + ['updated_by' => auth()->id(),]));
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function destroy($id)
    {
        try {
            return $this->successResponse($this->request($this->getMethod(), $this->getUriNoIdAndApi()."$id"));
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }
}
