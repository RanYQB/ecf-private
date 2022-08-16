<?php

namespace App\Controller;


use App\Security\EmailVerifier;
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
    // L'action d'ajout des utilisateurs est entièrement déléguée à l'administrateur dans l'AdminController

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Lorsque l'utilisateur suit le lien envoyé par mail, "is_verified" est défini à true (1)
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        // Une fois le compte vérifié, l'EmailVerifier redirige directement l'utilisateur à un formulaire
        // de réinitialisation du mot de passe à  sa première connexion.
        $route = 'app_edit_pass';


        return $this->redirectToRoute($route);
    }

}
