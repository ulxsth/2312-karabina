@extends('layouts.app')

@section('head')
    @vite(['resources/css/playlist_result.css', 'resources/js/playlist_result.js'])
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
@endsection

@section('content')
<div class="logo-block">
    <div class="logo">
        <h1>Recentri <span>for Spotify</span></h1>
    </div>
</div>
<div class="results">
    {{-- 作成したプレイリスト名を入れる --}}
    <h1 class="page-header">New Playlist</h1>
    <div class="playlist-url">
        <div id="url">playlist url</div>
        <span id="copied-message">copied!</span>
        <span id="copy-playlist-url" class="material-symbols-outlined copy-icon">
            content_copy
        </span>
    </div>
    <img src="" alt="playlist-thumbnail">
</div>
<script src="{{ asset('js/playlist_result.js') }}"></script>
@endsection
