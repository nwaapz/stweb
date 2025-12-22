
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
    legend.style.position = 'relative';
    legend.style.marginTop = '20px';
    legend.style.marginLeft = 'auto';
    legend.style.marginRight = 'auto';
    legend.style.width = 'fit-content';
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
    legend.style.pointerEvents = 'auto'; // Enable pointer events to catch clicks
    legend.style.boxShadow = '0 5px 20px rgba(0,0,0,0.5)';
    // Prevent all mouse events on legend from reaching map
    const stopLegendEvents = (event) => {
        event.stopPropagation();
    };
    legend.addEventListener('click', stopLegendEvents, true);
    legend.addEventListener('mousedown', stopLegendEvents, true);
    legend.addEventListener('mouseup', stopLegendEvents, true);

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
    // Don't append to container yet - will append to province-list-container later

    // --- Reset Camera Function (Reusable) ---
    const resetCameraView = () => {
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

        if (selectedObject) {
            selectedObject.material.color.setHex(selectedObject.userData.baseColor);
            selectedObject = null;
        }
    };

    // --- Reset View Button (Always Visible) ---
    const resetViewButton = document.createElement('button');
    resetViewButton.innerHTML = '<i class="bi bi-arrows-angle-contract" style="margin-left: 8px;"></i>بازگشت به نمای کلی';
    resetViewButton.style.position = 'absolute';
    resetViewButton.style.top = '20px';
    resetViewButton.style.right = '20px';
    resetViewButton.style.zIndex = '1000';
    resetViewButton.style.background = 'linear-gradient(45deg, #00ffff, #00cccc)';
    resetViewButton.style.color = '#000';
    resetViewButton.style.border = 'none';
    resetViewButton.style.padding = '12px 24px';
    resetViewButton.style.borderRadius = '8px';
    resetViewButton.style.cursor = 'pointer';
    resetViewButton.style.fontSize = '14px';
    resetViewButton.style.fontWeight = '600';
    resetViewButton.style.fontFamily = 'inherit';
    resetViewButton.style.transition = 'all 0.3s ease';
    resetViewButton.style.boxShadow = '0 5px 15px rgba(0, 255, 255, 0.4)';
    resetViewButton.style.direction = 'rtl';
    resetViewButton.style.backdropFilter = 'blur(5px)';
    resetViewButton.onmouseenter = () => {
        resetViewButton.style.transform = 'translateY(-2px)';
        resetViewButton.style.boxShadow = '0 8px 20px rgba(0, 255, 255, 0.6)';
    };
    resetViewButton.onmouseleave = () => {
        resetViewButton.style.transform = 'translateY(0)';
        resetViewButton.style.boxShadow = '0 5px 15px rgba(0, 255, 255, 0.4)';
    };
    resetViewButton.onclick = (event) => {
        event.stopPropagation();
        resetCameraView();
    };
    container.appendChild(resetViewButton);

    // --- Interaction State (Hoisted for Table Access) ---
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    let hoveredObject = null;
    let selectedObject = null;
    let currentLookAtTarget = { x: 0, y: 0, z: 0 };
    let isMouseOverProvinceList = false; // Track if mouse is over province list

    // --- Selection Logic ---
    const selectProvince = (object) => {
        if (!object) return;

        // Handle Selection Visuals
        if (selectedObject && selectedObject !== object) {
            // Reset previous selection to its specific base color
            selectedObject.material.color.setHex(selectedObject.userData.baseColor);
        }

        selectedObject = object;
        selectedObject.material.color.setHex(CONFIG.colors.hover); // Keep it highlighted

        const provinceName = selectedObject.userData.name;
        // const provinceAddress = selectedObject.userData.address; // Not used in notification logic currently? check below

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

        // Prevent clicks on notification from reaching map
        notification.onclick = (event) => {
            event.stopPropagation();
        };

        container.appendChild(notification);

        // Fetch Data
        fetch(`backend/api/branches.php?province_slug=${encodeURIComponent(provinceName)}`)
            .then(response => response.json())
            .then(data => {
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
                resetCameraView();
                if (notification.parentNode) notification.remove();
            };
        };
    };

    // --- Load Data & Build Map ---
    // Fetch both GeoJSON AND Active Provinces data
    Promise.all([
        fetch(CONFIG.jsonPath).then(r => r.json()),
        fetch('backend/api/provinces.php?is_active=1').then(r => r.json())
    ]).then(([geojson, provinceData]) => {

        // Create lookup for branches
        const provinceBranchMap = {}; // Name -> HasBranches
        if (provinceData.success && provinceData.data) {
            provinceData.data.forEach(p => {
                // p.branch_count > 0 means it has branches
                // Store using Persian Name as key, as GeoJSON uses Persian Names
                provinceBranchMap[p.name] = (parseInt(p.branch_count || 0) > 0);
            });
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

            // Determine Color
            // If province has active branches, use the classy green
            const hasBranches = provinceBranchMap[provinceName] === true;
            const baseColor = hasBranches ? CONFIG.colors.hasBranch : CONFIG.colors.base;

            // Material
            const material = new THREE.MeshPhongMaterial({
                color: baseColor,
                specular: 0x111111,
                shininess: 30,
                side: THREE.DoubleSide
            });

            const mesh = new THREE.Mesh(geometry, material);
            mesh.userData = {
                name: provinceName,
                address: hasBranches ? 'برای مشاهده شعب کلیک کنید' : 'شعبه فعال وجود ندارد',
                hasBranches: hasBranches,
                baseColor: baseColor // Store for reset logic
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

        // --- Create Province List Table ---
        if (provinceData.success && provinceData.data) {
            const listContainer = document.createElement('div');
            listContainer.className = 'province-list-container';
            listContainer.style.width = '100%';
            listContainer.style.padding = '20px';
            listContainer.style.boxSizing = 'border-box';
            listContainer.style.marginTop = 'auto'; // Push to bottom if space
            listContainer.style.backgroundColor = 'rgba(0,0,0,0.2)';
            // Prevent clicks on empty areas of container from reaching map
            // But allow province item clicks to work (they stop propagation themselves)
            listContainer.addEventListener('click', (event) => {
                // Only stop if clicking directly on container, not on child elements
                if (event.target === listContainer) {
                    event.stopPropagation();
                }
            }, false);
            
            // Disable map hover when mouse is over province list
            listContainer.addEventListener('mouseenter', () => {
                isMouseOverProvinceList = true;
                // Reset any hovered province on map
                if (hoveredObject && hoveredObject !== selectedObject) {
                    hoveredObject.material.color.setHex(hoveredObject.userData.baseColor);
                }
                hoveredObject = null;
                tooltip.style.opacity = '0';
                document.body.style.cursor = 'default';
            }, false);
            
            listContainer.addEventListener('mouseleave', () => {
                isMouseOverProvinceList = false;
            }, false);

            // Grid for cards
            const listGrid = document.createElement('div');
            listGrid.style.display = 'grid';
            listGrid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(140px, 1fr))';
            listGrid.style.gap = '15px';
            listGrid.style.direction = 'rtl';

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

                // Status dot
                const status = document.createElement('div');
                const hasBranch = (parseInt(p.branch_count || 0) > 0);
                status.style.width = '8px';
                status.style.height = '8px';
                status.style.borderRadius = '50%';
                status.style.backgroundColor = hasBranch ? '#00a86b' : '#333';
                status.style.boxShadow = hasBranch ? '0 0 5px #00a86b' : 'none';

                item.appendChild(name);
                item.appendChild(status);

                // Hover effect
                item.onmouseenter = () => {
                    item.style.background = 'rgba(255,255,255,0.15)';
                    item.style.transform = 'translateY(-3px)';
                    item.style.borderColor = hasBranch ? '#00a86b' : '#666';
                };
                item.onmouseleave = () => {
                    item.style.background = 'rgba(255,255,255,0.05)';
                    item.style.transform = 'translateY(0)';
                    item.style.borderColor = 'rgba(255,255,255,0.05)';
                };

                // Click Action
                item.onclick = (event) => {
                    event.preventDefault();
                    event.stopPropagation(); // Prevent click from reaching map
                    
                    console.log('Province button clicked:', p.name);
                    
                    // Normalize function to help with matching
                    const normalize = (str) => {
                        if (!str) return '';
                        return str.trim()
                            .toLowerCase()
                            .replace(/\s+/g, ' ') // Normalize spaces
                            .replace(/[ی]/g, 'ی') // Normalize ی
                            .replace(/[ک]/g, 'ک') // Normalize ک
                            .replace(/[ۀ]/g, 'ه'); // Normalize ه
                    };
                    
                    // Find mesh - try multiple matching strategies
                    let mesh = null;
                    
                    // Strategy 1: Exact match
                    mesh = mapGroup.children.find(m => 
                        m.userData && m.userData.name && m.userData.name === p.name
                    );
                    
                    // Strategy 2: Case-insensitive match
                    if (!mesh) {
                        mesh = mapGroup.children.find(m => 
                            m.userData && m.userData.name && p.name && 
                            normalize(m.userData.name) === normalize(p.name)
                        );
                    }
                    
                    // Strategy 3: Partial match (contains)
                    if (!mesh) {
                        const normalizedSearch = normalize(p.name);
                        mesh = mapGroup.children.find(m => 
                            m.userData && m.userData.name && 
                            normalize(m.userData.name).includes(normalizedSearch) ||
                            normalizedSearch.includes(normalize(m.userData.name))
                        );
                    }
                    
                    // Strategy 4: Try matching without common words
                    if (!mesh) {
                        const removeCommonWords = (str) => {
                            return normalize(str)
                                .replace(/\b(استان|province)\b/g, '')
                                .trim();
                        };
                        const searchNormalized = removeCommonWords(p.name);
                        mesh = mapGroup.children.find(m => 
                            m.userData && m.userData.name && 
                            removeCommonWords(m.userData.name) === searchNormalized
                        );
                    }
                    
                    if (mesh) {
                        console.log('Mesh found, selecting province:', p.name, '->', mesh.userData.name);
                        selectProvince(mesh);
                        // Scroll to map top
                        setTimeout(() => {
                            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 100);
                    } else {
                        console.warn('Province mesh not found for:', p.name);
                        const availableProvinces = mapGroup.children
                            .filter(m => m.userData && m.userData.name)
                            .map(m => m.userData.name);
                        console.log('Available provinces:', availableProvinces);
                        console.log('Searching for:', p.name, 'normalized:', normalize(p.name));
                        
                        // Try to find closest match
                        const searchNormalized = normalize(p.name);
                        const closestMatch = mapGroup.children.find(m => {
                            if (!m.userData || !m.userData.name) return false;
                            const meshNormalized = normalize(m.userData.name);
                            return meshNormalized.includes(searchNormalized) || 
                                   searchNormalized.includes(meshNormalized);
                        });
                        if (closestMatch) {
                            console.log('Found closest match:', closestMatch.userData.name, '- trying it...');
                            selectProvince(closestMatch);
                            setTimeout(() => {
                                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }, 100);
                        }
                    }
                };

                listGrid.appendChild(item);
            });

            listContainer.appendChild(listGrid);
            listContainer.appendChild(legend); // Append legend to province-list-container, centered at bottom
            container.appendChild(listContainer);
        } else {
            // If no province data, still show legend in container
            container.appendChild(legend);
        }

    }).catch(err => {
        console.error('Error loading map data:', err);
    });

    // --- Interaction (Raycaster) ---
    // Variables already defined above

    const onMouseMove = (event) => {
        // Don't process map hover if mouse is over province list
        if (isMouseOverProvinceList) {
            // Reset any hovered object when mouse leaves map area
            if (hoveredObject && hoveredObject !== selectedObject) {
                hoveredObject.material.color.setHex(hoveredObject.userData.baseColor);
            }
            hoveredObject = null;
            tooltip.style.opacity = '0';
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

        renderer.render(scene, camera);
    };

    animate();

});  // Close DOMContentLoaded listener

