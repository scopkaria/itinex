@extends('layouts.app')
@section('title', 'Login — Itinex')

@section('body')
<div class="login-wrapper">
    <div class="login-card">
        <h1>Itin<span style="color:#6366f1">ex</span></h1>
        <p class="subtitle">Tourism ERP &mdash; Sign in to your account</p>

        @if ($errors->any())
            <div class="error-msg">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@company.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="login-btn">Sign In</button>
        </form>
    </div>
</div>
@endsection
