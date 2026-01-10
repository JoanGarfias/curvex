# Guía de Uso de la API de Regresión

## Descripción

Esta API permite realizar cálculos de regresión lineal con una o múltiples variables independientes. 

## Estructura de la Solicitud

### Endpoint
```
POST /api/regresion/calcular
Content-Type: application/json
```

### Parámetros

| Campo | Tipo | Descripción | Requerido |
|-------|------|-------------|-----------|
| `dependent` | string | Valores de la variable dependiente (y) separados por comas | Sí |
| `independent` | array | Array de strings con valores de variables independientes separados por comas | Sí |
| `method` | string | Método de regresión: "lineal" o "exponencial" (default: "lineal") | No |

### Restricciones

- **Cantidad de datos**: Todas las variables (dependiente e independientes) DEBEN tener exactamente la misma cantidad de datos
- **Formato**: Los valores deben ser números (enteros o decimales), separados por comas
- **Espacios**: Se permiten espacios alrededor de las comas: `"1, 2, 3"` es válido
- **Variables negativas**: Se soportan valores negativos: `"-1, -2, -3"`

## Ejemplos de Uso

### 1. Regresión Simple (1 variable independiente)

**Datos:**
- X: 1, 2, 3, 4, 5
- Y: 2, 4, 6, 8, 10

**Solicitud:**
```json
{
  "dependent": "2,4,6,8,10",
  "independent": ["1,2,3,4,5"],
  "method": "lineal"
}
```

### 2. Regresión Múltiple (2 variables independientes)

**Datos:**
- U: 1, 2, 3, 4, 5
- V: 2, 3, 4, 5, 6
- Y: 5, 11, 17, 23, 29

**Solicitud:**
```json
{
  "dependent": "5,11,17,23,29",
  "independent": [
    "1,2,3,4,5",
    "2,3,4,5,6"
  ],
  "method": "lineal"
}
```

### 3. Regresión Múltiple (3 variables independientes)

**Datos:**
- U: 1, 2, 3, 4, 5
- V: 2, 3, 4, 5, 6
- Z: 3, 4, 5, 6, 7
- Y: 14, 29, 44, 59, 74

**Solicitud:**
```json
{
  "dependent": "14,29,44,59,74",
  "independent": [
    "1,2,3,4,5",
    "2,3,4,5,6",
    "3,4,5,6,7"
  ],
  "method": "lineal"
}
```

### 4. Con Valores Decimales

**Solicitud:**
```json
{
  "dependent": "3.2,4.8,7.1,8.5,10.2",
  "independent": ["1.5,2.3,3.7,4.2,5.1"]
}
```

### 5. Con Valores Negativos

**Solicitud:**
```json
{
  "dependent": "-4,-2,0,2,4",
  "independent": ["-2,-1,0,1,2"]
}
```

## Respuesta Exitosa

```json
{
  "message": "Cálculo de regresión realizado con éxito.",
  "data": {
    "R2": 0.98,
    "method": "lineal",
    "independent_variables_count": 1,
    "data_points_count": 5
  }
}
```

### Campos de la Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `R2` | float | Coeficiente de determinación (entre 0 y 1) |
| `method` | string | Método utilizado para el cálculo |
| `independent_variables_count` | int | Cantidad de variables independientes utilizadas |
| `data_points_count` | int | Cantidad de puntos de datos |

## Respuestas de Error

### Error 422: Datos Incorrectos

**Causa:** Estructura de datos inválida o validación fallida

**Ejemplo - Cantidad de datos diferentes:**
```json
{
  "message": "Datos incorrectos.",
  "errors": {
    "independent.0": [
      "La variable independiente #0 tiene 2 datos, pero la variable dependiente tiene 3. Todas deben tener la misma cantidad de datos."
    ]
  }
}
```

**Ejemplo - Variable dependiente ausente:**
```json
{
  "message": "Datos incorrectos.",
  "errors": {
    "dependent": [
      "Debe proporcionar los datos de la variable dependiente (y)."
    ]
  }
}
```

**Ejemplo - Sin variables independientes:**
```json
{
  "message": "Datos incorrectos.",
  "errors": {
    "independent": [
      "Debe proporcionar al menos una variable independiente."
    ]
  }
}
```

### Error 500: Error del Servidor

**Causa:** Error interno durante el cálculo

```json
{
  "message": "Error al realizar el cálculo de regresión.",
  "error": "Descripción del error"
}
```

## Ejemplos con cURL

### Regresión Simple
```bash
curl -X POST http://localhost:8000/api/regresion/calcular \
  -H 'Content-Type: application/json' \
  -d '{
    "dependent": "2,4,6,8,10",
    "independent": ["1,2,3,4,5"],
    "method": "lineal"
  }'
```

### Regresión Múltiple
```bash
curl -X POST http://localhost:8000/api/regresion/calcular \
  -H 'Content-Type: application/json' \
  -d '{
    "dependent": "5,11,17,23,29",
    "independent": ["1,2,3,4,5", "2,3,4,5,6"],
    "method": "lineal"
  }'
```

## Logs

La API genera logs detallados de cada cálculo realizado. Puedes revisar los logs en:

```
storage/logs/laravel.log
```

Los logs incluyen:
- ✅ Validación de datos
- 🔍 Detalles del cálculo de SSE y SST
- 🔢 Información de R²
- ❌ Errores detallados si los hay

Ejemplo de logs:
```
[2026-01-10] [INFO] 📥 Iniciando cálculo de regresión
[2026-01-10] [INFO] Variable dependiente (Y): 5 datos
[2026-01-10] [INFO] Total de variables independientes: 1
[2026-01-10] [INFO] 🔢 Iniciando cálculo de R² para regresión lineal
[2026-01-10] [INFO] 📊 Estadísticas finales:
[2026-01-10] [INFO] SSE (Sum of Squared Errors): 0
[2026-01-10] [INFO] SST (Total Sum of Squares): 40
[2026-01-10] [INFO] R² = 1 - (SSE/SST) = 1 - (0/40) = 1
[2026-01-10] [INFO] ✅ Cálculo de R² completado. Resultado: 1
```

## Notas Importantes

1. **Formato de datos**: Los valores deben estar separados ÚNICAMENTE por comas (`,`), no por puntos y comas (`;`) u otros separadores
2. **Espacios**: Se ignoran los espacios alrededor de los valores, así que `"1, 2, 3"` es equivalente a `"1,2,3"`
3. **Decimal**: Utiliza punto (`.`) como separador decimal, no coma: `3.14` es correcto, `3,14` es incorrecto
4. **Cantidad de variables**: Puedes tener de 1 a N variables independientes, pero todas deben tener el mismo número de datos que la variable dependiente
5. **Precisión**: Los cálculos se realizan con precisión de punto flotante

