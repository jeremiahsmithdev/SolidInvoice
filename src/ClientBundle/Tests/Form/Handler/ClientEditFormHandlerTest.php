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

namespace SolidInvoice\ClientBundle\Tests\Form\Handler;

use Mockery as M;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Form\Handler\ClientEditFormHandler;
use SolidInvoice\ClientBundle\Model\Status;
use SolidInvoice\CoreBundle\Response\FlashResponse;
use SolidInvoice\CoreBundle\Templating\Template;
use SolidInvoice\FormBundle\Test\FormHandlerTestCase;
use SolidWorx\FormHandler\FormRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use function iterator_to_array;

class ClientEditFormHandlerTest extends FormHandlerTestCase
{
    private string $firstName;
    private string $lastName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firstName = $this->faker->firstName;
        $this->lastName = $this->faker->lastName;
    }

    public function getHandler()
    {
        $router = M::mock(RouterInterface::class);

        $router->shouldReceive('generate')
            ->zeroOrMoreTimes()
            ->withAnyArgs()
            ->andReturn('/client/1');

        $handler = new ClientEditFormHandler($router);
        $handler->setDoctrine($this->registry);

        return $handler;
    }

    protected function getHandlerOptions(): array
    {
        $client = new Client();
        $client->setFirstName('Test')
            ->setLastName('One')
            ->setEmail('test@example.com')
            ->setStatus(Status::STATUS_ACTIVE);

        $this->em->persist($client);
        $this->em->flush();

        return [
            'client' => $client,
        ];
    }

    public function getFormData(): array
    {
        return [
            'client' => [
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'email' => $this->faker->email(),
            ],
        ];
    }

    protected function assertOnSuccess(?Response $response, FormRequest $form, $client): void
    {
        /** @var Client $client */

        self::assertSame($this->firstName, $client->getFirstName());
        self::assertSame($this->lastName, $client->getLastName());
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertInstanceOf(FlashResponse::class, $response);
        self::assertCount(1, iterator_to_array($response->getFlash()));
    }

    protected function assertResponse(FormRequest $formRequest): void
    {
        self::assertInstanceOf(Template::class, $formRequest->getResponse());
    }
}
