<?php

namespace App\Infrastructure\Commands;

use App\Application\Auth\JwtAuth;
use App\Application\Services\DonationAlertsService\DonationAlertsServiceInterface;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Payments\PaymentStatus;
use App\Domain\Promocode\PromocodeTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Payments\Payment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Nutgram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenToNewPayments extends Command
{
    protected static $defaultName = 'app:payments:process';

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param LoggerInterface $logger
     * @param DonationAlertsServiceInterface $donationAlertsService
     * @param JwtAuth $jwtAuth
     */
    public function __construct(
        protected LoggerInterface                $logger,
        protected DonationAlertsServiceInterface $donationAlertsService,
        protected JwtAuth                        $jwtAuth,
        protected Nutgram                        $nutgram
    )
    {
        $this->client = new Client([
            'base_uri' => 'http://nginx:83'
        ]);
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this->setDescription('Слушает оплаты');
        $this->setHelp('Слушает оплаты');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $expiredPaymentsTime = new \DateTime('-6 hours');
        $oldestPayment = $expiredPaymentsTime;
        while (1) {
            $suitableDonations = $this->getSuitableDonations($oldestPayment);

            /** @var Payment $payment */
            foreach ($suitableDonations as $donation) {
                try {
                    $response = $this->client->post(
                        '/api/payment/serve',
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $this->jwtAuth->createJwt(['id' => 499368030])
                            ],
                            'json' => [
                                'actualSum' => $donation['amount'],
                                'paymentSystemId' => $donation['message'],
                                'expiredPaymentTime' => $expiredPaymentsTime->format('d-m-Y-H-i-s')
                            ]
                        ]
                    );
                    if ($response->getStatusCode() === 201) {
                        $data = json_decode($response->getBody()->getContents(), true);
                        $output->writeln('Payment served: ' . $data['paymentId']);
                        $this->nutgram->sendMessage(
                            'Оплата успешно прошла, подробности в разделе "👁️ Мой тариф"',
                            [
                                'chat_id' => $data['clientId']
                            ]
                        );
                        $this->logger->info(
                            'Payment successfull',
                            $data
                        );
                    }
                } catch (GuzzleException $e) {
                    $response = $e->getResponse();
                    $data = json_decode($response->getBody()->getContents(), true);
                    $output->writeln('Payment not served: ' . $donation['message'] . ", data:" . $response->getBody()->getContents()
                    );
                    if ($response->getStatusCode() === 400) {
                        $this->logger->info(
                            'Payment expired',
                            $data
                        );
                    }
                }

            }
            $output->writeln('Passed cycle');
            sleep(1);
        }

        return Command::SUCCESS;
    }

    private function getSuitableDonations(\DateTime $oldestPayment, array $suitableDonations = [], int $initialPage = 1): array
    {
        $donations = $this->donationAlertsService->getDonations($initialPage);
        $counter = 0;
        $perPage = $donations['meta']['per_page'];
        foreach ($donations['data'] as $donation) {
            $timeZone = new \DateTimeZone('-7');
            $donationTime = new \DateTime($donation['created_at'], $timeZone);
            if ($donationTime > $oldestPayment) {
                $suitableDonations[$donation['message']] = $donation;
            }
            $counter++;

            if ($counter === $perPage && $donationTime < $oldestPayment) {
                sleep(1);
                $suitableDonations = array_merge(
                    $suitableDonations,
                    $this->getSuitableDonations($oldestPayment, $suitableDonations, $initialPage++)
                );
            }
        }

        return $suitableDonations;
    }
}