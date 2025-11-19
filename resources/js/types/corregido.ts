export interface Resultado{
    count: number;
    mean: number;
    variance: number;
    alpha: number;
    valor_critico: number;
    h: number;
    hreal: number;
}
/*
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
}*/