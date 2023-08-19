<?php

declare(strict_types=1);

namespace App\Domain\Payments;

enum PaymentStatus: string
{
    case Awaiting = 'awating';

    case Paid = 'paid';

    case Serviced = 'serviced';

    case Failed = 'failed';

    /**
     * @return string
     */
    public function getHumanReadableName(): string
    {
        return match ($this) {
            PaymentStatus::Paid => 'Оплачен',
            PaymentStatus::Awaiting => 'Ожидает оплаты',
            PaymentStatus::Serviced => 'Обслужен',
            PaymentStatus::Failed => 'Платёж устарел',
        };
    }
}
