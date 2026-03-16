// Dashboard Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    const CHART_W = 600, CHART_H = 200, PAD_TOP = 10, PAD_BOT = 10;
    const USABLE_H = CHART_H - PAD_TOP - PAD_BOT;
    const SVG_NS = 'http://www.w3.org/2000/svg';

    const svg          = document.getElementById('sales-chart-svg');
    const labelsEl     = document.getElementById('sales-chart-labels');
    const subtitleEl   = document.getElementById('sales-subtitle');
    const periodBtns   = document.querySelectorAll('.period-btn');

    if (!svg || !labelsEl || !periodBtns.length) return;

    const subtitleMap = {
        weekly:  'Weekly revenue trend',
        monthly: 'Monthly revenue trend',
        yearly:  'Yearly revenue trend',
    };

    // ── Button click handlers ──────────────────────────────────────────────
    periodBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Active style swap
            periodBtns.forEach(b => {
                b.classList.remove('bg-emerald-50', 'text-emerald-600', 'border-emerald-200');
                b.classList.add('text-gray-500', 'border-gray-200');
            });
            btn.classList.add('bg-emerald-50', 'text-emerald-600', 'border-emerald-200');
            btn.classList.remove('text-gray-500');

            const period = btn.dataset.period;
            if (subtitleEl) subtitleEl.textContent = subtitleMap[period] ?? '';
            fetchAndRender(period);
        });
    });

    // ── AJAX fetch ─────────────────────────────────────────────────────────
    function fetchAndRender(period) {
        fetch(`/dashboard/sales-data?period=${period}`)
            .then(res => res.json())
            .then(data => renderChart(data))
            .catch(err => console.error('Sales data fetch failed:', err));
    }

    // ── Chart render ───────────────────────────────────────────────────────
    function renderChart(salesData) {
        const keys   = Object.keys(salesData);
        const values = Object.values(salesData);
        const maxSale = Math.max(...values.map(d => d.amount), 1);

        // Compute SVG point coordinates
        const points = values.map((d, i) => {
            const x = keys.length > 1
                ? Math.round(i * (CHART_W / (keys.length - 1)))
                : CHART_W / 2;
            const y = Math.round(PAD_TOP + USABLE_H - (d.amount / maxSale) * USABLE_H);
            return { x, y, amount: d.amount, orders: d.orders };
        });

        const linePointsStr = points.map(p => `${p.x},${p.y}`).join(' ');
        const areaPointsStr = `0,${CHART_H} ${linePointsStr} ${CHART_W},${CHART_H}`;

        // Update area polygon
        const area = svg.querySelector('.chart-area');
        if (area) area.setAttribute('points', areaPointsStr);

        // Update line polyline
        const line = svg.querySelector('.chart-line');
        if (line) line.setAttribute('points', linePointsStr);

        // Rebuild dot groups
        const dotsContainer = svg.querySelector('.chart-dots-container');
        if (dotsContainer) {
            while (dotsContainer.firstChild) dotsContainer.removeChild(dotsContainer.firstChild);

            points.forEach(p => {
                const ttW = 68, ttH = 26;
                const ttX = Math.max(ttW / 2, Math.min(p.x, CHART_W - ttW / 2));
                const ttY = Math.max(p.y - 30, 2);
                const arrowX = p.x - ttX;

                const g = document.createElementNS(SVG_NS, 'g');
                g.setAttribute('class', 'chart-dot-group');
                g.style.cursor = 'pointer';

                // Hit area
                const hitCircle = makeSVG('circle', { cx: p.x, cy: p.y, r: 14, fill: 'transparent', class: 'chart-hit-area' });
                // Visible dot
                const dot = makeSVG('circle', { cx: p.x, cy: p.y, r: 3, fill: 'white', stroke: '#10b981', 'stroke-width': 2, class: 'chart-dot' });
                // Guide line
                const guide = makeSVG('line', { x1: p.x, y1: p.y, x2: p.x, y2: CHART_H, stroke: '#10b981', 'stroke-width': 1, 'stroke-dasharray': '3 3', class: 'chart-guide-line', opacity: 0 });

                // Tooltip group
                const tooltipG = document.createElementNS(SVG_NS, 'g');
                tooltipG.setAttribute('class', 'chart-tooltip');
                tooltipG.setAttribute('opacity', '0');
                tooltipG.setAttribute('transform', `translate(${ttX}, ${ttY})`);

                const bgRect = makeSVG('rect', { x: -ttW / 2, y: -2, width: ttW, height: ttH, rx: 6, fill: '#1f2937', opacity: 0.95 });
                const arrow  = makeSVG('polygon', { points: `${arrowX - 4},${ttH - 2} ${arrowX + 4},${ttH - 2} ${arrowX},${ttH + 3}`, fill: '#1f2937', opacity: 0.95 });
                const amtTxt = makeSVG('text', { x: 0, y: 8, 'text-anchor': 'middle', fill: 'white', 'font-size': 8, 'font-weight': 600 });
                amtTxt.textContent = `₱${p.amount.toLocaleString()}`;
                const ordTxt = makeSVG('text', { x: 0, y: 17, 'text-anchor': 'middle', fill: '#9ca3af', 'font-size': 6.5 });
                ordTxt.textContent = `${p.orders} orders`;

                tooltipG.append(bgRect, arrow, amtTxt, ordTxt);
                g.append(hitCircle, dot, guide, tooltipG);
                dotsContainer.appendChild(g);
            });
        }

        // Update x-axis labels
        labelsEl.innerHTML = keys.map(k => `<span class="text-xs text-gray-400 font-medium">${k}</span>`).join('');
    }

    // ── SVG element helper ─────────────────────────────────────────────────
    function makeSVG(tag, attrs) {
        const el = document.createElementNS(SVG_NS, tag);
        Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
        return el;
    }

    // ── Tooltip hover (event delegation on SVG) ────────────────────────────
    svg.addEventListener('mouseover', e => {
        const hitArea = e.target.closest('.chart-hit-area');
        if (!hitArea) return;
        const g = hitArea.closest('.chart-dot-group');
        if (!g) return;
        g.querySelector('.chart-tooltip')?.setAttribute('opacity', '1');
        g.querySelector('.chart-guide-line')?.setAttribute('opacity', '1');
        g.querySelector('.chart-dot')?.setAttribute('r', '5');
    });

    svg.addEventListener('mouseout', e => {
        const hitArea = e.target.closest('.chart-hit-area');
        if (!hitArea) return;
        const g = hitArea.closest('.chart-dot-group');
        if (!g) return;
        g.querySelector('.chart-tooltip')?.setAttribute('opacity', '0');
        g.querySelector('.chart-guide-line')?.setAttribute('opacity', '0');
        g.querySelector('.chart-dot')?.setAttribute('r', '3');
    });

    // ── Donut Chart (Order Status Distribution) ────────────────────────────
    const donutSvg = document.getElementById('donut-chart-svg');
    if (donutSvg) {
        const CIRC = 2 * Math.PI * 14; // ≈ 87.96

        const pending    = parseInt(donutSvg.dataset.pending)    || 0;
        const inprogress = parseInt(donutSvg.dataset.inprogress) || 0;
        const completed  = parseInt(donutSvg.dataset.completed)  || 0;
        const total      = pending + inprogress + completed;

        const segments = [
            { key: 'pending',    count: pending,    color: '#3b82f6', label: 'Pending' },
            { key: 'inprogress', count: inprogress, color: '#10b981', label: 'In Progress' },
            { key: 'completed',  count: completed,  color: '#f59e0b', label: 'Completed' },
        ];

        const centerEl  = document.getElementById('donut-center');
        const countEl   = document.getElementById('donut-center-count');
        const pctEl     = document.getElementById('donut-center-pct');
        const labelEl   = document.getElementById('donut-center-label');
        const legendItems = document.querySelectorAll('.donut-legend-item');

        let offset = 0;

        // Build and animate each segment
        segments.forEach((seg, idx) => {
            const dashLen = total > 0 ? (seg.count / total) * CIRC : 0;
            const gap     = dashLen > 0 ? CIRC - dashLen + 0.5 : CIRC;

            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', '18');
            circle.setAttribute('cy', '18');
            circle.setAttribute('r', '14');
            circle.setAttribute('fill', 'none');
            circle.setAttribute('stroke', seg.color);
            circle.setAttribute('stroke-width', '4');
            circle.setAttribute('stroke-linecap', 'round');
            circle.setAttribute('stroke-dasharray', `0 ${CIRC}`);
            circle.setAttribute('stroke-dashoffset', -offset);
            circle.setAttribute('data-key', seg.key);
            circle.style.transition = `stroke-dasharray 0.6s ease ${idx * 0.12}s, stroke-width 0.2s ease, opacity 0.2s ease`;
            circle.style.cursor = 'pointer';

            donutSvg.appendChild(circle);
            seg.circle = circle;

            // Trigger draw-in animation
            requestAnimationFrame(() => requestAnimationFrame(() => {
                circle.setAttribute('stroke-dasharray', `${dashLen > 0 ? dashLen - 0.5 : 0} ${gap}`);
            }));

            offset += dashLen;

            circle.addEventListener('mouseenter', () => activateSegment(seg));
            circle.addEventListener('mouseleave', deactivateAll);
        });

        // Legend hover
        legendItems.forEach(item => {
            const seg = segments.find(s => s.key === item.dataset.status);
            if (!seg) return;
            item.addEventListener('mouseenter', () => activateSegment(seg));
            item.addEventListener('mouseleave', deactivateAll);
        });

        function activateSegment(seg) {
            const pct = total > 0 ? Math.round((seg.count / total) * 100) : 0;
            if (countEl)  countEl.textContent  = seg.count;
            if (pctEl)  { pctEl.textContent    = `${pct}%`; pctEl.style.color = seg.color; }
            if (labelEl)  labelEl.textContent  = seg.label;
            if (centerEl) centerEl.style.opacity = '1';

            segments.forEach(s => {
                if (!s.circle) return;
                if (s.key === seg.key) {
                    s.circle.setAttribute('stroke-width', '5.5');
                    s.circle.style.opacity = '1';
                } else {
                    s.circle.setAttribute('stroke-width', '4');
                    s.circle.style.opacity = '0.3';
                }
            });

            legendItems.forEach(item => {
                if (item.dataset.status === seg.key) {
                    item.style.backgroundColor = '#f9fafb';
                    item.style.fontWeight = '700';
                } else {
                    item.style.opacity = '0.45';
                }
            });
        }

        function deactivateAll() {
            if (countEl)  countEl.textContent  = total;
            if (pctEl)  { pctEl.textContent    = ''; }
            if (labelEl)  labelEl.textContent  = 'Total Orders';
            if (centerEl) centerEl.style.opacity = total > 0 ? '1' : '0';

            segments.forEach(s => {
                if (!s.circle) return;
                s.circle.setAttribute('stroke-width', '4');
                s.circle.style.opacity = '1';
            });

            legendItems.forEach(item => {
                item.style.backgroundColor = '';
                item.style.fontWeight      = '';
                item.style.opacity         = '';
            });
        }

        // Show total on load
        if (total > 0) {
            setTimeout(() => {
                if (countEl)  countEl.textContent = total;
                if (labelEl)  labelEl.textContent = 'Total Orders';
                if (centerEl) centerEl.style.opacity = '1';
            }, 300);
        }
    }
});
