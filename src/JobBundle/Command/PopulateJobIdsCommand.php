<?php

declare(strict_types=1);

namespace SolidInvoice\JobBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use SolidInvoice\CoreBundle\Generator\BillingIdGenerator;
use SolidInvoice\JobBundle\Entity\Job;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'job:populate-ids',
    description: 'Populate job_id field for existing jobs without one'
)]
class PopulateJobIdsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BillingIdGenerator $billingIdGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $jobs = $this->entityManager->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->where('j.jobId IS NULL OR j.jobId = :empty')
            ->setParameter('empty', '')
            ->orderBy('j.created', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($jobs)) {
            $io->success('All jobs already have job IDs assigned.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d jobs without job IDs. Populating...', count($jobs)));

        foreach ($jobs as $job) {
            $jobId = $this->billingIdGenerator->generate($job, ['field' => 'jobId']);
            $job->setJobId($jobId);
            $io->writeln(sprintf('Assigned job ID "%s" to job %s', $jobId, $job->getId()));
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully assigned job IDs to %d jobs.', count($jobs)));

        return Command::SUCCESS;
    }
}