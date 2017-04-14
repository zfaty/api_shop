<?php

namespace ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;

use ApiBundle\Entity\ShopUser;

class ShopUserController extends FOSRestController
{

    /**
     * findUserByRequest
     *
     * @param Request $request
     * @return StackOverFlower
     * @throws NotFoundException
     */
    private function findUserByRequest(Request $request) {

        $id = $request->get('id');
        $user = $this->getDoctrine()->getManager()->getRepository("ApiBundle:ShopUser")->findOneBy(array('id' => $id));

        return $user;
    }

    /**
     * validateAndPersistEntity
     *
     * @param User $user
     * @param Boolean $delete
     * @return View the view
     */
    private function validateAndPersistEntity(ShopUser $user, $delete = false) {

        $template = "ApiBundle:ShopUser:users.html.twig";

        $validator = $this->get('validator');
        $errors_list = $validator->validate($user);

        if (count($errors_list) == 0) {

            $em = $this->getDoctrine()->getManager();

            if ($delete === true) {
                $em->remove($user);
            } else {
                $em->persist($user);
            }

            $em->flush();

            $view = $this->view($user)
                         ->setTemplateVar('user')
                         ->setTemplate($template);
        } else {

            $errors = "";
            foreach ($errors_list as $error) {
                $errors .= (string) $error->getMessage();
            }

            $view = $this->view($errors)
                         ->setTemplateVar('errors')
                         ->setTemplate($template);

        }

        return $view;
    }

    /**
     * newUserAction
     *
     * @Post("/shop/user")
     *
     * @param Request $request
     * @return String
     */
    public function newUserAction(Request $request)
    {
        $user = new ShopUser();
      //  print_r($request);die;
        $user->setUsername($request->get('username'));

        $plainPassword = $request->get('password');
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);

        $user->setEmail($request->get('email'));

        $user->setFirstName($request->get('first_name'));
        $user->setLastName($request->get('last_name'));

        $user->setCountry($request->get('country'));
        $user->setCity($request->get('city'));
        $user->setZip($request->get('zip'));
        $user->setAddress($request->get('address'));

        $create_at = new \DateTime('now');
        $user->setCreateAt($create_at);


        $view = $this->validateAndPersistEntity($user);

        return $this->handleView($view);
    }

    /**
     * editUserAction
     *
     * @Put("/shop/user/{id}")
     *
     * @param Request $request
     * @return type
     */
    public function editUserAction(Request $request) {

        $user = $this->findUserByRequest($request);

        if (! $user) {
            $view = $this->view("No User found for this id:". $request->get('id'), 404);
            return $this->handleView($view);
        }

        $plainPassword = $request->get('password');
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);

        $user->setEmail($request->get('email'));

        $user->setFirstName($request->get('first_name'));
        $user->setLastName($request->get('last_name'));

        $user->setCountry($request->get('country'));
        $user->setCity($request->get('city'));
        $user->setZip($request->get('zip'));
        $user->setAddress($request->get('address'));

        $view = $this->validateAndPersistEntity($user);

        return $this->handleView($view);
    }

    /**
     * deleteUserAction
     *
     * @Delete("/shop/user/{id}")
     *
     * @param Request $request
     * @return type
     */
    public function deleteUserAction(Request $request) {
        $template = "ApiBundle:ShopUser:users.html.twig";
        $user = $this->findUserByRequest($request);

        if (! $user) {
          //$view = $this->view("No User found for this id:". $request->get('id'), 404);
            $view = $this->view("No User found for this id:". $request->get('id'))
                         ->setTemplateVar('errors')
                         ->setTemplate($template);
            return $this->handleView($view);
        }

        $view = $this->validateAndPersistEntity($user, true);

        return $this->handleView($view);
    }

    /**
     * getUserAction
     *
     * @Get("/shop/user/{id}")
     *
     * @param Request $request
     * @return type
     */
    public function getUserAction(Request $request) {

      $template = "ApiBundle:ShopUser:users.html.twig";
      $user = $this->findUserByRequest($request);

      if (! $user) {
        //$view = $this->view("No User found for this id:". $request->get('id'), 404);
          $view = $this->view("No User found for this id:". $request->get('id'))
                       ->setTemplateVar('errors')
                       ->setTemplate($template);
          return $this->handleView($view);
      }

        $view = $this->view($user)
                     ->setTemplateVar('user')
                     ->setTemplate($template);

        return $this->handleView($view);
    }

    /**
     * getUserAction
     *
     * @Get("/shop/users")
     *
     * @param Request $request
     * @return type
     */
    public function getUsersAction(Request $request) {

        $template = "ApiBundle:ShopUser:users.html.twig";

        $users = $this->getDoctrine()->getManager()->getRepository("ApiBundle:ShopUser")->findAll();

        if (count($users) === 0) {
            $view = $this->view("No User found.", 404);
            return $this->handleView();
        }

        $view = $this->view($users)
                     ->setTemplateVar('users')
                     ->setTemplate($template);

        return $this->handleView($view);
    }
}
