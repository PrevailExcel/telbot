<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use App\Conversations\QuizConversation;
use App\Conversations\PrivacyConversation;
use App\Conversations\HighscoreConversation;
use App\Http\Middleware\PreventDoubleClicks;
use App\Services\TelegramService;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle(TelegramService $telegramService)
    {
        return $telegramService->handle();       
    }
}