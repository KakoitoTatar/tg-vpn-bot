<?php
declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SendReviewChatLink extends BotAction
{

    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage(
            'Отзывы:',
            [
                'reply_markup' => InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('Перейти', url: 't.me/fuckin_tatar'))
            ]
        );
    }
}