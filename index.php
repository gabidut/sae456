<?php
// On appelle global.php qui va charger la session, le CSS et la navBar
include 'includes/global.php';
?>

<section class="hero">
    <h2>La puissance du transport <span>Nordique</span></h2>
    <p>Réseau de bus, liaisons Express et voyages en Normandie. Nous guidons vos trajets du quotidien et vos plus belles échappées avec la force et la ponctualité des Vikings.</p>
    <a href="#services" class="btn">Découvrir nos services</a>
</section>

<section id="services" class="services">
    <h3>Nos Services</h3>

    <div class="services-grid">
        <div class="service-card">
            <h4>Réservation de trajet</h4>
            <p>Ce service vous permet de planifier votre itinéraire à l'avance et de bloquer votre place à bord. Que ce soit pour un trajet régulier ou un transport à la demande, il vous suffit d'indiquer votre point de départ, votre destination et l'heure souhaitée pour voyager l'esprit tranquille.</p>
        </div>

        <div class="service-card">
            <h4>Carte du réseau</h4>
            <p>La carte du réseau est l'outil idéal pour visualiser l'ensemble des lignes de transport en un coup d'œil. Elle vous permet de repérer facilement les correspondances, les arrêts principaux et les itinéraires possibles pour vous déplacer efficacement dans toute la région.</p>
        </div>

        <div class="service-card">
            <h4>Horaires des lignes</h4>
            <p>Ce service met à votre disposition les fiches horaires en temps réel de chaque ligne. Vous pouvez y consulter les heures de passage exactes à chaque arrêt, les fréquences des passages selon les jours de la semaine, ainsi que les éventuels changements ou retards.</p>
        </div>
    </div>
</section>

