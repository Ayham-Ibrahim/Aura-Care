<?php

namespace App\Http\Controllers;

use App\Http\Requests\Center\GetWalletForCenterRequest;
use App\Http\Requests\Wallet\GetCenterWalletDetailsRequest;
use App\Http\Requests\Wallet\GetWalletForAdminRequest;
use App\Models\Center\Center;
use App\Models\Wallet;
use App\Services\WalletService;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function getWalletForCenter(GetWalletForCenterRequest $request)
    {
        $payload = $this->walletService->getWalletForCenter($request->validated());
        return $this->success($payload, 'تم جلب بيانات المحفظة بنجاح');
    }

    public function getWalletForAdmin(GetWalletForAdminRequest $request)
    {
        $result = $this->walletService->getWalletForAdmin($request->validated());

        return $this->paginateWithData(
            $result['pagination'], 
            $result['summary'],
            'تم جلب بيانات محفظة الإدارة بنجاح'
        );
    }

    public function getCenterWalletDetails(GetCenterWalletDetailsRequest $request, Center $center)
    {
        $result = $this->walletService->getCenterWalletDetails($center, $request->validated());

        return $this->paginateWithData(
            $result['pagination'], 
            $result['summary'],
            'تم جلب تفاصيل محفظة المركز بنجاح'
        );
    }

    public function markWalletAsPaid(Wallet $wallet)
    {
        $wallet = $this->walletService->markWalletAsPaid($wallet);

        return $this->success($wallet, 'تم تأكيد دفع المحفظة بنجاح');
    }

    public function markCenterWalletsAsPaid(Center $center)
    {
        $count = $this->walletService->markCenterWalletsAsPaid($center);

        return $this->success(
            ['center_id' => $center->id, 'marked_paid_count' => $count],
            'تم تأكيد دفع جميع محافظ المركز بنجاح'
        );
    }
}
