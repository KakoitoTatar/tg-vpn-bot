<?php

declare (strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Auth\JwtAuth;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class ShowConnectionsAction extends BotAction
{
    /**
     * @var Client
     */
    private Client $client;

    private const RATE_TEMPLATE = <<<END
    Тариф: *%rate%*
    Дейтвует до: *%expireAt%*
    Ключ доступа:
    `%key%`
    END;

    public function __construct(
        private readonly JwtAuth                $jwtAuth,
        private readonly ServerRequestInterface $request
    )
    {
        $this->client = new Client([
            'base_uri' => 'http://nginx:83'
        ]);
    }

    public function __invoke(Nutgram $bot): void
    {
        /**
         * @var \App\Domain\Client\Client $client
         */
        $client = $this->request->getAttribute('client');

        $connectionsRaw = $this->client->get('/api/connections', [
            'headers' => ['Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])]
        ]);

        $connections = json_decode($connectionsRaw->getBody()->getContents(), true);

        $bot->sendMessage('🛒');

        foreach ($connections as $connection) {
            $rateMessage = str_replace(
                ['%rate%', '%expireAt%', '%key%'],
                [
                    $connection['name'],
                    $connection['activeTo'],
                    $connection['key']
                ],
                self::RATE_TEMPLATE
            );

            $bot->sendMessage(
                $rateMessage,
                [
                    'parse_mode' => 'markdown',
                    'reply_markup' => InlineKeyboardMarkup::make()
                        ->addRow(InlineKeyboardButton::make(
                            text: '💲 Продлить тариф',
                            callback_data: 'prolong:' . $connection['id']
                        ))
                        ->addRow(InlineKeyboardButton::make(
                            text: '📝 Инструкция по установке',
                            callback_data: 'guide:' . $connection['id']
                        ))
                ]
            );
        }

        $bot->sendMessage(
            '‼️ Не публикуйте ваш ключ в интернете и не передавайте его третьим лицам',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                    ->addRow(KeyboardButton::make('⬅️ На главную'))
            ]
        );
    }
}
