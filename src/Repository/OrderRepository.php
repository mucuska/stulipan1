<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DateRange;
use App\Entity\OrderItem;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;

class OrderRepository extends EntityRepository
{
    /**
     * Return all Orders in the last X period of time. Only REAL orders --> status shortcode == 'created'
     * @return array
     * @return Query
     *@throws Exception
     */
    public function findAllLast($period)
    {
        $date = new DateTime();
        if ($period != '24 hours' && $period != '7 days' && $period != '30 days') {
            return null;
        }

        if ($period == '24 hours') {
            $date->modify('-24 hours');
        }
        if ($period == '7 days') {
            $date->modify('-7 days');
        }
        if ($period == '30 days') {
            $date->modify('-30 days');
        }

        $status = OrderStatus::STATUS_CREATED;
        $status = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $status]);

        $qb = $this
            ->createQueryBuilder('o')
            ->where('o.createdAt > :date')
            ->andWhere('o.status = :status')
            ->setParameter('date', $date)
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            //->execute() // vagy ezzel is mukodik
        ;
        return $qb;
    }

    /**
     * Counts all Orders in the last X period of time. Only REAL orders --> status IS NOT NULL
     * @param string $period            Eg: '24 hours', '7 days'  Value must be a date/time string
     * @param array $filter             It is used to filter orders.
     *                                  If no filter is set, it will count all orders.
     *             [
     *                'paymentStatus' => 'pending'
     *                'orderStatus' => 'created'
     *             ]
     * @return array|null
     * @throws Exception
     */
    public function countAllLast($period = null, $filter = [])
    {
        if ($period && $period != '24 hours' && $period != '7 days' && $period != '30 days' && $period !== 'lifetime') {
            return null;
        }

        $qb = $this
            ->createQueryBuilder('o')
            ->select('COUNT(o.id) as count')   // COUNT
            ->andWhere('o.status IS NOT NULL')
            ->orderBy('o.createdAt', 'DESC')
        ;

        if ($period === null || $period === 'lifetime') {

        } else {
            $date = new DateTime();
            if ($period == '24 hours') {
                $date->modify('-24 hours');
            }
            if ($period == '7 days') {
                $date->modify('-7 days');
            }
            if ($period == '30 days') {
                $date->modify('-30 days');
//                $qb->where('o.createdAt > :date')
//                    ->setParameter('date', $date)
//                ;
//                dd($qb->getQuery()->getResult());
            }
            $qb->andWhere('o.createdAt > :date')
                ->setParameter('date', $date)
            ;
        }

//        $qb = $this
//            ->createQueryBuilder('o')
//            ->select('COUNT(o.id) as count')   // COUNT
//            ->where('o.createdAt > :date')
//            ->andWhere('o.status IS NOT NULL')
//            ->setParameter('date', $date)
//            ->orderBy('o.createdAt', 'DESC')
//        ;

        if (is_array($filter)) {
            if (array_key_exists('paymentStatus', $filter)) {
                $paymentStatus = $filter['paymentStatus'];
                $paymentStatus = $this->getEntityManager()->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);

                $qb->andWhere('o.paymentStatus = :status')
                    ->setParameter('status', $paymentStatus)
                ;
            }
            if (array_key_exists('orderStatus', $filter)) {
                $orderStatus = $filter['orderStatus'];
                $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);

                $qb->andWhere('o.status = :status')
                    ->setParameter('status', $orderStatus)
                ;
            }
        }
        $query = $qb->getQuery()->getOneOrNullResult();
        return $query;  // returns this array: $query = ['orderCount' => 2]
    }

    /**
     * Used with PagerFanta in OrderController.
     *
     * Fetches orders
     *  - using some filtering criteria
     *  - in the last X period of time. Only REAL orders --> status IS NOT NULL
     * @param array $filters             It is used to filter orders.
     *                                  If no filter is set, it will count all orders.
     *             [
     *                  'dateRange' => '2018-07-25 - 2019-11-23'
     *                  'searchTerm => 'valami'
     *                  'paymentStatus' => 'pending'
     *                  'orderStatus' => 'created'
     *             ]
     * @return Query
     * @throws Exception
     */
    public function findAllQuery($filters = [])
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.status IS NOT NULL')
            ->orderBy('o.createdAt', 'DESC')
        ;

        if (is_array($filters)) {
            if (array_key_exists('dateRange', $filters) && $filters['dateRange']) {
                $splitPieces = explode(" - ", $filters['dateRange']);
                $start = $splitPieces[0];
                $end = $splitPieces[1];

                $dateRange = new DateRange();
                if (!isset($start) or $start === null or $start == "") {
                } else {
                    $dateRange->setStart(DateTime::createFromFormat('!Y-m-d',$start));
                    $start = $dateRange->getStart();
                }
                if (!isset($end) or $end === null or $end == "") {
                } else {
                    $dateRange->setEnd(DateTime::createFromFormat('!Y-m-d',$end));
                    $end = $dateRange->getEnd();
                }

                $end->modify('24 hours'); // Ez nelkül az $end mindig az adott nap 00:00:00 óráját veszi, ergó az aznapi rendelések kimaradnak
                $qb->andWhere('o.createdAt >= :start')
                    ->andWhere('o.createdAt <= :end')
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                ;
            }
            if (array_key_exists('searchTerm', $filters) && $filters['searchTerm']) {
                $searchTerm = strtolower($filters['searchTerm']);
//                $qb->andWhere('o.id LIKE :id OR
//                                o.number LIKE :number OR
//                                LOWER(o.email) LIKE :email OR
//                                LOWER(o.firstname) LIKE :firstname OR
//                                LOWER(o.lastname) LIKE :lastname OR
//                                LOWER(CONCAT(o.firstname,\' \',o.lastname)) LIKE :fullname1 OR
//                                LOWER(CONCAT(o.lastname,\' \',o.firstname)) LIKE :fullname2 OR
//                                o.billingPhone LIKE :billingPhone OR
//                                o.shippingPhone LIKE :shippingPhone OR
//                                LOWER(o.shippingName) LIKE :shippingName OR
//                                LOWER(o.billingName) LIKE :billingName
//                                ')
//                    ->setParameter('id', '%'.$searchTerm.'%')
//                    ->setParameter('number', '%'.$searchTerm.'%')
//                    ->setParameter('email', '%'.$searchTerm.'%')
//                    ->setParameter('firstname', '%'.$searchTerm.'%')
//                    ->setParameter('lastname', '%'.$searchTerm.'%')
//                    ->setParameter('fullname1', '%'.$searchTerm.'%')
//                    ->setParameter('fullname2', '%'.$searchTerm.'%')
//                    ->setParameter('billingPhone', '%'.$searchTerm.'%')
//                    ->setParameter('shippingPhone', '%'.$searchTerm.'%')
//                    ->setParameter('shippingName', '%'.$searchTerm.'%')
//                    ->setParameter('billingName', '%'.$searchTerm.'%')
//                ;

                $comparisonsX = [
                    $qb->expr()->like('o.id',':id'),
                    $qb->expr()->like('o.number', ':number'),
                    $qb->expr()->like('LOWER(o.email)', ':email'),
                    $qb->expr()->like('LOWER(o.firstname)',':firstname'),
                    $qb->expr()->like('LOWER(o.lastname)', ':lastname'),
                    $qb->expr()->like('LOWER(CONCAT(o.firstname,\' \',o.lastname))',':fullname1'),
                    $qb->expr()->like('LOWER(CONCAT(o.lastname,\' \',o.firstname))',':fullname2'),
                    $qb->expr()->like('o.billingPhone', ':billingPhone'),
                    $qb->expr()->like('o.shippingPhone', ':shippingPhone'),
                    $qb->expr()->like('LOWER(o.shippingName)', ':shippingName'),
                    $qb->expr()->like('LOWER(o.billingName)', ':billingName'),
                ];
                $paramsX = [
                    'id' => '%'.$searchTerm.'%',
                    'number' => '%'.$searchTerm.'%',
                    'email' => '%'.$searchTerm.'%',
                    'firstname' => '%'.$searchTerm.'%',
                    'lastname' => '%'.$searchTerm.'%',
                    'fullname1' => '%'.$searchTerm.'%',
                    'fullname2' => '%'.$searchTerm.'%',
                    'billingPhone' => '%'.$searchTerm.'%',
                    'shippingPhone' => '%'.$searchTerm.'%',
                    'shippingName' => '%'.$searchTerm.'%',
                    'billingName' => '%'.$searchTerm.'%',
                ];

                // If $searchTerms contains several words (Eg: renata jr fazekas)
                // then we will search id db for every permutation of it.
                $words = explode( ' ', $searchTerm);
                $searchTermPermutations = $this->pc_permute($words);

                foreach ($searchTermPermutations as $key => $item) {
                    array_push($comparisonsX,
                        $qb->expr()->like('LOWER(o.shippingName)', ':shippingName_'.$key),
                        $qb->expr()->like('LOWER(o.billingName)', ':billingName_'.$key)
                    );
                    // Execute $qb->expr()->orX() with arguments from the array $comparisonsX
                    $orX = call_user_func_array([$qb->expr(), 'orX'], $comparisonsX);

                    $paramsX['shippingName_'.$key] = '%'.implode(' ', $item).'%';
                    $paramsX['billingName_'.$key] = '%'.implode(' ', $item).'%';
                }
                $qb->andWhere($orX)->setParameters($paramsX);
            }

            if (array_key_exists('paymentStatus', $filters) && $filters['paymentStatus']) {
                $paymentStatus = $filters['paymentStatus'];
                $paymentStatus = $this->getEntityManager()->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);

                $qb->andWhere('o.paymentStatus = :paymentStatus')
                    ->setParameter('paymentStatus', $paymentStatus)
                ;
            }
            if (array_key_exists('orderStatus', $filters) && $filters['orderStatus']) {
                $orderStatus = $filters['orderStatus'];
                $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);

                $qb->andWhere('o.status = :orderStatus')
                    ->setParameter('orderStatus', $orderStatus)
                ;
            }
        }
        $query = $qb->getQuery();
        return $query;
    }

    /**
     * Return all Orders in the last X period of time. Only REAL orders --> status shortcode == 'created'
     * @return array
     * @return Query
     *@throws Exception
     */
    public function summaryAllLast($period)
    {
        $date = new DateTime();
        if ($period != '24 hours' && $period != '7 days' && $period != '30 days') {
            return null;
        }

        if ($period == '24 hours') {
            $date->modify('-24 hours');
        }
        if ($period == '7 days') {
            $date->modify('-7 days');
        }
        if ($period == '30 days') {
            $date->modify('-30 days');
        }

        $status = OrderStatus::STATUS_CREATED;
        $status = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $status]);

        $qb = $this
            ->createQueryBuilder('o')
