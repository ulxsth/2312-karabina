@extends('layouts.app')

@section('content')
<div class="logo">
    <h1>Recentri <span>for Spotify</span></h1>
</div>

<div class="create-playlist">
  <form method="POST">
        @csrf
        <input type="text" name="playlist-name" value=""><br>

        <select name="month">
            @for ($i = 1; $i <= 12; $i++)
              <option value="{{ $i }}">{{ $i }} month ago</option>
            @endfor
        </select>
		<br>
		<button type="submit">Create New Playlist</button>
  </form>

</div>

@endsection