<?php

namespace Jayik\Laravel\Support;

/** Values of the `opration` key in webhook bodies. */
final class WebhookOperation
{
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const ASSIGNED = 'assigned';
    public const DELETED = 'deleted';
    public const RESTORED = 'restored';
    public const FORCE_DELETED = 'forceDeleted';
}
