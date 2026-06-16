<?php

namespace App\Enums;

enum Role: string
{
    case PLATFORM_ADMIN = 'platform_admin';
    case SHOP_ADMIN = 'shop_admin';
    case SHOP_STAFF = 'shop_staff';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'Platform Admin',
            self::SHOP_ADMIN => 'Shop Admin',
            self::SHOP_STAFF => 'Shop Staff',
            self::CUSTOMER => 'Customer',
        };
    }
}
