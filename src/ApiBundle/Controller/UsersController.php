<?php
namespace ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;

use ApiBundle\Entity\User;

class UsersController extends FOSRestController
{
    /**
     * findStackOverFlowerByRequest
     *
     * @param Request $request
     * @return StackOverFlower
     * @throws NotFoundException
     */
    private function findUserByRequest(Request $request)
    {

        $id = $request->get('id');
        $user = $this->getDoctrine()->getManager()->getRepository("ApiBundle:User")->findOneBy(array('id' => $id));

        return $user;
    }

    /**
     * validateAndPersistEntity
     *
     * @param User $user
     * @param Boolean $delete
     * @return View the view
     */
    private function validateAndPersistEntity(User $user, $delete = false)
    {

        $validator = $this->get('validator');
        $errors_list = $validator->validate($user);

        if (count($errors_list) == 0) {

            try {
              $em = $this->getDoctrine()->getManager();

              if ($delete === true) {
                  $em->remove($user);
              } else {
                  $em->persist($user);
              }

              $em->flush();

              $response_content = ['user' => $user];
              $status = 200;
            } catch (\Exception $e) {
                $response_content = ['error' => $e->getMessage()];
                $status = 500;
            }

        } else {

            $errors = "";
            foreach ($errors_list as $error) {
              $errors .= (string) $error->getMessage();
            }

            $response_content = ['error' => $errors];
            $status = 500;

        }

        return $this->getResponse($response_content,$status);

    }

    /**
     * newUserAction
     *
     * @Post("/users/new")
     *
     * @param Request $request
     * @return String
     */
    public function newUserAction(Request $request)
    {
      $user = new User();

      $user->setUsername($request->get('username'));

      $plainPassword = $request->get('password');
      $encoder = $this->container->get('security.password_encoder');
      $encoded = $encoder->encodePassword($user, $plainPassword);
      $user->setPassword($encoded);

      $user->setEmail($request->get('email'));
      $user->setIsActive(1);

      $user->setFirstName($request->get('first_name'));
      $user->setLastName($request->get('last_name'));

      $user->setCountry($request->get('country'));
      $user->setCity($request->get('city'));
      $user->setZip($request->get('zip'));
      $user->setAddress($request->get('address'));

      $create_at = new \DateTime('now');
      $user->setCreateAt($create_at);

      $encoded_api_key = $encoder->encodePassword($user, $request->get('username').$plainPassword);
      $user->setApiKey($encoded_api_key);

      $response = $this->validateAndPersistEntity($user);

      return $response;
    }

    /**
     * editUserAction
     *
     * @Post("/users/edit/{id}")
     *
     * @param Request $request
     * @return type
     */
    public function editUserAction(Request $request)
    {

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
        $user->setIsActive(1);

        $user->setFirstName($request->get('first_name'));
        $user->setLastName($request->get('last_name'));

        $user->setCountry($request->get('country'));
        $user->setCity($request->get('city'));
        $user->setZip($request->get('zip'));
        $user->setAddress($request->get('address'));

        $create_at = new \DateTime('now');
        $user->setCreateAt($create_at);

        $response = $this->validateAndPersistEntity($user);

        return $response;
    }

    /**
     * deleteUserAction
     *
     * @Get("/users/delete/{id}")
     *
     * @param Request $request
     * @return type
     */
    public function deleteUserAction(Request $request)
    {
        $user = $this->findUserByRequest($request);

        if (! $user) {
          //$view = $this->view("No User found for this id:". $request->get('id'), 404);
            $view = $this->view(['error' => "No User found for this id:". $request->get('id')])
                         ->setTemplateVar('errors')
                         ->setTemplate($template);
            return $this->handleView($view);
        }

        $response = $this->validateAndPersistEntity($user, true);

        return $response;
    }

    /**
     * getUsersAction
     *
     * @Get("/users")
     *
     * @param Request $request
     * @return type
     */
    public function getUsersAction(Request $request)
    {

        $users = $this->getDoctrine()->getManager()->getRepository("ApiBundle:User")->findAll();
        // print_r($users);die;
        if (count($users) === 0) {
            $response_content = ['error' => "No User found."];
            $status = 404;
        }else {
            $response_content = $users;
            $status = 200;
        }

        return $this->getResponse($response_content,$status);
    }

    /**
     * loginAction
     *
     * @Post("/login")
     *
     * @param Request $request
     * @return String
     */
    public function loginAction(Request $request)
    {

        $username = $request->get('username');
        $password = $request->get('password');

        $factory = $this->get('security.encoder_factory');

        $bool = false;
        $user = $this->getDoctrine()->getManager()->getRepository("ApiBundle:User")->findOneBy(array('username' => $username));

        if($user){
          $encoder = $factory->getEncoder($user);
          $bool = ($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) ? true : false;
        }

        if($bool == true){
          $response_content = ['api_key' => $user->getApiKey() ];
          $status = 200;
        }else{
          $response_content = ['error' => "Error login"];
          $status = 500;
        }

        return $this->getResponse($response_content,$status);

    }

    private function getResponse($response_content,$status)
    {
      $serializer = $this->container->get('serializer');
      $response_content = $serializer->serialize($response_content, 'json');
      $response = new Response($response_content, $status );
      $response->headers->set( 'Content-Type', 'application/json' );

      return $response;
    }
}
