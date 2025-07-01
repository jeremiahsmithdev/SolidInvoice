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

namespace SolidInvoice\JobBundle\Model;

final class Graph
{
    public const TRANSITION_START = 'start';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_CANCEL = 'cancel';

    public const TRANSITION_REOPEN = 'reopen';

    public const TRANSITION_ARCHIVE = 'archive';

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * @return array<string, string>
     */
    public static function statusArray(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_DONE => 'Done',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }
}