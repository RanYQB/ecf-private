<?php

namespace App\Repository;

use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partner>
 *
 * @method Partner|null find($id, $lockMode = null, $lockVersion = null)
 * @method Partner|null findOneBy(array $criteria, array $orderBy = null)
 * @method Partner[]    findAll()
 * @method Partner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }

    public function add(Partner $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Partner $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function searchWithoutFilters(string $words){
        $query = $this->createQueryBuilder('p');

        $query = $this->createQueryBuilder('p');
        if($words != null){
            $query->where('p.name LIKE :name')
                ->orderBy('p.name')
                // $words.'%' nous permet de saisir quelques lettres seulement et d'obtenir des résultats
                // substitut à la méthode SQL "STARTSWITH" en DQL
                ->setParameter('name', $words.'%');
        }

        return $query->getQuery()->getResult();

    }


    // Création de la fonction de recherche
    public function search(string $words, $filter )
    {
        $query = $this->createQueryBuilder('p');

        if($words != null){
            // $query->where('p.name LIKE :name')
                // $words.'%' nous permet de saisir quelques lettres seulement et d'obtenir des résultats
                // substitut à la méthode SQL "STARTSWITH" en DQL
            //    ->setParameter('name', $words.'%');

            if ($filter == 1) {
                // Utilisation du 0 et du 1 en remplacement des valeurs booléennes pour
                // éviter les erreurs dûes aux égalités strictes
                $query->select('p', 'u')
                    ->innerJoin('p.user', 'u')
                    ->where('u.is_active = 1')
                    ->andWhere('p.name LIKE :name')
                    ->orderBy('p.name')
                    // $words.'%' nous permet de saisir quelques lettres seulement et d'obtenir des résultats
                    // substitut à la méthode SQL "STARTSWITH" en DQL
                    ->setParameter('name', $words.'%');

            } elseif ($filter == 0) {
                $query->select('p', 'u')
                    ->innerJoin('p.user', 'u')
                    ->where('u.is_active = 0')
                    ->andWhere('p.name LIKE :name')
                    ->orderBy('p.name')
                    // $words.'%' nous permet de saisir quelques lettres seulement et d'obtenir des résultats
                    // substitut à la méthode SQL "STARTSWITH" en DQL
                    ->setParameter('name', $words.'%');
            } elseif ($filter == "none") {
                $query->select('p', 'u')
                    ->innerJoin('p.user', 'u')
                    ->where('p.name LIKE :name')
                    ->orderBy('p.name')
                    // $words.'%' nous permet de saisir quelques lettres seulement et d'obtenir des résultats
                    // substitut à la méthode SQL "STARTSWITH" en DQL
                    ->setParameter('name', $words.'%');
            }
        }

        return $query->getQuery()->getResult();
    }

    // Création de la fonction de filtrage
    public function filter($filter)
    {
        // On joint les tables User et Partner afin de récupérer les statuts activés
        // et désactivés des utilisateurs et les intégrer à notre vue "show_partners"
        $query = $this->createQueryBuilder('p');
        if ($filter == 1) {
            // Utilisation du 0 et du 1 en remplacement des valeurs booléennes pour
            // éviter les erreurs dûes aux égalités strictes
            $query->select('p', 'u')
                ->innerJoin('p.user', 'u')
                ->where('u.is_active = 1')
                ->orderBy('p.name');

        } elseif ($filter == 0) {
            $query->select('p', 'u')
                ->innerJoin('p.user', 'u')
                ->where('u.is_active = 0')
                ->orderBy('p.name');

        } elseif ($filter == "none") {
            $query->select('p')
                ->orderBy('p.name');
        }
        return $query->getQuery()->getResult();
    }

//    /**
//     * @return Partner[] Returns an array of Partner objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Partner
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
