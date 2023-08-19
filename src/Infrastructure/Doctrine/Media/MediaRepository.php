<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Media;

use App\Domain\Media\Media;
use App\Domain\Media\MediaRepositoryInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class MediaRepository extends EntityRepository implements MediaRepositoryInterface
{
    /**
     * @param string $url
     * @return Media
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(string $url): Media
    {
        $media = new Media();
        $media->setUrl($url);

        $this->getEntityManager()->persist($media);
        try {
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
            $media = $this->findOneBy(['url' => $media->getUrl()]);
        }

        return $media;
    }

    /**
     * @param int $id
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(int $id): bool
    {
        $media = $this->find($id);
        $this->getEntityManager()->remove($media);
        $this->getEntityManager()->flush();
        return true;
    }
}
