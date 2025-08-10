<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.banners.index');
    }

    /**
     * Get banners data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $banners = Banner::select(['id', 'title', 'description', 'icon', 'image', 'background_color', 'text_color', 'is_active', 'sort_order', 'created_at', 'updated_at']);

            return DataTables::of($banners)
                ->addIndexColumn()
                ->addColumn('status', function ($banner) {
                    $statusClass = $banner->is_active ? 'success' : 'danger';
                    $statusText = $banner->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('description_short', function ($banner) {
                    return $banner->description ? \Str::limit($banner->description, 50) : '-';
                })
                ->addColumn('preview', function ($banner) {
                    $bgColor = $banner->background_color ?? '#ff9500';
                    $textColor = $banner->text_color ?? '#ffffff';
                    $icon = $banner->icon ? '<i class="' . $banner->icon_class . ' me-2"></i>' : '';

                    return '<div class="banner-preview p-2 rounded" style="background-color: ' . $bgColor . '; color: ' . $textColor . '; min-height: 40px; display: flex; align-items: center;">' .
                        $icon . '<span class="fw-bold">' . \Str::limit($banner->title, 20) . '</span></div>';
                })
                ->addColumn('image_preview', function ($banner) {
                    if ($banner->image) {
                        return '<img src="' . $banner->image_url . '" alt="Banner Image" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">';
                    }
                    return '<span class="text-muted">No Image</span>';
                })
                ->addColumn('created_at_formatted', function ($banner) {
                    return $banner->created_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($banner) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewBanner(' . $banner->id . ')" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editBanner(' . $banner->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-' . ($banner->is_active ? 'secondary' : 'success') . '" onclick="toggleBannerStatus(' . $banner->id . ')" title="' . ($banner->is_active ? 'Deactivate' : 'Activate') . '">
                                <i class="ri-' . ($banner->is_active ? 'pause' : 'play') . '-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteBanner(' . $banner->id . ')" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'preview', 'image_preview', 'action'])
                ->orderColumn('sort_order', function ($query, $order) {
                    $query->orderBy('sort_order', $order);
                })
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'icon' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'background_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'text_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'is_active' => 'required|boolean',
                'sort_order' => 'nullable|integer|min:0'
            ], [
                'title.required' => 'Judul banner wajib diisi',
                'title.max' => 'Judul banner maksimal 255 karakter',
                'description.required' => 'Deskripsi banner wajib diisi',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'icon.max' => 'Icon maksimal 100 karakter',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
                'image.max' => 'Ukuran gambar maksimal 2MB',
                'background_color.regex' => 'Format warna latar belakang harus hex (#000000)',
                'text_color.regex' => 'Format warna teks harus hex (#000000)',
                'is_active.required' => 'Status wajib dipilih',
                'sort_order.min' => 'Urutan tidak boleh negatif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

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
            $data['sort_order'] = $data['sort_order'] ?? (Banner::max('sort_order') + 1);

            $banner = Banner::create($data);

            Log::info('Banner created successfully', [
                'banner_id' => $banner->id,
                'title' => $banner->title,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil dibuat',
                'data' => $banner
            ], 201);
        } catch (\Exception $e) {
            Log::error('Banner creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
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
                    'sort_order' => $banner->sort_order,
                    'created_at' => $banner->created_at,
                    'updated_at' => $banner->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'icon' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'background_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'text_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'is_active' => 'required|boolean',
                'sort_order' => 'nullable|integer|min:0'
            ], [
                'title.required' => 'Judul banner wajib diisi',
                'title.max' => 'Judul banner maksimal 255 karakter',
                'description.required' => 'Deskripsi banner wajib diisi',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'icon.max' => 'Icon maksimal 100 karakter',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
                'image.max' => 'Ukuran gambar maksimal 2MB',
                'background_color.regex' => 'Format warna latar belakang harus hex (#000000)',
                'text_color.regex' => 'Format warna teks harus hex (#000000)',
                'is_active.required' => 'Status wajib dipilih',
                'sort_order.min' => 'Urutan tidak boleh negatif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

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

            Log::info('Banner updated successfully', [
                'banner_id' => $banner->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil diperbarui',
                'data' => $banner
            ]);
        } catch (\Exception $e) {
            Log::error('Banner update failed', [
                'banner_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner tidak ditemukan'
                ], 404);
            }

            $bannerTitle = $banner->title;

            // Delete associated image if exists
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }

            $banner->delete();

            Log::info('Banner deleted successfully', [
                'banner_id' => $id,
                'banner_title' => $bannerTitle,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Banner deletion failed', [
                'banner_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle banner status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner tidak ditemukan'
                ], 404);
            }

            $banner->update(['is_active' => !$banner->is_active]);

            $statusText = $banner->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('Banner status toggled', [
                'banner_id' => $banner->id,
                'new_status' => $banner->is_active,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status banner berhasil ' . $statusText,
                'data' => [
                    'id' => $banner->id,
                    'is_active' => $banner->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update banner order
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'banners' => 'required|array',
                'banners.*.id' => 'required|exists:banners,id',
                'banners.*.sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            foreach ($request->banners as $bannerData) {
                Banner::where('id', $bannerData['id'])
                    ->update(['sort_order' => $bannerData['sort_order']]);
            }

            Log::info('Banner order updated', [
                'updated_by' => auth()->id(),
                'banners_count' => count($request->banners)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Urutan banner berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
