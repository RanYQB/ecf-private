<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Permissions;
use App\Entity\Structure;
use App\Entity\User;
use App\Form\PartnerType;
use App\Form\PermissionsType;
use App\Form\RegistrationFormType;
use App\Form\StructurePermissionsType;
use App\Form\StructureType;
use App\Repository\PartnerRepository;
use App\Repository\PermissionsRepository;
use App\Repository\StructureRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin', name: 'app_admin')]
class AdminController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier, private SluggerInterface $slugger)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/', name: '')]
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/nouveau-partenaire', name: '_create_partner')]
    public function createPartner(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $user = new User();
        $partner = new Partner();
        $permissions = new Permissions();
        $items = ['user' => $user, 'partner' => $partner];

        $form = $this->createFormBuilder($items)
            ->add('user', RegistrationFormType::class)
            ->add('partner', PartnerType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $user->setRoles((array)'ROLE_PARTNER');
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('user')->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);

            $partner->setUser($user);
            $partner->setIsActive(true);
            $partner->setSlug($this->slugger->slug($partner->getName())->lower());
            $entityManager->persist($partner);

            $permissions->setPartner($partner);
            $permissions->setNewsletter(true);
            $permissions->setPlanningManagement(true);
            $permissions->setDrinkSales(true);
            $permissions->setVideoCourses(true);
            $permissions->setProspectReminders(false);
            $permissions->setSponsorship(false);
            $permissions->setFreeWifi(false);
            $permissions->setFlexibleHours(false);

            $entityManager->persist($permissions);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('manager@manager-fitnessclub.com', 'Manager Fitness Club'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_admin');

        }



        return $this->render('admin/admin_new_partner.html.twig', [
            'partnerForm' => $form->createView(),
        ]);

    }


    #[Route('/nouvelle-structure', name: '_create_structure')]
    public function createStructure(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $user = new User();
        $structure = new Structure();
        $permissions = new Permissions();

        $items = ['user' => $user, 'structure' => $structure];

        $form = $this->createFormBuilder($items)
            ->add('user', RegistrationFormType::class)
            ->add('structure', StructureType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $user->setRoles((array)'ROLE_STRUCTURE');
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('user')->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);

            $partner = $form->get('structure')->get('partner')->getData();

            $structure->setUser($user);
            $structure->setPartner($partner);
            $structure->setIsActive(true);
            $structure->setSlug($this->slugger->slug($structure->getAddress())->lower());

            $entityManager->persist($structure);

            $permissions->setStructure($structure);
            $permissions->setNewsletter($partner->getPermissions()->isNewsletter());
            $permissions->setPlanningManagement($partner->getPermissions()->isPlanningManagement());
            $permissions->setDrinkSales($partner->getPermissions()->isDrinkSales());
            $permissions->setVideoCourses($partner->getPermissions()->isVideoCourses());
            $permissions->setProspectReminders($partner->getPermissions()->isProspectReminders());
            $permissions->setSponsorship($partner->getPermissions()->isSponsorship());
            $permissions->setFreeWifi($partner->getPermissions()->isFreeWifi());
            $permissions->setFlexibleHours($partner->getPermissions()->isFlexibleHours());

            $entityManager->persist($permissions);
            $entityManager->flush();


            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('manager@manager-fitnessclub.com', 'Manager Fitness Club'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_admin');

        }

        return $this->render('admin/admin_new_structure.html.twig', [
            'structureForm' => $form->createView(),
        ]);
    }


    #[Route('/partenaires', name: '_show_partners')]
    public function showPartners(PartnerRepository $partnerRepository): Response
    {
        $partners = $partnerRepository->findBy([], ['name' => 'ASC']);

        return $this->render('admin/admin_show_partners.html.twig', [
            'partners' => $partners,

        ]);
    }

    #[Route('/partenaires/{slug}', name: '_show_partner')]
    public function showPartner(EntityManagerInterface $entityManager, PartnerRepository $partnerRepository, string $slug, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository, Request $request): Response
    {
        $partner = $partnerRepository->findOneBy(['slug' => $slug]);
        $structures = $structureRepository->findBy(['partner' => $partner], ['address' => 'ASC']);
        $partnerPermissions = $permissionsRepository->findOneBy(['partner' => $partner]);

        $form = $this->createFormBuilder(['permissions' => $partnerPermissions])
            ->add('permissions', PermissionsType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partnerPermissions);
            $entityManager->flush();
        }

        return $this->render('admin/admin_show_partner.html.twig', [
            'partnerPermissionsForm' => $form->createView(),
            'partner' => $partner,
            'structures' => $structures,
        ]);
    }

    #[Route('/structure/{slug}', name: '_show_structure')]
    public function showStructure(EntityManagerInterface $entityManager, PartnerRepository $partnerRepository, string $slug, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository, Request $request): Response
    {
        $structure = $structureRepository->findOneBy(['slug' => $slug]);
        $structurePermissions = $permissionsRepository->findOneBy(['structure' => $structure]);

        $form = $this->createFormBuilder(['permissions' => $structurePermissions])
            ->add('permissions', PermissionsType::class)
            ->getForm();

        // $form = $this->createForm(PermissionsType::class, $partnerPermissions);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($structurePermissions);
            $entityManager->flush();
        }


        return $this->render('admin/admin_show_structure.html.twig', [
            'structurePermissionsForm' => $form->createView(),
            'structure' => $structure,
        ]);
    }
}
