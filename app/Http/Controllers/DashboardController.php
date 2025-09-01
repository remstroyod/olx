<?php

namespace App\Http\Controllers;
use App\Http\Handlers\Subscribe\SubscribeStoreHandler;
use App\Http\Requests\CheckUrlRequest;
use App\Http\Requests\SubscribeStoreRequest;
use App\Services\OlxParser;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    use HttpResponses;

    private $olx;

    public function __construct(OlxParser $olx)
    {
        $this->olx = $olx;
    }

    public function index(Request $request)
    {

        return view('dashboard');

    }

    public function store(SubscribeStoreRequest $request, SubscribeStoreHandler $handler)
    {

        return $handler->process($request);

    }

    public function checkLink(CheckUrlRequest $request)
    {

        $data = $this->olx->parseProduct($request->get('url'));

        if($data->isSuccessful())
        {
            return $this->success([
                'html' => view('partials.dashboard.subscribe', ['item' => $data->getData()->data])->render()
            ]);
        }

        return $this->error(message: $data->getData()->message);
    }

}
