/**
 * Admin Analytics Dashboard
 * Fetches data from API and renders charts using Chart.js
 */

// Global chart instances
let salesTrendChart, orderStatusChart, topProductsChart, categoryRevenueChart, categoryRatingsChart;

// Show/Hide loading overlay
function showLoading() {
  const overlay = document.getElementById('loadingOverlay');
  if (overlay) overlay.classList.remove('hidden');
}

function hideLoading() {
  const overlay = document.getElementById('loadingOverlay');
  if (overlay) {
    setTimeout(() => overlay.classList.add('hidden'), 300);
  }
}

// Fetch analytics data
async function fetchAnalyticsData(days = 30) {
  showLoading();

  // Set timeout to prevent infinite loading
  const timeout = setTimeout(() => {
    hideLoading();
    showError('Request timeout. Please check your internet connection and try again.');
  }, 10000); // 10 second timeout

  try {
    // Use absolute path to API
    const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
    const apiUrl = baseUrl + '/api/analytics-data.php';

    console.log('Base URL:', baseUrl);
    console.log('API URL:', apiUrl);

    const response = await fetch(`${apiUrl}?days=${days}`);
    clearTimeout(timeout);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log('Analytics data received:', data);

    if (data.success) {
      updateSummaryCards(data.summary, data.user_stats);
      renderSalesTrendChart(data.daily_sales);
      renderOrderStatusChart(data.orders_by_status);
      renderTopProductsChart(data.top_products);
      renderCategoryRevenueChart(data.category_revenue);
      renderCategoryRatingsChart(data.category_ratings);
      updateRecentOrders(data.recent_orders);
      updateLowStockAlerts(data.low_stock_alerts);
      hideLoading();
    } else {
      hideLoading();
      console.error('Error fetching analytics:', data.error);
      showError('Failed to load analytics data: ' + (data.error || 'Unknown error'));
    }
  } catch (error) {
    hideLoading();
    console.error('Fetch error:', error);
    showError('Failed to connect to analytics API. Please check console for details.');
  }
}

