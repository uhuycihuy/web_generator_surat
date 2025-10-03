'use strict';

(function () {
    const STORAGE_KEY = 'sidebarCollapsed';
    const html = document.documentElement;

    const getStoredCollapsed = () => {
        try {
            return localStorage.getItem(STORAGE_KEY) === 'true';
        } catch (error) {
            return false;
        }
    };

    const setStoredCollapsed = (collapsed) => {
        try {
            localStorage.setItem(STORAGE_KEY, collapsed ? 'true' : 'false');
        } catch (error) {
            /* ignore quota / privacy mode */
        }
    };

    const enableTransitions = () => {
        html.classList.add('js-loaded');
    };

    const init = () => {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if (!sidebar || !toggle) {
            enableTransitions();
            return;
        }

        const mediaQuery = window.matchMedia('(max-width: 768px)');
        const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

        const applyState = (collapsed, options = {}) => {
            const { persist = false } = options;
            const isCollapsed = Boolean(collapsed);

            html.dataset.sidebarState = isCollapsed ? 'collapsed' : 'open';
            html.classList.toggle('sidebar-collapsed', isCollapsed);
            html.classList.toggle('sidebar-open', !isCollapsed);
            sidebar.classList.toggle('collapsed', isCollapsed);

            if (persist) {
                setStoredCollapsed(isCollapsed);
            }
        };

        const openMobileMenu = (show) => {
            const shouldShow = typeof show === 'boolean' ? show : !sidebar.classList.contains('mobile-open');
            sidebar.classList.toggle('mobile-open', shouldShow);
            if (overlay) {
                overlay.classList.toggle('show', shouldShow);
            }
            document.body.classList.toggle('no-scroll', shouldShow);
        };

        let mobileButton = null;
        const ensureMobileButton = () => {
            if (mobileButton) {
                return mobileButton;
            }

            mobileButton = document.querySelector('.mobile-menu-btn');
            if (!mobileButton) {
                mobileButton = document.createElement('button');
                mobileButton.type = 'button';
                mobileButton.className = 'mobile-menu-btn';
                mobileButton.innerHTML = '<i class="fa-solid fa-bars"></i>';
                document.body.appendChild(mobileButton);
            }

            if (!mobileButton.dataset.bound) {
                mobileButton.addEventListener('click', () => openMobileMenu());
                mobileButton.dataset.bound = 'true';
            }

            if (overlay && !overlay.dataset.bound) {
                overlay.addEventListener('click', () => openMobileMenu(false));
                overlay.dataset.bound = 'true';
            }

            return mobileButton;
        };

        const teardownMobileButton = () => {
            if (mobileButton) {
                if (!mobileButton.dataset.persistent) {
                    mobileButton.remove();
                }
                mobileButton = null;
            }
            if (overlay) {
                overlay.classList.remove('show');
            }
            sidebar.classList.remove('mobile-open');
            document.body.classList.remove('no-scroll');
        };

        const handleViewportChange = (event) => {
            const isMobile = event.matches;

            if (isMobile) {
                html.classList.remove('sidebar-open', 'sidebar-collapsed');
                html.dataset.sidebarState = 'mobile';
                sidebar.classList.remove('collapsed');
                ensureMobileButton();
            } else {
                teardownMobileButton();
                const storedCollapsed = html.dataset.sidebarState === 'collapsed' || getStoredCollapsed();
                applyState(storedCollapsed);
            }
        };

        if (mediaQuery.matches) {
            handleViewportChange(mediaQuery);
        } else {
            const initialCollapsed = html.dataset.sidebarState === 'collapsed' || getStoredCollapsed();
            applyState(initialCollapsed);
        }

        toggle.addEventListener('click', () => {
            if (mediaQuery.matches) {
                return;
            }
            const collapsed = html.dataset.sidebarState !== 'collapsed';
            window.requestAnimationFrame(() => applyState(collapsed, { persist: true }));
        });

        mediaQuery.addEventListener ?
            mediaQuery.addEventListener('change', handleViewportChange) :
            mediaQuery.addListener(handleViewportChange);

        const handleReducedMotion = (event) => {
            html.classList.toggle('reduce-motion', event.matches);
        };

        handleReducedMotion(reducedMotionQuery);

        reducedMotionQuery.addEventListener ?
            reducedMotionQuery.addEventListener('change', handleReducedMotion) :
            reducedMotionQuery.addListener(handleReducedMotion);

        if (document.readyState === 'complete') {
            window.requestAnimationFrame(enableTransitions);
        } else {
            window.addEventListener('load', () => window.requestAnimationFrame(enableTransitions), { once: true });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
