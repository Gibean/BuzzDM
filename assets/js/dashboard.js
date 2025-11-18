document.addEventListener('DOMContentLoaded', function () {
    initSalesChart();
    initPaymentStatusChart(); // Initialize the new pie chart
    loadStats(); 
    // Las siguientes funciones quedan comentadas o eliminadas porque ahora los datos
    // se cargan directamente desde PHP en el HTML.
    // loadActivityLog();
    // loadTopProducts();
    // loadLowStockProducts();
});

async function loadStats() {
    try {
        const response = await fetch('php/api_stats.php');
        if (!response.ok) {
            throw new Error(`Error de red: ${response.statusText}`);
        }
        const stats = await response.json();

        if (stats.error) {
            throw new Error(`Error en la API: ${stats.error}`);
        }

        animateCounter('totalProducts', stats.totalProducts);
        animateCounter('totalOrders', stats.totalOrders);
        animateCounter('pendingShipments', stats.pendingShipments);
        animateCounter('monthlyRevenue', stats.monthlyRevenue, true);

        // Actualizar la fecha de "ÚLTIMA ACTUALIZACIÓN"
        const lastUpdateElement = document.getElementById('lastUpdate');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = new Date().toLocaleString('es-ES');
        }

    } catch (error) {
        console.error("No se pudieron cargar las estadísticas:", error);
        // Opcional: Mostrar un mensaje de error en la UI
    }
}

async function initSalesChart() {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    try {
        const response = await fetch('php/api_sales_chart.php');
        if (!response.ok) {
            throw new Error(`Error de red: ${response.statusText}`);
        }
        const chartData = await response.json();

        if (chartData.error) {
            throw new Error(`Error en la API: ${chartData.error}`);
        }

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'PPpp',
                            displayFormats: {
                                day: 'MMM d'
                            }
                        },
                        ticks: {
                            color: '#8b949e',
                            font: {
                                family: "Roboto Mono",
                            },
                        },
                        grid: {
                            color: "#343a40",
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#8b949e',
                            font: {
                                family: "Roboto Mono",
                            },
                        },
                        grid: {
                            color: "#343a40",
                        },
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#8b949e',
                            font: {
                                family: "Roboto Mono",
                            },
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    },
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });

    } catch (error) {
        console.error("No se pudo cargar el gráfico de ventas:", error);
        const canvas = ctx.getContext('2d');
        canvas.font = '16px Arial';
        canvas.fillStyle = '#8b949e';
        canvas.textAlign = 'center';
        canvas.fillText('No se pudieron cargar los datos del gráfico.', ctx.width / 2, ctx.height / 2);
    }
}

async function initPaymentStatusChart() {
    const ctx = document.getElementById('paymentStatusChart');
    if (!ctx) return;

    try {
        const response = await fetch('php/api_sales_by_status_chart.php');
        if (!response.ok) {
            throw new Error(`Error de red: ${response.statusText}`);
        }
        const chartData = await response.json();

        if (chartData.error) {
            throw new Error(`Error en la API: ${chartData.error}`);
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets.map(dataset => ({
                    ...dataset,
                    backgroundColor: ['#E4A01E', '#58666B', '#808080'],
                    borderWidth: 0
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#8b949e',
                            font: {
                                family: "Roboto Mono",
                            },
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error("No se pudo cargar el gráfico de estado de pago:", error);
        const canvas = ctx.getContext('2d');
        canvas.font = '16px Arial';
        canvas.fillStyle = '#8b949e';
        canvas.textAlign = 'center';
        canvas.fillText('No se pudieron cargar los datos del gráfico.', ctx.width / 2, ctx.height / 2);
    }
}

// Las funciones de animación y formato se pueden mantener si se usan en otras partes,
// pero por ahora no son llamadas ya que loadStats() está desactivado.

function animateCounter(elementId, targetValue, isCurrency = false) {
    const element = document.getElementById(elementId);
    if (!element) return;

    let currentValue = 0;
    const increment = targetValue / 100;
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(timer);
        }

        if (isCurrency) {
            element.textContent = formatCurrency(Math.floor(currentValue));
        } else {
            element.textContent = Math.floor(currentValue).toLocaleString('es-ES');
        }
    }, 20);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(value);
}