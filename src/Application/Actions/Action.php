<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Symfony\Component\Serializer\Serializer;

abstract class Action
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Response
     */
    protected Response $response;

    /**
     * @var array
     */
    protected array $args;

    /**
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly Serializer $serializer,
        protected readonly ValidatorInterface $validator
    ) {
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws HttpBadRequestException
     * @throws HttpForbiddenException
     * @throws HttpNotFoundException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->checkAccess($request->getAttribute('client'), $request);

        $this->request = $this->validate($request);
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
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     * @throws HttpBadRequestException
     */
    protected function getFormData(): object|array
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
        }

        return $input;
    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param object|array|null $data
     * @param int $statusCode
     * @return Response
     */
    protected function respondWithData(object|array $data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    /**
     * @param ActionPayload $payload
     * @return Response
     */
    protected function respond(ActionPayload $payload): Response
    {
        $json = $this->serializer->serialize($payload->getData(), 'json');
        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }

    /**
     * @param Client|null $user
     * @param Request $request
     * @return void
     */
    public function checkAccess(?Client $user, Request $request): void
    {
        if (!in_array($user?->getRole() ?? User::GUEST, $this->getAcceptedRoles(), true)) {
            throw new HttpForbiddenException($request);
        }
    }

    /**
     * @return array
     */
    abstract protected function getAcceptedRoles(): array;

    /**
     * @param Request $request
     * @return Request
     * @throws RequestValidationException
     */
    private function validate(Request $request): Request
    {
        $this->validator->validate($request, $this->getRules());

        if ($this->validator->isValid()) {
            return $request->withParsedBody($this->validator->getValidData());
        }

        throw new RequestValidationException($request, $this->validator->getErrors(), 400);
    }

    abstract protected function getRules(): array;
}
