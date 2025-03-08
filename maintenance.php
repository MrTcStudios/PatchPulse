
<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - In Manutenzione</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #fff;
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .container {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .logo {
            font-size: 4em;
            font-weight: bold;
            margin-bottom: 20px;
            animation: glow 2s ease-in-out infinite alternate,
                       float 6s ease-in-out infinite;
            background: linear-gradient(45deg, #4f46e5, #9333ea);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .message {
            font-size: 1.5em;
            margin-bottom: 30px;
            opacity: 0;
            animation: fadeIn 1s ease-out forwards 0.5s,
                       wave 8s ease-in-out infinite;
        }

        .loader {
            width: 150px;
            height: 4px;
            background: #333;
            margin: 30px auto;
            position: relative;
            overflow: hidden;
            border-radius: 2px;
        }

        .loader::after {
            content: '';
            position: absolute;
            left: -50%;
            height: 100%;
            width: 50%;
            background: linear-gradient(90deg, #4f46e5, #9333ea);
            animation: loading 1.5s infinite;
            border-radius: 2px;
            box-shadow: 0 0 20px rgba(79, 70, 229, 0.6);
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.8) 0%, rgba(79, 70, 229, 0) 70%);
            border-radius: 50%;
            animation: float-particle var(--duration) infinite linear;
            opacity: var(--opacity);
        }

        .orbiting-dots {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            animation: rotate 20s linear infinite;
        }

        .dot {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #4f46e5;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes glow {
            0% {
                text-shadow: 0 0 10px rgba(79, 70, 229, 0.5),
                           0 0 20px rgba(79, 70, 229, 0.3),
                           0 0 30px rgba(79, 70, 229, 0.2);
            }
            100% {
                text-shadow: 0 0 20px rgba(79, 70, 229, 0.8),
                           0 0 30px rgba(79, 70, 229, 0.6),
                           0 0 40px rgba(79, 70, 229, 0.4);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes wave {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(-5px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes loading {
            0% { left: -50%; }
            100% { left: 100%; }
        }

        @keyframes float-particle {
            0% { 
                transform: translate(0, 0) rotate(0deg); 
                opacity: var(--opacity);
            }
            100% { 
                transform: translate(var(--translate-x), var(--translate-y)) rotate(360deg);
                opacity: 0;
            }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        .background-gradient {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(79, 70, 229, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

    </style>
</head>
<body>
    <div class="background-gradient"></div>
    <div class="particles" id="particles"></div>
    <div class="orbiting-dots" id="orbiting-dots"></div>
    <div class="container">
        <div class="logo">PatchPulse</div>
        <div class="message">Stiamo lavorando per migliorare il sito.<br>Torneremo presto online!</div>
        <div class="loader"></div>
    </div>

    <script>
        // Creazione particelle avanzate
        const particlesContainer = document.getElementById('particles');
        const numberOfParticles = 30;

        for (let i = 0; i < numberOfParticles; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Dimensione casuale
            const size = 5 + Math.random() * 15;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            
            // Posizione iniziale casuale
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            
            // Proprietà di animazione casuali
            const duration = 15 + Math.random() * 20 + 's';
            const translateX = (Math.random() - 0.5) * 500 + '%';
            const translateY = -Math.random() * 500 + '%';
            const opacity = 0.1 + Math.random() * 0.4;
            
            particle.style.setProperty('--duration', duration);
            particle.style.setProperty('--translate-x', translateX);
            particle.style.setProperty('--translate-y', translateY);
            particle.style.setProperty('--opacity', opacity);
            
            particlesContainer.appendChild(particle);
        }

        // Creazione punti orbitanti
        const orbitingDotsContainer = document.getElementById('orbiting-dots');
        const numberOfDots = 12;

        for (let i = 0; i < numberOfDots; i++) {
            const dot = document.createElement('div');
            dot.className = 'dot';
            
            const angle = (i / numberOfDots) * Math.PI * 2;
            const radius = 150;
            const x = Math.cos(angle) * radius + window.innerWidth / 2;
            const y = Math.sin(angle) * radius + window.innerHeight / 2;
            
            dot.style.left = x + 'px';
            dot.style.top = y + 'px';
            dot.style.animationDelay = `${i * 0.2}s`;
            
            orbitingDotsContainer.appendChild(dot);
        }
    </script>
</body>
</html>
<?php exit; ?>




