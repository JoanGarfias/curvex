// Composable para el tour interactivo con driver.js
import { onMounted } from 'vue';
import { driver } from 'driver.js';
import type { ModeType } from '@/types/muestreo';

export function useMuestreoTour(mode: ModeType) {
  const startTour = () => {
    const driverObj = driver({
      showProgress: true,
      showButtons: ['next', 'previous', 'close'],
      steps: [
        {
          element: '#mode-selector',
          popover: {
            title: '🎯 Selector de Modo',
            description: 'Elige entre dos modos de cálculo: <strong>AQL/LTPD</strong> para calcular n y c, o <strong>n/c</strong> para calcular AQL y LTPD.',
            side: 'bottom',
            align: 'center'
          }
        },
        {
          element: '#form-container',
          popover: {
            title: '📝 Formulario de Parámetros',
            description: 'Ingresa los valores necesarios según el modo seleccionado. Todos los campos son obligatorios y deben estar en el rango válido.',
            side: 'right',
            align: 'start'
          }
        },
        {
          element: mode === 'aql-ltpd' ? '#aql-input' : '#n-input',
          popover: {
            title: mode === 'aql-ltpd' ? '📊 AQL (Nivel de Calidad Aceptable)' : '🔢 Tamaño de Muestra (n)',
            description: mode === 'aql-ltpd' 
              ? 'Porcentaje de defectos que consideras <strong>aceptable</strong> en tu proceso. Valor entre 0 y 1 (ej: 0.02 = 2%).'
              : 'Número de unidades que deseas inspeccionar de cada lote. Debe ser al menos 2.',
            side: 'left',
            align: 'start'
          }
        },
        {
          element: '#alpha-input',
          popover: {
            title: '✨ Confianza del Productor (1-α)',
            description: 'Probabilidad de <strong>aceptar</strong> un lote bueno. Valor típico: <strong>0.95</strong> (95% de confianza).',
            side: 'left',
            align: 'start'
          }
        },
        {
          element: '#beta-input',
          popover: {
            title: '⚠️ Riesgo del Consumidor (β)',
            description: 'Probabilidad de <strong>aceptar</strong> un lote malo. Valor típico: <strong>0.10</strong> (10% de riesgo).',
            side: 'left',
            align: 'start'
          }
        },
        {
          element: '#submit-button',
          popover: {
            title: '🚀 Calcular Resultados',
            description: 'Haz clic aquí para ejecutar el cálculo. Verás skeletons mientras procesa y luego los resultados completos.',
            side: 'top',
            align: 'center'
          }
        },
        {
          element: '#guide-info',
          popover: {
            title: '💡 Guía Rápida',
            description: 'Aquí siempre encontrarás recordatorios sobre el significado de cada parámetro según el modo activo.',
            side: 'top',
            align: 'start'
          }
        }
      ],
      nextBtnText: 'Siguiente →',
      prevBtnText: '← Anterior',
      doneBtnText: '¡Entendido! ✓',
      progressText: '{{current}} de {{total}}',
      onDestroyStarted: () => {
        localStorage.setItem('muestreo-tour-completed', 'true');
        driverObj.destroy();
      }
    });

    driverObj.drive();
  };

  const initTour = () => {
    onMounted(() => {
      const tourCompleted = localStorage.getItem('muestreo-tour-completed');
      if (!tourCompleted) {
        setTimeout(() => {
          startTour();
        }, 800);
      }
    });
  };

  const changeMode = (newMode: ModeType) => {
    mode = newMode;
    console.log(`Cambiando modo de validación a: ${mode}`);
  }

  return {
    startTour,
    initTour,
    changeMode
  };
}
