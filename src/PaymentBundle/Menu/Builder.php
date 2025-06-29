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

namespace SolidInvoice\PaymentBundle\Menu;

use SolidInvoice\MenuBundle\Core\AuthenticatedMenu;
use SolidInvoice\MenuBundle\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Builder extends AuthenticatedMenu
{
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($authorizationChecker);
    }

    /**
     * Renders the top menu for payments.
     */
    public function mainMenu(ItemInterface $menu): void
    {
        $menu->addHeader('Payments');
        PaymentMenu::main($menu);
        PaymentMenu::methods($menu);
    }
}
