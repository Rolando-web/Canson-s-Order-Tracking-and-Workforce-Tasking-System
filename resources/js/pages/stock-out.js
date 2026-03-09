window.filterStockOutHistory = function() {
    const search = document.getElementById('stockOutHistorySearch')?.value.toLowerCase() ?? '';
    document.querySelectorAll('.stockout-history-row').forEach(row => {
        row.style.display = !search || row.dataset.name.includes(search) ? '' : 'none';
    });
};
