<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$customCatalog = [
    [
        'name' => 'Gate',
        'img'  => 'assets/images/products/customized/gate.png',
        'desc' => 'Premium customized metal/stainless security gates. Integrated hinges and anti-rust base painting.',
        'spec' => 'Square tubes, Flat/Angle bars, Primer paint, Lockset options'
    ],
    [
        'name' => 'Water Tank (Stainless)',
        'img'  => 'assets/images/products/customized/water_tank.png',
        'desc' => 'Heavy duty grade 304 stainless steel water storage tanks. Highly resilient to corrosion.',
        'spec' => 'Stainless steel sheets, Inlet/Outlet pipes, Valves, Sealed welding'
    ],
    [
        'name' => 'Table',
        'img'  => 'assets/images/products/customized/table.png',
        'desc' => 'Sturdy industrial workbenches and dining table frames designed with clean modern finishes.',
        'spec' => 'Mild/Stainless frame, Angle bars, Protective footpads'
    ],
    [
        'name' => 'Lababo (Sink)',
        'img'  => 'assets/images/products/customized/lababo.jpg',
        'desc' => 'Single or multiple basin stainless kitchen sinks. Perfect for restaurants and home use.',
        'spec' => 'Stainless plates, drain fittings, brackets, protective sealant'
    ],
    [
        'name' => 'Stainless Letters',
        'img'  => 'assets/images/products/customized/stainless_letters.png',
        'desc' => 'Customized modern 3D signages and laser cut letters. Weatherproof and ideal for business storefronts.',
        'spec' => 'Polished or hairline finish sheets, mounting brackets'
    ],
    [
        'name' => 'Windows (Metal Frame)',
        'img'  => 'assets/images/products/customized/windows.png',
        'desc' => 'High-security metallic frame window grilles and architectural structural casings.',
        'spec' => 'Square tubes, security steel bars, premium hinges, primer coat'
    ],
    [
        'name' => 'Handrail',
        'img'  => 'assets/images/products/customized/handrail.jpg',
        'desc' => 'Polished stainless steel staircases and terrace handrails. Built for heavy structural support.',
        'spec' => 'Stainless circular/square pipes, brackets, heavy-duty anchors'
    ],
    [
        'name' => 'Push Cart',
        'img'  => 'assets/images/products/customized/push_cart.jpg',
        'desc' => 'Industrial flatbed material handling carts. Equipped with high-weight load bearing casters.',
        'spec' => 'Heavy-duty angle bars, handle grips, industrial caster wheels'
    ],
    [
        'name' => 'Carrier (Push Cart)',
        'img'  => 'assets/images/products/customized/carrier.jpg',
        'desc' => 'Luggage and heavy inventory logistics carriers. Custom welded framework for specific warehouses.',
        'spec' => 'Tubular framing, support reinforcement plates, bearing wheels'
    ],
    [
        'name' => 'Terrace (Metal Structure)',
        'img'  => 'assets/images/products/customized/terrace.png',
        'desc' => 'Full architectural balcony structural frame. Pre-welded elements for quick on-site installation.',
        'spec' => 'Heavy structural I-beams, metal sheets, supporting rods'
    ],
    [
        'name' => 'Upuan (Chair)',
        'img'  => 'assets/images/products/customized/upuan.jpg',
        'desc' => 'Sleek metal counter stools and heavy industrial shop chairs. Ergonomic welds.',
        'spec' => 'Round metal tubes, protective powder finish, customized back support'
    ],
    [
        'name' => 'Laboratory Cabinet',
        'img'  => 'assets/images/products/customized/cabinet.jpg',
        'desc' => 'Chemical and medical-grade cleanroom stainless cabinets. Dustproof tight hinges.',
        'spec' => 'Corrosion-proof sheets, handles, slide drawers, lock modules'
    ]
];
?>

