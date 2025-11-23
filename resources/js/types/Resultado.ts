export interface Resultado{
    count: number;
    sum?: number;
    mean: number;
    min: number;
    max: number;
    range: number;
    variance: number;
    standard_deviation: number;
    kurtosis: number;
    cuartiles: Array<Record<number, number>>;
    deciles: Array<Record<number, number>>;
    percentiles: Array<Record<number, number>>;
    data: number[];
    frequency_table?: FrequencyTable;
    // Resultados opcionales de la prueba Chi-cuadrado generados en el backend
    chi_results: {
        chicua: number;
        chi_inverso?: number;
        grados_libertad: number;
    };
}

export interface FrequencyTable {
    info_intervalos: {
        numero_intervalos: number;
        ancho_intervalo: number;
        mensaje?: string;
    };
    tabla_frecuencias: FrequencyRow[];
}

export interface FrequencyRow {
    clase: string;
    limite_inferior: number;
    limite_superior: number;
    marca_de_clase: number;
    frecuencia_absoluta: number;
    frecuencia_abs_acumulada: number;
    frecuencia_relativa_pct: number;
    frecuencia_rel_acumulada_pct: number;
}