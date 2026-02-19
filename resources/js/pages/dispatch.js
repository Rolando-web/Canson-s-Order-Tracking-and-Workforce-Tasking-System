// Dispatch Page JavaScript

window.filterDispatch = function () {
    const search = document.getElementById('dispatchSearch')?.value.toLowerCase() ?? '';
    const status = document.getElementById('dispatchStatusFilter')?.value ?? '';
    const range  = document.getElementById('dispatchDateFilter')?.value ?? '';

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    document.querySelectorAll('.dispatch-card').forEach(card => {
        const cardSearch = card.dataset.search ?? '';
        const cardStatus = card.dataset.status ?? '';
        const cardDate   = card.dataset.date ? new Date(card.dataset.date) : null;

        const matchSearch = !search || cardSearch.includes(search);
        const matchStatus = !status || cardStatus === status;

        let matchDate = true;
        if (range && cardDate) {
            const d = new Date(cardDate);
            d.setHours(0, 0, 0, 0);
            if (range === 'today') {
                matchDate = d.getTime() === today.getTime();
            } else if (range === 'week') {
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                matchDate = d >= weekStart && d <= weekEnd;
            } else if (range === 'month') {
                matchDate = d.getMonth() === today.getMonth() && d.getFullYear() === today.getFullYear();
            }
        }

        card.style.display = (matchSearch && matchStatus && matchDate) ? '' : 'none';
    });
};
