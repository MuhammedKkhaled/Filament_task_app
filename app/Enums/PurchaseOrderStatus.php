<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case PENDING = 'Pending';
    case COMPLETED = 'Completed';
}
