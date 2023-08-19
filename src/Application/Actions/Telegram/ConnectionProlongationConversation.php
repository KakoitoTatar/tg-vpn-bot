<?php

namespace App\Application\Actions\Telegram;

use App\Application\Auth\JwtAuth;
use App\Domain\Payments\PaymentStatus;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class ConnectionProlongationConversation extends Conversation
{
    /** @var ?int */
    public ?int $rateId = null;

    /** @var int */
    public ?int $connectionId;

    /**
     * @var int|null
     */
    public ?int $paymentId = null;

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

    public function start(Nutgram $bot)
    {
        $this->setSkipHandlers(true);
        $client = $this->request->getAttribute('client');
        $this->connectionId = (int) explode(':', $bot->callbackQuery()->data)[1];

        $connectionRaw = $this->client->get('/api/connections/' . $this->connectionId, [
            'headers' => ['Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])]
        ])->getBody()->getContents();
        $connection = json_decode($connectionRaw, true);

        $this->rateId = $connection['rateId'];

        $rateRaw = $this->client->get('/api/rates/' . $this->rateId, [
            'headers' => ['Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])]
        ])->getBody()->getContents();

        $rate = json_decode($rateRaw, true);

        $this->next('initiatePayment');
        $bot->sendMessage(
            '*' . $rate['name'] . '*' . PHP_EOL . PHP_EOL
            . $rate['description'] . PHP_EOL . PHP_EOL
            . '*Цена:*' . $rate['price'] . '₽',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make()
                    ->addRow(KeyboardButton::make(text: '✅ Оплатить c промокодом'))
                    ->addRow(KeyboardButton::make(text: '🚫 Оплатить без промокода'))
                    ->addRow(KeyboardButton::make(text: '⬅️ На главную'))
            ]
        );
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function initiatePayment(Nutgram $bot): void
    {
        if (!in_array($bot->message()->text, [
            '🚫 Оплатить без промокода',
            '✅ Оплатить с промокодом',
            '⬅️ Изменить способ оплаты'
        ])) {
            $bot->sendMessage('Выберите опции с клавиатуры');
            return;
        }

        if ($bot->message()->text === '✅ Оплатить с промокодом') {
            $this->initializePromocode($bot);
            return;
        }

        if ($bot->message()->text === '🚫 Оплатить без промокода') {
            $this->sendPaymentInfo($bot);
            return;
        }
    }


    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    private function initializePromocode(Nutgram $bot)
    {
        $this->next('awaitPromocode');

        $bot->sendMessage('Введите промокод в поле для ввода:', [
            'parse_mode' => 'markdown',
            'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                ->addRow(KeyboardButton::make('⬅️ Изменить способ оплаты'))
        ]);
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function awaitPromocode(Nutgram $bot)
    {
        $this->setSkipHandlers(true);

        $client = $this->request->getAttribute('client');

        if ($bot->message()->text === '⬅️ Изменить способ оплаты') {
            $this->getPaymentInfoStep($bot);
            return;
        }

        $message = $bot->message()->text;
        try {
            $paymentRaw = $this->client->post('api/payment', [
                'json' => [
                    'rateId' => $this->rateId,
                    'connectionId' => $this->connectionId,
                    'promocode' => $message
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])
                ]
            ]);

            $payment = json_decode($paymentRaw->getBody()->getContents(), true);

            $this->paymentId = $payment['id'];
        } catch (GuzzleException $exception) {
            if ($exception->getCode() === 404) {
                $bot->sendMessage('Неверный промокод, попробуйте снова или продолжите с клавиатуры');
                return;
            }

            if ($exception->getCode() === 404) {
                $bot->sendMessage('Промокод ' . $message . ' уже использовался, попробуйте другой или продолжите с клавиатуры');
                return;
            }
        }

        $bot->sendMessage('Промокод активирован ✅');
        $this->sendPaymentInfo($bot);
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    private function sendPaymentInfo(Nutgram $bot): void
    {
        /** @var \App\Domain\Client\Client $client */
        $client = $this->request->getAttribute('client');
        if ($this->paymentId === null) {
            try {
                $paymentRaw = $this->client->post('api/payment', [
                    'json' => [
                        'rateId' => $this->rateId,
                        'connectionId' => $this->connectionId
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])
                    ]
                ]);

                $payment = json_decode($paymentRaw->getBody()->getContents(), true);

                $this->paymentId = $payment['id'];
            } catch (GuzzleException $exception) {
                return;
            }
        } else {
            try {
                $paymentRaw = $this->client->get('api/payment/' . $this->paymentId, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])
                    ]
                ]);
                $payment = json_decode($paymentRaw->getBody()->getContents(), true);

                $this->paymentId = $payment['id'];
            } catch (GuzzleException) {
                return;
            }
        }

        $this->next('paymentAwaiting');
        $bot->sendMessage(
            '*Ссылка для оплаты ' . $payment['sum'] . '₽:*' . PHP_EOL . PHP_EOL
            . 'https://www.donationalerts.com/r/tatarstanvpn',
            [
                'parse_mode' => 'markdown',
                'chat_id' => $client->getId()
            ],
        );

        $bot->sendMessage(
            'Для оплаты, отправьте код в комментарие к донату',
            [
                'parse_mode' => 'markdown',
                'chat_id' => $client->getId()
            ]
        );

        $bot->sendMessage('`' . $payment['paymentCode'] . '`', [
            'parse_mode' => 'markdown',
            'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                ->addRow(KeyboardButton::make('⬅️ Изменить способ оплаты'))
                ->addRow(KeyboardButton::make('⬅️ На главную'))
        ]);
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    public function paymentAwaiting(Nutgram $bot): void
    {
        $client = $this->request->getAttribute('client');
        $paymentRaw = $this->client->get('api/payment/' . $this->paymentId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])
            ]
        ]);

        $payment = json_decode($paymentRaw->getBody()->getContents(), true);

        if (
            !in_array(
                $bot->message()->text,
                [
                    '⬅️ Изменить способ оплаты',
                    '⬅️ На главную',
                    '🚫 Отменить оплату'
                ]
            )
        ) {
            $bot->sendMessage('Воспользуйтесь доступными опциями');
            return;
        }

        if ($bot->message()->text === '⬅️ На главную') {
            $this->toMain($bot);
            return;
        }

        if ($bot->message()->text === '⬅️ Изменить способ оплаты') {
            $this->paymentId = null;
            $this->start($bot);
        }
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    public function toMain(Nutgram $bot): void
    {
        $bot->endConversation($bot->userId(), $bot->chatId());
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