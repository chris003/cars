<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RentRepository")
 */
class Rent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("rent")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("rent")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("rent")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="rents")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("rent_user")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Car", inversedBy="rents")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("rent_car")
     */
    private $car;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): self
    {
        $this->car = $car;

        return $this;
    }
}
