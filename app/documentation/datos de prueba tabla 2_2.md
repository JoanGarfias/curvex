Tabla 2.2

Datos para casos 0,1,2
Se ve en este link: https://support.ptc.com/help/mathcad/r10.0/es/index.html#page/PTC_Mathcad_Help/example_hypothesis_test_of_a_normal_mean.html

No se si es de alli pero como ejemplo:
```json
{
	"confiabilidad" : 0.9,
	"cantidad" : 50,
	"desviacion" : 1.5,
	"promedio" : 1.617,
	"u0": 2,
	"modo": 2
}
```

genera

```json
{
	"z0": -1.8054793146296515,
	"za": 1.2815515655446001,
	"veredicto": "Se aplica la hipotesis nula."
}
```

Datos para 3,4,5
Sacados de este video: https://www.youtube.com/watch?v=X4IYPdHJgUw
Con explicacion de Gemini
Tambien hay ejercicios en este link: https://www.studocu.com/es-mx/document/instituto-tecnologico-de-huatabampo/estadistica-inferencial/37-prueba-de-hipotesis-para-la-diferencia-de-medias/77249506

Pregunta: Explica paso a paso el resultado de este ejercicio

Ejemplo: En una industria se observa que existe una diferencia clara en el total de las ventas realizadas por sus tiendas. 
Las tiendas minoristas ubicadas al sur en una muestra de un mes lograron un promedio de $2.000.000 por día con una desviación estándar de $3.500 y en una muestra a tiendas minoristas del norte en mes y medio con un promedio de ventas de $2.500.00 con una desviación estándar de $3.000 y un nivel de significancia de 0,05. 
¿Se puede decir que las tiendas del norte venden más que las del sur y defina donde es mejor colocar una tienda?

Este es un problema clásico de **prueba de hipótesis para la diferencia de medias** con muestras grandes.

Para resolverlo, primero debemos organizar la información y corregir un error de escritura muy probable en el enunciado (la media del norte dice "$2.500.00", pero para que el ejercicio tenga sentido comparativo con los "$2.000.000" del sur, asumiremos que se refiere a **$2.500.000**).

Aquí tienes la solución paso a paso.

---

### Paso 1: Identificar los datos
Primero, desglosamos la información para las tiendas del Sur (Grupo 1) y las del Norte (Grupo 2).

**Tiendas del Sur (1):**
* **Media ($\bar{x}_1$):** $\$2,000,000$
* **Desviación estándar ($s_1$):** $\$3,500$
* **Tamaño de la muestra ($n_1$):** "Un mes" $\rightarrow$ **30 días**.

**Tiendas del Norte (2):**
* **Media ($\bar{x}_2$):** $\$2,500,000$ (Corregido para consistencia).
* **Desviación estándar ($s_2$):** $\$3,000$
* **Tamaño de la muestra ($n_2$):** "Mes y medio" $\rightarrow$ **45 días**.

**General:**
* **Nivel de significancia ($\alpha$):** 0.05 (5%)

---

### Paso 2: Planteamiento de las Hipótesis
La pregunta es: *"¿Se puede decir que las tiendas del norte venden **más** que las del sur?"*. Esto define nuestra hipótesis alternativa.

* **Hipótesis Nula ($H_0$):** Las ventas del Norte son iguales o menores que las del Sur.
    $$H_0: \mu_{norte} - \mu_{sur} \leq 0$$
* **Hipótesis Alternativa ($H_1$):** Las ventas del Norte son mayores que las del Sur.
    $$H_1: \mu_{norte} - \mu_{sur} > 0$$

*Nota:* Esta es una prueba de **cola derecha**.

---

### Paso 3: Determinar el Valor Crítico (Z)
Como las muestras son grandes ($n \geq 30$), utilizamos la distribución normal (Z).

* Para un nivel de significancia $\alpha = 0.05$ en una prueba de cola derecha, buscamos en la tabla Z el valor que deja el 95% del área a la izquierda.
* **Valor Crítico ($Z_{crit}$): 1.645**

**Regla de decisión:** Si nuestro Z calculado es mayor a 1.645, rechazamos la hipótesis nula y aceptamos que el Norte vende más.

---

### Paso 4: Calcular el Estadístico de Prueba
Usamos la fórmula para la diferencia de medias:

$$Z = \frac{(\bar{x}_2 - \bar{x}_1)}{\sqrt{\frac{s_1^2}{n_1} + \frac{s_2^2}{n_2}}}$$

1.  **Diferencia de medias (Numerador):**
    $$2,500,000 - 2,000,000 = 500,000$$

2.  **Error Estándar (Denominador):**
    * Varianza Sur sobre muestra: $\frac{3500^2}{30} = \frac{12,250,000}{30} \approx 408,333.33$
    * Varianza Norte sobre muestra: $\frac{3000^2}{45} = \frac{9,000,000}{45} = 200,000$
    * Suma: $408,333.33 + 200,000 = 608,333.33$
    * Raíz cuadrada: $\sqrt{608,333.33} \approx 779.96$

3.  **Cálculo final de Z:**
    $$Z = \frac{500,000}{779.96} \approx 641.05$$

---

### Paso 5: Conclusión Estadística
Comparamos los valores:
* **Z Calculado:** 641.05
* **Z Crítico:** 1.645

Como $641.05 > 1.645$, el valor cae profundamente en la zona de rechazo.

**Resultado:** Rechazamos la Hipótesis Nula ($H_0$). Existe evidencia estadística abrumadora para afirmar que las ventas promedio del Norte son superiores.

---

### Respuesta Final (Interpretación Gerencial)

