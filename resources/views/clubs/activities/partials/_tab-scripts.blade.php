{{-- QR code library --}}
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

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

// Share modal with QR code
function showShareModal() {
    var pageUrl = window.location.href.split('#')[0];

    // Build modal
    var overlay = document.createElement('div');
    overlay.className = 'share-modal-overlay';
    overlay.innerHTML =
        '<div class="share-modal">' +
            '<h3>Chia sẻ hoạt động</h3>' +
            '<div class="share-qr-wrap" id="share-qr-container"></div>' +
            '<input type="text" value="" readonly class="share-link-input" id="share-link-input">' +
            '<div class="share-modal-actions">' +
                '<button type="button" class="share-btn-copy" id="share-copy-btn">Sao chép liên kết</button>' +
                '<button type="button" class="share-btn-close" id="share-close-btn">Đóng</button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(overlay);

    // Close handlers
    document.getElementById('share-close-btn').addEventListener('click', function() {
        overlay.remove();
    });
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.remove();
    });

    // Copy link handler
    document.getElementById('share-copy-btn').addEventListener('click', function() {
        var input = document.getElementById('share-link-input');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(input.value);
        } else {
            input.select();
            document.execCommand('copy');
        }
        this.textContent = 'Đã sao chép!';
        var btn = this;
        setTimeout(function() { btn.textContent = 'Sao chép liên kết'; }, 2000);
    });

    @if(isset($activity) && $activity->isOpenPlay() && isset($isManagement) && $isManagement)
    // Open play: fetch check-in URL from server
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
            var qrUrl = data.checkin_url;
            document.getElementById('share-link-input').value = qrUrl;
            new QRCode(document.getElementById('share-qr-container'), {
                text: qrUrl,
                width: 200,
                height: 200,
                colorDark: '#1f2937',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });
    @else
    // Other activities: use page URL
    document.getElementById('share-link-input').value = pageUrl;
    new QRCode(document.getElementById('share-qr-container'), {
        text: pageUrl,
        width: 200,
        height: 200,
        colorDark: '#1f2937',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
    @endif
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
