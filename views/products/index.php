<div class="controls">
    <div class="search-box">
        <input 
            type="text" 
            id="searchInput" 
            placeholder="🔍 Search by name or SKU..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
        >
    </div>
    
    <div class="sort-controls">
        <select id="sortBy">
            <option value="name" <?= ($sortBy ?? 'name') === 'name' ? 'selected' : '' ?>>Sort: Name</option>
            <option value="sku" <?= ($sortBy ?? 'name') === 'sku' ? 'selected' : '' ?>>Sort: SKU</option>
            <option value="stock" <?= ($sortBy ?? 'name') === 'stock' ? 'selected' : '' ?>>Sort: Stock</option>
            <option value="price" <?= ($sortBy ?? 'name') === 'price' ? 'selected' : '' ?>>Sort: Price (BGN)</option>
        </select>
        
        <select id="sortDir">
            <option value="ASC" <?= ($sortDir ?? 'ASC') === 'ASC' ? 'selected' : '' ?>>↑ Ascending</option>
            <option value="DESC" <?= ($sortDir ?? 'ASC') === 'DESC' ? 'selected' : '' ?>>↓ Descending</option>
        </select>
    </div>
</div>

<div id="productsTable">
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 60px; color: #999;">
            <h2>😕 No products found</h2>
            <p>Try adjusting your search criteria</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product Name <?= ($sortBy ?? 'name') === 'name' ? (($sortDir ?? 'ASC') === 'ASC' ? '↑' : '↓') : '' ?></th>
                    <th>SKU <?= ($sortBy ?? 'name') === 'sku' ? (($sortDir ?? 'ASC') === 'ASC' ? '↑' : '↓') : '' ?></th>
                    <th>Stock <?= ($sortBy ?? 'name') === 'stock' ? (($sortDir ?? 'ASC') === 'ASC' ? '↑' : '↓') : '' ?></th>
                    <th>Price (Original)</th>
                    <th>Price in BGN <?= ($sortBy ?? 'name') === 'price' ? (($sortDir ?? 'ASC') === 'ASC' ? '↑' : '↓') : '' ?></th>
                    <th>Price in EUR</th>
                    <th>Qty to Buy</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php
                        $stockClass = 'stock-high';
                        if ($product['stock'] <= 5) $stockClass = 'stock-low';
                        elseif ($product['stock'] <= 15) $stockClass = 'stock-medium';
                    ?>
                    <tr data-product-id="<?= $product['id'] ?>" data-stock="<?= $product['stock'] ?>">
                        <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                        <td><code><?= htmlspecialchars($product['sku']) ?></code></td>
                        <td>
                            <span class="stock-badge <?= $stockClass ?>">
                                <?= $product['stock'] ?> pcs
                            </span>
                        </td>
                        <td>
                            <div class="price-original">
                                <?= number_format($product['price'], 2) ?> 
                                <span class="currency-badge currency-<?= strtolower($product['currency']) ?>">
                                    <?= $product['currency'] ?>
                                </span>
                            </div>
                        </td>
                        <td class="price-converted">
                            <?= number_format($product['price_bgn'], 2) ?> BGN
                        </td>
                        <td class="price-converted">
                            <?= number_format($product['price_eur'], 2) ?> EUR
                        </td>
                        <td>
                            <input 
                                type="number" 
                                class="qty-input" 
                                min="1" 
                                max="<?= $product['stock'] ?>"
                                value="1"
                                data-price-bgn="<?= $product['price_bgn'] ?>"
                                data-price-eur="<?= $product['price_eur'] ?>"
                                data-max="<?= $product['stock'] ?>"
                            >
                            <div class="error-msg"></div>
                        </td>
                        <td class="total-cell">
                            <div class="total-bgn">
                                <?= number_format($product['price_bgn'], 2) ?> BGN
                            </div>
                            <div class="total-eur" style="color: #388e3c; font-size: 13px;">
                                <?= number_format($product['price_eur'], 2) ?> EUR
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="pagination">
            <button id="prevPage" <?= $page <= 1 ? 'disabled' : '' ?>>
                ← Previous
            </button>
            <div class="page-info">
                Page <?= $page ?> of <?= $totalPages ?> 
                (<?= $total ?> products)
            </div>
            <button id="nextPage" <?= $page >= $totalPages ? 'disabled' : '' ?>>
                Next →
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
// Глобални променливи
let currentPage = <?= $page ?>;
let totalPages = <?= $totalPages ?>;

// Quantity Calculator
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty-input')) {
        const input = e.target;
        const row = input.closest('tr');
        const qty = parseInt(input.value) || 0;
        const max = parseInt(input.dataset.max);
        const priceBGN = parseFloat(input.dataset.priceBgn);
        const priceEUR = parseFloat(input.dataset.priceEur);
        const errorMsg = input.nextElementSibling;
        const totalCell = row.querySelector('.total-cell');
        
        // Валидация
        if (qty < 1 || qty > max) {
            input.classList.add('error');
            errorMsg.textContent = qty < 1 
                ? 'Min: 1' 
                : `Max: ${max}`;
            errorMsg.classList.add('show');
            
            totalCell.innerHTML = '<div style="color: #f44336;">Invalid</div>';
        } else {
            input.classList.remove('error');
            errorMsg.classList.remove('show');
            
            const totalBGN = (priceBGN * qty).toFixed(2);
            const totalEUR = (priceEUR * qty).toFixed(2);
            
            totalCell.innerHTML = `
                <div class="total-bgn">${totalBGN} BGN</div>
                <div class="total-eur" style="color: #388e3c; font-size: 13px;">
                    ${totalEUR} EUR
                </div>
            `;
        }
    }
});

