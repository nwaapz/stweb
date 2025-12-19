
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    // Configuration
    const CONFIG = {
        containerId: 'iran-3d-map-container',
        jsonPath: 'json/iran_provinces_v2.json',
        colors: {
            // Updated to be more vibrant and premium (No more simple green)
            // Using a tech/cyber palette: deep blue/purple base with cyan/magenta highlights
            base: 0x000000,       // Black
            hover: 0xff0055,      // Vibrant Magenta/Red for hover
            outline: 0x00ffff,    // Cyan/Neon Blue for borders
            background: 0xf5f5f5, // Light grey background to match site clean look (or we can go dark)
            light: 0xffffff       // White light
        },
        camera: {
            fov: 50,
            posX: 0,
            posY: 0,
            posZ: 700, // Distance - zoomed way out for complete overview
        },
        extrusion: {
            depth: 0.5,  // Much flatter for easier viewing
            bevelEnabled: false,
            bevelThickness: 0.1,
            bevelSize: 0.1,
            bevelSegments: 1
        }
    };

    const container = document.getElementById(CONFIG.containerId);
    if (!container) return; // Exit if container not found

    // Setup Dimensions
    let width = container.clientWidth;
    let height = container.clientHeight || 500; // Default height if 0
    if (height < 300) height = 500; // Minimum height
    container.style.height = height + 'px'; // Enforce height

    // --- Three.js Setup ---
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(CONFIG.colors.background);

    const camera = new THREE.PerspectiveCamera(CONFIG.camera.fov, width / height, 0.1, 1000);
    camera.position.set(CONFIG.camera.posX, CONFIG.camera.posY, CONFIG.camera.posZ);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // --- Lighting ---
    const ambientLight = new THREE.AmbientLight(CONFIG.colors.light, 0.6);
    scene.add(ambientLight);

    const directionalLight = new THREE.DirectionalLight(CONFIG.colors.light, 0.8);
    directionalLight.position.set(100, 100, 100);
    scene.add(directionalLight);

    const pointLight = new THREE.PointLight(CONFIG.colors.hover, 0.5); // Accents
    pointLight.position.set(0, 0, 50);
    scene.add(pointLight);

    // --- Group for Map ---
    const mapGroup = new THREE.Group();
    // Center the map group initially (rough adjustment, will fine-tune with bounding box)
    // Iran coords are approx Long 44-63, Lat 25-40. Center roughly ~53, ~32.
    // D3 projection will center it at 0,0 anyway.
    scene.add(mapGroup);

    // --- Tooltip ---
    const tooltip = document.createElement('div');
    tooltip.style.position = 'absolute';
    tooltip.style.padding = '12px 16px';
    tooltip.style.backgroundColor = 'rgba(0, 0, 0, 0.85)';
    tooltip.style.color = '#fff';
    tooltip.style.borderRadius = '6px';
    tooltip.style.pointerEvents = 'none'; // Click through
    tooltip.style.opacity = '0';
    tooltip.style.transition = 'opacity 0.2s';
    tooltip.style.fontFamily = 'inherit'; // Inherit site font
    tooltip.style.zIndex = '100';
    tooltip.style.textAlign = 'right'; // RTL support
    tooltip.style.direction = 'rtl';
    tooltip.style.boxShadow = '0 4px 15px rgba(0,0,0,0.3)';
    tooltip.style.backdropFilter = 'blur(4px)';
    tooltip.style.fontSize = '14px';
    tooltip.innerHTML = '<strong>استان</strong><br><span style="font-size:12px; color:#aaa">آدرس شعبه...</span>';
    container.appendChild(tooltip);

    // --- Load Data & Build Map ---
    const fileLoader = new THREE.FileLoader();
    fileLoader.load(CONFIG.jsonPath, (data) => {
        let geojson;
        try {
            geojson = JSON.parse(data);
        } catch (e) {
            console.error("Failed to parse GeoJSON", e);
            return;
        }

        // Use D3 to project coordinates to a flat plane (Mercator)
        // Adjust center and scale to fit Iran
        const projection = d3.geoMercator()
            .center([54, 32]) // Approx Center of Iran
            .scale(1800)      // Scale factor - adjusted for better fit
            .translate([0, 0]);

        const path = d3.geoPath().projection(projection);

        geojson.features.forEach(feature => {
            // Use Persian name (name property) instead of English (name:en)
            const provinceName = feature.properties.name || feature.properties['name:en'] || feature.name || "Unknown";
            const coordinates = feature.geometry.coordinates;
            const type = feature.geometry.type;

            const shapes = [];

            // Helper to parse rings
            const parsePolygon = (rings) => {
                // Handle the case where rings could be the outer ring directly
                if (!Array.isArray(rings[0])) {
                    // Single ring as array of points
                    const shape = new THREE.Shape();
                    rings.forEach((point, i) => {
                        const projected = projection(point);
                        if (projected && !isNaN(projected[0]) && !isNaN(projected[1])) {
                            const [x, y] = projected;
                            if (i === 0) {
                                shape.moveTo(x, -y);
                            } else {
                                shape.lineTo(x, -y);
                            }
                        }
                    });
                    if (shape.curves.length > 0) shapes.push(shape);
                    return;
                }

                rings.forEach(ring => {
                    const shape = new THREE.Shape();
                    ring.forEach((point, i) => {
                        const projected = projection(point);
                        if (projected && !isNaN(projected[0]) && !isNaN(projected[1])) {
                            const [x, y] = projected;
                            if (i === 0) {
                                shape.moveTo(x, -y);
                            } else {
                                shape.lineTo(x, -y);
                            }
                        }
                    });
                    if (shape.curves.length > 0) shapes.push(shape);
                });
            };

            if (type === 'Polygon') {
                parsePolygon(coordinates);
            } else if (type === 'MultiPolygon') {
                coordinates.forEach(poly => {
                    parsePolygon(poly);
                });
            }

            if (shapes.length === 0) {
                console.warn("No valid shapes for", provinceName);
                return;
            }

            // Extrude Shapes to 3D
            const geometry = new THREE.ExtrudeGeometry(shapes, CONFIG.extrusion);

            // Material
            const material = new THREE.MeshPhongMaterial({
                color: CONFIG.colors.base,
                specular: 0x111111,
                shininess: 30,
                side: THREE.DoubleSide
            });

            const mesh = new THREE.Mesh(geometry, material);
            mesh.userData = {
                name: provinceName,
                // Add fake address data for demo
                address: `شعبه مرکزی ${provinceName}<br>خیابان اصلی، پلاک ۱۱۰`
            };

            // Add Border/Edge for better visibility
            const edges = new THREE.EdgesGeometry(geometry);
            const line = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: CONFIG.colors.outline }));
            mesh.add(line);

            mapGroup.add(mesh);
        });

        // Center map in the scene properly
        const box = new THREE.Box3().setFromObject(mapGroup);
        const center = box.getCenter(new THREE.Vector3());
        mapGroup.position.sub(center); // Move group so center is at 0,0,0

    }, undefined, (err) => {
        console.error('Error loading GeoJSON:', err);
    });

    // --- Interaction (Raycaster) ---
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    let hoveredObject = null;
    let selectedObject = null;

    // Track current camera lookAt target for smooth transitions
    let currentLookAtTarget = { x: 0, y: 0, z: 0 };

    const onMouseMove = (event) => {
        // Calculate mouse position in normalized device coordinates (-1 to +1)
        const rect = renderer.domElement.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

        // Tooltip pos
        // Offset a bit so cursor doesn't cover it
        const tooltipX = event.clientX - rect.left + 15;
        const tooltipY = event.clientY - rect.top + 15;

        // Keep bounds
        tooltip.style.left = tooltipX + 'px';
        tooltip.style.top = tooltipY + 'px';

        checkIntersection();
    };

    const onClick = (event) => {
        if (hoveredObject) {
            // Handle Selection Visuals
            if (selectedObject && selectedObject !== hoveredObject) {
                // Reset previous selection
                selectedObject.material.color.setHex(CONFIG.colors.base);
            }

            selectedObject = hoveredObject;
            selectedObject.material.color.setHex(CONFIG.colors.hover); // Keep it highlighted

            const provinceName = selectedObject.userData.name;
            const provinceAddress = selectedObject.userData.address;

            // Calculate bounding box of the selected province
            const box = new THREE.Box3().setFromObject(selectedObject);
            const center = box.getCenter(new THREE.Vector3());
            const size = box.getSize(new THREE.Vector3());

            // Calculate appropriate camera distance based on province size
            const maxDim = Math.max(size.x, size.y, size.z);
            const fov = camera.fov * (Math.PI / 180);
            let cameraZ = Math.abs(maxDim / Math.tan(fov / 2)) * 1.5;

            // Clamp camera distance
            cameraZ = Math.max(150, Math.min(cameraZ, 500));

            // Target camera position
            const targetPosition = {
                x: center.x,
                y: camera.position.y,
                z: cameraZ
            };

            // Smooth camera animation
            const startPosition = {
                x: camera.position.x,
                y: camera.position.y,
                z: camera.position.z
            };

            const duration = 1000; // 1 second
            const startTime = Date.now();

            // Use current camera lookAt target as starting point
            const startLookAt = {
                x: currentLookAtTarget.x,
                y: currentLookAtTarget.y,
                z: currentLookAtTarget.z
            };

            const animateCamera = () => {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (ease-in-out)
                const eased = progress < 0.5
                    ? 2 * progress * progress
                    : -1 + (4 - 2 * progress) * progress;

                camera.position.x = startPosition.x + (targetPosition.x - startPosition.x) * eased;
                camera.position.y = startPosition.y + (targetPosition.y - startPosition.y) * eased;
                camera.position.z = startPosition.z + (targetPosition.z - startPosition.z) * eased;

                // Smoothly interpolate the lookAt target
                const lookAtX = startLookAt.x + (center.x - startLookAt.x) * eased;
                const lookAtY = startLookAt.y + (center.y - startLookAt.y) * eased;
                const lookAtZ = startLookAt.z + (0 - startLookAt.z) * eased;

                camera.lookAt(lookAtX, lookAtY, lookAtZ);

                if (progress < 1) {
                    requestAnimationFrame(animateCamera);
                } else {
                    // Update current lookAt target when animation completes
                    currentLookAtTarget.x = center.x;
                    currentLookAtTarget.y = center.y;
                    currentLookAtTarget.z = 0;
                }
            };

            animateCamera();

            // Remove existing notification if any
            const existingNote = document.getElementById('map-notification');
            if (existingNote) existingNote.remove();

            // Show a styled notification/alert
            const notification = document.createElement('div');
            notification.id = 'map-notification';
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.left = '20px';
            notification.style.transform = 'none';
            notification.style.backgroundColor = 'rgba(20, 20, 35, 0.95)';
            notification.style.color = '#fff';
            notification.style.padding = '30px 40px';
            notification.style.borderRadius = '16px';
            notification.style.boxShadow = '0 20px 50px rgba(0,0,0,0.6)';
            notification.style.border = '1px solid rgba(255, 255, 255, 0.1)';
            notification.style.zIndex = '10000';
            notification.style.textAlign = 'center';
            notification.style.direction = 'rtl';
            notification.style.minWidth = '320px';
            notification.style.maxWidth = '90%';
            notification.style.fontFamily = 'inherit';
            notification.style.animation = 'fadeIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)'; // Bouncy pop in
            notification.style.backdropFilter = 'blur(10px)';

            notification.innerHTML = `
                <h3 style="margin: 0 0 10px 0; font-size: 26px; font-weight:700; color: #ff0055;">${provinceName}</h3>
                <div style="width: 50px; height: 3px; background: #ff0055; margin: 0 auto 20px auto; border-radius: 2px;"></div>
                <div style="font-size: 16px; line-height: 1.8; margin-bottom: 25px; color: #e0e0e0;">${provinceAddress}</div>
                <button id="closeNotification" style="background: linear-gradient(45deg, #ff0055, #ff4081); color: white; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(255, 0, 85, 0.3); margin-left: 10px;">متوجه شدم</button>
                <button id="resetView" style="background: linear-gradient(45deg, #00ffff, #00cccc); color: #000; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(0, 255, 255, 0.3);">بازگشت به نمای کلی</button>
            `;

            document.body.appendChild(notification);

            // Close button handler
            const closeBtn = document.getElementById('closeNotification');
            closeBtn.addEventListener('mouseenter', () => closeBtn.style.transform = 'scale(1.05)');
            closeBtn.addEventListener('mouseleave', () => closeBtn.style.transform = 'scale(1)');

            // Reset view button handler
            const resetBtn = document.getElementById('resetView');
            resetBtn.addEventListener('mouseenter', () => resetBtn.style.transform = 'scale(1.05)');
            resetBtn.addEventListener('mouseleave', () => resetBtn.style.transform = 'scale(1)');

            resetBtn.addEventListener('click', () => {
                // Animate camera back to original position
                const resetDuration = 1000;
                const resetStartTime = Date.now();
                const currentPos = {
                    x: camera.position.x,
                    y: camera.position.y,
                    z: camera.position.z
                };

                // Store current lookAt target (the province center)
                const currentLookAt = {
                    x: center.x,
                    y: center.y,
                    z: 0
                };

                const targetLookAt = {
                    x: 0,
                    y: 0,
                    z: 0
                };

                const resetCamera = () => {
                    const elapsed = Date.now() - resetStartTime;
                    const progress = Math.min(elapsed / resetDuration, 1);
                    const eased = progress < 0.5
                        ? 2 * progress * progress
                        : -1 + (4 - 2 * progress) * progress;

                    camera.position.x = currentPos.x + (CONFIG.camera.posX - currentPos.x) * eased;
                    camera.position.y = currentPos.y + (CONFIG.camera.posY - currentPos.y) * eased;
                    camera.position.z = currentPos.z + (CONFIG.camera.posZ - currentPos.z) * eased;

                    // Smoothly interpolate the lookAt target
                    const lookAtX = currentLookAt.x + (targetLookAt.x - currentLookAt.x) * eased;
                    const lookAtY = currentLookAt.y + (targetLookAt.y - currentLookAt.y) * eased;
                    const lookAtZ = currentLookAt.z + (targetLookAt.z - currentLookAt.z) * eased;

                    camera.lookAt(lookAtX, lookAtY, lookAtZ);

                    if (progress < 1) {
                        requestAnimationFrame(resetCamera);
                    } else {
                        // Update current lookAt target when reset completes
                        currentLookAtTarget.x = 0;
                        currentLookAtTarget.y = 0;
                        currentLookAtTarget.z = 0;
                    }
                };

                resetCamera();

                // Deselect province
                if (selectedObject) {
                    selectedObject.material.color.setHex(CONFIG.colors.base);
                    selectedObject = null;
                }

                // Close notification
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -50%) scale(0.8)';
                notification.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) notification.parentNode.removeChild(notification);
                }, 300);
            });

            const closeNotification = () => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -50%) scale(0.8)';
                notification.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) notification.parentNode.removeChild(notification);
                }, 300);

                // Optional: clear selection on close? 
                // Let's keep selection to show what was clicked.
            };

            closeBtn.addEventListener('click', closeNotification);

            // Click outside to close
            setTimeout(() => {
                const closeOnOutside = (e) => {
                    if (notification && !notification.contains(e.target) && e.target.id !== 'iran-3d-map-canvas') { // Don't close if clicking map again quickly
                        // Check if notification is still in DOM
                        if (document.body.contains(notification)) {
                            closeNotification();
                        }
                        document.removeEventListener('click', closeOnOutside);
                    }
                };
                document.addEventListener('click', closeOnOutside);
            }, 100);

            console.log("Clicked:", provinceName);
        }
    };

    const checkIntersection = () => {
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(mapGroup.children);

        if (intersects.length > 0) {
            const object = intersects[0].object;
            if (hoveredObject !== object) {
                // Reset old hovered object if it's NOT the selected one
                if (hoveredObject && hoveredObject !== selectedObject) {
                    hoveredObject.material.color.setHex(CONFIG.colors.base);
                }

                hoveredObject = object;
                // Highlight new hovered object even if selected (or maybe brighter?)
                // Simple logic: Hover always highlights.
                hoveredObject.material.color.setHex(CONFIG.colors.hover);

                // Update Tooltip
                tooltip.innerHTML = `
                    <div style="font-size:16px; font-weight:700; margin-bottom:4px; color:${'#fff'}">${hoveredObject.userData.name}</div>
                    <div style="font-size:13px; color:#ddd; direction:rtl; text-align:right;">برای جزئیات کلیک کنید</div>
                `;
                tooltip.style.opacity = '1';
                document.body.style.cursor = 'pointer';
            }
        } else {
            if (hoveredObject) {
                // Reset hovered object logic
                if (hoveredObject !== selectedObject) {
                    hoveredObject.material.color.setHex(CONFIG.colors.base);
                } else {
                    // If it was selected, ensure it stays selected color (red/magenta)
                    // It likely already is, but good to be safe.
                    hoveredObject.material.color.setHex(CONFIG.colors.hover);
                }

                hoveredObject = null;
                tooltip.style.opacity = '0';
                document.body.style.cursor = 'default';
            }
        }
    };

    // --- Events ---
    window.addEventListener('resize', () => {
        width = container.clientWidth;
        // height = container.clientHeight; // Keep fixed height mostly
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    });

    container.addEventListener('mousemove', onMouseMove);
    container.addEventListener('click', onClick);

    // --- Animation Loop ---
    let frameId;
    let time = 0;
    const animate = () => {
        frameId = requestAnimationFrame(animate);

        // Subtle tilting animation on Y-axis (left-right)
        time += 0.005;
        mapGroup.rotation.y = Math.sin(time) * 0.1; // Oscillates between -0.1 and +0.1 radians

        renderer.render(scene, camera);
    };

    animate();
});
