<?php

namespace App\Repository;

use App\Entity\Structure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Structure>
 *
 * @method Structure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Structure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Structure[]    findAll()
 * @method Structure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StructureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Structure::class);
    }

    public function add(Structure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Structure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function showVerified(){
        $query = $this->createQueryBuilder('s');

        $query->select('s', 'u')
            ->innerJoin('s.user', 'u')
            ->where('u.isVerified = 1')
            ->orderBy('s.address');

        return $query->getQuery()->getResult();
    }


    public function searchWithoutFilters(string $words){


        $query = $this->createQueryBuilder('s');
        if($words != null){
            $query->select('s', 'u', 'p')
                ->innerJoin('s.user', 'u')
                ->innerJoin('s.partner', 'p')
                ->where('u.isVerified = 1')
                ->andWhere('s.address LIKE :address OR p.name LIKE :name')
                ->orderBy('s.address')
                ->setParameter('address', '%'.$words.'%')
                ->setParameter('name', $words.'%');
        }

        return $query->getQuery()->getResult();

    }


    // Création de la fonction de recherche
    public function search(string $words, $filter )
    {
        $query = $this->createQueryBuilder('s');

        if($words != null){

            if ($filter == 1) {
                $query->select('s', 'u', 'p' )
                    ->innerJoin('s.user', 'u')
                    ->innerJoin('s.partner', 'p')
                    ->where('u.is_active = 1')
                    ->andWhere('u.isVerified = 1')
                    ->andWhere('s.address LIKE :address OR p.name LIKE :name')
                    ->orderBy('s.address')
                    ->setParameter('address', '%'.$words.'%')
                    ->setParameter('name', $words.'%');

            } elseif ($filter == 0) {
                $query->select('s', 'u', 'p')
                    ->innerJoin('s.user', 'u')
                    ->innerJoin('s.partner', 'p')
                    ->where('u.is_active = 0')
                    ->andWhere('u.isVerified = 1')
                    ->andWhere('s.address LIKE :address OR p.name LIKE :name')
                    ->orderBy('s.address')
                    ->setParameter('address', '%'.$words.'%')
                    ->setParameter('name', $words.'%');
            } elseif ($filter == "none") {
                $query->select('s', 'u', 'p')
                    ->innerJoin('s.user', 'u')
                    ->innerJoin('s.partner', 'p')
                    ->where('u.isVerified = 1')
                    ->andWhere('s.address LIKE :address OR p.name LIKE :name')
                    ->orderBy('s.address')
                    ->setParameter('address', '%'.$words.'%')
                    ->setParameter('name', $words.'%');
            }
        }

        return $query->getQuery()->getResult();
    }

    // Création de la fonction de filtrage
    public function filter($filter)
    {
        $query = $this->createQueryBuilder('s');
        if ($filter == 1) {
            $query->select('s', 'u')
                ->innerJoin('s.user', 'u')
                ->where('u.is_active = 1')
                ->andWhere('u.isVerified = 1')
                ->orderBy('s.address');

        } elseif ($filter == 0) {
            $query->select('s', 'u')
                ->innerJoin('s.user', 'u')
                ->where('u.is_active = 0')
                ->andWhere('u.isVerified = 1')
                ->orderBy('s.address');

        } elseif ($filter == "none") {
            $query->select('s', 'u')
                ->innerJoin('s.user', 'u')
                ->where('u.isVerified = 1')
                ->orderBy('s.address');
        }
        return $query->getQuery()->getResult();
    }




//    /**
//     * @return Structure[] Returns an array of Structure objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Structure
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
