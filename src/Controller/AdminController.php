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

        // Conditionner l'exécution de la fonction à l'attribution du rôle Administrateur à l'utilisateur connecté
        if($this->isGranted('ROLE_ADMIN')){

            // Initialisation d'un nouvel utilisateur et d'un nouveau partenaire
            $user = new User();
            $partner = new Partner();
            $permissions = new Permissions();
            $items = ['user' => $user, 'partner' => $partner];

            // Création d'un formulaire rattaché aux deux entités précédemment initialisées
            $form = $this->createFormBuilder($items)
                ->add('user', RegistrationFormType::class)
                ->add('partner', PartnerType::class)
                ->getForm();

            $form->handleRequest($request);

            // Actions effectuées à la soumission du formulaire
            if($form->isSubmitted() && $form->isValid()) {

                $user->setRoles((array)'ROLE_PARTNER');
                $user->setIsActive(false);
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

                // Enregistrement en base de données du partenaire, de son compte utilisateur et de ses permissions.
                // L'action est effectuée en dernier afin d'éviter un enregistrement partiel ou incomplet en cas d'erreur.
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

                return $this->redirectToRoute('app_admin');
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

            //De nouveau, un formulaire rattaché à deux entités : User + Structure
            $form = $this->createFormBuilder($items)
                ->add('user', RegistrationFormType::class)
                ->add('structure', StructureType::class)
                ->getForm();

            $form->handleRequest($request);

            // Soumission du formulaire
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setRoles((array)'ROLE_STRUCTURE');
                $user->setIsActive(false);
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('user')->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($user);

                // Récupération dans une variable du partenaire sélectionné via le formulaire d'ajout de la structure "StructureType"
                $partner = $form->get('structure')->get('partner')->getData();

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


                // Envoi d'un mail à la structure pour confirmer son compte
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
                $this->addFlash('message', 'Votre e-mail a été envoyé.');


                return $this->redirectToRoute('app_admin');

            }
        }
        return $this->render('admin/admin_new_structure.html.twig', [
            'structureForm' => $form->createView(),
        ]);
    }


    #[Route('/partenaires', name: '_show_partners')]
    public function showPartners(PartnerRepository $partnerRepository, UserRepository $userRepository, Request $request): Response
    {
        // Récupération de la liste de tous les partenaires avec la fonction findBy afin de les classer par ordre
        // alphabétique.
        $user = $userRepository->findBy(['isVerified' => true]);

        $partners = $partnerRepository->findBy(['user' => $user], ['name' => 'ASC']);

        // Création de la barre de recherche
        //$form = $this->createForm(SearchPartnerType::class);

        // $search = $form->handleRequest($request);
        $search = $request->get("search");

        $filter = $request->get("filtre");

        if($filter != "" && $filter != null ){
            // Utilisation de la fonction Filter que l'on a créée dans le PartnerRepository
            $partners = $partnerRepository->filter($filter);
        }

        if($search != "" || $search != null){
            // Utilisation de la fonction Filter que l'on a créée dans le PartnerRepository
                // Utilisation de la fonction Filter que l'on a créée dans le PartnerRepository
                if(isset($_GET['filtre'])){
                    $partners = $partnerRepository->search($search, $filter);
                } else {
                    $partners = $partnerRepository->searchWithoutFilters($search);
                }


        }



        //if($form->isSubmitted() && $form->isValid()){
            // Utilisation de la fonction Search que l'on a créée dans le PartnerRepository
          //  $partners = $partnerRepository->search($search->get('word')->getData());
        //}


        // On vérifie si l'URL possède un paramètre "Ajax" pour retourner les résultats du filtrage de la page
        if($request->get('ajax')){
            return new JsonResponse([
                'content' => $this->renderView('Partials/_content.html.twig', [
                    'partners' => $partners,
                    //'searchForm' => $form->createView(),

                ])
            ]);
        }

        return $this->render('admin/admin_show_partners.html.twig', [
            'partners' => $partners,
            //'searchForm' => $form->createView(),

        ]);
    }

    #[Route('/partenaires/{slug}', name: '_show_partner')]
    public function showPartner(EntityManagerInterface $entityManager,UserRepository $userRepository, PartnerRepository $partnerRepository, string $slug, StructureRepository $structureRepository, PermissionsRepository $permissionsRepository, Request $request, MailerInterface $mailer): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            // Passage du slug du partenaire par l'intermédiaire de l'URL.
            // Le slug est en suite passé en paramètre de la fonction.
            // Une fois récupéré, nous pouvons effectuer une recherche du partenaire souhaité dans le répertoire
            // de l'entité Partner grâce à ce slug avec la méthode findOneBy.
            $partner = $partnerRepository->findOneBy(['slug' => $slug]);

            $user = $userRepository->findBy(['isVerified' => true]);
            // Récupération de l'intégralité des structures rattachées au partenaire
            $structures = $structureRepository->findBy(array('partner' => $partner, 'user' => $user), ['address' => 'ASC']);
            $partnerPermissions = $permissionsRepository->findOneBy(['partner' => $partner]);

            // Création d'un formulaire permettant la modification des permissions du partenaire dans la base de données
            $form = $this->createFormBuilder(['permissions' => $partnerPermissions])
                ->add('permissions', PermissionsType::class)
                ->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($partnerPermissions);
                $entityManager->flush();

                // Notification au partenaire de la modification de ses permissions si le formulaire est soumis
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
                $this->addFlash('message', 'Votre e-mail a été envoyé.');

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
            // Utilisation de la même méthode pour récupérer une structure et l'afficher sur une page grâce au slug
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
                $this->addFlash('message', 'Vos e-mails ont bien été envoyés.');
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

        }
        // Définition de la route dans une variable qui récupère l'URL de provenance de la requête
        // étant donné que plusieurs pages sont concernées par cette action
        $route = $request->headers->get('referer');

        return $this->redirect($route);
    }


    #[Route('/desactiver/{id}', name: '_enable_user')]
    public function setStatus(EntityManagerInterface $entityManager, UserRepository $userRepository, int $id,MailerInterface $mailer, Request $request): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            // Pour désactiver et activer un compte, nous récupérons l'id de l'utilisateur
            // afin que son status puisse être détecté au moment de la connexion
            $user = $userRepository->findOneBy(['id' => $id]);

            if($user->isIsActive() == true){
                $user->setIsActive(false);
                $subject = 'Désactivation de votre compte utilisateur Fitness Club';
                $template = 'user/disabled_email.html.twig';
            } elseif ($user->isIsActive() == false){
                $user->setIsActive(true);
                $subject = 'Activation de votre compte utilisateur Fitness Club';
                $template = 'user/enabled_email.html.twig';
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

        }
        // Définition de la route dans une variable qui récupère l'URL de provenance de la requête
        // étant donné que plusieurs pages sont concernées par cette action
        $route = $request->headers->get('referer');

        return $this->redirect($route);
    }

}
