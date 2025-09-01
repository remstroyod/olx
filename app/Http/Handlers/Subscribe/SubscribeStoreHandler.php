<?php
namespace App\Http\Handlers\Subscribe;

use App\Http\Handlers\BaseHandler;
use App\Http\Requests\SubscribeStoreRequest;
use App\Models\Subscribe;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Hash;

class SubscribeStoreHandler extends BaseHandler
{

    use HttpResponses;

    public function process(SubscribeStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {

            $email = $request->get('email');
            $url = $request->get('url');

            $user = User::updateOrCreate(
                [
                    'email' => $email
                ],
                [
                    'name' => $email,
                    'email' => $email,
                    'password' => Hash::make($email),
                ]
            );

            $subscribe = $user->subscribe()->updateOrCreate(
                [
                    'url' => $url,
                ],
                $request->validated()
            );

            if (! $user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
                return $this->error(message: __('To subscribe you must confirm your email. Please check your mail.'));
            }

            return $this->success($subscribe, __('Subscription successfully created'));

        } catch (\Throwable $e) {

            $this->setErrors($e->getMessage());
            return $this->error(message: $this->getErrors());

        }
    }

}
