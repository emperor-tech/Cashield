// Admin Dashboard JavaScript

// Dark Mode
document.addEventListener('DOMContentLoaded', () => {
    // Initialize dark mode from localStorage
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        document.documentElement.classList.add('dark');
    }

    // Handle system dark mode changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        const newDarkMode = e.matches;
        localStorage.setItem('darkMode', newDarkMode);
        document.documentElement.classList.toggle('dark', newDarkMode);
    });
});

// Chart Configurations
const chartColors = {
    blue: '#3b82f6',
    green: '#10b981',
    yellow: '#f59e0b',
    red: '#ef4444',
    purple: '#8b5cf6',
    gray: '#6b7280'
};

const chartOptions = {
    line: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#1f2937',
                bodyColor: '#1f2937',
                borderColor: '#e5e7eb',
                borderWidth: 1
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    display: true,
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    },
    doughnut: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 20,
                    boxWidth: 12,
                    font: {
                        size: 13
                    }
                }
            }
        },
        cutout: '75%'
    }
};

// Initialize Charts
function initializeCharts() {
    // Reports Trend Chart
    const trendCtx = document.getElementById('reportsTrendChart')?.getContext('2d');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: window.trendLabels || [],
                datasets: [{
                    label: 'Reports',
                    data: window.trendData || [],
                    borderColor: chartColors.blue,
                    backgroundColor: `${chartColors.blue}20`,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: chartOptions.line
        });
    }

    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryChart')?.getContext('2d');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: window.categoryLabels || [],
                datasets: [{
                    data: window.categoryData || [],
                    backgroundColor: Object.values(chartColors)
                }]
            },
            options: chartOptions.doughnut
        });
    }
}

// Handle Report Trend Range Change
function updateReportTrend(range) {
    fetch(`/admin/api/reports/trend/${range}`)
        .then(response => response.json())
        .then(data => {
            const chart = Chart.getChart('reportsTrendChart');
            if (chart) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.data;
                chart.update();
            }
        })
        .catch(error => console.error('Error updating report trend:', error));
}

// Handle Category Refresh
function refreshCategories() {
    fetch('/admin/api/reports/categories')
        .then(response => response.json())
        .then(data => {
            const chart = Chart.getChart('categoryChart');
            if (chart) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.data;
                chart.update();
            }
        })
        .catch(error => console.error('Error refreshing categories:', error));
}

// Notifications
function initializeNotifications() {
    const notificationCount = document.querySelector('[x-data]')?.__x.$data.notificationCount;
    
    if (typeof notificationCount !== 'undefined') {
        // Fetch notifications periodically
        setInterval(() => {
            fetch('/admin/api/notifications/unread')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('[x-data]').__x.$data.notificationCount = data.count;
                    document.querySelector('[x-data]').__x.$data.notifications = data.notifications;
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }, 30000); // Every 30 seconds
    }
}

// Search Functionality
function initializeSearch() {
    const searchInput = document.querySelector('.admin-search-input');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const query = e.target.value;
                if (query.length >= 2) {
                    fetch(`/admin/api/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            // Handle search results
                            console.log('Search results:', data);
                        })
                        .catch(error => console.error('Error performing search:', error));
                }
            }, 300);
        });
    }
}

// Initialize all features when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initializeCharts();
    initializeNotifications();
    initializeSearch();

    // Handle report trend range change
    const trendRange = document.getElementById('reportTrendRange');
    if (trendRange) {
        trendRange.addEventListener('change', (e) => updateReportTrend(e.target.value));
    }

    // Handle category refresh
    const refreshButton = document.getElementById('refreshCategories');
    if (refreshButton) {
        refreshButton.addEventListener('click', refreshCategories);
    }
});

// Export functions for use in other files
window.adminDashboard = {
    updateReportTrend,
    refreshCategories
};
