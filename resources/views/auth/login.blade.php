<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AnnSathi v2</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0b0f19;
            overflow-x: hidden;
        }

        /* Glassmorphism card */
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        /* Animated background glow blobs */
        .glow-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.25;
            animation: floatGlow 15s infinite alternate ease-in-out;
        }

        .blob-1 {
            width: 350px;
            height: 350px;
            background: #10b981; /* Emerald */
            top: -10%;
            left: -10%;
        }

        .blob-2 {
            width: 400px;
            height: 400px;
            background: #f59e0b; /* Amber */
            bottom: -10%;
            right: -10%;
            animation-delay: -5s;
        }

        .blob-3 {
            width: 300px;
            height: 300px;
            background: #3b82f6; /* Blue */
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -8s;
        }

        @keyframes floatGlow {
            0% {
                transform: translate(0, 0) scale(1);
            }
            100% {
                transform: translate(40px, 40px) scale(1.1);
            }
        }

        /* Interactive active transitions */
        .input-glow:focus {
            outline: none;
            border-color: rgba(16, 185, 129, 0.5);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative px-4 py-12">

    <!-- Decorative Glow Blobs -->
    <div class="glow-blob blob-1"></div>
    <div class="glow-blob blob-2"></div>
    <div class="glow-blob blob-3"></div>

    <!-- Main Container -->
    <div class="w-full max-w-md z-10">
        
        <!-- Logo / Brand Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold tracking-tight text-white flex items-center justify-center gap-2">
                <span class="text-emerald-500 font-black">AnnSathi</span>
                <span class="text-xs uppercase bg-emerald-500/10 text-emerald-400 px-2.5 py-0.5 rounded-full border border-emerald-500/20 font-semibold tracking-wider">v2</span>
            </h1>
            <p class="mt-2 text-sm text-gray-400 font-medium">Enterprise ERP & Smart POS Portal</p>
        </div>

        <!-- Glass Login Card -->
        <div class="glass-card rounded-2xl p-8 sm:p-10">
            <h2 class="text-2xl font-bold text-white mb-6 text-center sm:text-left">Sign In</h2>

            <!-- Session Warnings / Success -->
            @if ($errors->any())
                <div class="mb-5 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-300 mb-1.5">Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        required 
                        value="{{ old('email') }}" 
                        placeholder="name@restaurant.com"
                        class="w-full bg-gray-900/50 border border-gray-700/60 rounded-xl px-4 py-3 text-white text-sm input-glow transition duration-200 placeholder-gray-500"
                    >
                </div>

                <!-- Password field -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-gray-300">Password</label>
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        placeholder="••••••••"
                        class="w-full bg-gray-900/50 border border-gray-700/60 rounded-xl px-4 py-3 text-white text-sm input-glow transition duration-200 placeholder-gray-500"
                    >
                </div>

                <!-- Remember Me & Reset -->
                <div class="flex items-center justify-between text-sm py-1">
                    <label class="flex items-center text-gray-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="sr-only peer">
                        <div class="w-4 h-4 bg-gray-800 border border-gray-700 rounded peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition duration-150 flex items-center justify-center mr-2">
                            <!-- Checkmark icon -->
                            <svg class="w-2.5 h-2.5 text-white opacity-0 peer-checked:opacity-100 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        Remember me
                    </label>
                </div>

                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transform active:scale-[0.98] mt-2 text-sm"
                >
                    Access System
                </button>
            </form>

            <!-- Quick Demo Credentials Hint -->
            <div class="mt-8 pt-6 border-t border-gray-800 text-center">
                <p class="text-xs text-gray-500">Seed credentials for testing:</p>
                <div class="mt-2 flex flex-wrap justify-center gap-1.5 text-[11px] text-gray-400">
                    <span class="bg-gray-800/40 px-2 py-1 rounded border border-gray-800">admin@annsathi.com</span>
                    <span class="bg-gray-800/40 px-2 py-1 rounded border border-gray-800">manager@annsathi.com</span>
                    <span class="bg-gray-800/40 px-2 py-1 rounded border border-gray-800">chef@annsathi.com</span>
                    <span class="bg-gray-800/40 px-2 py-1 rounded border border-gray-800">waiter@annsathi.com</span>
                </div>
                <p class="text-[10px] text-gray-600 mt-2">Password: <span class="font-mono">password</span></p>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-600 mt-6">&copy; {{ date('Y') }} AnnSathi SaaS ERP. All rights reserved.</p>
    </div>

</body>
</html>
