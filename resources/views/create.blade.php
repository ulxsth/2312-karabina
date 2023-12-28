@extends('layouts.app')
@vite(['public/scss/app.scss','resources/js/app.js'])

@section('head')
<link rel="stylesheet" href="{{ asset('css/create.css') }}">
<link rel="stylesheet" href="{{ asset('scss/app.scss') }}">
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

			<div id="slider"></div>

		<br>
		<a href="#">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
				Create New Playlist
		</a><br>

  </form>
</div>

<script>
var slider = document.getElementById('slider');
const valuesForSlider = [0,1,2,3,4,5,6,7,8,9,10];
const Min = document.getElementById('Min');
const Max = document.getElementById('Max');

const format = {
    to: function(value) {
        return valuesForSlider[Math.round(value)];
    },
    from: function (value) {
        return valuesForSlider.indexOf(Number(value));
  }
}

noUiSlider.create(slider, {

range: {
	'min': 0,
	'max': valuesForSlider.length-1
},
step: 1,
start: [0, 10],
connect: true,
behaviour: 'tap-drag',
tooltips: true,
format: format,
});

range.noUiSlider.on('update', function( values, handle ) {
  min.value = Math.trunc(values[0])
  max.value = Math.trunc(values[1])
})
</script>

@endsection
