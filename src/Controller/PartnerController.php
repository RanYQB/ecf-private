<?php

namespace App\Controller;

use App\Repository\PartnerRepository;
use App\Repository\PermissionsRepository;
use App\Repository\StructureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartnerController extends AbstractController
{
    #[Route('/partner', name: 'app_partner')]
    public function index(PartnerRepository $partnerRepository, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository): Response
    {
        // Affichage des informations du partenaire
        $user = $this->getUser();
        $partner = $partnerRepository->findOneBy(['user' => $user]);
        $permissions = $permissionsRepository->findOneBy(['partner' => $partner]);
        $structures = $structureRepository->findBy(['partner' => $partner]);

        return $this->render('partner/partner.html.twig', [
            'partner' => $partner,
            'permissions' => $permissions,
            'structures' => $structures,
        ]);
    }
}
