# Crowdfunding

### Instalación

1. Clone el repositorio luego entre al directorio con `cd`
1. Instalar las dependencias del compositor `composer install`
1. Cambiar el nombre o copiar el `.env.example` a `.env`
1. Establecer clave de aplicación `php artisan key:generate`
1. Configure las credenciales de su base de datos en su archivo `.env`
1. Establezca su `APP_URL` en su archivo `.env`
1. Migrar con `php artisan migrate`
1. Crear el enlace para el amacenamiento : `php artisan storage:link`
1. `php artisan serve` or use Laravel Valet or Laravel Homestead
1. Visita `localhost:8000` en tu navegador