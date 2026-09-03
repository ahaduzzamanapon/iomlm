<x-admin-layout>
    <x-slot name="title">Artisan Command Console</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Artisan Command Runner</h1>
            <p>Hidden Console & Preset Command Manager (Restricted to Super Admin)</p>
        </div>
    </div>

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid-2" style="grid-template-columns: 360px 1fr; gap: 20px; align-items: start;">

        {{-- Left: Preset Commands & Quick Fill --}}
        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-header" style="background:#0f172a;color:#fff;border-radius:12px 12px 0 0">
                    <span class="card-title">Preset Commands</span>
                </div>
                <div class="card-body">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
                        ১-ক্লিকে চালাতে যেকোনো প্রিসেট কমাণ্ডে প্রেস করুন:
                    </div>

                    <div style="display:flex;flex-direction:column;gap:8px">
                        @foreach($presets as $cmd => $label)
                            <form method="POST" action="{{ route('admin.command.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="{{ $cmd }}">
                                <button type="submit" class="btn btn-outline btn-sm" style="width:100%;text-align:left;justify-content:flex-start;font-size:12px;gap:8px" onclick="return confirm('php artisan {{ $cmd }} চালাতে চান?')">
                                    <span style="color:#0284c7">▶</span>
                                    <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        <strong>{{ $cmd }}</strong>
                                        <div style="font-size:10px;color:var(--text-muted)">{{ $label }}</div>
                                    </div>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Custom Command Input & Terminal Output --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Command Input Box --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title">⌨️ Execute Custom Command</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.command.run') }}" style="display:flex;gap:12px;align-items:center">
                        @csrf
                        <div style="font-family:monospace;font-weight:700;color:#0284c7;font-size:14px">php artisan</div>
                        <input type="text" name="command" id="customCommandInput" class="form-control"
                               placeholder="e.g. migrate, view:clear, db:seed --class=SupportDepartmentSeeder"
                               value="{{ old('command', session('command_executed') ? str_replace('php artisan ', '', session('command_executed')) : '') }}"
                               style="font-family:monospace;font-size:14px;flex:1" required autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            Run Command
                        </button>
                    </form>
                </div>
            </div>

            {{-- Dark Console Output Window --}}
            <div class="card" style="background:#020617;border:1px solid #1e293b;border-radius:12px;overflow:hidden">
                <div style="background:#0f172a;padding:12px 16px;border-bottom:1px solid #1e293b;display:flex;justify-content:space-between;align-items:center">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block"></span>
                        <span style="color:#94a3b8;font-family:monospace;font-size:12px;margin-left:8px">Console Output</span>
                    </div>

                    @if(session('command_executed'))
                        <div style="font-size:11px;font-family:monospace;color:#38bdf8">
                            Executed: <strong>{{ session('command_executed') }}</strong>
                            ({{ session('execution_time') }} ms &middot; Exit Code: {{ session('exit_code') }})
                        </div>
                    @endif
                </div>

                <div style="padding:20px;font-family:'Courier New', Courier, monospace;font-size:13px;line-height:1.6;color:#22c55e;min-height:280px;max-height:500px;overflow-y:auto;white-space:pre-wrap;word-break:break-all">
@if(session('command_output'))
{{ session('command_output') }}
@else
$ Ready. Select a preset command on the left or type a custom artisan command above to execute.
@endif
                </div>
            </div>

        </div>

    </div>
</x-admin-layout>
