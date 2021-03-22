<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        if($this->getUser() == null)
        {
             return $this->redirectToRoute('security_home');
        }

        else if(in_array("ROLE_USER", $this->getUser()->getRoles()))
        {
            return $this->redirectToRoute('member_home');
        }

        else if(in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            return $this->redirectToRoute('admin_usersList');
        } 
    }
}
