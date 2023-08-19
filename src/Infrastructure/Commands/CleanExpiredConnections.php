<?php

namespace App\Infrastructure\Commands;

use App\Application\Services\MailTemplateService\MailTemplateServiceInterface;
use App\Application\Services\VpnControlService\VpnServiceFactory;
use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Connection\Connection;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Instance\Instance;
use App\Domain\Mail\Mail;
use App\Domain\Mail\MailRepositoryInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanExpiredConnections extends Command
{
    protected static $defaultName = 'app:clients:process';

    public function __construct(
        string $name = null,
        protected ConnectionRepositoryInterface $connectionRepository
    )
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Удаляет просроченные впн ключи');
        $this->setHelp('Удаляет просроченные впн ключи');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $continue = true;
        while ($continue) {
            /** @var QueryBuilder $qb */
            $qb = $this->connectionRepository->createQueryBuilder('c');
            $qb->where($qb->expr()->lte('c.periodEnd', ':date'))
                ->setParameter('date', (new \DateTime('+5days')), Types::DATETIME_MUTABLE)
                ->join('c.rate', 'r')
                ->setMaxResults(1);

            $result = $qb->getQuery()->getResult();

            if ($result === []) {
                sleep(5);
                $output->writeln('Nothing to process, sleep');
                continue;
            }

            /** @var Connection $connection */
            $connection = $result[0];

            $output->writeln('Proccessing client: ' . $connection->getId());

            $vpnControlService = VpnServiceFactory::getControlService($connection->getInstance()->getProtocol());

            $vpnControlService->authenticate($connection->getInstance()->getConnection());

            $vpnControlService->deleteUser(['id' => $connection->getVpnKey()]);

            $connection->setActive(false);
            $connection->setVpnKey(null);

            $this->connectionRepository->update($connection);
            $output->writeln('Client ' . $connection->getId() . ' processed');
        }

        return Command::SUCCESS;
    }
}