# Entrada
Tomando como ejemplo el ejercicio de clase, en la modalidad de texto. Se recibe como un JSON dentro del body, con el parametro "infinito" definiendo si la cantidad de datos es infinita o no (es decir, si se hace el paso 1 o 2 del ejercicio).:

##Caso 1: Infinito (Paso 1 del ejercicio)
```
{
	"values" : "6 7 9 8 5 4 7 8 7 6",
	"infinito" : true,
	"confiabilidad" : 95,
	"error" : 0.469,
	"cantdatos" : 1000
}
```

##Caso 2: Finito (Paso 2 del ejercicio)
```
{
	"cantdatoscorregido" : 50,
	"infinito" : false,
	"confiabilidad" : 95,
	"cantdatos" : 1000,
	"varianza" : 1.9955,
	"promedio" : 6.62
}
```


# Salida
##Caso 1: Infinito (Paso 1 del ejercicio)
```
{
	"count": 10,
	"mean": 6.7,
	"variance": 2.233333333333334,
	"alpha": 0.050000000000000044,
	"valor_critico": 2.2621571627982053,
	"h": 49.39181226812541,
	"hreal": 50
}
```

##Caso 2: Finito (Paso 2 del ejercicio)
```
{
	"variance": 1.9955,
	"alpha": 0.050000000000000044,
	"valor_critico": 2.0095752371292277,
	"variance2": 0.0379145,
	"desviacion2": 0.1947164605265821,
	"limite": 0.3912973773356701
}
```