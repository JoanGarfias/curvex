
### Pruebas de Hipótesis para una Proporción

#### Caso 1: Prueba de dos colas con Corrección de Continuidad (Default)

**Contexto:** Se sospecha que una moneda está trucada. Se lanza 100 veces y salen 62 caras. ¿Es la moneda justa ($p=0.5$)?
**Nota:** Al ser `continuity: true` (por defecto), el código ajustará el valor de X acercándolo 0.5 hacia la media esperada.

**Entrada (JSON):**

```json
{
    "x": 62,
    "n": 100,
    "p0": 0.5,
    "alpha": 0.05,
    "tail": "two",
    "continuity": true
}
```

**Salida Generada (JSON):**

```json
{
    "x": 62,
    "n": 100,
    "p_hat": 0.62,
    "p0": 0.5,
    "alpha": 0.05,
    "tail": "two",
    "continuity": true,
    "adjusted_x": 61.5,
    "np0": 50,
    "sd": 5,
    "z": 2.3,
    "p_value": 0.0214482,
    "critical": 1.9599639,
    "reject": true
}
```

*Interpretación:* Como el valor Z (2.3) es mayor que el crítico (1.96), o el p-value (0.021) es menor a alpha (0.05), se rechaza la hipótesis nula. La moneda probablemente está trucada.

-----

#### Caso 2: Prueba de cola derecha SIN Corrección de Continuidad

**Contexto:** Un medicamento afirma tener una tasa de curación superior al 80%. En una muestra de 200 pacientes, 168 se curaron.
**Fuente del ejemplo:** Adaptado de conceptos estándar de *Lumen Learning - Hypothesis Testing for a Proportion*.

**Entrada (JSON):**

```json
{
    "x": 168,
    "n": 200,
    "p0": 0.80,
    "alpha": 0.05,
    "tail": "right",
    "continuity": false
}
```

**Salida Generada (JSON):**

```json
{
    "x": 168,
    "n": 200,
    "p_hat": 0.84,
    "p0": 0.8,
    "alpha": 0.05,
    "tail": "right",
    "continuity": false,
    "adjusted_x": 168,
    "np0": 160,
    "sd": 5.6568542,
    "z": 1.41421356,
    "p_value": 0.0786496,
    "critical": 1.6448536,
    "reject": false
}
```

*Interpretación:* Aunque 84% es mayor que 80%, el valor Z (1.41) no supera el crítico (1.645) para este tamaño de muestra. No hay evidencia suficiente para afirmar que el medicamento es superior al 80%.

-----

### Detalles Técnicos de la Salida

  * **`p_hat`**: La proporción observada en la muestra ($x/n$).
  * **`adjusted_x`**: El número de éxitos modificado. Si `continuity` es `true`, resta o suma 0.5 para suavizar la aproximación de la binomial a la normal. Si es `false`, es igual a `x`.
  * **`sd`**: La desviación estándar bajo la hipótesis nula: $\sqrt{n \cdot p_0 \cdot (1-p_0)}$.
  * **`z`**: El estadístico de prueba calculado.
  * **`critical`**: El valor Z límite calculado usando la función inversa de la normal (`normalQuantile`). Depende de si la prueba es de 1 o 2 colas.
  * **`reject`**: Booleano final. `true` indica que se debe rechazar la Hipótesis Nula.

### Ejemplo usando el ejercicio 4 de la clase del miercoles

-----

Entrada:
```json
{
    "x": 41,
    "n": 250,
    "p0": 0.1,
    "alpha": 0.05,
    "tail": "two",
    "continuity": true
}
```

Salida:
```json
{
	"x": 41,
	"n": 250,
	"p_hat": 0.164,
	"p0": 0.1,
	"alpha": 0.05,
	"tail": "two",
	"continuity": true,
	"adjusted_x": 40.5,
	"np0": 25,
	"sd": 4.743416490252569,
	"z": 3.2676869155073254,
	"p_value": 0.0010844232694748879,
	"critical": 1.959962802939117,
	"reject": true
}
```

Los valores importantes (tomando como referencia el codigo de las tablas) son z (que es z0), critical(za/2) y reject (que seria el equivalente a veredicto, pero en booleano).