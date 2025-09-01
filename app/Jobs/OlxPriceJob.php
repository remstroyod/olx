<?php

namespace App\Jobs;

use App\Mail\PriceChangedMail;
use App\Models\Subscribe;
use App\Services\OlxParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OlxPriceJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $tries = 5;
    public $timeout = 120;


    public function __construct()
    {
        //
    }

    public function handle()
    {

        $olx = new OlxParser();
        $pingUrls = Subscribe::all()->groupBy('url');

        $pingUrls->each(function ($item, $url) use ($olx)
        {

            $data = $olx->parseProduct($url);

            if($data->status)
            {
                $responseData = json_decode($data->getContent(), true);
                $newPrice = $responseData['data']['price'];

                $item->each(function ($item) use ($newPrice) {
                    $user = $item->user;

                    if (! $user) {
                        return;
                    }

                    if ($user->hasVerifiedEmail() && $item->price != $newPrice) {
                        Mail::to($user->email)->send(
                            new PriceChangedMail($item->url, $item->price, $newPrice, $item->currency)
                        );

                        $item->update(['price' => $newPrice]);
                    }
                });
            }

        });

    }

    public function failed(Throwable $exception)
    {
        Log::critical($exception->getMessage());
    }
}
