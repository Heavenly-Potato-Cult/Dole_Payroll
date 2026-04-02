<?php

namespace App\Http\Controllers;

use App\Models\TevItineraryLine;
use App\Models\TevRequest;
use App\Services\TevComputationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TevItineraryController extends Controller
{
    public function __construct(private TevComputationService $tevService) {}

    // ─────────────────────────────────────────────────────────────────────
    //  Store — add one itinerary line
    //  POST /tev/{tevRequest}/itinerary
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request, int $tevRequest)
    {
        $tev = TevRequest::findOrFail($tevRequest);
        $this->assertDraft($tev);
        $this->authorizeEdit($tev);

        $data = $request->validate([
            'travel_date'         => ['required', 'date'],
            'origin'              => ['required', 'string', 'max:255'],
            'destination'         => ['required', 'string', 'max:255'],
            'departure_time'      => ['nullable', 'date_format:H:i'],
            'arrival_time'        => ['nullable', 'date_format:H:i'],
            'mode_of_transport'   => ['required', 'string', 'max:50'],
            'transportation_cost' => ['required', 'numeric', 'min:0'],
            'per_diem_amount'     => ['required', 'numeric', 'min:0'],
            'is_half_day'         => ['nullable', 'boolean'],
            'remarks'             => ['nullable', 'string', 'max:500'],
        ]);

        TevItineraryLine::create(array_merge($data, [
            'tev_request_id' => $tev->id,
            'is_half_day'    => !empty($data['is_half_day']),
        ]));

        $this->tevService->computeTotals($tev);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'Itinerary line added.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Update — edit one itinerary line
    //  PUT /tev/{tevRequest}/itinerary/{line}
    // ─────────────────────────────────────────────────────────────────────
    public function update(Request $request, int $tevRequest, int $line)
    {
        $tev      = TevRequest::findOrFail($tevRequest);
        $itinLine = TevItineraryLine::where('tev_request_id', $tev->id)->findOrFail($line);

        $this->assertDraft($tev);
        $this->authorizeEdit($tev);

        $data = $request->validate([
            'travel_date'         => ['required', 'date'],
            'origin'              => ['required', 'string', 'max:255'],
            'destination'         => ['required', 'string', 'max:255'],
            'departure_time'      => ['nullable', 'date_format:H:i'],
            'arrival_time'        => ['nullable', 'date_format:H:i'],
            'mode_of_transport'   => ['required', 'string', 'max:50'],
            'transportation_cost' => ['required', 'numeric', 'min:0'],
            'per_diem_amount'     => ['required', 'numeric', 'min:0'],
            'is_half_day'         => ['nullable', 'boolean'],
            'remarks'             => ['nullable', 'string', 'max:500'],
        ]);

        $itinLine->update(array_merge($data, [
            'is_half_day' => !empty($data['is_half_day']),
        ]));

        $this->tevService->computeTotals($tev);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'Itinerary line updated.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Destroy — remove one itinerary line
    //  DELETE /tev/{tevRequest}/itinerary/{line}
    // ─────────────────────────────────────────────────────────────────────
    public function destroy(int $tevRequest, int $line)
    {
        $tev      = TevRequest::findOrFail($tevRequest);
        $itinLine = TevItineraryLine::where('tev_request_id', $tev->id)->findOrFail($line);

        $this->assertDraft($tev);
        $this->authorizeEdit($tev);

        $itinLine->delete();

        $this->tevService->computeTotals($tev);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'Itinerary line removed.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────────────

    private function assertDraft(TevRequest $tev): void
    {
        if ($tev->status !== 'draft') {
            abort(403, 'Itinerary can only be edited while the TEV is in draft status.');
        }
    }

    private function authorizeEdit(TevRequest $tev): void
    {
        $user    = Auth::user();
        $isStaff = $user->hasAnyRole(['payroll_officer', 'hrmo']);
        $isOwner = $tev->employee && $tev->employee->user_id === $user->id;

        if (!$isStaff && !$isOwner) {
            abort(403);
        }
    }
}