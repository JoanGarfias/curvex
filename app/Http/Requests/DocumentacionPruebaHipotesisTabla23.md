
# Documentación de Pruebas de Hipótesis para la Media (t-Student) aplicado en la tabla 2.3

Este documento detalla los seis modos de operación (casos de prueba de hipótesis) implementados en el `PruebaHipotesisNoVarianzaController`.

El sistema se divide en dos secciones principales, basadas en el tipo de prueba estadística:
1.  **Casos 0, 1, 2:** Pruebas de Hipótesis para **una sola media ($\mu$)** (Estadístico $t_0$).
2.  **Casos 3, 4, 5:** Pruebas de Hipótesis para la **diferencia de dos medias ($\mu_1 - \mu_2$)** (Estadístico $t_0$).

---

## 1. Modos de Entrada (Casos 0, 1, 2): Prueba para una Media

Estos modos utilizan la distribución **t de Student** para probar una hipótesis sobre la media de una sola población ($\mu$), asumiendo que la varianza poblacional ($\sigma^2$) es **desconocida** y se utiliza la varianza muestral ($S^2$).

### 1.1. Parámetros de Entrada Requeridos

| Campo | Tipo | Descripción | Requerido |
| :--- | :--- | :--- | :--- |
| `modo` | `integer` | Define la hipótesis alternativa (0, 1 o 2). | Sí |
| `promedio` | `numeric` | Media muestral ($\bar{x}$). | Sí |
| `varianza` | `numeric` | **Varianza** muestral ($S$). | Sí |
| `u0` | `numeric` | Valor hipotético de la media ($\mu_0$). | Sí |
| `cantidad` | `numeric` | Tamaño de la muestra ($n$). | Sí |
| `confiabilidad` | `numeric` | Nivel de Confianza ($1-\alpha$). | Sí |

### 1.2. Fórmulas de Cálculo

El estadístico de prueba $t_0$ (t-student) se calcula como:

$$
t_0 = \frac{\bar{x} - \mu_0}{S / \sqrt{n}}
$$

Los grados de libertad son $v = n - 1$.

### 1.3. Detalles de los Modos

| Modo | Hipótesis Nula ($H_0$) | Hipótesis Alternativa ($H_a$) | Tipo de Prueba | Criterio de Rechazo ($H_0$) |
| :---: | :--- | :--- | :--- | :--- |
| **0** | $\mu = \mu_0$ | $\mu \neq \mu_0$ | **Bilateral** (Dos Colas) | $\|t_0\| > t_{\alpha/2, v}$ |
| **1** | $\mu \geq \mu_0$ | $\mu < \mu_0$ | **Unilateral Izquierda** | $t_0 < -t_{\alpha, v}$ |
| **2** | $\mu \leq \mu_0$ | $\mu > \mu_0$ | **Unilateral Derecha** | $t_0 > t_{\alpha, v}$ |

---

## 2. Modos de Entrada (Casos 3, 4, 5): Prueba para Dos Medias

Estos modos utilizan la distribución **t de Student** para probar una hipótesis sobre la **diferencia entre dos medias poblacionales** ($\mu_1 - \mu_2$), asumiendo que las varianzas poblacionales son **desconocidas**.

### 2.1. Parámetros de Entrada y Lógica Condicional de Varianza

La elección de la fórmula del estadístico $t_0$ y los grados de libertad ($v$) depende de si las varianzas se asumen como iguales (combinadas, *pooled*) o diferentes (separadas).

| Campo | Tipo | Descripción | Condicionalidad |
| :--- | :--- | :--- | :--- |
| `modo` | `integer` | Define la hipótesis alternativa (3, 4 o 5). | Sí |
| `promedio1`, `promedio2` | `numeric` | Medias muestrales ($\bar{x}_1, \bar{x}_2$). | Sí |
| `cantidad1`, `cantidad2` | `numeric` | Tamaños de muestra ($n_1, n_2$). | Sí |
| `confiabilidad` | `numeric` | Nivel de Confianza ($1-\alpha$). | Sí |
| **`varianzap`** | `numeric` | Varianza combinada (opcional). | **Obligatorio** si `varianza1` y `varianza2` están ausentes. |
| **`varianza1`**, **`varianza2`** | `numeric` | Varianzas muestrales separadas (opcional). | **Obligatorio** si `varianzap` está ausente. |
| **`boolEsVarianzaUnica`** | `boolean` | **Indica si la varianza debe calcularse a partir de $S_1^2$ y $S_2^2$** (se asume $\sigma_1^2 = \sigma_2^2$). Si este campo está presente, el programa calcula `varianzap` internamente y usa la fórmula de Varianza Combinada. | Opcional (`nullable`). |

### 2.2. Lógica Interna de Varianza

| Condición de Entrada | Bandera Interna (`modovarp`) | Tipo de Prueba t | Grados de Libertad ($v$) |
| :--- | :--- | :--- | :--- |
| Se proporciona `varianzap` **O** se proporciona `boolEsVarianzaUnica` (`true`) junto con `varianza1` y `varianza2`. | `true` (Varianza Combinada) | Se asume $\sigma_1^2 = \sigma_2^2$. | $v = n_1 + n_2 - 2$ |
| Se proporcionan `varianza1` y `varianza2` (y no `varianzap` ni `boolEsVarianzaUnica`). | `false` (Varianza Separada) | Se asume $\sigma_1^2 \neq \sigma_2^2$. | $v \approx$ Aproximación de Welch. |

### 2.3. Detalles de los Modos

| Modo | Hipótesis Nula ($H_0$) | Hipótesis Alternativa ($H_a$) | Tipo de Prueba | Criterio de Rechazo ($H_0$) |
| :---: | :--- | :--- | :--- | :--- |
| **3** | $\mu_1 = \mu_2$ | $\mu_1 \neq \mu_2$ | **Bilateral** (Dos Colas) | $\|t_0\| > t_{\alpha/2, v}$ |
| **4** | $\mu_1 \geq \mu_2$ | $\mu_1 < \mu_2$ | **Unilateral Izquierda** | $t_0 < -t_{\alpha, v}$ |
| **5** | $\mu_1 \leq \mu_2$ | $\mu_1 > \mu_2$ | **Unilateral Derecha** | $t_0 > t_{\alpha, v}$ |

---

## 3. Salidas del Programa (Output)

El controlador devuelve un objeto JSON que contiene los resultados clave de la prueba de hipótesis, independientemente del modo.

| Campo de Salida | Descripción | Ejemplo |
| :--- | :--- | :--- |
| **`t0`** | **Estadístico de Prueba (t-calculated)**. Es el valor calculado a partir de los datos de entrada, que se compara con el valor crítico. | $2.35$ |
| **`ta`** | **Valor Crítico (t-alpha)**. Es el valor umbral (o par de umbrales) obtenido de la distribución t de Student para un nivel de significancia $\alpha$ dado. Determina la región de rechazo. | $1.96$ |
| **`veredicto`** | **Conclusión** de la prueba basada en la comparación entre $t_0$ y $t_{\alpha}$ (o sus equivalentes unilaterales). | "Se rechaza la hipotesis nula." |

### Criterio de Veredicto (Resumen)

* **Si $|t_0|$ cae en la Región de Rechazo (definida por $t_{\alpha}$):** El programa indica que **"Se rechaza la hipotesis nula."** (Existe suficiente evidencia estadística para apoyar la hipótesis alternativa $H_a$).
* **Si $|t_0|$ cae fuera de la Región de Rechazo:** El programa indica que **"Se aplica la hipotesis nula."** (No hay suficiente evidencia estadística para rechazar la hipótesis nula $H_0$).