<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banque MaxiMoula</title>
    <style>
        * {
            font-family: Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90%;
        }

        .nav {
            background-color: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            align-items: center;
        }

        .nav-logo {
            font-size: 24px;
            margin-right: 10px;
        }

        .scene {
            text-align: center;
            position: relative;
        }

        .titre {
            font-size: 1.5rem;
            margin-bottom: 40px;
            font-weight: bold;
            color: #ff6b6b;
        }

        .portefeuille-container {
            position: relative;
            display: inline-block;
        }

        .portefeuille {
            font-size: 6rem;
            position: relative;
            z-index: 10;
            transition: transform 0.2s;
        }

        .portefeuille:hover {
            transform: scale(0.9) rotate(5deg);
        }

        .billet {
            position: absolute;
            font-size: 3rem;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) scale(0.5);
            opacity: 0;
            z-index: 5;

            animation: envol 2.5s infinite ease-out;
            animation-delay: var(--delay);
        }

        .facture {
            font-size: 3.5rem;
        }

        /* --- LA BARRE DE PROGRESSION --- */
        .progress-container {
            margin-top: 50px;
        }

        .progress-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .progress {
            width: 100%;
            height: 10px; /* Un peu plus épaisse pour bien la voir */
            background-color: #ddd;
            position: relative;
            overflow: hidden;
            border-radius: 5px; /* Bords arrondis pour l'esthétique */
        }

        /* La jauge qui avance */
        .progress::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            /* L'animation est appliquée ici ! */
            animation: progressAnim 4s ease-out forwards; 
        }

        /* --- LES ANIMATIONS --- */
        @keyframes progressAnim {
            0% {
                width: 0%;
                background-color: #2ecc71; /* Vert au début, on y croit */
            }
            50% {
                background-color: #f1c40f; /* Jaune, ça commence à piquer */
            }
            100% {
                width: 100%;
                background-color: #e74c3c; /* Rouge vif, fin du game */
            }
        }

        @keyframes envol {
            0% {
                opacity: 0;
                transform: translate(-50%, 0) scale(0.5) rotate(0deg);
            }
            15% {
                opacity: 1;
                transform: translate(-50%, -50px) scale(1.2) rotate(0deg);
            }
            50% {
                opacity: 1;
                transform: translate(calc(-50% + var(--x) / 2), -200px) scale(1) rotate(calc(var(--r) / 2));
            }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--x)), -400px) scale(0.8) rotate(var(--r));
            }
        }

        @keyframes apparaitre {
            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="nav">
        <p><span class="nav-logo">🤑</span> MaxiMoula Bank Services</p>
    </div>

    <div class="main">
        <div class="scene">
            <div class="portefeuille-container">
                <div class="billet" style="--delay: 0s; --x: -150px; --r: -45deg;">💸</div>
                <div class="billet" style="--delay: 0.4s; --x: 100px; --r: 30deg;">💸</div>
                <div class="billet" style="--delay: 0.8s; --x: -80px; --r: -20deg;">💸</div>
                <div class="billet" style="--delay: 1.2s; --x: 150px; --r: 60deg;">💸</div>
                <div class="billet facture" style="--delay: 1.6s; --x: 0px; --r: 10deg;">📄</div>
                <div class="portefeuille">👛</div>
            </div>
            
            <div class="progress-container">
                <div class="progress-label">Vidage du compte en cours...</div>
                <div class="progress"></div>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    setTimeout(() => {
        window.location.href = '/reservation/confirm.php';
    }, 4000);
</script>