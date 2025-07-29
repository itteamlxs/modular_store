<!-- Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="modalProductImage" src="" class="img-fluid rounded" alt="">
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-2" id="modalProductCategory"></p>
                        <p id="modalProductDescription"></p>
                        <h4 class="text-success mb-3" id="modalProductPrice"></h4>
                        <p class="text-muted mb-3">Stock: <span id="modalProductStock"></span></p>
                        
                        <form id="modalAddToCartForm" action="/modular-store/modules/cart/controllers/add-modal.php" method="post">
                            <input type="hidden" id="modalProductId" name="product_id" value="">
                            <div class="mb-3">
                                <label class="form-label">Cantidad:</label>
                                <div class="input-group" style="max-width: 150px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">-</button>
                                    <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">+</button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Añadir al Carrito</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentStock = 0;

function showProductModal(product) {
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductCategory').textContent = product.category;
    document.getElementById('modalProductDescription').textContent = product.description || 'Sin descripción';
    document.getElementById('modalProductPrice').textContent = '$' + parseFloat(product.price).toFixed(2);
    document.getElementById('modalProductStock').textContent = product.stock;
    document.getElementById('modalProductId').value = product.id;
    
    const img = document.getElementById('modalProductImage');
    if (product.image_url) {
        img.src = product.image_url;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }
    
    currentStock = parseInt(product.stock);
    document.getElementById('quantity').max = currentStock;
    document.getElementById('quantity').value = 1;
    
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    let newQuantity = parseInt(quantityInput.value) + delta;
    
    if (newQuantity < 1) newQuantity = 1;
    if (newQuantity > currentStock) newQuantity = currentStock;
    
    quantityInput.value = newQuantity;
}
</script>