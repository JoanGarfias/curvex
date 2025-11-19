<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticsCorreccionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use MathPHP\Probability\Distribution\Continuous\StudentT;
use Exception; // Para el try-catch general

use App\Services\StatisticService;
use App\Services\FrequencyService;

class CorreccionStatisticsController extends Controller
{
    /**
     * Recibe una lista de números y calcula sus estadísticas.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function corregir(StatisticsCorreccionRequest $request): JsonResponse
    {
        // 1. VALIDACIÓN (Equivalente a jTextArea1.getText().isBlank() y NumberFormatException)
        $data = $request->validated();
        $service = new StatisticService();
        $frequencyService = new FrequencyService();

        // 2. OBTENER NÚMEROS (Equivalente a tu bloque 'try' inicial)

        if (isset($data['file'])) {
            $numbers = [];
            $path = $data['file']->getRealPath();
            $content = file_get_contents($path);
            
            // Eliminar BOM (Byte Order Mark) si existe
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            
            // Dividir por saltos de línea y procesar cada línea
            $lines = preg_split('/\r\n|\r|\n/', $content);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Intentar separar por comas, espacios o tabulaciones
                $values = preg_split('/[,\s\t]+/', $line);
                
                foreach ($values as $value) {
                    $trimmed = trim($value);
                    if ($trimmed !== '' && is_numeric($trimmed)) {
                        $numbers[] = floatval($trimmed);
                    }
                }
            }
        } else if(isset($data['values'])){
            // Parse space-separated numbers
            $numbers = preg_split('/\s+/', trim($data['values']));
            $numbers = array_filter(array_map('floatval', $numbers));
        } else{
            if(!isset($data['infinito']) || $data['infinito'] == true){
                return response()->json(['message' => 'Ocurrió un error.', 'error' => "Proporcione valores de la muestra, o cambie el metodo de correccion de varianza para elegir el que usa valores finitos."], 422);
            }
        }
        
        try {
            Log::info('Varianza muestral');
            $modo = 0;
            if(isset($data['infinito']))
                $modo = $data['infinito'] ? 1 : 0;

            $cantdatos = 0;
            if(isset($data['cantdatos']))
                $cantdatos = $data['cantdatos'] ? $data['cantdatos'] : 0;

            $error = 0;
            if(isset($data['error']))
                $error = $data['error'] ? $data['error'] : 0;

            $confiabilidad = 0;
            if(isset($data['confiabilidad']))
                $confiabilidad = $data['confiabilidad'] ? $data['confiabilidad'] : 0;
            
            if($confiabilidad < 0 || $confiabilidad > 100){
                return response()->json(['message' => 'Ocurrió un error.', 'error' => "La confiabilidad no puede ser menor a 0 o mayor a 100. Ingresa otro valor para tu confiabilidad."], 500);
            }

            $cantdatoscorregido = 0;
            if(isset($data['cantdatoscorregido']))
                $cantdatoscorregido = $data['cantdatoscorregido'] ? $data['cantdatoscorregido'] : 0;

            $varianzanueva = 0;
            if(isset($data['varianza']))
                $varianzanueva = $data['varianza'] ? $data['varianza'] : 0;

            $promedionuevo = 0;
            if(isset($data['promedio']))
                $promedionuevo = $data['promedio'] ? $data['promedio'] : 0;


            if($modo == 0){ 
            //sin infinito (paso 2 ejercicio)

                $alpha = 1 - ($confiabilidad/100);
                $distribution = new StudentT($cantdatoscorregido-1);
                $valorCritico = $distribution->inverse2Tails(($alpha));

                $varianza2 = ((($cantdatos - $cantdatoscorregido)/$cantdatos)*(($varianzanueva)/$cantdatoscorregido));
                $desviacion2 = sqrt($varianza2);

                $limite = $desviacion2 * $valorCritico;

                $resultados = [
                    'variance' => $varianzanueva, // Varianza poblacional
                    'alpha' => $alpha, // Nueva: tabla de frecuencias
                    'valor_critico' => $valorCritico, // Nueva: tabla de frecuencias
                    'variance2' => $varianza2, // Varianza poblacional
                    'desviacion2' => $desviacion2,
                    'limite' => $limite,
                ];
            }else if($modo == 1){ 
            //con infinito (paso 1 ejercicio)
                
                $listaNumeros = $numbers;

                // int n = obtenerCantNumeros(...)
                $n = count($listaNumeros);

                if($n >= $cantdatos){
                    return response()->json(['message' => 'Ocurrió un error.', 'error' => "La cantidad de la muestra de datos es mayor el total ingresado de datos. Ingresa otro total o coloca menos datos en la muestra."], 500);
                }

                // double promedio = promedio(mat, n)
                $promedio = $service->promedio($listaNumeros, $n);

                // double varianza = obtenerVarianza(mat,n,promedio)
                $varianza = $service->obtenerVarianza($listaNumeros, $n, $promedio, 1);

                $alpha = 1 - ($confiabilidad/100);
                $distribution = new StudentT($n-1);
                $valorCritico = $distribution->inverse2Tails(($alpha));

                $h = (($cantdatos*($valorCritico**2))*$varianza) / ((1000*($error**2))+(($valorCritico**2)*$varianza));

                $varianza2 = (($cantdatos - $h)/$cantdatos)*(($varianza**2)/$h);

                $resultados = [
                    'count' => $n,
                    'mean' => $promedio,
                    'variance' => $varianza, // Varianza poblacional
                    'alpha' => $alpha, // Nueva: tabla de frecuencias
                    'valor_critico' => $valorCritico, // Nueva: tabla de frecuencias
                    'h' => $h, // Nueva: tabla de frecuencias
                    'hreal' => ceil($h),
                ];
            }
            

            // 5. DEVOLVER RESPUESTA
            // (Equivalente a JOptionPane.showMessageDialog(rootPane, "Operación exitosa!"))
            return response()->json($resultados, 200);

        } catch (Exception $e) {
            // 6. MANEJO DE ERRORES GENERALES
            // (Equivalente a tu 'catch(Exception e)')
            return response()->json(['message' => 'Ocurrió un error inesperado.', 'error' => $e->getMessage()], 500);
        }
    }
}

?>