window.filterClaims = function() {
    var search = document.getElementById('claimSearch').value.toLowerCase();
    var status = document.getElementById('statusFilter').value;
    document.querySelectorAll('.claim-row').forEach(function(row) {
        var matchSearch = !search || row.getAttribute('data-search').includes(search);
        var matchStatus = status === 'all' || row.getAttribute('data-status') === status;
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}
