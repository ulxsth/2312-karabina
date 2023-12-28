@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/playlist_result.css') }}">
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
    <div id="url" class="playlist-url">playlist url</div>
		<button onclick="copy()">コピーする</button>
    <img src="" alt="playlist-thumbnail">
</div>
<script src="{{ asset('js/playlist_result.js') }}"></script>

@endsection
