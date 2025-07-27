<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of banners
     * GET /api/banners
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Banner::query();

            // Filter by active status if requested
            if ($request->has('active_only') && $request->active_only) {
                $query->active();
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply ordering
            $banners = $query->ordered()->get();

            // Transform data for response
            $banners = $banners->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'icon' => $banner->icon,
                    'icon_class' => $banner->icon_class,
                    'image' => $banner->image,
                    'image_url' => $banner->image_url,
                    'background_color' => $banner->background_color,
                    'text_color' => $banner->text_color,
                    'is_active' => $banner->is_active,
                    'status_label' => $banner->status_label,
                    'status_color' => $banner->status_color,
                    'sort_order' => $banner->sort_order,
                    'created_at' => $banner->created_at,
                    'updated_at' => $banner->updated_at,
                ];
            });

            return $this->successResponse(
                $banners,
                'Banners retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve banners: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Store a newly created banner
     * POST /api/banners
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_color' => 'nullable|string|max:7', // Hex color code
            'text_color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('banners', 'public');
                $data['image'] = $imagePath;
            }

            // Set defaults
            $data['background_color'] = $data['background_color'] ?? '#ff9500';
            $data['text_color'] = $data['text_color'] ?? '#ffffff';
            $data['is_active'] = $data['is_active'] ?? true;
            $data['sort_order'] = $data['sort_order'] ?? Banner::max('sort_order') + 1;

            $banner = Banner::create($data);

            return $this->createdResponse(
                [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'icon' => $banner->icon,
                    'icon_class' => $banner->icon_class,
                    'image' => $banner->image,
                    'image_url' => $banner->image_url,
                    'background_color' => $banner->background_color,
                    'text_color' => $banner->text_color,
                    'is_active' => $banner->is_active,
                    'sort_order' => $banner->sort_order,
                    'created_at' => $banner->created_at,
                    'updated_at' => $banner->updated_at,
                ],
                'Banner created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create banner: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Display the specified banner
     * GET /api/banners/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);

            return $this->successResponse([
                'id' => $banner->id,
                'title' => $banner->title,
                'description' => $banner->description,
                'icon' => $banner->icon,
                'icon_class' => $banner->icon_class,
                'image' => $banner->image,
                'image_url' => $banner->image_url,
                'background_color' => $banner->background_color,
                'text_color' => $banner->text_color,
                'is_active' => $banner->is_active,
                'status_label' => $banner->status_label,
                'status_color' => $banner->status_color,
                'sort_order' => $banner->sort_order,
                'created_at' => $banner->created_at,
                'updated_at' => $banner->updated_at,
            ], 'Banner retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Banner not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve banner: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Update the specified banner
     * PUT /api/banners/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $banner = Banner::findOrFail($id);
            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                    Storage::disk('public')->delete($banner->image);
                }

                $image = $request->file('image');
                $imagePath = $image->store('banners', 'public');
                $data['image'] = $imagePath;
            }

            $banner->update($data);

            return $this->updatedResponse([
                'id' => $banner->id,
                'title' => $banner->title,
                'description' => $banner->description,
                'icon' => $banner->icon,
                'icon_class' => $banner->icon_class,
                'image' => $banner->image,
                'image_url' => $banner->image_url,
                'background_color' => $banner->background_color,
                'text_color' => $banner->text_color,
                'is_active' => $banner->is_active,
                'sort_order' => $banner->sort_order,
                'created_at' => $banner->created_at,
                'updated_at' => $banner->updated_at,
            ], 'Banner updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Banner not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update banner: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Remove the specified banner
     * DELETE /api/banners/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);

            // Delete associated image if exists
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }

            $banner->delete();

            return $this->deletedResponse('Banner deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Banner not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete banner: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Get active banners for frontend display
     * GET /api/banners/active
     */
    public function getActiveBanners(): JsonResponse
    {
        try {
            $banners = Banner::active()->ordered()->get();

            $banners = $banners->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'icon' => $banner->icon,
                    'icon_class' => $banner->icon_class,
                    'image_url' => $banner->image_url,
                    'background_color' => $banner->background_color,
                    'text_color' => $banner->text_color,
                    'sort_order' => $banner->sort_order,
                ];
            });

            return $this->successResponse(
                $banners,
                'Active banners retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve active banners: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Toggle banner active status
     * POST /api/banners/{id}/toggle
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->update(['is_active' => !$banner->is_active]);

            return $this->successResponse([
                'id' => $banner->id,
                'is_active' => $banner->is_active,
                'status_label' => $banner->status_label,
            ], 'Banner status updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Banner not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to toggle banner status: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Update banner sort order
     * POST /api/banners/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:banners,id',
            'banners.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            foreach ($request->banners as $bannerData) {
                Banner::where('id', $bannerData['id'])
                    ->update(['sort_order' => $bannerData['sort_order']]);
            }

            return $this->successResponse(
                null,
                'Banner order updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to reorder banners: ' . $e->getMessage(),
                null,
                500
            );
        }
    }
}
