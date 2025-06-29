<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\JobBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\QuoteBundle\Entity\Quote;

/**
 * @extends ServiceEntityRepository<Job>
 * 
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    /**
     * Find a job by quote.
     */
    public function findByQuote(Quote $quote): ?Job
    {
        return $this->findOneBy(['quote' => $quote]);
    }

    /**
     * Find all jobs for a specific client.
     *
     * @return Job[]
     */
    public function findByClient(Client $client): array
    {
        return $this->findBy(['client' => $client], ['created' => 'DESC']);
    }

    /**
     * Find all jobs by status.
     *
     * @return Job[]
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status], ['created' => 'DESC']);
    }

    /**
     * Find all pending jobs.
     *
     * @return Job[]
     */
    public function findPendingJobs(): array
    {
        return $this->findByStatus(Job::STATUS_PENDING);
    }

    /**
     * Find all in-progress jobs.
     *
     * @return Job[]
     */
    public function findInProgressJobs(): array
    {
        return $this->findByStatus(Job::STATUS_IN_PROGRESS);
    }

    /**
     * Save a job entity.
     */
    public function save(Job $job, bool $flush = false): void
    {
        $this->getEntityManager()->persist($job);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a job entity.
     */
    public function remove(Job $job, bool $flush = false): void
    {
        $this->getEntityManager()->remove($job);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}