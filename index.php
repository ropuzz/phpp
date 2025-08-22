<?php
// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = basename($_FILES['music_file']['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['music_file']['tmp_name'], $targetPath)) {
        $uploadSuccess = "File uploaded successfully!";
    } else {
        $uploadError = "Error uploading file.";
    }
}

// Get list of music files
$musicFiles = [];
if (file_exists('uploads/')) {
    $files = scandir('uploads/');
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
                $musicFiles[] = $file;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
        }

        .player-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .now-playing {
            text-align: center;
            margin-bottom: 30px;
        }

        .album-art {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .album-art img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .track-info h3 {
            font-size: 1.5em;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .track-info p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 25px 0;
        }

        .control-btn {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            font-size: 1.2em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .control-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .play-btn {
            width: 60px;
            height: 60px;
            font-size: 1.5em;
        }

        .progress-container {
            margin: 20px 0;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            cursor: pointer;
        }

        .progress {
            height: 100%;
            background: linear-gradient(90deg, #4facfe, #00f2fe);
            width: 0%;
            transition: width 0.1s ease;
        }

        .time-display {
            display: flex;
            justify-content: space-between;
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .volume-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }

        .volume-slider {
            flex: 1;
            -webkit-appearance: none;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            outline: none;
        }

        .volume-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #4facfe;
            cursor: pointer;
        }

        .library-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .library-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .track-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .track-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .track-item:hover {
            background: #e3f2fd;
            border-left-color: #4facfe;
            transform: translateX(5px);
        }

        .track-item.active {
            background: #e3f2fd;
            border-left-color: #4facfe;
        }

        .track-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #4facfe, #00f2fe);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
        }

        .track-details {
            flex: 1;
        }

        .track-details h4 {
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .track-details p {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .upload-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
        }

        .file-input {
            padding: 12px;
            border: 2px dashed #4facfe;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            background: #f0f8ff;
        }

        .upload-btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎵 Modern Music Player</h1>
            <p>Musik tanpa batas untuk semua</p>
        </div>

        <div class="content">
            <div class="player-section">
                <div class="now-playing">
                    <div class="album-art">
                        <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/813b6575-fae9-4de2-854d-1e6909901d36.png" alt="Album cover art featuring abstract musical notes and gradient background" />
                    </div>
                    <div class="track-info">
                        <h3 id="current-track">Select a track</h3>
                        <p id="current-artist">Music Player</p>
                    </div>
                </div>

                <div class="controls">
                    <button class="control-btn" onclick="previousTrack()">⏮</button>
                    <button class="control-btn play-btn" onclick="togglePlay()" id="play-btn">▶</button>
                    <button class="control-btn" onclick="nextTrack()">⏭</button>
                </div>

                <div class="progress-container">
                    <div class="progress-bar" onclick="seek(event)">
                        <div class="progress" id="progress"></div>
                    </div>
                    <div class="time-display">
                        <span id="current-time">0:00</span>
                        <span id="total-time">0:00</span>
                    </div>
                </div>

                <div class="volume-control">
                    <span>🔈</span>
                    <input type="range" class="volume-slider" id="volume" min="0" max="1" step="0.1" value="0.7" oninput="setVolume(this.value)">
                    <span>🔊</span>
                </div>
            </div>

            <div class="library-section">
                <h2>Your Music Library</h2>
                <div class="track-list" id="track-list">
                    <?php if (!empty($musicFiles)): ?>
                        <?php foreach ($musicFiles as $index => $file): ?>
                            <div class="track-item" onclick="playTrack(<?php echo $index; ?>)">
                                <div class="track-icon">🎵</div>
                                <div class="track-details">
                                    <h4><?php echo pathinfo($file, PATHINFO_FILENAME); ?></h4>
                                    <p><?php echo strtoupper(pathinfo($file, PATHINFO_EXTENSION)); ?> • <?php echo round(filesize('uploads/' . $file) / 1024 / 1024, 2); ?> MB</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div>📁</div>
                            <h3>No music files found</h3>
                            <p>Upload some music to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="upload-section">
            <h2>Upload Music</h2>
            <?php if (isset($uploadSuccess)): ?>
                <div class="message success"><?php echo $uploadSuccess; ?></div>
            <?php endif; ?>
            <?php if (isset($uploadError)): ?>
                <div class="message error"><?php echo $uploadError; ?></div>
            <?php endif; ?>
            <form class="upload-form" method="POST" enctype="multipart/form-data">
                <label class="file-input">
                    <input type="file" name="music_file" accept=".mp3,.wav,.ogg,.m4a" style="display: none;" onchange="this.form.submit()">
                    📁 Choose music file (MP3, WAV, OGG, M4A)
                </label>
                <button type="submit" class="upload-btn">Upload Music</button>
            </form>
        </div>
    </div>

    <audio id="audio-player"></audio>

    <script>
        const audio = document.getElementById('audio-player');
        const playBtn = document.getElementById('play-btn');
        const progress = document.getElementById('progress');
        const currentTime = document.getElementById('current-time');
        const totalTime = document.getElementById('total-time');
        const currentTrack = document.getElementById('current-track');
        const currentArtist = document.getElementById('current-artist');
        
        let tracks = <?php echo json_encode($musicFiles); ?>;
        let currentTrackIndex = -1;
        let isPlaying = false;

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        function updateProgress() {
            const percent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = percent + '%';
            currentTime.textContent = formatTime(audio.currentTime);
        }

        function togglePlay() {
            if (isPlaying) {
                audio.pause();
                playBtn.innerHTML = '▶';
            } else {
                audio.play();
                playBtn.innerHTML = '⏸';
            }
            isPlaying = !isPlaying;
        }

        function setVolume(volume) {
            audio.volume = volume;
        }

        function seek(event) {
            const progressBar = event.currentTarget;
            const clickPosition = event.offsetX;
            const totalWidth = progressBar.offsetWidth;
            const seekTime = (clickPosition / totalWidth) * audio.duration;
            audio.currentTime = seekTime;
        }

        function playTrack(index) {
            if (tracks.length === 0) return;
            
            currentTrackIndex = index;
            const track = tracks[index];
            audio.src = 'uploads/' + encodeURIComponent(track);
            
            // Update UI
            currentTrack.textContent = track.replace(/\.[^/.]+$/, "");
            currentArtist.textContent = 'Now Playing';
            
            // Remove active class from all tracks
            document.querySelectorAll('.track-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to current track
            document.querySelectorAll('.track-item')[index].classList.add('active');
            
            audio.load();
            audio.play();
            playBtn.innerHTML = '⏸';
            isPlaying = true;
        }

        function nextTrack() {
            if (tracks.length === 0) return;
            const nextIndex = (currentTrackIndex + 1) % tracks.length;
            playTrack(nextIndex);
        }

        function previousTrack() {
            if (tracks.length === 0) return;
            const prevIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;
            playTrack(prevIndex);
        }

        // Event listeners
        audio.addEventListener('timeupdate', updateProgress);
        audio.addEventListener('ended', nextTrack);
        audio.addEventListener('loadedmetadata', () => {
            totalTime.textContent = formatTime(audio.duration);
        });

        // Initialize volume
        setVolume(0.7);

        // Auto-play first track if available
        <?php if (!empty($musicFiles)): ?>
            setTimeout(() => playTrack(0), 1000);
        <?php endif; ?>
    </script>
</body>
</html>

