<?php

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\Rent;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        $user = new User();
        $user->setLastName($faker->lastName());
        $user->setFirstName($faker->firstName());
        $user->setEmail("admin@outlooktest.com");
        $user->setPassword($this->encoder->encodePassword($user, 'Az+1111111'));
        $user->setPhone($faker->phoneNumber());
        $user->setStreet($faker->streetName());
        $user->setStreetNum($faker->buildingNumber());
        $user->setPostalCode($faker->postcode());
        $user->setLocality($faker->city());
        $user->setCountry($faker->country());
        $user->addRole(User::ROLE_ADMIN);

        $manager->persist($user);

        $user = new User();
        $user->setLastName($faker->lastName());
        $user->setFirstName($faker->firstName());
        $user->setEmail("user@outlooktest.com");
        $user->setPassword($this->encoder->encodePassword($user, 'Az+1111111'));
        $user->setPhone($faker->phoneNumber());
        $user->setStreet($faker->streetName());
        $user->setStreetNum($faker->buildingNumber());
        $user->setPostalCode($faker->postcode());
        $user->setLocality($faker->city());
        $user->setCountry($faker->country());
        $user->addRole(User::ROLE_USER);

        $manager->persist($user);

        $car = new Car();
        $car->setBrand('Ford');
        $car->setModel('GT');
        $car->setDoors(3);
        $car->setTransmission(CAR::MANUAL_TRANSMISSION);
        $car->setEmission(150);
        $car->setSeats(2);
        $car->setPrice(50000);
        $car->setImageName('Ford_GT.jpg');

        $manager->persist($car);

        $car = new Car();
        $car->setBrand('Ford');
        $car->setModel('Mustang');
        $car->setDoors(3);
        $car->setTransmission(CAR::MANUAL_TRANSMISSION);
        $car->setEmission(165);
        $car->setSeats(2);
        $car->setPrice(60000);
        $car->setImageName('Ford_Mustang.png');

        $manager->persist($car);

        $car = new Car();
        $car->setBrand('Ford');
        $car->setModel('Focus');
        $car->setDoors(5);
        $car->setTransmission(CAR::MANUAL_TRANSMISSION);
        $car->setEmission(100);
        $car->setSeats(5);
        $car->setPrice(15000);
        $car->setImageName('Ford_Focus.jpg');

        $manager->persist($car);

        $car = new Car();
        $car->setBrand('Ford');
        $car->setModel('Fiesta');
        $car->setDoors(5);
        $car->setTransmission(CAR::AUTOMATIC_TRANSMISSION);
        $car->setEmission(80);
        $car->setSeats(5);
        $car->setPrice(10000);
        $car->setImageName('Ford_Fiesta.jpg');

        $manager->persist($car);

        $car = new Car();
        $car->setBrand('Ford');
        $car->setModel('Mondeo');
        $car->setDoors(5);
        $car->setTransmission(CAR::MANUAL_TRANSMISSION);
        $car->setEmission(110);
        $car->setSeats(5);
        $car->setPrice(20000);
        $car->setImageName('Ford_Mondeo.jpg');

        $manager->persist($car);

        for ($i = 1; $i <= 10; $i++) {

            $user = new User();
            $user->setLastName($faker->lastName());
            $user->setFirstName($faker->firstName());
            $user->setEmail($faker->email());
            $user->setPassword($this->encoder->encodePassword($user, 'Az+1111111'));
            $user->setPhone($faker->phoneNumber());
            $user->setStreet($faker->streetName());
            $user->setStreetNum($faker->buildingNumber());
            $user->setPostalCode($faker->postcode());
            $user->setLocality($faker->city());
            $user->setCountry($faker->country());
            $user->addRole(User::ROLE_USER);

            $manager->persist($user);
        }
        $manager->flush();
    }
}
