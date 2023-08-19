<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Auth\JwtAuth;
use App\Application\Helpers\StringHelper;
use App\Domain\Rate\Rate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class PurchaseConversation extends Conversation
{
    private const KEY_NAMES = [
        'Эчпочмачная',
        'Друг Туйбик подогнал',
        'ШУАРСНИГЫР'
    ];

    /** @var int|null */
    public ?int $rateId = null;

    /** @var int|null */
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

    /**
     * @param Nutgram $bot
     * @return void
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot): void
    {
        $this->setSkipHandlers(true);

        $ratesRaw = $this->client->get('api/rates')->getBody()->getContents();

        $rates = json_decode($ratesRaw, true);

        $this->next('getPaymentInfoStep');

        $bot->sendMessage(
            $this->buildRateDescriptions($rates),
            [
                'parse_mode' => 'markdown',
                'reply_markup' => $this->buildKeyboard($rates)
            ]
        );
    }

    /**
     * @param Rate[] $rates
     * @return string
     */
    private function buildRateDescriptions(array $rates): string
    {
        return '*Тарифы:*' . PHP_EOL . PHP_EOL
            . '💠 *Премиум*' . PHP_EOL
            . '🚀 Высокая скорость соединения' . PHP_EOL
            . '✅ Без ограничений по трафику' . PHP_EOL
            . '📱 Без ограничений по к - ву устройств' . PHP_EOL . PHP_EOL

            . '*Цена:*' . PHP_EOL
            . '1 мес - *199 ₽*' . PHP_EOL
            . '3 мес - *499 ₽*' . PHP_EOL
            . '6 мес - *999 ₽*';
    }

    /**
     * @param Rate[] $rates
     * @return ReplyKeyboardMarkup
     */
    private function buildKeyboard(array $rates): ReplyKeyboardMarkup
    {
        $keyboard = new ReplyKeyboardMarkup(resize_keyboard: true, selective: false);

        foreach ($rates as $rate) {
            $keyboard->addRow(
                KeyboardButton::make(
                    $rate['id'] .
                    ')' . $rate['name'] .
                    ' / ' . StringHelper::convertDuration($rate['duration']) .
                    ' / ' . $rate['price'] . '₽'
                )
            );
        }

        $keyboard->addRow(KeyboardButton::make('⬅️ На главную'));

        return $keyboard;
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getPaymentInfoStep(Nutgram $bot): void
    {
        $client = $this->request->getAttribute('client');
        $this->setSkipHandlers(true);

        if ($bot->message()->text === '⬅️ На главную') {
            $bot->endConversation($client->getId(), $client->getId());
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true);
            $keyboard->addRow(KeyboardButton::make('👁️ Мой тариф'), KeyboardButton::make('💲 Выбрать тариф'));
            $keyboard->addRow(KeyboardButton::make('💰 Партнёрская программа'));
            $keyboard->addRow(KeyboardButton::make('⁉️ Полезные материалы'));

            $bot->sendMessage(
                'Главная 🏠',
                [
                    'parse_mode' => 'markdown',
                    'reply_markup' => $keyboard
                ]
            );
            return;
        }

        $rateData = explode(' / ', $bot->message()->text);

        if (count($rateData) !== 3 && $this->rateId === null) {
            $bot->sendMessage('Выберите опции с клавиатуры');
            return;
        }

        if (count($rateData) === 3) {
            $rateId = (int)explode(')', $rateData[0])[0];
            $this->rateId = $rateId;
        }

        $rateRaw = $this->client->get('api/rates/' . $this->rateId)->getBody()->getContents();

        $rate = json_decode($rateRaw, true);

        $this->next('initiatePayment');

        $bot->sendMessage(
            '*' . $rate['name'] . '*' . PHP_EOL . PHP_EOL
            . $rate['description'] . PHP_EOL . PHP_EOL
            . '*Цена:*' . $rate['price'] . '₽',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make()
                    ->addRow(
                        KeyboardButton::make(text: '✅ Оплатить с промокодом'),
                        KeyboardButton::make(text: '🚫 Оплатить без промокода')
                    )
                    ->addRow(KeyboardButton::make(text: '⬅️ Список тарифов'))
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
            '⬅️ Список тарифов',
            '⬅️ Изменить способ оплаты'
        ])) {
            $bot->sendMessage('Выберите опции с клавиатуры');
            return;
        }

        if ($bot->message()->text === '⬅️ Список тарифов') {
            $this->start($bot);
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
                ->addRow(KeyboardButton::make('⬅️ Список тарифов'))
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

        if ($bot->message()->text === '⬅️ Список тарифов') {
            $this->start($bot);
            return;
        }

        if ($bot->message()->text === '⬅️ Изменить способ оплаты') {
            $this->getPaymentInfoStep($bot);
            return;
        }

        $message = $bot->message()->text;
        try {
            $paymentRaw = $this->client->post('api/payment', [
                'json' => [
                    'rateId' => $this->rateId,
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
                ->addRow(KeyboardButton::make('⬅️ Изменить способ оплаты'), KeyboardButton::make('⬅️ Список тарифов'))
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
        if (
            !in_array(
                $bot->message()->text,
                [
                    '⬅️ Список тарифов',
                    '⬅️ Изменить способ оплаты',
                    '⬅️ На главную',
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

        if ($bot->message()->text === '⬅️ Список тарифов') {
            $this->paymentId = null;

            $this->start($bot);
            return;
        }

        if ($bot->message()->text === '⬅️ Изменить способ оплаты') {
            $this->paymentId = null;
            $this->getPaymentInfoStep($bot);
        }
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    public function toMain(Nutgram $bot): void
    {
        $client = $this->request->getAttribute('client');

        $bot->endConversation($client->getId(), $client->getId());
        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true);
        $keyboard->addRow(KeyboardButton::make('👁️ Мой тариф'), KeyboardButton::make('💲 Выбрать тариф'));
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