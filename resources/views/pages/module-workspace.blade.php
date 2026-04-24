@extends('layouts.app')
@section('title', $module . ' - ' . ucfirst($section) . ' - Itinex')

@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => strtolower($module)])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">{{ $module }} Module Workspace</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>

        <div class="content-area">
            <div class="card" style="padding:20px; margin-bottom:16px;">
                <a href="{{ url($managePath) }}" class="btn btn-sm btn-secondary" style="margin-bottom:12px;">Open Full Manager</a>
                <h3 style="margin:0 0 4px 0;">{{ $entityName }}</h3>
                <p style="color:var(--text-secondary);margin:0;">Separated pages keep content, structure, pricing, policy, and setting operations isolated by route-level guard.</p>
            </div>

            <div class="tab-nav" style="margin-bottom:16px;">
                @foreach($sections as $item)
                    <a href="{{ url($basePath . '/' . $item) }}" class="{{ $item === $section ? 'active' : '' }}" style="text-decoration:none;">{{ ucfirst($item) }}</a>
                @endforeach
            </div>

            <div class="card" style="padding:20px;">
                @if($section === 'content')
                    <h4>Content</h4>
                    <p>Manage media, descriptions, and inventory metadata in this section.</p>
                @elseif($section === 'structure')
                    <h4>Structure</h4>
                    <p>Manage structural entities like routes, room or vehicle structure, and seasonal scaffolding.</p>
                @elseif($section === 'pricing')
                    <h4>Pricing</h4>
                    <p>All pricing updates flow through the centralized pricing engine and write audit/version logs.</p>
                @elseif($section === 'policies')
                    <h4>Policies</h4>
                    <p>Provider-scoped payment and cancellation policies are served via unified policy adapters.</p>
                    <p style="margin-top:8px;">Policy API endpoint: <strong>/api/pricing-policies/{{ strtolower($module) === 'accommodation' ? 'accommodation' : strtolower($module) }}/{{ $entityId }}</strong></p>
                @else
                    <h4>Settings</h4>
                    <p>Module settings and activation controls are isolated from pricing to reduce accidental changes.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
