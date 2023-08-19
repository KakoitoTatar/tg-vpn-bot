<?php

namespace App\Application\Actions\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class SendSupportLink extends BotAction
{
    /**
     * @param Nutgram $bot
     * @return void
     */
    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage('👩‍💻',
            ['reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true)
                ->addRow(KeyboardButton::make('⬅️ На главную'))
            ]);
        $bot->sendMessage('Ничего страшного!' . PHP_EOL
            . 'Напишите нам в поддержку и мы постараемся решить вашу проблему',
            [
                'reply_markup' => InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('💬 Обратиться в поддержку', 't.me/fuckin_tatar'))
            ]
        );
    }
}