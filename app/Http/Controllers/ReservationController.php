<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    // Admin: Daftar semua reservasi
    public function adminIndex(Request $request)
    {
        $status = $request->query('status', 'all');
        $query  = Reservation::with(['court', 'user'])->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reservations = $query->get();

        $counts = [
            'all'       => Reservation::count(),
            'pending'   => Reservation::where('status', 'pending')->count(),
            'success'   => Reservation::where('status', 'success')->count(),
            'cancelled' => Reservation::where('status', 'cancelled')->count(),
        ];

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $reservations,
            'counts'       => $counts,
            'currentStatus'=> $status,
            'flash'        => session()->only(['success', 'error']),
        ]);
    }

    // Admin: Konfirmasi reservasi
    public function confirm(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['message' => 'Reservasi ini tidak bisa dikonfirmasi.']);
        }
        $reservation->update(['status' => 'success']);
        return back()->with('success', "Reservasi {$reservation->booking_code} berhasil dikonfirmasi.");
    }

    // Admin: Tolak/batalkan reservasi oleh admin
    public function adminCancel(Reservation $reservation)
    {
        if ($reservation->status === 'cancelled') {
            return back()->withErrors(['message' => 'Reservasi ini sudah dibatalkan.']);
        }
        $reservation->update(['status' => 'cancelled']);
        return back()->with('success', "Reservasi {$reservation->booking_code} telah dibatalkan.");
    }


    // Public: Cek ketersediaan slot (GET)
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'date'     => 'required|date',
        ]);

        $reservations = Reservation::where('court_id', $request->query('court_id'))
            ->where('date', $request->query('date'))
            ->whereIn('status', ['pending', 'success'])
            ->get(['start_time', 'end_time']);

        return response()->json($reservations);
    }

    // Authenticated: Buat booking baru
    public function store(Request $request)
    {
        $request->validate([
            'court_id'    => 'required|exists:courts,id',
            'date'        => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'total_price' => 'required|numeric|min:0',
        ]);

        // Double booking check
        $conflict = Reservation::where('court_id', $request->court_id)
            ->where('date', $request->date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($request) {
                $q->where('start_time', '<', $request->end_time)
                  ->where('end_time', '>', $request->start_time);
            })->exists();

        if ($conflict) {
            return back()->withErrors(['message' => 'Slot waktu tersebut sudah dibooking oleh orang lain.']);
        }

        $bookingCode = 'VZ-' . strtoupper(\Illuminate\Support\Str::random(8));

        $reservation = Reservation::create([
            'user_id'     => auth()->id(),
            'court_id'    => $request->court_id,
            'booking_code'=> $bookingCode,
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'total_price' => $request->total_price,
            'status'      => 'pending',
        ]);

        return redirect()->route('reservations.show', $reservation->id);
    }

    // Authenticated: Halaman sukses / detail booking
    public function show(Reservation $reservation)
    {
        // Pastikan hanya pemilik booking yang bisa lihat
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        $reservation->load('court');

        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    // Authenticated: Daftar semua booking milik user
    public function myBookings()
    {
        $reservations = Reservation::where('user_id', auth()->id())
            ->with('court')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
        ]);
    }

    // Authenticated: Batalkan booking (hanya jika masih pending)
    public function cancel(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        if ($reservation->status !== 'pending') {
            return back()->withErrors(['message' => 'Booking ini tidak dapat dibatalkan.']);
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('reservations.index')
            ->with('success', 'Booking berhasil dibatalkan.');
    }
}
