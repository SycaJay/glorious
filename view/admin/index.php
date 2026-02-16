<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TV Network Admin Dashboard</title>
  <!-- Tailwind CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
  </style>
</head>
<body class="bg-gray-100">
  <div class="flex flex-col md:flex-row h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-full md:w-64 bg-gray-800 text-white p-6 flex flex-col">
      <div class="flex items-center mb-8">
        <i class="fas fa-tv text-yellow-400 mr-2 text-2xl"></i>
        <div class="text-2xl font-bold">TV Network Admin</div>
      </div>
      
      <nav class="flex-grow">
        <ul class="space-y-4">
          <li>
            <a href="live-tv.html" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-150">
              <i class="fas fa-tv text-yellow-400"></i>
              <span>Live TV/24 Hour Channel</span>
            </a>
          </li>
          <li>
            <a href="movies.html" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-150">
              <i class="fas fa-film text-yellow-400"></i>
              <span>Movies</span>
            </a>
          </li>
          <li>
            <a href="herrnhut.html" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-150">
              <i class="fas fa-book-open text-yellow-400"></i>
              <span>Herrnhut</span>
            </a>
          </li>
          <li>
            <a href="teens-tv.html" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-150">
              <i class="fas fa-users text-yellow-400"></i>
              <span>Teens TV</span>
            </a>
          </li>
          <li>
            <a href="peds.html" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-150">
              <i class="fas fa-book text-yellow-400"></i>
              <span>PEDS</span>
            </a>
          </li>
          <li>
            <a href="settings.html" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-150">
              <i class="fas fa-cog text-yellow-400"></i>
              <span>Settings</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-grow overflow-y-auto">
      <!-- Header -->
      <div class="bg-white p-4 md:p-5 shadow-md flex flex-col md:flex-row justify-between items-center">
        <h1 class="text-2xl md:text-3xl font-bold flex items-center text-gray-800">
          <i class="fas fa-home mr-3 text-yellow-500"></i>
          <span>Welcome, Admin</span>
        </h1>
        
        <div class="flex items-center space-x-4 md:space-x-6 mt-4 md:mt-0">
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input 
              type="text" 
              class="bg-gray-100 pl-10 pr-4 py-2 rounded-full w-48 md:w-64 focus:outline-none focus:ring-2 focus:ring-yellow-400 border border-gray-300" 
              placeholder="Search channels..." 
            />
          </div>
          
          <div class="relative">
            <i class="fas fa-bell text-gray-600 hover:text-yellow-500 cursor-pointer text-xl"></i>
            <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
          </div>
          
          <div class="flex items-center space-x-3">
            <span class="text-gray-600">Admin Name</span>
            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
              <i class="fas fa-user text-white"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Dashboard -->
      <div class="p-4 md:p-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
          <!-- Live TV Card -->
          <div class="bg-red-600 p-6 rounded-xl shadow-lg card text-white">
            <div class="flex flex-col items-center mb-6">
              <i class="fas fa-tv text-4xl"></i>
              <h2 class="text-2xl font-bold mt-4">Live TV</h2>
            </div>
            
            <div class="flex flex-col items-center">
              <p class="text-gray-100 mb-4">Last Updated: 2023-10-15</p>
              <a href="managelive.php" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Manage Live TV
              </a>
            </div>
          </div>
          
          <!-- Movies Card -->
          <div class="bg-blue-600 p-6 rounded-xl shadow-lg card text-white">
            <div class="flex flex-col items-center mb-6">
              <i class="fas fa-film text-4xl"></i>
              <h2 class="text-2xl font-bold mt-4">Movies</h2>
            </div>
            
            <div class="flex flex-col items-center">
              <p class="text-gray-100 mb-4">Last Updated: 2023-10-14</p>
              <a href="managemovies.php" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Manage Movies
              </a>
            </div>
          </div>
          
          <!-- Herrnhut Card -->
          <div class="bg-purple-600 p-6 rounded-xl shadow-lg card text-white">
            <div class="flex flex-col items-center mb-6">
              <i class="fas fa-book-open text-4xl"></i>
              <h2 class="text-2xl font-bold mt-4">Herrnhut</h2>
            </div>
            
            <div class="flex flex-col items-center">
              <p class="text-gray-100 mb-4">Last Updated: 2023-10-13</p>
              <a href="manageherrnhut.php" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Manage Herrnhut
              </a>
            </div>
          </div>
          
          <!-- Teens TV Card -->
          <div class="bg-green-600 p-6 rounded-xl shadow-lg card text-white">
            <div class="flex flex-col items-center mb-6">
              <i class="fas fa-users text-4xl"></i>
              <h2 class="text-2xl font-bold mt-4">Teens TV</h2>
            </div>
            
            <div class="flex flex-col items-center">
              <p class="text-gray-100 mb-4">Last Updated: 2023-10-12</p>
              <a href="manageteens.php" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Manage Teens TV
              </a>
            </div>
          </div>
          
          <!-- PEDS Card -->
          <div class="bg-pink-600 p-6 rounded-xl shadow-lg card text-white">
            <div class="flex flex-col items-center mb-6">
              <i class="fas fa-book text-4xl"></i>
              <h2 class="text-2xl font-bold mt-4">PEDS</h2>
            </div>
            
            <div class="flex flex-col items-center">
              <p class="text-gray-100 mb-4">Last Updated: 2023-10-11</p>
              <a href="managePEDS.php" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Manage PEDS
              </a>
            </div>
          </div>
          
          <!-- Settings Card -->
          <div class="bg-gray-600 p-6 rounded-xl shadow-lg card text-white">
            <div class="flex flex-col items-center mb-6">
              <i class="fas fa-cog text-4xl"></i>
              <h2 class="text-2xl font-bold mt-4">Settings</h2>
            </div>
            
            <div class="flex flex-col items-center">
              <p class="text-gray-100 mb-4">Last Updated: 2023-10-10</p>
              <a href="managesettings.php" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Manage Settings
              </a>
            </div>
          </div>

          <!-- Manage Channels Card -->
          <div class="bg-orange-600 p-6 rounded-xl shadow-lg card text-white">
            <div class="flex flex-col items-center mb-6">
              <i class="fas fa-satellite-dish text-4xl"></i>
              <h2 class="text-2xl font-bold mt-4">Manage Channels</h2>
            </div>
            
            <div class="flex flex-col items-center">
              <p class="text-gray-100 mb-4">Last Updated: 2023-10-16</p>
              <a href="managechannels.php" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Manage Channels
              </a>
            </div>
          </div>
        </div>
        
        <!-- Recent Activity Section -->
        <div class="mt-8 bg-white p-6 rounded-xl shadow-lg">
          <h2 class="text-2xl font-bold mb-6 flex items-center text-gray-800">
            <i class="fas fa-bell mr-3 text-yellow-500"></i>
            Recent Activity
          </h2>
          
          <div class="space-y-4">
            <div class="flex items-center space-x-4 border-b border-gray-200 pb-4">
              <div class="p-3 bg-gray-100 rounded-lg">
                <i class="fas fa-tv text-red-500"></i>
              </div>
              <div class="flex-grow">
                <div class="flex justify-between">
                  <p class="font-semibold text-gray-800">Live TV: Stream updated</p>
                  <p class="text-gray-500 text-sm">2 hours ago</p>
                </div>
              </div>
            </div>
            
            <div class="flex items-center space-x-4 border-b border-gray-200 pb-4">
              <div class="p-3 bg-gray-100 rounded-lg">
                <i class="fas fa-film text-blue-500"></i>
              </div>
              <div class="flex-grow">
                <div class="flex justify-between">
                  <p class="font-semibold text-gray-800">Movies: New content added</p>
                  <p class="text-gray-500 text-sm">5 hours ago</p>
                </div>
              </div>
            </div>
            
            <div class="flex items-center space-x-4 border-b border-gray-200 pb-4">
              <div class="p-3 bg-gray-100 rounded-lg">
                <i class="fas fa-users text-green-500"></i>
              </div>
              <div class="flex-grow">
                <div class="flex justify-between">
                  <p class="font-semibold text-gray-800">Teens TV: Schedule modified</p>
                  <p class="text-gray-500 text-sm">1 day ago</p>
                </div>
              </div>
            </div>
            
            <div class="flex items-center space-x-4 border-b border-gray-200 pb-4">
              <div class="p-3 bg-gray-100 rounded-lg">
                <i class="fas fa-book text-pink-500"></i>
              </div>
              <div class="flex-grow">
                <div class="flex justify-between">
                  <p class="font-semibold text-gray-800">PEDS: Content removed</p>
                  <p class="text-gray-500 text-sm">2 days ago</p>
                </div>
              </div>
            </div>
          </div>
          
          <a href="all-activity.html" class="mt-6 w-full py-3 bg-transparent border border-yellow-500 text-yellow-500 rounded-lg hover:bg-yellow-500 hover:text-white transition-colors duration-200 block text-center">
            View All Activity
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>