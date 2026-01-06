# Tests de Regresión

Este documento describe los tests unitarios y de integración para el servicio de regresión lineal.

## Tests Unitarios (RegresionServiceTest)

Los tests unitarios verifican la funcionalidad del `RegresionService` de forma aislada:

### Casos de Prueba

1. **Regresión Lineal Perfecta**
   - Verifica que R² ≈ 1.0 para datos que forman una línea perfecta (y = 2x + 1)
   - Datos: (1,3), (2,5), (3,7), (4,9), (5,11)

2. **Correlación Fuerte**
   - Verifica que R² > 0.9 para datos con correlación fuerte pero no perfecta
   - Datos: (1,2.1), (2,4.0), (3,5.9), (4,8.1), (5,10.0)

3. **Correlación Débil**
   - Verifica que 0 ≤ R² ≤ 1 para datos con correlación débil
   - Datos: (1,2), (2,3), (3,2), (4,5), (5,4)

4. **Valores Negativos**
   - Verifica cálculo correcto con pendiente negativa (y = -x + 10)
   - Datos: (1,9), (2,8), (3,7), (4,6), (5,5)

5. **Valores Decimales**
   - Verifica manejo correcto de coordenadas con decimales
   - Datos: (1.5,3.2), (2.3,4.8), (3.1,6.5), (4.7,9.1), (5.9,11.3)

6. **Conjunto Mínimo de Puntos**
   - Verifica que con 2 puntos R² = 1.0
   - Datos: (1,2), (2,4)

7. **Valores Grandes**
   - Verifica estabilidad numérica con valores grandes
   - Datos: (1000,2000), (2000,4000), (3000,6000), (4000,8000), (5000,10000)

8. **Valores X Repetidos**
   - Verifica manejo de puntos con mismo valor x pero diferentes y
   - Datos: (1,2), (1,3), (2,4), (3,5), (4,6)

9. **Ejemplo Real**
   - Verifica con dataset típico de un problema de regresión
   - 8 puntos de datos con correlación esperada > 0.9

10. **Línea Horizontal (Caso Especial)**
    - Verifica que se lanza `DivisionByZeroError` cuando SST = 0
    - Datos: (1,5), (2,5), (3,5), (4,5), (5,5)

11. **Manejo de Excepciones**
    - Verifica comportamiento con datos problemáticos
    - Datos: (0,0), (0,0)

## Tests de Integración (RegresionEndpointTest)

Los tests de integración verifican el endpoint `/calc-regresion` completo:

### Casos de Prueba

1. **Cálculo a través del Endpoint**
   - Verifica respuesta HTTP 200 y estructura JSON correcta
   - Formato: `values=1,3;2,5;3,7;4,9;5,11`

2. **Datos con Correlación Fuerte**
   - Verifica R² > 0.9 a través del endpoint
   - Formato: `values=1,2.1;2,4.0;3,5.9;4,8.1;5,10.0`

3. **Valores Decimales**
   - Verifica que el endpoint acepta coordenadas decimales
   - Formato: `values=1.5,3.2;2.3,4.8;...`

4. **Método por Defecto**
   - Verifica que usa 'lineal' cuando no se especifica método
   - Sin campo `method` en la request

5. **Validación de Campos Requeridos**
   - Verifica respuesta 422 cuando falta el campo `values`
   - Verifica errores de validación en JSON

6. **Valores Negativos**
   - Verifica cálculo correcto con coordenadas negativas
   - Formato: `values=1,9;2,8;3,7;4,6;5,5`

7. **Múltiples Puntos**
   - Verifica manejo de datasets grandes (10 puntos)
   - Formato: `values=1,2.5;2,3.1;...;10,11.2`

8. **Mínimo de Puntos**
   - Verifica funcionamiento con solo 2 puntos
   - Formato: `values=1,2;2,4`

9. **Formato de Respuesta**
   - Verifica estructura JSON: `{message, data: {R2}}`
   - Verifica mensaje de éxito correcto

10. **Valores Grandes**
    - Verifica estabilidad con números grandes
    - Formato: `values=1000,2000;2000,4000;...`

## Ejecutar los Tests

### Todos los tests de regresión
```bash
php artisan test --filter=Regresion
```

### Solo tests unitarios
```bash
php artisan test --filter=RegresionService
```

### Solo tests de integración
```bash
php artisan test --filter=RegresionEndpoint
```

## Cobertura

- **Tests unitarios**: 11 tests, 30 assertions
- **Tests de integración**: 10 tests, 40 assertions
- **Total**: 21 tests, 70 assertions
- **Cobertura**: 100% de las funcionalidades del servicio de regresión

## Notas

- Los tests utilizan **Pest PHP** como framework
- Se mockea el facade `Log` para evitar dependencias en tests unitarios
- Los tests de integración verifican tanto la lógica del negocio como las validaciones HTTP
- Se prueban casos edge: división por cero, valores extremos, correlaciones perfectas y débiles
