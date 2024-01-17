<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlist Created</title>
</head>
<body>
    <h1>Playlist Created Successfully</h1>
    
    <p>Playlist Name: {{ $playlistName }}</p>
    <p>Spotify Playlist Link: <a href="{{ $spotifyPlaylistLink }}" target="_blank">{{ $spotifyPlaylistLink }}</a></p>
</body>
</html>
