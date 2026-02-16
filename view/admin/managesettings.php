<?php
// Include your database connection here
include('../../db/config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Settings</title>
    
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

        function ManageSettings() {
            const settingsSections = [
                {
                    title: 'General Settings',
                    icon: 'settings',
                    description: 'Basic configuration and preferences',
                    status: 'Updated'
                },
                {
                    title: 'Notifications',
                    icon: 'bell',
                    description: 'Manage alerts and notifications',
                    status: 'Review needed'
                },
                {
                    title: 'Security',
                    icon: 'shield',
                    description: 'Security and authentication settings',
                    status: 'Secure'
                },
                {
                    title: 'User Management',
                    icon: 'users',
                    description: 'Manage user roles and permissions',
                    status: 'Active'
                },
                {
                    title: 'Database',
                    icon: 'database',
                    description: 'Database configuration and backup',
                    status: 'Healthy'
                },
                {
                    title: 'API Settings',
                    icon: 'globe',
                    description: 'API keys and endpoint configuration',
                    status: 'Connected'
                }
            ];

            return (
                <div className="min-h-screen bg-gradient-to-br from-gray-50 to-slate-50 p-6">
                    <div className="max-w-7xl mx-auto">
                        {/* Header */}
                        <div className="flex justify-between items-center mb-8">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">System Settings</h1>
                                <p className="text-gray-600 mt-1">Manage your application settings</p>
                            </div>
                            <button className="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <span className="lucide-arrow-left"></span>
                                Back
                            </button>
                        </div>

                       {/* Settings Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {settingsSections.map((section, index) => (
                                <div 
                                    key={index} 
                                    className="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow cursor-pointer p-6"
                                >
                                    <div className="flex items-start gap-4">
                                        <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <span className={`lucide-${section.icon} text-blue-600`}></span>
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex justify-between items-start">
                                                <h3 className="font-semibold text-lg text-gray-900">{section.title}</h3>
                                                <span className="px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded-full">
                                                    {section.status}
                                                </span>
                                            </div>
                                            <p className="text-sm text-gray-500 mt-1">{section.description}</p>
                                            <button className="mt-4 text-sm text-blue-600 hover:text-blue-700">
                                                Configure →
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* System Status */}
                        <div className="mt-8 bg-white rounded-lg shadow-sm p-6">
                            <div className="flex items-center gap-4 mb-4">
                                <span className="lucide-cloud text-blue-600"></span>
                                <h2 className="text-xl font-semibold text-gray-900">System Status</h2>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div className="p-4 bg-green-50 rounded-lg">
                                    <h3 className="text-sm font-medium text-green-800">System Health</h3>
                                    <p className="text-2xl font-semibold text-green-600 mt-1">98.5%</p>
                                </div>
                                <div className="p-4 bg-blue-50 rounded-lg">
                                    <h3 className="text-sm font-medium text-blue-800">Active Users</h3>
                                    <p className="text-2xl font-semibold text-blue-600 mt-1">1,234</p>
                                </div>
                                <div className="p-4 bg-purple-50 rounded-lg">
                                    <h3 className="text-sm font-medium text-purple-800">Storage Used</h3>
                                    <p className="text-2xl font-semibold text-purple-600 mt-1">45.8 GB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        ReactDOM.render(<ManageSettings />, document.getElementById('root'));
    </script>

    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>