<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CarRepository")
 * @Vich\Uploadable
 */
class Car
{
    const MANUAL_TRANSMISSION = 'M';
    const AUTOMATIC_TRANSMISSION = 'A';

    private $_EnumTransmission = array(
        self::MANUAL_TRANSMISSION, self::AUTOMATIC_TRANSMISSION
    );
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("car")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("car")
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("car")
     */
    private $model;

    /**
     * @ORM\Column(type="integer")
     * @Groups("car")
     */
    private $doors;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('M', 'A')")
     * @Groups("car")
     */
    private $transmission;

    /**
     * @ORM\Column(type="integer")
     * @Groups("car")
     */
    private $seats;

    /**
     * @ORM\Column(type="integer")
     * @Groups("car")
     */
    private $emission;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     * @Groups("car")
     */
    private $price;

    /** 
    * @Vich\UploadableField(mapping="car_image", fileNameProperty="imageName")
    * 
    * @var File|null
    */
    private $imageFile;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("car")
     *
     * @var string|null
     */
    private $imageName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rent", mappedBy="car")
     * @Groups("car_rents")
     */
    private $rents;

    public function __construct()
    {
        $this->rents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getDoors(): ?int
    {
        return $this->doors;
    }

    public function setDoors(int $doors): self
    {
        $this->doors = $doors;

        return $this;
    }

    public function getTransmission(): ?string
    {
        return $this->transmission;
    }

    public function setTransmission($transmission): self
    {
        if (!in_array($transmission, $this->_EnumTransmission)) {
            throw new \InvalidArgumentException(
                sprintf('Valeur invalide pour my_entity.my_enum_field : %s.', $transmission)
            );
        }

        $this->transmission = $transmission;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getEmission(): ?int
    {
        return $this->emission;
    }

    public function setEmission(int $emission): self
    {
        $this->emission = $emission;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    /**
     * @return Collection|Rent[]
     */
    public function getRents(): Collection
    {
        return $this->rents;
    }

    public function addRent(Rent $rent): self
    {
        if (!$this->rents->contains($rent)) {
            $this->rents[] = $rent;
            $rent->setCar($this);
        }

        return $this;
    }

    public function removeRent(Rent $rent): self
    {
        if ($this->rents->contains($rent)) {
            $this->rents->removeElement($rent);
            // set the owning side to null (unless already changed)
            if ($rent->getCar() === $this) {
                $rent->setCar(null);
            }
        }

        return $this;
    }

    public function containsRent(Rent $rent): bool
    {
        foreach ($this->rents as $item) {
            
            if ($rent->getStartDate() <= $item->getEndDate() && $rent->getEndDate() >= $item->getStartDate()) {
                return true;
            }
        }
        return false;
    }
}
