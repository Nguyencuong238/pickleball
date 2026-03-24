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

@if(isset($activity) && $activity->isOpenPlay() && isset($isManagement) && $isManagement)
function generateQrCode() {
    fetch('{{ route("clubs.activities.generate-qr", [$club, $activity]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            // Show modal with QR link
            var modal = document.createElement('div');
            modal.className = 'ca-qr-modal-overlay';
            modal.innerHTML =
                '<div class="ca-qr-modal">' +
                    '<h3>Ma QR Check-in</h3>' +
                    '<p style="margin:12px 0;font-size:13px;color:#666;">Chia se link nay hoac tao ma QR tu link ben duoi:</p>' +
                    '<div class="ca-qr-link-box">' +
                        '<input type="text" value="' + data.checkin_url + '" readonly id="qr-link-input" style="width:100%;padding:10px;border:2px solid #e0e0e0;border-radius:8px;font-size:13px;">' +
                    '</div>' +
                    '<div style="display:flex;gap:8px;margin-top:16px;">' +
                        '<button onclick="copyQrLink()" style="flex:1;padding:10px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Sao chep link</button>' +
                        '<button onclick="this.closest(\'.ca-qr-modal-overlay\').remove()" style="flex:1;padding:10px;background:#f3f4f6;color:#555;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Dong</button>' +
                    '</div>' +
                '</div>';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:9999;';
            modal.querySelector('.ca-qr-modal').style.cssText = 'background:#fff;border-radius:16px;padding:24px;max-width:440px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.15);';
            modal.addEventListener('click', function(e) { if (e.target === modal) modal.remove(); });
            document.body.appendChild(modal);
        }
    });
}

function copyQrLink() {
    var input = document.getElementById('qr-link-input');
    input.select();
    document.execCommand('copy');
    var btn = input.closest('.ca-qr-modal').querySelector('button');
    btn.textContent = 'Da sao chep!';
    setTimeout(function() { btn.textContent = 'Sao chep link'; }, 2000);
}
@endif
</script>
