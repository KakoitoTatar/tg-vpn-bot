<?php

declare (strict_types=1);

namespace App\Domain\Rate;

use App\Domain\Client\Client;
use App\Domain\Instance\Instance;
use App\Domain\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'rates', options: ['collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'])]
#[ORM\Entity(repositoryClass: RateRepositoryInterface::class)]
class Rate
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $duration;

    #[ORM\Column(type: 'string')]
    private string $description;

    #[ORM\Column(type: 'integer')]
    private int $price;

    #[ORM\ManyToMany(targetEntity: Instance::class, mappedBy: 'rates')]
    #[ORM\JoinTable(name: 'instances_rates')]
    private Collection $instances;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    /**
     *
     */
    public function __construct()
    {
        $this->instances = new ArrayCollection();
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param int $duration
     * @return $this
     */
    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @param Instance $instance
     * @return $this
     */
    public function addInstance(Instance $instance): self
    {
        $this->instances->add($instance);

        return $this;
    }

    /**
     * @param Instance $instance
     * @return $this
     */
    public function removeInstance(Instance $instance): self
    {
        $instance->removeRate($this);
        $this->instances->remove($instance->getId());

        return $this;
    }

    /**
     * @return Collection
     */
    public function getInstances(): Collection
    {
        return $this->instances;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
