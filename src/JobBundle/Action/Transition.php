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

final class Transition
{
    public function __construct(
        private readonly JobRepository $repository,
        private readonly RouterInterface $router
    ) {
    }

    public function __invoke(Request $request, string $action, Job $job): RedirectResponse
    {
        // Simple status transitions for now - will add workflow later
        switch ($action) {
            case 'start':
                if ($job->isPending()) {
                    $job->setStatus(Job::STATUS_IN_PROGRESS);
                }
                break;
            case 'complete':
                if ($job->isInProgress()) {
                    $job->setStatus(Job::STATUS_DONE);
                }
                break;
            case 'reopen':
                if ($job->isDone()) {
                    $job->setStatus(Job::STATUS_IN_PROGRESS);
                }
                break;
        }

        $this->repository->save($job, true);

        $route = $this->router->generate('_jobs_view', ['id' => $job->getId()]);

        return new class($action, $route) extends RedirectResponse implements FlashResponse {
            public function __construct(
                private readonly string $action,
                string $route
            ) {
                parent::__construct($route);
            }

            public function getFlash(): Generator
            {
                yield self::FLASH_SUCCESS => 'job.transition.action.' . $this->action;
            }
        };
    }
}