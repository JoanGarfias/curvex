// Types para Muestreo de Aceptación

export interface FormData {
  AQT: string;
  LTPD: string;
  '1-alpha': string;
  beta: string;
  n: string;
  c: string;
}

export interface FormErrors {
  [key: string]: string;
}

export interface GraficaPoint {
  p: number;
  res: number;
  AQT?: boolean;
  LTPD?: boolean;
}

export interface ResultData {
  distancia_menor: {
    n: number;
    c: number;
    distancia: number;
    AQT?: number;
    LTPD?: number;
    AQL?: number;
    '1-alpha': number;
    beta: number;
  };
  grafica: GraficaPoint[];
}

export type ModeType = 'aql-ltpd' | 'n-c';
