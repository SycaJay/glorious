<?php
// Include your database connection here
include('../../db/tvconfig.php');

// Pagination settings
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Fetch total count
$countQuery = "SELECT COUNT(*) as total FROM teenstv";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $perPage);

// Fetch TeensTV data from database with pagination
$query = "SELECT * FROM teenstv ORDER BY created_at DESC LIMIT $offset, $perPage";
$result = mysqli_query($conn, $query);
$teensTV = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $teensTV[] = $row;
    }
    mysqli_free_result($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage TeensTV</title>
    
    <!-- React and Tailwind -->
    <script src="https://unpkg.com/react@17/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            300: '#f9a8d4',
                            400: '#f472b6',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                            800: '#9d174d',
                            900: '#831843',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Include Lucide icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Include modal styles -->
    <style>
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            background-color: white;
            border-radius: 1rem;
            width: 95%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            margin: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .content-card {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        
        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-left-color: #ec4899;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            border-radius: 0.375rem;
        }
        
        .pagination-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .pagination-item:hover:not(.active) {
            background-color: #f3f4f6;
        }
        
        .pagination-item.active {
            background-color: #ec4899;
            color: white;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect, useRef } = React;

        function ManageTeensTV() {
            const [teensData, setTeensData] = useState(<?php echo json_encode($teensTV); ?>);
            const [showAddModal, setShowAddModal] = useState(false);
            const [showEditModal, setShowEditModal] = useState(false);
            const [currentItem, setCurrentItem] = useState(null);
            const [isSubmitting, setIsSubmitting] = useState(false);
            const [uploadProgress, setUploadProgress] = useState(null);
            const [uploadStatus, setUploadStatus] = useState('');
            const [currentPage, setCurrentPage] = useState(<?php echo $page; ?>);
            const [totalPages, setTotalPages] = useState(<?php echo $totalPages; ?>);
            const addFormRef = useRef(null);
            const CHUNK_SIZE = 5 * 1024 * 1024;
            
            const handleAddContent = () => {
                setShowAddModal(true);
            };
            
            const handleEditContent = (item) => {
                setCurrentItem(item);
                setShowEditModal(true);
            };
            
            const handleDelete = async (id) => {
                if (!confirm('Are you sure you want to delete this content?')) return;
                
                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    const response = await fetch('../../actions/teens/delete.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        setTeensData(teensData.filter(item => item.teens_id !== id));
                        alert('Content deleted successfully!');
                    } else {
                        const errorText = await response.text();
                        alert('Error deleting content: ' + errorText);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting. Please try again.');
                }
            };
            
            const closeModals = () => {
                setShowAddModal(false);
                setShowEditModal(false);
                setCurrentItem(null);
                setIsSubmitting(false);
                setUploadProgress(null);
                setUploadStatus('');
            };
            
            const refreshData = async () => {
                window.location.reload();
            };

            const changePage = (page) => {
                if (page < 1 || page > totalPages) return;
                window.location.href = `?page=${page}`;
            };

            useEffect(() => {
                const handleOutsideClick = (e) => {
                    if (e.target.classList.contains('modal-backdrop')) {
                        closeModals();
                    }
                };

                document.addEventListener('mousedown', handleOutsideClick);
                return () => {
                    document.removeEventListener('mousedown', handleOutsideClick);
                };
            }, []);

            const getBadgeColor = (type) => {
                switch(type) {
                    case 'Teens Service': return 'bg-purple-100 text-purple-800';
                    case 'Trending Now': return 'bg-blue-100 text-blue-800';
                    case 'Live Now': return 'bg-red-100 text-red-800';
                    default: return 'bg-gray-100 text-gray-800';
                }
            };

            return (
                <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 sm:p-6">
                    <div className="max-w-7xl mx-auto">
                        {/* Header */}
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">TeensTV Content</h1>
                                <p className="text-gray-500 mt-1">Manage all your TeensTV content in one place</p>
                            </div>
                            <div className="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                <button 
                                    onClick={handleAddContent}
                                    className="flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-all shadow-sm hover:shadow-md"
                                >
                                    <span className="lucide-plus"></span>
                                    Add New
                                </button>
                                <a href="index.php">
                                    <button className="flex items-center justify-center gap-2 px-5 py-2.5 border border-gray-200 bg-white rounded-lg hover:bg-gray-50 transition-all shadow-sm hover:shadow-md">
                                        <span className="lucide-arrow-left"></span>
                                        Dashboard
                                    </button>
                                </a>
                            </div>
                        </div>

                        {/* Stats Cards */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Total Content</p>
                                        <h3 className="text-2xl font-bold text-gray-900 mt-1"><?php echo $totalItems; ?></h3>
                                    </div>
                                    <div className="p-3 rounded-full bg-primary-50 text-primary-600">
                                        <span className="lucide-film text-xl"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Current Page</p>
                                        <h3 className="text-2xl font-bold text-gray-900 mt-1">{currentPage} of {totalPages}</h3>
                                    </div>
                                    <div className="p-3 rounded-full bg-blue-50 text-blue-600">
                                        <span className="lucide-layers text-xl"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Showing</p>
                                        <h3 className="text-2xl font-bold text-gray-900 mt-1"><?php echo min($perPage, $totalItems - ($page - 1) * $perPage); ?> items</h3>
                                    </div>
                                    <div className="p-3 rounded-full bg-green-50 text-green-600">
                                        <span className="lucide-eye text-xl"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Content Grid */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div className="px-6 py-5 border-b border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <span className="lucide-list-video text-primary-600 text-2xl"></span>
                                        <h2 className="text-xl font-semibold text-gray-900">Content Library</h2>
                                    </div>
                                    <div className="relative">
                                        <input 
                                            type="text" 
                                            placeholder="Search content..." 
                                            className="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 w-full sm:w-64"
                                        />
                                        <span className="lucide-search absolute left-3 top-2.5 text-gray-400"></span>
                                    </div>
                                </div>
                            </div>
                            
                            {teensData.length > 0 ? (
                                <div className="content-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                                    {teensData.map(item => (
                                        <div key={item.teens_id} className="content-card bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-all">
                                            <div className="p-5">
                                                <div className="flex justify-between items-start">
                                                    <h3 className="font-bold text-lg text-gray-900 truncate">{item.title}</h3>
                                                    <span className={`badge ${getBadgeColor(item.content_type)}`}>
                                                        {item.content_type}
                                                    </span>
                                                </div>
                                                <p className="text-gray-500 text-sm mt-2 line-clamp-2">{item.description}</p>
                                                
                                                <div className="mt-4 pt-4 border-t border-gray-100">
                                                    <div className="flex items-center text-sm text-gray-500 mb-2">
                                                        <span className="lucide-calendar mr-2"></span>
                                                        {new Date(item.created_at).toLocaleDateString()}
                                                    </div>
                                                    {item.is_scheduled && (
                                                        <div>
                                                            <div className="flex items-center text-sm text-gray-500 mb-2">
                                                                <span className="lucide-clock mr-2"></span>
                                                                {item.schedule_start ? new Date(item.schedule_start).toLocaleString() : 'No schedule'}
                                                            </div>
                                                            <div className="flex items-center text-sm text-gray-500">
                                                                <span className="lucide-timer mr-2"></span>
                                                                Countdown starts: {item.countdown_start_offset ? `${item.countdown_start_offset} minutes before` : 'Not set'}
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            
                                            <div className="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                                                <button 
                                                    onClick={() => handleEditContent(item)}
                                                    className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-white bg-primary-600 rounded-md hover:bg-primary-700 transition-colors"
                                                >
                                                    <span className="lucide-edit-2 w-4 h-4"></span>
                                                    Edit
                                                </button>
                                                <button 
                                                    onClick={() => handleDelete(item.teens_id)}
                                                    className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors"
                                                >
                                                    <span className="lucide-trash-2 w-4 h-4"></span>
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-12 text-center">
                                    <div className="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <span className="lucide-folder-x text-gray-400 text-3xl"></span>
                                    </div>
                                    <h3 className="text-lg font-medium text-gray-900">No content found</h3>
                                    <p className="text-gray-500 mt-1">Add your first content to get started</p>
                                    <button 
                                        onClick={handleAddContent}
                                        className="mt-4 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
                                    >
                                        Add Content
                                    </button>
                                </div>
                            )}
                            
                            {/* Pagination */}
                            {totalPages > 1 && (
                                <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                                    <div className="text-sm text-gray-500">
                                        Showing page {currentPage} of {totalPages}
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <button 
                                            onClick={() => changePage(currentPage - 1)}
                                            disabled={currentPage === 1}
                                            className={`pagination-item ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}`}
                                        >
                                            <span className="lucide-chevron-left"></span>
                                        </button>
                                        
                                        {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                                            let pageNum;
                                            if (totalPages <= 5) {
                                                pageNum = i + 1;
                                            } else if (currentPage <= 3) {
                                                pageNum = i + 1;
                                            } else if (currentPage >= totalPages - 2) {
                                                pageNum = totalPages - 4 + i;
                                            } else {
                                                pageNum = currentPage - 2 + i;
                                            }
                                            
                                            return (
                                                <button
                                                    key={pageNum}
                                                    onClick={() => changePage(pageNum)}
                                                    className={`pagination-item ${currentPage === pageNum ? 'active' : 'hover:bg-gray-100'}`}
                                                >
                                                    {pageNum}
                                                </button>
                                            );
                                        })}
                                        
                                        <button 
                                            onClick={() => changePage(currentPage + 1)}
                                            disabled={currentPage === totalPages}
                                            className={`pagination-item ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}`}
                                        >
                                            <span className="lucide-chevron-right"></span>
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                    
                    {/* Add Content Modal */}
                    {showAddModal && (
                        <div className="modal-backdrop">
                            <div className="modal-content p-6">
                                <div className="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 className="text-2xl font-bold text-gray-900">Add New Content</h3>
                                        <p className="text-gray-500 mt-1">Fill in the details below</p>
                                    </div>
                                    <button onClick={closeModals} className="text-gray-400 hover:text-gray-500 p-1 rounded-full hover:bg-gray-100">
                                        <span className="lucide-x text-xl"></span>
                                    </button>
                                </div>
                                <form 
                                    id="addContentForm"
                                    ref={addFormRef}
                                    action="../../actions/teens/add.php" 
                                    method="post" 
                                    encType="multipart/form-data"
                                    onSubmit={async (e) => {
                                        e.preventDefault();
                                        if (isSubmitting) return;
                                        const form = e.target;
                                        const videoFile = form.querySelector('#video-upload').files[0];
                                        const audioFile = form.querySelector('#audio-upload').files[0];
                                        const isVideoLarge = videoFile && videoFile.size > CHUNK_SIZE;
                                        const isAudioLarge = audioFile && audioFile.size > CHUNK_SIZE;
                                        const buildFormFields = (fd) => {
                                            fd.append('content_type', form.content_type.value);
                                            fd.append('title', form.title.value);
                                            fd.append('description', form.description.value);
                                            fd.append('is_scheduled', form.is_scheduled.checked ? '1' : '');
                                            if (form.schedule_start.value) fd.append('schedule_start', form.schedule_start.value);
                                            if (form.schedule_end.value) fd.append('schedule_end', form.schedule_end.value);
                                            fd.append('countdown_start_offset', form.countdown_start_offset.value);
                                        };
                                        const doChunkedVideo = async () => {
                                            setIsSubmitting(true);
                                            setUploadProgress(0);
                                            setUploadStatus('Uploading video: 0%');
                                            const totalChunks = Math.ceil(videoFile.size / CHUNK_SIZE);
                                            for (let i = 0; i < totalChunks; i++) {
                                                const chunk = videoFile.slice(i * CHUNK_SIZE, Math.min((i + 1) * CHUNK_SIZE, videoFile.size));
                                                const fd = new FormData();
                                                fd.append('video_file', chunk);
                                                fd.append('chunk', i);
                                                fd.append('totalChunks', totalChunks);
                                                fd.append('fileName', videoFile.name);
                                                fd.append('uploadType', 'video');
                                                buildFormFields(fd);
                                                if (i + 1 === totalChunks && audioFile) fd.append('audio_file', audioFile);
                                                const res = await fetch('../../actions/teens/add.php', { method: 'POST', body: fd });
                                                if (!res.ok) throw new Error(await res.text());
                                                const pct = Math.round(((i + 1) / totalChunks) * 100);
                                                setUploadProgress(pct);
                                                setUploadStatus('Uploading video: ' + pct + '%');
                                            }
                                        };
                                        const doChunkedAudio = async () => {
                                            setIsSubmitting(true);
                                            setUploadProgress(0);
                                            setUploadStatus('Uploading audio: 0%');
                                            const totalChunks = Math.ceil(audioFile.size / CHUNK_SIZE);
                                            for (let i = 0; i < totalChunks; i++) {
                                                const chunk = audioFile.slice(i * CHUNK_SIZE, Math.min((i + 1) * CHUNK_SIZE, audioFile.size));
                                                const fd = new FormData();
                                                fd.append('audio_file', chunk);
                                                fd.append('chunk', i);
                                                fd.append('totalChunks', totalChunks);
                                                fd.append('fileName', audioFile.name);
                                                fd.append('uploadType', 'audio');
                                                buildFormFields(fd);
                                                if (i + 1 === totalChunks && videoFile) fd.append('video_file', videoFile);
                                                const res = await fetch('../../actions/teens/add.php', { method: 'POST', body: fd });
                                                if (!res.ok) throw new Error(await res.text());
                                                const pct = Math.round(((i + 1) / totalChunks) * 100);
                                                setUploadProgress(pct);
                                                setUploadStatus('Uploading audio: ' + pct + '%');
                                            }
                                        };
                                        try {
                                            if (isVideoLarge) {
                                                await doChunkedVideo();
                                                closeModals();
                                                refreshData();
                                                alert('Content added successfully!');
                                            } else if (isAudioLarge) {
                                                await doChunkedAudio();
                                                closeModals();
                                                refreshData();
                                                alert('Content added successfully!');
                                            } else {
                                                setIsSubmitting(true);
                                                const formData = new FormData(form);
                                                const response = await fetch('../../actions/teens/add.php', { method: 'POST', body: formData });
                                                if (response.ok) {
                                                    closeModals();
                                                    refreshData();
                                                    alert('Content added successfully!');
                                                } else {
                                                    const err = await response.text();
                                                    alert('Error adding content: ' + err);
                                                }
                                            }
                                        } catch (error) {
                                            setUploadStatus('Upload failed: ' + (error.message || error));
                                            setUploadProgress(null);
                                            alert('An error occurred. Please try again.');
                                        } finally {
                                            setIsSubmitting(false);
                                            setUploadProgress(null);
                                            setUploadStatus('');
                                        }
                                    }}
                                >
                                    <div className="space-y-5">
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Content Type<span className="text-red-500">*</span>
                                                </label>
                                                <select 
                                                    name="content_type" 
                                                    required
                                                    className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                >
                                                    <option value="">Select type</option>
                                                    <option value="Teens Service">Teens Service</option>
                                                    <option value="Trending Now">Trending Now</option>
                                                    <option value="Live Now">Live Now</option>
                                                </select>
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Title<span className="text-red-500">*</span>
                                                </label>
                                                <input 
                                                    type="text" 
                                                    name="title" 
                                                    required
                                                    className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                />
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Description
                                            </label>
                                            <textarea 
                                                name="description"
                                                rows="3"
                                                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                            ></textarea>
                                        </div>
                                        
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Video File (optional)
                                                </label>
                                                <div className="flex items-center gap-3">
                                                    <label className="flex-1 cursor-pointer">
                                                        <input 
                                                            type="file" 
                                                            name="video_file"
                                                            accept="video/*"
                                                            className="hidden"
                                                            id="video-upload"
                                                        />
                                                        <div className="w-full px-4 py-10 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center hover:border-primary-500 transition-colors">
                                                            <span className="lucide-upload-cloud text-2xl text-primary-500 mb-2"></span>
                                                            <span className="text-sm text-gray-500">Click to upload video</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Audio File (optional)
                                                </label>
                                                <div className="flex items-center gap-3">
                                                    <label className="flex-1 cursor-pointer">
                                                        <input 
                                                            type="file" 
                                                            name="audio_file"
                                                            accept="audio/*"
                                                            className="hidden"
                                                            id="audio-upload"
                                                        />
                                                        <div className="w-full px-4 py-10 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center hover:border-primary-500 transition-colors">
                                                            <span className="lucide-upload-cloud text-2xl text-primary-500 mb-2"></span>
                                                            <span className="text-sm text-gray-500">Click to upload audio</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="pt-4 border-t border-gray-200">
                                            <div className="flex items-center mb-4">
                                                <input 
                                                    type="checkbox" 
                                                    name="is_scheduled"
                                                    id="is_scheduled"
                                                    className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                                />
                                                <label htmlFor="is_scheduled" className="ml-2 block text-sm text-gray-700">
                                                    Schedule this content
                                                </label>
                                            </div>
                                            
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                                        Schedule Start (optional)
                                                    </label>
                                                    <input 
                                                        type="datetime-local"
                                                        name="schedule_start"
                                                        className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                    />
                                                </div>

                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                                        Schedule End (optional)
                                                    </label>
                                                    <input 
                                                        type="datetime-local"
                                                        name="schedule_end"
                                                        className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                    />
                                                </div>
                                            </div>

                                            <div className="mt-4">
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Countdown Timer Start (optional)
                                                </label>
                                                <select 
                                                    name="countdown_start_offset"
                                                    className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                >
                                                    <option value="0">No Countdown</option>
                                                    <option value="15">15 Minutes Before</option>
                                                    <option value="30">30 Minutes Before</option>
                                                    <option value="60">1 Hour Before</option>
                                                    <option value="120">2 Hours Before</option>
                                                </select>
                                                <p className="mt-1 text-xs text-gray-500">
                                                    Select when the countdown timer should start before the scheduled start time. 
                                                    This timer will be displayed on the TeensTV page for scheduled videos, 
                                                    showing the time remaining until the video premieres. 
                                                    Choose an appropriate duration to build anticipation among viewers. 
                                                    Multiple admins can set this, so coordinate to ensure consistency.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {uploadProgress != null && (
                                        <div className="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                            <div className="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                <div className="bg-primary-600 h-2.5 rounded-full transition-all duration-300" style={{ width: uploadProgress + '%' }}></div>
                                            </div>
                                            <p className="text-sm text-gray-600 mt-1">{uploadStatus}</p>
                                        </div>
                                    )}
                                    
                                    <div className="mt-8 flex flex-wrap justify-end gap-3">
                                        <button
                                            type="button"
                                            onClick={closeModals}
                                            className="px-5 py-2.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            disabled={isSubmitting}
                                            className={`px-5 py-2.5 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ${isSubmitting ? 'opacity-75 cursor-not-allowed' : ''}`}
                                        >
                                            {isSubmitting ? (
                                                <React.Fragment>
                                                    <span className="lucide-loader-circle animate-spin mr-2"></span>
                                                    {uploadProgress != null ? uploadStatus : 'Adding...'}
                                                </React.Fragment>
                                            ) : (
                                                <React.Fragment>
                                                    <span className="lucide-check mr-2"></span>
                                                    Add Content
                                                </React.Fragment>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                    
                    {/* Edit Content Modal */}
                    {showEditModal && currentItem && (
                        <div className="modal-backdrop">
                            <div className="modal-content p-6">
                                <div className="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 className="text-2xl font-bold text-gray-900">Edit Content</h3>
                                        <p className="text-gray-500 mt-1">Update the content details</p>
                                    </div>
                                    <button onClick={closeModals} className="text-gray-400 hover:text-gray-500 p-1 rounded-full hover:bg-gray-100">
                                        <span className="lucide-x text-xl"></span>
                                    </button>
                                </div>
                                <form 
                                    id="editContentForm"
                                    action="../../actions/teens/edit.php" 
                                    method="post" 
                                    encType="multipart/form-data"
                                    onSubmit={async (e) => {
                                        e.preventDefault();
                                        if (isSubmitting) return;
                                        const form = e.target;
                                        const videoFile = form.querySelector('#edit-video-upload').files[0];
                                        const audioFile = form.querySelector('#edit-audio-upload').files[0];
                                        const isVideoLarge = videoFile && videoFile.size > CHUNK_SIZE;
                                        const isAudioLarge = audioFile && audioFile.size > CHUNK_SIZE;
                                        const buildFormFields = (fd) => {
                                            fd.append('teens_id', currentItem.teens_id);
                                            fd.append('content_type', form.content_type.value);
                                            fd.append('title', form.title.value);
                                            fd.append('description', form.description.value);
                                            fd.append('is_scheduled', form.is_scheduled.checked ? '1' : '');
                                            if (form.schedule_start.value) fd.append('schedule_start', form.schedule_start.value);
                                            if (form.schedule_end.value) fd.append('schedule_end', form.schedule_end.value);
                                            fd.append('countdown_start_offset', form.countdown_start_offset.value);
                                        };
                                        const doChunkedVideo = async () => {
                                            setIsSubmitting(true);
                                            setUploadProgress(0);
                                            setUploadStatus('Uploading video: 0%');
                                            const totalChunks = Math.ceil(videoFile.size / CHUNK_SIZE);
                                            for (let i = 0; i < totalChunks; i++) {
                                                const chunk = videoFile.slice(i * CHUNK_SIZE, Math.min((i + 1) * CHUNK_SIZE, videoFile.size));
                                                const fd = new FormData();
                                                fd.append('video_file', chunk);
                                                fd.append('chunk', i);
                                                fd.append('totalChunks', totalChunks);
                                                fd.append('fileName', videoFile.name);
                                                fd.append('uploadType', 'video');
                                                buildFormFields(fd);
                                                if (i + 1 === totalChunks && audioFile) fd.append('audio_file', audioFile);
                                                const res = await fetch('../../actions/teens/edit.php', { method: 'POST', body: fd });
                                                if (!res.ok) throw new Error(await res.text());
                                                const pct = Math.round(((i + 1) / totalChunks) * 100);
                                                setUploadProgress(pct);
                                                setUploadStatus('Uploading video: ' + pct + '%');
                                            }
                                        };
                                        const doChunkedAudio = async () => {
                                            setIsSubmitting(true);
                                            setUploadProgress(0);
                                            setUploadStatus('Uploading audio: 0%');
                                            const totalChunks = Math.ceil(audioFile.size / CHUNK_SIZE);
                                            for (let i = 0; i < totalChunks; i++) {
                                                const chunk = audioFile.slice(i * CHUNK_SIZE, Math.min((i + 1) * CHUNK_SIZE, audioFile.size));
                                                const fd = new FormData();
                                                fd.append('audio_file', chunk);
                                                fd.append('chunk', i);
                                                fd.append('totalChunks', totalChunks);
                                                fd.append('fileName', audioFile.name);
                                                fd.append('uploadType', 'audio');
                                                buildFormFields(fd);
                                                if (i + 1 === totalChunks && videoFile) fd.append('video_file', videoFile);
                                                const res = await fetch('../../actions/teens/edit.php', { method: 'POST', body: fd });
                                                if (!res.ok) throw new Error(await res.text());
                                                const pct = Math.round(((i + 1) / totalChunks) * 100);
                                                setUploadProgress(pct);
                                                setUploadStatus('Uploading audio: ' + pct + '%');
                                            }
                                        };
                                        try {
                                            if (isVideoLarge) {
                                                await doChunkedVideo();
                                                closeModals();
                                                refreshData();
                                                alert('Content updated successfully!');
                                            } else if (isAudioLarge) {
                                                await doChunkedAudio();
                                                closeModals();
                                                refreshData();
                                                alert('Content updated successfully!');
                                            } else {
                                                setIsSubmitting(true);
                                                const formData = new FormData(form);
                                                const response = await fetch('../../actions/teens/edit.php', { method: 'POST', body: formData });
                                                if (response.ok) {
                                                    closeModals();
                                                    refreshData();
                                                    alert('Content updated successfully!');
                                                } else {
                                                    const err = await response.text();
                                                    alert('Error updating content: ' + err);
                                                }
                                            }
                                        } catch (error) {
                                            setUploadStatus('Upload failed: ' + (error.message || error));
                                            setUploadProgress(null);
                                            alert('An error occurred. Please try again.');
                                        } finally {
                                            setIsSubmitting(false);
                                            setUploadProgress(null);
                                            setUploadStatus('');
                                        }
                                    }}
                                >
                                    <input type="hidden" name="teens_id" value={currentItem.teens_id} />
                                    
                                    <div className="space-y-5">
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Content Type<span className="text-red-500">*</span>
                                                </label>
                                                <select 
                                                    name="content_type"
                                                    required
                                                    defaultValue={currentItem.content_type}
                                                    className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                >
                                                    <option value="Teens Service">Teens Service</option>
                                                    <option value="Trending Now">Trending Now</option>
                                                    <option value="Live Now">Live Now</option>
                                                </select>
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Title<span className="text-red-500">*</span>
                                                </label>
                                                <input 
                                                    type="text" 
                                                    name="title"
                                                    required
                                                    defaultValue={currentItem.title}
                                                    className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                />
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Description
                                            </label>
                                            <textarea 
                                                name="description"
                                                rows="3"
                                                defaultValue={currentItem.description}
                                                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                            ></textarea>
                                        </div>
                                        
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Video File (optional)
                                                </label>
                                                {currentItem.video_path && (
                                                    <div className="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                        <div className="flex items-center justify-between">
                                                            <div className="flex items-center gap-2">
                                                                <span className="lucide-video text-primary-500"></span>
                                                                <span className="text-sm font-medium text-gray-700 truncate max-w-xs">
                                                                    {currentItem.video_path.split('/').pop()}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                                <div className="flex items-center gap-3">
                                                    <label className="flex-1 cursor-pointer">
                                                        <input 
                                                            type="file" 
                                                            name="video_file"
                                                            accept="video/*"
                                                            className="hidden"
                                                            id="edit-video-upload"
                                                        />
                                                        <div className="w-full px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center hover:border-primary-500 transition-colors">
                                                            <span className="lucide-upload-cloud text-xl text-primary-500 mb-1"></span>
                                                            <span className="text-xs text-gray-500">Click to {currentItem.video_path ? 'replace' : 'upload'} video</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Audio File (optional)
                                                </label>
                                                {currentItem.audio_path && (
                                                    <div className="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                        <div className="flex items-center justify-between">
                                                            <div className="flex items-center gap-2">
                                                                <span className="lucide-music text-primary-500"></span>
                                                                <span className="text-sm font-medium text-gray-700 truncate max-w-xs">
                                                                    {currentItem.audio_path.split('/').pop()}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                                <div className="flex items-center gap-3">
                                                    <label className="flex-1 cursor-pointer">
                                                        <input 
                                                            type="file" 
                                                            name="audio_file"
                                                            accept="audio/*"
                                                            className="hidden"
                                                            id="edit-audio-upload"
                                                        />
                                                        <div className="w-full px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center hover:border-primary-500 transition-colors">
                                                            <span className="lucide-upload-cloud text-xl text-primary-500 mb-1"></span>
                                                            <span className="text-xs text-gray-500">Click to {currentItem.audio_path ? 'replace' : 'upload'} audio</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="pt-4 border-t border-gray-200">
                                            <div className="flex items-center mb-4">
                                                <input 
                                                    type="checkbox" 
                                                    name="is_scheduled"
                                                    id="edit_is_scheduled"
                                                    defaultChecked={currentItem.is_scheduled}
                                                    className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                                />
                                                <label htmlFor="edit_is_scheduled" className="ml-2 block text-sm text-gray-700">
                                                    Schedule this content
                                                </label>
                                            </div>
                                            
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                                        Schedule Start (optional)
                                                    </label>
                                                    <input 
                                                        type="datetime-local"
                                                        name="schedule_start"
                                                        defaultValue={currentItem.schedule_start ? new Date(currentItem.schedule_start).toISOString().slice(0,16) : ''}
                                                        className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                    />
                                                </div>

                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                                        Schedule End (optional)
                                                    </label>
                                                    <input 
                                                        type="datetime-local"
                                                        name="schedule_end"
                                                        defaultValue={currentItem.schedule_end ? new Date(currentItem.schedule_end).toISOString().slice(0,16) : ''}
                                                        className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                    />
                                                </div>
                                            </div>

                                            <div className="mt-4">
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Countdown Timer Start (optional)
                                                </label>
                                                <select 
                                                    name="countdown_start_offset"
                                                    defaultValue={currentItem.countdown_start_offset || 0}
                                                    className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                >
                                                    <option value="0">No Countdown</option>
                                                    <option value="15">15 Minutes Before</option>
                                                    <option value="30">30 Minutes Before</option>
                                                    <option value="60">1 Hour Before</option>
                                                    <option value="120">2 Hours Before</option>
                                                </select>
                                                <p className="mt-1 text-xs text-gray-500">
                                                    Select when the countdown timer should start before the scheduled start time. 
                                                    This timer will be displayed on the TeensTV page for scheduled videos, 
                                                    showing the time remaining until the video premieres. 
                                                    Choose an appropriate duration to build anticipation among viewers. 
                                                    Multiple admins can set this, so coordinate to ensure consistency.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {uploadProgress != null && (
                                        <div className="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                            <div className="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                <div className="bg-primary-600 h-2.5 rounded-full transition-all duration-300" style={{ width: uploadProgress + '%' }}></div>
                                            </div>
                                            <p className="text-sm text-gray-600 mt-1">{uploadStatus}</p>
                                        </div>
                                    )}
                                    
                                    <div className="mt-8 flex flex-wrap justify-end gap-3">
                                        <button
                                            type="button"
                                            onClick={closeModals}
                                            className="px-5 py-2.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            disabled={isSubmitting}
                                            className={`px-5 py-2.5 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ${isSubmitting ? 'opacity-75 cursor-not-allowed' : ''}`}
                                        >
                                            {isSubmitting ? (
                                                <React.Fragment>
                                                    <span className="lucide-loader-circle animate-spin mr-2"></span>
                                                    {uploadProgress != null ? uploadStatus : 'Updating...'}
                                                </React.Fragment>
                                            ) : (
                                                <React.Fragment>
                                                    <span className="lucide-check mr-2"></span>
                                                    Update Content
                                                </React.Fragment>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            );
        }

        ReactDOM.render(<ManageTeensTV />, document.getElementById('root'));
    </script>

    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>