<?php

namespace App\Application\Actions\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class SendCallToBuyAction extends BotAction
{
    /**
     * @param Nutgram $bot
     * @return void
     */
    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage('😔');
        $bot->sendMessage(
            'К сожалению у вас нет активных подписок. Но вы можете приобрести тариф, нажав на кнопку: “💲 Выбрать тариф”',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                    ->addRow(KeyboardButton::make('💲 Выбрать тариф'))
                    ->addRow(KeyboardButton::make('⬅️ На главную'))
            ]
        );
    }
}