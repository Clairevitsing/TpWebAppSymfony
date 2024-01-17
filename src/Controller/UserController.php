<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;



class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'app_user_list', methods: ['GET'])]
    public function listUsers(
        Request $request,
        PaginatorInterface $paginator,
        UserRepository $userRepository,
    ):Response {

        $users = $paginator->paginate(
            $userRepository->findAll(),
            $request->query->getInt('page',1),
        12 );

        return $this->render('admin/users/index.html.twig', [
            'users'=> $users
        ]);
    }

   #[Route('/admin/users/show/{id}', name: 'app_user_show', methods: ['GET'])]
    public function showUser(User $user
    ):Response {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/users/add', name: 'app_user_register')]
    public function createUser(
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        //dump($authenticationUtils->getLastAuthenticationError());

        $user = new User();

        $form = $this->createForm(UserFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Persist the users to the database
                $roles=$form->get('roles')->getData();
                $roles = array_unique($roles);
                $user->setRoles($roles);

                print_r($user-> getRoles());

                //var_dump($users);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Le formulaire a été soumis avec succès.');
            } else {
                $this->addFlash('errors', "Le formulaire n'a pas été soumis avec succès.");
            }
        }


        return $this->render('admin/users/register.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true),
            'users'=> $user
        ]);
    }

    #[Route('/admin/users/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function editUser($id, EntityManagerInterface $em, UserRepository $userRepository, Request $request): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData);
            $user = $form->getData();
            // dd($user);
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'The new product has been editted successfully'
            );
            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/users/{id} ', name: 'app_user_delete', methods: ['GET'])]
    public function deleteUser($id, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        if (!$user) {
            $this->addFlash(
                'Success',
                'Don\'t find product in question!'
            );
            return $this->redirectToRoute('app_user_list');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash(
            'success',
            'The new product has been deleted successfully'
        );
        return $this->redirectToRoute('app_user_list');
    }

}
