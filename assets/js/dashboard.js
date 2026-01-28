document.addEventListener('DOMContentLoaded', function () {
    initSalesChart(30); // Default to 30 days
    setupPeriodTabs();
    initPaymentStatusChart(); // Initialize the new pie chart
    loadStats();
    // Las siguientes funciones quedan comentadas o eliminadas porque ahora los datos
    // se cargan directamente desde PHP en el HTML.
    // loadActivityLog();
    // loadTopProducts();
    // loadLowStockProducts();
});

// Store chart instance globally for updates
let salesChartInstance = null;

function setupPeriodTabs() {
    const tabs = document.querySelectorAll('.period-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active state
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Reload chart with new period
            const period = parseInt(this.dataset.period);
            initSalesChart(period);
        });
    });
}

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

        // Nuevos contadores tácticos
        animateCounter('tcLowStock', stats.lowStockCount);
        animateCounter('tcCustomers', stats.totalCustomers);
        animateCounter('tcDailyOrders', stats.dailyOrders);


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

async function initSalesChart(days = 30) {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    // Destroy existing chart if exists
    if (salesChartInstance) {
        salesChartInstance.destroy();
        salesChartInstance = null;
    }

    // Remove any existing no-data message
    const existingMessage = document.getElementById('salesChartNoData');
    if (existingMessage) existingMessage.remove();

    try {
        const response = await fetch(`php/api_sales_chart.php?days=${days}`);
        if (!response.ok) {
            throw new Error(`Error de red: ${response.statusText}`);
        }
        const chartData = await response.json();

        if (chartData.error) {
            throw new Error(`Error en la API: ${chartData.error}`);
        }

        // Check if there's no data
        const hasData = chartData.labels && chartData.labels.length > 0;

        if (!hasData) {
            // Show no-data message
            const periodText = getPeriodText(days);
            showNoDataMessage(ctx, periodText);
            return;
        }

        // Determine time unit based on period
        let timeUnit = 'day';
        let displayFormat = 'MMM d';
        if (days <= 7) {
            timeUnit = 'day';
            displayFormat = 'EEE d';
        } else if (days > 60) {
            timeUnit = 'week';
            displayFormat = 'MMM d';
        }

        salesChartInstance = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: timeUnit,
                            tooltipFormat: 'PPpp',
                            displayFormats: {
                                day: displayFormat,
                                week: 'MMM d'
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
        showNoDataMessage(ctx, 'Error al cargar datos');
    }
}

function getPeriodText(days) {
    switch(days) {
        case 7: return 'los últimos 7 días';
        case 30: return 'el último mes';
        case 180: return 'los últimos 6 meses';
        default: return `los últimos ${days} días`;
    }
}

function showNoDataMessage(canvas, periodText) {
    const container = canvas.parentElement;

    // Create styled message overlay
    const messageDiv = document.createElement('div');
    messageDiv.id = 'salesChartNoData';
    messageDiv.style.cssText = `
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: #6c757d;
        font-family: 'Roboto Mono', monospace;
        pointer-events: none;
    `;
    messageDiv.innerHTML = `
        <i class="fas fa-chart-line" style="font-size: 2rem; opacity: 0.3; margin-bottom: 10px; display: block;"></i>
        <span style="font-size: 0.85rem;">Sin datos de ventas en ${periodText}</span>
    `;

    container.appendChild(messageDiv);
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
    return 'Bs. ' + new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2 }).format(value);
}

async function syncBcv() {
    const syncBtn = document.querySelector('[onclick="syncBcv()"]');
    const icon = syncBtn ? syncBtn.querySelector('i') : null;

    if (icon) icon.classList.add('fa-spin');

    try {
        const response = await fetch('php/tasa_bcv_update.php');
        const data = await response.json();

        if (data.success) {
            const labelMap = { 'bcv': 'BCV', 'paralelo': 'Paralelo', 'bcv_euro': 'Euro', 'binance_usdt': 'USDT' };
            const cleanLabels = Object.keys(data.rates).map(key => labelMap[key] || key);
            showToast('Tasas actualizadas: ' + cleanLabels.join(', '), 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    } catch (error) {
        console.error("Error sincronizando tasas:", error);
        showToast('Error al sincronizar: ' + error.message, 'error');
    } finally {
        if (icon) icon.classList.remove('fa-spin');
    }
}