//            ->select('COUNT(o.id) as count')
//            ->select('o.id, SUM(oi.priceTotal)+o.deliveryFee as totalAmountToPay')
            ->select('o.id, SUM(oi.priceTotal)+o.deliveryFee as totalAmountToPay, COUNT(oi.id) as itemCount')
//            ->from(OrderItem::class, 'oi')
            ->join(OrderItem::class,'oi','WITH', 'o=oi.order')
            ->groupBy('o.id')
            ->orderBy('o.id')

            ->where('o.createdAt > :date')
            ->andWhere('o.status = :status')
            ->setParameter('date', $date)
            ->setParameter('status', $status)
            ->getDQL()
//            ->getQuery()
//            ->getResult()
        ;
//        dd($qb);

        $date = $date->format('Y-m-d H:i:s');
//        dd($date);
        $sql = "SELECT SUM(t.totalAmountToPay) AS totalAmountToPay
                FROM cart_order_2 o, (
                    SELECT o.id, COUNT(oi.product_id) AS itemCount, SUM(oi.price_total)+o.delivery_fee AS totalAmountToPay
                    FROM cart_order_2 o, cart_order_item oi
                    WHERE o.id = oi.order_id AND o.created_at > '.$date.' AND o.status_id = 1
                    GROUP BY o.id
                    ORDER BY o.id
                    ) t"
            ;
//        AND o.status = :status
        $result = $this->_em->getConnection()->prepare($sql);
        $result->execute();
        return $result->fetchAll();
    }

    /**
     * @return Query
     */
    public function findAllByQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->where('p.status IS NOT NULL')
//            ->setParameter('status', 1)
            ->getQuery()
            ;
    }

    /**
     * Helper function for permutations. Returns an array with all permutations
     *
     * @param $items
     * @param array $perms
     * @return array            # Returns an array with all permutations
     */
    function pc_permute($items, $perms = [])
    {
        if (empty($items)) {
            $return = array($perms);
        } else {
            $return = array();
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $return = array_merge($return, $this->pc_permute($newitems, $newperms));
            }
        }
        return $return;
    }
}