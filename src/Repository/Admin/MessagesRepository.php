<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Messages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Messages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Messages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Messages[]    findAll()
 * @method Messages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Messages::class);
    }

    public function getUserMessagesCount(): int
    {
        $em=$this->getEntityManager();
        $query=$em->createQuery("
        SELECT count(m.id) as shopcount
        FROM App\Entity\Admin\Messages m
        WHERE m.status= 'Yeni'
        ");
        $result= $query->getResult();

        if($result[0]["shopcount"]!=null){
            return $result[0]["shopcount"];
        }
        else{
            return 0;
        }
    }

//    /**
//     * @return Messages[] Returns an array of Messages objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Messages
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
