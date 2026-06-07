<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — TeleStore</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    {{-- Brand --}}
    <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-2xl mx-auto mb-4">✈️</div>
        <h1 class="text-2xl font-extrabold">TeleStore</h1>
        <p class="text-gray-500 text-sm font-mono mt-1">Admin Panel</p>
    </div>

    {{-- Card --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-teal-500 to-blue-500"></div>

        @if($errors->any())
        <div class="mb-5 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm font-mono">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm font-mono text-gray-100 focus:outline-none focus:border-teal-500 transition-colors"
                       placeholder="admin@telestore.local">
            </div>
            <div>
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm font-mono text-gray-100 focus:outline-none focus:border-teal-500 transition-colors"
                       placeholder="••••••••">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded">
                <label for="remember" class="text-sm text-gray-400">Ingat saya</label>
            </div>
            <button type="submit"
                    class="w-full py-3 bg-teal-500 hover:bg-teal-400 text-black font-bold rounded-xl transition-all">
                🔐 Login
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-600 font-mono mt-6">
        Default: admin@telestore.local / admin123
    </p>
</div>
</body>
</html>
