<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Playlist Test</title>
</head>
<body>
    <h1>Create Playlist Test</h1>

    <form action="{{ route('createPlaylist') }}" method="post">
        @csrf
        <label for="start_date">Start Date:</label>
        <input type="text" name="start_date" id="start_date" value="2024-01-01">
        <br>

        <label for="end_date">End Date:</label>
        <input type="text" name="end_date" id="end_date" value="2024-01-10">
        <br>

        <label for="playlist_name">Playlist Name:</label>
        <input type="text" name="playlist_name" id="playlist_name" value="MyPlaylist">
        <br>

        <button type="submit">Create Playlist</button>
    </form>
</body>
</html>