<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Requested = 'requested';
    case Rejected = 'rejected';
    case Approved = 'approved';
}
