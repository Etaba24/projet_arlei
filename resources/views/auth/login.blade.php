<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | ARLEI</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts and CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-15px) scale(1.02); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.1); }
        }
        .animate-pulse-slow {
            animation: pulse-slow 8s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full text-slate-800 antialiased overflow-x-hidden">

<div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- Left Column: Branding and Illustration (visible on large screens) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-emerald-800 via-emerald-600 to-teal-800 flex-col items-center justify-center p-12 overflow-hidden select-none">
        
        <!-- Abstract Glass Orbs / Blurs -->
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-white/10 blur-3xl animate-pulse-slow"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-emerald-500/20 blur-3xl animate-pulse-slow" style="animation-delay: 3s;"></div>
        
        <!-- Decorative Floating Glass Cards -->
        <div class="absolute top-[15%] left-[10%] w-32 h-32 rounded-3xl bg-white/5 border border-white/10 backdrop-blur-md rotate-12 pointer-events-none transition-transform hover:rotate-45 duration-1000"></div>
        <div class="absolute bottom-[20%] right-[10%] w-24 h-24 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm -rotate-12 pointer-events-none transition-transform hover:-rotate-45 duration-1000"></div>

        <!-- Text / Logo Content -->
        <div class="relative z-10 text-center max-w-md">
            <h1 class="text-6xl font-black text-white tracking-wider mb-2 drop-shadow-lg animate-fade-in">
                AR<span class="text-emerald-300">LEI</span>
            </h1>
            <p class="text-emerald-100/80 font-medium text-lg tracking-wide uppercase">
                Suivi de Production & Traçabilité
            </p>
        </div>

        <!-- Illustration Image -->
        <div class="relative z-10 mt-12 max-w-sm w-full animate-float">
            <div class="absolute inset-0 bg-emerald-950/20 rounded-[2.5rem] blur-2xl transform translate-y-4"></div>
            <img src="{{ asset('images/image.png') }}" class="relative z-10 w-full object-contain rounded-[2.5rem] drop-shadow-2xl" alt="Agroprod Illustration">
        </div>

        <!-- Footer Creds on Left -->
        <div class="absolute bottom-6 left-12 right-12 z-10 flex justify-between text-emerald-200/50 text-xs">
            <span>&copy; {{ date('Y') }} ARLEI</span>
            <span>Version 2.0</span>
        </div>
    </div>

    <!-- Right Column: Login Form -->
    <div class="flex-1 flex flex-col justify-center items-center px-6 sm:px-12 lg:px-20 bg-slate-50 relative py-12">
        
        <!-- Small top branding for mobile -->
        <div class="lg:hidden mb-8 text-center">
            <h1 class="text-4xl font-black text-slate-800 tracking-wider">
                AR<span class="text-emerald-600">LEI</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">Suivi de Production & Traçabilité</p>
        </div>

        <!-- Login Container -->
        <div class="w-full max-w-md">
            <div id="login-box" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 sm:p-10 transition-all duration-700 transform translate-y-4 opacity-0" x-data x-init="setTimeout(() => { $el.classList.remove('translate-y-4', 'opacity-0'); $el.classList.add('translate-y-0', 'opacity-100'); }, 150)">
                
                <!-- Welcome Title -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Bienvenue</h2>
                    <p class="text-slate-500 mt-1 text-sm">Connectez-vous pour accéder à votre espace de travail.</p>
                </div>

                <!-- Session / Authentication Status Alerts -->
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-start space-x-3 text-rose-800 text-sm animate-modal-pop">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="font-bold block mb-0.5">Identifiants incorrects</span>
                            <span class="text-rose-600/95 text-xs">
                                Les informations d'identification saisies sont incorrectes. Veuillez réessayer.
                            </span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6" id="login-form">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Identifiant (Email)</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border @error('email') border-rose-300 focus:border-rose-500 focus:ring-rose-100 @else border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 @enderror rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-4 transition duration-200"
                                placeholder="nom@entreprise.com">
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Mot de passe</label>
                            @if (Route::has('password.request'))
                                <a class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition" href="{{ route('password.request') }}">
                                    Oublié ?
                                </a>
                            @endif
                        </div>
                        <div class="relative group" x-data="{ show: false }">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                class="block w-full pl-11 pr-12 py-3.5 bg-slate-50 border @error('email') border-rose-300 focus:border-rose-500 focus:ring-rose-100 @else border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 @enderror rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-4 transition duration-200"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!show">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="show" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 014.132-5.4m1.962-1.127A9.97 9.97 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.4m-4.659-3.619a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center cursor-pointer select-none" for="remember_me">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500/20 w-4 h-4 transition duration-150">
                            <span class="ml-2 text-xs font-semibold text-slate-500">Se souvenir de moi</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="w-full py-4 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/20 hover:shadow-xl hover:shadow-emerald-600/30 active:scale-[0.98] transition-all duration-200">
                            Se connecter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer Creds on Mobile -->
        <div class="lg:hidden text-center text-slate-400 text-xs mt-12">
            <p>&copy; {{ date('Y') }} ARLEI. Tous droits réservés.</p>
        </div>
    </div>
</div>

</body>
</html>