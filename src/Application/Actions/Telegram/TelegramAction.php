<?php

namespace App\Application\Actions\Telegram;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\DomainException\DomainRecordNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use Slim\Exception\HttpNotFoundException;
use Symfony\Component\Serializer\Serializer;

class TelegramAction extends Action
{
    public function __construct(
        LoggerInterface              $logger,
        Serializer                   $serializer,
        ValidatorInterface           $validator,
        protected ContainerInterface $container,
        protected Nutgram            $bot
    )
    {
        parent::__construct($logger, $serializer, $validator);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->container->set(ServerRequestInterface::class, $request);
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (DomainRecordNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function action(): Response
    {
        Conversation::refreshOnDeserialize();
        /** @var Client $client */
        $client = $this->request->getAttribute('client', null);

        $this->bot->onCommand(
            'start',
            StartBot::class
        );

        $this->bot->onText('💲 Выбрать тариф', PurchaseConversation::class);
        $this->bot->onText('⬅️ На главную', MainMenuAction::class);
        $this->bot->onText('⁉️ Полезные материалы', HandbookAction::class);
        $this->bot->onText('⁉️ F.A.Q', FAQAction::class);
        $this->bot->onText('💬 Обратиться в поддержку', SendSupportLink::class);
        $this->bot->onText('💰 Партнёрская программа', AffiliateProgramAction::class);

        if (!$client->getConnections()->isEmpty()) {
            $this->bot->onText('👁️ Мой тариф', ShowConnectionsAction::class);
        } else {
            $this->bot->onText('👁️ Мой тариф', SendCallToBuyAction::class);
        }

        $this->bot->onCallbackQueryData(
            'prolong:{connection}',
            ConnectionProlongationConversation::class
        );

        $this->bot->onCallbackQueryData(
            'cancel:{payment}',
            CancelPayment::class
        );

        $this->bot->onCallbackQueryData(
            'guide:{connection}',
            InstructionConversation::class
        );

        $this->bot->onCallbackQueryData(
            'freeDays:{type}',
            ApplyAffiliateDays::class
        );

        if ($client->getConnections() !== []) {
            $this->bot->onCallbackQueryData('mobilesMenu', function (Nutgram $bot) {
                $this->bot->editMessageText(
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
            });
            $this->bot->onCallbackQueryData('pcMenu', function (Nutgram $bot) {
                $this->bot->editMessageText(
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
            });
            $this->bot->onCallbackQueryData('devicesHome', function (Nutgram $bot) {
                $this->bot->editMessageText(
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
            });

        }
        $this->bot->onText('💲 Выбрать тариф', PurchaseConversation::class);

        $this->bot->run();

        return $this->respondWithData(['Status' => 'success']);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [];
    }
}