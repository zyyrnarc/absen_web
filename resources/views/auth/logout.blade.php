<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl p-8 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-3xl">
            &#128682;
        </div>

        <h1 class="text-2xl font-bold text-slate-800">Logout Akun</h1>
        <p class="mt-3 text-sm leading-6 text-slate-500">
            Anda yakin ingin keluar dari dashboard admin?
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                Batal
            </a>

            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-red-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-600"
                >
                    Ya, Logout
                </button>
            </form>
        </div>
    </div>
</body>
</html>
