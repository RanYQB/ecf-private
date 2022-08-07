<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Permissions;
use App\Entity\Structure;
use App\Entity\User;
use App\Form\ActivatePartnerType;
use App\Form\PartnerType;
use App\Form\PermissionsType;
use App\Form\RegistrationFormType;
use App\Form\StructurePermissionsType;
use App\Form\StructureType;
use App\Repository\PartnerRepository;
use App\Repository\PermissionsRepository;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
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
            $user->setIsActive(true);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('user')->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);

            $partner->setUser($user);
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
    public function createStructure(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
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
            $user->setIsActive(true);
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
            $email = (new TemplatedEmail())
                ->from(new Address('manager@manager-fitnessclub.com', 'Manager Fitness Club'))
                ->to($structure->getPartner()->getUser()->getEmail())
                ->subject('Nouvelle structure ajoutée à votre compte')
                ->htmlTemplate('partner/new_structure_email.html.twig')
                ->context([
                    'structure'=> $structure,
                    'address' => $structure->getAddress(),
                    'zipcode' => $structure->getZipcode(),
                    'city' => $structure->getCity(),
                ]);
            $mailer->send($email);
            $this->addFlash('message', 'Votre e-mail a été envoyé.');


            return $this->redirectToRoute('app_admin_create_structure');

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
    public function showPartner(EntityManagerInterface $entityManager, PartnerRepository $partnerRepository, string $slug, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository, Request $request, MailerInterface $mailer): Response
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

            $email = (new TemplatedEmail())
                ->from(new Address('manager@manager-fitnessclub.com', 'Manager Fitness Club'))
                ->to($partner->getUser()->getEmail())
                ->subject('Modifications de vos permissions')
                ->htmlTemplate('partner/new_permissions_email.html.twig')
                ->context([
                    'partner'=> $partner,
                    'name' => $partner->getName(),
                ]);
            $mailer->send($email);
            $this->addFlash('message', 'Votre e-mail a été envoyé.');
        }


        return $this->render('admin/admin_show_partner.html.twig', [
            'partnerPermissionsForm' => $form->createView(),
            'partner' => $partner,
            'structures' => $structures,
        ]);
    }

    #[Route('/structure/{slug}', name: '_show_structure')]
    public function showStructure(EntityManagerInterface $entityManager, string $slug, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository, Request $request, MailerInterface $mailer): Response
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

            $email = (new TemplatedEmail())
                ->from(new Address('manager@manager-fitnessclub.com', 'Manager Fitness Club'))
                ->to($structure->getUser()->getEmail())
                ->subject('Modifications de vos permissions')
                ->htmlTemplate('structure/new_permissions_email.html.twig')
                ->context([
                    'structure'=> $structure,
                    'address' => $structure->getAddress(),
                    'zipcode' => $structure->getZipcode(),
                    'city' => $structure->getCity(),
                ]);
            $mailer->send($email);

            $emailPartner = (new TemplatedEmail())
                ->from(new Address('manager@manager-fitnessclub.com', 'Manager Fitness Club'))
                ->to($structure->getPartner()->getUser()->getEmail())
                ->subject('Modifications des permissions d\'une structure')
                ->htmlTemplate('partner/new_structure_permissions_email.html.twig')
                ->context([
                    'partner'=> $structure->getPartner(),
                    'name' => $structure->getPartner()->getName(),
                ]);
            $mailer->send($emailPartner);
            $this->addFlash('message', 'Vos e-mails ont bien été envoyés.');

        }

        return $this->render('admin/admin_show_structure.html.twig', [
            'structurePermissionsForm' => $form->createView(),
            'structure' => $structure,
        ]);
    }

    #[Route('/desactiver/{id}', name: '_enable_user')]
    public function setStatus(EntityManagerInterface $entityManager, UserRepository $userRepository, PartnerRepository $partnerRepository , int $id, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository, Request $request): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);

        if($user->isIsActive() == true){
            $user->setIsActive(false);
        } elseif ($user->isIsActive() == false){
            $user->setIsActive(true);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $route = $request->headers->get('referer');

        return $this->redirect($route);
    }

}
