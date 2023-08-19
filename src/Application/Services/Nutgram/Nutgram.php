<?php

declare(strict_types=1);

namespace App\Application\Services\Nutgram;

use GuzzleHttp\Client as Guzzle;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use SergiX44\Nutgram\Cache\Adapters\ArrayCache;
use SergiX44\Nutgram\Cache\GlobalCache;
use SergiX44\Nutgram\Cache\UserCache;
use SergiX44\Nutgram\Hydrator\Hydrator;
use SergiX44\Nutgram\Hydrator\NutgramHydrator;
use SergiX44\Nutgram\Proxies\GlobalCacheProxy;
use SergiX44\Nutgram\Proxies\UpdateDataProxy;
use SergiX44\Nutgram\Proxies\UserCacheProxy;
use SergiX44\Nutgram\RunningMode\Polling;
use SergiX44\Nutgram\RunningMode\RunningMode;
use SergiX44\Nutgram\Telegram\Client;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

class Nutgram extends \SergiX44\Nutgram\Nutgram
{
    use Client, UpdateDataProxy, GlobalCacheProxy, UserCacheProxy;
    /**
     * @var string
     */
    private string $token;

    /**
     * @var array
     */
    private array $config;

    /**
     * @var ClientInterface
     */
    private ClientInterface $http;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Nutgram constructor.
     * @param  string  $token
     * @param  array  $config
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(string $token, array $config = [])
    {
        if (empty($token)) {
            throw new InvalidArgumentException('The token cannot be empty.');
        }

        $this->bootstrap($token, $config);
    }

    /**
     * Initializes the current instance
     * @param  string  $token
     * @param  array  $config
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function bootstrap(string $token, array $config): void
    {
        $this->token = $token;
        $this->config = $config;

        $this->container = new Container();
        if (isset($config['container']) && $config['container'] instanceof ContainerInterface) {
            $this->container->delegate($config['container']);
        }
        $this->container->delegate(new ReflectionContainer());
        $this->container->addShared(ContainerInterface::class, $this->container);

        SerializableClosure::setSecretKey($this->token);

        $baseUri = sprintf(
            '%s/bot%s/%s',
            $this->config['api_url'] ?? self::DEFAULT_API_URL,
            $this->token,
                $this->config['test_env'] ?? false ? 'test/' : ''
        );

        $this->http = new Guzzle(array_merge([
            'base_uri' => $baseUri,
            'timeout' => $this->config['timeout'] ?? 5,
        ], $this->config['client'] ?? []));
        $this->container->addShared(ClientInterface::class, $this->http);

        $hydrator = $this->container->get(NutgramHydrator::class);
        $this->container->addShared(Hydrator::class)->setConcrete($this->config['mapper'] ?? $hydrator);
        $this->mapper = $this->container->get(Hydrator::class);

        $botId = $this->config['bot_id'] ?? (int)explode(':', $this->token)[0];
        $this->container->addShared(CacheInterface::class, $this->config['cache'] ?? ArrayCache::class);
        $this->container->addShared(LoggerInterface::class, $this->config['logger'] ?? NullLogger::class);

        $this->container->add(ConversationCache::class)->addArguments([CacheInterface::class, $botId]);
        $this->container->add(GlobalCache::class)->addArguments([CacheInterface::class, $botId]);
        $this->container->add(UserCache::class)->addArguments([CacheInterface::class, $botId]);

        $this->conversationCache = $this->container->get(ConversationCache::class);

        $this->globalCache = $this->container->get(GlobalCache::class);
        $this->userCache = $this->container->get(UserCache::class);
        $this->logger = $this->container->get(LoggerInterface::class);

        $this->container->addShared(RunningMode::class, Polling::class);
        $this->container->addShared(__CLASS__, $this);
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}