<?php

declare (strict_types=1);

namespace App\Application\Actions\Telegram;

use SergiX44\Nutgram\Nutgram;

abstract class BotAction
{
    /**
     * @param Nutgram $bot
     * @return void
     */
    abstract public function __invoke(Nutgram $bot): void;
}
