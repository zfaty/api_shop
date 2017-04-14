<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ));
    }
    /**
     * loginAction
     *
     * @Route("/users/list" ,name="list")
     *
     * @param Request $request
     * @return String
     */
    public function loginAction(Request $request)
    {
        print_r($request);die;
        // $user = new User();
        // $user->setUsername($request->get('username'));
        //
        // $password = $this->get('security.password_encoder')
        //         ->encodePassword($user, $request->get('password'));
        // $user->setPassword($password);
        // $user->setEmail($request->get('email'));
        // $user->setIsActive($request->get('is_active'));
        //
        // $apiKey = $this->get('security.password_encoder')
        //         ->encodePassword($user, $request->get('username').$request->get('password'));
        //
        // $user->setApiKey($apiKey);
        // $view = $this->validateAndPersistEntity($user);
        //
        // return $this->handleView($view);
    }
}
