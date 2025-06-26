<?php

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\PaymentBundle\Twig\Components;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SolidInvoice\CoreBundle\Response\FlashResponse;
use SolidInvoice\PaymentBundle\Entity\PaymentMethod;
use SolidInvoice\PaymentBundle\Factory\PaymentFactories;
use SolidInvoice\PaymentBundle\Form\Methods\BankTransfer;
use SolidInvoice\PaymentBundle\Form\Type\PaymentMethodType;
use SolidInvoice\PaymentBundle\Repository\PaymentMethodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use function assert;

#[AsLiveComponent(name: 'PaymentSettings')]
final class PaymentSettings extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly PaymentFactories $factories,
        private readonly PaymentMethodRepository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[LiveProp(writable: true, updateFromParent: true, url: true)]
    public string $method = '';

    #[ExposeInTemplate]
    public function paymentMethod(): PaymentMethod
    {
        $paymentMethod = $this->repository->findOneBy(['gatewayName' => $this->method]);

        if (! $paymentMethod instanceof PaymentMethod) {
            $paymentMethod = new PaymentMethod();
            $paymentMethod->setGatewayName($this->method);
            $paymentMethod->setFactoryName($this->factories->getFactory($this->method));
            $paymentMethod->setInternal($this->factories->isOffline($this->method));
        } else {
            // Only refresh if the entity is managed to avoid issues
            if ($this->entityManager->contains($paymentMethod)) {
                $this->entityManager->refresh($paymentMethod);
            } else {
                // Re-fetch the entity if it's detached
                $paymentMethod = $this->repository->findOneBy(['gatewayName' => $this->method]);
            }
        }

        return $paymentMethod;
    }

    /**
     * @throws Exception
     */
    protected function instantiateForm(): FormInterface
    {
        $paymentMethod = $this->paymentMethod();
        $factory = $paymentMethod->getFactoryName();

        return $this->createForm(
            PaymentMethodType::class,
            $paymentMethod,
            [
                'config' => $this->factories->getForm($this->method),
                'internal' => $factory === 'offline',
            ]
        );
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {
        $this->submitForm();

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->getForm()->getData();

        // Only set gateway name from slug if it's a new payment method and not a predefined gateway
        if (!$paymentMethod->getId() && !in_array($this->method, ['bank_transfer', 'paypal_express_checkout', 'stripe_checkout', 'cash', 'credit', 'custom'], true)) {
            $paymentMethod->setGatewayName(
                (new AsciiSlugger())
                    ->slug($paymentMethod->getName())
                    ->lower()
                    ->toString()
            );
        } elseif (!$paymentMethod->getId()) {
            // For predefined gateways, use the method name as gateway name
            $paymentMethod->setGatewayName($this->method);
        }

        $entityManager->persist($paymentMethod);
        $entityManager->flush();

        $session = $requestStack->getSession();
        assert($session instanceof Session);

        $session->getFlashBag()->add(FlashResponse::FLASH_SUCCESS, 'payment.method.updated');

        return $this->redirectToRoute('_payment_settings_index', ['method' => $paymentMethod->getGatewayName()]);
    }

    #[LiveAction]
    public function delete(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        assert($session instanceof Session);

        $paymentMethod = $this->paymentMethod();

        if (count($paymentMethod->getPayments()) > 0) {
            $session->getFlashBag()->add(FlashResponse::FLASH_ERROR, 'Unable to delete payment method as there are payments associated with it');

            return $this->redirectToRoute('_payment_settings_index', ['method' => $paymentMethod->getGatewayName()]);
        }

        $this->repository->delete($paymentMethod);

        $session->getFlashBag()->add(FlashResponse::FLASH_INFO, 'Payment method deleted');

        return $this->redirectToRoute('_payment_settings_index');
    }
}
