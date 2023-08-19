<?php
declare(strict_types=1);

use App\Application\Auth\JwtAuth;
use App\Application\Services\DonationAlertsService\DonationAlertsServiceInterface;
use App\Application\Services\FileService\FileServiceInterface;
use App\Application\Services\MailTemplateService\MailTemplateServiceInterface;
use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Services\DonationAlertsService\DonationAlertsService;
use App\Infrastructure\Services\MailTemplateService;
use App\Infrastructure\Services\S3FilesystemService;
use App\Infrastructure\Validator\Rules\UniqueRule;
use Aws\S3\S3Client;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        EntityManagerInterface::class => function (ContainerInterface $c): EntityManager {
            $doctrineSettings = $c->get(SettingsInterface::class)->get('doctrine');

            $config = ORMSetup::createAttributeMetadataConfiguration(
                $doctrineSettings['metadata_dirs'],
                $doctrineSettings['dev_mode']
            );

            $config->setMetadataCache(
                new FilesystemAdapter(
                    '',
                    3600,
                    $doctrineSettings['cache_dir']
                )
            );

            $connection = DriverManager::getConnection($doctrineSettings['connection'], $config);

            return new EntityManager($connection, $config);
        },
        UniqueRule::class => function (ContainerInterface $c) {
            return new UniqueRule($c->get(EntityManagerInterface::class));
        },

        // And add this entry
        JwtAuth::class => function (ContainerInterface $c) {
            $config = $c->get(SettingsInterface::class)->get('jwt');

            $issuer = (string)$config['issuer'];
            $lifetime = (int)$config['lifetime'];
            $privateKey = (string)$config['private_key'];
            $publicKey = (string)$config['public_key'];
            return new JwtAuth($issuer, $lifetime, $privateKey, $publicKey);
        },
        MailTemplateServiceInterface::class => function (ContainerInterface $c) {
            return new MailTemplateService();
        },
        Serializer::class => function (ContainerInterface $c) {
            $encoders = [new XmlEncoder(), new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];

            return new Serializer($normalizers, $encoders);
        },
        DonationAlertsServiceInterface::class => function (ContainerInterface $c) {
            $config = $c->get(SettingsInterface::class)->get('donationAlerts');
            return new DonationAlertsService(
                $config['baseUri'],
                $config['token']
            );
        },
        CacheInterface::class => function (ContainerInterface $c) {
            /** @var EntityManagerInterface $entityManager */
            $entityManager = $c->get(EntityManagerInterface::class);
            $psr6Cache = new \Symfony\Component\Cache\Adapter\DoctrineDbalAdapter($entityManager->getConnection());
            return new Psr16Cache($psr6Cache);
        },
        Nutgram::class => function (ContainerInterface $c) {
            $nutgram = new \App\Application\Services\Nutgram\Nutgram(
                '6123517287:AAHsJYO1QNq5tw6MElPE_attHpOSrLKe24k',
                [
                    'logger' => $c->get(LoggerInterface::class),
                    'cache' => $c->get(CacheInterface::class),
                    'container' => $c,
                ]
            );
            $nutgram->setRunningMode(Webhook::class);
            return $nutgram;
        },
        \SergiX44\Nutgram\Cache\ConversationCache::class =>
            function (ContainerInterface $c) {
                return new \App\Application\Services\Nutgram\ConversationCache(
                    $c->get(CacheInterface::class),
                    6123517287
                );
            }
    ]);
};
