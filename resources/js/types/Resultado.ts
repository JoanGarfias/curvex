export interface Resultado{
    count: number;
    sum?: number;
    mean: number;
    min: number;
    max: number;
    range: number;
    variance: number;
    standardDeviation: number;
    kurtosis: number;
    quartiles: number[];
    deciles: number[];
    percentiles: number[];
    data: number[];
    frequency_table?: FrequencyTable;
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