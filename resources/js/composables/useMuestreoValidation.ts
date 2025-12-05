// Composable para validación de formularios
import { ref } from 'vue';
import type { FormData, FormErrors, ModeType } from '@/types/muestreo';

export function useMuestreoValidation(mode: ModeType) {
  const errors = ref<FormErrors>({});

  const clearError = (field: string) => {
    if (errors.value[field]) {
      delete errors.value[field];
    }
  };

  const validate = (formData: FormData): boolean => {
    const newErrors: FormErrors = {};
    
    if (mode === 'aql-ltpd') {
      // Validar AQL, LTPD, 1-alpha, beta
      ['AQT', 'LTPD', '1-alpha', 'beta'].forEach(key => {
        const value = parseFloat(formData[key as keyof FormData]);
        if (!formData[key as keyof FormData]) {
          newErrors[key] = 'Campo requerido';
        } else if (isNaN(value) || value < 0 || value > 1) {
          newErrors[key] = 'Debe ser entre 0 y 1';
        }
      });

      if (!newErrors.AQT && !newErrors.LTPD) {
        if (parseFloat(formData.LTPD) < parseFloat(formData.AQT)) {
          newErrors.LTPD = 'LTPD debe ser mayor que AQL';
        }
      }
    } else {
      // Validar n, c, 1-alpha, beta
      const n = parseInt(formData.n);
      const c = parseInt(formData.c);
      
      if (!formData.n) {
        newErrors.n = 'Campo requerido';
      } else if (isNaN(n) || n < 2) {
        newErrors.n = 'Debe ser entero mayor o igual a 2';
      }

      if (formData.c !== '0' && !formData.c) {
        newErrors.c = 'Campo requerido';
      } else if (isNaN(c) || c < 0 || c > 7) {
        newErrors.c = 'Debe ser entero entre 0 y 7';
      }

      ['1-alpha', 'beta'].forEach(key => {
        const value = parseFloat(formData[key as keyof FormData]);
        if (!formData[key as keyof FormData]) {
          newErrors[key] = 'Campo requerido';
        } else if (isNaN(value) || value < 0 || value > 1) {
          newErrors[key] = 'Debe ser entre 0 y 1';
        }
      });
    }

    errors.value = newErrors;
    return Object.keys(newErrors).length === 0;
  };

  return {
    errors,
    clearError,
    validate
  };
}
