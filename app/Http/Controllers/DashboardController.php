<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->userDashboard($user);
    }

    private function adminDashboard()
    {
        $totalCourts      = Court::count();
        $activeCourts     = Court::where('is_active', true)->count();
        $totalUsers       = User::where('role', 'user')->count();
        $totalReservations = Reservation::count();
        $pendingCount     = Reservation::where('status', 'pending')->count();
        $confirmedCount   = Reservation::where('status', 'success')->count();
        $cancelledCount   = Reservation::where('status', 'cancelled')->count();
        $totalRevenue     = Reservation::where('status', 'success')->sum('total_price');

        // Reservasi terbaru (10 terakhir)
        $recentReservations = Reservation::with(['court', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Reservasi pending yang perlu dikonfirmasi
        $pendingReservations = Reservation::with(['court', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'role'       => 'admin',
            'stats'      => [
                'totalCourts'       => $totalCourts,
                'activeCourts'      => $activeCourts,
                'totalUsers'        => $totalUsers,
                'totalReservations' => $totalReservations,
                'pendingCount'      => $pendingCount,
                'confirmedCount'    => $confirmedCount,
                'cancelledCount'    => $cancelledCount,
                'totalRevenue'      => $totalRevenue,
            ],
            'recentReservations'  => $recentReservations,
            'pendingReservations' => $pendingReservations,
        ]);
    }

    private function userDashboard($user)
    {
        $reservations = Reservation::where('user_id', $user->id)
            ->with('court')
            ->orderBy('created_at', 'desc')
            ->get();

        $upcoming = $reservations->filter(function ($r) {
            return in_array($r->status, ['pending', 'success'])
                && $r->date >= now()->toDateString();
        })->values()->take(3);

        $stats = [
            'total'     => $reservations->count(),
            'upcoming'  => $reservations->filter(fn($r) => in_array($r->status, ['pending', 'success']) && $r->date >= now()->toDateString())->count(),
            'confirmed' => $reservations->where('status', 'success')->count(),
            'cancelled' => $reservations->where('status', 'cancelled')->count(),
        ];

        return Inertia::render('Dashboard', [
            'role'        => 'user',
            'stats'       => $stats,
            'upcoming'    => $upcoming,
            'reservations'=> $reservations->take(5)->values(),
        ]);
    }
}
