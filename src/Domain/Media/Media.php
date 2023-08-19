<?php
declare(strict_types=1);

namespace App\Domain\Media;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Table(name: 'media')]
#[ORM\Entity(repositoryClass: MediaRepositoryInterface::class)]
#[UniqueConstraint(name: 'url_idx', columns: ['bucket', 'url'])]
class Media
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    protected ?int $id;

    #[ORM\Column(type: 'string')]
    protected string $bucket;

    #[ORM\Column(type: 'string')]
    protected string $url;

    /**
     * Media constructor.
     */
    public function __construct()
    {
        $this->bucket = 'tatarstan';
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * @param string $bucket
     * @return $this
     */
    public function setBucket(string $bucket): self
    {
        $this->bucket = $bucket;

        return $this;
    }
}
