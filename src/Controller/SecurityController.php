<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordType;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use App\Services\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername,
            'error' => $error
        ]);
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
            $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
            return $this->redirectToRoute($route);
        }


        return $this->render('security/edit_pass.html.twig', [
            'editPassForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/reinitialisation-mot-de-passe', name: 'app_reset_pass')]
    public function resetPassword(Request $request, MailerInterface $mailer, JWTService $jwt, UserRepository $userRepository): Response
    {

        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userEmail = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $userEmail]);

            if($user !== null ){

                if($user->isIsActive() == 1){
                    $header = [
                        'alg' => 'HS256',
                        'typ' => 'JWT'];
                    $payload = [
                        'user_id' => $user->getId(),
                    ];
                    $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
                    $email = (new TemplatedEmail())
                        ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                        ->to($user->getEmail())
                        ->subject('Réinitialisation de votre mot de passe')
                        ->htmlTemplate('user/reset_pass_email.html.twig')
                        ->context([
                            'token' => $token,


                        ]);
                    $mailer->send($email);
                    $this->addFlash('success', 'Un email a été envoyé pour réinitialiser votre mot de passe.');
                    return $this->redirectToRoute('app_login');
                } else {
                    $this->addFlash('danger', 'Votre compte est désactivé, vous ne pouvez pas réinitialiser votre mot de passe.');
                }

            }

        }

        return $this->render('security/reset_pass.html.twig', [
                'resetPassForm' => $form->createView(),
            ]
        );

    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
