<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activator;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivatorController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of active activators
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activator::active()->orderBy('name');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $activators = $query->get();

        return $this->successResponse($activators, 'Activators retrieved successfully');
    }

    /**
     * Display the specified activator
     */
    public function show($id): JsonResponse
    {
        $activator = Activator::find($id);

        if (!$activator) {
            return $this->errorResponse('Activator not found', null, 404);
        }

        return $this->successResponse($activator, 'Activator retrieved successfully');
    }
}