# Estrategia de despliegue, rollback y respaldo de base de datos

## 1. Estrategia de despliegue

La aplicación Farmart IPS está dividida en dos repositorios independientes: frontend desarrollado con React + Vite y backend desarrollado con Laravel. Ambos utilizan Docker y Docker Compose, con PostgreSQL como base de datos.

El despliegue se realizará mediante pipelines CI/CD propuestos para cada repositorio:

1. **Integración continua (CI):** ejecutar pruebas, validaciones y construir las imágenes Docker.
2. **Despliegue continuo (CD):** publicar las imágenes y actualizar los servicios en el servidor mediante Docker Compose.
3. **Verificación:** comprobar el estado de los servicios, la conexión a la base de datos y las funcionalidades principales.

Las variables de entorno y credenciales se administrarán de forma segura, fuera de las imágenes Docker.

## 2. Estrategia de rollback

Se conservarán las versiones anteriores de las imágenes Docker para recuperar una versión estable en caso de fallos. Si el nuevo despliegue presenta errores, se restaurará la versión anterior de los servicios afectados.

Las migraciones de base de datos se diseñarán para mantener la compatibilidad entre versiones siempre que sea posible. Si existe riesgo de pérdida o incompatibilidad de datos, se evaluará la restauración de un respaldo antes de reanudar el servicio.

## 3. Respaldo de la base de datos

Se realizarán respaldos periódicos de PostgreSQL mediante `pg_dump`, almacenados fuera del contenedor y del volumen de la base de datos. Se establecerá una política de retención y se restringirá el acceso a los archivos.

Ejemplo de respaldo:

```bash
docker compose exec -T postgres pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" -Fc > backup.dump
```

## 4. Restauración de la base de datos

Ante una pérdida o corrupción de información, se verificará el respaldo y se restaurará en una base de datos compatible. La operación se realizará de forma controlada, con un respaldo previo del estado actual.

Ejemplo de restauración:

```bash
docker compose exec -T postgres pg_restore -U "$POSTGRES_USER" -d "$POSTGRES_DB" --clean --if-exists < backup.dump
```

Finalmente, se validará la integridad de los datos y el funcionamiento de la aplicación antes de reanudar el servicio.

**Estado de implementación:** Docker y Docker Compose están implementados. Los pipelines CI/CD y la automatización de respaldos y restauración están propuestos y pendientes de implementación, si todavía no se han configurado.
