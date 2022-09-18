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

    public function showVerified(){
        $query = $this->createQueryBuilder('p');

        $query->select('p', 'u')
            ->innerJoin('p.user', 'u')
            ->where('u.isVerified = 1')
            ->orderBy('p.name');

        return $query->getQuery()->getResult();
    }



    public function searchWithoutFilters(string $words){

        $query = $this->createQueryBuilder('p');
        if($words != null){
            $query->select('p', 'u')
                ->innerJoin('p.user', 'u')
                ->where('u.isVerified = 1')
                ->andWhere('p.name LIKE :name')
                ->orderBy('p.name')
                // $words.'%' nous permet de saisir quelques lettres seulement
                ->setParameter('name', $words.'%');
        }

        return $query->getQuery()->getResult();

    }


    // Création de la fonction de recherche
    public function search(string $words, $filter )
    {
        $query = $this->createQueryBuilder('p');

        if($words != null){

            if ($filter == 1) {

                $query->select('p', 'u')
                    ->innerJoin('p.user', 'u')
                    ->where('u.is_active = 1')
                    ->andWhere('u.isVerified = 1')
                    ->andWhere('p.name LIKE :name')
                    ->orderBy('p.name')
                    ->setParameter('name', $words.'%');

            } elseif ($filter == 0) {
                $query->select('p', 'u')
                    ->innerJoin('p.user', 'u')
                    ->where('u.is_active = 0')
                    ->andWhere('u.isVerified = 1')
                    ->andWhere('p.name LIKE :name')
                    ->orderBy('p.name')
                    ->setParameter('name', $words.'%');
            } elseif ($filter == "none") {
                $query->select('p', 'u')
                    ->innerJoin('p.user', 'u')
                    ->where('u.isVerified = 1')
                    ->andWhere('p.name LIKE :name')
                    ->orderBy('p.name')
                    ->setParameter('name', $words.'%');
            }
        }

        return $query->getQuery()->getResult();
    }

    // Création de la fonction de filtrage
    public function filter($filter)
    {

        $query = $this->createQueryBuilder('p');
        if ($filter == 1) {
            $query->select('p', 'u')
                ->innerJoin('p.user', 'u')
                ->where('u.is_active = 1')
                ->andWhere('u.isVerified = 1')
                ->orderBy('p.name');

        } elseif ($filter == 0) {
            $query->select('p', 'u')
                ->innerJoin('p.user', 'u')
                ->where('u.is_active = 0')
                ->andWhere('u.isVerified = 1')
                ->orderBy('p.name');

        } elseif ($filter == "none") {
            $query->select('p', 'u')
                ->innerJoin('p.user', 'u')
                ->where('u.isVerified = 1')
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
