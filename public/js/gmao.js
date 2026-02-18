(function () {
    const t = window.gmaoI18n || { weatherNow: 'Current weather', wind: 'Wind', weatherFail: 'Unable to load weather data.' };

    function initClock() {
        const clock = document.getElementById('analogClock');
        const digital = document.getElementById('digitalClock');
        const dateNode = document.getElementById('clockDate');
        if (!clock) return;

        const ctx = clock.getContext('2d');
        const radius = clock.height / 2;
        ctx.translate(radius, radius);

        function drawHand(pos, length, width, color = '#0f172a') {
            ctx.beginPath();
            ctx.lineWidth = width;
            ctx.lineCap = 'round';
            ctx.strokeStyle = color;
            ctx.moveTo(0, 0);
            ctx.rotate(pos);
            ctx.lineTo(0, -length);
            ctx.stroke();
            ctx.rotate(-pos);
        }

        function drawClockFace() {
            ctx.clearRect(-radius, -radius, clock.width, clock.height);
            const r = radius - 10;
            const ring = ctx.createLinearGradient(-r, -r, r, r);
            ring.addColorStop(0, '#0c5079');
            ring.addColorStop(1, '#0e7b88');
            ctx.beginPath();
            ctx.arc(0, 0, r + 4, 0, Math.PI * 2);
            ctx.strokeStyle = ring;
            ctx.lineWidth = 7;
            ctx.stroke();

            const face = ctx.createRadialGradient(-r * 0.2, -r * 0.25, 20, 0, 0, r);
            face.addColorStop(0, '#ffffff');
            face.addColorStop(1, '#e8f2fa');
            ctx.beginPath();
            ctx.arc(0, 0, r, 0, Math.PI * 2);
            ctx.fillStyle = face;
            ctx.fill();
            ctx.strokeStyle = '#9cc0d8';
            ctx.lineWidth = 2;
            ctx.stroke();

            for (let i = 0; i < 60; i++) {
                const ang = (i * Math.PI) / 30;
                const inner = i % 5 === 0 ? r * 0.81 : r * 0.87;
                const outer = r * 0.93;
                ctx.beginPath();
                ctx.lineWidth = i % 5 === 0 ? 3 : 1.4;
                ctx.strokeStyle = i % 5 === 0 ? '#1d5a7d' : '#90afc6';
                ctx.moveTo(inner * Math.sin(ang), -inner * Math.cos(ang));
                ctx.lineTo(outer * Math.sin(ang), -outer * Math.cos(ang));
                ctx.stroke();
            }

            for (let n = 1; n <= 12; n++) {
                const ang = (n * Math.PI) / 6;
                ctx.rotate(ang);
                ctx.translate(0, -radius * 0.78);
                ctx.rotate(-ang);
                ctx.fillStyle = '#0f4568';
                ctx.font = '700 16px "Segoe UI", sans-serif';
                ctx.fillText(String(n), -5, 5);
                ctx.rotate(ang);
                ctx.translate(0, radius * 0.78);
                ctx.rotate(-ang);
            }
        }

        function render() {
            drawClockFace();
            const now = new Date();
            const hour = now.getHours() % 12;
            const minute = now.getMinutes();
            const second = now.getSeconds();

            drawHand((hour * Math.PI) / 6 + (minute * Math.PI) / 360, radius * 0.43, 8, '#0f3a5b');
            drawHand((minute * Math.PI) / 30, radius * 0.61, 5, '#0f6a88');
            drawHand((second * Math.PI) / 30, radius * 0.72, 2, '#c62828');

            ctx.beginPath();
            ctx.arc(0, 0, 7, 0, Math.PI * 2);
            ctx.fillStyle = '#0f4f78';
            ctx.fill();
            ctx.beginPath();
            ctx.arc(0, 0, 3, 0, Math.PI * 2);
            ctx.fillStyle = '#f59e0b';
            ctx.fill();

            if (digital) {
                digital.textContent = now.toLocaleTimeString([], { hour12: false });
            }
            if (dateNode) {
                dateNode.textContent = now.toLocaleDateString();
            }
        }

        render();
        setInterval(render, 1000);
    }

    async function fetchWeather() {
        const weatherBox = document.getElementById('weatherBox');
        if (!weatherBox) return;

        function fetchOpenMeteo(lat, lon) {
            return fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code,wind_speed_10m`)
                .then(r => r.json());
        }

        function geolocationPromise() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('No geolocation'));
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    pos => resolve({ lat: pos.coords.latitude, lon: pos.coords.longitude }),
                    reject,
                    { enableHighAccuracy: false, timeout: 5000, maximumAge: 600000 }
                );
            });
        }

        try {
            let coords;
            try {
                coords = await geolocationPromise();
            } catch (geoErr) {
                const ip = await fetch('https://ipapi.co/json/').then(r => r.json());
                coords = { lat: ip.latitude, lon: ip.longitude, city: ip.city };
            }

            const data = await fetchOpenMeteo(coords.lat, coords.lon);
            if (!data.current) throw new Error('No weather payload');

            const cityLabel = coords.city ? ` | ${coords.city}` : '';
            weatherBox.textContent = `${t.weatherNow}: ${data.current.temperature_2m}°C | ${t.wind} ${data.current.wind_speed_10m} km/h${cityLabel}`;
        } catch (err) {
            weatherBox.textContent = t.weatherFail;
        }
    }

    function pieChart(id, colors) {
        const node = document.getElementById(id);
        if (!node || typeof Chart === 'undefined') return;
        const values = JSON.parse(node.dataset.values || '{}');
        new Chart(node, {
            type: 'doughnut',
            data: { labels: Object.keys(values), datasets: [{ data: Object.values(values), backgroundColor: colors }] },
            options: {
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                }
            }
        });
    }

    function barChart(id) {
        const node = document.getElementById(id);
        if (!node || typeof Chart === 'undefined') return;
        const values = JSON.parse(node.dataset.values || '{}');
        const data = Object.values(values);
        const maxValue = data.length ? Math.max(...data) : 0;
        new Chart(node, {
            type: 'bar',
            data: {
                labels: Object.keys(values),
                datasets: [{ data, backgroundColor: '#0b5d8b', borderRadius: 8, minBarLength: 6 }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: Math.max(1, maxValue + 1),
                        ticks: { precision: 0, stepSize: 1 }
                    }
                }
            }
        });
    }

    initClock();
    fetchWeather();
    pieChart('projectStatusChart', ['#0ea5a4', '#2563eb', '#22c55e', '#f97316']);
    pieChart('requestStatusChart', ['#f59e0b', '#3b82f6', '#10b981', '#ef4444']);
    pieChart('tasksTypeChart', ['#8b5cf6', '#06b6d4', '#14b8a6']);
    barChart('sectorBacklogChart');
})();
