<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include your database connection here
include('../../db/tvconfig.php');

// Fetch data from each table
$video_sermons = [];
$podcasts = [];
$audio_sermons = [];
$glorious_highlights = [];

// Use try-catch to handle any database errors
try {
    $video_sermons = $conn->query("SELECT * FROM video_sermons ORDER BY created_at DESC");
    if (!$video_sermons) {
        throw new Exception("Error fetching video sermons: " . $conn->error);
    }
    $video_sermons = $video_sermons->fetch_all(MYSQLI_ASSOC);

    $podcasts = $conn->query("SELECT * FROM podcasts ORDER BY created_at DESC");
    if (!$podcasts) {
        throw new Exception("Error fetching podcasts: " . $conn->error);
    }
    $podcasts = $podcasts->fetch_all(MYSQLI_ASSOC);

    $audio_sermons = $conn->query("SELECT * FROM audio_sermons ORDER BY created_at DESC");
    if (!$audio_sermons) {
        throw new Exception("Error fetching audio sermons: " . $conn->error);
    }
    $audio_sermons = $audio_sermons->fetch_all(MYSQLI_ASSOC);

    $glorious_highlights = $conn->query("SELECT * FROM glorious_highlights ORDER BY created_at DESC");
    if (!$glorious_highlights) {
        throw new Exception("Error fetching glorious highlights: " . $conn->error);
    }
    $glorious_highlights = $glorious_highlights->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
    // Set empty arrays as fallback
    $video_sermons = [];
    $podcasts = [];
    $audio_sermons = [];
    $glorious_highlights = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Iconify for icons -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Content Management</h1>
                <p class="text-gray-600 mt-1">Manage all media content in one place</p>
            </div>
            <div class="flex gap-4">
                <button 
                    onclick="showAddModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <iconify-icon icon="lucide:plus"></iconify-icon>
                    Add Content
                </button>
                <a href="index.php">
    <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon>
        Back
    </button>
</a>

            </div>
        </div>

        <!-- Content Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="lucide:video" class="text-indigo-600 text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Video Sermons</p>
                        <h3 class="text-2xl font-semibold text-gray-900"><?php echo count($video_sermons); ?></h3>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="lucide:headphones" class="text-purple-600 text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Podcasts</p>
                        <h3 class="text-2xl font-semibold text-gray-900"><?php echo count($podcasts); ?></h3>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="lucide:music" class="text-green-600 text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Audio Sermons</p>
                        <h3 class="text-2xl font-semibold text-gray-900"><?php echo count($audio_sermons); ?></h3>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="lucide:star" class="text-yellow-600 text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Glorious Highlights</p>
                        <h3 class="text-2xl font-semibold text-gray-900"><?php echo count($glorious_highlights); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Sermons Table -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:video" class="text-indigo-600"></iconify-icon>
                    <h2 class="text-xl font-semibold text-gray-900">Video Sermons</h2>
                </div>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-sm rounded-full">
                    <?php echo count($video_sermons); ?> items
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($video_sermons) > 0): ?>
                            <?php foreach ($video_sermons as $index => $item): ?>
                                <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['title']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-md truncate"><?php echo $item['description'] ?? 'No description'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button 
                                            onclick="editContent(<?php echo htmlspecialchars(json_encode($item)); ?>, 'video_sermons')"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            onclick="deleteContent('<?php echo $item['video_id']; ?>', 'video_sermons')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No content available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Podcasts Table -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:headphones" class="text-purple-600"></iconify-icon>
                    <h2 class="text-xl font-semibold text-gray-900">Podcasts</h2>
                </div>
                <span class="px-3 py-1 bg-purple-50 text-purple-600 text-sm rounded-full">
                    <?php echo count($podcasts); ?> items
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($podcasts) > 0): ?>
                            <?php foreach ($podcasts as $index => $item): ?>
                                <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['title']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-md truncate"><?php echo $item['description'] ?? 'No description'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button 
                                            onclick="editContent(<?php echo htmlspecialchars(json_encode($item)); ?>, 'podcasts')"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            onclick="deleteContent('<?php echo $item['podcast_id']; ?>', 'podcasts')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No content available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audio Sermons Table -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:music" class="text-green-600"></iconify-icon>
                    <h2 class="text-xl font-semibold text-gray-900">Audio Sermons</h2>
                </div>
                <span class="px-3 py-1 bg-green-50 text-green-600 text-sm rounded-full">
                    <?php echo count($audio_sermons); ?> items
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($audio_sermons) > 0): ?>
                            <?php foreach ($audio_sermons as $index => $item): ?>
                                <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['title']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-md truncate"><?php echo $item['description'] ?? 'No description'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button 
                                            onclick="editContent(<?php echo htmlspecialchars(json_encode($item)); ?>, 'audio_sermons')"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            onclick="deleteContent('<?php echo $item['audio_id']; ?>', 'audio_sermons')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No content available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Glorious Highlights Table -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:star" class="text-yellow-600"></iconify-icon>
                    <h2 class="text-xl font-semibold text-gray-900">Glorious Highlights</h2>
                </div>
                <span class="px-3 py-1 bg-yellow-50 text-yellow-600 text-sm rounded-full">
                    <?php echo count($glorious_highlights); ?> items
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($glorious_highlights) > 0): ?>
                            <?php foreach ($glorious_highlights as $index => $item): ?>
                                <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['title']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['uploaded_date']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button 
                                            onclick="editContent(<?php echo htmlspecialchars(json_encode($item)); ?>, 'glorious_highlights')"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            onclick="deleteContent('<?php echo $item['glory_id']; ?>', 'glorious_highlights')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No content available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal templates -->
    <!-- Add Content Type Modal -->
    <div id="addTypeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Add New Content</h2>
                <button 
                    onclick="closeModal('addTypeModal')"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <iconify-icon icon="lucide:x"></iconify-icon>
                </button>
            </div>
            <p class="text-gray-600 mb-4">Select the type of content you want to add:</p>
            <div class="space-y-3">
                <button 
                    onclick="showContentModal('video_sermons')"
                    class="w-full flex items-center p-3 bg-white hover:bg-indigo-50 rounded-lg border border-gray-200 transition-colors"
                >
                    <iconify-icon icon="lucide:video" class="text-indigo-600 mr-3"></iconify-icon>
                    <span class="font-medium">Video Sermon</span>
                </button>
                <button 
                    onclick="showContentModal('podcasts')"
                    class="w-full flex items-center p-3 bg-white hover:bg-indigo-50 rounded-lg border border-gray-200 transition-colors"
                >
                    <iconify-icon icon="lucide:headphones" class="text-indigo-600 mr-3"></iconify-icon>
                    <span class="font-medium">Podcast</span>
                </button>
                <button 
                    onclick="showContentModal('audio_sermons')"
                    class="w-full flex items-center p-3 bg-white hover:bg-indigo-50 rounded-lg border border-gray-200 transition-colors"
                >
                    <iconify-icon icon="lucide:music" class="text-indigo-600 mr-3"></iconify-icon>
                    <span class="font-medium">Audio Sermon</span>
                </button>
                <button 
                    onclick="showContentModal('glorious_highlights')"
                    class="w-full flex items-center p-3 bg-white hover:bg-indigo-50 rounded-lg border border-gray-200 transition-colors"
                >
                    <iconify-icon icon="lucide:star" class="text-indigo-600 mr-3"></iconify-icon>
                    <span class="font-medium">Glorious Highlight</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Add Content Modal -->
    <div id="addContentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 id="modalTitle" class="text-xl font-semibold text-gray-900">Add New Content</h2>
                <button 
                    onclick="closeModal('addContentModal')"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <iconify-icon icon="lucide:x"></iconify-icon>
                </button>
            </div>
            
            <form id="addContentForm" method="post" enctype="multipart/form-data" action="../../actions/studio/add.php">
                <input type="hidden" id="content_type" name="content_type" value="">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input
                            type="text"
                            name="title"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required
                        >
                    </div>
                    
                    <div id="descriptionField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            name="description"
                            rows="3"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>
                    
                    <div>
                        <label id="fileLabel" class="block text-sm font-medium text-gray-700">File</label>
                        <input
                            type="file"
                            name="file_path"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required
                        >
                    </div>
                    
                    <div id="thumbnailField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Thumbnail Image</label>
                        <input
                            type="file"
                            name="image_path"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                    
                    <div id="uploadDateField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Uploaded Date</label>
                        <input
                            type="text"
                            name="uploaded_date"
                            placeholder="e.g., January 15, 2024"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        onclick="closeModal('addContentModal')"
                        class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Add Content
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Content Modal -->
    <div id="editContentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 id="editModalTitle" class="text-xl font-semibold text-gray-900">Edit Content</h2>
                <button 
                    onclick="closeModal('editContentModal')"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <iconify-icon icon="lucide:x"></iconify-icon>
                </button>
            </div>
            
            <form id="editContentForm" method="post" enctype="multipart/form-data" action="../../actions/studio/edit.php">
                <input type="hidden" id="edit_content_type" name="content_type" value="">
                <input type="hidden" id="edit_content_id" name="content_id" value="">
                <input type="hidden" id="edit_id_field" name="id_field" value="">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input
                            type="text"
                            id="edit_title"
                            name="title"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required
                        >
                    </div>
                    
                    <div id="edit_descriptionField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            id="edit_description"
                            name="description"
                            rows="3"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>
                    
                    <div>
                        <label id="edit_fileLabel" class="block text-sm font-medium text-gray-700">File (Upload new to replace)</label>
                        <input
                            type="file"
                            name="file_path"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        <p class="mt-1 text-sm text-gray-500" id="current_file_path">Current file: </p>
                    </div>
                    
                    <div id="edit_thumbnailField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Thumbnail Image (Upload new to replace)</label>
                        <input
                            type="file"
                            name="image_path"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        <p class="mt-1 text-sm text-gray-500" id="current_image_path">Current image: </p>
                    </div>
                    
                    <div id="edit_uploadDateField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Uploaded Date</label>
                        <input
                            type="text"
                            id="edit_uploaded_date"
                            name="uploaded_date"
                            placeholder="e.g., January 15, 2024"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        onclick="closeModal('editContentModal')"
                        class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Update Content
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Confirm Deletion</h2>
                <button 
                    onclick="closeModal('deleteModal')"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <iconify-icon icon="lucide:x"></iconify-icon>
                </button>
            </div>
            
            <p class="text-gray-600 mb-4">Are you sure you want to delete this content? This action cannot be undone.</p>
            
            <form id="deleteForm" method="post" action="../../actions/studio/delete.php">
                <input type="hidden" id="delete_content_type" name="content_type" value="">
                <input type="hidden" id="delete_content_id" name="content_id" value="">
                <input type="hidden" id="delete_id_field" name="id_field" value="">
                
                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        onclick="closeModal('deleteModal')"
                        class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for modal functionality -->
    <script>
        // Show add content type selection modal
        function showAddModal() {
            document.getElementById('addTypeModal').classList.remove('hidden');
        }
        
        // Show content form modal based on selected type
        function showContentModal(contentType) {
            // Set the content type in the form
            document.getElementById('content_type').value = contentType;
            
            // Update modal title
            let modalTitle = document.getElementById('modalTitle');
            let fileLabel = document.getElementById('fileLabel');
            
            switch(contentType) {
                case 'video_sermons':
                    modalTitle.textContent = 'Add New Video Sermon';
                    fileLabel.textContent = 'Video File';
                    document.getElementById('descriptionField').classList.remove('hidden');
                    document.getElementById('thumbnailField').classList.remove('hidden');
                    document.getElementById('uploadDateField').classList.add('hidden');
                    break;
                case 'podcasts':
                    modalTitle.textContent = 'Add New Podcast';
                    fileLabel.textContent = 'Audio File';
                    document.getElementById('descriptionField').classList.remove('hidden');
                    document.getElementById('thumbnailField').classList.remove('hidden');
                    document.getElementById('uploadDateField').classList.add('hidden');
                    break;
                case 'audio_sermons':
                    modalTitle.textContent = 'Add New Audio Sermon';
                    fileLabel.textContent = 'Audio File';
                    document.getElementById('descriptionField').classList.remove('hidden');
                    document.getElementById('thumbnailField').classList.remove('hidden');
                    document.getElementById('uploadDateField').classList.add('hidden');
                    break;
                case 'glorious_highlights':
                    modalTitle.textContent = 'Add New Glorious Highlight';
                    fileLabel.textContent = 'Video File';
                    document.getElementById('descriptionField').classList.add('hidden');
                    document.getElementById('thumbnailField').classList.remove('hidden');
                    document.getElementById('uploadDateField').classList.remove('hidden');
                    break;
            }
            
            // Close the type selection modal and open the content form modal
            closeModal('addTypeModal');
            document.getElementById('addContentModal').classList.remove('hidden');
        }
        
        // Handle edit content modal
        function editContent(item, contentType) {
            // Set the content type and ID in the form
            document.getElementById('edit_content_type').value = contentType;
            
            // Set the appropriate ID field name based on content type
            let idField;
            switch(contentType) {
                case 'video_sermons':
                    idField = 'video_id';
                    document.getElementById('editModalTitle').textContent = 'Edit Video Sermon';
                    document.getElementById('edit_fileLabel').textContent = 'Video File (Upload new to replace)';
                    document.getElementById('edit_descriptionField').classList.remove('hidden');
                    document.getElementById('edit_thumbnailField').classList.remove('hidden');
                    document.getElementById('edit_uploadDateField').classList.add('hidden');
                    break;
                case 'podcasts':
                    idField = 'podcast_id';
                    document.getElementById('editModalTitle').textContent = 'Edit Podcast';
                    document.getElementById('edit_fileLabel').textContent = 'Audio File (Upload new to replace)';
                    document.getElementById('edit_descriptionField').classList.remove('hidden');
                    document.getElementById('edit_thumbnailField').classList.remove('hidden');
                    document.getElementById('edit_uploadDateField').classList.add('hidden');
                    break;
                case 'audio_sermons':
                    idField = 'audio_id';
                    document.getElementById('editModalTitle').textContent = 'Edit Audio Sermon';
                    document.getElementById('edit_fileLabel').textContent = 'Audio File (Upload new to replace)';
                    document.getElementById('edit_descriptionField').classList.remove('hidden');
                    document.getElementById('edit_thumbnailField').classList.remove('hidden');
                    document.getElementById('edit_uploadDateField').classList.add('hidden');
                    break;
                case 'glorious_highlights':
                    idField = 'glory_id';
                    document.getElementById('editModalTitle').textContent = 'Edit Glorious Highlight';
                    document.getElementById('edit_fileLabel').textContent = 'Video File (Upload new to replace)';
                    document.getElementById('edit_descriptionField').classList.add('hidden');
                    document.getElementById('edit_thumbnailField').classList.remove('hidden');
                    document.getElementById('edit_uploadDateField').classList.remove('hidden');
                    break;
            }
            
            document.getElementById('edit_id_field').value = idField;
            document.getElementById('edit_content_id').value = item[idField];
            
            // Fill in form fields with current values
            document.getElementById('edit_title').value = item.title || '';
            
            if (document.getElementById('edit_description')) {
                document.getElementById('edit_description').value = item.description || '';
            }
            
            if (document.getElementById('edit_uploaded_date') && item.uploaded_date) {
                document.getElementById('edit_uploaded_date').value = item.uploaded_date;
            }
            
            // Show current file paths
            document.getElementById('current_file_path').textContent = 'Current file: ' + (item.file_path || 'None');
            
            if (document.getElementById('current_image_path')) {
                document.getElementById('current_image_path').textContent = 'Current image: ' + (item.image_path || 'None');
            }
            
            // Show the modal
            document.getElementById('editContentModal').classList.remove('hidden');
        }
        
        // Handle delete confirmation
        function deleteContent(contentId, contentType) {
            // Set the content type and ID in the form
            document.getElementById('delete_content_type').value = contentType;
            document.getElementById('delete_content_id').value = contentId;
            
            // Set the appropriate ID field name based on content type
            let idField;
            switch(contentType) {
                case 'video_sermons': idField = 'video_id'; break;
                case 'podcasts': idField = 'podcast_id'; break;
                case 'audio_sermons': idField = 'audio_id'; break;
                case 'glorious_highlights': idField = 'glory_id'; break;
            }
            document.getElementById('delete_id_field').value = idField;
            
            // Show the modal
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        // Close modal function
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        });
        
        // Prevent event propagation for modal content
        const modalContents = document.querySelectorAll('.fixed > div');
        modalContents.forEach(content => {
            content.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
        
        // Handle dynamic file upload button styling
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                const parent = input.parentElement;
                const label = parent.querySelector('label');
                
                input.addEventListener('change', function() {
                    if (this.files.length) {
                        parent.classList.add('has-file');
                        if (label) {
                            label.classList.add('text-indigo-600');
                        }
                    } else {
                        parent.classList.remove('has-file');
                        if (label) {
                            label.classList.remove('text-indigo-600');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
                            