{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-gray-50">
    @yield('content')
</body>
</html>

{{-- resources/views/layouts/dashboard.blade.php --}}
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
</head>
<body class="bg-gray-100">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                                <span class="text-white text-sm font-bold">C</span>
                            </div>
                            <span class="ml-2 text-lg font-semibold text-gray-900">Company</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=User+Name&background=6366f1&color=fff" alt="User">
                        </div>
                        <span class="text-gray-700">User Name</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <div class="flex">
            <div class="w-64 bg-white shadow-sm min-h-screen">
                <div class="py-4">
                    <ul class="space-y-2 px-3">
                        <li>
                            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.factor') }}" class="sidebar-link {{ request()->routeIs('dashboard.factor') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i>
                                Factor Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('questions.track') }}" class="sidebar-link {{ request()->routeIs('questions.track') ? 'active' : '' }}">
                                <i class="fas fa-question-circle"></i>
                                Track Questions
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('questions.edit') }}" class="sidebar-link {{ request()->routeIs('questions.edit') ? 'active' : '' }}">
                                <i class="fas fa-edit"></i>
                                Edit Questions
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.historical') }}" class="sidebar-link {{ request()->routeIs('dashboard.historical') ? 'active' : '' }}">
                                <i class="fas fa-history"></i>
                                Historical Assessment
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('assessment.form') }}" class="sidebar-link {{ request()->routeIs('assessment.form') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list"></i>
                                Assessment Form
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-6">
                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 flex items-center justify-center p-4">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-20 w-64 h-64 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-white rounded-full blur-3xl"></div>
    </div>

    <!-- Header -->
    <div class="absolute top-6 left-6">
        <span class="text-white text-lg font-medium">Login</span>
    </div>

    <!-- Login Form -->
    <div class="relative z-10 w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Login</h2>
                <p class="text-gray-600">Enter your email and password to sign in!</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email*</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        placeholder="mail@example.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password*</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            placeholder="Min. 8 characters"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 pr-12"
                            required
                        >
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword()">
                            <i class="fas fa-eye text-gray-400" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-500">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Sign In
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const passwordIcon = document.getElementById('password-icon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
}
</script>
@endsection