// AJAX Search with debounce
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const sortBy = document.getElementById('sortBy');
const sortDir = document.getElementById('sortDir');

function performSearch() {
    const search = searchInput.value;
    const sort = sortBy.value;
    const dir = sortDir.value;
    
    const url = `/api/products/search?search=${encodeURIComponent(search)}&sort=${sort}&dir=${dir}&page=${currentPage}`;
    
    document.getElementById('productsTable').innerHTML = '<div class="loading">⏳ Loading...</div>';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            updateTable(data);
            totalPages = data.totalPages;
            updatePagination();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('productsTable').innerHTML = 
                '<div style="color: red; text-align: center; padding: 40px;">❌ Error loading products</div>';
        });
}

function updateTable(data) {
    if (data.products.length === 0) {
        document.getElementById('productsTable').innerHTML = `
            <div style="text-align: center; padding: 60px; color: #999;">
                <h2>😕 No products found</h2>
                <p>Try adjusting your search criteria</p>
            </div>
        `;
        return;
    }
    
    // Обновяваме сортиране селектите
    const currentSort = sortBy.value;
    const currentDir = sortDir.value;
    
    let html = `
        <table>
            <thead>
                <tr>
                    <th>Product Name ${currentSort === 'name' ? (currentDir === 'ASC' ? '↑' : '↓') : ''}</th>
                    <th>SKU ${currentSort === 'sku' ? (currentDir === 'ASC' ? '↑' : '↓') : ''}</th>
                    <th>Stock ${currentSort === 'stock' ? (currentDir === 'ASC' ? '↑' : '↓') : ''}</th>
                    <th>Price (Original)</th>
                    <th>Price in BGN ${currentSort === 'price' ? (currentDir === 'ASC' ? '↑' : '↓') : ''}</th>
                    <th>Price in EUR</th>
                    <th>Qty to Buy</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    data.products.forEach(product => {
        let stockClass = 'stock-high';
        if (product.stock <= 5) stockClass = 'stock-low';
        else if (product.stock <= 15) stockClass = 'stock-medium';
        
        html += `
            <tr data-product-id="${product.id}" data-stock="${product.stock}">
                <td><strong>${product.name}</strong></td>
                <td><code>${product.sku}</code></td>
                <td>
                    <span class="stock-badge ${stockClass}">
                        ${product.stock} pcs
                    </span>
                </td>
                <td>
                    <div class="price-original">
                        ${parseFloat(product.price).toFixed(2)} 
                        <span class="currency-badge currency-${product.currency.toLowerCase()}">
                            ${product.currency}
                        </span>
                    </div>
                </td>
                <td class="price-converted">
                    ${parseFloat(product.price_bgn).toFixed(2)} BGN
                </td>
                <td class="price-converted">
                    ${parseFloat(product.price_eur).toFixed(2)} EUR
                </td>
                <td>
                    <input 
                        type="number" 
                        class="qty-input" 
                        min="1" 
                        max="${product.stock}"
                        value="1"
                        data-price-bgn="${product.price_bgn}"
                        data-price-eur="${product.price_eur}"
                        data-max="${product.stock}"
                    >
                    <div class="error-msg"></div>
                </td>
                <td class="total-cell">
                    <div class="total-bgn">
                        ${parseFloat(product.price_bgn).toFixed(2)} BGN
                    </div>
                    <div class="total-eur" style="color: #388e3c; font-size: 13px;">
                        ${parseFloat(product.price_eur).toFixed(2)} EUR
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
        
        <div class="pagination">
            <button id="prevPage" ${data.page <= 1 ? 'disabled' : ''}>
                ← Previous
            </button>
            <div class="page-info">
                Page ${data.page} of ${data.totalPages} 
                (${data.total} products)
            </div>
            <button id="nextPage" ${data.page >= data.totalPages ? 'disabled' : ''}>
                Next →
            </button>
        </div>
    `;
    
    document.getElementById('productsTable').innerHTML = html;
    
    // Re-attach pagination handlers
    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            performSearch();
        }
    });
    
    document.getElementById('nextPage').addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            performSearch();
        }
    });
}

function updatePagination() {
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    
    if (prevBtn) {
        prevBtn.disabled = currentPage <= 1;
    }
    
    if (nextBtn) {
        nextBtn.disabled = currentPage >= totalPages;
    }
}

// Search with debounce
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage = 1; // Reset to first page
        performSearch();
    }, 500);
});

// Sort changes
sortBy.addEventListener('change', () => {
    currentPage = 1;
    performSearch();
});

sortDir.addEventListener('change', () => {
    currentPage = 1;
    performSearch();
});

// Pagination handlers (for initial load)
document.getElementById('prevPage')?.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        performSearch();
    }
});

document.getElementById('nextPage')?.addEventListener('click', () => {
    if (currentPage < totalPages) {
        currentPage++;
        performSearch();
    }
});
</script>