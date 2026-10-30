
document.addEventListener('DOMContentLoaded', () => {

    // 1. TOOLTIPS INITIALIZATION (khusus icon saat sidebar dicollapse atau icon general)
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. SIDEBAR TOGGLE & LOCAL STORAGE
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth >= 992) {
        sidebar.classList.add('collapsed');
    }

    sidebarToggle.addEventListener('click', () => {
        if (window.innerWidth >= 992) {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        } else {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        }
    });

    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });

    // FIX: Auto-expand sidebar saat menu accordion di-klik di mode collapsed
    const accordionLinks = document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]');
    accordionLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        });
    });

    // 3. DARK MODE TOGGLE
    const themeToggle = document.getElementById('themeToggle');
    const htmlElement = document.documentElement;
    const savedTheme = localStorage.getItem('theme') || 'light';

    htmlElement.setAttribute('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);

    themeToggle.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        htmlElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
        updateChartsTheme(newTheme);
    });

    function updateThemeIcon(theme) {
        if (theme === 'dark') {
            themeToggle.innerHTML = '<i class="bi bi-sun fs-5"></i>';
        } else {
            themeToggle.innerHTML = '<i class="bi bi-moon fs-5"></i>';
        }
    }

    // 4. FULLSCREEN TOGGLE
    const fullscreenToggle = document.getElementById('fullscreenToggle');
    fullscreenToggle.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            fullscreenToggle.innerHTML = '<i class="bi bi-fullscreen-exit fs-6"></i>';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                fullscreenToggle.innerHTML = '<i class="bi bi-arrows-fullscreen fs-6"></i>';
            }
        }
    });

    // 5. SCROLL TO TOP FAB
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollTopBtn.style.display = 'flex';
        } else {
            scrollTopBtn.style.display = 'none';
        }
    });
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // 6. SHOW INITIAL TOAST NOTIFICATION
    const toastEl = document.getElementById('systemToast');
    if (toastEl) {
        const systemToast = new bootstrap.Toast(toastEl, {
            delay: 3000
        });
        setTimeout(() => systemToast.show(), 1000);
    }

    // 7. CHART.JS INITIALIZATION
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748B';

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    };

    let revenueChart, categoryChart;

    const revEl = document.getElementById('revenueChart');
    if (revEl) {
        const ctxRev = revEl.getContext('2d');
        const gradientBlue = ctxRev.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(37, 99, 235, 0.4)');
        gradientBlue.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        revenueChart = new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: ['12 Aug', '13 Aug', '14 Aug', '15 Aug', '16 Aug', '17 Aug', '18 Aug',
                    '19 Aug'
                ],
                datasets: [{
                    label: 'Revenue',
                    data: [8000, 9500, 8500, 11000, 14521, 12000, 13500, 15000],
                    borderColor: '#2563EB',
                    backgroundColor: gradientBlue,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#2563EB',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [4, 4],
                            color: '#E2E8F0'
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    }

    const catEl = document.getElementById('categoryChart');
    if (catEl) {
        const ctxCat = catEl.getContext('2d');
        categoryChart = new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: ['Electronics', 'Fashion', 'Home & Kitchen'],
                datasets: [{
                    data: [1200000, 950000, 750000],
                    backgroundColor: ['#2563EB', '#0EA5E9', '#F59E0B'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function updateChartsTheme(theme) {
        if (!revenueChart) return;
        const gridColor = theme === 'dark' ? '#334155' : '#E2E8F0';
        const textColor = theme === 'dark' ? '#94A3B8' : '#64748B';

        revenueChart.options.scales.y.grid.color = gridColor;
        revenueChart.options.scales.x.ticks.color = textColor;
        revenueChart.options.scales.y.ticks.color = textColor;
        revenueChart.update();
    }

    if (savedTheme === 'dark') updateChartsTheme('dark');

    // 8. DATATABLES INITIALIZATION
    // Override default alert error messages to use SweetAlert2 globally
    if (typeof $.fn.dataTable !== 'undefined') {
        $.fn.dataTable.ext.errMode = 'none';
        $(document).on('error.dt', function (e, settings, techNote, message) {
            console.error('DataTables error: ', message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan DataTables',
                    text: 'Gagal memproses tabel data. Silakan hubungi Administrator.',
                    footer: `<small class="text-danger">${message}</small>`,
                    confirmButtonColor: '#3085d6'
                });
            } else {
                alert('DataTables Error: ' + message);
            }
        });
    }

    if ($('#recentOrdersTable').length > 0) {
        $('#recentOrdersTable').DataTable({
            pageLength: 5,
            lengthMenu: [5, 10, 25],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records..."
            },
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
        });
    }

    // 9. FIX CHROMIUM ACCESSIBILITY WARNING:
    // "Blocked aria-hidden on an element because its descendant retained focus"
    document.addEventListener('hide.bs.modal', function (event) {
        if (document.activeElement && event.target && event.target.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    }, true);

    document.addEventListener('hidden.bs.modal', function () {
        // If another modal is still visible on the page (stacked / nested modals),
        // keep modal-open class on body so background doesn't lock or freeze
        if (document.querySelector('.modal.show')) {
            document.body.classList.add('modal-open');
        }
    });

});