<div class="rh-main">
    
    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1>Available Customizations</h1>
            <p>Browse our 12 premium fabricated structures. Select any model to book your design consultation slot!</p>
        </div>
    </div>

    <!-- CATALOG INTRO CARD -->
    <div class="card bg-dark text-white border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="fw-800 text-amber mb-2"><i class="fas fa-hammer me-2"></i>Fabrication Consultations</h4>
                <p class="mb-0 text-secondary" style="font-size:0.9rem;">We don't use simple e-commerce add-to-cart checkouts because every single project is custom tailored! Choose any product design below to book a consultation slot. Our branch welder will visit your site, log dimensions, and finalize your specs!</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>customer/available_appointments.php" class="btn btn-warning fw-800 text-dark px-4 py-2">
                    <i class="fas fa-calendar-check me-2"></i>General Appointment
                </a>
            </div>
        </div>
    </div>

    <!-- 12 CUSTOMIZED PRODUCTS GRID -->
    <div class="row g-4 mb-5">
        <?php foreach ($customCatalog as $p): 
            $pName = $conn->real_escape_string($p['name']);
            $variants = $conn->query("SELECT * FROM custom_product_variants WHERE product_name = '$pName' ORDER BY created_at DESC");
        ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 rh-proj-card border-0 shadow-sm d-flex flex-column justify-content-between" style="overflow:hidden; border-radius:12px;">
                <div>
                    <!-- Thumbnail container with absolute overlay and status float -->
                    <div class="rh-proj-thumb position-relative" style="height: 200px; overflow:hidden;">
                        <img src="<?= BASE_URL ?><?= htmlspecialchars($p['img']) ?>"
                             class="w-100 h-100 object-fit-cover"
                             onerror="this.src='<?= BASE_URL ?>assets/images/no-image.png'" alt="<?= htmlspecialchars($p['name']) ?>">
                        <span class="badge bg-amber text-dark status-float position-absolute top-0 end-0 m-3 fw-800 shadow-sm">Custom Build</span>
                    </div>
                    
                    <div class="card-body p-4 bg-white pb-3">
                        <h5 class="fw-800 text-light-emphasis mb-2"><?= htmlspecialchars($p['name']) ?></h5>
                        <p class="text-muted mb-3" style="font-size:0.85rem; line-height:1.4; height: 60px; overflow:hidden;">
                            <?= htmlspecialchars($p['desc']) ?>
                        </p>
                        
                        <div class="p-3 rounded-3 mb-3" style="background: rgba(0,0,0,0.02); font-size:0.75rem;">
                            <span class="d-block fw-800 text-light-emphasis mb-1"><i class="fas fa-layer-group text-amber me-1"></i>CORE MATERIALS INCLUDED:</span>
                            <span class="text-muted"><?= htmlspecialchars($p['spec']) ?></span>
                        </div>

                        <!-- VARIANTS PREVIEW GALLERY -->
                        <?php if ($variants && $variants->num_rows > 0): ?>
                            <div class="fw-800 small text-light-emphasis mb-2"><i class="fas fa-images me-1 text-amber"></i>Design Previews:</div>
                            <div class="d-flex gap-2 pb-2" style="overflow-x: auto; scroll-snap-type: x mandatory;">
                                <?php while($v = $variants->fetch_assoc()): ?>
                                    <div class="flex-shrink-0" style="scroll-snap-align: start; width:80px; position:relative; group">
                                        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($v['image_url']) ?>" 
                                             class="rounded border" style="width:80px; height:60px; object-fit:cover; cursor:pointer;"
                                             onclick="previewImage(this.src, '<?= htmlspecialchars($v['variant_name'], ENT_QUOTES) ?>')"
                                             onerror="this.src='<?= BASE_URL ?>assets/images/no-image.png'">
                                        <div class="small fw-700 mt-1 text-truncate" style="font-size:0.65rem;" title="<?= htmlspecialchars($v['variant_name']) ?>">
                                            <?= htmlspecialchars($v['variant_name']) ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="card-footer bg-white border-0 p-4 pt-0">
                    <a href="<?= BASE_URL ?>customer/available_appointments.php?service=<?= urlencode($p['name']) ?>" 
                       class="btn btn-warning w-100 fw-800 text-dark py-2">
                        <i class="fas fa-calendar-plus me-1"></i>Customize <?= htmlspecialchars($p['name']) ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <img id="previewModalImage" src="" class="img-fluid rounded shadow-lg mb-3" style="max-height:80vh;">
                <h4 id="previewModalTitle" class="text-white fw-800 m-0 text-shadow"></h4>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(src, title) {
    document.getElementById('previewModalImage').src = src;
    document.getElementById('previewModalTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
}
</script>
</div>
</body></html>