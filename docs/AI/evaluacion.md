# Evaluación del asistente de IA

## Ejecución

La evaluación automatizada del asistente se ejecuta mediante el siguiente comando desde el backend:

```bash
php artisan inventory-ai:evaluate
```

Si el backend se ejecuta en Docker, el comando puede ejecutarse dentro del contenedor PHP:

```bash
docker compose exec api php artisan inventory-ai:evaluate
```

## Propósito

El comando permite ejecutar los casos de evaluación definidos para el asistente de IA y comprobar que las respuestas cumplen las condiciones esperadas.

## Criterios de evaluación

- Respuesta válida para entradas admitidas.
- Manejo de entradas vacías o inválidas.
- Respuesta controlada ante preguntas fuera del alcance del asistente.
- Cumplimiento de las condiciones de salida definidas en cada caso.

Los casos deben utilizar datos ficticios y no incluir información real de pacientes ni datos confidenciales de la IPS.

**Nota:** los criterios concretos y el resultado de la evaluación deben corresponder a las validaciones realmente implementadas por el comando.
