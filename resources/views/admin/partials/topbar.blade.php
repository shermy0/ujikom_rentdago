<header class="topbar">
    <!-- Left Section -->
    <div class="topbar-left">
        <h5>{{ $title ?? 'Admin Panel' }}</h5>
    </div>

    <!-- Right Section -->
    <div class="topbar-right">
        @auth
            <!-- Profile Button -->
            <a href="{{ route('admin.profile.index') }}" class="btn-topbar btn-profile">
                <i class="bi bi-person-circle"></i>
                <span>{{ auth()->user()->name }}</span>
            </a>

            <!-- Logout Button -->
            <form action="{{ route('auth.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-topbar btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        @else
            <!-- Login Button (jika belum login) -->
            <a href="#" class="btn-topbar btn-profile">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Login</span>
            </a>
        @endauth
    </div>
</header>