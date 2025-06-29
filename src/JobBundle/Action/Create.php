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

use Exception;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Repository\ClientRepository;
use SolidInvoice\CoreBundle\Templating\Template;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Form\Handler\JobCreateHandler;
use SolidInvoice\QuoteBundle\Entity\Quote;
use SolidWorx\FormHandler\FormHandler;
use SolidWorx\FormHandler\FormRequest;
use Symfony\Component\HttpFoundation\Request;

final class Create
{
    public function __construct(
        private readonly ClientRepository $repository,
        private readonly FormHandler $handler
    ) {
    }

    /**
     * @return Template|FormRequest
     * @throws Exception
     */
    public function __invoke(Request $request, ?Client $client = null, ?Quote $quote = null)
    {
        $totalClientsCount = $this->repository->getTotalClients();
        if (0 === $totalClientsCount) {
            return new Template('@SolidInvoiceJob/Default/empty_clients.html.twig');
        }

        $job = new Job();
        $job->setClient($client);
        $job->setQuote($quote);

        if (1 === $totalClientsCount && ! $client instanceof Client) {
            $job->setClient($this->repository->findOneBy([]));
        }

        if ($quote instanceof Quote && null === $client) {
            $job->setClient($quote->getClient());
        }

        $options = [
            'job' => $job,
            'form_options' => [],
        ];

        return $this->handler->handle(JobCreateHandler::class, $options);
    }
}