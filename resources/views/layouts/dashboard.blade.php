<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('head')
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <div class="w-64 bg-white text-gray-800 border-r border-gray-200 flex flex-col relative">
            <div class="p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                        <span class="text-white text-sm font-bold">C</span>
                    </div>
                    <span class="text-lg font-semibold">Company</span>
                </div>
            </div>

            <nav class="mt-8" style="font-size: 14px !important;">
                <div class="px-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('dashboard.factor') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard.factor') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>Factor Dashboard</span>
                    </a>
                    
                    <a href="{{ route('assessment.form') }}" class="sidebar-nav-item {{ request()->routeIs('assessment.form') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list w-5"></i>
                        <span>Assessment Form</span>
                    </a>
                    
                    <a href="{{ route('dashboard.historical') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard.historical') ? 'active' : '' }}">
                        <i class="fas fa-history w-5"></i>
                        <span>Historical Assessment</span>
                    </a>
                    
                    <div class="sidebar-nav-item cursor-pointer" onclick="toggleDropdown()">
                        <i class="fas fa-cog w-5"></i>
                        <span>Manage Assessment</span>
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </div>
                    
                    <div id="manageDropdown" class="hidden ml-6 space-y-1">
                        <a href="{{ route('questions.track') }}" class="sidebar-nav-item text-sm">
                            <span>Track Questions</span>
                        </a>
                        <a href="{{ route('questions.edit') }}" class="sidebar-nav-item text-sm">
                            <span>Edit Questions</span>
                        </a>
                        <a href="{{ route('questions.send') }}" class="sidebar-nav-item text-sm">
                            <span>Send Question</span>
                        </a>
                    </div>
                </div>
                
                <div class="absolute bottom-4 left-4 text-gray-600">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button inline-flex items-center space-x-2 px-3 py-2 text-gray-600 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors duration-150">
                            <i class="fas fa-sign-out-alt w-5 text-current"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <div class="flex-1 bg-gray-50">
            <header class="bg-white shadow-sm border-b px-6 py-4">
                <div class="flex justify-between items-center">
                    {{-- Page title with factor dropdown for Factor Dashboard --}}
                    @if(request()->routeIs('dashboard.factor'))
                        <div class="flex items-center space-x-4">
                            <h1 class="text-2xl font-bold text-gray-900">Factor Dashboard</h1>
                            <div class="flex items-center space-x-2">
                                <select id="factorFilter" class="border border-gray-300 rounded-md px-4 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent min-w-[200px]">
                                    @php
                                        $factors = \App\Models\Factor::where('is_active', true)->get();
                                        $currentFactorSlug = isset($factor) ? $factor->slug : (request()->route('factor') ?? 'it-infrastructure');
                                    @endphp
                                    @foreach($factors as $factorOption)
                                        <option value="{{ $factorOption->slug }}" {{ $currentFactorSlug === $factorOption->slug ? 'selected' : '' }}>
                                            {{ $factorOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    @endif
                    
                    {{-- User profile with proper name --}}
                    <div class="flex items-center space-x-3">
                        @php
                            $userName = auth()->user()->name ?? 'User Name';
                            $userInitials = collect(explode(' ', $userName))->map(fn($name) => strtoupper(substr($name, 0, 1)))->join('');
                            $userInitials = substr($userInitials, 0, 2); // Limit to 2 initials
                        @endphp
                        
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-semibold">{{ $userInitials }}</span>
                        </div>
                        <span class="text-gray-700 font-medium">{{ $userName }}</span>
                    </div>
                </div>
            </header>

            <main class="p-6" style="background-color: #f5f7fe; padding-top: 50px;">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('manageDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Factor dropdown change handler
        document.addEventListener('DOMContentLoaded', function() {
            const factorDropdown = document.getElementById('factorFilter');
            if (factorDropdown) {
                factorDropdown.addEventListener('change', function() {
                    const selectedFactor = this.value;
                    console.log('🔄 Factor changed to:', selectedFactor);
                    
                    // Navigate to the selected factor
                    window.location.href = `{{ route('dashboard.factor') }}/${selectedFactor}`;
                });
            }
        });
    </script>
</body>
</html>

<style>
.sidebar-nav-item {
    @apply flex items-center space-x-3 px-3 py-3 text-gray-600 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors duration-200 cursor-pointer;
}

.sidebar-nav-item.active {
    @apply bg-blue-100 text-blue-700 font-semibold;
}

.sidebar-nav-item i {
    @apply text-current;
    width: 1.25rem;
    text-align: center;
}

/* Custom styles for table headers */
.assessment-table-header,
.corrective-table-header {
    background-color: #25408f;
    color: white;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03); 
    position: relative; 
    z-index: 1; 
}

.assessment-form-table-header {
    background-color: #25408f;
    color: white;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03); 
    position: relative; 
    z-index: 1; 
}

.historical-assessment-table-header {
    background-color: #25408f;
    color: white;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03); 
    position: relative; 
    z-index: 1; 
}

.bg-table-head {
    background-color: #25408f;
    border: none !important; 
}

.min-w-full tbody {
    border-top: none !important;
}

.min-w-full tbody td {
    border-top: 1px solid #e2e8f0;
}

.min-w-full thead th {
    border: none !important;
}

.bg-white.rounded-xl.shadow-sm.border.border-gray-100.overflow-hidden {
    border: none; 
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); 
    overflow: hidden; 
}

.bg-white.px-4.py-3.flex.items-center.justify-between.border-t.border-gray-200.sm\:px-6 {
    background-color: white !important; 
    border-top: 1px solid #e2e8f0; 
}

/* Factor dropdown styling */
#factorFilter {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    padding-left: 0.75rem;
    padding-right: 2.5rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none'%3e%3cpath fill='%236B7280' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1.5em 1.5em;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
</style>