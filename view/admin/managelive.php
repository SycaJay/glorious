<?php
include '../../db/tvconfig.php';
session_start();

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $query = "SELECT video FROM hero_video WHERE hero_id=$id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $video_path = $row['video'];
        if (file_exists($video_path)) {
            unlink($video_path);
        }
    }
    
    $sql = "DELETE FROM hero_video WHERE hero_id=$id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Record deleted successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting record: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: managelive.php");
    exit();
}

// Pagination settings
$records_per_page = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch total number of records
$sql_count = "SELECT COUNT(*) as total FROM hero_video";
$result_count = $conn->query($sql_count);
$total_records = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch data with pagination
$sql = "SELECT hero_id, title, video, scheduled_time, end_time, created_at, is_live, stream_url FROM hero_video LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage 24 Hour Channel Content</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@400;500;600&display=swap">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f6f8fd 0%, #f1f4f9 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
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
            color: #2d3748;
            font-size: 2.5rem;
            margin: 0;
            background: linear-gradient(45deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            background: #fff;
            color: #3b82f6;
            border: 2px solid #3b82f6;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: #3b82f6;
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .content-card {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .content-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .content-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .content-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: #2d3748;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stream-info {
            margin-top: 1rem;
            color: #64748b;
            font-size: 0.875rem;
        }

        .stream-info p {
            margin: 0.5rem 0;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .search-bar input {
            border: none;
            outline: none;
            width: 100%;
            padding: 0.5rem;
            font-family: 'Inter', sans-serif;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 10% auto;
            padding: 1.5rem;
            border-radius: 12px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: modalfade 0.3s;
        }

        @keyframes modalfade {
            from {opacity: 0; transform: translateY(-30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            color: #64748b;
        }

        .close-modal:hover {
            color: #2d3748;
        }

        .modal-header {
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            margin: 0;
            color: #2d3748;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #4b5563;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .video-thumbnail {
            width: 100%;
            height: 120px;
            margin-bottom: 0.75rem;
            border-radius: 6px;
            background-color: #f1f5f9;
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }

        .video-thumbnail:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%233b82f6"><path d="M8 5v14l11-7z"/></svg>');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.8;
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .file-upload input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            cursor: pointer;
            display: block;
        }

        .upload-label {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 6px;
            font-weight: 500;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        .upload-label:hover {
            background-color: #f1f5f9;
            border-color: #3b82f6;
        }

        .upload-label.has-file {
            background-color: #ebf5ff;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .file-name {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #64748b;
            word-break: break-all;
            display: none;
        }

        .file-name.visible {
            display: block;
        }

        .time-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .progress-container {
            display: none;
            margin-top: 1rem;
            width: 100%;
            background-color: #f3f4f6;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-bar {
            height: 20px;
            width: 0%;
            background-color: #3b82f6;
            text-align: center;
            line-height: 20px;
            color: white;
            transition: width 0.3s ease;
        }

        .progress-text {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #4b5563;
            text-align: center;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-decoration: none;
            color: #4b5563;
            transition: all 0.2s ease;
        }

        .pagination a:hover {
            background-color: #3b82f6;
            color: #fff;
            border-color: #3b82f6;
        }

        .pagination .active {
            background-color: #3b82f6;
            color: #fff;
            border-color: #3b82f6;
        }

        .pagination .disabled {
            color: #9ca3af;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage 24 Hour Channel Content</h1>
            <div class="actions">
                <button class="btn btn-primary" id="openAddModal">Add New Stream</button>
                <a href="index.php">
                    <button class="btn">Dashboard</button>
                </a>
            </div>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            $messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
            echo '<div class="alert alert-' . $messageType . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search live content...">
        </div>

        <div class="content-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $thumbnailStyle = 'background-image: url(\'../../assets/video-placeholder.jpg\');';
                    
                    echo '<div class="content-card">
                            <div class="video-thumbnail" style="' . $thumbnailStyle . '"></div>
                            <div class="content-card-header">
                                <h3>' . htmlspecialchars($row["title"]) . '</h3>
                                ' . ($row["is_live"] == 1 ? '<span class="live-badge">Live</span>' : '') . '
                            </div>
                            <div class="stream-info">
                                <p>Uploaded: ' . htmlspecialchars($row["created_at"]) . '</p>';
                    
                    if ($row["scheduled_time"]) {
                        echo '<p>Scheduled: ' . htmlspecialchars($row["scheduled_time"]) . '</p>';
                    }
                    
                    if ($row["end_time"]) {
                        echo '<p>Ends: ' . htmlspecialchars($row["end_time"]) . '</p>';
                    }

                    if ($row["is_live"] == 1 && $row["stream_url"]) {
                        echo '<p>Stream URL: ' . htmlspecialchars($row["stream_url"]) . '</p>';
                    }

                    echo '<div class="action-buttons">
                              <button class="btn btn-primary edit-btn" 
                                      data-id="' . $row["hero_id"] . '" 
                                      data-title="' . htmlspecialchars($row["title"]) . '" 
                                      data-video="' . htmlspecialchars($row["video"]) . '" 
                                      data-scheduled="' . htmlspecialchars($row["scheduled_time"]) . '"
                                      data-endtime="' . htmlspecialchars($row["end_time"]) . '"
                                      data-is-live="' . $row["is_live"] . '"
                                      data-stream-url="' . htmlspecialchars($row["stream_url"]) . '">
                                Edit
                              </button>
                              <button class="btn delete-btn" data-id="' . $row["hero_id"] . '">Delete</button>
                            </div>
                          </div>
                        </div>';
                }
            } else {
                echo '<p>No videos found.</p>';
            }
            $conn->close();
            ?>
        </div>

        <div class="pagination">
            <?php
            if ($page > 1) {
                echo '<a href="?page=' . ($page - 1) . '">&laquo; Previous</a>';
            } else {
                echo '<a class="disabled">&laquo; Previous</a>';
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                echo '<a href="?page=' . $i . '"' . ($i == $page ? ' class="active"' : '') . '>' . $i . '</a>';
            }

            if ($page < $total_pages) {
                echo '<a href="?page=' . ($page + 1) . '">Next &raquo;</a>';
            } else {
                echo '<a class="disabled">Next &raquo;</a>';
            }
            ?>
        </div>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeAddModal">×</span>
            <div class="modal-header">
                <h2>Add New Stream</h2>
            </div>
            <form id="addForm" action="../../actions/livetv/add.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="is_live">Is Live?</label>
                    <select id="is_live" name="is_live" class="form-input" onchange="toggleLiveFields()">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="form-group" id="streamUrlField" style="display: none;">
                    <label for="stream_url">Stream URL</label>
                    <input type="text" id="stream_url" name="stream_url" class="form-input">
                </div>
                <div class="form-group" id="videoFileField">
                    <label for="video_file">Video File (optional)</label>
                    <div class="file-upload">
                        <label for="video_file" class="upload-label" id="uploadLabel">Choose video file</label>
                        <input type="file" id="video_file" name="video_file" accept="video/*">
                    </div>
                    <div class="file-name" id="fileName"></div>
                    <div class="progress-container" id="progressContainer">
                        <div class="progress-bar" id="progressBar"></div>
                    </div>
                    <div class="progress-text" id="progressText"></div>
                </div>
                <div class="time-fields">
                    <div class="form-group">
                        <label for="scheduled_time">Scheduled Time (optional)</label>
                        <input type="datetime-local" id="scheduled_time" name="scheduled_time" class="form-input" step="1">
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time (optional)</label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-input" step="1">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeEditModal">×</span>
            <div class="modal-header">
                <h2>Edit Stream</h2>
            </div>
            <form action="../../actions/livetv/edit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_id" name="hero_id">
                <input type="hidden" id="current_video" name="current_video">
                <div class="form-group">
                    <label for="edit_title">Title</label>
                    <input type="text" id="edit_title" name="title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="edit_is_live">Is Live?</label>
                    <select id="edit_is_live" name="is_live" class="form-input" onchange="toggleEditLiveFields()">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="form-group" id="editStreamUrlField" style="display: none;">
                    <label for="edit_stream_url">Stream URL</label>
                    <input type="text" id="edit_stream_url" name="stream_url" class="form-input">
                </div>
                <div class="form-group" id="editVideoFileField">
                    <label for="edit_video_file">Video File (leave empty to keep current video)</label>
                    <div class="file-upload">
                        <label for="edit_video_file" class="upload-label" id="editUploadLabel">Change video file</label>
                        <input type="file" id="edit_video_file" name="video_file" accept="video/*">
                    </div>
                    <div class="file-name" id="editFileName"></div>
                    <p class="current-file" id="currentVideoName" style="margin-top: 0.5rem; font-size: 0.85rem; color: #64748b;"></p>
                </div>
                <div class="time-fields">
                    <div class="form-group">
                        <label for="edit_scheduled_time">Scheduled Time (optional)</label>
                        <input type="datetime-local" id="edit_scheduled_time" name="scheduled_time" class="form-input" step="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_end_time">End Time (optional)</label>
                        <input type="datetime-local" id="edit_end_time" name="end_time" class="form-input" step="1">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeDeleteModal">×</span>
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
            </div>
            <p>Are you sure you want to delete this stream? This action cannot be undone.</p>
            <div class="form-group" style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button id="cancelDelete" class="btn">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-primary">Delete</a>
            </div>
        </div>
    </div>

    <script>
        const addModal = document.getElementById('addModal');
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');

        document.getElementById('openAddModal').onclick = () => {
            addModal.style.display = 'block';
        };
        document.getElementById('closeAddModal').onclick = () => {
            addModal.style.display = 'none';
        };

        document.getElementById('video_file').onchange = function() {
            const fileName = this.files[0] ? this.files[0].name : '';
            document.getElementById('fileName').textContent = fileName;
            document.getElementById('fileName').classList.toggle('visible', fileName !== '');
            document.getElementById('uploadLabel').classList.toggle('has-file', fileName !== '');
            document.getElementById('uploadLabel').textContent = fileName ? 'File selected' : 'Choose video file';
        };

        document.getElementById('edit_video_file').onchange = function() {
            const fileName = this.files[0] ? this.files[0].name : '';
            document.getElementById('editFileName').textContent = fileName;
            document.getElementById('editFileName').classList.toggle('visible', fileName !== '');
            document.getElementById('editUploadLabel').classList.toggle('has-file', fileName !== '');
            document.getElementById('editUploadLabel').textContent = fileName ? 'File selected' : 'Change video file';
        };

        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.onclick = () => {
                const id = button.dataset.id;
                const title = button.dataset.title;
                const video = button.dataset.video;
                const scheduled = button.dataset.scheduled || '';
                const endTime = button.dataset.endtime || '';
                const isLive = button.dataset.isLive;
                const streamUrl = button.dataset.streamUrl || '';
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_title').value = title;
                document.getElementById('current_video').value = video;
                document.getElementById('edit_is_live').value = isLive;
                document.getElementById('edit_stream_url').value = streamUrl;
                
                const videoPath = video;
                const videoName = videoPath.split('/').pop();
                document.getElementById('currentVideoName').textContent = `Current file: ${videoName}`;
                
                document.getElementById('edit_scheduled_time').value = scheduled.replace(' ', 'T');
                document.getElementById('edit_end_time').value = endTime.replace(' ', 'T');
                
                toggleEditLiveFields();
                
                editModal.style.display = 'block';
            };
        });

        document.getElementById('closeEditModal').onclick = () => {
            editModal.style.display = 'none';
        };

        document.getElementById('closeDeleteModal').onclick = () => {
            deleteModal.style.display = 'none';
        };
        document.getElementById('cancelDelete').onclick = () => {
            deleteModal.style.display = 'none';
        };
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.onclick = () => {
                const id = button.dataset.id;
                document.getElementById('confirmDelete').href = `?delete=${id}`;
                deleteModal.style.display = 'block';
            };
        });

        window.onclick = (event) => {
            if (event.target === addModal) {
                addModal.style.display = 'none';
            } else if (event.target === editModal) {
                editModal.style.display = 'none';
            } else if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        };

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const cards = document.querySelectorAll('.content-card');
            
            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                if (title.includes(searchValue)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            setTimeout(() => {
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                });
            }, 5000);
        }

        function toggleLiveFields() {
            const isLive = document.getElementById('is_live').value;
            const streamUrlField = document.getElementById('streamUrlField');
            const videoFileField = document.getElementById('videoFileField');
            
            if (isLive == 1) {
                streamUrlField.style.display = 'block';
                videoFileField.style.display = 'none';
            } else {
                streamUrlField.style.display = 'none';
                videoFileField.style.display = 'block';
            }
        }

        function toggleEditLiveFields() {
            const isLive = document.getElementById('edit_is_live').value;
            const streamUrlField = document.getElementById('editStreamUrlField');
            const videoFileField = document.getElementById('editVideoFileField');
            
            if (isLive == 1) {
                streamUrlField.style.display = 'block';
                videoFileField.style.display = 'none';
            } else {
                streamUrlField.style.display = 'none';
                videoFileField.style.display = 'block';
            }
        }

        document.getElementById('addForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const isLive = document.getElementById('is_live').value;
            const fileInput = document.getElementById('video_file');
            const file = fileInput.files[0];
            const submitBtn = document.getElementById('submitBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            if (isLive == 1 || !file) {
                this.submit();
                return;
            }

            const chunkSize = 1024 * 1024 * 5; // 5MB chunks
            const totalChunks = Math.ceil(file.size / chunkSize);
            let currentChunk = 0;

            submitBtn.disabled = true;
            progressContainer.style.display = 'block';
            progressText.textContent = 'Uploading: 0%';

            async function uploadChunk(chunk, chunkIndex) {
                const formData = new FormData();
                formData.append('video_file', chunk);
                formData.append('chunk', chunkIndex);
                formData.append('totalChunks', totalChunks);
                formData.append('fileName', file.name);
                formData.append('title', document.getElementById('title').value);
                formData.append('is_live', isLive);
                formData.append('scheduled_time', document.getElementById('scheduled_time').value);
                formData.append('end_time', document.getElementById('end_time').value);

                try {
                    const response = await fetch('../../actions/livetv/add.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Upload failed');
                    }

                    const percent = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                    progressBar.style.width = `${percent}%`;
                    progressText.textContent = `Uploading: ${percent}%`;

                    if (chunkIndex + 1 === totalChunks) {
                        progressText.textContent = 'Upload completed successfully!';
                        progressBar.style.backgroundColor = '#22c55e';
                        setTimeout(() => {
                            window.location.href = '../../view/admin/managelive.php';
                        }, 2000);
                    }
                } catch (error) {
                    progressText.textContent = 'Upload failed: ' + error.message;
                    progressBar.style.backgroundColor = '#ef4444';
                    submitBtn.disabled = false;
                }
            }

            while (currentChunk < totalChunks) {
                const start = currentChunk * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);
                await uploadChunk(chunk, currentChunk);
                currentChunk++;
            }
        });
    </script>
</body>
</html>