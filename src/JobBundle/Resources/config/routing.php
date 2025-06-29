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

use SolidInvoice\JobBundle\Action\CloneJob;
use SolidInvoice\JobBundle\Action\Create;
use SolidInvoice\JobBundle\Action\Edit;
use SolidInvoice\JobBundle\Action\Index;
use SolidInvoice\JobBundle\Action\Transition;
use SolidInvoice\JobBundle\Action\View;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator
        ->add('_jobs_index', '/')
        ->controller(Index::class);

    $routingConfigurator
        ->add('_jobs_create', '/create/{client}/{quote}')
        ->controller(Create::class)
        ->defaults(['client' => null, 'quote' => null]);

    $routingConfigurator
        ->add('_jobs_edit', '/edit/{id}')
        ->controller(Edit::class);

    $routingConfigurator
        ->add('_jobs_view', '/view/{id}.{_format}')
        ->controller(View::class)
        ->defaults(['_format' => 'html'])
        ->requirements(['_format' => 'html|pdf']);

    $routingConfigurator
        ->add('_jobs_clone', '/clone/{id}')
        ->controller(CloneJob::class);

    $routingConfigurator
        ->add('_transition_job', '/action/{action}/{id}')
        ->controller(Transition::class);
};