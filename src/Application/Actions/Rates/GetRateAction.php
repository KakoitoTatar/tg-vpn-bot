<?php

namespace App\Application\Actions\Rates;

use App\Application\Actions\Action;
use App\Application\DTO\RateDTO;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\Rate\RateRepositoryInterface;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class GetRateAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        Serializer $serializer,
        ValidatorInterface $validator,
        private readonly RateRepositoryInterface $rateRepository
    ) {
        parent::__construct($logger, $serializer, $validator);
    }

    protected function action(): Response
    {
        $rateId = $this->request->getParsedBody()['id'];

        $rate = $this->rateRepository->find($rateId);

        return $this->respondWithData(RateDTO::create($rate));
    }

    protected function getAcceptedRoles(): array
    {
        return [User::GUEST, User::USER, User::ADMIN];
    }

    protected function getRules(): array
    {
        return ['id' => 'required', 'integer'];
    }
}