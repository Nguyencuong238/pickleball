<?php

namespace App\Contracts;

use App\Models\GemTransaction;
use App\Models\User;

/**
 * Hợp đồng cho bất kỳ thực thể nào có thể được thanh toán bằng Gems.
 * Dùng chung cho Booking, ClubActivity và các dịch vụ tương lai (League, Tournament, ...).
 */
interface Payable
{
    /**
     * Người thanh toán mặc định của thực thể (nếu có).
     * Trả về null nếu thực thể không có payer cố định — caller phải truyền rõ.
     */
    public function getPayer(): ?User;

    /**
     * Chủ sở hữu nhận Gems (stadium owner, club owner, ...).
     */
    public function getPayee(): ?User;

    /**
     * Tổng số tiền phải trả tính theo VND.
     */
    public function getPayableAmountVnd(): int;

    /**
     * Mô tả giao dịch hiển thị cho người dùng.
     */
    public function getPayableDescription(): string;

    /**
     * Loại tham chiếu (FQCN) — mặc định triển khai qua trait IsPayable.
     */
    public function getPayableReferenceType(): string;

    /**
     * ID tham chiếu — mặc định triển khai qua trait IsPayable.
     */
    public function getPayableReferenceId(): int;

    /**
     * Hook gọi sau khi transfer Gems thành công: xác nhận trạng thái domain.
     */
    public function markPaidWithGems(GemTransaction $debitTx): void;
}
