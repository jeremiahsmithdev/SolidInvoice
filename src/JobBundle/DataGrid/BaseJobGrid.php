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

use SolidInvoice\DataGridBundle\Grid;
use SolidInvoice\DataGridBundle\GridBuilder\Action\EditAction;
use SolidInvoice\DataGridBundle\GridBuilder\Action\ViewAction;
use SolidInvoice\DataGridBundle\GridBuilder\Batch\BatchAction;
use SolidInvoice\DataGridBundle\GridBuilder\Column\DateTimeColumn;
use SolidInvoice\DataGridBundle\GridBuilder\Column\StringColumn;
use SolidInvoice\DataGridBundle\GridBuilder\Filter\ChoiceFilter;
use SolidInvoice\DataGridBundle\GridBuilder\Filter\DateRangeFilter;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Repository\JobRepository;

abstract class BaseJobGrid extends Grid
{
    public function entityFQCN(): string
    {
        return Job::class;
    }

    public function columns(): array
    {
        return [
            StringColumn::new('id')
                ->label('Job #')
                ->searchable(false),
            StringColumn::new('client')
                ->searchable(false)
                ->linkToRoute('_clients_view', ['id' => 'client.id']),
            StringColumn::new('quote')
                ->searchable(false)
                ->linkToRoute('_quotes_view', ['id' => 'quote.id'])
                ->formatValue(fn (object $quote) => $quote->getQuoteId()),
            StringColumn::new('status')
                ->filter(ChoiceFilter::new('status', [
                    Job::STATUS_PENDING => 'Pending',
                    Job::STATUS_IN_PROGRESS => 'In Progress',
                    Job::STATUS_DONE => 'Done'
                ])->multiple()),
            StringColumn::new('description')
                ->truncate(50),
            DateTimeColumn::new('scheduledDate')
                ->label('Scheduled Date')
                ->format('d M Y')
                ->nullable(),
            DateTimeColumn::new('created')
                ->format('d M Y')
                ->filter(new DateRangeFilter('created'))
        ];
    }

    public function actions(): array
    {
        return [
            ViewAction::new('_jobs_view', ['id' => 'id']),
            EditAction::new('_jobs_edit', ['id' => 'id']),
        ];
    }

    public function batchActions(): iterable
    {
        yield BatchAction::new('Delete')
            ->icon('trash')
            ->color('danger')
            ->action(static function (JobRepository $repository, array $selectedItems): void {
                foreach ($selectedItems as $jobId) {
                    $job = $repository->find($jobId);
                    if ($job) {
                        $repository->remove($job);
                    }
                }
                $repository->getEntityManager()->flush();
            });
    }
}