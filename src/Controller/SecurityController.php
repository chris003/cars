<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;

use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller qui permet la connexion et la déconnexion
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/home", name="security_home")
     */
    public function home(Request $request, CarRepository $repo, PaginatorInterface $paginator)
    {
        $cars = $repo->findBy([], ['brand' => 'asc', 'model' => 'desc']);

        // Pagination
        //    $cars = $paginator->paginate(
        //        $datas, // Requête contenant les données à paginer (ici nos articles)
        //        $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
        //        6 // Nombre de résultats par page
        //    );

        //    $cars->setCustomParameters([
        //     'align' => 'center',
        //     'style' => 'bottom',
        //     'span_class' => 'whatever',
        // ]);

        return $this->render('security/home.html.twig', [
            'cars' => $cars,
        ]);
    }

    /**
     * @Route("/registration", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

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
                        'message' => 'Vous êtes désormais inscrit ! Veuillez-vous connecter !'
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
                'message' => $user->getEmail()
            ], 200);
        }

        return $this->render('security/registration.html.twig', [
            'formUser' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        if ($this->getUser() == null) // Test si l'utilisateur n'existe pas, dans ce cas une erreur est renvoyée.
        {
            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error
            ]);
        } else if (in_array("ROLE_USER", $this->getUser()->getRoles())) // Connexion utilisateur
        {
            return $this->redirectToRoute('member_home');
        } else if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) // Connexion admin
        {
            return $this->redirectToRoute('admin_usersList');
        }
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
    }

    /**
     * @Route("/getRents", name="getRents")
     */
    public function getRents(CarRepository $repo)
    {
        $cars = $repo->findBy([], ['brand' => 'asc', 'model' => 'desc']);

        return $this->json($cars, 200, [], ['groups' => 'car']);
    }

    /**
     * @Route("/help", name="help")
     */
    public function carRents()
    {
        return $this->render('security/help.html.twig');
    }
}
