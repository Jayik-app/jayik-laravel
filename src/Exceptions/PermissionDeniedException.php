<?php

namespace Jayik\Laravel\Exceptions;

/** 403 — inactive account, missing permission, or action not allowed (e.g. deleting a non-Pending order). */
class PermissionDeniedException extends RequestException
{
}
