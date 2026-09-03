<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::with(['department', 'author'])->latest()->paginate(15);
        if ($request->wantsJson()) {
            return response()->json($announcements);
        }
        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_kh' => 'nullable|string|max:255',
            'content' => 'required|string',
            'content_kh' => 'nullable|string',
            'priority' => 'required|in:info,warning,urgent',
            'department_id' => 'nullable|exists:departments,id',
            'expires_at' => 'nullable|date',
            'send_telegram' => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'title_kh' => $request->title_kh,
            'content' => $request->content,
            'content_kh' => $request->content_kh,
            'priority' => $request->priority,
            'department_id' => $request->department_id,
            'expires_at' => $request->expires_at,
            'send_telegram' => $request->boolean('send_telegram'),
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        AuditLog::log('announcement_created', "Created announcement: {$announcement->title}");

        // Broadcast to Telegram if option checked
        if ($announcement->send_telegram) {
            try {
                $telegram = new TelegramService();
                $msg = "📢 *NTTI ANNOUNCEMENT / ដំណឹង* (" . strtoupper($announcement->priority) . ")\n\n";
                $msg .= "📌 *" . $announcement->title . "*\n";
                if ($announcement->title_kh) {
                    $msg .= "📌 *" . $announcement->title_kh . "*\n";
                }
                $msg .= "\n" . $announcement->content . "\n";
                if ($announcement->content_kh) {
                    $msg .= "\n" . $announcement->content_kh . "\n";
                }
                $telegram->broadcastMessage($msg);
            } catch (\Exception $e) {
                // Log failure gracefully
                \Illuminate\Support\Facades\Log::warning("Telegram broadcast failed: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Announcement published successfully.'),
            'announcement' => $announcement,
        ]);
    }

    public function destroy(Announcement $announcement)
    {
        AuditLog::log('announcement_deleted', "Deleted announcement: {$announcement->title}");
        $announcement->delete();
        return response()->json(['success' => true, 'message' => __('Announcement deleted successfully.')]);
    }

    public function activeAnnouncements()
    {
        $now = now();
        $announcements = Announcement::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->latest()
            ->take(5)
            ->get();

        return response()->json($announcements);
    }
}
