<?php

namespace App\Application\Actions\Telegram;

use App\Application\Auth\JwtAuth;
use App\Domain\Connection\Connection;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class InstructionConversation extends Conversation
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

    public function start(Nutgram $bot): void
    {
        $client = $this->request->getAttribute('client');

        $connectionId = explode(':', $bot->callbackQuery()->data)['1'];

        $connectionRaw = $this->client->get('/api/connections/' . $connectionId, [
            'headers' => ['Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])]
        ])->getBody()->getContents();

        /** @var Connection $connection */
        $connection = json_decode($connectionRaw, true);

        $this->next('instructions');

        $bot->sendMessage('💻');
        $bot->sendMessage(
            'Отлично, теперь приступим к подключению VPN.'
            . PHP_EOL . 'Следуйте простой инструкции ниже ⬇️'
        );
        $bot->sendMessage(
            '*[1] Вам необходимо скачать приложение Outline.* '
            . PHP_EOL .' Оно  бесплатное и работает на всех типах устройств.',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('📱 Для мобильных устройств', callback_data: 'mobilesMenu'))
                    ->addRow(InlineKeyboardButton::make('💻 Для ноутбуков и компьютеров ', callback_data: 'pcMenu'))
            ]
        );
        $bot->sendMessage(
            '*[2] Скопируйте набор символов ниже.* ' . PHP_EOL . 'Это ваш уникальный ключ доступа. ',
            [
                'parse_mode' => 'markdown'
            ]
        );
        $bot->sendMessage(
            '`' . $connection['key'] . '`',
            [
                'parse_mode' => 'markdown'
            ]
        );
        $bot->sendMessage(
            '*Важно ‼️* ' . PHP_EOL . 'Не публикуйте и не передавайте ваш ключ доступа третьим лицам.',
            [
                'parse_mode' => 'markdown'
            ]
        );

        $bot->sendMessage(
            '*[3] Добавьте ключ доступа в Outline.*' . PHP_EOL
            . 'Нажмите на “+” и вставьте скопированный ключ в поле. Затем нажмите на кнопку “Добавить сервер”.' . PHP_EOL
            . '(Часто при первичном использовании программы скопированный ключ вставляется в поле автоматически)',
            [
                'parse_mode' => 'markdown'
            ]
        );

        $bot->sendMessage(
            'Надеемся что у вас не возникло трудностей при подключении. Расскажите, все ли у вас получилось?',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                    ->addRow(KeyboardButton::make('✅ Да, все работает'))
                    ->addRow(KeyboardButton::make('😟 Нет, что-то пошло не так'))
            ]
        );
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    public function instructions(Nutgram $bot): void
    {
        if ($bot->callbackQuery()->data === 'mobilesMenu') {
            $bot->editMessageText(
                '*[1] Вам необходимо скачать приложение Outline.* '
                . PHP_EOL . ' Оно  бесплатное и работает на всех типах устройств.',
                [
                    'parse_mode' => 'markdown',
                    'reply_markup' => InlineKeyboardMarkup::make()
                        ->addRow(
                            InlineKeyboardButton::make(
                                text: 'IOS',
                                url: 'https://itunes.apple.com/app/outline-app/id1356177741'
                            )
                        )->addRow(
                            InlineKeyboardButton::make(
                                text: 'Android',
                                url: 'https://play.google.com/store/apps/details?id=org.outline.android.client'
                            )
                        )->addRow(
                            InlineKeyboardButton::make(
                                text: '⬅️ Назад',
                                callback_data: 'devicesHome'
                            )
                        )]
            );
        }

        if ($bot->callbackQuery()->data === 'pcMenu') {
            $bot->editMessageText(
                '*[1] Вам необходимо скачать приложение Outline.* '
                . PHP_EOL . ' Оно  бесплатное и работает на всех типах устройств.',
                [
                    'parse_mode' => 'markdown',
                    'reply_markup' => InlineKeyboardMarkup::make()
                        ->addRow(
                            InlineKeyboardButton::make(
                                text: 'macOS',
                                url: 'https://itunes.apple.com/app/outline-app/id1356178125'
                            )
                        )->addRow(
                            InlineKeyboardButton::make(
                                text: 'Windows',
                                url: 'https://s3.amazonaws.com/outline-releases/client/windows/stable/Outline-Client.exe'
                            )
                        )->addRow(
                            InlineKeyboardButton::make(
                                text: 'Linux',
                                url: 'https://s3.amazonaws.com/outline-releases/client/linux/stable/Outline-Client.AppImage'
                            )
                        )->addRow(
                            InlineKeyboardButton::make(
                                text: '⬅️ Назад',
                                callback_data: 'devicesHome'
                            )
                        )]
            );
        }

        if ($bot->callbackQuery()->data === 'devicesHome') {
            $bot->editMessageText(
                '*[1] Вам необходимо скачать приложение Outline.* '
                . PHP_EOL . ' Оно  бесплатное и работает на всех типах устройств.',
                [
                    'parse_mode' => 'markdown',
                    'reply_markup' => InlineKeyboardMarkup::make()
                        ->addRow(
                            InlineKeyboardButton::make(
                                '📱 Для мобильных устройств',
                                callback_data: 'mobilesMenu')
                        )
                        ->addRow(
                            InlineKeyboardButton::make(
                                '💻 Для ноутбуков и компьютеров ',
                                callback_data: 'pcMenu')
                        )
                ]
            );
        }

        if ($bot->message()->text === '✅ Да, все работает') {
            $this->successfulConnection($bot);
            return;
        }

        if ($bot->message()->text === '😟 Нет, что-то пошло не так') {
            $this->next('reassuring');
            $bot->sendMessage('🤔');
            $bot->sendMessage('Странно...' . PHP_EOL
                . 'Проверьте правильность выполняемых действий. Для наглядности посмотрите этот видео пример:'
            );
            $bot->sendMessage('*Ну как?* Теперь получилось?', [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true)
                    ->addRow(KeyboardButton::make('✅ Да, спасибо!'))
                    ->addRow(KeyboardButton::make('😟 Нет, не помогло'))
            ]);
        }
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    public function reassuring(Nutgram $bot): void
    {
        if ($bot->message()->text === '✅ Да, спасибо!') {
            $this->successfulConnection($bot);
            return;
        }

        if ($bot->message()->text === '😟 Нет, не помогло') {
            $this->next('endConversation');
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

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    private function successfulConnection(Nutgram $bot)
    {
        $this->next('endConversation');

        $bot->sendMessage(
            '🎊',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make(
                        text: '✏️ Оставить отзыв',
                        url: 'https://t.me/fuckin_tatar'
                    ))
            ]
        );
        $bot->sendMessage(
            '*Отлично!*' . PHP_EOL
            . 'Рады, что у вас все получилось. Мы будем признательны если вы оставите нам отзыв.',
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true)
                    ->addRow(KeyboardButton::make('⬅️ На главную'))
            ]
        );
    }

    /**
     * @param Nutgram $bot
     * @return void
     * @throws InvalidArgumentException
     */
    public function toMain(Nutgram $bot): void
    {
        $bot->endConversation($bot->userId(), $bot->chatId());
        if ($bot->message()->text === '⬅️ На главную') {
            $this->toMain($bot);
        }
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