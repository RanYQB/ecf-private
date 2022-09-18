<?php

namespace App\Controller;

use App\Form\EditPasswordType;
use App\Repository\UserRepository;
use App\Services\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    #[Route('/definir-mot-de-passe/{token}', name: 'app_new_pass')]
    public function createPass($token, JWTService $jwt, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret')))
        {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);

            if($user !== null){
                $form = $this->createForm(EditPasswordType::class, $user);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // encodage du mot de passe
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );

                    $entityManager->persist($user);
                    $entityManager->flush();

                    // message flash et redirection
                    $this->addFlash('success', 'Votre mot de passe a bien été réinitialisé.');
                    return $this->redirectToRoute('app_login');
                }
            }

            return $this->render('security/new_pass.html.twig', [
                'newPassForm' => $form->createView(),
                'user' => $user,
            ]);
        }

        $this->addFlash('danger', 'Le token est invalide ou a expiré !');
        return $this->redirectToRoute('app_login');
    }
}
