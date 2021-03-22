<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 * fields={"email"},
 * message="L'email que vous avez indiqué est déjà utilisé !"
 * )
 */
class User implements UserInterface
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    private $_EnumRole = array ( 
        self::ROLE_USER, self::ROLE_ADMIN); 

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("user")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *     message = "L'email '{{ value }}' n'est pas un email valide !"
     * )
     * @Groups("user")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min="10",
     *      max="30",
     *      minMessage="Votre mot de passe doit faire minimum 10 caractères !",
     *      maxMessage="Votre mot de passe doit faire maximum 30 caractères !"
     * )
     * @Assert\Regex(
     *     pattern="/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[*+\/!=@?\#|&$€%£\\-]).{10,30}$/",
     *     message="Votre mot de passe doit au moins contenir un chiffre, une minuscule, une majuscule. et un caractère special !"
     * )
     */
    private $password;

     /**
     * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas tapé le même mot de passe !")
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rent", mappedBy="user")
     * @Groups("user_rents")
     */
    private $rents;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $streetNum;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $locality;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("user")
     */
    private $country;

    /**
     * @ORM\Column(name="roles", type="array")
     * @Groups("user")
     */
    private $roles = array();

    public function __construct()
    {
        $this->rents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
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
            $rent->setUser($this);
        }

        return $this;
    }

    public function removeRent(Rent $rent): self
    {
        if ($this->rents->contains($rent)) {
            $this->rents->removeElement($rent);
            // set the owning side to null (unless already changed)
            if ($rent->getUser() === $this) {
                $rent->setUser(null);
            }
        }

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getStreetNum(): ?string
    {
        return $this->streetNum;
    }

    public function setStreetNum(string $streetNum): self
    {
        $this->streetNum = $streetNum;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(string $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    function addRole($role)
    {
        if (!in_array($role, $this->_EnumRole)) 
      { 
         throw new \InvalidArgumentException( 
            sprintf('Valeur invalide pour my_entity.my_enum_field : %s.', $role) 
         ); 
      } 
      $this->roles[] = $role;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
