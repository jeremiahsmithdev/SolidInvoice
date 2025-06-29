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

use SolidInvoice\CoreBundle\Templating\Template;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Repository\JobRepository;
use Symfony\Component\HttpFoundation\Request;

final class Index
{
    public function __construct(
        private readonly JobRepository $repository
    ) {
    }

    public function __invoke(Request $request)
    {
        return new Template(
            '@SolidInvoiceJob/Default/index.html.twig',
            [
                'status_list_count' => [
                    Job::STATUS_PENDING => $this->repository->getTotalJobs(Job::STATUS_PENDING),
                    Job::STATUS_IN_PROGRESS => $this->repository->getTotalJobs(Job::STATUS_IN_PROGRESS),
                    Job::STATUS_DONE => $this->repository->getTotalJobs(Job::STATUS_DONE),
                ],
            ]
        );
    }
}