var _currentPhaseItemId = null;
var _csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

window.openUpdateProgress = function(itemId, itemName, required, current) {
    _currentPhaseItemId = itemId;
    document.getElementById('progressItemName').textContent = itemName;
    document.getElementById('progressItemCurrent').textContent = current;
    document.getElementById('progressItemRequired').textContent = required;
    var max = required - current;
    var input = document.getElementById('progressAddQty');
    input.max = max;
    input.value = Math.min(1, max);
    document.getElementById('updateProgressModal').classList.remove('hidden');
}

window.closeUpdateProgress = function() {
    document.getElementById('updateProgressModal').classList.add('hidden');
    _currentPhaseItemId = null;
}

window.submitProgress = function() {
    if (!_currentPhaseItemId) return;
    var addQty = parseInt(document.getElementById('progressAddQty').value);
    if (!addQty || addQty < 1) { alert('Please enter a valid quantity.'); return; }

    var btn = document.querySelector('#updateProgressModal button[onclick="submitProgress()"]');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    fetch('/order-phase-items/' + _currentPhaseItemId + '/progress', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
        body: JSON.stringify({ add_qty: addQty }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            var completedEl = document.querySelector('.completed-count-' + _currentPhaseItemId);
            if (completedEl) completedEl.textContent = data.completed_qty;

            var bar = document.getElementById('item-bar-' + _currentPhaseItemId);
            if (bar) {
                bar.style.width = data.pct + '%';
                if (data.pct >= 100) bar.classList.replace('bg-blue-400', 'bg-emerald-500');
            }

            var pctEl = document.getElementById('item-pct-' + _currentPhaseItemId);
            if (pctEl) pctEl.textContent = data.pct;

            closeUpdateProgress();

            if (data.phase_done) {
                setTimeout(() => location.reload(), 500);
            }
        } else {
            alert('Failed to update: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(() => alert('Network error. Please try again.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Save Progress';
    });
}
