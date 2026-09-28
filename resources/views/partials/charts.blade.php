@push('scripts')
<script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const charts = @json($charts);
    const common = { responsive: true, plugins: { legend: { display: false } } };
    const monthly = document.getElementById('monthlyChart');
    const category = document.getElementById('categoryChart');
    if (monthly) {
        new Chart(monthly, { type: 'bar', data: { labels: charts.monthly.labels, datasets: [{ data: charts.monthly.data, backgroundColor: '#0f5c56' }] }, options: common });
    }
    if (category) {
        new Chart(category, { type: 'doughnut', data: { labels: charts.categories.labels, datasets: [{ data: charts.categories.data, backgroundColor: ['#0c2d3e','#0f5c56','#8a5a12','#5c6b7a','#1d6b45','#8d2f2f'] }] }, options: { responsive: true } });
    }
});
</script>
@endpush
