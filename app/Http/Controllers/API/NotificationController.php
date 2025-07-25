<?php
// app/Http/Controllers/API/NotificationController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by read status
        if ($request->has('unread_only') && $request->unread_only) {
            $query->unread();
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan',
                ],
                404,
            );
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah dibaca',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request
            ->user()
            ->notifications()
            ->unread()
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah dibaca',
        ]);
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->notifications()->unread()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan',
                ],
                404,
            );
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah dihapus',
        ]);
    }
}
