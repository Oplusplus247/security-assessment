class DashboardManager {
    constructor() {
        this.charts = new Map();
        this.init();
    }
    
    init() {
        this.setupChartResizing();
        this.setupDataRefresh();
        this.setupKeyboardShortcuts();
    }
    
    createGaugeChart(canvasId, value, target = 5) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [value, target - value],
                    backgroundColor: [
                        this.getReadinessColor(value, target),
                        '#E5E7EB'
                    ],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
        
        this.charts.set(canvasId, chart);
        return chart;
    }
    
    getReadinessColor(value, target) {
        const percentage = (value / target) * 100;
        if (percentage >= 80) return '#3ec516';
        if (percentage >= 60) return '#F59E0B';
        return '#f34b26';
    }
    
    setupChartResizing() {
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.charts.forEach(chart => chart.resize());
            }, 150);
        });
    }
    
    setupDataRefresh() {
        // Auto-refresh data every 5 minutes
        setInterval(() => {
            this.refreshDashboardData();
        }, 300000);
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'r':
                        e.preventDefault();
                        this.refreshDashboardData();
                        break;
                    case 'd':
                        e.preventDefault();
                        window.location.href = '/dashboard';
                        break;
                }
            }
        });
    }
    
    async refreshDashboardData() {
        try {
            const response = await fetch('/api/dashboard/refresh', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.updateCharts(data);
                this.showNotification('Dashboard data refreshed', 'success');
            }
        } catch (error) {
            console.error('Failed to refresh dashboard data:', error);
            this.showNotification('Failed to refresh data', 'error');
        }
    }
    
    updateCharts(data) {
        // Update gauge charts
        if (data.readiness_level && this.charts.has('readinessGauge')) {
            const chart = this.charts.get('readinessGauge');
            chart.data.datasets[0].data = [data.readiness_level, 5 - data.readiness_level];
            chart.update();
        }
        
        // Update historical charts
        if (data.historical && this.charts.has('historicalChart')) {
            const chart = this.charts.get('historicalChart');
            chart.data.labels = data.historical.labels;
            chart.data.datasets[0].data = data.historical.readiness;
            chart.data.datasets[1].data = data.historical.target;
            chart.update();
        }
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialize dashboard manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});