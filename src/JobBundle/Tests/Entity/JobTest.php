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

namespace SolidInvoice\JobBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\QuoteBundle\Entity\Quote;

/**
 * @covers \SolidInvoice\JobBundle\Entity\Job
 */
final class JobTest extends TestCase
{
    public function testJobCreation(): void
    {
        $quote = new Quote();
        $client = new Client();

        $job = new Job();
        $job->setQuote($quote);
        $job->setClient($client);
        $job->setStatus(Job::STATUS_PENDING);
        $job->setDescription('Test job description');

        self::assertSame($quote, $job->getQuote());
        self::assertSame($client, $job->getClient());
        self::assertSame(Job::STATUS_PENDING, $job->getStatus());
        self::assertSame('Test job description', $job->getDescription());
        self::assertTrue($job->isPending());
        self::assertFalse($job->isInProgress());
        self::assertFalse($job->isDone());
        self::assertFalse($job->isCancelled());
        self::assertFalse($job->isArchived());
    }

    public function testJobStatusTransitions(): void
    {
        $job = new Job();

        // Test initial status
        self::assertSame(Job::STATUS_PENDING, $job->getStatus());
        self::assertTrue($job->isPending());

        // Test in progress status
        $job->setStatus(Job::STATUS_IN_PROGRESS);
        self::assertSame(Job::STATUS_IN_PROGRESS, $job->getStatus());
        self::assertTrue($job->isInProgress());
        self::assertFalse($job->isPending());
        self::assertFalse($job->isDone());

        // Test done status
        $job->setStatus(Job::STATUS_DONE);
        self::assertSame(Job::STATUS_DONE, $job->getStatus());
        self::assertTrue($job->isDone());
        self::assertFalse($job->isPending());
        self::assertFalse($job->isInProgress());
        self::assertFalse($job->isCancelled());
        self::assertFalse($job->isArchived());

        // Test cancelled status
        $job->setStatus(Job::STATUS_CANCELLED);
        self::assertSame(Job::STATUS_CANCELLED, $job->getStatus());
        self::assertTrue($job->isCancelled());
        self::assertFalse($job->isPending());
        self::assertFalse($job->isInProgress());
        self::assertFalse($job->isDone());
        self::assertFalse($job->isArchived());

        // Test archived status
        $job->setStatus(Job::STATUS_ARCHIVED);
        self::assertSame(Job::STATUS_ARCHIVED, $job->getStatus());
        self::assertTrue($job->isArchived());
        self::assertFalse($job->isPending());
        self::assertFalse($job->isInProgress());
        self::assertFalse($job->isDone());
        self::assertFalse($job->isCancelled());
    }

    public function testJobToString(): void
    {
        $quote = new Quote();
        $job = new Job();
        $job->setQuote($quote);

        // Test with description
        $job->setDescription('Custom job description');
        self::assertSame('Custom job description', (string) $job);

        // Test without description (should use quote ID)
        $job->setDescription(null);
        self::assertStringStartsWith('Job for Quote #', (string) $job);
    }

    public function testJobScheduledDate(): void
    {
        $job = new Job();
        $scheduledDate = new \DateTime('+1 week');

        $job->setScheduledDate($scheduledDate);
        self::assertSame($scheduledDate, $job->getScheduledDate());

        $job->setScheduledDate(null);
        self::assertNull($job->getScheduledDate());
    }
}