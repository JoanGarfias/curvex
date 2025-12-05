<?php

namespace App\Services;

use App\Services\UtilsService as Utils;

class MuestreoAceptacionService {






    /**
     * Calcula la distribución binomial acumulativa.
     * Equivalente a BINOM.DIST(k, n, p, TRUE) en Excel.
     * 
     * @param int $n Número de ensayos
     * @param int $k Número de éxitos (calcula P(X ≤ k))
     * @param float $p Probabilidad de éxito en cada ensayo
     * @return float Probabilidad acumulada
     */
    public function binomDistAcum(int $n, int $k, float $p) : float{
        $binomAcum = 0.0;
        
        // Suma de probabilidades desde i=0 hasta i=k
        for($i = 0; $i <= $k; $i++){
            $coefBinomial = $this->coeficienteBinomial($n, $i);
            $probabilidad = $coefBinomial * pow($p, $i) * pow(1.0 - $p, $n - $i);
            $binomAcum += $probabilidad;
        }
        
        return $binomAcum;
    }

    /**
     * Calcula la distribución binomial puntual.
     * Equivalente a BINOM.DIST(k, n, p, FALSE) en Excel.
     * 
     * @param int $n Número de ensayos
     * @param int $k Número de éxitos
     * @param float $p Probabilidad de éxito
     * @return float Probabilidad puntual P(X = k)
     */
    public function binomDist(int $n, int $k, float $p) : float{
        $coefBinomial = $this->coeficienteBinomial($n, $k);
        return $coefBinomial * pow($p, $k) * pow(1.0 - $p, $n - $k);
    }

    /**
     * Calcula el coeficiente binomial C(n, k) = n! / (k! * (n-k)!)
     * Optimizado para evitar desbordamiento con factoriales grandes.
     * 
     * @param int $n Total de elementos
     * @param int $k Elementos a seleccionar
     * @return float Coeficiente binomial
     */
    private function coeficienteBinomial(int $n, int $k) : float{
        // Optimización: C(n,k) = C(n, n-k), usar el menor
        if ($k > $n - $k) {
            $k = $n - $k;
        }
        
        // Casos base
        if ($k == 0 || $k == $n) {
            return 1.0;
        }
        
        // Cálculo iterativo para evitar desbordamiento
        // C(n,k) = (n * (n-1) * ... * (n-k+1)) / (k * (k-1) * ... * 1)
        $resultado = 1.0;
        for ($i = 0; $i < $k; $i++) {
            $resultado *= ($n - $i);
            $resultado /= ($i + 1);
        }
        
        return $resultado;
    }

    /**
     * Calcula el plan de muestreo óptimo (n, c) dados AQL, LTPD, alfa y beta.
     * Busca la combinación de n y c que minimice la distancia a los puntos ideales.
     * 
     * @param float $AQT Nivel de Calidad Aceptable
     * @param float $LTPD Tolerancia del Lote
     * @param float $alfa Riesgo del productor (1-alpha es la confianza)
     * @param float $beta Riesgo del consumidor
     * @return array Array con n, c, distancia, AQT, LTPD, 1-alpha, beta
     */
    public function calcularPlanOptimo(float $AQT, float $LTPD, float $alfa, float $beta): array {
        $distancias = array();
        $distancias["AQT"] = $AQT;
        $distancias["LTPD"] = $LTPD;
        $distancias["1-alpha"] = $alfa;
        $distancias["beta"] = $beta;

        $distanciamenor = -1;
        $n = 10;
        
        while($n < 500){
            $menordist = -1;
            $menorc = -1;
            
            foreach([1,2,3,5,7] as $k){
                $probabilidadaqt = $this->binomDistAcum($n, $k, $AQT);
                $probabilidadltpd = $this->binomDistAcum($n, $k, $LTPD);
                $distancia = sqrt((($alfa - $probabilidadaqt) ** 2) + ($beta - $probabilidadltpd)**2);

                if(($menordist == -1) || ($menordist > $distancia)){
                    $menordist = $distancia;
                    $menorc = $k;
                }
            }

            if(($distanciamenor == -1) || ($distanciamenor > $menordist)){
                $distancias["n"] = $n;
                $distancias["c"] = $menorc;
                $distancias["distancia"] = $menordist;
                $distanciamenor = $menordist;
            }
            
            $n+=1;
        }
        
        return $distancias;
    }

