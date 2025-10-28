# Curvex

<img width="1888" height="917" alt="image" src="https://github.com/user-attachments/assets/ff718b50-478f-49a9-9ce6-6d2a3ceeb9df" />


> Proyecto para la materia de Probabilidad y Estadística de la carrera de Ingeniería en Computación en la Universidad del Istmo.

Curvex es una aplicación web diseñada para realizar cálculos estadísticos y visualizar distribuciones de probabilidad de una manera intuitiva y eficiente. El proyecto integra un backend robusto con un frontend moderno y reactivo para ofrecer una experiencia de usuario fluida.

## Información Académica

-   **Universidad:** Universidad del Istmo
-   **Carrera:** Ingeniería en Computación
-   **Materia:** Probabilidad y Estadística

## Tecnologías Utilizadas

El proyecto está construido utilizando el siguiente stack de tecnologías:

-   **Backend:**
    -   [Laravel](https://laravel.com/) - Framework de PHP para aplicaciones web.
    -   [Fortify](https://laravel.com/docs/fortify) - Para la autenticación de usuarios.
-   **Frontend:**
    -   [Vue.js](https://vuejs.org/) - Framework progresivo de JavaScript.
    -   [TypeScript](https://www.typescriptlang.org/) - Superset de JavaScript con tipado estático.
    -   [Inertia.js](https://inertiajs.com/) - Para crear aplicaciones de una sola página (SPA) sin construir una API.
    -   [Tailwind CSS](https://tailwindcss.com/) - Framework de CSS para diseño de interfaces.

## Instalación y Puesta en Marcha

Sigue estos pasos para configurar el entorno de desarrollo local:

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/JoanGarfias/curvex.git
    cd curvex
    ```

2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```

3.  **Instalar dependencias de Node.js:**
    ```bash
    npm install
    ```

4.  **Configurar el entorno:**
    Copia el archivo de ejemplo `.env.example` y configúralo con tus credenciales de base de datos.
    ```bash
    cp .env.example .env
    ```

5.  **Generar la clave de la aplicación:**
    ```bash
    php artisan key:generate
    ```

6.  **Ejecutar las migraciones de la base de datos:**
    Asegúrate de que tu servidor de base de datos esté en ejecución.
    ```bash
    php artisan migrate
    ```

7.  **Compilar los assets del frontend:**
    ```bash
    npm run dev
    ```

8.  **Iniciar el servidor de desarrollo:**
    En una terminal separada, ejecuta:
    ```bash
    php artisan serve
    ```

La aplicación estará disponible en `http://localhost:8000`.

## Características Principales

-   **Autenticación de Usuarios:** Registro, inicio de sesión y recuperación de contraseña.
-   **Autenticación de Dos Factores (2FA):** Para una capa extra de seguridad.
-   **Gestión de Perfil:** Los usuarios pueden actualizar su información personal y contraseña.
-   **Cálculos Estadísticos:** Módulo para realizar operaciones estadísticas (detallar aquí las operaciones específicas).
-   **Visualización de Datos:** Gráficas interactivas para representar los resultados.
-   **Diseño Adaptable:** Interfaz de usuario que se adapta a diferentes tamaños de pantalla.
