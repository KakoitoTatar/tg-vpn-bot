<?php

namespace App\Application\Actions\Telegram;

use App\Application\Auth\JwtAuth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ServerRequestInterface;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class CancelPayment extends Conversation
{
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
        /** @var \App\Domain\Client\Client $client */
        $client = $this->request->getAttribute('client');
        $payment = explode(':', $bot->callbackQuery()->data)[1];

        try {
            $this->client->delete('/api/payment/' . $payment, [
                'json' => [
                    'reason' => 'Payment cancelled (payment way)'
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => $client->getId()])
                ]
            ]);
        } catch (GuzzleException $e) {
            $bot->endConversation($client->getId(), $client->getId());
            $bot->sendMessage(
                'Платеж не может быть отменен 🚫'
            );
        }
        $bot->endConversation($client->getId(), $client->getId());
        $bot->sendMessage('Платеж успешно отменен ✅');
    }
}