    /**
     * Calcula AQL y LTPD dados n, c, alfa y beta.
     * Busca los valores de p que minimizan la distancia a alfa y beta.
     * 
     * @param int $n Tamaño de muestra
     * @param int $c Criterio de aceptación
     * @param float $alfa Riesgo del productor
     * @param float $beta Riesgo del consumidor
     * @return array Array con n, c, AQL, LTPD, distancia, 1-alpha, beta
     */
    public function calcularAQLyLTPD(int $n, int $c, float $alfa, float $beta): array {
        $distancias = array();
        $distancias["n"] = $n;
        $distancias["c"] = $c;
        $distancias["1-alpha"] = $alfa;
        $distancias["beta"] = $beta;

        $menoraql = -1;
        $menorltpd = -1;
        $menoraqldist = -1;
        $menorltpddist = -1;
        $p = 0.01;
        
        // Obtención de AQL y LTPD
        while($p < 1){
            $probabilidad = $this->binomDistAcum($n, $c, $p);

            if(($menoraql == -1) || ($menoraqldist > ($alfa - $probabilidad)**2)){
                $menoraql = round($p,8);
                $menoraqldist = ($alfa - $probabilidad)**2;
            }
            else if(($menorltpd == -1) || ($menorltpddist > ($beta - $probabilidad)**2)){
                $menorltpd = round($p,8);
                $menorltpddist = ($beta - $probabilidad)**2;
            }
            
            $p+=0.01;
        }

        $AQT = $menoraql;
        $LTPD = $menorltpd;

        $distancias["AQL"] = $menoraql;
        $distancias["LTPD"] = $menorltpd;

        $probabilidadaqt = $this->binomDistAcum($n, $c, $AQT);
        $probabilidadltpd = $this->binomDistAcum($n, $c, $LTPD);
        $distancia = sqrt((($alfa - $probabilidadaqt) ** 2) + ($beta - $probabilidadltpd)**2);
        $distancias["distancia"] = $distancia;
        
        return $distancias;
    }

    /**
     * Genera los datos para la gráfica de la curva característica de operación.
     * 
     * @param int $n Tamaño de muestra
     * @param int $c Criterio de aceptación
     * @param float $AQT Nivel de Calidad Aceptable
     * @param float $LTPD Tolerancia del Lote
     * @return array Array de puntos con p, res, AQT y LTPD flags
     */
    public function generarDatosGrafica(int $n, int $c, float $AQT, float $LTPD): array {
        $datos = array();
        
        if($LTPD <= 0.1){
            $i = 0.01;
            while($i < 0.10){
                $punto = array();
                $punto["p"] = round($i, 4);
                $punto["LTPD"] = false;
                $punto["AQT"] = false;
                $punto["res"] = $this->binomDistAcum($n, $c, round($i, 4));
                $datos[] = $punto;
                $i+=0.01;
            }

            $punto = array();
            $punto["p"] = $LTPD;
            $punto["LTPD"] = true;
            $punto["AQT"] = false;
            $punto["res"] = $this->binomDistAcum($n, $c, $LTPD);
            $datos[] = $punto;

            $punto = array();
            $punto["p"] = $AQT;
            $punto["LTPD"] = false;
            $punto["AQT"] = true;
            $punto["res"] = $this->binomDistAcum($n, $c, $AQT);
            $datos[] = $punto;

        } else {
            $constante = round(($LTPD / 10), 8);
            $i = round($constante, 4);
            
            while($i < $LTPD){
                $punto = array();
                $punto["p"] = round($i, 4);
                $punto["LTPD"] = false;
                $punto["AQT"] = false;
                $punto["res"] = $this->binomDistAcum($n, $c, round($i, 4));
                $datos[] = $punto;
                $i+=round($constante, 4);
            }

            $punto = array();
            $punto["p"] = $LTPD;
            $punto["LTPD"] = true;
            $punto["AQT"] = false;
            $punto["res"] = $this->binomDistAcum($n, $c, $LTPD);
            $datos[] = $punto;

            $punto = array();
            $punto["p"] = $AQT;
            $punto["LTPD"] = false;
            $punto["AQT"] = true;
            $punto["res"] = $this->binomDistAcum($n, $c, $AQT);
            $datos[] = $punto;
        }
        
        return $datos;
    }

}