<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - TechSys</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Matches app.blade.php variables */
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --gradient-danger: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-gold: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); /* Adjusted to be true gold */
            
            --glass-bg: rgba(255, 255, 255, 0.85); /* Slightly more opaque for better readability */
            --glass-border: rgba(255, 255, 255, 0.6);
            --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Tajawal', 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eaf6 50%, #f3e5f5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated Ambient Background */
        body::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(235, 51, 73, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 40% 20%, rgba(17, 153, 142, 0.1) 0%, transparent 40%);
            animation: gradientMove 20s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes gradientMove {
            0% { transform: translate(-10%, -10%); }
            100% { transform: translate(10%, 10%); }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        /* Glass Card */
        .login-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-xl);
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Top Accent Line */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .logo-wrapper img {
            max-width: 70%;
            max-height: 70%;
            object-fit: contain;
        }

        .logo-wrapper i {
            font-size: 3rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 0.95rem;
            color: #718096;
            font-weight: 500;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: #4a5568;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .input-group {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 3rem; /* Right padding for RTL icon adjustment below */
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(226, 232, 240, 0.8);
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2d3748;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        /* RTL specific padding adjustment */
        html[dir="rtl"] .form-input {
            padding: 0.85rem 3rem 0.85rem 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        html[dir="rtl"] .input-icon {
            right: 1.2rem;
            left: auto;
        }
        
        html[dir="ltr"] .input-icon {
            left: 1.2rem;
            right: auto;
        }

        .form-input:focus ~ .input-icon {
            color: #667eea;
        }

        .form-input.is-invalid {
            border-color: #fc8181;
            background-color: #fff5f5;
        }

        .invalid-feedback {
            display: block;
            color: #e53e3e;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        /* Options Row */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4a5568;
            cursor: pointer;
            font-weight: 600;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            border: 2px solid #cbd5e0;
            cursor: pointer;
            accent-color: #667eea;
            transition: all 0.2s;
        }

        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #5a67d8;
        }

        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: var(--gradient-primary);
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 1.05rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            filter: brightness(1.05);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login i {
            font-size: 1.1rem;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: #718096;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Loading State */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
            display: none;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-login.loading .spinner {
            display: block;
        }
        
        .btn-login.loading .btn-text {
            display: none;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            
            <!-- Dynamic Logo Section -->
            <div class="logo-section">
                @php
                    $systemLogo = \App\Models\SystemSetting::where('key', 'system_default_logo')->value('value');
                @endphp
                
                <div class="logo-wrapper">
                    @if($systemLogo)
                        <img src="{{ asset('storage/' . $systemLogo) }}" alt="System Logo">
                    @else
                        <i class="fas fa-layer-group"></i>
                    @endif
                </div>
                
                <h1 class="logo-title">TechSys</h1>
                <p class="logo-subtitle">نظام إدارة متقدم للمتاجر</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <div class="input-group">
                        <input 
                            id="email" 
                            type="email" 
                            class="form-input @error('email') is-invalid @enderror" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autocomplete="email" 
                            autofocus
                            placeholder="user@example.com"
                        >
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <div class="input-group">
                        <input 
                            id="password" 
                            type="password" 
                            class="form-input @error('password') is-invalid @enderror" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="••••••••"
                        >
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>تذكرني</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="forgot-link" href="{{ route('password.request') }}">
                            نسيت كلمة المرور؟
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    <div class="spinner"></div>
                    <span class="btn-text">تسجيل الدخول</span>
                    <i class="fas fa-arrow-left btn-text" style="font-size: 0.9em;"></i>
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; {{ date('Y') }} TechSys - جميع الحقوق محفوظة</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('.btn-login');
            btn.classList.add('loading');
        });
    </script>
</body>
</html>
