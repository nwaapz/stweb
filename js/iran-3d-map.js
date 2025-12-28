
// Initialize map - handle both early and late script loading
(function() {
	// If DOMContentLoaded already fired, run immediately, otherwise wait for it
	const init = () => {
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
            light: 0xffffff,       // White light
            hasBranch: 0x00a86b    // Classy Jade Green for provinces with branches
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
    // Setup Dimensions
    let width = container.clientWidth;
    let height = 600; // Fixed height for map canvas
    container.style.height = 'auto'; // Allow container to grow
    container.style.minHeight = '600px';
    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.position = 'relative';  // Ensure children are positioned relative to this

    // --- Three.js Setup ---
    const scene = new THREE.Scene();


    scene.background = new THREE.Color(CONFIG.colors.background);

    const camera = new THREE.PerspectiveCamera(CONFIG.camera.fov, width / height, 0.1, 1000);
    camera.position.set(CONFIG.camera.posX, CONFIG.camera.posY, CONFIG.camera.posZ);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(window.devicePixelRatio);
    // Make sure renderer canvas doesn't block pointer events on elements above it
    renderer.domElement.style.pointerEvents = 'auto';
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

    // --- Legend ---
    const legend = document.createElement('div');
    legend.style.position = 'absolute';
    legend.style.bottom = '30px';
    legend.style.right = '30px';
    legend.style.backgroundColor = 'rgba(20, 20, 35, 0.85)';
    legend.style.backdropFilter = 'blur(5px)';
    legend.style.padding = '15px 20px';
    legend.style.borderRadius = '12px';
    legend.style.border = '1px solid rgba(255, 255, 255, 0.1)';
    legend.style.color = '#fff';
    legend.style.fontFamily = 'inherit';
    legend.style.fontSize = '14px';
    legend.style.direction = 'rtl';
    legend.style.zIndex = '10';
    legend.style.pointerEvents = 'none'; // Allow clicks to pass through if needed (though bottom corner is usually safe)
    legend.style.boxShadow = '0 5px 20px rgba(0,0,0,0.5)';

    const itemStyle = 'display: flex; align-items: center; margin-bottom: 8px;';
    const circleStyle = (color) => `width: 12px; height: 12px; background-color: ${color}; border-radius: 50%; margin-left: 10px; box-shadow: 0 0 8px ${color}; border: 1px solid rgba(255,255,255,0.2);`;

    legend.innerHTML = `
        <div style="${itemStyle}">
            <div style="${circleStyle('#00a86b')}"></div>
            <span>استان‌های دارای شعبه</span>
        </div>
        <div style="display: flex; align-items: center;">
            <div style="${circleStyle('#000000')}"></div>
            <span>سایر استان‌ها</span>
        </div>
    `;
    container.appendChild(legend);

    // --- Interaction State (Declared early for button use) ---
    let mapClickDisabled = false; // Flag to disable map clicks when over province buttons

    // --- Reset View Function (Reusable) - Defined early for button use ---
    const resetView = () => {
        // Reset Camera Logic
        const resetDuration = 1000;
        const resetStartTime = Date.now();
        const currentPos = { x: camera.position.x, y: camera.position.y, z: camera.position.z };
        const targetLookAt = { x: 0, y: 0, z: 0 };

        const resetCameraLoop = () => {
            const elapsed = Date.now() - resetStartTime;
            const progress = Math.min(elapsed / resetDuration, 1);
            const eased = progress < 0.5 ? 2 * progress * progress : -1 + (4 - 2 * progress) * progress;

            camera.position.x = currentPos.x + (CONFIG.camera.posX - currentPos.x) * eased;
            camera.position.y = currentPos.y + (CONFIG.camera.posY - currentPos.y) * eased;
            camera.position.z = currentPos.z + (CONFIG.camera.posZ - currentPos.z) * eased;
            camera.lookAt(currentLookAtTarget.x * (1 - eased), currentLookAtTarget.y * (1 - eased), 0);

            if (progress < 1) requestAnimationFrame(resetCameraLoop);
            else currentLookAtTarget = { x: 0, y: 0, z: 0 };
        };
        resetCameraLoop();

        if (currentDotGrid) {
            if (currentDotGrid.parent) currentDotGrid.parent.remove(currentDotGrid);
            currentDotGrid.geometry.dispose();
            currentDotGrid.material.dispose();
            currentDotGrid = null;
        }

        if (selectedObject) {
            selectedObject.material.color.setHex(selectedObject.userData.baseColor);
            selectedObject = null;
        }

        // Close notification if open
        const notification = document.getElementById('map-notification');
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            setTimeout(() => { if (notification.parentNode) notification.remove(); }, 300);
        }
    };

    // --- Reset View Button (Always Visible) ---
    const resetViewButton = document.createElement('button');
    resetViewButton.id = 'reset-view-button';
    resetViewButton.innerHTML = '<i class="bi bi-arrow-counterclockwise" style="font-size: 18px;"></i>';
    resetViewButton.title = 'بازگشت به نمای کلی';
    resetViewButton.style.position = 'absolute';
    resetViewButton.style.top = '20px';
    resetViewButton.style.left = '20px';
    resetViewButton.style.width = '50px';
    resetViewButton.style.height = '50px';
    resetViewButton.style.borderRadius = '50%';
    resetViewButton.style.border = 'none';
    resetViewButton.style.background = 'linear-gradient(45deg, #00ffff, #00cccc)';
    resetViewButton.style.color = '#000';
    resetViewButton.style.cursor = 'pointer';
    resetViewButton.style.fontSize = '18px';
    resetViewButton.style.fontWeight = 'bold';
    resetViewButton.style.display = 'flex';
    resetViewButton.style.alignItems = 'center';
    resetViewButton.style.justifyContent = 'center';
    resetViewButton.style.zIndex = '1000';
    resetViewButton.style.boxShadow = '0 5px 20px rgba(0, 255, 255, 0.4)';
    resetViewButton.style.transition = 'all 0.3s ease';
    resetViewButton.style.pointerEvents = 'auto';
    
    // Hover and click handlers
    resetViewButton.onmouseenter = (e) => {
        e.stopPropagation();
        resetViewButton.style.transform = 'scale(1.1) rotate(180deg)';
        resetViewButton.style.boxShadow = '0 8px 25px rgba(0, 255, 255, 0.6)';
        mapClickDisabled = true;
    };
    resetViewButton.onmouseleave = (e) => {
        e.stopPropagation();
        resetViewButton.style.transform = 'scale(1) rotate(0deg)';
        resetViewButton.style.boxShadow = '0 5px 20px rgba(0, 255, 255, 0.4)';
        mapClickDisabled = false;
    };
    resetViewButton.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        resetView();
    };
    resetViewButton.onmousedown = (e) => e.stopPropagation();
    resetViewButton.onmouseup = (e) => e.stopPropagation();
    resetViewButton.onmousemove = (e) => e.stopPropagation();
    
    container.appendChild(resetViewButton);

    // --- Interaction State (Hoisted for Table Access) ---
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    let hoveredObject = null;
    let selectedObject = null;
    let currentLookAtTarget = { x: 0, y: 0, z: 0 };
    let currentDotGrid = null; // Store reference to current dot grid

    // --- Dot Grid VFX ---
    const createDotGrid = (mesh) => {
        if (!mesh || !mesh.userData.shapes) return null;

        if (!mesh.geometry.boundingBox) mesh.geometry.computeBoundingBox();
        const box = mesh.geometry.boundingBox;
        const min = box.min;
        const max = box.max;

        console.log("CreateDotGrid Debug:", { min, max, name: mesh.userData.name });

        const spacing = 2.0; // Gap between dots
        const positions = [];

        // Pre-calculate shape points to avoid calling getPoints() in the loop
        const shapeData = mesh.userData.shapes.map(s => ({
            points: s.getPoints(),
            holes: s.holes && s.holes.length > 0 ? s.holes.map(h => h.getPoints()) : []
        }));

        // Helper function for point in polygon (Ray Casting algo)
        const isPointInPolygon = (p, polygon) => {
            let isInside = false;
            for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
                const xi = polygon[i].x, yi = polygon[i].y;
                const xj = polygon[j].x, yj = polygon[j].y;
                const intersect = ((yi > p.y) !== (yj > p.y)) &&
                    (p.x < (xj - xi) * (p.y - yi) / (yj - yi) + xi);
                if (intersect) isInside = !isInside;
            }
            return isInside;
        };

        // Loop through bounds
        for (let x = min.x; x <= max.x; x += spacing) {
            for (let y = min.y; y <= max.y; y += spacing) {
                const pt = { x: x, y: y };
                let isInside = false;

                for (const data of shapeData) {
                    if (isPointInPolygon(pt, data.points)) {
                        isInside = true;
                        if (data.holes.length > 0) {
                            for (const holePts of data.holes) {
                                if (isPointInPolygon(pt, holePts)) {
                                    isInside = false;
                                    break;
                                }
                            }
                        }
                        if (isInside) break;
                    }
                }

                if (isInside) {
                    // Z slightly above surface
                    positions.push(x, y, CONFIG.extrusion.depth + 0.2);
                }
            }
        }

        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));

        // Add aIsBranch attribute (default 0)
        const isBranch = new Float32Array(positions.length / 3).fill(0.0);
        geometry.setAttribute('aIsBranch', new THREE.BufferAttribute(isBranch, 1));

        // Shader for Scanning Effect
        const vertexShader = `
            uniform float uTime;
            uniform float uMin;
            uniform float uMax;
            uniform float uSize;
            attribute float aIsBranch;
            varying float vOpacity;
            varying float vIsBranch;
            
            void main() {
                vIsBranch = aIsBranch;
                vec3 pos = position;
                
                // Calculate normalized scan position (0 to 1) based on time
                float scanPos = uMin + (uMax - uMin) * fract(uTime * 0.5); 
                
                // Distance from current scan line
                float dist = abs(pos.x - scanPos);
                
                // Wave width very narrow (single column)
                float waveWidth = (uMax - uMin) * 0.02; 
                
                // Gaussian-ish falloff for visibility
                float alpha = exp(- (dist * dist) / (2.0 * waveWidth * waveWidth));
                
                // Visibility Logic
                vOpacity = alpha;
                if (vOpacity < 0.1) vOpacity = 0.0;
                
                // Scale Logic for Branches
                float scale = 1.0;
                if (aIsBranch > 0.5) {
                    // Make pulse visible regardless of wave scanning (optional, but requested "pulsing dot")
                    // But user said "when moving column reaches... it scales"
                    
                    // Wider trigger for scaling
                    float scaleWidth = (uMax - uMin) * 0.15; 
                    if (dist < scaleWidth) {
                        float scaleAlpha = 1.0 - (dist / scaleWidth);
                        scaleAlpha = clamp(scaleAlpha, 0.0, 1.0);
                        scale += 3.0 * sin(scaleAlpha * 3.14159);
                        
                        // Ensure it's visible during pulse even if slightly outside narrow beam
                        if (vOpacity < 0.5) vOpacity = 0.5 * scaleAlpha; 
                    }
                }
                
                gl_PointSize = uSize * scale;
                gl_Position = projectionMatrix * modelViewMatrix * vec4(pos, 1.0);
            }
        `;

        const fragmentShader = `
            uniform vec3 uColor;
            varying float vOpacity;
            varying float vIsBranch;
            
            void main() {
                // Circular particle
                vec2 coord = gl_PointCoord - vec2(0.5);
                if(length(coord) > 0.5) discard;
                
                vec3 finalColor = uColor;
                if (vIsBranch > 0.5) {
                    finalColor = vec3(0.0, 1.0, 0.0); // GREEN
                }
                
                gl_FragColor = vec4(finalColor, vOpacity);
            }
        `;

        const material = new THREE.ShaderMaterial({
            uniforms: {
                uTime: { value: 0 },
                uMin: { value: min.x },
                uMax: { value: max.x },
                uSize: { value: 3.5 * window.devicePixelRatio }, // Adjust size for density
                uColor: { value: new THREE.Color(0x000000) }
            },
            vertexShader: vertexShader,
            fragmentShader: fragmentShader,
            transparent: true,
            depthTest: false // Ensure always visible
        });

        const dots = new THREE.Points(geometry, material);
        dots.renderOrder = 999;
        dots.raycast = () => { };
        return dots;
    };


    // --- Selection Logic ---
    const selectProvince = (object) => {
        if (!object) return;

        // Handle Selection Visuals
        if (selectedObject && selectedObject !== object) {
            // Reset previous selection to its specific base color
            selectedObject.material.color.setHex(selectedObject.userData.baseColor);
        }

        if (currentDotGrid) {
            if (selectedObject) selectedObject.remove(currentDotGrid); // Remove from parent mesh
            currentDotGrid.geometry.dispose();
            currentDotGrid.material.dispose();
            currentDotGrid = null;
        }

        selectedObject = object;
        selectedObject.material.color.setHex(CONFIG.colors.hover); // Keep it highlighted

        const provinceName = selectedObject.userData.name;

        // Visual FX: Delayed slightly to allow camera animation to start smoothly
        setTimeout(() => {
            try {
                // Create and add dot grid
                currentDotGrid = createDotGrid(selectedObject);
                if (currentDotGrid) {
                    selectedObject.add(currentDotGrid); // Add to mesh, not scene
                }
            } catch (err) {
                console.error("VFX Error:", err);
            }
        }, 50);

        // Calculate bounding box
        const box = new THREE.Box3().setFromObject(selectedObject);
        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());

        // Calculate appropriate camera distance
        const maxDim = Math.max(size.x, size.y, size.z);
        const fov = camera.fov * (Math.PI / 180);
        let cameraZ = Math.abs(maxDim / Math.tan(fov / 2)) * 1.5;
        cameraZ = Math.max(150, Math.min(cameraZ, 500));

        // Animate Camera
        const targetPosition = { x: center.x, y: camera.position.y, z: cameraZ };
        const startPosition = { x: camera.position.x, y: camera.position.y, z: camera.position.z };
        const startLookAt = { x: currentLookAtTarget.x, y: currentLookAtTarget.y, z: currentLookAtTarget.z };
        const duration = 1000;
        const startTime = Date.now();

        const animateCamera = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = progress < 0.5 ? 2 * progress * progress : -1 + (4 - 2 * progress) * progress;

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
                currentLookAtTarget.x = center.x;
                currentLookAtTarget.y = center.y;
                currentLookAtTarget.z = 0;
            }
        };
        animateCamera();

        // Notification UI
        const existingNote = document.getElementById('map-notification');
        if (existingNote) existingNote.remove();

        const notification = document.createElement('div');
        notification.id = 'map-notification';
        notification.style.position = 'absolute';
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
        notification.style.animation = 'fadeIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        notification.style.backdropFilter = 'blur(10px)';

        notification.innerHTML = `
            <h3 style="margin: 0 0 10px 0; font-size: 26px; font-weight:700; color: #ff0055;">${provinceName}</h3>
            <div style="width: 50px; height: 3px; background: #ff0055; margin: 0 auto 20px auto; border-radius: 2px;"></div>
            <div style="font-size: 16px; color: #e0e0e0;">در حال بارگذاری شعب...</div>
        `;

        container.appendChild(notification);

        // Fetch Data
        fetch(`backend/api/branches.php?province_slug=${encodeURIComponent(provinceName)}`)
            .then(response => response.json())
            .then(data => {
                // Update specific dots to be branches if we have count
                if (data.success && data.count > 0) {
                    console.log(`Setting up ${data.count} branch dots for ${provinceName}`);

                    // Helper to update attributes
                    const updateAttributes = () => {
                        if (!currentDotGrid) return; // Should not happen if timing is right, but safe guard

                        const attr = currentDotGrid.geometry.attributes.aIsBranch;
                        if (attr) {
                            const count = attr.count;
                            const array = attr.array;

                            // Reset
                            array.fill(0);

                            // Pick random unique indices
                            const indices = new Set();
                            let attempts = 0;
                            // Limit max dots to animate to avoid clutter if many branches
                            const dotsToAnimate = Math.min(data.count, 20);

                            while (indices.size < data.count && attempts < count * 2) {
                                indices.add(Math.floor(Math.random() * count));
                                attempts++;
                            }

                            indices.forEach(idx => {
                                array[idx] = 1.0;
                            });
                            attr.needsUpdate = true;
                            console.log("Updated aIsBranch attribute");
                        }
                    };

                    if (currentDotGrid) {
                        updateAttributes();
                    } else {
                        // Wait for it? usually createDotGrid is 50ms, fetch > 50ms.
                        // But if fetch is instant (cache), wait a bit.
                        setTimeout(updateAttributes, 100);
                    }
                }

                if (data.success && data.count > 0) {
                    let branchesHTML = '';
                    data.data.forEach((branch, index) => {
                        branchesHTML += `
                            <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; margin-bottom: ${index < data.count - 1 ? '12px' : '0'}; text-align: right;">
                                <div style="font-size: 16px; font-weight: 600; color: #00ffff; margin-bottom: 8px;">${branch.name}</div>
                                <div style="font-size: 14px; color: #ddd; margin-bottom: 6px; line-height: 1.6;">
                                    <i class="bi bi-geo-alt" style="margin-left: 5px;"></i> ${branch.address}
                                </div>
                                ${branch.phone ? `<div style="font-size: 14px; color: #ddd;"><i class="bi bi-telephone" style="margin-left: 5px;"></i> <a href="tel:${branch.phone}" style="color: #00ffff; text-decoration: none;">${branch.phone}</a></div>` : ''}
                            </div>
                        `;
                    });

                    notification.innerHTML = `
                        <h3 style="margin: 0 0 10px 0; font-size: 26px; font-weight:700; color: #ff0055;">${provinceName}</h3>
                        <div style="width: 50px; height: 3px; background: #ff0055; margin: 0 auto 20px auto; border-radius: 2px;"></div>
                        <div style="font-size: 14px; color: #aaa; margin-bottom: 20px;">${data.count} شعبه</div>
                        <div style="max-height: 400px; overflow-y: auto; margin-bottom: 25px;">${branchesHTML}</div>
                        <button id="closeNotification" style="background: linear-gradient(45deg, #ff0055, #ff4081); color: white; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(255, 0, 85, 0.3); margin-left: 10px;">بستن</button>
                        <button id="resetView" style="background: linear-gradient(45deg, #00ffff, #00cccc); color: #000; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(0, 255, 255, 0.3);">بازگشت به نمای کلی</button>
                    `;
                } else {
                    notification.innerHTML = `
                        <h3 style="margin: 0 0 10px 0; font-size: 26px; font-weight:700; color: #ff0055;">${provinceName}</h3>
                        <div style="width: 50px; height: 3px; background: #ff0055; margin: 0 auto 20px auto; border-radius: 2px;"></div>
                        <div style="font-size: 16px; line-height: 1.8; margin-bottom: 25px; color: #e0e0e0;">در حال حاضر شعبه‌ای برای این استان ثبت نشده است.</div>
                        <button id="closeNotification" style="background: linear-gradient(45deg, #ff0055, #ff4081); color: white; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(255, 0, 85, 0.3); margin-left: 10px;">بستن</button>
                        <button id="resetView" style="background: linear-gradient(45deg, #00ffff, #00cccc); color: #000; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(0, 255, 255, 0.3);">بازگشت به نمای کلی</button>
                    `;
                }
                attachNotificationHandlers();
            })
            .catch(error => {
                console.error('Error fetching branches:', error);
                notification.innerHTML = `
                    <h3 style="margin: 0 0 10px 0; font-size: 26px; font-weight:700; color: #ff0055;">${provinceName}</h3>
                    <div style="width: 50px; height: 3px; background: #ff0055; margin: 0 auto 20px auto; border-radius: 2px;"></div>
                    <div style="font-size: 16px; line-height: 1.8; margin-bottom: 25px; color: #e0e0e0;">خطا در دریافت اطلاعات شعب</div>
                    <button id="closeNotification" style="background: linear-gradient(45deg, #ff0055, #ff4081); color: white; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(255, 0, 85, 0.3); margin-left: 10px;">بستن</button>
                    <button id="resetView" style="background: linear-gradient(45deg, #00ffff, #00cccc); color: #000; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; font-family: inherit; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(0, 255, 255, 0.3);">بازگشت به نمای کلی</button>
                `;
                attachNotificationHandlers();
            });

        // Handlers using shared variables
        const attachNotificationHandlers = () => {
            const closeBtn = document.getElementById('closeNotification');
            const resetBtn = document.getElementById('resetView');

            if (closeBtn) closeBtn.onclick = () => {
                notification.style.opacity = '0';
                setTimeout(() => { if (notification.parentNode) notification.remove(); }, 300);
            };

            if (resetBtn) resetBtn.onclick = () => {
                resetView();
            };
        };
    };

    // --- Load Data & Build Map ---
    // Fetch both GeoJSON AND Provinces with branches from CMS database
    Promise.all([
        fetch(CONFIG.jsonPath).then(r => r.json()),
        // Fetch provinces from CMS - they include branch_count
        fetch('backend/api/provinces.php?include_inactive=1').then(r => {
            if (!r.ok) {
                throw new Error(`HTTP error! status: ${r.status}`);
            }
            return r.json();
        }).catch(err => {
            console.error('Error fetching provinces from CMS:', err);
            return { success: false, data: [] };
        })
    ]).then(([geojson, provinceData]) => {

        // Create lookup for provinces that have branches in CMS database
        const provinceHasBranchesMap = {}; // Name -> Has Branches
        // Create a comprehensive mapping: API province -> matched GeoJSON feature
        const apiProvinceMap = {}; // Multiple keys pointing to API province object
        const provinceIdToMeshMap = {}; // Province ID -> Mesh (for direct lookup)
        
        if (provinceData.success && provinceData.data && Array.isArray(provinceData.data)) {
            provinceData.data.forEach(p => {
                // Check if province has branches (branch_count > 0)
                const hasBranches = parseInt(p.branch_count || 0) > 0;
                
                // Only mark provinces that have branches
                if (hasBranches && p.name) {
                    provinceHasBranchesMap[p.name] = true;
                    // Also store by English name if available for better matching
                    if (p.name_en) {
                        provinceHasBranchesMap[p.name_en] = true;
                    }
                    // Also store by slug for additional matching
                    if (p.slug) {
                        provinceHasBranchesMap[p.slug] = true;
                    }
                }
                
                // Create multiple lookup keys for this API province
                const normalize = (str) => {
                    if (!str) return '';
                    return String(str).trim().toLowerCase().replace(/\s+/g, ' ');
                };
                
                // Store by ID (most reliable)
                if (p.id) {
                    apiProvinceMap[p.id] = p;
                }
                
                // Store by normalized names and slug
                if (p.name) {
                    apiProvinceMap[normalize(p.name)] = p;
                }
                if (p.name_en) {
                    apiProvinceMap[normalize(p.name_en)] = p;
                }
                if (p.slug) {
                    apiProvinceMap[normalize(p.slug)] = p;
                }
            });
            const provincesWithBranches = Object.keys(provinceHasBranchesMap);
            console.log(`Found ${provincesWithBranches.length} provinces with branches in CMS:`, provincesWithBranches);
            console.log(`Created API province map with ${Object.keys(apiProvinceMap).length} lookup keys`);
        } else {
            console.warn('No provinces data received from CMS:', provinceData);
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
            const provinceNameEn = feature.properties['name:en'] || feature.properties.name_en || null;
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

            // Determine Color
            // If province has branches in CMS database, use the classy green
            // Check both Persian and English names for better matching
            const hasBranches = provinceHasBranchesMap[provinceName] === true || 
                               (provinceNameEn && provinceHasBranchesMap[provinceNameEn] === true);
            const baseColor = hasBranches ? CONFIG.colors.hasBranch : CONFIG.colors.base;
            
            // Debug log for provinces with branches
            if (hasBranches) {
                console.log(`Province "${provinceName}" has branches in CMS - coloring green`);
            }

            // Material
            const material = new THREE.MeshPhongMaterial({
                color: baseColor,
                specular: 0x111111,
                shininess: 30,
                side: THREE.DoubleSide
            });

            // Try to find matching API province data
            const normalize = (str) => {
                if (!str) return '';
                return String(str).trim().toLowerCase().replace(/\s+/g, ' ');
            };
            
            let matchedApiProvince = null;
            // Try to match by normalized names
            if (apiProvinceMap[normalize(provinceName)]) {
                matchedApiProvince = apiProvinceMap[normalize(provinceName)];
            } else if (provinceNameEn && apiProvinceMap[normalize(provinceNameEn)]) {
                matchedApiProvince = apiProvinceMap[normalize(provinceNameEn)];
            }
            
            const mesh = new THREE.Mesh(geometry, material);
            mesh.userData = {
                name: provinceName,
                nameEn: provinceNameEn, // Store English name for matching
                apiProvince: matchedApiProvince, // Store matched API province data
                provinceId: matchedApiProvince ? matchedApiProvince.id : null, // Store province ID for direct lookup
                address: hasBranches ? 'برای مشاهده شعب کلیک کنید' : 'شعبه فعال وجود ندارد',
                hasBranches: hasBranches, // True if province has branches in CMS
                baseColor: baseColor, // Store for reset logic
                shapes: shapes // Store shapes for dot grid generation
            };
            
            // Add to lookup map by province ID if we have a match
            if (matchedApiProvince && matchedApiProvince.id) {
                provinceIdToMeshMap[matchedApiProvince.id] = mesh;
            }

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
        
        // Log matching summary
        const matchedCount = Object.keys(provinceIdToMeshMap).length;
        const totalMeshes = mapGroup.children.length;
        console.log(`Province matching summary: ${matchedCount} meshes matched to API provinces out of ${totalMeshes} total meshes`);

        // --- Create Province List Table ---
        if (provinceData.success && provinceData.data) {
            const listContainer = document.createElement('div');
            listContainer.className = 'province-list-container';
            listContainer.style.width = '100%';
            listContainer.style.padding = '20px';
            listContainer.style.boxSizing = 'border-box';
            listContainer.style.marginTop = 'auto'; // Push to bottom if space
            listContainer.style.backgroundColor = 'rgba(0,0,0,0.2)';
            listContainer.style.position = 'relative';
            listContainer.style.zIndex = '10'; // Above the map canvas
            listContainer.style.pointerEvents = 'auto'; // Ensure it can receive events
            // Prevent map clicks when mouse is over the button area
            listContainer.onmouseenter = (e) => {
                e.stopPropagation();
                mapClickDisabled = true;
            };
            listContainer.onmouseleave = (e) => {
                e.stopPropagation();
                mapClickDisabled = false;
            };
            // Stop all events from propagating to map
            listContainer.onmousedown = (e) => {
                e.stopPropagation();
                e.preventDefault();
            };
            listContainer.onmouseup = (e) => {
                e.stopPropagation();
                e.preventDefault();
            };
            listContainer.onclick = (e) => {
                e.stopPropagation();
                e.preventDefault();
            };
            listContainer.onmousemove = (e) => {
                e.stopPropagation();
            };

            // Grid for cards
            const listGrid = document.createElement('div');
            listGrid.style.display = 'grid';
            listGrid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(140px, 1fr))';
            listGrid.style.gap = '15px';
            listGrid.style.direction = 'rtl';
            listGrid.style.pointerEvents = 'auto';
            // Also prevent events on the grid
            listGrid.onmousedown = (e) => {
                e.stopPropagation();
                e.preventDefault();
            };
            listGrid.onmouseup = (e) => {
                e.stopPropagation();
                e.preventDefault();
            };
            listGrid.onclick = (e) => {
                e.stopPropagation();
                e.preventDefault();
            };
            listGrid.onmousemove = (e) => {
                e.stopPropagation();
            };

            provinceData.data.forEach(p => {
                const item = document.createElement('div');
                item.style.background = 'rgba(255,255,255,0.05)';
                item.style.borderRadius = '10px';
                item.style.padding = '15px';
                item.style.cursor = 'pointer';
                item.style.transition = 'all 0.3s ease';
                item.style.border = '1px solid rgba(255,255,255,0.05)';
                item.style.textAlign = 'center';
                item.style.display = 'flex';
                item.style.flexDirection = 'column';
                item.style.alignItems = 'center';
                item.style.justifyContent = 'center';

                const name = document.createElement('div');
                name.textContent = p.name;
                name.style.fontWeight = 'bold';
                name.style.color = '#fff';
                name.style.marginBottom = '5px';

                // Status dot - green if province has branches, red if no branches
                const status = document.createElement('div');
                const hasBranches = parseInt(p.branch_count || 0) > 0;
                status.style.width = '8px';
                status.style.height = '8px';
                status.style.borderRadius = '50%';
                status.style.backgroundColor = hasBranches ? '#00a86b' : '#ff0055'; // Green for branches, red for no branches
                status.style.boxShadow = hasBranches ? '0 0 5px #00a86b' : '0 0 5px #ff0055';

                item.appendChild(name);
                item.appendChild(status);

                // Hover effect - use green border if has branches, red if no branches
                item.onmouseenter = (e) => {
                    e.stopPropagation();
                    item.style.background = 'rgba(255,255,255,0.15)';
                    item.style.transform = 'translateY(-3px)';
                    item.style.borderColor = hasBranches ? '#00a86b' : '#ff0055';
                };
                item.onmouseleave = (e) => {
                    e.stopPropagation();
                    item.style.background = 'rgba(255,255,255,0.05)';
                    item.style.transform = 'translateY(0)';
                    item.style.borderColor = 'rgba(255,255,255,0.05)';
                };
                
                // Prevent all mouse events from reaching the map
                item.onmousedown = (e) => e.stopPropagation();
                item.onmouseup = (e) => e.stopPropagation();
                item.onmousemove = (e) => e.stopPropagation();

                // Click Action
                item.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    let mesh = null;
                    
                    // Method 1: Direct lookup by province ID (most reliable)
                    if (p.id && provinceIdToMeshMap[p.id]) {
                        mesh = provinceIdToMeshMap[p.id];
                        console.log(`Found mesh by ID for province: ${p.name} (ID: ${p.id})`);
                    } else {
                        // Method 2: Find by matching API province data stored in mesh
                        mesh = mapGroup.children.find(m => {
                            if (m.userData.apiProvince && m.userData.apiProvince.id === p.id) {
                                return true;
                            }
                            return false;
                        });
                        
                        if (mesh) {
                            console.log(`Found mesh by API province match for: ${p.name}`);
                        }
                    }
                    
                    // Method 3: Fallback to name matching if ID lookup failed
                    if (!mesh) {
                        const normalize = (str) => {
                            if (!str) return '';
                            return String(str).trim().toLowerCase().replace(/\s+/g, ' ');
                        };
                        
                        const apiName = normalize(p.name);
                        const apiNameEn = p.name_en ? normalize(p.name_en) : null;
                        const apiSlug = p.slug ? normalize(p.slug) : null;
                        
                        mesh = mapGroup.children.find(m => {
                            const meshName = normalize(m.userData.name);
                            const meshNameEn = m.userData.nameEn ? normalize(m.userData.nameEn) : null;
                            
                            // Try exact match first
                            if (meshName === apiName) return true;
                            if (meshNameEn === apiName) return true;
                            if (meshName === apiNameEn) return true;
                            if (meshNameEn === apiNameEn) return true;
                            
                            // Try case-insensitive match
                            if (meshName === apiName) return true;
                            if (meshNameEn && apiNameEn && meshNameEn === apiNameEn) return true;
                            
                            // Try partial match (contains)
                            if (meshName.includes(apiName) || apiName.includes(meshName)) return true;
                            if (meshNameEn && apiNameEn && (meshNameEn.includes(apiNameEn) || apiNameEn.includes(meshNameEn))) return true;
                            
                            return false;
                        });
                        
                        if (mesh) {
                            console.log(`Found mesh by name matching for province: ${p.name} -> ${mesh.userData.name}`);
                        }
                    }
                    
                    if (mesh) {
                        selectProvince(mesh);
                        // Scroll to map top
                        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        console.warn(`Could not find mesh for province: ${p.name} (ID: ${p.id})`, {
                            availableMeshes: mapGroup.children.map(m => ({
                                name: m.userData.name,
                                nameEn: m.userData.nameEn,
                                provinceId: m.userData.provinceId
                            }))
                        });
                    }
                };

                listGrid.appendChild(item);
            });

            listContainer.appendChild(listGrid);
            container.appendChild(listContainer);
        }

    }).catch(err => {
        console.error('Error loading map data:', err);
    });

    // --- Interaction (Raycaster) ---
    // Variables already defined above

    const onMouseMove = (event) => {
        // Don't process map interactions if clicks are disabled (mouse over buttons)
        if (mapClickDisabled) {
            // Hide tooltip when over buttons
            if (tooltip.style.opacity !== '0') {
                tooltip.style.opacity = '0';
            }
            // Reset hover state
            if (hoveredObject && hoveredObject !== selectedObject) {
                hoveredObject.material.color.setHex(hoveredObject.userData.baseColor);
                hoveredObject = null;
            }
            document.body.style.cursor = 'default';
            return;
        }
        
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
        // Don't process map clicks if disabled (mouse over buttons)
        if (mapClickDisabled) {
            return;
        }
        
        if (hoveredObject) {
            selectProvince(hoveredObject);
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
                    hoveredObject.material.color.setHex(hoveredObject.userData.baseColor);
                }

                hoveredObject = object;
                // Highlight new hovered object even if selected (or maybe brighter?)
                // Simple logic: Hover always highlights.
                hoveredObject.material.color.setHex(CONFIG.colors.hover);

                // Update Tooltip
                tooltip.innerHTML = `
                    <div style="font-size:16px; font-weight:700; margin-bottom:4px; color:${'#fff'}">${hoveredObject.userData.name}</div>
                    <div style="font-size:13px; color:#ddd; direction:rtl; text-align:right;">${hoveredObject.userData.address}</div>
                `;
                tooltip.style.opacity = '1';
                document.body.style.cursor = 'pointer';
            }
        } else {
            if (hoveredObject) {
                // Reset hovered object logic
                if (hoveredObject !== selectedObject) {
                    hoveredObject.material.color.setHex(hoveredObject.userData.baseColor);
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

        // Update Dot Grid Animation
        if (currentDotGrid && currentDotGrid.material.uniforms) {
            currentDotGrid.material.uniforms.uTime.value = time;
        }

        renderer.render(scene, camera);
    };

    animate();

	};
	
	// If DOM is already loaded, run immediately, otherwise wait for DOMContentLoaded
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(); // Close wrapper

