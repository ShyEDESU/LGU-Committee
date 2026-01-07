// ==============================
// ANALYTICS MODULE
// ==============================

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function () {
    renderDocumentsOverTimeChart();
    renderDocumentsByStatusChart();
});

// Documents Over Time Chart (Line Chart)
function renderDocumentsOverTimeChart() {
    const ctx = document.getElementById('documentsOverTimeChart');
    if (!ctx) return;

    // Check if dark mode is active
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e5e5' : '#374151';
    const gridColor = isDark ? '#374151' : '#e5e7eb';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Documents',
                data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 40, 38, 45],
                borderColor: '#dc2626',
                backgroundColor: 'rgba(220, 38, 38, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: textColor
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        stepSize: 10
                    },
                    grid: {
                        color: gridColor
                    }
                },
                x: {
                    ticks: {
                        color: textColor
                    },
                    grid: {
                        color: gridColor
                    }
                }
            }
        }
    });
}

// Documents by Status Chart (Bar Chart)
function renderDocumentsByStatusChart() {
    const ctx = document.getElementById('documentsByStatusChart');
    if (!ctx) return;

    // Check if dark mode is active
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e5e5' : '#374151';
    const gridColor = isDark ? '#374151' : '#e5e7eb';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Approved', 'Pending', 'Draft'],
            datasets: [{
                label: 'Documents',
                data: [12, 8, 3],
                backgroundColor: ['#16a34a', '#f59e0b', '#6b7280']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        stepSize: 2
                    },
                    grid: {
                        color: gridColor
                    }
                },
                x: {
                    ticks: {
                        color: textColor
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Listen for theme changes and update charts
document.addEventListener('themeChanged', function () {
    // Destroy and recreate charts with new colors
    Chart.helpers.each(Chart.instances, function (instance) {
        instance.destroy();
    });

    renderDocumentsOverTimeChart();
    renderDocumentsByStatusChart();
});
