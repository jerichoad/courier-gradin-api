<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCourierRequest;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;

class CourierController extends Controller
{
    private const SORTABLE = [
        'courier_id',
        'courier_code',
        'courier_name',
        'courier_level',
        'created_at',
        'updated_at',
    ];

    public function index(IndexCourierRequest $request): JsonResponse
    {
        [$column, $direction] = $this->resolveSort($request->query('sort'));

        $couriers = Courier::query()
            ->when($request->filled('search'), fn ($query) => $query->search((string) $request->query('search')))
            ->when($request->filled('level'), fn ($query) => $query->levelIn((string) $request->query('level')))
            ->orderBy($column, $direction)
            ->paginate($request->perPage())
            ->withQueryString()
            ->through(fn (Courier $courier) => new CourierResource($courier));

        return response()->json([
            'success' => true,
            'message' => 'Couriers retrieved successfully.',
            'data' => $couriers,
        ]);
    }

    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Courier created successfully.',
            'data' => new CourierResource($courier),
        ], 201);
    }

    public function show(string $courierId): JsonResponse
    {
        $courier = Courier::find($courierId);

        if ($courier === null) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Courier retrieved successfully.',
            'data' => new CourierResource($courier),
        ]);
    }

    public function update(UpdateCourierRequest $request, string $courierId): JsonResponse
    {
        $courier = Courier::find($courierId);

        if ($courier === null) {
            return $this->notFound();
        }

        $courier->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Courier updated successfully.',
            'data' => new CourierResource($courier->fresh()),
        ]);
    }

    public function destroy(string $courierId): JsonResponse
    {
        $courier = Courier::find($courierId);

        if ($courier === null) {
            return $this->notFound();
        }

        $courier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Courier deleted successfully.',
            'data' => null,
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveSort(?string $sort): array
    {
        if ($sort === null || trim($sort) === '') {
            return ['courier_name', 'asc'];
        }

        $sort = trim($sort);
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-+');

        if (! in_array($column, self::SORTABLE, true)) {
            return ['courier_name', 'asc'];
        }

        return [$column, $direction];
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Courier not found.',
        ], 404);
    }
}
