<?php

declare(strict_types=1);

namespace App\Application\Services\Nutgram;

use Laravel\SerializableClosure\SerializableClosure;
use SergiX44\Nutgram\Conversations\Conversation;

class ConversationCache extends \SergiX44\Nutgram\Cache\ConversationCache
{
    /**
     * @param int $userId
     * @param int $chatId
     * @return callable|Conversation|null
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(int $userId, int $chatId): null|callable|Conversation
    {
        return parent::get($chatId, $chatId);
    }

    /**
     * @param int $userId
     * @param int $chatId
     * @param callable|Conversation|SerializableClosure $conversation
     * @return bool
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(int $userId, int $chatId, callable|Conversation|SerializableClosure $conversation): bool
    {
        return parent::set($chatId, $chatId, $conversation);
    }
}