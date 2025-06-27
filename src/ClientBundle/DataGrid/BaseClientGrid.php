<?php

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\ClientBundle\DataGrid;

use Brick\Math\BigInteger;
use Doctrine\ORM\EntityManagerInterface;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Repository\ClientRepository;
use SolidInvoice\DataGridBundle\Grid;
use SolidInvoice\DataGridBundle\GridBuilder\Action\EditAction;
use SolidInvoice\DataGridBundle\GridBuilder\Action\ViewAction;
use SolidInvoice\DataGridBundle\GridBuilder\Batch\BatchAction;
use SolidInvoice\DataGridBundle\GridBuilder\Column\DateTimeColumn;
use SolidInvoice\DataGridBundle\GridBuilder\Column\MoneyColumn;
use SolidInvoice\DataGridBundle\GridBuilder\Column\StringColumn;
use SolidInvoice\DataGridBundle\GridBuilder\Filter\DateRangeFilter;
use SolidInvoice\DataGridBundle\GridBuilder\Query;
use SolidInvoice\DataGridBundle\Source\ORMSource;
use SolidInvoice\InvoiceBundle\Model\Graph;
use Symfony\Component\Translation\TranslatableMessage;

abstract class BaseClientGrid extends Grid
{
    public function __construct(
        private readonly string $locale
    ) {
    }

    public function columns(): array
    {
        return [
            StringColumn::new('firstName')
                ->label(new TranslatableMessage('First Name')),
            StringColumn::new('lastName')
                ->label(new TranslatableMessage('Last Name')),
            StringColumn::new('email')
                ->label(new TranslatableMessage('Email')),
            MoneyColumn::new('total')
                ->label(new TranslatableMessage('Total Balance'))
                ->sortable(false)
                ->searchable(false)
                ->formatValue(static function ($value, Client $client) {
                    $total = BigInteger::zero();

                    foreach ($client->getInvoices() as $invoice) {
                        if (
                            $invoice->getStatus() === Graph::STATUS_PAID ||
                            $invoice->getStatus() === Graph::STATUS_PENDING ||
                            $invoice->getStatus() === Graph::STATUS_OVERDUE
                        ) {
                            $total = $total->plus($invoice->getTotal());
                        }
                    }

                    return $total;
                }),
            MoneyColumn::new('outstanding')
                ->label(new TranslatableMessage('Outstanding Balance'))
                ->sortable(false)
                ->searchable(false)
                ->formatValue(static function ($value, Client $client) {
                    $totalOutstanding = BigInteger::zero();

                    foreach ($client->getOutstandingInvoices() as $invoice) {
                        $totalOutstanding = $totalOutstanding->plus($invoice->getBalance());
                    }

                    return $totalOutstanding;
                }),
            DateTimeColumn::new('created')
                ->format('d F Y')
                ->filter(new DateRangeFilter('created')),
        ];
    }

    public function actions(): array
    {
        return [
            ViewAction::new('_clients_view', ['id' => 'id']),
            EditAction::new('_clients_edit', ['id' => 'id']),
        ];
    }

    public function entityFQCN(): string
    {
        return Client::class;
    }

    public function batchActions(): iterable
    {
        yield BatchAction::new('Delete')
            ->icon('trash')
            ->color('danger')
            ->action(static function (ClientRepository $repository, array $selectedItems): void {
                $repository->deleteClients($selectedItems);
            });
    }

    public function query(EntityManagerInterface $entityManager, Query $query): Query
    {
        $query->getQueryBuilder()
            ->orderBy(ORMSource::ALIAS . '.created', 'ASC');

        return $query;
    }
}