<!-- SECTION DE NOTRE FLOTTE ÉCOLOGIQUE (3D BUS SHOWCASE) -->
<style>
    .eco-bus-section {
        margin: 60px 0;
        padding: 40px 20px;
        background-color: #1a1a1e;
        border-radius: 20px;
    }

    .eco-bus-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .eco-bus-header h2 {
        font-size: 2.2rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #ffffff;
        margin-bottom: 10px;
    }

    .eco-bus-header h2 span {
        color: var(--accent-color);
        /* Viking Red Accent */
    }

    .eco-bus-viewer-card h2 span {
        color: var(--accent-color);
        /* Viking Red Accent */
    }

    .eco-bus-header p {
        color: #b0b0b0;
        max-width: 800px;
        margin: 0 auto;
        font-size: 1.1rem;
    }

    /* 3D Viewer Container */
    .eco-bus-container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* Colonne 3D Viewer Card */
    .eco-bus-viewer-card {
        background-color: #232329;
        border-radius: 12px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        position: relative;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    #canvas-container {
        position: relative;
        width: 100%;
        height: 380px;
        border-radius: 12px;
        overflow: hidden;
        background: radial-gradient(circle at center, #271313 0%, #0f0d0d 100%);
        /* Red Glow Radial Background */
        box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.6);
    }

    #bus-canvas {
        display: block;
        width: 100%;
        height: 100%;
        outline: none;
        cursor: pointer;
    }

    /* Loading & Fallbacks */
    .canvas-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #141111;
        /* Dark Red tint black */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 10;
        color: #ffffff;
        gap: 15px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(229, 9, 20, 0.1);
        border-top: 4px solid var(--accent-color, #e50914);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .fallback-view {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, #271313 0%, #0f0d0d 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 20px;
        z-index: 8;
    }

    .fallback-view.hidden {
        display: none;
    }

    .fallback-icon {
        font-size: 4rem;
        margin-bottom: 15px;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .fallback-view h3 {
        color: var(--accent-color, #e50914);
        margin-bottom: 8px;
        font-size: 1.5rem;
    }

    .fallback-view p {
        color: #b0b0b0;
        max-width: 320px;
    }

    @media (min-width: 992px) {
        #canvas-container {
            height: 520px;
        }
    }
</style>

<section class="eco-bus-section">
    <div class="eco-bus-header">
        <h2>Nos Bus <span>Viking Transport</span></h2>
        <p>Viking Transport s'engage à préserver les routes et paysages de Normandie en ayant une conduite adaptée et respectueuse de l'environnement.</p>
    </div>

    <div class="eco-bus-container">
        <div class="eco-bus-viewer-card">
            <div id="canvas-container">
                <canvas id="bus-canvas"></canvas>

                <div id="canvas-loader" class="canvas-loader">
                    <div class="spinner"></div>
                    <p style="font-weight: 500; font-size: 0.95rem;">Chargement du bus 3D...</p>
                </div>

                <div id="fallback-view" class="fallback-view hidden">
                    <div class="fallback-icon">🚌</div>
                    <h3>Technologie Viking Rouge</h3>
                    <p>Nos bus sont propulsés à 100% par de l'électricité et de l'hydrogène décarboné, garantissant un voyage éco-responsable à travers toute la Normandie.</p>
                </div>
            </div>
            <h2>Technologie <span>VIKING ROUGE</span></h2>
            <p>Nos bus sont propulsés à 100% par de l'électricité et de l'hydrogène décarboné produit via électrolyse de l'eau, garantissant un voyage éco-responsable à travers toute la Normandie.</p>
            <p>Grâce à notre flotte de bus électriques et à hydrogène, nous réduisons considérablement les émissions de CO2, tout en offrant un confort optimal et une expérience de voyage silencieuse et agréable.</p>
        </div>
    </div>
</section>

<!-- Inclusion des scripts nécessaires pour la 3D et les animations -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('canvas-container');
        const canvas = document.getElementById('bus-canvas');
        const loaderEl = document.getElementById('canvas-loader');
        const fallbackEl = document.getElementById('fallback-view');

        let scene, camera, renderer, controls;
        let busModel = null;
        let wheels = [];
        let particleSystem;
        let particleGeometry;
        let motorActive = false;
        let animationTime = 0;
        let bumpTimeline = null;
        let activeBirds = [];
        let activeFeathers = [];

        const raycaster = new THREE.Raycaster();
        const mouse = new THREE.Vector2();

        let particleSpeedMultiplier = 1.0;
        let wheelRotationSpeed = 0.0;
        const particleCount = 70;
        const particleSpeeds = [];

        let ambientLight, dirLight, ecoUnderglow, rimLight;

        function hasWebGL() {
            try {
                const canvas = document.createElement('canvas');
                return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
            } catch (e) {
                return false;
            }
        }

        if (!hasWebGL()) {
            showFallback();
            return;
        }

        function init() {
            scene = new THREE.Scene();
            scene.fog = new THREE.FogExp2(0x0d0f0d, 0.015);

            camera = new THREE.PerspectiveCamera(40, container.clientWidth / container.clientHeight, 0.1, 100);
            camera.position.set(5.5, 2.0, 4.0);

            renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                antialias: true,
                alpha: true
            });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.0;

            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.minDistance = 3.0;
            controls.maxDistance = 10.0;
            controls.maxPolarAngle = Math.PI / 2 - 0.05;
            controls.target.set(0.0, 0.7, 1.5);

            setupLighting();
            setupParticles();
            loadModel();
            animate();

            window.addEventListener('resize', onWindowResize);
            container.addEventListener('click', onCanvasClick);
        }

        function setupLighting() {
            ambientLight = new THREE.AmbientLight(0xd1e8ff, 1.1);
            scene.add(ambientLight);

            dirLight = new THREE.DirectionalLight(0xffffff, 2.0);
            dirLight.position.set(6, 10, 4);
            dirLight.castShadow = true;
            scene.add(dirLight);

            rimLight = new THREE.DirectionalLight(0xff3b30, 0.8);
            rimLight.position.set(-6, 2, -6);
            scene.add(rimLight);

            ecoUnderglow = new THREE.PointLight(0xe50914, 0.0, 6.0);
            ecoUnderglow.position.set(0, -0.4, 0);
            scene.add(ecoUnderglow);

            const floorGeo = new THREE.PlaneGeometry(30, 30);
            const floorMat = new THREE.ShadowMaterial({
                opacity: 0.4
            });
            const floor = new THREE.Mesh(floorGeo, floorMat);
            floor.rotation.x = -Math.PI / 2;
            floor.position.y = -0.5;
            floor.receiveShadow = true;
            scene.add(floor);
        }

        function setupParticles() {
            particleGeometry = new THREE.BufferGeometry();
            const positions = new Float32Array(particleCount * 2 * 3);

            for (let i = 0; i < particleCount; i++) {
                const x = (Math.random() + Math.random() - 1.0) * 10.0;
                const y = Math.random() * 6.5 - 0.5;
                const z = (Math.random() - 0.5) * 24.0;
                const streakLength = Math.random() * 0.4 + 0.3;

                positions[i * 6] = x;
                positions[i * 6 + 1] = y;
                positions[i * 6 + 2] = z;
                positions[i * 6 + 3] = x;
                positions[i * 6 + 4] = y;
                positions[i * 6 + 5] = z - streakLength;

                particleSpeeds.push(Math.random() * 0.04 + 0.02);
            }

            particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            const lineMaterial = new THREE.LineBasicMaterial({
                color: 0x666666,
                transparent: true,
                opacity: 0.15,
                blending: THREE.AdditiveBlending
            });

            particleSystem = new THREE.LineSegments(particleGeometry, lineMaterial);
            scene.add(particleSystem);
        }

        function spawnBird() {
            const bird = new THREE.Group();
            bird.rotation.y = Math.PI;

            const body = new THREE.Mesh(new THREE.ConeGeometry(0.12, 0.45, 4), new THREE.MeshBasicMaterial({
                color: 0xeeeeee
            }));
            body.rotation.x = Math.PI / 2;
            bird.add(body);

            const beak = new THREE.Mesh(new THREE.ConeGeometry(0.04, 0.18, 4), new THREE.MeshBasicMaterial({
                color: 0xff9500
            }));
            beak.position.set(0, 0, 0.3);
            beak.rotation.x = Math.PI / 2;
            bird.add(beak);

            const wingL = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.02, 0.18), new THREE.MeshBasicMaterial({
                color: 0xcccccc
            }));
            wingL.position.set(-0.25, 0, 0);
            bird.add(wingL);

            const wingR = wingL.clone();
            wingR.position.set(0.25, 0, 0);
            bird.add(wingR);

            const randY = 0.2 + Math.random() * 1.2;
            bird.position.set((Math.random() - 0.5) * 0.8, randY, 12.0);
            scene.add(bird);

            activeBirds.push({
                mesh: bird,
                wings: [wingL, wingR],
                active: true,
                speed: 0.22 + Math.random() * 0.06
            });
        }

        function triggerFeathersBurst(x, y, z) {
            const burstFeatherCount = 15;
            const geom = new THREE.BufferGeometry();
            const positions = new Float32Array(burstFeatherCount * 3);
            const velocities = [];

            for (let i = 0; i < burstFeatherCount; i++) {
                positions[i * 3] = x;
                positions[i * 3 + 1] = y;
                positions[i * 3 + 2] = z;
                velocities.push(new THREE.Vector3((Math.random() - 0.5) * 0.12, (Math.random() - 0.1) * 0.08 + 0.04, (Math.random() * 0.06) + 0.04));
            }

            geom.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            const mat = new THREE.PointsMaterial({
                color: 0xeeeeee,
                size: 0.22,
                transparent: true,
                opacity: 1.0
            });
            const system = new THREE.Points(geom, mat);
            scene.add(system);

            gsap.to(mat, {
                opacity: 0.0,
                duration: 1.2,
                delay: 0.4,
                onComplete: () => {
                    scene.remove(system);
                    geom.dispose();
                    mat.dispose();
                }
            });
            activeFeathers.push({
                system,
                geometry: geom,
                velocities,
                count: burstFeatherCount
            });
        }

        function loadModel() {
            new THREE.GLTFLoader().load('/Bus.glb', (gltf) => {
                const loadedScene = gltf.scene;
                const box = new THREE.Box3().setFromObject(loadedScene);
                const center = box.getCenter(new THREE.Vector3());

                loadedScene.position.set(-center.x, -box.min.y, -center.z);
                busModel = new THREE.Group();
                busModel.add(loadedScene);
                busModel.position.set(0, -0.5, 0);

                loadedScene.traverse((child) => {
                    if (child.isMesh) {
                        child.castShadow = child.receiveShadow = true;
                        const name = child.name.toLowerCase();
                        if (name.includes('wheel') || name.includes('roue') || name.includes('tire')) wheels.push(child);
                    }
                });

                scene.add(busModel);
                gsap.to(loaderEl, {
                    opacity: 0,
                    duration: 0.5,
                    onComplete: () => loaderEl.style.display = 'none'
                });

                isSquashing = true;
                const startY = busModel.position.y;
                busModel.position.y += 4;
                gsap.to(busModel.position, {
                    y: startY,
                    duration: 0.7,
                    ease: 'power2.in',
                    onComplete: () => {
                        isSquashing = false;
                        playLandingAnimation();
                    }
                });
            }, undefined, (err) => {
                console.error(err);
                showFallback();
            });
        }

        function showFallback() {
            loaderEl.style.display = 'none';
            fallbackEl.classList.remove('hidden');
            canvas.style.display = 'none';
        }

        function onWindowResize() {
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            if (controls) controls.update();
            animationTime += 0.12;

            if (busModel) busModel.rotation.y = -(Math.PI * 0.5);

            if (wheels.length > 0 && wheelRotationSpeed > 0) {
                wheels.forEach(w => w.rotation.x -= wheelRotationSpeed);
            }

            if (motorActive && busModel && !isSquashing) {
                busModel.rotation.x = Math.cos(animationTime * 1.5) * 0.012;
                if (!(bumpTimeline && bumpTimeline.isActive())) {
                    busModel.position.y = -0.5 + Math.sin(animationTime * 1.8) * 0.035;
                    busModel.rotation.z = Math.sin(animationTime * 2.2) * 0.008;
                }
                if (Math.random() < 0.003) triggerRoadBump();
            }

            if (particleSystem) {
                const pos = particleGeometry.attributes.position.array;
                for (let i = 0; i < particleCount; i++) {
                    const s = particleSpeeds[i] * particleSpeedMultiplier;
                    pos[i * 6 + 2] -= s;
                    pos[i * 6 + 5] -= s;
                    const zA = pos[i * 6 + 2];
                    if (zA > -1.8 && zA < 1.8) {
                        const xA = pos[i * 6],
                            yA = pos[i * 6 + 1];
                        if (Math.abs(xA) < 0.85 && yA < 0.85) {
                            if (yA > 0.35) {
                                pos[i * 6 + 1] = pos[i * 6 + 4] = 0.88;
                            } else {
                                const dX = xA >= 0 ? 0.88 : -0.88;
                                pos[i * 6] = pos[i * 6 + 3] = dX;
                            }
                        }
                    }
                    if (pos[i * 6 + 2] < -12.0) {
                        const nX = (Math.random() - 0.5) * 20,
                            nY = Math.random() * 6.5 - 0.5,
                            nZ = 12,
                            sL = Math.random() * 0.4 + 0.3;
                        pos[i * 6] = nX;
                        pos[i * 6 + 1] = nY;
                        pos[i * 6 + 2] = nZ;
                        pos[i * 6 + 3] = nX;
                        pos[i * 6 + 4] = nY;
                        pos[i * 6 + 5] = nZ - sL;
                    }
                }
                particleGeometry.attributes.position.needsUpdate = true;
            }

            for (let k = activeBirds.length - 1; k >= 0; k--) {
                const b = activeBirds[k];
                if (!b.active) continue;
                b.mesh.position.z -= b.speed;
                b.wings[0].rotation.z = Math.sin(animationTime * 2) * 0.6;
                b.wings[1].rotation.z = -Math.sin(animationTime * 2) * 0.6;

                let coll = false;
                if (busModel) {
                    const r = new THREE.Raycaster(b.mesh.position.clone(), new THREE.Vector3(0, 0, -1), 0, b.speed + 0.03);
                    const ints = r.intersectObject(busModel, true);
                    if (ints.length > 0) {
                        coll = true;
                        b.mesh.position.copy(ints[0].point);
                        b.mesh.position.z += 0.12;
                    }
                }

                if (coll) {
                    b.active = false;
                    triggerFeathersBurst(b.mesh.position.x, b.mesh.position.y, b.mesh.position.z);
                    if (busModel && !isSquashing) {
                        gsap.timeline().to(busModel.rotation, {
                            z: 0.04,
                            duration: 0.08
                        }).to(busModel.rotation, {
                            z: 0,
                            duration: 0.5,
                            ease: 'elastic.out'
                        });
                    }
                    const bMesh = b.mesh;
                    gsap.timeline().to(bMesh.scale, {
                            x: 1.3,
                            y: 1.3,
                            z: 0.2,
                            duration: 0.05
                        })
                        .to(bMesh.position, {
                            x: bMesh.position.x >= 0 ? 4 : -4,
                            z: bMesh.position.z + 0.35,
                            duration: 1.2,
                            ease: 'power2.in',
                            delay: 0.65
                        })
                        .to(bMesh.rotation, {
                            z: Math.PI * 2,
                            duration: 1.2,
                            ease: 'power2.in'
                        }, "-=1.2")
                        .to(bMesh.position, {
                            z: -25.0,
                            duration: 0.9,
                            ease: 'power2.in'
                        }, "-=0.7")
                        .call(() => scene.remove(bMesh));
                    activeBirds.splice(k, 1);
                } else if (b.mesh.position.z < -12.0) {
                    scene.remove(b.mesh);
                    activeBirds.splice(k, 1);
                }
            }

            for (let j = activeFeathers.length - 1; j >= 0; j--) {
                const f = activeFeathers[j];
                if (f.system.material.opacity <= 0) {
                    activeFeathers.splice(j, 1);
                    continue;
                }
                const p = f.geometry.attributes.position.array;
                for (let i = 0; i < f.count; i++) {
                    p[i * 3] += f.velocities[i].x;
                    p[i * 3 + 1] += f.velocities[i].y;
                    p[i * 3 + 2] += f.velocities[i].z;
                    f.velocities[i].y -= 0.0025;
                }
                f.geometry.attributes.position.needsUpdate = true;
            }

            renderer.render(scene, camera);
        }

        let isSquashing = false;

        function playLandingAnimation() {
            if (!busModel) return;
            isSquashing = true;
            gsap.timeline({
                    onComplete: () => {
                        isSquashing = false;
                        motorActive = true;
                        startMotorAnimation();
                    }
                })
                .to(busModel.scale, {
                    x: 1.18,
                    y: 0.65,
                    z: 1.18,
                    duration: 0.15
                })
                .to(busModel.scale, {
                    x: 0.92,
                    y: 1.15,
                    z: 0.92,
                    duration: 0.15
                })
                .to(busModel.scale, {
                    x: 1,
                    y: 1,
                    z: 1,
                    duration: 0.4,
                    ease: 'elastic.out'
                });
        }

        function triggerRoadBump() {
            if (!motorActive || !busModel || isSquashing || (bumpTimeline && bumpTimeline.isActive())) return;
            bumpTimeline = gsap.timeline();
            bumpTimeline.to(busModel.position, {
                    y: -0.32,
                    duration: 0.15
                }).to(busModel.rotation, {
                    z: 0.06,
                    duration: 0.15
                }, 0)
                .to(busModel.position, {
                    y: -0.5,
                    duration: 0.12
                }).to(busModel.rotation, {
                    z: -0.08,
                    duration: 0.12
                }, 0.15)
                .to(busModel.scale, {
                    x: 1.08,
                    y: 0.86,
                    z: 1.08,
                    duration: 0.08
                }).to(busModel.rotation, {
                    z: 0,
                    duration: 0.15
                })
                .to(busModel.scale, {
                    x: 1,
                    y: 1,
                    z: 1,
                    duration: 0.35,
                    ease: 'elastic.out'
                });
        }

        function startMotorAnimation() {
            if (!busModel) return;
            isSquashing = true;
            gsap.timeline({
                    onComplete: () => isSquashing = false
                })
                .to(busModel.rotation, {
                    z: 0.15,
                    duration: 0.25
                })
                .to(busModel.scale, {
                    x: 1.25,
                    y: 0.55,
                    z: 1.25,
                    duration: 0.25
                }, 0)
                .to(busModel.rotation, {
                    z: -0.05,
                    duration: 0.2
                })
                .to(busModel.scale, {
                    x: 0.85,
                    y: 1.28,
                    z: 0.85,
                    duration: 0.2
                }, "-=0.2")
                .to(busModel.rotation, {
                    z: 0,
                    duration: 0.4,
                    ease: 'elastic.out'
                })
                .to(busModel.scale, {
                    x: 1,
                    y: 1,
                    z: 1,
                    duration: 0.6,
                    ease: 'elastic.out'
                }, "-=0.4");

            gsap.to({
                v: 1
            }, {
                v: 4.5,
                duration: 1.5,
                onUpdate: function() {
                    particleSpeedMultiplier = this.targets()[0].v;
                }
            });
            gsap.to({
                v: 0
            }, {
                v: 0.18,
                duration: 1.8,
                onUpdate: function() {
                    wheelRotationSpeed = this.targets()[0].v;
                }
            });
            gsap.to(ecoUnderglow, {
                intensity: 4,
                duration: 1
            });
        }

        function onCanvasClick(e) {
            const r = container.getBoundingClientRect();
            mouse.x = ((e.clientX - r.left) / r.width) * 2 - 1;
            mouse.y = -((e.clientY - r.top) / r.height) * 2 + 1;
            if (busModel) {
                raycaster.setFromCamera(mouse, camera);
                if (raycaster.intersectObject(busModel, true).length > 0) spawnBird();
            }
        }

        init();
    });
</script>

<?php
require 'includes/footer.php';
?>