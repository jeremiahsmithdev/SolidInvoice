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
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\JobBundle\Form\Handler\JobEditHandler;
use SolidWorx\FormHandler\FormHandler;
use SolidWorx\FormHandler\FormRequest;
use Symfony\Component\HttpFoundation\Request;

final class Edit
{
    public function __construct(
        private readonly FormHandler $handler,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request, Job $job): FormRequest
    {
        $options = [
            'job' => $job,
            'form_options' => [],
        ];

        return $this->handler->handle(JobEditHandler::class, $options);
    }
}