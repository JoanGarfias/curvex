
## Documentación de Pruebas de Hipótesis para la Media (Varianza Conocida / Z-Test) aplicado en la tabla 2.2

Este programa (`PruebaHipotesisVarianzaController`) está diseñado para realizar **Pruebas de Hipótesis para la Media ($\mu$)** asumiendo que la **Varianza Poblacional ($\sigma^2$) es Conocida**, utilizando la **Distribución Normal Estándar** (Prueba Z).

---

## 1. Modos de Entrada (Casos 0, 1, 2): Prueba Z para una Media

Estos modos utilizan el estadístico **Z** para probar una hipótesis sobre la media de una población, asumiendo que la **desviación estándar poblacional ($\sigma$) es conocida**.

### 1.1. Parámetros de Entrada Requeridos

| Campo | Tipo | Descripción | Requerido |
| :--- | :--- | :--- | :--- |
| `modo` | `integer` | Define la hipótesis alternativa (0, 1 o 2). | Sí |
| `promedio` | `numeric` | Media muestral ($\bar{x}$). | Sí |
| **`desviacion`** | `numeric` | **Desviación Estándar Poblacional ($\sigma$)**. | Sí |
| `u0` | `numeric` | Valor hipotético de la media ($\mu_0$). | Sí |
| `cantidad` | `numeric` | Tamaño de la muestra ($n$). | Sí |
| `confiabilidad` | `numeric` | Nivel de Confianza ($1-\alpha$). | Sí |

### 1.2. Fórmulas de Cálculo

El **Estadístico de Prueba** $z_0$ se calcula como:

$$
z_0 = \frac{\bar{x} - \mu_0}{\sigma / \sqrt{n}}
$$

### 1.3. Detalles de los Modos

| Modo | Hipótesis Nula ($H_0$) | Hipótesis Alternativa ($H_a$) | Tipo de Prueba | Criterio de Rechazo ($H_0$) |
| :---: | :--- | :--- | :--- | :--- |
| **0** | $\mu = \mu_0$ | $\mu \neq \mu_0$ | **Bilateral** (Dos Colas) | $\|z_0\| > z_{\alpha/2}$ |
| **1** | $\mu \geq \mu_0$ | $\mu < \mu_0$ | **Unilateral Izquierda** | $z_0 < -z_{\alpha}$ |
| **2** | $\mu \leq \mu_0$ | $\mu > \mu_0$ | **Unilateral Derecha** | $z_0 > z_{\alpha}$ |

---

## 2. Modos de Entrada (Casos 3, 4, 5): Prueba Z para Dos Medias

Estos modos utilizan el estadístico **Z** para probar una hipótesis sobre la **diferencia entre dos medias poblacionales** ($\mu_1 - \mu_2$), asumiendo que las **desviaciones estándar poblacionales ($\sigma_1, \sigma_2$) son conocidas**.

### 2.1. Parámetros de Entrada Requeridos

| Campo | Tipo | Descripción | Requerido |
| :--- | :--- | :--- | :--- |
| `modo` | `integer` | Define la hipótesis alternativa (3, 4 o 5). | Sí |
| `promedio1`, `promedio2` | `numeric` | Medias muestrales ($\bar{x}_1, \bar{x}_2$). | Sí |
| **`desviacion1`**, **`desviacion2`** | `numeric` | **Desviaciones Estándar Poblacionales ($\sigma_1, \sigma_2$)**. | Sí |
| `cantidad1`, `cantidad2` | `numeric` | Tamaños de muestra ($n_1, n_2$). | Sí |
| `confiabilidad` | `numeric` | Nivel de Confianza ($1-\alpha$). | Sí |

### 2.2. Fórmulas de Cálculo

El **Estadístico de Prueba** $z_0$ se calcula utilizando la fórmula para dos muestras independientes con varianzas conocidas:

$$
z_0 = \frac{(\bar{x}_1 - \bar{x}_2)}{\sqrt{\frac{\sigma_1^2}{n_1} + \frac{\sigma_2^2}{n_2}}}
$$

### 2.3. Detalles de los Modos

| Modo | Hipótesis Nula ($H_0$) | Hipótesis Alternativa ($H_a$) | Tipo de Prueba | Criterio de Rechazo ($H_0$) |
| :---: | :--- | :--- | :--- | :--- |
| **3** | $\mu_1 = \mu_2$ | $\mu_1 \neq \mu_2$ | **Bilateral** (Dos Colas) | $\|z_0\| > z_{\alpha/2}$ |
| **4** | $\mu_1 \geq \mu_2$ | $\mu_1 < \mu_2$ | **Unilateral Izquierda** | $z_0 < -z_{\alpha}$ |
| **5** | $\mu_1 \leq \mu_2$ | $\mu_1 > \mu_2$ | **Unilateral Derecha** | $z_0 > z_{\alpha}$ |

---

## 3. Salidas del Programa (Output) 

El controlador devuelve un objeto JSON que contiene los resultados clave de la prueba de hipótesis.

| Campo de Salida | Descripción | Tipo de Valor | Ejemplo
| :--- | :--- |  :--- | :--- |
| **`z0`** | **Estadístico de Prueba (Z-calculated)**. Es el valor calculado a partir de los datos de entrada. | `float` | 2.5155 |
| **`za`** | **Valor Crítico (Z-alpha)**. Es el valor umbral (o par de umbrales) obtenido de la distribución Normal Estándar. | `float` | 1.6448 |
| **`veredicto`** | **Conclusión** de la prueba (Basado en la comparación de $z_0$ con $z_\alpha$). | `string` | "Se rechaza la hipotesis nula." |


### Criterio de Veredicto (Resumen)

*  **Si $|t_0|$ cae en la Región de Rechazo (definida por $t_{\alpha}$):** El programa indica que **"Se rechaza la hipotesis nula."** (Existe suficiente evidencia estadística para apoyar la hipótesis alternativa $H_a$).

*  **Si $|t_0|$ cae fuera de la Región de Rechazo:** El programa indica que **"Se aplica la hipotesis nula."** (No hay suficiente evidencia estadística para rechazar la hipótesis nula $H_0$).