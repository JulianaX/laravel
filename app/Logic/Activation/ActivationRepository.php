<?php

namespace App\Logic\Activation;

use App\Models\Activation;
use App\Models\User;
use App\Notifications\SendActivationEmail;
use App\Traits\CaptureIpTrait;
use Carbon\Carbon;

class ActivationRepository
{

    public function createTokenAndSendEmail(User $user)
    {

        $activations = Activation::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subHours(config('settings.timePeriod')))
            ->count();

        if ($activations >= config('settings.maxAttempts')) {
            return true;
        }

        //якщо юзер змінив email
        if ($user->activated) {

            $user->update([
                'activated' => false
            ]);

        }

        $activation = self::createNewActivationToken($user);

        self::sendNewActivationEmail($user, $activation->token);

    }

    public function createNewActivationToken(User $user) {

        $ipAddress              = new CaptureIpTrait;
        $activation             = new Activation;
        $activation->user_id    = $user->id;
        $activation->token      = str_random(64);
        $activation->ip_address = $ipAddress->getClientIp();
        $activation->save();

        return $activation;

    }

    public function sendNewActivationEmail(User $user, $token) {

        $user->notify(new SendActivationEmail($token));

    }

    public function deleteExpiredActivations()
    {

        Activation::where('created_at', '<=', Carbon::now()->subHours(72))->delete();

    }
}