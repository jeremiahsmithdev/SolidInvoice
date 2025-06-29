<?php

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\InvoiceBundle\Twig\Components;

use Brick\Math\Exception\MathException;
use SolidInvoice\ClientBundle\Repository\ClientRepository;
use SolidInvoice\CoreBundle\Billing\TotalCalculator;
use SolidInvoice\CoreBundle\Traits\SaveableTrait;
use SolidInvoice\InvoiceBundle\Email\InvoiceEmail;
use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\InvoiceBundle\Form\Type\InvoiceType;
use SolidInvoice\InvoiceBundle\Model\Graph;
use SolidInvoice\TaxBundle\Repository\TaxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent()]
final class CreateInvoice extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;
    use SaveableTrait;

    #[LiveProp(writable: true, fieldName: 'formData')]
    public Invoice $invoice;

    #[LiveProp(writable: true)]
    public bool $isEdit = false;

    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly TotalCalculator $totalCalculator,
        private readonly TaxRepository $taxRepository,
        private readonly WorkflowInterface $invoiceStateMachine,
        private readonly RouterInterface $router,
        private readonly MailerInterface $mailer,
    ) {
    }

    /**
     * @throws MathException
     */
    #[PreReRender]
    public function preRender(): void
    {
        $this->totalCalculator->calculateTotals($this->invoice);
    }

    protected function instantiateForm(): FormInterface
    {
        $options = [];

        if (($this->formValues['client'] ?? '') !== '') {
            $client = $this->clientRepository->find($this->formValues['client']);
            $options['currency'] = $client?->getCurrency();
        }

        return $this->createForm(InvoiceType::class, $this->invoice, $options);
    }

    #[LiveAction]
    public function clearClient(): void
    {
        $this->formValues['client'] = null;
    }

    #[LiveAction]
    public function saveInvoice(Request $request): RedirectResponse
    {
        $this->submitForm();
        
        /** @var Invoice $invoice */
        $invoice = $this->getForm()->getData();
        $action = $request->request->get('save');

        if (! $invoice->getId() instanceof Ulid) {
            $this->invoiceStateMachine->apply($invoice, Graph::TRANSITION_NEW);
        }

        if (Graph::STATUS_PENDING === $action || 'publish' === $action) {
            $this->invoiceStateMachine->apply($invoice, Graph::TRANSITION_ACCEPT);
        }

        $this->save($invoice);

        if (Graph::STATUS_PENDING === $action) {
            $this->mailer->send(new InvoiceEmail($invoice));
        }

        $this->addFlash('success', 'invoice.create.success');

        return $this->redirectToRoute('_invoices_view', ['id' => $invoice->getId()]);
    }

    #[ExposeInTemplate]
    public function hasTax(): bool
    {
        return $this->taxRepository->taxRatesConfigured();
    }
}
