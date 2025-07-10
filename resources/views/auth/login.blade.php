<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css?family=DM+Sans:400,500,700&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Main Figma Structure -->
    <div class="v33_4272">
        <div class="v78_291"></div>
        <div class="v78_292"></div>
        <div class="v83_3472">
            <div class="v33_4273">
                <div class="v33_4274"></div>
                <div class="v33_4275">
                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="error-container" style="padding-bottom: 20px;">
                            @foreach ($errors->all() as $error)
                                <p class="error-text">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="v33_4276">
                        <div class="v33_4277">
                            <button type="submit" form="loginForm" class="submit-button">Sign In</button>
                        </div>
                        
                        <div class="v33_4279">
                            <div class="v33_4280">
                                <input type="checkbox" id="remember" name="remember" class="checkbox-input" form="loginForm" {{ old('remember') ? 'checked' : '' }}>
                                <span class="v33_4281">Remember me</span>
                                <div class="name"></div>
                            </div>
                            <div class="v33_4283">
                                <a href="#" class="v33_4284">Forget password?</a>
                            </div>
                        </div>
                        
                        <div class="v33_4285">
                            <span class="v33_4286">Password*</span>
                            <div class="v33_4287">
                                <div class="v33_4288">
                                    <div class="v33_4289">
                                        <input 
                                            type="password" 
                                            id="password" 
                                            name="password"
                                            class="form-input"
                                            required
                                            autocomplete="current-password"
                                            form="loginForm"
                                        >
                                        <button type="button" class="password-toggle" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="name"></div>
                        </div>
                        <br>
                        <!-- Email Field -->
                        <div class="v33_4293">
                            <span class="v33_4294">Email*</span>
                            <div class="v33_4295">
                                <div class="v33_4296">
                                    <div class="v33_4297">
                                        <input 
                                            type="text" 
                                            id="email" 
                                            name="email" 
                                            value="{{ old('email') }}"
                                            class="form-input"
                                            required
                                            autocomplete="email"
                                            form="loginForm"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Header Section -->
                    <div class="v33_4300">
                        <span class="v33_4301">Login</span>
                        <span class="v33_4302">Enter your email and password to sign in!</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Laravel Form -->
    <form id="loginForm" method="POST" action="{{ route('login') }}" style="display: none;">
        @csrf
    </form>

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

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>