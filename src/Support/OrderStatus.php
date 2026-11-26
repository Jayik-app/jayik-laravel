<?php

namespace Jayik\Laravel\Support;

/** Stable status codes, found in an order's `status.name`. */
final class OrderStatus
{
    public const PENDING = 'Pending';
    public const AWAITING_PICKUP = 'AwaitingPickup';
    public const PICKUP_DELAYED = 'PickupDelayed';
    public const CANCELLED_BY_MERCHANT = 'CancelledByMerchant';
    public const IN_TRANSIT = 'InTransit';
    public const IN_DELIVERY_COMPANY_WAREHOUSE = 'InDeliveryCompanyWarehouse';
    public const RECEIVED = 'Received';
    public const HAND_OVER = 'HandOver';
    public const DELAYED = 'Delayed';
    public const RE_DELIVERY = 'ReDelivery';
    public const DELIVERED = 'Delivered';
    public const RETURNED = 'Returned';
    public const RETURNED_TO_COMPANY = 'ReturnedToCompany';
    public const ON_THE_WAY_TO_MERCHANT_WAREHOUSE = 'OnTheWayToMerchantWarehouse';
    public const RETURNED_TO_MAIN_WAREHOUSE = 'ReturnedToMainWarehouse';
    public const CANCELLED = 'Cancelled';

    /** Statuses in which the merchant may still cancel the order. */
    public const CANCELLABLE = [self::PENDING, self::AWAITING_PICKUP, self::PICKUP_DELAYED];
}
