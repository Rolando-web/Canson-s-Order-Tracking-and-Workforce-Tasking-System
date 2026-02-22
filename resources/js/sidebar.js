/**
 * Sidebar Toggle Script
 * Handles expand / collapse on desktop and off-canvas drawer on mobile.
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar      = document.getElementById('sidebar');
    const mainContent  = document.getElementById('mainContent');
    const overlay      = document.getElementById('sidebarOverlay');
    const btnCollapse  = document.getElementById('sidebarToggle');
    const btnExpand    = document.getElementById('sidebarToggleCollapsed');
    const btnMobile    = document.getElementById('sidebarMobileToggle');

    const LG_BREAKPOINT = 1024;

    /* ── Desktop: collapse / expand ── */
    function toggleDesktop() {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('sidebar-collapsed', isCollapsed);
        sidebar.setAttribute('data-collapsed', isCollapsed);
    }

    /* ── Mobile: open / close drawer ── */
    function openMobile() {
        sidebar.classList.add('mobile-open');
        overlay?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMobile() {
        sidebar.classList.remove('mobile-open');
        overlay?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    /* ── Event Listeners ── */
    btnCollapse?.addEventListener('click', () => {
        if (window.innerWidth < LG_BREAKPOINT) {
            closeMobile();
        } else {
            toggleDesktop();
        }
    });

    btnExpand?.addEventListener('click', toggleDesktop);
    btnMobile?.addEventListener('click', openMobile);
    overlay?.addEventListener('click', closeMobile);

    /* Close mobile drawer on Escape key */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMobile();
    });

    /* ── Sidebar Dropdowns ── */
    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const dropdown = toggle.closest('.sidebar-dropdown');
            const isOpen = dropdown.getAttribute('data-dropdown-open') === 'true';
            dropdown.setAttribute('data-dropdown-open', !isOpen);

            const menu = dropdown.querySelector('.sidebar-dropdown-menu');
            if (!isOpen) {
                menu.style.maxHeight = menu.scrollHeight + 'px';
            } else {
                menu.style.maxHeight = '0';
            }
        });
    });

    /* Ensure open dropdowns have correct max-height on load */
    document.querySelectorAll('.sidebar-dropdown[data-dropdown-open="true"]').forEach(dropdown => {
        const menu = dropdown.querySelector('.sidebar-dropdown-menu');
        if (menu) {
            menu.style.maxHeight = menu.scrollHeight + 'px';
        }
    });

    /* Clean up mobile state when resizing past the breakpoint */
    window.addEventListener('resize', () => {
        if (window.innerWidth >= LG_BREAKPOINT) {
            closeMobile();
        }
    });
});
