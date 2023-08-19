<?php

declare (strict_types=1);

namespace App\Application\Actions\Media;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;

class GetMediaAsFile extends MediaAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $url = $this->request->getParsedBody()['url'];

        $media = $this->repository->findOneBy(['url' => $url]);

        $file = $this->fileService->get($media->getBucket(), $media->getUrl());

        return $this->response->withBody($file->getStream())
            ->withHeader('Content-Disposition', $media->getUrl())
            ->withHeader('Content-Length', $file->getSize())
            ->withHeader('Content-Type', $file->getClientMediaType());
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::USER,
            User::ADMIN,
            User::GUEST,
            User::INACTIVE_USER
        ];
    }

    /**
     * @return string[][]
     */
    protected function getRules(): array
    {
        return [
            'url' => ['required']
        ];
    }
}