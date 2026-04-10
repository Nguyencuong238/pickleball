<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Ngoại lệ miền cho luồng chuyển Gems (payer → payee).
 * Mọi thông báo hướng tới người dùng đều viết bằng Tiếng Việt có dấu.
 */
class GemTransferException extends RuntimeException
{
    public static function selfPayment(): self
    {
        return new self('Không thể thanh toán Gems cho chính mình.');
    }

    public static function missingOwner(): self
    {
        return new self('Không tìm thấy chủ sở hữu để nhận Gems. Vui lòng liên hệ hỗ trợ.');
    }

    public static function missingPayer(): self
    {
        return new self('Không xác định được người thanh toán Gems.');
    }

    public static function invalidAmount(int $gems): self
    {
        return new self("Số Gems không hợp lệ: {$gems}.");
    }

    public static function insufficientSpendable(int $have, int $need): self
    {
        return new self("Số dư Gems khả dụng không đủ. Cần {$need} Gems, hiện có {$have} Gems có thể sử dụng.");
    }

    public static function duplicateTransaction(string $refType, int $refId): self
    {
        return new self("Giao dịch Gems trùng lặp cho tham chiếu {$refType}#{$refId}.");
    }

    public static function refundOutsideWindow(): self
    {
        return new self('Không thể hoàn Gems sau 24 giờ. Vui lòng liên hệ hỗ trợ.');
    }

    public static function alreadyRefunded(): self
    {
        return new self('Giao dịch đã được hoàn trước đó, không thể hoàn lại.');
    }

    public static function refundTargetNotFound(): self
    {
        return new self('Không tìm thấy giao dịch đối ứng để hoàn Gems.');
    }
}
