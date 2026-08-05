
import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const toggleButtons = document.querySelectorAll('[data-admin-sidebar-toggle]');

    if (!sidebar) {
        return;
    }

    const storageKey = 'labcentral.admin.sidebar.open.v2';
    const updateToggleState = (isOpen) => {
        toggleButtons.forEach((button) => {
            button.classList.toggle('is-collapsed', !isOpen);
            button.setAttribute('aria-pressed', String(isOpen));
        });
    };

    const setSidebarState = (isOpen) => {
        body.classList.toggle('sidebar-collapsed', !isOpen);
        body.classList.toggle('coordinator-sidebar-open', isOpen);
        localStorage.setItem(storageKey, isOpen ? 'open' : 'closed');
        updateToggleState(isOpen);
    };

    const applyStoredState = () => {
        const storedState = localStorage.getItem(storageKey);

        setSidebarState(storedState !== 'closed');
    };

    applyStoredState();

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const isOpen = body.classList.contains('sidebar-collapsed');
            setSidebarState(isOpen);
        });
    });
});
