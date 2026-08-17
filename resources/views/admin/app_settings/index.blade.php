<x-admin-layout>
    <x-slot name="title">App Settings</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Application Settings</h1>
            <p>Blood Groups, Religions, Divisions, Districts ও Global Settings পরিচালনা করুন</p>
        </div>
    </div>

    {{-- Tab navigation --}}
    @php $tab = request('tab', 'blood-groups'); @endphp
    <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--card-border);padding-bottom:0">
        @foreach(['blood-groups'=>'🩸 Blood Groups','religions'=>'☪️ Religions','divisions'=>'🗺️ Divisions & Districts','settings'=>'⚙️ Global Settings'] as $key => $label)
        <a href="?tab={{ $key }}"
           style="padding:9px 18px;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;text-decoration:none;
                  {{ $tab === $key ? 'background:var(--card-bg);border:1px solid var(--card-border);border-bottom:2px solid var(--card-bg);color:var(--blue);margin-bottom:-2px' : 'color:var(--text-muted)' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- ═══ BLOOD GROUPS ═══ --}}
    @if($tab === 'blood-groups')
    <div class="grid-2" style="align-items:start">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Blood Groups</span>
                <span class="badge badge-secondary no-dot">{{ $bloodGroups->count() }} items</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>#</th><th>Name</th><th style="text-align:right">Action</th></tr></thead>
                    <tbody>
                        @forelse($bloodGroups as $bg)
                        <tr>
                            <td style="color:var(--text-muted);font-size:12px">{{ $loop->iteration }}</td>
                            <td><strong>{{ $bg->name }}</strong></td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('admin.app-settings.blood-groups.destroy', $bg) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-sm text-red">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">No blood groups yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Add Blood Group</span></div>
            <div style="padding:20px">
                <form method="POST" action="{{ route('admin.app-settings.blood-groups.store') }}?tab=blood-groups">
                    @csrf
                    <div class="form-group">
                        <label>Blood Group Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. A+, B-, O+" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Blood Group</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ RELIGIONS ═══ --}}
    @if($tab === 'religions')
    <div class="grid-2" style="align-items:start">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Religions</span>
                <span class="badge badge-secondary no-dot">{{ $religions->count() }} items</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>#</th><th>Name</th><th style="text-align:right">Action</th></tr></thead>
                    <tbody>
                        @forelse($religions as $rel)
                        <tr>
                            <td style="color:var(--text-muted);font-size:12px">{{ $loop->iteration }}</td>
                            <td><strong>{{ $rel->name }}</strong></td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('admin.app-settings.religions.destroy', $rel) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-sm text-red">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">No religions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Add Religion</span></div>
            <div style="padding:20px">
                <form method="POST" action="{{ route('admin.app-settings.religions.store') }}?tab=religions">
                    @csrf
                    <div class="form-group">
                        <label>Religion Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Islam, Hinduism" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Religion</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ DIVISIONS & DISTRICTS ═══ --}}
    @if($tab === 'divisions')
    <div class="grid-2" style="align-items:start">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Divisions & Districts</span>
                <span class="badge badge-secondary no-dot">{{ $divisions->count() }} Divisions</span>
            </div>
            <div style="padding:0">
                @foreach($divisions as $div)
                <div style="border-bottom:1px solid var(--card-border)">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 18px;background:rgba(59,130,246,.04)">
                        <strong style="font-size:13px">{{ $div->name }}</strong>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span class="badge badge-secondary no-dot">{{ $div->districts->count() }} Districts</span>
                            <form method="POST" action="{{ route('admin.app-settings.divisions.destroy', $div) }}?tab=divisions" onsubmit="return confirm('Delete division and all its districts?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-ghost btn-sm text-red">&times;</button>
                            </form>
                        </div>
                    </div>
                    @foreach($div->districts as $dist)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 28px;font-size:13px">
                        <span>{{ $dist->name }}</span>
                        <form method="POST" action="{{ route('admin.app-settings.districts.destroy', $dist) }}?tab=divisions" onsubmit="return confirm('Delete district?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost btn-sm text-red" style="font-size:10px">&times;</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        <div>
            <div class="card" style="margin-bottom:16px">
                <div class="card-header"><span class="card-title">Add Division</span></div>
                <div style="padding:20px">
                    <form method="POST" action="{{ route('admin.app-settings.divisions.store') }}?tab=divisions">
                        @csrf
                        <div class="form-group">
                            <label>Division Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. ঢাকা" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Division</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><span class="card-title">Add District</span></div>
                <div style="padding:20px">
                    <form method="POST" action="{{ route('admin.app-settings.districts.store') }}?tab=divisions">
                        @csrf
                        <div class="form-group">
                            <label>Division <span class="required">*</span></label>
                            <select name="division_id" class="form-control" required>
                                <option value="">-- Select Division --</option>
                                @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>District Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. ঢাকা" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add District</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ GLOBAL SETTINGS ═══ --}}
    @if($tab === 'settings')
    <div class="card">
        <div class="card-header">
            <span class="card-title">Global Settings</span>
        </div>
        <div style="padding:24px">
            <form method="POST" action="{{ route('admin.app-settings.update') }}?tab=settings">
                @csrf @method('PUT')
                @foreach($settings as $group => $groupSettings)
                <div class="section-divider" style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--card-border);padding-bottom:6px;margin-bottom:14px">
                    {{ ucfirst($group) }}
                </div>
                <div style="margin-bottom:20px">
                    @foreach($groupSettings as $s)
                    <div class="form-group">
                        <label>{{ $s->label }}</label>
                        @if($s->type === 'boolean')
                            <label class="form-check" style="margin-top:4px">
                                <input type="checkbox" name="{{ $s->key }}" value="1" {{ $s->value === '1' ? 'checked' : '' }}>
                                Enabled
                            </label>
                        @elseif($s->type === 'textarea')
                            <textarea name="{{ $s->key }}" class="form-control" rows="4">{{ $s->value }}</textarea>
                        @else
                            <input type="text" name="{{ $s->key }}" class="form-control" value="{{ $s->value }}">
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
                <button type="submit" class="btn btn-primary">Save All Settings</button>
            </form>
        </div>
    </div>
    @endif

</x-admin-layout>
