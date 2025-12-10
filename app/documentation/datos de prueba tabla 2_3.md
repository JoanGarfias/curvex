Tabla 2.3

Datos para casos 0,1,2
Ahorita busco

Datos para 3,4,5
Sacados de este link para el modo con variable "boolEsVarianzaUnica" en true: Ejercicio de los perritos: https://www.vaia.com/en-us/explanations/math/statistics/comparing-two-means-hypothesis-testing/ 

{
	"confiabilidad" : 0.95,
	"cantidad1" : 4,
	"cantidad2" : 5,
	"boolEsVarianzaUnica": true,
	"promedio1" : 5.41,
	"promedio2" : 5.17,
	"varianza1": 0.038866667,
	"varianza2": 0.03015,
	"modo": 5
}

Ese booleano es necesario ponerlo si el usuario tiene 2 varianzas y las quiere convertir en una sola, sin usar la caja de texto para el parametro varianzap. 

Cuando se usa la caja de texto el booleano se pondra como falso, como en este ejemplo, mismos datos del ejercicio de los perritos, diferente disposicion:

{
	"confiabilidad" : 0.95,
	"cantidad1" : 4,
	"cantidad2" : 5,
	"boolEsVarianzaUnica": false,
	"promedio1" : 5.41,
	"promedio2" : 5.17,
	"varianzap": 0.18408072802054,
	"modo": 5
}

Resultado (ambos casos): 
{
	"t0": 1.9435542234494316,
	"ta": 1.8945786050900038,
	"veredicto": "Se rechaza la hipotesis nula."
}