import { animate, stagger } from 'motion';
import { createIcons, icons } from 'lucide';

// Initialize Lucide icons
function initIcons() {
    createIcons({
        icons,
        attrs: {
            'stroke-width': 2,
            class: 'lucide-icon inline-block',
        },
    });
}

// Initialize Motion animations
function initMotion() {
    // Staggered cards entrance
    const cards = document.querySelectorAll('.motion-card');
    if (cards.length > 0) {
        animate(
            cards,
            { opacity: [0, 1], y: [20, 0] },
            { delay: stagger(0.06), duration: 0.45, easing: 'ease-out' }
        );
    }

    // Stat items entrance
    const stats = document.querySelectorAll('.motion-stat');
    if (stats.length > 0) {
        animate(
            stats,
            { opacity: [0, 1], scale: [0.92, 1] },
            { delay: stagger(0.08), duration: 0.4, easing: 'ease-out' }
        );
    }

    // General fade-in elements
    const fadeIns = document.querySelectorAll('.motion-fade-in');
    if (fadeIns.length > 0) {
        animate(
            fadeIns,
            { opacity: [0, 1], y: [12, 0] },
            { duration: 0.35, easing: 'ease-out' }
        );
    }
}

// Toast notification helper
function initToasts() {
    const toasts = document.querySelectorAll('.toast-alert');
    toasts.forEach((toast) => {
        animate(
            toast,
            { opacity: [0, 1], x: [50, 0] },
            { duration: 0.35, easing: 'ease-out' }
        );

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            animate(
                toast,
                { opacity: [1, 0], x: [0, 50] },
                { duration: 0.3 }
            ).then(() => {
                toast.remove();
            });
        }, 5000);

        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                animate(
                    toast,
                    { opacity: [1, 0], x: [0, 50] },
                    { duration: 0.25 }
                ).then(() => {
                    toast.remove();
                });
            });
        }
    });
}

// Modal dialog helper with Motion physics
window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const backdrop = modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('.modal-panel');

    if (backdrop) {
        animate(backdrop, { opacity: [0, 1] }, { duration: 0.2 });
    }
    if (panel) {
        animate(
            panel,
            { opacity: [0, 1], scale: [0.92, 1], y: [20, 0] },
            { duration: 0.3, easing: [0.16, 1, 0.3, 1] }
        );
    }
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const backdrop = modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('.modal-panel');

    const animations = [];
    if (backdrop) {
        animations.push(animate(backdrop, { opacity: [1, 0] }, { duration: 0.15 }));
    }
    if (panel) {
        animations.push(animate(panel, { opacity: [1, 0], scale: [1, 0.95] }, { duration: 0.2 }));
    }

    Promise.all(animations).then(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
};

// Quiz Countdown Timer with auto-submit
window.initQuizTimer = function (totalSeconds, formId) {
    let remaining = totalSeconds;
    const timerDisplay = document.getElementById('quiz-timer-display');
    const timerContainer = document.getElementById('quiz-timer-container');
    const form = document.getElementById(formId);

    function updateDisplay() {
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;

        let timeString = '';
        if (hours > 0) {
            timeString += String(hours).padStart(2, '0') + ':';
        }
        timeString += String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

        if (timerDisplay) {
            timerDisplay.textContent = timeString;
        }

        // Warning state when under 5 minutes (300 seconds)
        if (remaining <= 300 && timerContainer) {
            timerContainer.classList.add('bg-rose-50', 'border-rose-300', 'text-rose-700', 'animate-timer-warning');
            timerContainer.classList.remove('bg-indigo-50', 'border-indigo-200', 'text-indigo-700');
        }

        if (remaining <= 0) {
            clearInterval(interval);
            alert('Waktu pengerjaan kuis telah habis! Jawaban Anda akan otomatis dikumpulkan.');
            if (form) {
                form.submit();
            }
        } else {
            remaining--;
        }
    }

    updateDisplay();
    const interval = setInterval(updateDisplay, 1000);
};

// Responsive Mobile & Tablet Sidebar Drawer
function initSidebarDrawer() {
    const sidebar = document.getElementById('app-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    const backdrop = document.getElementById('sidebar-backdrop');

    if (!sidebar) return;

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        if (backdrop) {
            backdrop.classList.remove('hidden');
            // Force reflow for CSS transition
            void backdrop.offsetWidth;
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
        }
        document.body.classList.add('overflow-hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        if (backdrop) {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
        document.body.classList.remove('overflow-hidden');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openSidebar();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeSidebar();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
            closeSidebar();
        }
    });

    // Close sidebar on link click when on mobile/tablet screens (< 1024px)
    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    });

    // Reset when resizing window to desktop view
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            if (backdrop) {
                backdrop.classList.add('hidden');
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
            }
            document.body.classList.remove('overflow-hidden');
        }
    });
}

// Run when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initIcons();
    initMotion();
    initToasts();
    initSidebarDrawer();
});

