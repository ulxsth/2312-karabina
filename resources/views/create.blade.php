@extends('layouts.app')

@section('head')
<link rel="stylesheet" href="{{ asset('css/create.css') }}">
@endsection

@section('content')
<div class="logo">
    <h1>Recentri <span>for Spotify</span></h1>
</div>

<div class="create-playlist">
  <form method="POST">
        @csrf
        <div class="input-group">
					<input id="name" type="text" required="" autocomplete="off">
					<label for="name">#new playlist</label>
			</div>

        <select name="month">
            @for ($i = 1; $i <= 12; $i++)
              <option value="{{ $i }}">{{ $i }} month ago</option>
            @endfor
        </select>
		<br>
		<a href="#">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
				Create New Playlist
			</a>
  </form>

</div>

@endsection