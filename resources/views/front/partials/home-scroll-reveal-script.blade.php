<script>
// Scroll reveal
const hpReveals = document.querySelectorAll('.homepage .reveal');
const hpObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('visible');
        }
    });
}, { threshold: 0.12 });
hpReveals.forEach(r => hpObserver.observe(r));

// Counter animation for stats band
function hpAnimateCounter(el, target, suffix) {
    suffix = suffix || '';
    let start = 0;
    const duration = 2000;
    const step = (timestamp) => {
        if (!start) start = timestamp;
        const progress = Math.min((timestamp - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(eased * target).toLocaleString() + suffix;
        if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

const hpStatsObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            const nums = e.target.querySelectorAll('.num');
            nums.forEach(n => {
                const text = n.textContent;
                const match = text.match(/[\d,]+/);
                if (match) {
                    const num = parseInt(match[0].replace(/,/g, ''));
                    const suffix = text.replace(/[\d,]+/, '');
                    hpAnimateCounter(n, num, suffix);
                }
            });
            hpStatsObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.3 });

const hpStatsBand = document.querySelector('.stats-band-inner');
if (hpStatsBand) hpStatsObserver.observe(hpStatsBand);
</script>
