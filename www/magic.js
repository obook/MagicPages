/* ── Theme toggle ── */
function toggleTheme() {
    var isLight = document.documentElement.getAttribute('data-theme') === 'light';
    if (isLight) {
        document.documentElement.removeAttribute('data-theme');
        localStorage.removeItem('theme');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    }
}

/* ── Shooting stars ── */
(function () {
    var canvas = document.getElementById('shootingStars');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var stars = [];
    var W, H;

    function resize() {
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    var colors = [
        { r: 212, g: 168, b: 68 },
        { r: 232, g: 226, b: 244 },
        { r: 168, g: 126, b: 223 }
    ];

    function spawn() {
        var side = Math.random();
        var x, y;
        if (side < 0.7) {
            x = Math.random() * W * 1.2 - W * 0.1;
            y = -10;
        } else {
            x = W + 10;
            y = Math.random() * H * 0.5;
        }

        var angle = (200 + Math.random() * 40) * Math.PI / 180;
        var speed = 6 + Math.random() * 10;
        var maxLife = 40 + Math.random() * 50;
        var color = colors[Math.floor(Math.random() * colors.length)];
        var trailLen = 18 + Math.floor(Math.random() * 14);

        stars.push({
            x: x, y: y,
            vx: Math.cos(angle) * speed,
            vy: -Math.sin(angle) * speed,
            life: 0,
            maxLife: maxLife,
            color: color,
            width: 1 + Math.random() * 1.5,
            trail: [],
            trailLen: trailLen
        });
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);

        for (var i = stars.length - 1; i >= 0; i--) {
            var s = stars[i];
            s.x += s.vx;
            s.y += s.vy;
            s.life++;

            s.trail.push({ x: s.x, y: s.y });
            if (s.trail.length > s.trailLen) s.trail.shift();

            var progress = s.life / s.maxLife;
            var alpha;
            if (progress < 0.12) {
                alpha = progress / 0.12;
            } else if (progress > 0.55) {
                alpha = 1 - (progress - 0.55) / 0.45;
            } else {
                alpha = 1;
            }
            alpha = Math.max(0, Math.min(1, alpha)) * 0.85;

            var len = s.trail.length;
            if (len > 1) {
                for (var j = 1; j < len; j++) {
                    var t = j / (len - 1);
                    var segAlpha = alpha * t * t;
                    var segWidth = s.width * (0.15 + 0.85 * t);

                    var c = s.color;
                    var r = Math.round(c.r + (255 - c.r) * t * 0.5);
                    var g = Math.round(c.g + (255 - c.g) * t * 0.5);
                    var b = Math.round(c.b + (255 - c.b) * t * 0.5);

                    ctx.beginPath();
                    ctx.moveTo(s.trail[j - 1].x, s.trail[j - 1].y);
                    ctx.lineTo(s.trail[j].x, s.trail[j].y);
                    ctx.strokeStyle = 'rgba(' + r + ',' + g + ',' + b + ',' + segAlpha + ')';
                    ctx.lineWidth = segWidth;
                    ctx.lineCap = 'round';
                    ctx.stroke();
                }
            }

            ctx.beginPath();
            ctx.arc(s.x, s.y, s.width * 1.2, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,' + alpha * 0.7 + ')';
            ctx.fill();

            ctx.beginPath();
            ctx.arc(s.x, s.y, s.width * 0.5, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,' + alpha + ')';
            ctx.fill();

            if (s.life >= s.maxLife || s.x < -200 || s.x > W + 200 || s.y > H + 200) {
                stars.splice(i, 1);
            }
        }

        requestAnimationFrame(draw);
    }

    function scheduleNext() {
        var delay = 2000 + Math.random() * 6000;
        setTimeout(function () {
            spawn();
            if (Math.random() < 0.2) {
                setTimeout(spawn, 100 + Math.random() * 300);
                if (Math.random() < 0.5) {
                    setTimeout(spawn, 200 + Math.random() * 400);
                }
            }
            scheduleNext();
        }, delay);
    }

    setTimeout(spawn, 1000);
    scheduleNext();
    draw();
})();
