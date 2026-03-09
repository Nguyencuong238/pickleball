{{-- Tab switching, share, dropdown menu scripts --}}
<script>
(function() {
    // Tab switching
    function switchTab(tabName) {
        // Update buttons
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.tab === tabName);
            btn.setAttribute('aria-selected', btn.dataset.tab === tabName ? 'true' : 'false');
        });
        // Update panels
        document.querySelectorAll('.tab-content').forEach(function(panel) {
            panel.classList.toggle('active', panel.id === 'tab-' + tabName);
        });
        // Update URL hash
        history.replaceState(null, null, '#' + tabName);
    }

    // Bind tab buttons
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            switchTab(this.dataset.tab);
        });
    });

    // Hash routing on page load
    var hash = window.location.hash.substring(1);
    if (hash && document.getElementById('tab-' + hash)) {
        switchTab(hash);
    }

    // Expose globally for participant preview click
    window.switchTab = switchTab;
})();

// Share activity
function shareActivity() {
    var shareData = {
        title: @json($activity->title),
        text: @json($activity->title . ' - ' . $activity->activity_date->format('d/m/Y H:i')),
        url: window.location.href.split('#')[0]
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(shareData.url).then(function() {
            showToast('Đã sao chép liên kết!');
        });
    } else {
        // Legacy fallback
        var tempInput = document.createElement('input');
        tempInput.value = shareData.url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showToast('Đã sao chép liên kết!');
    }
}

// Simple toast notification
function showToast(message) {
    var toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.classList.add('show'); }, 10);
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { toast.remove(); }, 300);
    }, 2500);
}

// Header dropdown menu
function toggleHeaderMenu() {
    var dropdown = document.getElementById('header-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    var dropdown = document.getElementById('header-dropdown');
    if (dropdown && dropdown.classList.contains('show')) {
        if (!e.target.closest('.header-menu-wrapper')) {
            dropdown.classList.remove('show');
        }
    }
});
</script>
