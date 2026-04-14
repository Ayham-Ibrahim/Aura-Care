<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\Reservation;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WalletService extends Service
{
    public function getWalletForCenter($data): array
    {
        $center = auth('center')->user();

        if (!$center) {
            $this->throwExceptionJson('غير مصرح لك بالوصول إلى هذه البيانات', 403);
        }

        try {

            $center_wallet = Wallet::with('reservation:id,center_id,total_amount,deposit_amount,status,user_id', 'reservation.user:id,name,phone,avatar')
            ->when($data['status'] ?? null, function ($query) use ($data) {
                if ($data['status'] === 'completed') {
                    $query->whereHas('reservation', function ($q) {
                        $q->where('status', 'completed');
                    });
                } elseif ($data['status'] === 'incompleted') {
                    $query->whereHas('reservation', function ($q) {
                        $q->where('status', 'incompleted');
                    });
                }
            })
            ->where('center_id', $center->id)
            ->get();

            $reservations = $center_wallet->pluck('reservation');

            $totalReservation = $reservations->sum(function ($reservation) {
                return $reservation->status === 'completed' ? $reservation->total_amount : $reservation->deposit_amount;
            });

            $profitPercentage = (float) ($center->section?->profit_percentage ?? 0);
            $adminPayable = round($center_wallet->sum('required_value'), 2);
            $centerNetProfit = $totalReservation - $adminPayable;

            return [
                'total_reservation' => round($totalReservation, 2),
                'management_percentage' => $profitPercentage,
                'admin_payable' => $adminPayable,
                'center_net_profit' => round($centerNetProfit, 2),
                'reservations' => $reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'status' => $reservation->status,
                        'total_amount' => $reservation->total_amount,
                        'deposit_amount' => $reservation->deposit_amount,
                        'user' => $reservation->user ? $reservation->user->only(['id', 'name', 'phone', 'avatar']) : null,
                    ];
                })->values(),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching wallet data for center', [
                'center_id' => $center->id ?? null,
                'status' => $data['status'] ?? null,
                'error' => $e->getMessage(),
            ]);

            $this->throwExceptionJson('حدث خطأ ما أثناء جلب بيانات المحفظة');
        }
    }

    public function getWalletForAdmin(array $data): array
    {
        try {
            $perPage = isset($data['perPage']) ? (int) $data['perPage'] : 10;
            $today = Carbon::today();

            $summary = [
                'current_month_total' => (float) Wallet::whereYear('created_at', $today->year)
                    ->whereMonth('created_at', $today->month)
                    ->sum('required_value'),
                'today_total' => (float) Wallet::whereDate('created_at', $today)->sum('required_value'),
                'total_value' => (float) Wallet::where('is_paid', false)->sum('required_value'),
            ];

            $paginatedCenters = Center::withSum('wallets as total_required_value', 'required_value')
                ->orderByDesc('total_required_value')
                ->wherehas('wallets', function ($query) {
                    $query->where('is_paid', false);
                })
                ->paginate($perPage);

            $paginatedCenters->getCollection()->transform(function (Center $center) {
                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'logo' => $center->logo,
                    'total_value' => round((float) $center->total_required_value, 2),
                ];
            });

            return [
                'summary' => $summary,
                'pagination' => $paginatedCenters,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching admin wallet summary', [
                'error' => $e->getMessage(),
                'input' => $data,
            ]);

            $this->throwExceptionJson('حدث خطأ ما أثناء جلب بيانات محفظة الإدارة');
        }
    }

    public function getCenterWalletDetails(Center $center, array $data): array
    {
        try {
            $perPage = isset($data['perPage']) ? (int) $data['perPage'] : 10;
            $today = Carbon::today();

            $summary = [
                'current_month_value' => (float) Wallet::where('center_id', $center->id)
                    ->whereYear('created_at', $today->year)
                    ->whereMonth('created_at', $today->month)
                    ->sum('required_value'),
                'today_value' => (float) Wallet::where('center_id', $center->id)
                    ->whereDate('created_at', $today)
                    ->sum('required_value'),
            ];

            $wallets = Wallet::with([
                'reservation:id,center_id,user_id,status,total_amount,deposit_amount,date',
                'reservation.user:id,name',
                'reservation.manageSubservices.subservice:id,name'
            ])
                ->where('center_id', $center->id)
                ->where('is_paid', false)
                ->orderByDesc('created_at')
                ->paginate($perPage);

            $wallets->getCollection()->transform(function (Wallet $wallet) {
                $reservation = $wallet->reservation;

                return [
                    'id' => $wallet->id,
                    'customer_name' => $reservation?->user ? $reservation->user->name : null,
                    'reservation_date' => optional($reservation?->date)->format('Y-m-d'),
                    'required_value' => (float) $wallet->required_value,
                    'reservation_status' => $reservation?->status,
                    'subservices' => $reservation?->manageSubservices
                        ->map(fn ($item) => optional($item->subservice)->name)
                        ->filter()
                        ->values(),
                ];
            });

            return [
                'summary' => $summary,
                'pagination' => $wallets,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching center wallet details', [
                'center_id' => $center->id,
                'error' => $e->getMessage(),
                'input' => $data,
            ]);

            $this->throwExceptionJson('حدث خطأ ما أثناء جلب تفاصيل محفظة المركز');
        }
    }

    public function markWalletAsPaid(Wallet $wallet): Wallet
    {
        try {
            if ($wallet->is_paid) {
                return $wallet;
            }

            return $wallet->markAsPaid();
        } catch (\Exception $e) {
            Log::error('Error marking wallet as paid', [
                'wallet_id' => $wallet->id,
                'error' => $e->getMessage(),
            ]);

            $this->throwExceptionJson('حدث خطأ ما أثناء تحديث حالة المحفظة');
        }
    }

    public function markCenterWalletsAsPaid(Center $center): int
    {
        try {
            return $center->wallets()
                ->unpaid()
                ->update(['is_paid' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking center wallets as paid', [
                'center_id' => $center->id,
                'error' => $e->getMessage(),
            ]);

            $this->throwExceptionJson('حدث خطأ ما أثناء تحديث حالة محافظ المركز');
        }
    }
}
