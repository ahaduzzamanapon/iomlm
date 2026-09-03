<x-support-layout>
    <x-slot name="title">My Custom Quick Replies</x-slot>

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div>
            <h1 style="font-size:20px;font-weight:700">My Custom Quick Replies (কাস্টম মেসেজ)</h1>
            <p style="font-size:13px;color:#64748b">তৈরি করে রাখুন আপনার প্রতিদিনের প্রয়োজনীয় রেসপন্স বার্তাগুলো</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="openModal('addCannedModal')">+ New Quick Reply</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">My Saved Quick Replies ({{ $cannedMessages->count() }})</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:200px">Title / Shortcode</th>
                        <th>Message Content</th>
                        <th style="text-align:right;width:150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cannedMessages as $cm)
                    <tr>
                        <td>
                            <strong style="color:#0284c7">{{ $cm->title }}</strong>
                        </td>
                        <td>
                            <div style="font-size:13px;color:#334155;white-space:pre-line">{{ $cm->message }}</div>
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-outline btn-sm" onclick='openEditCannedModal(@json($cm))'>Edit</button>
                            <form method="POST" action="{{ route('support.canned-messages.destroy', $cm) }}" style="display:inline" onsubmit="return confirm('Delete this quick reply?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:30px;color:#94a3b8">
                            আপনার কোনো কাস্টম কুইক মেসেজ সংরক্ষিত নেই। "New Quick Reply" এ ক্লিক করে মেসেজ যোগ করুন।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Modal --}}
    <div class="modal-overlay" id="addCannedModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Quick Reply</span>
                <button class="modal-close" onclick="closeModal('addCannedModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('support.canned-messages.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title / Short Description <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. সালাম ও শুভেচ্ছা" required>
                    </div>
                    <div class="form-group">
                        <label>Message Body <span class="required">*</span></label>
                        <textarea name="message" class="form-control" rows="5" placeholder="আসসালামু আলাইকুম, আইওএম অনলাইন সাপোর্টে আপনাকে স্বাগতম। কীভাবে সাহায্য করতে পারি?" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addCannedModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Quick Reply</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal-overlay" id="editCannedModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Quick Reply</span>
                <button class="modal-close" onclick="closeModal('editCannedModal')">&times;</button>
            </div>
            <form method="POST" id="editCannedForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title / Short Description <span class="required">*</span></label>
                        <input type="text" name="title" id="ec_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Message Body <span class="required">*</span></label>
                        <textarea name="message" id="ec_message" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editCannedModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Quick Reply</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditCannedModal(cm) {
        document.getElementById('editCannedForm').action = '/support/canned-messages/' + cm.id;
        document.getElementById('ec_title').value = cm.title;
        document.getElementById('ec_message').value = cm.message;
        openModal('editCannedModal');
    }
    </script>
    @endpush
</x-support-layout>
