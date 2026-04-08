{{-- RSVP script only - UI moved to _rsvp-button.blade.php --}}
<script>
var _rsvpBusy = false;
function rsvpAction(action) {
    if (_rsvpBusy) return;
    _rsvpBusy = true;
    var btns = document.querySelectorAll('.btn-rsvp');
    btns.forEach(function(b) { b.disabled = true; b.style.opacity = '0.6'; });

    var joinUrl = @json(route('clubs.activities.rsvp', [$club, $activity]));
    var url = joinUrl;
    var method = action === 'join' ? 'POST' : 'DELETE';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            if (data.gems_charged) {
                alert(data.message);
            }
            if (data.gems_refunded > 0) {
                alert(data.message);
            }
            location.reload();
        } else if (data.insufficient_gems) {
            alert(data.message + '\n\nVui lòng nạp thêm Gems để tham gia.');
            _rsvpReset(btns);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
            _rsvpReset(btns);
        }
    })
    .catch(function() {
        alert('Không thể kết nối. Vui lòng thử lại.');
        _rsvpReset(btns);
    });
}
function _rsvpReset(btns) {
    _rsvpBusy = false;
    btns.forEach(function(b) { b.disabled = false; b.style.opacity = ''; });
}
</script>
