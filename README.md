# Farmart IPS — Backend

API REST desarrollada con Laravel y PostgreSQL para gestionar las funcionalidades de Farmart IPS, incluyendo autenticación, usuarios, roles y gestión de inventario.

## Tecnologías

- PHP y Laravel
- PostgreSQL
- Docker y Docker Compose
- Nginx y PHP-FPM
- API REST con respuestas JSON

## Requisitos previos

- Docker Desktop con Docker Compose.
- Git.
- Archivo de variables de entorno configurado según `.env.example`.

## Ejecución del proyecto

Clonar el repositorio:

```bash
git clone <URL_DEL_REPOSITORIO>
cd backend-farmart
```

Crear el archivo `.env` a partir de la plantilla disponible y configurar las variables necesarias para la aplicación y PostgreSQL.

```bash
cp .env.example .env
```

En Windows, este paso también puede realizarse copiando manualmente `.env.example` como `.env`.

Construir e iniciar los servicios:

```bash
docker compose up --build -d
```

Consultar el estado de los contenedores:

```bash
docker compose ps
```

Si la configuración del proyecto no genera automáticamente la clave de Laravel, ejecutarla mediante Artisan dentro del contenedor PHP correspondiente:

```bash
docker compose exec api php artisan key:generate
```

La generación de la clave debe realizarse antes de utilizar la aplicación por primera vez y conservarse entre reinicios.

**Nota:** el comando anterior presupone que el servicio PHP se llama `api`. Verificar el nombre real en el archivo Compose. Las migraciones deben ejecutarse mediante el mecanismo configurado en el proyecto.

## Acceso a la API

- API local: `http://localhost:8000/api`
- Endpoint de inicio de sesión: `POST /api/login`

La URL y los endpoints deben coincidir con la configuración de Nginx y las rutas registradas en Laravel.

## Documentación de la API

La colección de Postman se encuentra disponible en el directorio de documentación del proyecto o se entrega como archivo independiente. Incluye las solicitudes necesarias para probar los endpoints, sus parámetros y las respuestas esperadas.

## Decisiones de diseño y trade-offs

1. **Laravel con arquitectura basada en servicios:** separar la lógica de negocio de los controladores facilita el mantenimiento y la reutilización del código, aunque requiere definir y mantener clases adicionales.
2. **PostgreSQL como base de datos relacional:** permite gestionar relaciones e integridad de los datos; como contrapartida, requiere administrar migraciones, respaldos y recuperación.
3. **Docker Compose:** permite reproducir el entorno de desarrollo y simplifica la ejecución de los servicios; sin embargo, por sí solo no proporciona alta disponibilidad ni orquestación avanzada.
4. **Separación entre frontend y backend:** permite evolucionar y desplegar ambos componentes independientemente, a costa de coordinar los cambios en sus contratos de API.

## Supuestos

- El frontend consume la API mediante HTTP y utiliza la URL definida en su configuración de entorno.
- Las credenciales y secretos se configuran mediante variables de entorno y no se incluyen en el repositorio.
- Los datos utilizados para pruebas son ficticios y no corresponden a pacientes reales ni a información confidencial de la IPS.

## Fuera del alcance

- Despliegue automatizado mediante CI/CD, mientras los pipelines no estén implementados.
- Alta disponibilidad y escalamiento automático de la infraestructura.
- Monitoreo centralizado y recuperación automatizada ante desastres.

Estos elementos pueden incorporarse en futuras iteraciones según los requisitos de producción.
