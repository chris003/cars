<?php

namespace App\Controller;

use DateTime;
use App\Entity\Car;
use App\Entity\Rent;
use App\Entity\User;
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

class MemberController extends AbstractController
{
  /**
   * @Route("/member", name="member_home")
   */
  public function home(CarRepository $repo)
  {
    $cars = $repo->findBy([], ['brand' => 'asc', 'model' => 'desc']);

    return $this->render('member/home.html.twig', [
      'cars' => $cars,
    ]);
  }

  /**
   * @Route("/member/profil", name="member_profil")
   */
  public function profil(Request $request, EntityManagerInterface $manager, ValidatorInterface $validator)
  {
    $user = $this->getUser();
    $password = $this->getUser()->getPassword();

    $form = $this->createForm(ProfilModificationType::class, $user);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

      if ($form->isSubmitted()) {

        if ($form->isValid()) {
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
        'message' => 'Votre profil n\'a pas pu être modifié !'
      ], 200);
    }

    return $this->render('member/profil.html.twig', [
      'formUser' => $form->createView()
    ]);
  }

  /**
   * @Route("/member/password", name="member_password")
   */
  public function password(Request $request, EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
  {
    $user = $this->getUser();
    $password = $user->getPassword();

    $form = $this->createForm(PasswordModificationType::class, $user);

    $form->handleRequest($request);

    if ($request->isXmlHttpRequest()) {

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
        } else {
          $errors = $validator->validate($user);
          $errorsString = '';

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
      }

      return $this->json([
        'status' => 200,
        'alert' => 'alert-danger',
        'message' => 'Votre mot de passe n\'a pas pu être modifié !'
      ], 200);
    }

    return $this->render('member/password.html.twig', [
      'formUser' => $form->createView()
    ]);
  }

  /**
   * @Route("/member/userRents", name="member_userRents")
   */
  public function userRents(Request $request)
  {
    return $this->render('member/userRents.html.twig');
  }

  /**
   * @Route("/member/{id}/carRents", name="member_carRents")
   */
  public function carRents(Car $car = null, Request $request)
  {
    return $this->render('member/carRents.html.twig', [
      'car' => $car
    ]);
  }

  /**
   * @Route("/member/getRents", name="member_getRents")
   */
  public function getRents(CarRepository $repo)
  {
    $cars = $repo->findBy([], ['brand' => 'asc', 'model' => 'desc']);

    return $this->json($cars, 200, [], ['groups' => 'car']);
  }

  /**
   * @Route("/member/getRentsWithUsers", name="member_getRentsWithUsers")
   */
  public function getRentsWithUsers(CarRepository $repo)
  {
    $cars = $repo->findBy([], ['brand' => 'asc', 'model' => 'desc']);

    return $this->json($cars, 200, [], ['groups' => ['car', 'car_rents', 'rent', 'rent_user', 'user']]);
  }

  /**
   * @Route("/member/{id}/getCarRents", name="member_getCarRents")
   */
  public function getCarRents(Car $car = null)
  {
    $json = [];

    foreach ($car->getRents() as $rent) {
      array_push($json, array('title' => 'Réservé', 'start' => $rent->getStartDate(), 'end' => $rent->getEndDate()));
    }

    return $this->json($json, 200);
  }

  /**
   * @Route("/member/{id}/carRentAdding", name="member_carRentAdding")
   */
  public function carRentAdding(Request $request, Car $car, EntityManagerInterface $manager)
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

    $user = $this->getUser();

    if (!$user) return $this->json([
      'status' => 400,
      'message' => "Unauthorized"
    ], 403);

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

  /**
   * @Route("/member/{id}/getUserRents", name="member_getUserRents")
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
   * @Route("/member/removeRent", name="member_removeRent")
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
          'message' => 'La réservation du véhicule "' . $rent->getCar()->getBrand() . ' ' . $rent->getCar()->getModel() . '" a bien été supprimée !'
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
      'message' => 'La réservation du véhicule "' . $rent->getCar()->getBrand() . ' ' . $rent->getCar()->getModel() . '" n\'a pas pu être supprimé !'
    ], 200);
  }
}
