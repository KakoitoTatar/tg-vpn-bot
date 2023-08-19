<?php

namespace App\Application\Actions\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class MainMenuAction extends BotAction
{
    /**
     * @param Nutgram $bot
     * @return void
     */
    public function __invoke(Nutgram $bot): void
    {
        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true);
        $keyboard->addRow(KeyboardButton::make('👁️ Мой тариф'),KeyboardButton::make('💲 Выбрать тариф'));
        $keyboard->addRow(KeyboardButton::make('💰 Партнёрская программа'));
        $keyboard->addRow(KeyboardButton::make('⁉️ Полезные материалы'));

        $bot->sendMessage(
            'Главная 🏠',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => $keyboard
            ]
        );
    }
}
