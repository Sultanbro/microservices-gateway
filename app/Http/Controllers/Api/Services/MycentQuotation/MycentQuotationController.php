<?php

namespace App\Http\Controllers\Api\Services\MycentQuotation;

use App\Http\Controllers\Controller;
use App\Traits\Request\RequestService;
use App\Traits\Request\RouteData;
use Illuminate\Http\Request;

class MycentQuotationController extends Controller
{
    use RequestService;
    use RouteData;
    public $baseUri;
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.mycent-quotation.base_uri');
        $this->secret = config('services.mycent-quotation.secret');
    }
}
