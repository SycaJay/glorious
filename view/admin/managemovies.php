<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@400;500;600&display=swap">
    <style>
        /* Your existing CSS styles */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a1c2e 0%, #2d3748 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: #e2e8f0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            margin: 0;
            background: linear-gradient(45deg, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            background: rgba(255, 255, 255, 0.1);
            color: #f59e0b;
            border: 2px solid #f59e0b;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #f59e0b;
            color: #1a1c2e;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #1a1c2e;
            padding: 2rem;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .modal-content .form-group {
            margin-bottom: 1rem;
        }

        .modal-content label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .modal-content input,
        .modal-content textarea,
        .modal-content select {
            width: 100%;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .modal-content .btn {
            width: 100%;
            margin-top: 1rem;
        }
        
        .optional-field {
            color: #94a3b8;
            font-size: 0.8rem;
            margin-left: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Movies</h1>
            <div class="actions">
                <button class="btn btn-primary" onclick="openAddModal()">Add New Movie</button>
                <a href="index.php">
                    <button class="btn">Dashboard</button>
                </a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Video Path</th>
                    <th>Image Path</th>
                    <th>Subtitle Path</th>
                    <th>Status</th>
                    <th>Scheduled Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Include your database connection file
                include '../../db/tvconfig.php'; // Replace with your actual database connection file

                // Fetch movies from the database
                $sql = "SELECT * FROM movies";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '
                        <tr>
                            <td>' . $row['movie_id'] . '</td>
                            <td>' . $row['title'] . '</td>
                            <td>' . ($row['description'] ?? 'N/A') . '</td>
                            <td>' . ($row['video_path'] ?? 'N/A') . '</td>
                            <td>' . ($row['image_path'] ?? 'N/A') . '</td>
                            <td>' . ($row['subtitle_path'] ?? 'N/A') . '</td>
                            <td>' . $row['status'] . '</td>
                            <td>' . ($row['scheduled_time'] ?? 'N/A') . '</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn" onclick="openEditModal(' . $row['movie_id'] . ', \'' . 
                                    addslashes($row['title']) . '\', \'' . 
                                    addslashes($row['description'] ?? '') . '\', \'' . 
                                    addslashes($row['video_path'] ?? '') . '\', \'' . 
                                    addslashes($row['image_path'] ?? '') . '\', \'' . 
                                    addslashes($row['subtitle_path'] ?? '') . '\', \'' . 
                                    $row['status'] . '\', \'' . 
                                    addslashes($row['scheduled_time'] ?? '') . '\')">Edit</button>
                                    <button class="btn" onclick="deleteMovie(' . $row['movie_id'] . ')">Delete</button>
                                </div>
                            </td>
                        </tr>';
                    }
                } else {
                    echo '<tr><td colspan="9" style="text-align: center;">No movies found.</td></tr>';
                }

                // Close the database connection
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Add Movie Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h2>Add New Movie</h2>
            <form id="addMovieForm" action="../../actions/movie/add.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="video">Video File</label>
                    <input type="file" id="video" name="video">
                </div>
                <div class="form-group">
                    <label for="image">Image File</label>
                    <input type="file" id="image" name="image">
                </div>
                <div class="form-group">
                    <label for="subtitle">Subtitle File <span class="optional-field">(optional)</span></label>
                    <input type="file" id="subtitle" name="subtitle" accept=".vtt,.srt">
                    <small style="display: block; margin-top: 5px; color: #94a3b8;">Accepts .vtt or .srt subtitle files</small>
                </div>
                <div id="addProgressContainer" class="form-group" style="display: none;">
                    <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                        <div id="addProgressBar" class="bg-amber-500 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="addProgressText" class="text-sm text-gray-400 mt-1">Uploading: 0%</p>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="now_showing">Now Showing</option>
                        <option value="upcoming">Upcoming</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="scheduled_time">Scheduled Time</label>
                    <input type="datetime-local" id="scheduled_time" name="scheduled_time">
                </div>
                <button type="submit" class="btn btn-primary" id="addMovieSubmitBtn">Add Movie</button>
            </form>
        </div>
    </div>

    <!-- Edit Movie Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Movie</h2>
            <form id="editMovieForm" action="../../actions/movie/edit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_movie_id" name="movie_id">
                <div class="form-group">
                    <label for="edit_title">Title</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_video">Video File (leave empty to keep current)</label>
                    <input type="file" id="edit_video" name="video">
                    <input type="hidden" id="current_video_path" name="current_video_path">
                </div>
                <div class="form-group">
                    <label for="edit_image">Image File (leave empty to keep current)</label>
                    <input type="file" id="edit_image" name="image">
                    <input type="hidden" id="current_image_path" name="current_image_path">
                </div>
                <div class="form-group">
                    <label for="edit_subtitle">Subtitle File <span class="optional-field">(optional - leave empty to keep current)</span></label>
                    <input type="file" id="edit_subtitle" name="subtitle" accept=".vtt,.srt">
                    <input type="hidden" id="current_subtitle_path" name="current_subtitle_path">
                    <small style="display: block; margin-top: 5px; color: #94a3b8;">Accepts .vtt or .srt subtitle files</small>
                </div>
                <div id="editProgressContainer" class="form-group" style="display: none;">
                    <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                        <div id="editProgressBar" class="bg-amber-500 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="editProgressText" class="text-sm text-gray-400 mt-1">Uploading: 0%</p>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="now_showing">Now Showing</option>
                        <option value="upcoming">Upcoming</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_scheduled_time">Scheduled Time</label>
                    <input type="datetime-local" id="edit_scheduled_time" name="scheduled_time">
                </div>
                <button type="submit" class="btn btn-primary" id="editMovieSubmitBtn">Update Movie</button>
            </form>
        </div>
    </div>

    <script>
        // Open Add Movie Modal
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        // Open Edit Movie Modal with pre-filled data
        function openEditModal(movieId, title, description, videoPath, imagePath, subtitlePath, status, scheduledTime) {
            document.getElementById('edit_movie_id').value = movieId;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('current_video_path').value = videoPath;
            document.getElementById('current_image_path').value = imagePath;
            document.getElementById('current_subtitle_path').value = subtitlePath;
            document.getElementById('edit_status').value = status;
            
            // Format datetime-local input
            if (scheduledTime && scheduledTime !== 'N/A') {
                // Convert the MySQL datetime format to the format required by datetime-local input
                let dt = new Date(scheduledTime);
                let year = dt.getFullYear();
                let month = (dt.getMonth() + 1).toString().padStart(2, '0');
                let day = dt.getDate().toString().padStart(2, '0');
                let hours = dt.getHours().toString().padStart(2, '0');
                let minutes = dt.getMinutes().toString().padStart(2, '0');
                
                document.getElementById('edit_scheduled_time').value = `${year}-${month}-${day}T${hours}:${minutes}`;
            } else {
                document.getElementById('edit_scheduled_time').value = '';
            }
            
            document.getElementById('editModal').style.display = 'flex';
        }

        // Close Modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                addModal.style.display = 'none';
            }
            
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        }

        // Delete Movie Function
        function deleteMovie(movieId) {
            if (confirm('Are you sure you want to delete this movie?')) {
                window.location.href = `../../actions/movie/delete.php?id=${movieId}`;
            }
        }

        const MOVIE_CHUNK_SIZE = 5 * 1024 * 1024; // 5MB

        // Chunked upload for Add Movie
        document.getElementById('addMovieForm').addEventListener('submit', async function(e) {
            const videoInput = document.getElementById('video');
            const file = videoInput.files[0];
            if (!file || file.size <= MOVIE_CHUNK_SIZE) return;
            e.preventDefault();
            const submitBtn = document.getElementById('addMovieSubmitBtn');
            const progressContainer = document.getElementById('addProgressContainer');
            const progressBar = document.getElementById('addProgressBar');
            const progressText = document.getElementById('addProgressText');
            const totalChunks = Math.ceil(file.size / MOVIE_CHUNK_SIZE);
            let currentChunk = 0;
            submitBtn.disabled = true;
            progressContainer.style.display = 'block';
            progressText.textContent = 'Uploading video: 0%';
            while (currentChunk < totalChunks) {
                const start = currentChunk * MOVIE_CHUNK_SIZE;
                const end = Math.min(start + MOVIE_CHUNK_SIZE, file.size);
                const chunk = file.slice(start, end);
                const formData = new FormData();
                formData.append('video', chunk);
                formData.append('chunk', currentChunk);
                formData.append('totalChunks', totalChunks);
                formData.append('fileName', file.name);
                formData.append('title', document.getElementById('title').value);
                formData.append('description', document.getElementById('description').value);
                formData.append('status', document.getElementById('status').value);
                formData.append('scheduled_time', document.getElementById('scheduled_time').value);
                if (currentChunk + 1 === totalChunks) {
                    const img = document.getElementById('image').files[0];
                    const sub = document.getElementById('subtitle').files[0];
                    if (img) formData.append('image', img);
                    if (sub) formData.append('subtitle', sub);
                }
                try {
                    const response = await fetch('../../actions/movie/add.php', { method: 'POST', body: formData });
                    if (!response.ok) throw new Error('Upload failed');
                    const percent = Math.round(((currentChunk + 1) / totalChunks) * 100);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = 'Uploading video: ' + percent + '%';
                    if (currentChunk + 1 === totalChunks) {
                        progressText.textContent = 'Upload completed successfully!';
                        progressBar.style.backgroundColor = '#22c55e';
                        setTimeout(function() { window.location.href = '../../view/admin/managemovies.php?success=1'; }, 1500);
                    }
                } catch (err) {
                    progressText.textContent = 'Upload failed: ' + err.message;
                    progressBar.style.backgroundColor = '#ef4444';
                    submitBtn.disabled = false;
                    return;
                }
                currentChunk++;
            }
        });

        // Chunked upload for Edit Movie (only when new video selected and large)
        document.getElementById('editMovieForm').addEventListener('submit', async function(e) {
            const videoInput = document.getElementById('edit_video');
            const file = videoInput.files[0];
            if (!file || file.size <= MOVIE_CHUNK_SIZE) return;
            e.preventDefault();
            const submitBtn = document.getElementById('editMovieSubmitBtn');
            const progressContainer = document.getElementById('editProgressContainer');
            const progressBar = document.getElementById('editProgressBar');
            const progressText = document.getElementById('editProgressText');
            const totalChunks = Math.ceil(file.size / MOVIE_CHUNK_SIZE);
            let currentChunk = 0;
            submitBtn.disabled = true;
            progressContainer.style.display = 'block';
            progressText.textContent = 'Uploading video: 0%';
            while (currentChunk < totalChunks) {
                const start = currentChunk * MOVIE_CHUNK_SIZE;
                const end = Math.min(start + MOVIE_CHUNK_SIZE, file.size);
                const chunk = file.slice(start, end);
                const formData = new FormData();
                formData.append('video', chunk);
                formData.append('chunk', currentChunk);
                formData.append('totalChunks', totalChunks);
                formData.append('fileName', file.name);
                formData.append('movie_id', document.getElementById('edit_movie_id').value);
                formData.append('title', document.getElementById('edit_title').value);
                formData.append('description', document.getElementById('edit_description').value);
                formData.append('status', document.getElementById('edit_status').value);
                formData.append('scheduled_time', document.getElementById('edit_scheduled_time').value);
                formData.append('current_video_path', document.getElementById('current_video_path').value);
                formData.append('current_image_path', document.getElementById('current_image_path').value);
                formData.append('current_subtitle_path', document.getElementById('current_subtitle_path').value);
                if (currentChunk + 1 === totalChunks) {
                    const img = document.getElementById('edit_image').files[0];
                    const sub = document.getElementById('edit_subtitle').files[0];
                    if (img) formData.append('image', img);
                    if (sub) formData.append('subtitle', sub);
                }
                try {
                    const response = await fetch('../../actions/movie/edit.php', { method: 'POST', body: formData });
                    if (!response.ok) throw new Error('Upload failed');
                    const percent = Math.round(((currentChunk + 1) / totalChunks) * 100);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = 'Uploading video: ' + percent + '%';
                    if (currentChunk + 1 === totalChunks) {
                        progressText.textContent = 'Upload completed successfully!';
                        progressBar.style.backgroundColor = '#22c55e';
                        setTimeout(function() { window.location.href = '../../view/admin/managemovies.php?success=2'; }, 1500);
                    }
                } catch (err) {
                    progressText.textContent = 'Upload failed: ' + err.message;
                    progressBar.style.backgroundColor = '#ef4444';
                    submitBtn.disabled = false;
                    return;
                }
                currentChunk++;
            }
        });
    </script>
</body>
</html>