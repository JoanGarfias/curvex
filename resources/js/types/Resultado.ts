export interface Resultado{
    count: number;
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
}