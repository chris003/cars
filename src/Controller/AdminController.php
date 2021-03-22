<?php

namespace App\Controller;

use DateTime;
use App\Entity\Car;
use App\Entity\Rent;
use App\Entity\User;
use App\Form\CarType;
use App\Form\RegistrationType;
use App\Repository\CarRepository;
use App\Repository\RentRepository;
use App\Repository\UserRepository;
use App\Form\ProfilModificationType;
use App\Form\PasswordModificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
  /**
   * @Route("/admin", name="admin_home")
   */
  public function index()
  {
    return $this->render('admin/home.html.twig');
  }

  /**
   * @Route("/admin/profil", name="admin_profil")
   */
  public function profil(Request $request, EntityManagerInterface $manager, UserRepository $repo)
  {
    $user = $this->getUser();
    $password = $this->getUser()->getPassword();

    $form = $this->createForm(ProfilModificationType::class, $user);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

      if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword($password);

        try {
          $manager->flush();

          return $this->json([
            'status' => 200,
            'alert' => 'alert-success',
            'message' => 'Votre profil a bien été modifié !',
            'profilFirstName' => $user->getFirstName(),
            'profilLastName' => $user->getLastName(),
          ], 200);
        } catch (Exception $e) {
          return $this->json([
            'status' => 400,
            'message' => $e->getMessage()
          ], 400);
        }
      }

      return $this->json([
        'status' => 200,
        'alert' => 'alert-danger',
        'message' => 'Votre profil n\'a pas pu être modifié !'
      ], 200);
    }

    return $this->render('admin/profil.html.twig', [
      'formUser' => $form->createView()
    ]);
  }

  /**
   * @Route("/admin/password", name="admin_password")
   */
  public function password(Request $request, EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
  {
    $user = $this->getUser();
    $password = $user->getPassword();

    $form = $this->createForm(PasswordModificationType::class, $user);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {
      $errors = $validator->validate($user);
      $errorsString = '';

      if (count($errors) > 0) {

        foreach ($errors as $error) {
          $errorsString = $errorsString . $error->getMessage() . '<br>';
        }

        try {
          $manager->refresh($this->getUser());
        } catch (Exception $e) {
          return $this->json([
            'status' => 400,
            'message' => $e->getMessage()
          ], 400);
        }

        return $this->json([
          'status' => 200,
          'alert' => 'alert-danger',
          'message' => $errorsString
        ], 200);
      }

      if ($form->isSubmitted()) {

        if ($form->isValid()) {
          $newPassword = $user->getPassword();
          $user->setPassword($password);
          $match = $encoder->isPasswordValid($user, $newPassword);

          if (!$match) {
            $user->setPassword($encoder->encodePassword($user, $newPassword));
            $repo->passwordUpdate($this->getUser(), $user->getPassword());

            try {
              $manager->refresh($this->getUser());

              if ($this->getUser()->getPassword() == $user->getPassword()) {
                return $this->json([
                  'status' => 200,
                  'alert' => 'alert-success',
                  'message' => 'Votre mot de passe a bien été modifié !'
                ], 200);
              } else {
                $this->getUser()->setPassword($password);
                return $this->json([
                  'status' => 200,
                  'alert' => 'alert-warning',
                  'message' => 'Ce mot de passe est déjà utilisé !'
                ], 200);
              }
            } catch (Exception $e) {
              return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
              ], 400);
            }
          } else {
            try {
              $manager->refresh($this->getUser());
            } catch (Exception $e) {
              return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
              ], 400);
            }
          }
        }

        try {
          $manager->refresh($this->getUser());
        } catch (Exception $e) {
          return $this->json([
            'status' => 400,
            'message' => $e->getMessage()
          ], 400);
        }

        return $this->json([
          'status' => 200,
          'alert' => 'alert-danger',
          'message' => 'Votre mot de passe n\'a pas pu être modifié !'
        ], 200);
      }
    }

    return $this->render('admin/password.html.twig', [
      'formUser' => $form->createView()
    ]);
  }

  /**
   * @Route("/admin/getUsers", name="admin_getUsers")
   */
  public function getUsers(UserRepository $repo)
  {
    $users = $repo->findUsers();

    return $this->json($users, 200, [], ['groups' => 'user']);
  }

  /**
   * @Route("/admin/addUser", name="admin_addUser")
   */
  public function addUser(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
  {
    $user = new User();

    $form = $this->createForm(RegistrationType::class, $user);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

      if ($form->isSubmitted()) {

        if ($form->isValid()) {
          $hash = $encoder->encodePassword($user, $user->getPassword());
          $user->setPassword($hash);
          $user->addRole(User::ROLE_USER);
          $manager->persist($user);

          try {
            $manager->flush();
          } catch (Exception $e) {
            return $this->json([
              'status' => 400,
              'message' => $e->getMessage()
            ], 400);
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-success',
            'message' => 'L\'utilisateur "' . $user->getFirstName() . ' ' . $user->getLastName() . '" a bien été ajouté !'
          ], 200);
        } else {
          $errors = $validator->validate($user);
          $errorsString = '';

          foreach ($errors as $error) {
            $errorsString = $errorsString . $error->getMessage() . '<br>';
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-danger',
            'message' => $errorsString
          ], 200);
        }
      }

      return $this->json([
        'status' => 200,
        'alert' => 'alert-danger',
        'message' => 'L\'utilisateur "' . $user->getFirstName() . ' ' . $user->getLastName() . '" n\' a pas pu être ajouté !'
      ], 200);
    }

    return $this->render('admin/userAdd.html.twig', [
      'formUser' => $form->createView()
    ]);
  }

  /**
   * @Route("/admin/{id}/editUser", name="admin_editUser")
   */
  public function editUser(User $user, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
  {
    $form = $this->createForm(ProfilModificationType::class, $user);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

      if ($user->getId() == 1) {
        return $this->json([
          'status' => 200,
          'alert' => 'alert-danger',
          'message' => 'Le compte administrateur ne peux pas être modifié !'
        ], 200);
      }

      if ($form->isSubmitted()) {

        if ($form->isValid()) {
          $hash = $encoder->encodePassword($user, $user->getPassword());
          $user->setPassword($hash);
          $manager->persist($user);

          try {
            $manager->flush();
          } catch (Exception $e) {
            return $this->json([
              'status' => 400,
              'message' => $e->getMessage()
            ], 400);
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-success',
            'message' => 'Le profil a bien été modifié !'
          ], 200);
        } else {
          $errors = $validator->validate($user);
          $errorsString = '';

          foreach ($errors as $error) {
            $errorsString = $errorsString . $error->getMessage() . '<br>';
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-danger',
            'message' => $errorsString
          ], 200);
        }
      }

      return $this->json([
        'status' => 200,
        'alert' => 'alert-danger',
        'message' => 'Le profil n\'a pas pu être modifié !'
      ], 200);
    }

    return $this->render('admin/userEdit.html.twig', [
      'formUser' => $form->createView(),
      'userFirstName' => $user->getFirstName(),
      'userLastName' => $user->getLastName(),
      'userId' => $user->getId()
    ]);
  }

  /**
   * @Route("/admin/{id}/editUserPassword", name="admin_editUserPassword")
   */
  public function editUserPassword(User $user, Request $request, EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
  {
    $user = $repo->find($user->getId());

    $password = $user->getPassword();

    $form = $this->createForm(PasswordModificationType::class, $user);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

      if ($user->getId() == 1) {
        return $this->json([
          'status' => 200,
          'alert' => 'alert-danger',
          'message' => 'Le compte administrateur ne peux pas être modifié !'
        ], 200);
      }

      $errors = $validator->validate($user);
      $errorsString = '';

      if (count($errors) > 0) {

        foreach ($errors as $error) {
          $errorsString = $errorsString . $error->getMessage() . '<br>';
        }

        return $this->json([
          'status' => 200,
          'alert' => 'alert-danger',
          'message' => $errorsString
        ], 200);
      }

      if ($form->isSubmitted()) {

        if ($form->isValid()) {
          $newPassword = $user->getPassword();
          $user->setPassword($password);
          $match = $encoder->isPasswordValid($user, $newPassword);

          if (!$match) {
            $user->setPassword($encoder->encodePassword($user, $newPassword));
            $manager->persist($user);

            try {
              $manager->flush();
            } catch (Exception $e) {
              return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
              ], 400);
            }
          } else {
            try {
              $manager->refresh($this->getUser());
            } catch (Exception $e) {
              return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
              ], 400);
            }
            return $this->json([
              'status' => 200,
              'alert' => 'alert-success',
              'message' => 'Le mot de passe a bien été modifié !'
            ], 200);
          }
        }

        return $this->json([
          'status' => 200,
          'alert' => 'alert-danger',
          'message' => 'Le mot de passe n\'a pas pu être modifié !'
        ], 200);
      }
    }

    return $this->render('admin/userPasswordEdit.html.twig', [
      'formUser' => $form->createView(),
      'userFirstName' => $user->getFirstName(),
      'userLastName' => $user->getLastName(),
      'userId' => $user->getId()
    ]);
  }


  /**
   * @Route("/admin/removeUser", name="admin_removeUser")
   */
  public function removeUser(UserRepository $repo, Request $request, EntityManagerInterface $manager)
  {
    $userId = $request->request->get('userId');

    try {
      $user = $repo->findOneBy(array('id' => $userId));
    } catch (Exception $e) {
      return $this->json([
        'status' => 400,
        'message' => $e->getMessage()
      ], 400);
    }

    if ($request->isXmlHttpRequest()) {

      if ($user->getId() == 1) {
        return $this->json([
          'status' => 200,
          'alert' => 'alert-danger',
          'message' => 'Le compte administrateur ne peux pas être modifié !'
        ], 200);
      }

      try {
        $manager->remove($user);
      } catch (Exception $e) {
        return $this->json([
          'status' => 400,
          'message' => $e->getMessage()
        ], 400);
      }

      try {
        $manager->flush();

        return $this->json([
          'status' => 200,
          'alert' => 'alert-success',
          'message' => 'L\'utilisateur \'' . $user->getFirstName() . ' ' . $user->getLastName() . '\' a bien été supprimé !'
        ], 200);
      } catch (Exception $e) {
        return $this->json([
          'status' => 400,
          'message' => $e->getMessage()
        ], 400);
      }
    }

    return $this->json([
      'status' => 200,
      'alert' => 'alert-danger',
      'message' => 'L\'utilisateur \'' . $user->getFirstName() . ' ' . $user->getLastName() . '\' n\'a pas pu être supprimé !'
    ], 200);
  }

  /**
   * @Route("/admin/usersList", name="admin_usersList")
   */
  public function usersList(UserRepository $repo)
  {
    return $this->render(
      'admin/usersList.html.twig',
      [
        'usersNbr' => $repo->countUsers()
      ]
    );
  }

  /**
   * @Route("/admin/getCars", name="admin_getCars")
   */
  public function getCars(CarRepository $repo)
  {
    $cars = $repo->findBy([], ['brand' => 'asc', 'model' => 'desc']);

    return $this->json($cars, 200, [], ['groups' => 'car']);
  }

  /**
   * @Route("/admin/carsList", name="admin_carsList")
   */
  public function carsList(CarRepository $repo)
  {
    return $this->render(
      'admin/carsList.html.twig',
      [
        'carsNbr' => $repo->countCars()
      ]
    );
  }


  /**
   * @Route("/admin/{id}/editCar", name="admin_editCar")
   */
  public function editCar(Car $car, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
  {
    $form = $this->createForm(CarType::class, $car);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

      if ($form->isSubmitted()) {

        if ($form->isValid()) {
          $manager->persist($car);

          try {
            $manager->flush();
          } catch (Exception $e) {
            return $this->json([
              'status' => 400,
              'message' => $e->getMessage()
            ], 400);
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-success',
            'message' => 'Le véhicule a bien été modifié !',
            'carBrand' => $car->getBrand(),
            'carModel' => $car->getModel(),
            'carImageName' => $car->getImageName()
          ], 200);
        } else {
          $errors = $validator->validate($car);
          $errorsString = '';

          foreach ($errors as $error) {
            $errorsString = $errorsString . $error->getMessage() . '<br>';
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-danger',
            'message' => $errorsString
          ], 200);
        }
      }

      return $this->json([
        'status' => 200,
        'alert' => 'alert-danger',
        'message' => 'Le véhicule n\'a pas pu être modifié !'
      ], 200);
    }

    return $this->render('admin/carEdit.html.twig', [
      'formCar' => $form->createView(),
      'car' => $car
    ]);
  }

  /**
   * @Route("/admin/addCar", name="admin_addCar")
   */
  public function addCar(Request $request, EntityManagerInterface $manager, ValidatorInterface $validator)
  {
    $car = new Car();

    $form = $this->createForm(CarType::class, $car);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

      if ($form->isSubmitted()) {

        if ($form->isValid()) {
          $manager->persist($car);

          try {
            $manager->flush();
          } catch (Exception $e) {
            return $this->json([
              'status' => 400,
              'message' => $e->getMessage()
            ], 400);
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-success',
            'message' => 'Le véhicule "' . $car->getBrand() . ' ' . $car->getModel() . '" a bien été ajouté !'
          ], 200);
        } else {
          $errors = $validator->validate($car);
          $errorsString = '';

          foreach ($errors as $error) {
            $errorsString = $errorsString . $error->getMessage() . '<br>';
          }

          return $this->json([
            'status' => 200,
            'alert' => 'alert-danger',
            'message' => $errorsString
          ], 200);
        }
      }

      return $this->json([
        'status' => 200,
        'alert' => 'alert-danger',
        'message' => 'Le véhicule "' . $car->getBrand() . ' ' . $car->getModel() . '" n\' a pas pu être ajouté !'
      ], 200);
    }

    return $this->render('admin/carAdd.html.twig', [
      'formCar' => $form->createView()
    ]);
  }

  /**
   * @Route("/admin/removeCar", name="admin_removeCar")
   */
  public function removeCar(CarRepository $repo, Request $request, EntityManagerInterface $manager)
  {
    $carId = $request->request->get('carId');

    try {
      $car = $repo->findOneBy(array('id' => $carId));
    } catch (Exception $e) {
      return $this->json([
        'status' => 400,
        'message' => $e->getMessage()
      ], 400);
    }

    if ($request->isXmlHttpRequest()) {

      try {
        $manager->remove($car);
      } catch (Exception $e) {
        return $this->json([
          'status' => 400,
          'message' => $e->getMessage()
        ], 400);
      }

      try {
        $manager->flush();

        return $this->json([
          'status' => 200,
          'alert' => 'alert-success',
          'message' => 'Le véhicule "' . $car->getBrand() . ' ' . $car->getModel() . '" a bien été supprimé !'
        ], 200);
      } catch (Exception $e) {
        return $this->json([
          'status' => 400,
          'message' => $e->getMessage()
        ], 400);
      }
    }

    return $this->json([
      'status' => 200,
      'alert' => 'alert-danger',
      'message' => 'Le véhicule "' . $car->getBrand() . ' ' . $car->getModel() . '" n\'a pas pu être supprimé !'
    ], 200);
  }

  /**
   * @Route("/admin/{id}/carRents", name="admin_carRents")
   */
  public function carRents(Car $car = null, Request $request)
  {
    return $this->render('admin/carRents.html.twig', [
      'car' => $car
    ]);
  }

  /**
   * @Route("/admin/{id}/getCarRents", name="admin_getCarRents")
   */
  public function getCarRents(Car $car = null)
  {
    $json = [];

    foreach ($car->getRents() as $rent) {
      array_push($json, array('id' => $rent->getId(), 'title' => $rent->getUser()->getFirstName() . ' ' . $rent->getUser()->getLastName(), 'start' => $rent->getStartDate(), 'end' => $rent->getEndDate(), 'userId' => $rent->getUser()->getId()));
    }

    return $this->json($json, 200);
  }

  /**
   * @Route("/admin/{id}/getUserRents", name="admin_getUserRents")
   */
  public function getUserRents(User $user = null)
  {
    $json = [];

    foreach ($user->getRents() as $rent) {
      array_push($json, array('id' => $rent->getId(), 'title' => $rent->getCar()->getBrand() . ' ' . $rent->getCar()->getModel(), 'start' => $rent->getStartDate(), 'end' => $rent->getEndDate()));
    }

    return $this->json($json, 200);
  }

  /**
   * @Route("/admin/{id}/userRents", name="admin_userRents")
   */
  public function userRents(User $user = null, Request $request)
  {
    return $this->render('admin/userRents.html.twig', [
      'user' => $user
    ]);
  }

  /**
   * @Route("/admin/removeRent", name="admin_removeRent")
   */
  public function removeRent(RentRepository $repo, Request $request, EntityManagerInterface $manager)
  {
    $id = $request->request->get('id');

    try {
      $rent = $repo->findOneBy(array('id' => $id));
    } catch (Exception $e) {
      return $this->json([
        'status' => 400,
        'message' => $e->getMessage()
      ], 400);
    }

    if ($request->isXmlHttpRequest()) {

      try {
        $manager->remove($rent);
      } catch (Exception $e) {
        return $this->json([
          'status' => 400,
          'message' => $e->getMessage()
        ], 400);
      }

      try {
        $manager->flush();

        return $this->json([
          'status' => 200,
          'alert' => 'alert-success',
          'message' => 'La réservation du véhicule "' . $rent->getCar()->getBrand() . ' ' . $rent->getCar()->getModel() . '" pour ' . $rent->getUser()->getFirstName() . ' ' . $rent->getUser()->getLastName() .  ' a bien été supprimée !'
        ], 200);
      } catch (Exception $e) {
        return $this->json([
          'status' => 400,
          'message' => $e->getMessage()
        ], 400);
      }
    }

    return $this->json([
      'status' => 200,
      'alert' => 'alert-danger',
      'message' => 'La réservation du véhicule "' . $rent->getCar()->getBrand() . ' ' . $rent->getCar()->getModel() . '" pour ' . $rent->getUser()->getFirstName() . ' ' . $rent->getUser()->getLastName() . ' n\'a pas pu être supprimé !'
    ], 200);
  }

  /**
   * @Route("/admin/{id}/carRentAdding", name="admin_carRentAdding")
   */
  public function carRentAdding(Request $request, Car $car, EntityManagerInterface $manager,  UserRepository $userRepo)
  {
    $startDateReceived = $request->request->get('start');
    $endDateReceived = $request->request->get('end');
    $startDateReceived = strtotime(current(explode("(", $startDateReceived)));
    $endDateReceived = strtotime(current(explode("(", $endDateReceived)));

    $now = new DateTime();
    $now = $now->getTimestamp();
    if ($now > $startDateReceived) return $this->json([
      'status' => 200,  'alert' => 'alert-danger',
      'message' => 'La date de réservation est dépassée !'
    ], 200);

    $userId = $request->request->get('userId');

    try {
      $user = $userRepo->findOneBy(array('id' => $userId));
    } catch (Exception $e) {
      return $this->json([
        'status' => 400,
        'message' => $e->getMessage()
      ], 400);
    }

    $rent = new Rent();
    $startDate = new DateTime();
    $endDate = new DateTime();

    $startDate->setTimestamp($startDateReceived);
    $endDate->setTimestamp($endDateReceived);

    $rent->setStartDate($startDate);
    $rent->setEndDate($endDate);

    if ($car->containsRent($rent)) return $this->json([
      'status' => 200,  'alert' => 'alert-danger',
      'message' => 'Le véhicule "' . $car->getBrand() . ' ' . $car->getModel() . '" est déjà réservé durant cette période !'
    ], 200);

    $rent->setUser($user);
    $rent->setCar($car);

    $manager->persist($rent);

    try {
      $manager->flush();
    } catch (Exception $e) {
      return $this->json([
        'status' => 400,
        'message' => $e->getMessage()
      ], 400);
    }

    return $this->json([
      'status' => 200,  'alert' => 'alert-success',
      'message' => 'La réservation du véhicule "' . $car->getBrand() . ' ' . $car->getModel() . '" a bien été prise en compte !'
    ], 200);
  }
}
