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

namespace SolidInvoice\QuoteBundle\Tests\Listener;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as M;
use SolidInvoice\ClientBundle\Test\Factory\ClientFactory;
use SolidInvoice\CoreBundle\Generator\BillingIdGenerator;
use SolidInvoice\CoreBundle\Test\Traits\DoctrineTestTrait;
use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\InvoiceBundle\Manager\InvoiceManager;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Repository\JobRepository;
use SolidInvoice\NotificationBundle\Notification\NotificationManager;
use SolidInvoice\QuoteBundle\Entity\Quote;
use SolidInvoice\QuoteBundle\Listener\WorkFlowSubscriber;
use SolidInvoice\QuoteBundle\Mailer\QuoteMailer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @covers \SolidInvoice\QuoteBundle\Listener\WorkFlowSubscriber
 */
final class WorkFlowSubscriberTest extends KernelTestCase
{
    use DoctrineTestTrait;
    use MockeryPHPUnitIntegration;
    use Factories;

    public function testOnQuoteAccepted(): void
    {
        $client = ClientFactory::createOne()->_real();
        $quote = (new Quote())
            ->setClient($client)
            ->setTitle('Test Quote for Tree Removal');
        $invoice = new Invoice();

        $invoiceManager = M::mock(InvoiceManager::class);

        $invoiceManager->shouldReceive('createFromQuote')
            ->with($quote)
            ->andReturn($invoice);

        $stateMachine = M::mock(StateMachine::class);

        $stateMachine->shouldReceive('apply')
            ->with($invoice, 'new');

        $stateMachine->shouldReceive('apply')
            ->with($invoice, 'accept');

        $notification = M::mock(NotificationManager::class);
        $notification->shouldReceive('sendNotification')
            ->zeroOrMoreTimes();

        $jobRepository = M::mock(JobRepository::class);
        $jobRepository->shouldReceive('save')
            ->once()
            ->with(M::type(Job::class), true)
            ->andReturnUsing(function (Job $job) use ($quote, $client) {
                // Verify the job is created with correct properties
                self::assertSame($quote, $job->getQuote());
                self::assertSame($client, $job->getClient());
                self::assertSame(Job::STATUS_PENDING, $job->getStatus());
                self::assertSame('Test Quote for Tree Removal', $job->getDescription());
                self::assertNotNull($job->getJobId(), 'Job ID should be generated');
                return null;
            });

        $billingIdGenerator = M::mock(BillingIdGenerator::class);
        $billingIdGenerator->shouldReceive('generate')
            ->once()
            ->with(M::type(Job::class), ['field' => 'jobId'])
            ->andReturn('JOB-001');

        $subscriber = new WorkFlowSubscriber(
            $this->registry,
            $invoiceManager,
            $stateMachine,
            $notification,
            new QuoteMailer($stateMachine, M::mock(MailerInterface::class), $notification),
            $jobRepository,
            $billingIdGenerator
        );

        $subscriber->onQuoteAccepted(new Event($quote, new Marking(['pending' => 1]), new Transition('accept', 'pending', 'accepted'), M::mock(WorkflowInterface::class)));
    }

    public function testOnWorkflowTransitionApplied(): void
    {
        $quote = (new Quote())
            ->setClient(ClientFactory::createOne()->_real())
            ->setStatus('pending');

        $invoiceManager = M::mock(InvoiceManager::class);
        $stateMachine = M::mock(StateMachine::class);

        $notification = M::mock(NotificationManager::class);
        $notification->shouldReceive('sendNotification')
            ->zeroOrMoreTimes();

        $jobRepository = M::mock(JobRepository::class);
        $billingIdGenerator = M::mock(BillingIdGenerator::class);
        
        $subscriber = new WorkFlowSubscriber(
            $this->registry,
            $invoiceManager,
            $stateMachine,
            $notification,
            new QuoteMailer($stateMachine, M::mock(MailerInterface::class), $notification),
            $jobRepository,
            $billingIdGenerator
        );

        $subscriber->onWorkflowTransitionApplied(new Event($quote, new Marking(['pending' => 1]), new Transition('archive', 'pending', 'archived'), M::mock(WorkflowInterface::class)));

        self::assertTrue($quote->isArchived());
        self::assertSame($quote, $this->em->getRepository(Quote::class)->find($quote->getId()));
    }
}
