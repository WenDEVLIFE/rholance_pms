<?php
$catalog = [
    ['name' => 'Gate', 'img' => 'gate.png', 'desc' => 'Durable customized iron and stainless steel gates for ultimate residential and commercial security.'],
    ['name' => 'Water Tank (Stainless)', 'img' => 'water_tank.png', 'desc' => 'Corrosion-resistant stainless steel water storage tanks fabricated for maximum structural life and purity.'],
    ['name' => 'Table', 'img' => 'table.png', 'desc' => 'Beautifully crafted customized metal and stainless steel frame tables suitable for dining, office, or industrial use.'],
    ['name' => 'Lababo (Sink)', 'img' => 'lababo.jpg', 'desc' => 'Premium customized single, double, or triple basin stainless steel sinks, perfect for commercial kitchens or residential spaces.'],
    ['name' => 'Stainless Letters', 'img' => 'stainless_letters.png', 'desc' => 'Polished or brushed custom 3D stainless steel signage letters for modern professional building facades or reception displays.'],
    ['name' => 'Windows (Metal Frame)', 'img' => 'windows.png', 'desc' => 'Robust window metal structures and secure frames designed with aesthetic charm and heavy-duty durability.'],
    ['name' => 'Handrail', 'img' => 'handrail.jpg', 'desc' => 'Elegant stainless steel or wrought iron handrails fabricated perfectly for safe stairs, terraces, and commercial walkspaces.'],
    ['name' => 'Push Cart', 'img' => 'push_cart.jpg', 'desc' => 'Sturdy multi-purpose push carts built for medical clinics, high-end salons, industrial warehouses, or catering use.'],
    ['name' => 'Carrier (Push Cart)', 'img' => 'carrier.jpg', 'desc' => 'Heavy-duty flatbed towable utility carrier carts fabricated to handle massive loads and rough outdoor terrains.'],
    ['name' => 'Terrace (Metal Structure)', 'img' => 'terrace.png', 'desc' => 'Structural metal framing, reliable trusses, and safety deck structures for balconies, rooftops, and outdoor terraces.'],
    ['name' => 'Upuan (Chair)', 'img' => 'upuan.jpg', 'desc' => 'Custom polished minimal stainless steel chairs built with sleek silhouettes, modern geometry, and extreme lifetime durability.'],
    ['name' => 'Laboratory Cabinet', 'img' => 'cabinet.jpg', 'desc' => 'Hygienic, chemical-resistant custom industrial stainless steel cabinets designed for modern laboratories and medical labs.']
];
?>
<div class="d-flex align-items-center justify-content-between mb-3 mt-5">
    <h5 class="fw-800 mb-0"><i class="fas fa-box-open me-2 text-amber"></i>Our Products Catalog</h5>
</div>
<div class="row g-3 mb-5">
    <?php foreach($catalog as $idx => $p): ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card h-100 border-0 shadow-sm cursor-pointer catalog-item" data-product="<?= htmlspecialchars($p['name']) ?>">
            <img src="../assets/images/products/customized/<?= $p['img'] ?>" class="card-img-top" style="height:150px; object-fit:cover;" onerror="this.src='../assets/images/no-image.png'">
            <div class="card-body p-3 text-center">
                <h6 class="fw-800 mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                <p class="small text-muted mb-0" style="font-size:0.75rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?= htmlspecialchars($p['desc']) ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Variants Modal -->
<div class="modal fade" id="variantsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-layer-group me-2 text-amber"></i><span id="variantModalTitle">Variants</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div id="variantsGrid" class="row g-3">
                    <div class="col-12 text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading variants...</div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-outline-secondary fw-700" data-bs-dismiss="modal">Close</button>
                <a href="customize.php" class="btn btn-warning fw-800 shadow-sm"><i class="fas fa-paper-plane me-1"></i>Request Custom Build</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const vModal = new bootstrap.Modal(document.getElementById('variantsModal'));
    const title = document.getElementById('variantModalTitle');
    const grid = document.getElementById('variantsGrid');

    document.querySelectorAll('.catalog-item').forEach(el => {
        el.addEventListener('click', function() {
            const product = this.getAttribute('data-product');
            title.textContent = product + ' Variants';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading variants...</div>';
            vModal.show();

            fetch('../api/get_variants.php?product=' + encodeURIComponent(product))
            .then(r => r.json())
            .then(data => {
                if (data.error || !data.variants || data.variants.length === 0) {
                    grid.innerHTML = '<div class="col-12 text-center text-muted py-4"><i class="fas fa-box-open fs-2 mb-2 d-block opacity-25"></i>No sample variants uploaded for this product yet.</div>';
                    return;
                }
                
                let html = '';
                data.variants.forEach(v => {
                    html += `
                    <div class="col-6 col-md-4 text-center">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="../${v.image_path}" class="card-img-top rounded-top" style="height:150px; object-fit:cover;" onerror="this.src='../assets/images/no-image.png'">
                            <div class="card-body p-2">
                                <h6 class="fw-800 mb-0 small text-truncate" title="${v.variant_name}">${v.variant_name}</h6>
                            </div>
                        </div>
                    </div>`;
                });
                grid.innerHTML = html;
            })
            .catch(e => {
                grid.innerHTML = '<div class="col-12 text-center text-danger py-4">Error loading variants.</div>';
            });
        });
    });
});
</script>
<style>.cursor-pointer { cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; } .cursor-pointer:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }</style>
