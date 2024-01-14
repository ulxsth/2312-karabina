@extends('layouts.app')

@section('head')
@vite(['resources/scss/app.scss', 'resources/css/create.css'])
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />


@endsection

@section('content')
<div class="logo">
    <h1>Recentri <span>for Spotify</span></h1>
</div>


<div class="create-playlist">
  <form method="POST">
        @csrf

			<div id="slider"></div>

		<div class="create_button">
		<br>
		<a href="#">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
				Create New Playlist
		</a>

		<span class="material-symbols-outlined" id="lock" onclick="lock(lockButton)">
			lock_open
		</span>
		</div>
  </form>
</div>

<script>
	const lockButton = document.getElementById("lock"); 
	function lock(){
		if(lockButton.innerText === "lock"){
			lockButton.innerText = "lock_open";
			lockButton.style.color = "white";
			console.log("public");
		}else if(lockButton.innerText === "lock_open"){
			lockButton.innerText = "lock";
			lockButton.style.color = "black";
			console.log("private");
		}
	}
</script>

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