**¿Se puede decir que las tiendas del norte venden más que las del sur?**
Sí, con un nivel de confianza del 95%, las tiendas del norte tienen un promedio de ventas significativamente mayor. La diferencia en promedios ($500,000) es inmensa comparada con la pequeña variación diaria (desviación estándar) que presentan las tiendas.

**¿Dónde es mejor colocar una tienda?**
Es mejor colocar la tienda en la **zona Norte**, ya que los datos históricos demuestran un volumen de ventas superior y consistente en comparación con la zona Sur.

---

#### *Nota sobre los datos originales*
*Si en el ejercicio original los datos NO eran 2.5 Millones vs 2 Millones, sino que eran literalmente **$2,500** (Norte) vs **$2,000** (Sur):*
El cálculo de Z sería: $500 / 779.96 = 0.64$.
En ese caso hipotético, $0.64 < 1.645$, por lo que **NO** se rechazaría la hipótesis nula y no se podría afirmar que el Norte vende más. Sin embargo, dado el contexto de "Industria", la interpretación de millones es la más lógica.

Pregunta 2: Podrias aplicar los datos de este ejercicio pero para una prueba de dos colas y una prueba de cola izquierda?

Claro que sí. El cálculo matemático "duro" (el valor de Z calculado) **no cambia**, sigue siendo **641.05** porque los datos (medias, desviaciones y muestras) son los mismos.

Lo que cambia es la **pregunta** que nos hacemos (las hipótesis) y la **regla** para decidir (la zona de rechazo en la gráfica).

Aquí tienes el desglose para ambos casos.

---

### Dato clave que se mantiene
Para ambos casos, recordamos el estadístico de prueba calculado anteriormente:
$$Z_{calculado} = 641.05$$
*(Recuerda: Es un valor positivo inmenso porque la media del Norte es mucho mayor que la del Sur).*

---

### Caso 1: Prueba de Dos Colas (Bilateral)
**Pregunta:** "¿Existe alguna **diferencia** entre las ventas del Norte y del Sur?"
Aquí no nos importa quién vende más, solo queremos saber si son distintos.

**1. Planteamiento de Hipótesis:**
* **$H_0$:** Las ventas son iguales ($\mu_{norte} = \mu_{sur}$).
* **$H_1$:** Las ventas son diferentes ($\mu_{norte} \neq \mu_{sur}$).

**2. Valor Crítico ($Z_{crit}$):**
Como la prueba es de dos colas, dividimos el nivel de significancia ($\alpha = 0.05$) entre dos: $0.025$ para cada lado.
* Buscamos en la tabla Z el área de 0.975 (o 0.025).
* **Z crítico = $\pm 1.96$**

**3. Regla de Decisión:**
Rechazamos $H_0$ si el $Z_{calculado}$ es mayor que $1.96$ o menor que $-1.96$.

**4. Conclusión:**
* $641.05 > 1.96$
* **Decisión:** Se rechaza la Hipótesis Nula.
* **Interpretación:** Existe evidencia estadística para afirmar que **hay una diferencia significativa** entre las ventas de ambas zonas. (No decimos cuál es mejor, solo que no son iguales).

---

### Caso 2: Prueba de Cola Izquierda (Unilateral Izquierda)
**Pregunta:** "¿Venden las tiendas del norte **menos** que las del sur?"
Esta prueba busca demostrar si el rendimiento del Norte es inferior.

**1. Planteamiento de Hipótesis:**
* **$H_0$:** Las ventas del Norte son iguales o mayores que las del Sur ($\mu_{norte} \geq \mu_{sur}$).
* **$H_1$:** Las ventas del Norte son menores que las del Sur ($\mu_{norte} < \mu_{sur}$).

**2. Valor Crítico ($Z_{crit}$):**
Todo el error ($\alpha = 0.05$) se pone en el lado izquierdo de la campana.
* Buscamos en la tabla Z.
* **Z crítico = $-1.645$**

**3. Regla de Decisión:**
Rechazamos $H_0$ (y aceptamos que venden menos) **solo si** el $Z_{calculado}$ es menor que $-1.645$ (es decir, un número negativo como -2, -3, etc.).

**4. Conclusión:**
* Tenemos un $Z_{calculado} = 641.05$ (Positivo).
* Comparación: $641.05$ **NO** es menor que $-1.645$. De hecho, está totalmente en el lado opuesto.
* **Decisión:** **NO se rechaza la Hipótesis Nula.**
* **Interpretación:** No hay ninguna evidencia para decir que las tiendas del norte venden menos. De hecho, los datos sugieren fuertemente lo contrario, por lo que esta hipótesis se descarta por completo.

---

### Resumen Visual de los 3 Escenarios

| Tipo de Prueba | Hipótesis Alternativa ($H_1$) | Zona de Rechazo (Crítico) | ¿Dónde cayó nuestro Z (641.05)? | Conclusión |
| :--- | :--- | :--- | :--- | :--- |
| **Cola Derecha** (Original) | Norte > Sur | $Z > 1.645$ | Dentro de la zona de rechazo | **El Norte vende más.** |
| **Dos Colas** | Norte $\neq$ Sur | $Z < -1.96$ o $Z > 1.96$ | Dentro de la zona de rechazo | **Son diferentes.** |
| **Cola Izquierda** | Norte < Sur | $Z < -1.645$ | Fuera de la zona de rechazo | **Falso (No venden menos).** |

No me acuerdo de que ejercicio es, pero como ejemplo:

```json
{
	"confiabilidad" : 0.99,
	"cantidad1" : 30,
	"cantidad2" : 40,
	"desviacion1" : 14000,
	"desviacion2" : 10000,
	"promedio1" : 280000,
	"promedio2" : 270000,
	"modo": 5
}
```

genera

```json
{
	"z0": 3.327177587852354,
	"za": 2.3263478740408385,
	"veredicto": "Se rechaza la hipotesis nula."
}
```