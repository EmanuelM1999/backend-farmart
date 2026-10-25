# AI_USAGE.md — Uso de inteligencia artificial

## Herramientas utilizadas

**ChatGPT (OpenAI)** fue la herramienta de inteligencia artificial utilizada como apoyo durante el desarrollo de Farmart IPS. Se empleó como asistente de programación, no como sustituto de la revisión técnica ni de las pruebas del desarrollador.

## Tareas en las que se utilizó

- **Estilos del frontend:** obtener sugerencias para organizar y mejorar la presentación visual de los componentes de React.
- **Simulación del asistente de IA:** generar ideas y estructuras de datos mock para simular respuestas durante el desarrollo, sin depender de un servicio de IA real para cada prueba.
- **Integración entre controladores y servicios:** acelerar la escritura de llamadas a los servicios existentes desde los controladores de Laravel, manteniendo la separación de responsabilidades.

Las sugerencias se revisaron y adaptaron al código, a la estructura del proyecto y a los requisitos funcionales.

## Ejemplo de una sugerencia aceptada

Se utilizó ChatGPT para proponer datos mock que permitieran simular las respuestas del asistente de IA y probar la interacción desde la aplicación sin depender de una integración real durante esa etapa del desarrollo.

Esto facilitó validar el flujo de la interfaz y trabajar en su presentación mientras se desarrollaba la funcionalidad.

## Ejemplo de una sugerencia rechazada o corregida

En una ocasión, una propuesta generaba la lógica de negocio directamente dentro de los controladores de Laravel, en lugar de utilizar los servicios que ya existían en el proyecto.

Se corrigió la propuesta para que los controladores delegaran las operaciones a los servicios correspondientes. Esta decisión mantiene la separación de responsabilidades, favorece la reutilización de la lógica y facilita las pruebas y el mantenimiento.

## Validación y responsabilidad

El código generado o sugerido por IA se considera una propuesta que debe ser revisada por el desarrollador. Se comprueba su coherencia con la arquitectura, las reglas de negocio, la seguridad y el comportamiento esperado antes de incorporarlo.

El desarrollador es responsable del código entregado y debe poder explicar, modificar y mantener las decisiones tomadas durante la sustentación.

## Privacidad y manejo de datos

No se utilizaron datos reales de pacientes ni información confidencial de la IPS en las solicitudes a la herramienta. Para las pruebas y simulaciones se utilizaron datos ficticios o mock.

Las credenciales, los tokens, las contraseñas y otros secretos no deben compartirse con herramientas de IA.

## Limitaciones

Las respuestas de ChatGPT pueden contener errores, proponer soluciones que no respeten la arquitectura existente o asumir requisitos que no se han especificado. Por ello, las sugerencias se evaluaron y ajustaron según las necesidades reales de Farmart IPS.

El uso de datos mock permite probar flujos de interacción, pero no demuestra por sí solo la calidad, precisión o confiabilidad de un modelo de IA en producción.