// Show error message
function showError(message) {
  const errorDiv = document.createElement('div');
  errorDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
  errorDiv.style.zIndex = '10000';
  errorDiv.innerHTML = `
    <strong>Error:</strong> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.body.appendChild(errorDiv);

  setTimeout(() => errorDiv.remove(), 5000);
}

// Update summary cards
function updateSummaryCards(summary, userStats) {
  document.getElementById('totalRevenue').textContent = `₹${formatNumber(summary.total_revenue)}`;
  document.getElementById('totalOrders').textContent = formatNumber(summary.total_orders);
  document.getElementById('avgOrderValue').textContent = `₹${formatNumber(summary.average_order_value)}`;
  document.getElementById('newCustomers').textContent = formatNumber(userStats.new_customers);
  document.getElementById('returningCustomers').textContent = `${formatNumber(userStats.returning_customers)} returning`;

  const growthEl = document.getElementById('revenueGrowth');
  const growth = summary.revenue_growth;
  growthEl.textContent = `${growth >= 0 ? '+' : ''}${growth.toFixed(1)}%`;
  growthEl.className = growth >= 0 ? 'text-success' : 'text-danger';
}

// Render Sales Trend Chart
function renderSalesTrendChart(dailySales) {
  const ctx = document.getElementById('salesTrendChart').getContext('2d');

  if (salesTrendChart) salesTrendChart.destroy();

  salesTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: dailySales.map(d => formatDate(d.date)),
      datasets: [{
        label: 'Revenue (₹)',
        data: dailySales.map(d => parseFloat(d.daily_revenue)),
        borderColor: '#667eea',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
        fill: true,
        tension: 0.4
      }, {
        label: 'Orders',
        data: dailySales.map(d => parseInt(d.order_count)),
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        fill: true,
        tension: 0.4,
        yAxisID: 'y1'
      }]
    },
    options: {
      responsive: true,
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              let label = context.dataset.label || '';
              if (label) label += ': ';
              if (context.dataset.label === 'Revenue (₹)') {
                label += '₹' + formatNumber(context.parsed.y);
              } else {
                label += context.parsed.y;
              }
              return label;
            }
          }
        }
      },
      scales: {
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          title: {
            display: true,
            text: 'Revenue (₹)'
          }
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          title: {
            display: true,
            text: 'Orders'
          },
          grid: {
            drawOnChartArea: false
          }
        }
      }
    }
  });
}

// Render Order Status Chart
function renderOrderStatusChart(ordersByStatus) {
  const ctx = document.getElementById('orderStatusChart').getContext('2d');

  if (orderStatusChart) orderStatusChart.destroy();

  const statusColors = {
    'pending': '#fbbf24',
    'processing': '#3b82f6',
    'shipped': '#06b6d4',
    'completed': '#10b981',
    'delivered': '#10b981',
    'cancelled': '#ef4444',
    'returned': '#f59e0b'
  };

  orderStatusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ordersByStatus.map(o => o.status.charAt(0).toUpperCase() + o.status.slice(1)),
      datasets: [{
        data: ordersByStatus.map(o => parseInt(o.count)),
        backgroundColor: ordersByStatus.map(o => statusColors[o.status.toLowerCase()] || '#6b7280')
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              const label = context.label || '';
              const value = context.parsed;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((value / total) * 100).toFixed(1);
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
}

// Render Top Products Chart
function renderTopProductsChart(topProducts) {
  const ctx = document.getElementById('topProductsChart').getContext('2d');

  if (topProductsChart) topProductsChart.destroy();

  const top5 = topProducts.slice(0, 5);

  topProductsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: top5.map(p => p.name.length > 20 ? p.name.substring(0, 20) + '...' : p.name),
      datasets: [{
        label: 'Quantity Sold',
        data: top5.map(p => parseInt(p.total_quantity)),
        backgroundColor: '#667eea'
      }]
    },
    options: {
      responsive: true,
      indexAxis: 'y',
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return `Sold: ${context.parsed.x} units`;
            }
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true
        }
      }
    }
  });
}

// Render Category Revenue Chart
function renderCategoryRevenueChart(categoryRevenue) {
  const ctx = document.getElementById('categoryRevenueChart').getContext('2d');

  if (categoryRevenueChart) categoryRevenueChart.destroy();

  categoryRevenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: categoryRevenue.map(c => c.category),
      datasets: [{
        label: 'Revenue (₹)',
        data: categoryRevenue.map(c => parseFloat(c.revenue)),
        backgroundColor: [
          '#667eea', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6'
        ]
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return `Revenue: ₹${formatNumber(context.parsed.y)}`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return '₹' + formatNumber(value);
            }
          }
        }
      }
    }
  });
}

// Render Category Ratings Chart
function renderCategoryRatingsChart(categoryRatings) {
  const ctx = document.getElementById('categoryRatingsChart').getContext('2d');

  if (categoryRatingsChart) categoryRatingsChart.destroy();

  categoryRatingsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: categoryRatings.map(c => c.category),
      datasets: [{
        label: 'Average Rating',
        data: categoryRatings.map(c => parseFloat(c.avg_rating)),
        backgroundColor: '#fbbf24',
        borderColor: '#f59e0b',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              const rating = context.parsed.y.toFixed(2);
              const reviews = categoryRatings[context.dataIndex].review_count;
              return `Rating: ${rating}/5 (${reviews} reviews)`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 5,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
}

// Update Recent Orders Table
function updateRecentOrders(orders) {
  const tbody = document.querySelector('#recentOrdersTable tbody');

  if (orders.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No recent orders</td></tr>';
    return;
  }

  tbody.innerHTML = orders.map(order => {
    const statusBadge = getStatusBadge(order.status);
    return `
      <tr>
        <td><strong>#${order.id}</strong></td>
        <td>${order.username}</td>
        <td>₹${formatNumber(order.total)}</td>
        <td>${statusBadge}</td>
        <td>${formatDateTime(order.created_at)}</td>
      </tr>
    `;
  }).join('');
}

// Update Low Stock Alerts
function updateLowStockAlerts(products) {
  const container = document.getElementById('lowStockList');

  if (products.length === 0) {
    container.innerHTML = '<p class="text-center text-muted">No low stock items</p>';
    return;
  }

  container.innerHTML = products.map(product => `
    <div class="low-stock-item">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <strong>${product.name}</strong>
          <br>
          <small class="text-muted">Stock: ${product.stock} units</small>
        </div>
        <span class="badge bg-warning text-dark">${product.stock}</span>
      </div>
    </div>
  `).join('');
}

// Helper Functions
function formatNumber(num) {
  return new Intl.NumberFormat('en-IN').format(Math.round(num));
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-IN', { month: 'short', day: 'numeric' });
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-IN', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function getStatusBadge(status) {
  const badges = {
    'pending': 'warning',
    'processing': 'info',
    'shipped': 'info',
    'completed': 'success',
    'delivered': 'success',
    'cancelled': 'danger',
    'returned': 'secondary'
  };

  const color = badges[status.toLowerCase()] || 'secondary';
  const text = status.charAt(0).toUpperCase() + status.slice(1);

  return `<span class="badge bg-${color}">${text}</span>`;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM loaded, initializing analytics...');

  // Check if Chart.js is loaded
  if (typeof Chart === 'undefined') {
    console.error('Chart.js is not loaded!');
    hideLoading();
    showError('Chart.js library failed to load. Please refresh the page.');
    return;
  }

  console.log('Chart.js loaded successfully');
  console.log('Fetching analytics data...');

  fetchAnalyticsData(30);

  // Refresh every 5 minutes
  setInterval(() => {
    console.log('Auto-refreshing analytics...');
    fetchAnalyticsData(30);
  }, 300000);
});
