<?php

namespace App\Controller;


use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    // La fonction "register" avec la route "app_register" a été supprimée car nous n'aurons pas de formulaire d'enregistrement

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            $user->setIsActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_login');
        }
        $this->addFlash('success', 'Votre adresse email a bien été vérifiée.');

        // redirection de l'utilisateur vers une modification du mot de passe
        $route = 'app_edit_pass';

        return $this->redirectToRoute($route);
    }

}
