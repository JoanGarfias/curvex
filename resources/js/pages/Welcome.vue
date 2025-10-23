<template>
    <Head title="Software Estadístico" />
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all"
    >
        <!-- Header -->
        <header class="w-full max-w-5xl px-6 py-6 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-[#2563eb] dark:text-[#60a5fa]">
                📊 Software Estadístico
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">
                Herramienta web para análisis de datos — Proyecto académico 2025
            </p>
        </header>

        <!-- Main content -->
        <main class="flex flex-col items-center text-center mt-10 px-6">
            <h2 class="text-2xl font-semibold mb-3">Bienvenido</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-10 max-w-xl">
                Selecciona una de las herramientas estadísticas para comenzar tus cálculos. 
                Puedes generar histogramas, analizar distribuciones o calcular medidas de posición.
            </p>

            <!-- Cards / Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 w-full max-w-4xl">
                <!-- Card 1 -->
                <div
                    class="bg-white dark:bg-[#18181b] rounded-2xl shadow-lg hover:shadow-2xl transition p-6 cursor-pointer border border-transparent hover:border-[#2563eb]/40"
                    @click="() => router.visit('/histograma')"
                >
                    <div class="text-5xl mb-3 text-[#2563eb]">📈</div>
                    <h3 class="text-xl font-semibold mb-2">Generar Histograma</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Crea un histograma y genera la tabla estadística con promedio, rango, varianza y más.
                    </p>
                </div>

                <!-- Card 2 -->
                <div
                    class="bg-white dark:bg-[#18181b] rounded-2xl shadow-lg hover:shadow-2xl transition p-6 cursor-pointer border border-transparent hover:border-[#16a34a]/40"
                    @click="() => router.visit('/cuartiles')"
                >
                    <div class="text-5xl mb-3 text-[#16a34a]">📊</div>
                    <h3 class="text-xl font-semibold mb-2">Cuartiles, Deciles y Percentiles</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Calcula medidas de posición que te permiten analizar la distribución de tus datos.
                    </p>
                </div>

                <!-- Card 3 -->
                <div
                    class="bg-white dark:bg-[#18181b] rounded-2xl shadow-lg hover:shadow-2xl transition p-6 cursor-pointer border border-transparent hover:border-[#f97316]/40"
                    @click="() => router.visit('/otra-operacion')"
                >
                    <div class="text-5xl mb-3 text-[#f97316]">⚙️</div>
                    <h3 class="text-xl font-semibold mb-2">Otra Operación</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Aquí podrás agregar tu siguiente módulo estadístico o cualquier otra funcionalidad.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-16 mb-6 text-sm text-gray-500 dark:text-gray-400 text-center">
            Desarrollado con ❤️ por Jere, Juan, Joan, Gera y Karlis · Laravel + Vue 3 + TailwindCSS · 2025
        </footer>
    </div>
</template>
