<x-admin-layout>
    <x-slot name="title">Notification Broadcast History</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>📡 Notification Broadcast Center</h1>
            <p>Send and manage Push Notifications &amp; Email broadcasts to students and faculty</p>
        </div>
        <div>
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-lg">
                📣 Send New Notification
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <span class="card-title">Sent Notifications History</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Title &amp; Message</th>
                            <th>Channel</th>
                            <th>Target Audience</th>
                            <th>Recipients</th>
                            <th>Sent By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $n)
                            <tr>
                                <td>
                                    <div style="font-weight:600">{{ $n->created_at->format('M d, Y') }}</div>
                                    <div style="font-size:12px;color:#64748b">{{ $n->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:700;color:#1e293b;margin-bottom:3px">{{ $n->title }}</div>
                                    <div style="font-size:13px;color:#475569;max-width:400px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        {{ Str::limit(strip_tags($n->message), 80) }}
                                    </div>
                                    @if($n->image_url)
                                        <div style="margin-top:4px">
                                            <a href="{{ $n->image_url }}" target="_blank" style="font-size:11px;color:#2563eb;font-weight:600">🖼 View Image Banner</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($n->channel === 'BOTH')
                                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">
                                            🔥 PUSH + 📧 EMAIL
                                        </span>
                                    @elseif($n->channel === 'PUSH')
                                        <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a">
                                            🔥 PUSH ONLY
                                        </span>
                                    @else
                                        <span class="badge" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                                            📧 EMAIL ONLY
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9;color:#334155">
                                        {{ str_replace('_', ' ', $n->recipient_type) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:700;color:#0f172a">{{ $n->sent_count }}</div>
                                    <div style="font-size:11px;color:#64748b">recipients</div>
                                </td>
                                <td>
                                    <div style="font-size:13px;font-weight:600">{{ $n->sender->name ?? 'System' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:40px;color:#94a3b8">
                                    No broadcast notifications sent yet. Click "Send New Notification" above to dispatch your first message!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notifications->hasPages())
                <div style="padding:16px">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
