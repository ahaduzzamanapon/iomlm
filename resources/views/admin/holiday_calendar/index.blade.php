<x-admin-layout>
    <x-slot name="title">Holiday Calendar</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Holiday Calendar</h1>
            <p>Manage institute vacations automatically skipped during timeline generation</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addHolidayModal')">
                New Holiday
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Holiday Occasion</th>
                        <th>Yearly Recurring</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $hol)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ \Carbon\Carbon::parse($hol->date)->format('d M Y (l)') }}</strong>
                        </td>
                        <td>{{ $hol->name }}</td>
                        <td>
                            @if($hol->is_recurring_yearly)
                                <span class="badge badge-active">Yes (Every Year)</span>
                            @else
                                <span class="badge badge-secondary">One-time</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('admin.holiday-calendar.destroy', $hol) }}" style="display:inline" onsubmit="return confirm('Delete holiday?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">No holidays scheduled in academic calendar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Holiday Modal -->
    <div class="modal-overlay" id="addHolidayModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Academic Holiday</span>
                <button class="modal-close" onclick="closeModal('addHolidayModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.holiday-calendar.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Holiday Date <span class="required">*</span></label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <input type="hidden" name="scope" value="GLOBAL">

                    <div class="form-group">
                        <label>Holiday Name / Occasion <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Eid-ul-Fitr Vacation" required>
                    </div>

                    <label class="form-check" style="margin-top:10px">
                        <input type="checkbox" name="is_recurring_yearly" value="1" checked> Recurring every year on same date
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addHolidayModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
