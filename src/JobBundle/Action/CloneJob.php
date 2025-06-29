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

namespace SolidInvoice\JobBundle\Action;

use Generator;
use SolidInvoice\CoreBundle\Response\FlashResponse;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Repository\JobRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

final class CloneJob
{
    public function __construct(
        private readonly JobRepository $repository,
        private readonly RouterInterface $router
    ) {
    }

    public function __invoke(Request $request, Job $job)
    {
        $newJob = new Job();
        $newJob->setClient($job->getClient());
        $newJob->setQuote($job->getQuote());
        $newJob->setInvoice($job->getInvoice());
        $newJob->setStatus(Job::STATUS_PENDING);
        $newJob->setDescription($job->getDescription());
        $newJob->setScheduledDate($job->getScheduledDate());

        $this->repository->save($newJob, true);

        $route = $this->router->generate('_jobs_view', ['id' => $newJob->getId()]);

        return new class($route) extends RedirectResponse implements FlashResponse {
            public function getFlash(): Generator
            {
                yield self::FLASH_SUCCESS => 'job.clone.success';
            }
        };
    }
}