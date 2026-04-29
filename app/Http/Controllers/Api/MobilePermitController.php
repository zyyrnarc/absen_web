<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePermitRequest;
use App\Models\Permit;
use App\Support\MobileApiAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MobilePermitController extends Controller
{
    public function meta(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        return response()->json([
            'permit_types' => collect(StorePermitRequest::TYPES)
                ->map(fn (string $type) => ['value' => $type, 'label' => $type])
                ->values(),
            'attachment' => [
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_mb' => 4,
                'is_optional' => true,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $permits = Permit::query()
            ->where('user_id', $user->id)
            ->when($request->filled('month'), fn ($query) => $query->whereMonth('permit_date', (int) $request->input('month')))
            ->when($request->filled('year'), fn ($query) => $query->whereYear('permit_date', (int) $request->input('year')))
            ->latest('permit_date')
            ->latest('id')
            ->paginate(15);

        $permits->setCollection(
            $permits->getCollection()->map(fn (Permit $permit) => $this->transformPermit($permit))
        );

        return response()->json($permits);
    }

    public function store(StorePermitRequest $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $attachment = $request->file('attachment');

        $permit = Permit::query()->create([
            'user_id' => $user->id,
            'type' => $request->input('type'),
            'permit_date' => $request->input('permit_date', today()->toDateString()),
            'reason' => $request->input('reason'),
            'attachment_path' => $attachment?->store('mobile-permits', 'public'),
            'attachment_original_name' => $attachment?->getClientOriginalName(),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Permit berhasil diajukan.',
            'data' => $this->transformPermit($permit),
        ], 201);
    }

    private function transformPermit(Permit $permit): array
    {
        return [
            'id' => $permit->id,
            'type' => $permit->type,
            'permit_date' => optional($permit->permit_date)->toDateString(),
            'reason' => $permit->reason,
            'status' => $permit->status,
            'attachment' => $permit->attachment_path ? [
                'name' => $permit->attachment_original_name,
                'url' => Storage::disk('public')->url($permit->attachment_path),
                'path' => $permit->attachment_path,
            ] : null,
            'created_at' => optional($permit->created_at)->toDateTimeString(),
        ];
    }
}
