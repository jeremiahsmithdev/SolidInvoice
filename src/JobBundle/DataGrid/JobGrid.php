<?php

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\JobBundle\DataGrid;

use Doctrine\ORM\EntityManagerInterface;
use SolidInvoice\DataGridBundle\Attributes\AsDataGrid;
use SolidInvoice\DataGridBundle\GridBuilder\Batch\BatchAction;
use SolidInvoice\DataGridBundle\GridBuilder\Query;
use SolidInvoice\DataGridBundle\Source\ORMSource;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Repository\JobRepository;
use Symfony\Bridge\Doctrine\Types\UlidType;
use function array_key_exists;

#[AsDataGrid(name: 'job_grid', title: 'Active Jobs')]
final class JobGrid extends BaseJobGrid
{
    public function batchActions(): iterable
    {
        yield from parent::batchActions();

        yield BatchAction::new('Mark as Done')
            ->icon('check')
            ->color('success')
            ->action(static function (JobRepository $repository, array $selectedItems): void {
                foreach ($selectedItems as $jobId) {
                    $job = $repository->find($jobId);
                    if ($job && !$job->isDone()) {
                        $job->setStatus(Job::STATUS_DONE);
                        $repository->save($job);
                    }
                }
                $repository->getEntityManager()->flush();
            });

        yield BatchAction::new('Mark as In Progress')
            ->icon('play')
            ->color('info')
            ->action(static function (JobRepository $repository, array $selectedItems): void {
                foreach ($selectedItems as $jobId) {
                    $job = $repository->find($jobId);
                    if ($job && $job->isPending()) {
                        $job->setStatus(Job::STATUS_IN_PROGRESS);
                        $repository->save($job);
                    }
                }
                $repository->getEntityManager()->flush();
            });
    }

    public function query(EntityManagerInterface $entityManager, Query $query): Query
    {
        $query = parent::query($entityManager, $query);

        if (array_key_exists('client_id', $this->context)) {
            $query
                ->getQueryBuilder()
                ->where(ORMSource::ALIAS . '.client = :client_id')
                ->setParameter('client_id', $this->context['client_id'], UlidType::NAME);
        }

        if (array_key_exists('quote_id', $this->context)) {
            $query
                ->getQueryBuilder()
                ->where(ORMSource::ALIAS . '.quote = :quote_id')
                ->setParameter('quote_id', $this->context['quote_id'], UlidType::NAME);
        }

        return $query;
    }
}