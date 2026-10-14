<?php

declare(strict_types = 1);

namespace App\Traits\Request;

use Illuminate\Http\Response;

use function response;

trait RouteData
{
    public function getMethod()
    {
        return request()->route()->methods[0];
    }

    public function getLinkNoApi()
    {
        return stristr(request()->route()->uri, '/');
    }

    public function getUriNoIdAndApi()
    {
        return stristr($this->getUriNoId(), '/');
    }

    public function getUriNoId()
    {
        return stristr(request()->route()->uri, '{', true);
    }
}
