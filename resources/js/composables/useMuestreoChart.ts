// Composable para manejo de gráfica con Chart.js
import { ref, nextTick } from 'vue';
import Chart from 'chart.js/auto';
import type { Chart as ChartType } from 'chart.js/auto';
import type { ResultData } from '@/types/muestreo';

export function useMuestreoChart() {
  const chartCanvas = ref<HTMLCanvasElement | null>(null);
  let chartInstance: ChartType | null = null;

  const renderChart = async (data: ResultData) => {
    await nextTick();
    
    if (!chartCanvas.value) return;

    // Destruir gráfica anterior si existe
    if (chartInstance) {
      chartInstance.destroy();
    }

    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) return;
    
    // Preparar datos
    const chartData = data.grafica.map(point => ({
      x: point.p,
      y: point.res,
      isAQT: point.AQT,
      isLTPD: point.LTPD
    }));

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        datasets: [{
          label: 'Probabilidad de Aceptación',
          data: chartData,
          borderColor: '#000',
          backgroundColor: 'rgba(0, 0, 0, 0.1)',
          borderWidth: 2,
          pointRadius: (context) => {
            const point = context.raw as { isAQT?: boolean; isLTPD?: boolean };
            return (point.isAQT || point.isLTPD) ? 6 : 0;
          },
          pointBackgroundColor: (context) => {
            const point = context.raw as { isAQT?: boolean; isLTPD?: boolean };
            if (point.isAQT) return '#fff';
            if (point.isLTPD) return '#000';
            return '#000';
          },
          pointBorderColor: (context) => {
            const point = context.raw as { isAQT?: boolean; isLTPD?: boolean };
            if (point.isAQT) return '#000';
            if (point.isLTPD) return '#fff';
            return '#000';
          },
          pointBorderWidth: 2,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                return `P(Aceptar): ${context.parsed.y.toFixed(4)}`;
              },
              title: (context) => {
                return `p = ${context[0].parsed.x.toFixed(4)}`;
              }
            }
          }
        },
        scales: {
          x: {
            type: 'linear',
            title: {
              display: true,
              text: 'Proporción de Defectos (p)'
            },
            min: 0,
            max: Math.max(...chartData.map((d: { x: number }) => d.x)) * 1.1
          },
          y: {
            title: {
              display: true,
              text: 'P(Aceptar)'
            },
            min: 0,
            max: 1
          }
        }
      }
    });
  };

  const destroyChart = () => {
    if (chartInstance) {
      chartInstance.destroy();
      chartInstance = null;
    }
  };

  return {
    chartCanvas,
    renderChart,
    destroyChart
  };
}
