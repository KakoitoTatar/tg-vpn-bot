<?php

declare (strict_types=1);

namespace App\Application\Actions\Telegram;
use App\Domain\Client\Client;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ServerRequestInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

readonly class StartBot
{
    public function __construct(
        private ServerRequestInterface $request
    ){}

    /**
     * @param Nutgram $bot
     * @return void
     */
    public function __invoke(Nutgram $bot): void
    {
        /**
         * @var Client $client
         */
        $client = $this->request->getAttribute('client');
        $bot->sendMessage('👋');
        $bot->sendMessage(
            'Привет, *' . $client->getName() . '*',
            ['parse_mode' => 'markdown']
        );
        $bot->sendMessage(
            'Я бот “*название*”, помогу тебе получить доступ к закрытым ресурсам на самых выгодных условиях подписки.' . PHP_EOL . PHP_EOL
            . '*Наши преимущества:*' . PHP_EOL
            . '✅ Без ограничений по скорости и трафику' . PHP_EOL
            . '🛡️ Высокая степень анонимности и защиты в сети' . PHP_EOL
            . '📱 Доступен на всех типах устройств' . PHP_EOL . PHP_EOL
            . 'Мы не собираем данные пользователей т.к. используем приложение Outline не собирающее данные с открытым исходным кодом',
            [
                'parse_mode' => 'markdown',
            ]
        );
        $bot->sendMessage(
            '*Основные разделы бота:*',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                    ->addRow(KeyboardButton::make('💲 Выбрать тариф'))
                    ->addRow(KeyboardButton::make('👁️ Мой тариф'))
                    ->addRow(KeyboardButton::make('💰 Партнёрская программа'))
                    ->addRow(KeyboardButton::make('⁉️ Полезные материалы'))
            ]
        );
    }
}
