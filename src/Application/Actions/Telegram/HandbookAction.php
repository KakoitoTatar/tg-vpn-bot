<?php

namespace App\Application\Actions\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class HandbookAction extends BotAction
{
    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage('📁');
        $bot->sendMessage(
            '*Полезные материалы*' . PHP_EOL
            . 'Выберите один из разделов:',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                    ->addRow(KeyboardButton::make('⁉️ F.A.Q'))
                    ->addRow(KeyboardButton::make('💬 Обратиться в поддержку'))
                    ->addRow(KeyboardButton::make('⬅️ На главную'))
            ]
        );
    }
}