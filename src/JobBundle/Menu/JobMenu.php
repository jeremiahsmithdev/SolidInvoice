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

namespace SolidInvoice\JobBundle\Menu;

use Knp\Menu\ItemInterface;

/**
 * Menu items for jobs.
 */
class JobMenu
{
    public static function list(ItemInterface $item): ItemInterface
    {
        return $item->addChild(
            'job.menu.list',
            [
                'route' => '_jobs_index',
                'extras' => [
                    'icon' => 'tasks',
                ],
            ],
        );
    }

    public static function create(ItemInterface $item): ItemInterface
    {
        return $item->addChild(
            'job.menu.create',
            [
                'extras' => [
                    'icon' => 'plus',
                ],
                'route' => '_jobs_create',
            ],
        );
    }
}