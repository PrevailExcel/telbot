<?php

namespace App\Services;

use BotMan\BotMan\BotMan;
use App\Http\Middleware\PreventDoubleClicks;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class TelegramService
 * @package App\Services
 */
class TelegramService
{

    /**
     * This method takes initiates the bot and sends out the notifications.
     */
    public function handle(Collection|array $users)
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

        $channels = [env('TELEGRAM_CHANNEL_ID', "-1002101301983")];
        // $channels = [env('TELEGRAM_CHANNEL_ID', "-1002087236569")];

        $botman->hears('Hello', function ($bot) {
            $bot->reply('Hello!');
            $bot->ask('Whats your name?', function ($answer, $bot) {
                $bot->say('Welcome ' . $answer->getText());
            });
        });

        $botman->hears('start|/start', function (BotMan $bot) {
            $bot->reply('Hello!');
            $bot->typesAndWaits(2);
            $bot->ask('Whats your name?', function ($answer, $bot) {
                $bot->say('Welcome ' . $answer->getText());
            });
        });

        //Title message: Showing number of new users, active users and total users
        $message = OutgoingMessage::create('You have <b>' . count($users['list']) . "</b> New Users \n \nActive Users: <b>" . $users['active'] . "</b> \nTotal Users: <b>" . $users['total'] . "</b> \n");
        $botman->say($message, $channels, \BotMan\Drivers\Telegram\TelegramDriver::class, ["parse_mode" => "HTML"]);

        // Create message for each user and send to channel
        foreach ($users['list'] as $user) {

            // Build message object
            $confirmed = $user['emailConfirmed'] ? 'Yes' : 'No';
            $referal = $user['isReferral'] ? 'Yes' : 'No';
            $date = Carbon::parse($user['registrationTimestamp'])->format('d M Y');

            $messageString = "NEW USER \n \nUsername: <b>" . $user['login'] . "</b> \nWallet: <b>" . $user['wallet'] . "</b> \nReferral Code: <b>" . $user['referralCode'] . "</b> \nDate: <b>" . $date . "</b> \nConfirmed Email: <b>" . $confirmed . "</b> \nUser was referred: <b>" . $referal . "</b>";

            $message = OutgoingMessage::create($messageString);
            $botman->say($message, $channels, \BotMan\Drivers\Telegram\TelegramDriver::class, ["parse_mode" => "HTML"]);
        }

        $botman->listen();
    }
}
