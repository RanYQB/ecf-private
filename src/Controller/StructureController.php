<?php

namespace App\Controller;


use App\Repository\PermissionsRepository;
use App\Repository\StructureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StructureController extends AbstractController
{
    #[Route('/structure', name: 'app_structure')]
    public function index( StructureRepository $structureRepository, PermissionsRepository $permissionsRepository): Response
    {

        // Affichage des informations de la structure
        $user = $this->getUser();
        if(!$user->isVerified()){

            return $this->redirectToRoute('app_logout');

        }
        $structure = $structureRepository->findOneBy(['user'=> $user]);
        $permissions = $permissionsRepository->findOneBy(['structure' => $structure]);

        return $this->render('structure/structure.html.twig', [
            'structure' => $structure,
            'permissions' => $permissions,
        ]);
    }
}
