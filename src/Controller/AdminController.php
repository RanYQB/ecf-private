<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Permissions;
use App\Entity\Structure;
use App\Entity\User;
use App\Form\PartnerType;
use App\Form\PermissionsType;
use App\Form\RegistrationFormType;
use App\Form\StructureType;
use App\Repository\PartnerRepository;
use App\Repository\PermissionsRepository;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function index(UserRepository $userRepository, PartnerRepository $partnerRepository, StructureRepository $structureRepository): Response
    {
        $partners = $partnerRepository->findAll();
        $structures = $structureRepository->findAll();
        $users = $userRepository->findAll();
        $usersVerified = $userRepository->findBy(['isVerified' => true]);
        $usersUnverified = $userRepository->findBy(['isVerified' => false]);
        $usersEnabled = $userRepository->findBy(['is_active' => true]);
        $usersDisabled = $userRepository->findBy(['is_active' => false]);
        $partnersVerified = $partnerRepository->findBy(['user' => $usersVerified]);
        $partnersUnverified = $partnerRepository->findBy(['user' => $usersUnverified]);
        $partnersEnabled = $partnerRepository->findBy(['user' => $usersEnabled]);
        $partnersDisabled = $partnerRepository->findBy(['user' => $usersDisabled]);
        $structuresVerified = $structureRepository->findBy(['user' => $usersVerified]);
        $structuresUnverified = $structureRepository->findBy(['user' => $usersUnverified]);
        $structuresEnabled = $structureRepository->findBy(['user' => $usersEnabled]);
        $structuresDisabled = $structureRepository->findBy(['user' => $usersDisabled]);
        $current = $this->getUser()->getEmail();

        return $this->render('admin/admin.html.twig', [
            'partners' => $partners,
            'structures' => $structures,
            'users' => $users,
            'verifiedUsers' => $usersVerified,
            'unverifiedUsers' => $usersUnverified,
            'verifiedPartners' => $partnersVerified,
            'unverifiedPartners' => $partnersUnverified,
            'enabledPartners' => $partnersEnabled,
            'disabledPartners' => $partnersDisabled,
            'verifiedStructures' => $structuresVerified,
            'unverifiedStructures' => $structuresUnverified,
            'enabledStructures' => $structuresEnabled,
            'disabledStructures' => $structuresDisabled,
            'currentUser' => $current,

        ]);
    }

    #[Route('/nouveau-partenaire', name: '_create_partner')]
    public function createPartner(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Conditionner l'exécution de la fonction à l'attribution du rôle Administrateur
        if($this->isGranted('ROLE_ADMIN')){

            // Initialisation d'un nouvel utilisateur et d'un nouveau partenaire
            $user = new User();
            $partner = new Partner();
            $permissions = new Permissions();
            $items = ['user' => $user, 'partner' => $partner];

            // Création d'un formulaire
            $form = $this->createFormBuilder($items)
                ->add('user', RegistrationFormType::class)
                ->add('partner', PartnerType::class)
                ->getForm();

            $form->handleRequest($request);

            // Actions effectuées à la soumission du formulaire
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

                //Définition d'un slug pour permettre par la suite un affichage individuel de chaque partenaire
                $partner->setUser($user);
                $partner->setSlug($this->slugger->slug($partner->getName())->lower());
                $entityManager->persist($partner);

                // Ajout des permissions par défaut au partenaire en cours de création
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

                // Envoi d'un mail au partenaire pour confirmer son compte
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                        ->to($user->getEmail())
                        ->subject('Veuillez confirmer votre compte')
                        ->htmlTemplate('registration/confirmation_partner_email.html.twig')
                        ->context([
                            'user' => $user,
                            'partner' => $partner,
                            'password' => $form->get('user')->get('plainPassword')->getData(),
                        ])
                );
                $this->addFlash('success', 'Le partenaire a bien été créé.');
                return $this->redirectToRoute('app_admin_create_partner');
            }
        }

        return $this->render('admin/admin_new_partner.html.twig', [
            'partnerForm' => $form->createView(),
        ]);
    }


    #[Route('/nouvelle-structure', name: '_create_structure')]
    public function createStructure(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if($this->isGranted('ROLE_ADMIN')) {
            // Initialisation d'un nouvel utilisateur et d'une nouvelle structure
            $user = new User();
            $structure = new Structure();
            $permissions = new Permissions();

            $items = ['user' => $user, 'structure' => $structure];

            $form = $this->createFormBuilder($items)
                ->add('user', RegistrationFormType::class)
                ->add('structure', StructureType::class)
                ->getForm();

            $form->handleRequest($request);

            // Soumission du formulaire
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setRoles((array)'ROLE_STRUCTURE');
                $user->setIsActive(true);
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('user')->get('plainPassword')->getData()
                    )
                );
                // Récupération dans une variable du partenaire
                $partner = $form->get('structure')->get('partner')->getData();

                if($partner->getUser()->isIsActive() == 1 ) {

                    $entityManager->persist($user);

                    $structure->setUser($user);
                    $structure->setPartner($partner);
                    $structure->setSlug($this->slugger->slug($structure->getAddress())->lower());

                    $entityManager->persist($structure);

                    // Ajout des permissions à la structure, héritées du partenaire auquel elle est rattachée
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


                    // Envoi d'un mail à la structure
                    $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                        (new TemplatedEmail())
                            ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                            ->to($user->getEmail())
                            ->subject('Veuillez confirmer votre compte')
                            ->htmlTemplate('registration/confirmation_structure_email.html.twig')
                            ->context([
                                'user' => $user,
                                'structure' => $structure,
                                'password' => $form->get('user')->get('plainPassword')->getData(),
                            ])
                    );

                    // Envoi d'un mail au partenaire pour lui notifier la création d'une nouvelle structure
                    $email = (new TemplatedEmail())
                        ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                        ->to($structure->getPartner()->getUser()->getEmail())
                        ->subject('Nouvelle structure ajoutée à votre compte')
                        ->htmlTemplate('partner/new_structure_email.html.twig')
                        ->context([
                            'structure' => $structure,
                            'address' => $structure->getAddress(),
                            'zipcode' => $structure->getZipcode(),
                            'city' => $structure->getCity(),
                        ]);
                    $mailer->send($email);
                    $this->addFlash('success', 'La structure a bien été créée.');

                    return $this->redirectToRoute('app_admin_create_structure');
                }  elseif ($partner->getUser()->isIsActive() == 0){
                    $this->addFlash('danger', 'Le partenaire sélectionné est désactivé, vous ne pouvez pas lui créer une nouvelle structure.');
                }
            } elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash('danger', 'Formulaire invalide, vérifiez votre saisie.');
            }
        }
        return $this->render('admin/admin_new_structure.html.twig', [
            'structureForm' => $form->createView(),
        ]);
    }

    #[Route('/partenaires', name: '_show_partners')]
    public function showPartners(PartnerRepository $partnerRepository, Request $request): Response
    {
        $partners = $partnerRepository->showVerified();
        $search = $request->get("search");
        $filter = $request->get("filtre");

        if($filter != "" && $filter != null ){
            // Utilisation de la fonction Filter
            $partners = $partnerRepository->filter($filter);
        }

        if($search != "" || $search != null){
            // Utilisation des fonctions search et searchWithoutFilters
                if(isset($_GET['filtre'])){
                    $partners = $partnerRepository->search($search, $filter);
                } else {
                    $partners = $partnerRepository->searchWithoutFilters($search);
                }
        }

        // On vérifie si l'URL possède un paramètre "Ajax"
        if($request->get('ajax')){
            return new JsonResponse([
                'content' => $this->renderView('Partials/_content.html.twig', [
                    'partners' => $partners,
                ])
            ]);
        }

        return $this->render('admin/admin_show_partners.html.twig', [
            'partners' => $partners,
        ]);
    }

    #[Route('/structures', name: '_show_structures')]
    public function showStructures(StructureRepository $structureRepository, Request $request): Response
    {
        $structures = $structureRepository->showVerified();
        $search = $request->get("search");
        $filter = $request->get("filtre");

        if($filter != "" && $filter != null ){
            $structures = $structureRepository->filter($filter);
        }

        if($search != "" || $search != null){
            if(isset($_GET['filtre'])){
                $structures = $structureRepository->search($search, $filter);
            } else {
                $structures = $structureRepository->searchWithoutFilters($search);
            }
        }

        // On vérifie si l'URL possède un paramètre "Ajax"
        if($request->get('ajax')){
            return new JsonResponse([
                'content' => $this->renderView('Partials/_structure_content.html.twig', [
                    'structures' => $structures,
                ])
            ]);
        }

        return $this->render('admin/admin_show_structures.html.twig', [
            'structures' => $structures,
        ]);
    }

    #[Route('/partenaires/{slug}', name: '_show_partner')]
    public function showPartner(EntityManagerInterface $entityManager,UserRepository $userRepository, PartnerRepository $partnerRepository, string $slug, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository, Request $request, MailerInterface $mailer): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            $partner = $partnerRepository->findOneBy(['slug' => $slug]);

            $user = $userRepository->findBy(['isVerified' => true]);
            // Récupération de l'intégralité des structures rattachées au partenaire
            $structures = $structureRepository->findBy(array('partner' => $partner, 'user' => $user), ['address' => 'ASC']);
            $partnerPermissions = $permissionsRepository->findOneBy(['partner' => $partner]);

            // Création d'un formulaire permettant la modification des permissions du partenaire
            $form = $this->createFormBuilder(['permissions' => $partnerPermissions])
                ->add('permissions', PermissionsType::class)
                ->getForm();
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($partnerPermissions);
                $entityManager->flush();
                // Notification au partenaire de la modification de ses permissions
                $email = (new TemplatedEmail())
                    ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                    ->to($partner->getUser()->getEmail())
                    ->subject('Vos permissions ont été modifiées')
                    ->htmlTemplate('partner/new_permissions_email.html.twig')
                    ->context([
                        'partner'=> $partner,
                        'name' => $partner->getName(),
                    ]);
                $mailer->send($email);
                $this->addFlash('success', 'Les permissions globales du partenaire ont bien été modifiées.');
            }
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
        if($this->isGranted('ROLE_ADMIN')){
            $structure = $structureRepository->findOneBy(['slug' => $slug]);
            $structurePermissions = $permissionsRepository->findOneBy(['structure' => $structure]);
            $form = $this->createFormBuilder(['permissions' => $structurePermissions])
                ->add('permissions', PermissionsType::class)
                ->getForm();

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($structurePermissions);
                $entityManager->flush();

                // Notification à la structure de la modification de ses permissions
                $email = (new TemplatedEmail())
                    ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
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

                // Notification au partenaire de la modification des permissions d'une de ses structures
                $emailPartner = (new TemplatedEmail())
                    ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                    ->to($structure->getPartner()->getUser()->getEmail())
                    ->subject('Modification des permissions d\'une structure')
                    ->htmlTemplate('partner/new_structure_permissions_email.html.twig')
                    ->context([
                        'structure'=> $structure,
                    ]);
                $mailer->send($emailPartner);
                $this->addFlash('success', 'Les permissions de la structure ont bien été modifiées.');
            }
        }

        return $this->render('admin/admin_show_structure.html.twig', [
            'structurePermissionsForm' => $form->createView(),
            'structure' => $structure,
        ]);
    }

    #[Route('/en-attente', name: '_unverified_user')]
    public function showUnverified( UserRepository $userRepository): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            $users = $userRepository->findBy(['isVerified' => false]);
        }

        return $this->render('admin/admin_show_unverified.html.twig', [
            'users' => $users,
        ]);
    }


    #[Route('/confirmation/{id}', name: '_verify_user')]
    public function sendVerif( UserRepository $userRepository, int $id, Request $request): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            $user = $userRepository->findOneBy(['id' => $id]);

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre compte')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([
                        'user' => $user,
                    ])
            );

            $this->addFlash('success', 'L\'email de confirmation a bien été renvoyé.');

        }

        $route = $request->headers->get('referer');

        return $this->redirect($route);
    }


    #[Route('/desactiver/{id}', name: '_enable_user')]
    public function setStatus(EntityManagerInterface $entityManager, UserRepository $userRepository,StructureRepository $structureRepository, PartnerRepository $partnerRepository, int $id,MailerInterface $mailer, Request $request): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            // Pour désactiver et activer un compte, nous récupérons l'id de l'utilisateur
            $user = $userRepository->findOneBy(['id' => $id]);

            if(in_array('ROLE_PARTNER', $user->getRoles())){
                $partner = $partnerRepository->findOneBy(['user' => $user]);
                $structures = $partner->getStructures();
                if($user->isIsActive() == true){
                    $user->setIsActive(false);
                    // On désactive ou active toutes les structures du partenaire
                    foreach ($structures as $structure){
                        $structureUser = $structure->getUser();
                        $structureUser->setIsActive(false);
                    }
                    $subject = 'Désactivation de votre compte utilisateur Fitness Club';
                    $template = 'user/disabled_email.html.twig';
                    $message = 'Le partenaire a bien été désactivé.';
                } elseif ($user->isIsActive() == false){
                    $user->setIsActive(true);
                    foreach ($structures as $structure){
                        $structureUser = $structure->getUser();
                        $structureUser->setIsActive(true);
                    }
                    $subject = 'Activation de votre compte utilisateur Fitness Club';
                    $template = 'user/enabled_email.html.twig';
                    $message = 'Le partenaire a bien été activé.';
                }
            } elseif (in_array('ROLE_STRUCTURE', $user->getRoles())) {
                if ($user->isIsActive() == true) {
                    $user->setIsActive(false);
                    $subject = 'Désactivation de votre compte utilisateur Fitness Club';
                    $template = 'user/disabled_email.html.twig';
                    $message = 'La structure a bien été désactivée.';
                } elseif ($user->isIsActive() == false) {
                    $structure = $structureRepository->findOneBy(['user' => $user]);
                    $partner = $structure->getPartner();
                    $partnerUser = $partner->getUser();
                    if($partnerUser->isIsActive() == true){
                    $user->setIsActive(true);
                    $subject = 'Activation de votre compte utilisateur Fitness Club';
                    $template = 'user/enabled_email.html.twig';
                    $message = 'La structure a bien été activée.';
                    } elseif ($partnerUser->isIsActive() == false){
                        $this->addFlash('danger', 'Echec de l\'action : Le partenaire de cette structure est désactivé.');
                        $route = $request->headers->get('referer');
                        return $this->redirect($route);
                    }
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('manager.fitnessclub.app@gmail.com', 'Manager Fitness Club'))
                ->to($user->getEmail())
                ->subject($subject)
                ->htmlTemplate($template)
                ->context([
                    'user'=> $user,
                ]);
            $mailer->send($email);
            $this->addFlash('success', $message);
        }

        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }

}
