<?php

declare(strict_types=1);

namespace App\Application\Actions\Media;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;

class PostMedia extends MediaAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();
        $file = $this->request->getUploadedFiles()['media'];

        $identifier = $data['rootDirectory'] . '/' . urlencode($file->getClientFilename());

        $media = $this->repository->findOneBy(['url' => $identifier]);

        if ($media !== null) {
            return $this->respondWithData($media, 200);
        }

        $this->fileService->put($identifier, $file);

        $media = $this->repository->save($identifier);

        return $this->respondWithData($media, 200);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::ADMIN
        ];
    }

    /**
     * @return string[]
     */
    protected function getRules(): array
    {
        return [
            'rootDirectory' => ['required'],
            'media' => ['required', ['uploadedFile']]
        ];
    }
}
