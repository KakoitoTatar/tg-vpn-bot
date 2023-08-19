<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Auth\JwtAuth;
use App\Domain\Payments\PaymentStatus;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class AffiliateProgramAction extends BotAction
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param JwtAuth $jwtAuth
     * @param ServerRequestInterface $request
     */
    public function __construct(
        private readonly JwtAuth                $jwtAuth,
        private readonly ServerRequestInterface $request
    )
    {
        $this->client = new Client([
            'base_uri' => 'http://nginx:83'
        ]);
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __invoke(Nutgram $bot): void
    {
        $client = $this->request->getAttribute('client');

        $extClientRaw = $this->client->get('/api/client', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])
            ]
        ]);

        $extClient = json_decode($extClientRaw->getBody()->getContents(), true);
        $isNewClient = (bool)$extClient['isNewClient'];
        $freeDays = (int) $extClient['freeDays'];
        $promocode = $extClient['referralCode'];

        $connectionsRaw = $this->client->get('/api/connections', [
            'headers' => ['Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])]
        ]);

        $connections = json_decode($connectionsRaw->getBody()->getContents(), true);

        $bot->sendMessage('💰');
        $bot->sendMessage(
            'Партнерская программа' . PHP_EOL
            . 'Приглашай друзей и получай бонусные дни подписки.' . PHP_EOL . PHP_EOL
            . '[1] Пригласи друга' . PHP_EOL
            . '[2] Попроси указать твой код в промокодах' . PHP_EOL
            . '[3] Получай бонусные дни ✨' . PHP_EOL . PHP_EOL
            . 'Твой код: ' . $promocode,
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                    ->addRow(KeyboardButton::make('💲 Выбрать тариф'))
                    ->addRow(KeyboardButton::make('⬅️ На главную'))
            ]
        );

        if ($isNewClient) {
            $bot->sendMessage('Воспользоваться бонусными днями можно в случае если вы приобретали или имеете активный тариф ⚠️ ');
        } elseif ($connections === []) {
            if ($freeDays === 0) {
                $bot->sendMessage(
                    'Активные тарифы:' . PHP_EOL
                    . '*Отсутствуют*' . PHP_EOL . PHP_EOL
                    . 'Бонусных дней: ' . $freeDays
                );
            } else {
                $bot->sendMessage(
                    'Активные тарифы:' . PHP_EOL
                    . '*Отсутствуют*' . PHP_EOL . PHP_EOL
                    . 'Бонусных дней: ' . $freeDays,
                    [
                        'parse_mode' => 'markdown',
                        'reply_markup' => InlineKeyboardMarkup::make()
                            ->addRow(InlineKeyboardButton::make(
                                'Получить ' . $freeDays . 'бонусных дней',
                                callback_data: 'freeDays:new'
                            ))
                    ]
                );
            }
        } else {
            $message = 'Активные тарифы:' . PHP_EOL;
            $keyboard = InlineKeyboardMarkup::make();

            foreach ($connections as $key => $connection) {
                $message .= '*' . $key . '.' . $connection['name'] . ' (до ' . $connection['activeTo'] . ')*' . PHP_EOL;
                $keyboard->addRow(InlineKeyboardButton::make(
                    'Добавить ' . $freeDays . ' бонусных дней к тарифу №' . $key,
                    callback_data: 'freeDays:' . $connection['id']
                ));
            }

            $message .= PHP_EOL . 'Бонусных дней: *' . $freeDays . '*';

            if ($freeDays === 0) {
                $keyboard = InlineKeyboardMarkup::make();
            }

            $bot->sendMessage($message,
                [
                    'parse_mode' => 'markdown',
                    'reply_markup' => $keyboard
                ]
            );
        }

    }
}