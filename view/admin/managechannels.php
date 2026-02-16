<?php
// Include your database connection here
include('../../db/config.php');

// Fetch channels from database
$query = "SELECT * FROM channels ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$channels = [];
while($row = mysqli_fetch_assoc($result)) {
    $channels[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Channels</title>
    
    <!-- React and Tailwind -->
    <script src="https://unpkg.com/react@17/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Include Lucide icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div id="root"></div>

    <script type="text/babel">
        const { useState } = React;
        const { Tv, Search, PlusCircle, ArrowLeft, Activity, Trash2 } = lucide;

        // Get PHP channels data
        const channelsData = <?php echo json_encode($channels); ?>;

        function ManageChannels() {
            const [channels, setChannels] = useState(channelsData);
            const [searchTerm, setSearchTerm] = useState('');
            const [editingChannel, setEditingChannel] = useState(null);
            const [isModalOpen, setIsModalOpen] = useState(false);

            const handleDelete = async (channelId) => {
                if (confirm('Are you sure you want to delete this channel?')) {
                    try {
                        const response = await fetch('delete_channel.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: channelId }),
                        });
                        
                        if (response.ok) {
                            setChannels(channels.filter(channel => channel.id !== channelId));
                        } else {
                            alert('Failed to delete channel');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the channel');
                    }
                }
            };

            const handleEdit = (channel) => {
                setEditingChannel(channel);
                setIsModalOpen(true);
            };

            const handleSave = async (updatedChannel) => {
                try {
                    const response = await fetch('update_channel.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(updatedChannel),
                    });

                    if (response.ok) {
                        setChannels(channels.map(channel => 
                            channel.id === updatedChannel.id ? updatedChannel : channel
                        ));
                        setIsModalOpen(false);
                    } else {
                        alert('Failed to update channel');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the channel');
                }
            };

            const filteredChannels = channels.filter(channel =>
                channel.name.toLowerCase().includes(searchTerm.toLowerCase())
            );

            return (
                <div className="min-h-screen bg-gradient-to-br from-emerald-50 to-teal-50 p-6">
                    <div className="max-w-7xl mx-auto">
                        {/* Header */}
                        <div className="flex justify-between items-center mb-8">
                            <div>
                                <h1 className="text-3xl font-bold text-emerald-900">Manage Channels</h1>
                                <p className="text-emerald-600 mt-1">Monitor and update channel settings</p>
                            </div>
                            <div className="flex gap-4">
                                <a href="add_channel.php" className="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                    <PlusCircle size={20} />
                                    Add Channel
                                </a>
                                <button className="flex items-center gap-2 px-4 py-2 border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors">
                                    <ArrowLeft size={20} />
                                    Back
                                </button>
                            </div>
                        </div>

                        {/* Search */}
                        <div className="bg-white rounded-lg shadow-sm p-4 mb-6">
                            <div className="relative">
                                <Search className="absolute left-3 top-2.5 text-gray-400" size={20} />
                                <input
                                    type="text"
                                    placeholder="Search channels..."
                                    className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                />
                            </div>
                        </div>

                        {/* Channels Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {filteredChannels.map((channel) => (
                                <div key={channel.id} className="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow p-6">
                                    <div className="flex justify-between items-start mb-4">
                                        <div className="flex items-center gap-3">
                                            <div className="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                                                <Tv className="text-emerald-600" size={24} />
                                            </div>
                                            <div>
                                                <h3 className="font-semibold text-lg text-gray-900">{channel.name}</h3>
                                                <p className="text-sm text-gray-500">{channel.description}</p>
                                            </div>
                                        </div>
                                        <span className="flex items-center gap-1 text-emerald-600">
                                            <Activity size={16} />
                                            {channel.status}
                                        </span>
                                    </div>
                                    <div className="space-y-2 mb-4">
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-500">Viewers</span>
                                            <span className="text-gray-900">{channel.viewers}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-500">Stream Quality</span>
                                            <span className="text-gray-900">{channel.quality}</span>
                                        </div>
                                    </div>
                                    <div className="flex justify-end gap-2">
                                        <button 
                                            onClick={() => handleEdit(channel)}
                                            className="px-4 py-2 text-sm text-emerald-600 hover:bg-emerald-50 rounded"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            onClick={() => handleDelete(channel.id)}
                                            className="px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded flex items-center gap-1"
                                        >
                                            <Trash2 size={16} />
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Edit Modal */}
                    {isModalOpen && (
                        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
                            <div className="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                                <h2 className="text-xl font-bold mb-4">Edit Channel</h2>
                                <form onSubmit={(e) => {
                                    e.preventDefault();
                                    handleSave(editingChannel);
                                }}>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Name</label>
                                            <input
                                                type="text"
                                                value={editingChannel.name}
                                                onChange={(e) => setEditingChannel({ ...editingChannel, name: e.target.value })}
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Description</label>
                                            <input
                                                type="text"
                                                value={editingChannel.description}
                                                onChange={(e) => setEditingChannel({ ...editingChannel, description: e.target.value })}
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Status</label>
                                            <input
                                                type="text"
                                                value={editingChannel.status}
                                                onChange={(e) => setEditingChannel({ ...editingChannel, status: e.target.value })}
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Viewers</label>
                                            <input
                                                type="number"
                                                value={editingChannel.viewers}
                                                onChange={(e) => setEditingChannel({ ...editingChannel, viewers: e.target.value })}
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Stream Quality</label>
                                            <input
                                                type="text"
                                                value={editingChannel.quality}
                                                onChange={(e) => setEditingChannel({ ...editingChannel, quality: e.target.value })}
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                                            />
                                        </div>
                                    </div>
                                    <div className="mt-6 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onClick={() => setIsModalOpen(false)}
                                            className="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            className="px-4 py-2 text-sm text-white bg-emerald-600 rounded hover:bg-emerald-700"
                                        >
                                            Save
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            );
        }

        ReactDOM.render(<ManageChannels />, document.getElementById('root'));
    </script>

    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>