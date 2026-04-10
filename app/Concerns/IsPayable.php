<?php

namespace App\Concerns;

/**
 * Mặc định triển khai cho loại và ID tham chiếu của Payable.
 */
trait IsPayable
{
    public function getPayableReferenceType(): string
    {
        return static::class;
    }

    public function getPayableReferenceId(): int
    {
        return (int) $this->getKey();
    }
}
