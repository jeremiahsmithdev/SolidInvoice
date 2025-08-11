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

namespace SolidInvoice\JobBundle\Form\Handler;

use Generator;
use SolidInvoice\CoreBundle\Generator\BillingIdGenerator;
use SolidInvoice\CoreBundle\Response\FlashResponse;
use SolidInvoice\CoreBundle\Traits\SaveableTrait;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Form\Type\JobType;
use SolidWorx\FormHandler\FormHandlerInterface;
use SolidWorx\FormHandler\FormHandlerOptionsResolver;
use SolidWorx\FormHandler\FormHandlerResponseInterface;
use SolidWorx\FormHandler\FormHandlerSuccessInterface;
use SolidWorx\FormHandler\FormRequest;
use SolidWorx\FormHandler\Options;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractJobHandler implements FormHandlerInterface, FormHandlerResponseInterface, FormHandlerSuccessInterface, FormHandlerOptionsResolver
{
    use SaveableTrait;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly BillingIdGenerator $billingIdGenerator,
    ) {
    }

    public function getForm(FormFactoryInterface $factory, Options $options)
    {
        return $factory->create(JobType::class, $options->get('job'), $options->get('form_options') ?? []);
    }

    public function onSuccess(FormRequest $formRequest, $data): ?Response
    {
        /** @var Job $job */
        $job = $data;

        // Auto-set client based on quote relationship
        if ($job->getQuote() && null === $job->getClient()) {
            $job->setClient($job->getQuote()->getClient());
        }

        // Auto-generate job ID if not set
        if (null === $job->getJobId()) {
            $jobId = $this->billingIdGenerator->generate($job, ['field' => 'jobId']);
            $job->setJobId($jobId);
        }

        $this->save($job);

        $route = $this->router->generate('_jobs_view', ['id' => $job->getId()]);

        return new class($route) extends RedirectResponse implements FlashResponse {
            public function getFlash(): Generator
            {
                yield self::FLASH_SUCCESS => 'job.create.success';
            }
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['job'])
            ->setDefault('form_options', [])
            ->addAllowedTypes('job', [Job::class])
            ->addAllowedTypes('form_options', ['array']);
    }
}
