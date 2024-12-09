<?php

namespace App\Http\Middleware;

use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\BotMan;

class BotManMiddleware implements Received
{
    /**
     * Handle the received payload.
     *
     * @param array $payload
     * @param callable $next
     * @param BotMan $bot
     * @return mixed
     */
    public function received($payload, $next, BotMan $bot)
    {
        // Check if the user is authenticated
        if (auth()->check()) {
            // Store user ID in BotMan's user storage
            $bot->userStorage()->save([
                'user_id' => auth()->user()->id,
            ]);
        }

        return $next($payload);
    }
}
