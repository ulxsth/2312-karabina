<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="theme-color" content="#000000">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
				
        <!--googlefont londrinascketch-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Londrina+Sketch&display=swap" rel="stylesheet">
        <!--googlefont londrinascketch-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lusitana:wght@700&display=swap" rel="stylesheet">
		<!--googlefont Rubik Broken Fax-->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Rubik+Broken+Fax&display=swap" rel="stylesheet">
        <!--googlefont Rubik Lines-->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Rubik+Lines&display=swap" rel="stylesheet">
		{{-- googlefont  dotgothic16--}}
		<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DotGothic16&display=swap" rel="stylesheet">
		
		<title>Recentri</title>


		<!-- noUiSlider -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>

		<title>Recentri</title>
        @yield('head')

    </head>
    <body>
		<div id="crt-lines" background-image=></div>
		<!-- particles.js container -->
		<div id="particles-js"></div>
		<!-- stats - count particles -->
		<div class="count-particles">
			<span class="js-count-particles"></span>
		</div>
		<!-- particles.js lib - https://github.com/VincentGarreau/particles.js -->
		<script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
		<!-- stats.js lib -->
		<script src="http://threejs.org/examples/js/libs/stats.min.js"></script>

		<div class = "space"></div>
		<img src="\images\chara2.png">
        @yield('content')
    </body>
</html>
