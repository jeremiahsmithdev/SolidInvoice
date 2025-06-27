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

namespace SolidInvoice\MapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SolidInvoiceMapBundle extends Bundle
{
    final public const NAMESPACE = __NAMESPACE__;

    public function getPath(): string
    {
        return \dirname(__DIR__) . '/MapBundle';
    }
}