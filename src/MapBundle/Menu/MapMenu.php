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

namespace SolidInvoice\MapBundle\Menu;

use Knp\Menu\ItemInterface;

class MapMenu
{
    public static function view(ItemInterface $item): ItemInterface
    {
        return $item->addChild(
            'menu.map.view',
            [
                'route' => '_map',
                'extras' => ['icon' => 'map-marker-alt'],
            ]
        );
    }
}