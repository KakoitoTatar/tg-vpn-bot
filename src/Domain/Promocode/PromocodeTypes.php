<?php

declare(strict_types=1);

namespace App\Domain\Promocode;

enum PromocodeTypes: string
{
    case Referral = 'referral';

    case Discount5 = 'discount5';

    case Discount10 = 'discount10';

    case Additional5 = 'additional5';

    case Additional10 = 'additional10';

    /**
     * @return int
     */
    public function getDiscount(): int
    {
        return match ($this) {
            PromocodeTypes::Referral => 7,
            PromocodeTypes::Discount10 => 10,
            PromocodeTypes::Discount5 => 5,
            PromocodeTypes::Additional5, PromocodeTypes::Additional10 => 0
        };
    }

    /**
     * @return int
     */
    public function getAdditionalDays(): int
    {
        return match ($this) {
            PromocodeTypes::Referral,PromocodeTypes::Discount10, PromocodeTypes::Discount5 => 0,
            PromocodeTypes::Additional5 => 5,
            PromocodeTypes::Additional10 => 10
        };
    }
}