<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StructureController extends AbstractController
{
    #[Route('/structure', name: 'app_structure')]
    public function index(): Response
    {
        return $this->render('structure/structure.html.twig', [
            'controller_name' => 'StructureController',
        ]);
    }
}
