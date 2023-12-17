@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/signin.css') }}">
@endsection

@section('content')
<div class="logo">
    <h1>Recentri <span>for Spotify</span></h1>
</div>
<form method="POST" action={{ route('auth/spotify') }}>
    @csrf
    <button>
        <h1 class="signin-heading">Sign in</h1>
    </button>
</form>
@endsection
