

<section class="conversation-section" style="max-width: 420px; margin: 0 auto;">
    <div class="new-conversation-container" style="margin-bottom: var(--spacing-xl); padding: var(--spacing-lg); background-color: var(--dsizzlers-gray-light); border-radius: var(--radius-md); border-left: 4px solid var(--dsizzlers-orange); box-shadow: var(--shadow-sm);">
        <h4 style="color: var(--dsizzlers-orange); margin-bottom: var(--spacing-md); font-size: 18px; font-weight: 700;">Start a New Conversation</h4>
        <form method="POST" action="{{ route('communication.start') }}">
            @csrf
            @php
                $isFranchisor = auth()->guard('admin')->check() || auth()->guard('franchisor_staff')->check();
                $isFranchisee = auth()->guard('franchisee')->check() || auth()->guard('franchisee_staff')->check();
            @endphp
            @if($isFranchisor)
                <label for="partner_id" style="font-weight: 600; color: var(--dsizzlers-black); font-size: 14px;">Select Franchisee</label><br>
                <select name="partner_id" id="partner_id" required style="width: 100%; padding: 12px; border: 2px solid var(--dsizzlers-gray); border-radius: var(--radius-sm); font-size: 14px; background-color: var(--dsizzlers-white); color: var(--dsizzlers-gray-darkest); margin-bottom: var(--spacing-md);">
                    <option value="" disabled selected>- Choose a Franchisee -</option>
                    @foreach(\App\Models\Franchisee::all() as $franchisee)
                        <option value="{{ $franchisee->franchisee_id }}">{{ $franchisee->franchisee_name }}</option>
                    @endforeach
                </select>
            @elseif($isFranchisee)
                <label for="partner_id" style="font-weight: 600; color: var(--dsizzlers-black); font-size: 14px;">Select Franchisor</label><br>
                <select name="partner_id" id="partner_id" required style="width: 100%; padding: 12px; border: 2px solid var(--dsizzlers-gray); border-radius: var(--radius-sm); font-size: 14px; background-color: var(--dsizzlers-white); color: var(--dsizzlers-gray-darkest); margin-bottom: var(--spacing-md);">
                    <option value="" disabled selected>- Choose a Franchisor -</option>
                    @foreach(\App\Models\Admin::all() as $admin)
                        <option value="{{ $admin->admin_id }}">{{ $admin->admin_fname }} {{ $admin->admin_lname }}</option>
                    @endforeach
                </select>
            @else
                <label for="partner_id" style="font-weight: 600; color: var(--dsizzlers-black); font-size: 14px;">Select Franchisee</label><br>
                <select name="partner_id" id="partner_id" required style="width: 100%; padding: 12px; border: 2px solid var(--dsizzlers-gray); border-radius: var(--radius-sm); font-size: 14px; background-color: var(--dsizzlers-white); color: var(--dsizzlers-gray-darkest); margin-bottom: var(--spacing-md);">
                    <option value="" disabled selected>- Choose a Franchisee -</option>
                    @foreach(\App\Models\Franchisee::all() as $franchisee)
                        <option value="{{ $franchisee->franchisee_id }}">{{ $franchisee->franchisee_name }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-sm);">Create</button>
        </form>
    </div>

    <div class="conversation-card" style="background: var(--dsizzlers-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); padding: var(--spacing-xl) var(--spacing-lg); border: 1.5px solid var(--dsizzlers-gray);">
        <h3 style="color: var(--dsizzlers-orange); font-size: 22px; font-weight: 700; margin-bottom: var(--spacing-lg); text-align: center; letter-spacing: 0.5px;">Conversations</h3>
        <ul class="conversations-list" style="list-style: none; padding: 0; margin: 0;">
            @forelse($conversations as $conversation)
                @php
                    $admin = $conversation->admin;
                    $franchisee = $conversation->franchisee;
                    $isCurrentUserAdmin = auth()->guard('admin')->check() || auth()->guard('franchisor_staff')->check();
                    if ($isCurrentUserAdmin) {
                        $displayName = $franchisee ? ($franchisee->franchisee_name ?: 'Franchisee') : 'Franchisee';
                    } else {
                        $adminName = $admin ? trim(($admin->admin_fname ?? '') . ' ' . ($admin->admin_lname ?? '')) ?: 'System Administrator' : 'System Administrator';
                        $displayName = $adminName;
                    }
                @endphp
                <li style="margin-bottom: var(--spacing-md);">
                    <div class="conversation-card-row" data-chat-url="{{ url('/communication/' . $conversation->id) }}" style="display: flex; align-items: center; justify-content: space-between; background: var(--dsizzlers-gray-light); border-radius: var(--radius-md); padding: var(--spacing-md) var(--spacing-lg); box-shadow: var(--shadow-sm); cursor: pointer; transition: background 0.2s, color 0.2s;">
                        <a href="{{ url('/communication/' . $conversation->id) }}" class="open-chat-link conversation-name" data-chat-url="{{ url('/communication/' . $conversation->id) }}" style="color: var(--dsizzlers-orange); font-weight: 600; font-size: 16px; letter-spacing: 0.2px; user-select: none; background: transparent; border-radius: var(--radius-md); padding: 8px 18px; transition: background 0.2s, color 0.2s; text-decoration: none; display: inline-block;">{{ $displayName }}</a>
                        <div>
                            @if(($conversationView ?? 'active') === 'archived')
                                <form method="POST" action="{{ route('communication.restore', $conversation->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-camera" style="padding: 6px 14px; font-size: 13px;" onclick="return confirm('Restore this conversation?');">Restore</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('communication.archive', $conversation->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-remove" style="padding: 6px 14px; font-size: 13px;" onclick="return confirm('Archive this conversation?');">Archive</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </li>
            @empty
                <li><p class="empty-state" style="margin: 0;">No conversations available.</p></li>
            @endforelse
        </ul>
    </div>
</section>
<style>
.conversation-card-row {
    background: var(--dsizzlers-gray-light);
    border-radius: var(--radius-md);
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    position: relative;
}
.conversation-card-row .conversation-name {
    background: transparent;
    color: var(--dsizzlers-orange);
    font-weight: 600;
    border-radius: var(--radius-md);
    padding: 8px 18px;
    transition: background 0.2s, color 0.2s;
}
.conversation-card-row:hover,
.conversation-card-row.selected {
    background: #fff !important;
    box-shadow: 0 2px 12px 0 rgba(44,62,80,0.13);
}
.conversation-card-row:hover .conversation-name,
.conversation-card-row.selected .conversation-name {
    background: #fff;
    color: var(--dsizzlers-orange);
    font-weight: 700;
    box-shadow: 0 2px 8px 0 rgba(44,62,80,0.08);
}
</style>
<script>
// Make the entire conversation card clickable and highlight it when selected
document.addEventListener('DOMContentLoaded', function() {
    var rows = document.querySelectorAll('.conversation-card-row');
    rows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            // Prevent Archive/Restore button click from triggering row click
            if (e.target.closest('form')) return;
            rows.forEach(function(r) {
                r.classList.remove('selected');
            });
            this.classList.add('selected');
            // AJAX navigation: trigger the same as clicking the old link
            var chatUrl = this.getAttribute('data-chat-url');
            if (chatUrl) {
                if (typeof openChatModal === 'function') {
                    openChatModal(chatUrl);
                } else {
                    window.location.href = chatUrl;
                }
            }
        });
    });
});
</script>


