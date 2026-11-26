<?php

namespace Jayik\Laravel\Support;

final class DeliveryType
{
    public const NORMAL = 0;

    /** Express ("fawri"). May require district_id on the order. */
    public const FAWRI = 1;
}
