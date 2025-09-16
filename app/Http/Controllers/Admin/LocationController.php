<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.locations.index');
    }

    /**
     * Get locations data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $locations = Location::select(['id', 'name', 'description', 'city', 'province', 'is_active', 'created_at', 'updated_at']);

            return DataTables::of($locations)
                ->addIndexColumn()
                ->addColumn('status', function ($location) {
                    $statusClass = $location->is_active ? 'success' : 'danger';
                    $statusText = $location->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('description_short', function ($location) {
                    return $location->description ? \Str::limit($location->description, 50) : '-';
                })
                ->addColumn('location_info', function ($location) {
                    $parts = array_filter([$location->city, $location->province]);
                    return !empty($parts) ? implode(', ', $parts) : '-';
                })
                ->addColumn('created_at_formatted', function ($location) {
                    return $location->created_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($location) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewLocation(' . $location->id . ')" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editLocation(' . $location->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteLocation(' . $location->id . ')" title="Delete">
                            <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load locations: ' . $e->getMessage()
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
                'name' => 'required|string|max:255|unique:locations,name',
                'description' => 'nullable|string|max:1000',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'is_active' => 'required|boolean'
            ], [
                'name.required' => 'Nama lokasi wajib diisi',
                'name.unique' => 'Nama lokasi sudah ada',
                'name.max' => 'Nama lokasi maksimal 255 karakter',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'address.max' => 'Alamat maksimal 500 karakter',
                'city.max' => 'Kota maksimal 100 karakter',
                'province.max' => 'Provinsi maksimal 100 karakter',
                'postal_code.max' => 'Kode pos maksimal 10 karakter',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180',
                'is_active.required' => 'Status wajib dipilih'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $location = Location::create([
                'name' => $request->name,
                'description' => $request->description,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_active' => $request->is_active
            ]);

            Log::info('Location created successfully', [
                'location_id' => $location->id,
                'name' => $location->name,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lokasi berhasil dibuat',
                'data' => $location
            ], 201);
        } catch (\Exception $e) {
            Log::error('Location creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat lokasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $location = Location::find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $location
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
            $location = Location::find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:locations,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'is_active' => 'required|boolean'
            ], [
                'name.required' => 'Nama lokasi wajib diisi',
                'name.unique' => 'Nama lokasi sudah ada',
                'name.max' => 'Nama lokasi maksimal 255 karakter',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'address.max' => 'Alamat maksimal 500 karakter',
                'city.max' => 'Kota maksimal 100 karakter',
                'province.max' => 'Provinsi maksimal 100 karakter',
                'postal_code.max' => 'Kode pos maksimal 10 karakter',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180',
                'is_active.required' => 'Status wajib dipilih'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $location->update([
                'name' => $request->name,
                'description' => $request->description,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_active' => $request->is_active
            ]);

            Log::info('Location updated successfully', [
                'location_id' => $location->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lokasi berhasil diperbarui',
                'data' => $location
            ]);
        } catch (\Exception $e) {
            Log::error('Location update failed', [
                'location_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui lokasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $location = Location::find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi tidak ditemukan'
                ], 404);
            }

            // Note: Currently reports and observations tables don't have location_id column
            // This check can be enabled when location_id is added to those tables
            //
            // Check if location is being used in reports or observations
            // $reportsCount = \DB::table('reports')->where('location_id', $id)->count();
            // $observationsCount = \DB::table('observations')->where('location_id', $id)->count();
            //
            // if ($reportsCount > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Lokasi tidak dapat dihapus karena masih digunakan dalam ' . $reportsCount . ' laporan'
            //     ], 400);
            // }
            //
            // if ($observationsCount > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Lokasi tidak dapat dihapus karena masih digunakan dalam ' . $observationsCount . ' observasi'
            //     ], 400);
            // }

            $locationName = $location->name;
            $location->delete();

            Log::info('Location deleted successfully', [
                'location_id' => $id,
                'location_name' => $locationName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lokasi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Location deletion failed', [
                'location_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus lokasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle location status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $location = Location::find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi tidak ditemukan'
                ], 404);
            }

            $location->update(['is_active' => !$location->is_active]);

            $statusText = $location->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('Location status toggled', [
                'location_id' => $location->id,
                'new_status' => $location->is_active,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status lokasi berhasil ' . $statusText,
                'data' => [
                    'id' => $location->id,
                    'is_active' => $location->is_active
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
     * Get active locations for dropdowns
     */
    public function getActiveLocations(): JsonResponse
    {
        try {
            $locations = Location::active()
                ->select(['id', 'name', 'city', 'province'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get master data for dropdowns
     */
    public function getMasterData(): \Illuminate\Http\JsonResponse
    {
        try {
            $locations = Location::active()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}