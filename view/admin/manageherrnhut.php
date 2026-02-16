<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include your database connection here
include('../../db/tvconfig.php');
// Fetch herrnhut data from database
$query = "SELECT * FROM herrnhut_videos ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$herrnhutData = [];
while($row = mysqli_fetch_assoc($result)) {
    $herrnhutData[] = $row;
}
// Check for success messages
$success_message = "";
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1:
            $success_message = "Video added successfully!";
            break;
        case 2:
            $success_message = "Video updated successfully!";
            break;
        case 3:
            $success_message = "Video deleted successfully!";
            break;
    }
}
// Check for error messages
$error_message = "";
if (isset($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Herrnhut</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/lucide-css@0.263.1" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-violet-50 to-indigo-50">
    <div class="p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-violet-900">Manage Herrnhut</h1>
                    <p class="text-violet-600 mt-1">Update and manage content</p>
                </div>
                <div class="flex gap-4">
                    <button onclick="openAddModal()" class="flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors">
                        <i class="icon-plus-circle"></i>
                        Add New
                    </button>
                    <a href="index.php" class="flex items-center gap-2 px-4 py-2 border border-violet-300 rounded-lg hover:bg-violet-50 transition-colors">
                        <i class="icon-arrow-left"></i>
                        Back
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($success_message): ?>
            <div id="successAlert" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div id="errorAlert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex gap-4 items-center">
                    <div class="flex-1 relative">
                        <i class="icon-search absolute left-3 top-2.5 text-gray-400"></i>
                        <input
                            type="text"
                            id="searchInput"
                            placeholder="Search..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500"
                        >
                    </div>
                    <button 
                        class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50"
                        id="dateFilter"
                    >
                        <i class="icon-calendar"></i>
                        Filter by Date
                    </button>
                </div>
            </div>
<!-- Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="w-full" id="herrnhutTable">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video File</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Scheduled</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($herrnhutData as $item): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['video_title']); ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Video File Present
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $item['scheduled_date'] ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                        <?php echo $item['scheduled_date'] ? 'Scheduled' : 'Available'; ?>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo $item['scheduled_date'] ? date('M d, Y', strtotime($item['scheduled_date'])) : 'Not scheduled'; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                        <button
                            onclick="handleEdit(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                            class="inline-flex items-center p-1 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-full"
                            type="button"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            onclick="handleDelete(<?php echo $item['herrnhut_id']; ?>)"
                            class="inline-flex items-center p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-full"
                            type="button"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m4-6v.01M5 7V4a1 1 0 011-1h12a1 1 0 011 1v3" />
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

            <!-- Add Modal -->
            <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Video</h3>
                        <form id="addForm" method="POST" action="../../actions/herrnhut/add.php" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Video Title</label>
                                <input type="text" name="video_title" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Video File</label>
                                <input type="file" name="video_file" id="add_video_file" required accept="video/*"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-500">
                            </div>
                            <div id="addProgressContainer" class="mb-4 hidden">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                    <div id="addProgressBar" class="bg-violet-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <p id="addProgressText" class="text-sm text-gray-600 mt-1">Uploading: 0%</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date (Optional)</label>
                                <input type="datetime-local" name="scheduled_date" id="add_scheduled_date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-500">
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeAddModal()" id="addCancelBtn"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" id="addSubmitBtn"
                                    class="px-4 py-2 bg-violet-600 text-white rounded-md hover:bg-violet-700">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Video</h3>
                        <form id="editForm" method="POST" action="../../actions/herrnhut/edit.php" enctype="multipart/form-data">
                            <input type="hidden" name="herrnhut_id" id="edit_herrnhut_id">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Video Title</label>
                                <input type="text" name="video_title" id="edit_video_title" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Video File (Optional)</label>
                                <input type="file" name="video_file" id="edit_video_file" accept="video/*"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-500">
                            </div>
                            <div id="editProgressContainer" class="mb-4 hidden">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                    <div id="editProgressBar" class="bg-violet-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <p id="editProgressText" class="text-sm text-gray-600 mt-1">Uploading: 0%</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date (Optional)</label>
                                <input type="datetime-local" name="scheduled_date" id="edit_scheduled_date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-500">
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeEditModal()" id="editCancelBtn"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" id="editSubmitBtn"
                                    class="px-4 py-2 bg-violet-600 text-white rounded-md hover:bg-violet-700">
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        // Handle message fadeout
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('#successAlert, #errorAlert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#herrnhutTable tbody tr');
            
            rows.forEach(row => {
                const title = row.querySelector('td:first-child').textContent.toLowerCase();
                row.style.display = title.includes(searchTerm) ? '' : 'none';
            });
        });

        // Modal Functions
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('addForm').reset();
        }

        function handleEdit(item) {
            document.getElementById('edit_herrnhut_id').value = item.herrnhut_id;
            document.getElementById('edit_video_title').value = item.video_title;
            if (item.scheduled_date) {
                document.getElementById('edit_scheduled_date').value = item.scheduled_date.slice(0, 16);
            }
           document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editForm').reset();
        }

        function handleDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6D28D9',
                cancelButtonColor: '#DC2626',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `../../actions/herrnhut/delete.php?id=${id}`;
                }
            });
        }

        // Date filter functionality
        document.getElementById('dateFilter').addEventListener('click', function() {
            Swal.fire({
                title: 'Select Date Range',
                html: `
                    <input type="date" id="startDate" class="swal2-input">
                    <input type="date" id="endDate" class="swal2-input">
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;
                    return { startDate, endDate }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    filterByDate(result.value.startDate, result.value.endDate);
                }
            });
        });

        function filterByDate(startDate, endDate) {
            const rows = document.querySelectorAll('#herrnhutTable tbody tr');
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            rows.forEach(row => {
                const dateStr = row.querySelector('td:nth-child(3)').textContent;
                const rowDate = new Date(dateStr);
                
                if (rowDate >= start && rowDate <= end) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Chunked upload for Add form
        const CHUNK_SIZE = 5 * 1024 * 1024; // 5MB
        document.getElementById('addForm').addEventListener('submit', async function(e) {
            const fileInput = document.getElementById('add_video_file');
            const file = fileInput.files[0];
            if (!file) return;
            if (file.size <= CHUNK_SIZE) return; // small file: submit normally
            e.preventDefault();
            const submitBtn = document.getElementById('addSubmitBtn');
            const progressContainer = document.getElementById('addProgressContainer');
            const progressBar = document.getElementById('addProgressBar');
            const progressText = document.getElementById('addProgressText');
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            let currentChunk = 0;
            submitBtn.disabled = true;
            progressContainer.classList.remove('hidden');
            progressText.textContent = 'Uploading: 0%';
            while (currentChunk < totalChunks) {
                const start = currentChunk * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, file.size);
                const chunk = file.slice(start, end);
                const formData = new FormData();
                formData.append('video_file', chunk);
                formData.append('chunk', currentChunk);
                formData.append('totalChunks', totalChunks);
                formData.append('fileName', file.name);
                formData.append('video_title', document.querySelector('#addForm input[name="video_title"]').value);
                formData.append('scheduled_date', document.getElementById('add_scheduled_date').value);
                try {
                    const response = await fetch('../../actions/herrnhut/add.php', { method: 'POST', body: formData });
                    if (!response.ok) throw new Error('Upload failed');
                    const percent = Math.round(((currentChunk + 1) / totalChunks) * 100);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = 'Uploading: ' + percent + '%';
                    if (currentChunk + 1 === totalChunks) {
                        progressText.textContent = 'Upload completed successfully!';
                        progressBar.classList.remove('bg-violet-600');
                        progressBar.classList.add('bg-green-500');
                        setTimeout(() => { window.location.href = '../../view/admin/manageherrnhut.php?success=1'; }, 1500);
                    }
                } catch (err) {
                    progressText.textContent = 'Upload failed: ' + err.message;
                    progressBar.classList.remove('bg-violet-600');
                    progressBar.classList.add('bg-red-500');
                    submitBtn.disabled = false;
                    return;
                }
                currentChunk++;
            }
        });

        // Chunked upload for Edit form (only when new video file selected)
        document.getElementById('editForm').addEventListener('submit', async function(e) {
            const fileInput = document.getElementById('edit_video_file');
            const file = fileInput.files[0];
            if (!file || file.size <= CHUNK_SIZE) return;
            e.preventDefault();
            const submitBtn = document.getElementById('editSubmitBtn');
            const progressContainer = document.getElementById('editProgressContainer');
            const progressBar = document.getElementById('editProgressBar');
            const progressText = document.getElementById('editProgressText');
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            let currentChunk = 0;
            submitBtn.disabled = true;
            progressContainer.classList.remove('hidden');
            progressText.textContent = 'Uploading: 0%';
            while (currentChunk < totalChunks) {
                const start = currentChunk * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, file.size);
                const chunk = file.slice(start, end);
                const formData = new FormData();
                formData.append('video_file', chunk);
                formData.append('chunk', currentChunk);
                formData.append('totalChunks', totalChunks);
                formData.append('fileName', file.name);
                formData.append('herrnhut_id', document.getElementById('edit_herrnhut_id').value);
                formData.append('video_title', document.getElementById('edit_video_title').value);
                formData.append('scheduled_date', document.getElementById('edit_scheduled_date').value);
                try {
                    const response = await fetch('../../actions/herrnhut/edit.php', { method: 'POST', body: formData });
                    if (!response.ok) throw new Error('Upload failed');
                    const percent = Math.round(((currentChunk + 1) / totalChunks) * 100);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = 'Uploading: ' + percent + '%';
                    if (currentChunk + 1 === totalChunks) {
                        progressText.textContent = 'Upload completed successfully!';
                        progressBar.classList.remove('bg-violet-600');
                        progressBar.classList.add('bg-green-500');
                        setTimeout(() => { window.location.href = '../../view/admin/manageherrnhut.php?success=2'; }, 1500);
                    }
                } catch (err) {
                    progressText.textContent = 'Upload failed: ' + err.message;
                    progressBar.classList.remove('bg-violet-600');
                    progressBar.classList.add('bg-red-500');
                    submitBtn.disabled = false;
                    return;
                }
                currentChunk++;
            }
        });
    </script>
</body>
</html>