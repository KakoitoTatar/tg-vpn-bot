<?php

declare (strict_types=1);

namespace App\Domain\Hosting;

use App\Domain\Instance\Instance;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'hostings')]
#[ORM\Entity(repositoryClass: HostingRepositoryInterface::class)]
class Hosting
{
    /**
     * @var string[]
     */
    private const COLLECTION = [
        self::PQHOSTING,
        self::VDSINA
    ];

    /**
     * @var string
     */
    public const VDSINA = 'vdsina';

    /**
     * @var string
     */
    public const PQHOSTING = 'pqhosting';

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $login;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string')]
    private string $provider;

    #[ORM\OneToMany(mappedBy: 'hosting', targetEntity: Instance::class)]
    private Collection $instances;

    public function __construct()
    {
        $this->instances = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param string $login
     * @return $this
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $provider
     * @return $this
     */
    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

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
     * @param Instance $instance
     * @return $this
     */
    public function addInstance(Instance $instance): self
    {
        $this->instances->add($instance);

        return $this;
    }
}
