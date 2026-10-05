<?php
// login page for the administrator of the system
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrator Login | QELA Technology</title>

    <link rel="shortcut icon" href="./resource/favico.ico" type="image/x-icon">

    <link href="./output.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        qela: {
                            dark: '#07130d',
                            green: '#10271b',
                            gold: '#dfa21f',
                            cream: '#f8f7f3'
                        }
                    },
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-qela-cream font-sans text-qela-green">

    <main class="min-h-screen flex items-center justify-center px-4 py-8">

        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl lg:grid lg:grid-cols-2">

            <!-- Left Side -->
            <div class="relative hidden min-h-[650px] overflow-hidden bg-qela-green lg:flex">

                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full border-[40px] border-qela-gold/10"></div>

                <div class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full border-[50px] border-white/5"></div>

                <div class="relative z-10 flex w-full flex-col justify-between p-12">

                    <!-- Logo -->
                    <div>
                        <a href="./index.html" class="inline-flex items-center gap-3">

                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-qela-gold text-qela-green shadow-lg">
                                <i class="fa-solid fa-leaf text-xl"></i>
                            </div>

                            <div>
                                <h1 class="font-heading text-2xl font-bold tracking-tight text-white">
                                    QELA
                                </h1>

                                <p class="text-xs font-medium uppercase tracking-[0.25em] text-qela-gold">
                                    Technology
                                </p>
                            </div>

                        </a>
                    </div>

                    <!-- Content -->
                    <div class="max-w-md">

                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-qela-gold/30 bg-qela-gold/10 px-4 py-2 text-sm font-medium text-qela-gold">
                            <i class="fa-solid fa-shield-halved"></i>
                            Secure Administration
                        </div>

                        <h2 class="font-heading text-4xl font-bold leading-tight text-white xl:text-5xl">
                            Welcome back to
                            <span class="text-qela-gold">QELA.</span>
                        </h2>

                        <p class="mt-6 leading-7 text-white/70">
                            Access the QELA Technology administration system
                            to manage your digital platform, content and
                            business operations securely.
                        </p>

                        <div class="mt-10 grid grid-cols-3 gap-4">

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <i class="fa-solid fa-lock text-qela-gold"></i>
                                <p class="mt-3 text-sm font-semibold text-white">
                                    Secure
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <i class="fa-solid fa-chart-line text-qela-gold"></i>
                                <p class="mt-3 text-sm font-semibold text-white">
                                    Powerful
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <i class="fa-solid fa-bolt text-qela-gold"></i>
                                <p class="mt-3 text-sm font-semibold text-white">
                                    Efficient
                                </p>
                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="text-sm text-white/50">
                        © <?php echo date('Y'); ?> QELA Technologies (T) Ltd.
                    </div>

                </div>
            </div>

            <!-- Right Side -->
            <div class="flex min-h-[650px] items-center justify-center px-6 py-12 sm:px-10 lg:px-12">

                <div class="w-full max-w-md">

                    <!-- Mobile Logo -->
                    <div class="mb-10 text-center lg:hidden">

                        <a href="./index.html" class="inline-flex items-center gap-3">

                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-qela-gold text-qela-green shadow-lg">
                                <i class="fa-solid fa-leaf text-xl"></i>
                            </div>

                            <div class="text-left">
                                <h1 class="font-heading text-2xl font-bold text-qela-green">
                                    QELA
                                </h1>

                                <p class="text-xs font-medium uppercase tracking-[0.25em] text-qela-gold">
                                    Technology
                                </p>
                            </div>

                        </a>

                    </div>

                    <!-- Heading -->
                    <div class="mb-8">

                        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-qela-gold">
                            Administrator
                        </p>

                        <h2 class="font-heading text-3xl font-bold text-qela-green sm:text-4xl">
                            Sign in to your account
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-gray-500">
                            Enter your credentials to access the QELA administration dashboard.
                        </p>

                    </div>

                    <!-- Login Form -->
                    <form action="" method="POST" class="space-y-6">

                        <!-- Email -->
                        <div>

                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-qela-green">
                                Email Address
                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <i class="fa-regular fa-envelope"></i>
                                </span>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="admin@qelatechnologies.co.tz"
                                    autocomplete="email"
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3.5 pl-11 pr-4 text-sm text-qela-green outline-none transition duration-300 placeholder:text-gray-400 focus:border-qela-gold focus:bg-white focus:ring-4 focus:ring-qela-gold/10">
                            </div>

                        </div>

                        <!-- Password -->
                        <div>

                            <div class="mb-2 flex items-center justify-between">

                                <label
                                    for="password"
                                    class="block text-sm font-semibold text-qela-green">
                                    Password
                                </label>

                                <a
                                    href="#"
                                    class="text-xs font-semibold text-qela-gold transition hover:text-qela-green">
                                    Forgot password?
                                </a>

                            </div>

                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <i class="fa-solid fa-lock"></i>
                                </span>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3.5 pl-11 pr-12 text-sm text-qela-green outline-none transition duration-300 placeholder:text-gray-400 focus:border-qela-gold focus:bg-white focus:ring-4 focus:ring-qela-gold/10">

                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 transition hover:text-qela-green"
                                    aria-label="Show password">

                                    <i id="passwordIcon" class="fa-regular fa-eye"></i>

                                </button>

                            </div>

                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">

                            <label class="flex cursor-pointer items-center gap-3">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    class="h-4 w-4 cursor-pointer rounded border-gray-300 text-qela-green focus:ring-qela-gold">

                                <span class="text-sm text-gray-600">
                                    Remember me
                                </span>

                            </label>

                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="group flex w-full items-center justify-center gap-3 rounded-xl bg-qela-green px-6 py-4 text-sm font-bold text-white shadow-lg shadow-qela-green/20 transition duration-300 hover:-translate-y-0.5 hover:bg-qela-dark hover:shadow-xl">

                            <span>Sign In</span>

                            <i class="fa-solid fa-arrow-right text-sm transition duration-300 group-hover:translate-x-1"></i>

                        </button>

                    </form>

                    <!-- Security Notice -->
                    <div class="mt-8 flex items-start gap-3 rounded-xl border border-qela-green/10 bg-qela-green/5 p-4">

                        <i class="fa-solid fa-shield-halved mt-0.5 text-qela-gold"></i>

                        <p class="text-xs leading-5 text-gray-500">
                            This is a restricted administration area.
                            Your login credentials are protected and should
                            never be shared with anyone.
                        </p>

                    </div>

                    <!-- Back -->
                    <div class="mt-8 text-center">

                        <a
                            href="./index.html"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-qela-green transition hover:text-qela-gold">

                            <i class="fa-solid fa-arrow-left text-xs"></i>
                            Back to QELA Technology

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');

        togglePassword.addEventListener('click', function () {
            const isPassword = password.type === 'password';

            password.type = isPassword ? 'text' : 'password';

            passwordIcon.classList.toggle('fa-eye', !isPassword);
            passwordIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    </script>

</body>
</html>