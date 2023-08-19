<?php

namespace App\Application\Actions\Rates;

use App\Application\Actions\Action;
use App\Application\DTO\RateDTO;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Rate\RateRepositoryInterface;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class GetRatesAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        Serializer $serializer,
        ValidatorInterface $validator,
        private readonly RateRepositoryInterface $rateRepository
    ) {
        parent::__construct($logger, $serializer, $validator);
    }

    /**
     * @return Response
     */
    protected function action(): Response
    {
        $rates = $this->rateRepository->findBy(['active' => true]);

        return $this->respondWithData(RateDTO::createFromCollection($rates));
    }

    protected function getAcceptedRoles(): array
    {
        return [User::GUEST, User::USER, User::ADMIN];
    }

    protected function getRules(): array
    {
        return [];
    }
}