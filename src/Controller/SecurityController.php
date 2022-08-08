<?php

namespace App\Controller;

use App\Form\EditPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        // Retour d'un message d'erreur à la connexion, s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Dernier utilisateur connecté
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }



    #[Route(path: '/modifier-mot-de-passe', name: 'app_edit_pass')]
    public function editPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur
        $user = $this->getUser();
        // Création d'un formulaire de réinitialisation du mot de passe
        $form = $this->createForm(EditPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encodage du mot de passe en clair
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // En fonction du rôle attribué, les routes de redirection sont définies différemment
            if($this->isGranted('ROLE_PARTNER')){
                $route = 'app_partner';
            } elseif ($this->isGranted('ROLE_STRUCTURE')){
                $route = 'app_structure';
             }

            return $this->redirectToRoute($route);
        }


        return $this->render('security/edit_pass.html.twig', [
            'editPassForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/reinitialisation-mot-de-passe', name: 'app_reset_pass')]
    public function resetPassword(): Response
    {
        return $this->render('security/reset_pass.html.twig');
    }



    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
