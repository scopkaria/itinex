{{-- Sidebar partial --}}
<aside class="sidebar">
    <div class="sidebar-brand">Itin<span>ex</span></div>
    <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>
        <a href="{{ url('/dashboard') }}" class="{{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}">
            <span class="nav-icon">&#9632;</span> Dashboard
        </a>

        @if(auth()->user()->isSuperAdmin())
            <div class="sidebar-section">System</div>
            <a href="{{ url('/geography') }}" class="{{ ($activePage ?? '') === 'geography' ? 'active' : '' }}">
                <span class="nav-icon">&#127758;</span> Geography
            </a>
            <a href="{{ url('/companies') }}" class="{{ ($activePage ?? '') === 'companies' ? 'active' : '' }}">
                <span class="nav-icon">&#9881;</span> Companies
            </a>
            <a href="{{ url('/users') }}" class="{{ ($activePage ?? '') === 'users' ? 'active' : '' }}">
                <span class="nav-icon">&#128101;</span> Users
            </a>
        @endif

        <div class="sidebar-section">Master Data</div>
        <a href="{{ url('/destinations') }}" class="{{ ($activePage ?? '') === 'destinations' ? 'active' : '' }}">
            <span class="nav-icon">&#127961;</span> Destinations
        </a>
        <a href="{{ url('/accommodations') }}" class="{{ ($activePage ?? '') === 'accommodations' ? 'active' : '' }}">
            <span class="nav-icon">&#127976;</span> Accommodation
        </a>
        <a href="{{ url('/flight-providers') }}" class="{{ ($activePage ?? '') === 'flights' ? 'active' : '' }}">
            <span class="nav-icon">&#9992;</span> Flights
        </a>
        <a href="{{ url('/transport-providers') }}" class="{{ ($activePage ?? '') === 'transport' ? 'active' : '' }}">
            <span class="nav-icon">&#128663;</span> Transport
        </a>
        <a href="{{ url('/activities') }}" class="{{ ($activePage ?? '') === 'activities' ? 'active' : '' }}">
            <span class="nav-icon">&#127914;</span> Activities
        </a>
        <a href="{{ url('/miscellaneous') }}" class="{{ ($activePage ?? '') === 'miscellaneous' ? 'active' : '' }}">
            <span class="nav-icon">&#127873;</span> Miscellaneous
        </a>

        <div class="sidebar-section">Operations</div>
        <a href="{{ url('/itineraries') }}" class="{{ ($activePage ?? '') === 'itineraries' ? 'active' : '' }}">
            <span class="nav-icon">&#128203;</span> Itineraries
        </a>
    </nav>
</aside>
