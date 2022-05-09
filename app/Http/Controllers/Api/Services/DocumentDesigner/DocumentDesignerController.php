<?php


namespace App\Http\Controllers\Api\Services\DocumentDesigner;


use App\Http\Controllers\Controller;
use App\Traits\Request\RequestService;
use App\Traits\Request\RouteData;
use Illuminate\Http\Request;

class DocumentDesignerController extends Controller
{
    use RequestService;
    use RouteData;
    public $baseUri;
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.document_designer.base_uri');
        $this->secret = config('services.document_designer.secret');
    }


}
