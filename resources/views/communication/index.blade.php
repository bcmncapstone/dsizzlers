<h2>📩 Communication Management</h2>

<hr>

<section>
    <h3>💬 Conversations</h3>

    <div style="margin-bottom: 20px;">
        <h4>➕ Start a New Conversation</h4>

        <form method="POST" action="{{ route('communication.start') }}">
            @csrf

            @php
                $isFranchisor = auth()->guard('admin')->check() || auth()->guard('franchisor_staff')->check();
                $isFranchisee = auth()->guard('franchisee')->check() || auth()->guard('franchisee_staff')->check();
            @endphp

            @if($isFranchisor)
    <label for="partner_id">Select Franchisee</label><br>
    <select name="partner_id" id="partner_id" required>
        <option value="" disabled selected>— Choose a Franchisee —</option>
        @foreach(\App\Models\Franchisee::all() as $franchisee)
            <option value="{{ $franchisee->franchisee_id }}">{{ $franchisee->franchisee_name }}</option>
        @endforeach
    </select>
@elseif($isFranchisee)
    <label for="partner_id">Select Franchisor</label><br>
    <select name="partner_id" id="partner_id" required>
        <option value="" disabled selected>— Choose a Franchisor —</option>
        @foreach(\App\Models\Admin::all() as $admin)
            <option value="{{ $admin->admin_id }}">{{ $admin->admin_fname }} {{ $admin->admin_lname }} (Admin)</option>
        @endforeach
        @foreach(\App\Models\FranchisorStaff::all() as $astaff)
            <option value="{{ $astaff->astaff_id }}">{{ $astaff->astaff_fname }} {{ $astaff->astaff_lname }} (Staff)</option>
        @endforeach
    </select>
@else
    {{-- fallback to franchisee list if no guard detected --}}
    <label for="partner_id">Select Franchisee</label><br>
    <select name="partner_id" id="partner_id" required>
        <option value="" disabled selected>— Choose a Franchisee —</option>
        @foreach(\App\Models\Franchisee::all() as $franchisee)
            <option value="{{ $franchisee->franchisee_id }}">{{ $franchisee->franchisee_name }}</option>
        @endforeach
    </select>
@endif

            <br><br>
            <button type="submit">Start Conversation</button>
        </form>
    </div>

    <hr>

    <h4>📨 Existing Conversations</h4>

    <ul>
        @forelse($conversations as $conversation)
            <li>
                <a href="{{ url('/communication/' . $conversation->id) }}">
                    Conversation #{{ $conversation->id }}
                </a>
            </li>
        @empty
            <p><em>No conversations available.</em></p>
        @endforelse
    </ul>
</section>

<hr>

<section>
    <h3>📢 Digital Marketing Posts</h3>

    @forelse($digitalMarketing as $post)
        <div style="margin-bottom: 20px;">
            <img 
                src="{{ Storage::url($post->image_path) }}" 
                alt="Marketing Image"
                width="150"
                style="display:block; margin-bottom:10px;"
            >
            <p>{{ $post->description }}</p>
        </div>
    @empty
        <p><em>No digital marketing posts available.</em></p>
    @endforelse
</section>
