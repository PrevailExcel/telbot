<?php

namespace App\Services;

use BotMan\BotMan\BotMan;
use App\Http\Middleware\PreventDoubleClicks;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;
use Illuminate\Support\Collection;

/**
 * Class TelegramService
 * @package App\Services
 */
class TelegramService
{

    /**
     * Place your BotMan logic here.
     */
    public function handle(Collection|null $users)
    {
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);

        $config = [
            'user_cache_time' => 720,

            'config' => [
                'conversation_cache_time' => 720,
            ],

            // Your driver-specific configuration
            "telegram" => [
                "token" => env('TELEGRAM_TOKEN'),
            ]
        ];

        // // Create BotMan instance
        $botman = BotManFactory::create($config, new LaravelCache());

        $botman->middleware->captured(new PreventDoubleClicks);

        $channels = env('TELEGRAM_CHANNEL_ID');

        // Create message for each user and send to channel
        foreach ($users as $user) {

            // Build message object
            $messageString = "username: " . $user->login .
                "\n wallet: " . $user->wallet .
                "\n Confirmed Email: " . $user->emailConfirmed .
                "\n Is Referral: " . $user->isReferral .
                "\n Referral Code: " . $user->referralCode .
                "\n registrationTimestamp: " . $user->registrationTimestamp;

            $botman->say($messageString, $channels);
        }

        $botman->hears('Hello', function ($bot) {
            $bot->reply('Hello!');
            $bot->ask('Whats your name?', function ($answer, $bot) {
                $bot->say('Welcome ' . $answer->getText());
            });
        });

        $botman->listen();
    }
}
