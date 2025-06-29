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

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->defaults()
        ->autoconfigure()
        ->autowire()
        ->private()
    ;

    $services
        ->load('SolidInvoice\\JobBundle\\', dirname(__DIR__, 3))
        ->exclude(dirname(__DIR__, 3) . '/{DependencyInjection,Resources,Tests}');

    $services
        ->load('SolidInvoice\\JobBundle\\Action\\', dirname(__DIR__, 3) . '/Action')
        ->tag('controller.service_arguments');

    $services
        ->set('solidinvoice.job.menu.builder', 'SolidInvoice\\JobBundle\\Menu\\Builder')
        ->tag('cs_core.menu', ['menu' => 'sidebar']);
};