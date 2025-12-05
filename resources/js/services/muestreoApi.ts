// API Service para Muestreo de Aceptación
import type { FormData, ResultData, ModeType } from '@/types/muestreo';

export class MuestreoApiService {
  private static getEndpoint(mode: ModeType): string {
    return mode === 'aql-ltpd' ? '/calc-muestroaceptacion' : '/calc-muestroaceptacion2';
  }

  private static prepareData(mode: ModeType, formData: FormData) {
    if (mode === 'aql-ltpd') {
      return {
        AQT: formData.AQT,
        LTPD: formData.LTPD,
        '1-alpha': formData['1-alpha'],
        beta: formData.beta
      };
    } else {
      return {
        n: parseInt(formData.n),
        c: parseInt(formData.c),
        '1-alpha': formData['1-alpha'],
        beta: formData.beta
      };
    }
  }

  static async calculate(mode: ModeType, formData: FormData): Promise<ResultData> {
    const endpoint = this.getEndpoint(mode);
    const dataToSend = this.prepareData(mode, formData);

    const csrfToken = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken?.content || ''
      },
      body: JSON.stringify(dataToSend)
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Error en la petición');
    }

    return await response.json();
  }
}
