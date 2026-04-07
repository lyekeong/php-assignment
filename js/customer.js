window.toggleFilterDropdown = function() {
    const menu = document.getElementById("filter-menu");
    if (menu) {
        menu.classList.toggle("show");
    }
};

// Close dropdown if user clicks outside
window.onclick = function(event) {
    if (!event.target.matches('.filter-btn') && !event.target.closest('.filter-menu-content')) {
        var dropdowns = document.getElementsByClassName("filter-menu-content");
        for (var i = 0; i < dropdowns.length; i++) {
            dropdowns[i].classList.remove('show');
        }
    }
}

window.toggleAccordion = function(element) {
    const options = element.nextElementSibling;
    const icon = element.querySelector('i');
    
    // Check current display
    const isFlex = window.getComputedStyle(options).display === 'flex';
    
    if (isFlex) {
        options.style.setProperty('display', 'none', 'important');
        icon.style.transform = "rotate(0deg)";
    } else {
        options.style.setProperty('display', 'flex', 'important');
        icon.style.transform = "rotate(180deg)";
    }
};

function updateQty(id, change) {
    const item = document.querySelector(`[data-cart-id="${id}"]`);
    if (!item) return;

    const input = item.querySelector('.cart-qty-input');
    const variantId = item.getAttribute('data-variant-id'); // Get the DB ID
    const maxStock = parseInt(item.getAttribute('data-max-stock'));
    let currentVal = parseInt(input.value);
    let newVal = currentVal + change;
    
    // 1. Frontend Validation
    if (change > 0 && newVal > maxStock) {
        alert(`Sorry, only ${maxStock} items available.`);
        return; 
    }

    if (newVal <= 0) {
        syncQtyToDatabase(variantId, 0, item);
        return;
    }

    // 2. Sync to Database
    syncQtyToDatabase(variantId, newVal, item, input);
}

// Helper function to handle the Fetch request
function syncQtyToDatabase(variantId, newQty, itemElement, inputElement = null) {
    const formData = new FormData();
    formData.append('variant_id', variantId);
    formData.append('new_qty', newQty);

    fetch('update_cart_qty.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (newQty === 0) {
                itemElement.remove(); // Remove visually if deleted in DB
            } else {
                inputElement.value = newQty; // Update input visually
            }
            updateCartSubtotal();
        } else {
            alert("Error updating cart: " + data.message);
        }
    })
    .catch(err => console.error("Sync Error:", err));
}

function updateCartSubtotal() {
    let subtotal = 0;
    
    // Check if we are on the Cart Page or just using the Drawer
    const isCartPage = document.querySelector('.cart-page-container') !== null;
    const itemClass = isCartPage ? '.bag-item' : '.cart-item';
    
    const items = document.querySelectorAll(itemClass);
    
    items.forEach(item => {
        const qtyInput = item.querySelector('.cart-qty-input');
        const price = parseFloat(item.getAttribute('data-price'));
        const qty = qtyInput ? parseInt(qtyInput.value) : 0;
        if (!isNaN(price) && !isNaN(qty)) {
            subtotal += price * qty;
        }
    });

    if (isCartPage) {
        // Update the big Bag Page Summary
        const subtotalEl = document.getElementById('summary-subtotal');
        const deliveryEl = document.getElementById('summary-delivery');
        const totalEl = document.getElementById('summary-total');

        let delivery = (subtotal > 500 || subtotal === 0) ? 0 : 15.00;

        if (subtotalEl) subtotalEl.innerText = `RM ${subtotal.toFixed(2)}`;
        if (deliveryEl) deliveryEl.innerText = (delivery === 0) ? 'Free' : `RM ${delivery.toFixed(2)}`;
        if (totalEl) totalEl.innerText = `RM ${(subtotal + delivery).toFixed(2)}`;
    } else {
        // Update the Sidebar Drawer Subtotal
        const subtotalEl = document.getElementById('cart-subtotal');
        if (subtotalEl) {
            subtotalEl.innerText = `RM ${subtotal.toLocaleString('en-MY', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        }
    }
}

/**
 * INITIALIZATION & EVENT LISTENERS
 */
document.addEventListener('DOMContentLoaded', function() {
    // Selectors
    const colorTags = document.querySelectorAll('.color-tag');
    const sizePills = document.querySelectorAll('.size-pill');
    const statusContainer = document.getElementById('stock-status-container');
    const stockMessage = document.getElementById('stock-message');
    const colorInput = document.getElementById('selected-color');
    const sizeInput = document.getElementById('selected-size');
    const addToCartBtn = document.getElementById('add-to-cart-btn');

    const addToCartForm = document.getElementById('add-to-cart-form');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartOverlay = document.getElementById('cart-overlay');
    const closeCartBtn = document.getElementById('close-cart-btn');
    const cartItemsContainer = document.getElementById('cart-items-container');

    const termsCheck = document.getElementById('terms-check');
    const checkoutBtn = document.getElementById('checkout-main-btn');

if (termsCheck && checkoutBtn) {
        // This makes sure the button unlocks when the checkbox is clicked
        termsCheck.addEventListener('change', function() {
            checkoutBtn.disabled = !this.checked;
            checkoutBtn.style.opacity = this.checked ? "1" : "0.5";
            checkoutBtn.style.cursor = this.checked ? "pointer" : "not-allowed";
        });
    }
    
    // 1. Color & Size mapping (Handles DB Stock & Variant IDs)
    function updateSizeAvailability(color) {
        // productVariants is passed from PHP as a JSON object
        const colorStock = (typeof productVariants !== 'undefined') ? (productVariants[color] || {}) : {};
        
        sizePills.forEach(pill => {
            const size = pill.getAttribute('data-size');
            const variantData = colorStock[size] || { stock: 0, id: null };

            if (variantData.stock > 0) {
                pill.classList.remove('disabled');
                pill.setAttribute('data-stock', variantData.stock);
                pill.setAttribute('data-variant-id', variantData.id); // Crucial for Database
            } else {
                pill.classList.add('disabled');
                pill.classList.remove('active'); 
                pill.setAttribute('data-stock', 0);
                pill.setAttribute('data-variant-id', '');
            }
        });
        
        // Reset UI when color changes
        if (statusContainer) statusContainer.style.display = 'none';
        if (sizeInput) sizeInput.value = "";
        if (addToCartBtn) {
            addToCartBtn.disabled = true;
            addToCartBtn.innerText = "Select a Size";
        }
    }

    // 2. Click Listeners for selection
    colorTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const selectedColor = this.innerText.trim();
            colorTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            if (colorInput) colorInput.value = selectedColor;
            updateSizeAvailability(selectedColor);
        });
    });

    sizePills.forEach(pill => {
        pill.addEventListener('click', function() {
            if (this.classList.contains('disabled')) return;

            sizePills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            const stockCount = parseInt(this.getAttribute('data-stock'));
            
            // Show stock status pulse
            if (statusContainer && stockMessage) {
                statusContainer.style.display = 'flex';
                if (stockCount <= 3) {
                    statusContainer.classList.add('low-stock');
                    stockMessage.innerText = `Low stock, only ${stockCount} left`;
                } else {
                    statusContainer.classList.remove('low-stock');
                    stockMessage.innerText = `In stock, ready to ship`;
                }
            }

            if (sizeInput) sizeInput.value = this.getAttribute('data-size');
            if (addToCartBtn) {
                addToCartBtn.disabled = false;
                addToCartBtn.innerText = "Add to Cart";
            }
        });
    });

    // 3. Cart Drawer Open/Close
    function openCart() {
        if (cartDrawer && cartOverlay) {
            // Reset Terms checkbox every time cart opens
            if (termsCheck) termsCheck.checked = false;
            if (checkoutBtn) {
                checkoutBtn.disabled = true;
                checkoutBtn.style.opacity = "0.5";
            }
            cartDrawer.classList.add('open');
            cartOverlay.classList.add('open');
        }
    }

    function closeCart() {
        if (cartDrawer && cartOverlay) {
            cartDrawer.classList.remove('open');
            cartOverlay.classList.remove('open');
        }
    }

    if (closeCartBtn) closeCartBtn.addEventListener('click', closeCart);
    if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

    // 4. "Add to Cart" Logic (Frontend + DATABASE STORAGE)
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // 1. Validation
        if (userRole !== 'customer') {
            alert("Please login as a customer to purchase.");
            return;
        }

            const activeSizePill = document.querySelector('.size-pill.active');
            const variantId = activeSizePill ? activeSizePill.getAttribute('data-variant-id') : '';

            if (!variantId) {
                alert("Please select a size.");
                return;
            }

            // --- PART A: STORE TO DATABASE ---
            const formData = new FormData();
            formData.append('variant_id', variantId);

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // --- PART B: UPDATE UI (Only runs if DB saved successfully) ---
                    const productTitle = document.querySelector('.product-title').innerText;
                    const selectedColor = colorInput.value;
                    const selectedSize = sizeInput.value;
                    const productPriceRaw = document.querySelector('.product-price').innerText;
                    const numericPrice = productPriceRaw.replace(/[^\d.]/g, ''); 
                    const maxStock = parseInt(activeSizePill.getAttribute('data-stock'));
                    const productImageSrc = document.getElementById('current-img').src;

                    const itemID = `${productTitle}-${selectedColor}-${selectedSize}`.replace(/\s+/g, '');
                    const existingItem = document.querySelector(`[data-cart-id="${itemID}"]`);

                if (existingItem) {
                    const qtyInput = existingItem.querySelector('.cart-qty-input');
                    const currentQty = parseInt(qtyInput.value);

                    if (currentQty < maxStock) {
                        // FIX: Instead of just changing the value, sync it to the DB!
                        const newQty = currentQty + 1;
                        syncQtyToDatabase(variantId, newQty, existingItem, qtyInput);
                    } else {
                        alert("Maximum stock reached for this size.");
                    }
                    } else {
                        const cartItemHTML = `
                            <div class="cart-item" 
                                data-cart-id="${itemID}" 
                                data-variant-id="${variantId}" 
                                data-max-stock="${maxStock}" 
                                data-price="${numericPrice}">
                                <img src="${productImageSrc}" alt="${productTitle}">
                                <div class="item-details">
                                    <h3>${productTitle}</h3>
                                    <p>Color: ${selectedColor}</p>
                                    <p>Size: ${selectedSize}</p>
                                    <div class="quantity-controls">
                                        <button type="button" class="qty-btn minus" onclick="updateQty('${itemID}', -1)">-</button>
                                        <input type="text" class="cart-qty-input" value="1" readonly>
                                        <button type="button" class="qty-btn plus" onclick="updateQty('${itemID}', 1)">+</button>
                                    </div>
                                </div>
                                <p class="item-price">${productPriceRaw}</p>
                            </div>
                        `;
                        cartItemsContainer.insertAdjacentHTML('beforeend', cartItemHTML);
                    }

                    updateCartSubtotal();
                    openCart();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => console.error("Fetch Error:", err));
        });
    }

    // 5. Checkout Button Guard
    if (termsCheck && checkoutBtn) {
        termsCheck.addEventListener('change', function() {
            checkoutBtn.disabled = !this.checked;
            checkoutBtn.style.opacity = this.checked ? "1" : "0.5";
            checkoutBtn.style.cursor = this.checked ? "pointer" : "not-allowed";
        });
    }

    // Initialize first color view
    if (typeof currentActiveColor !== 'undefined') {
        updateSizeAvailability(currentActiveColor);
    }
});

function toggleCardForm(show) {
    const cardForm = document.getElementById('card-details-form');
    cardForm.style.display = show ? 'flex' : 'none';
    
    // Toggle 'required' attribute based on visibility
    const inputs = cardForm.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        input.required = show;
    });
}

// Ensure it checks the state on page load
document.addEventListener('DOMContentLoaded', () => {
    const isCredit = document.querySelector('input[name="payment_method"]:checked').value === 'Credit Card';
    toggleCardForm(isCredit);
});

function togglePaymentForms(type) {
    // Only two forms remaining now
    const forms = ['form-card', 'form-tng'];
    
    forms.forEach(formId => {
        const element = document.getElementById(formId);
        if (formId === 'form-' + type) {
            element.style.display = 'flex';
            // Enable required for inputs in the active form
            element.querySelectorAll('input').forEach(el => el.required = true);
        } else {
            element.style.display = 'none';
            // Disable required for hidden forms
            element.querySelectorAll('input').forEach(el => el.required = false);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    togglePaymentForms('card');
});

document.addEventListener('input', e => {
    const el = e.target;
    
    // CVV: Only allow 3 digits
    if (el.id === 'card-cvv') {
        el.value = el.value.replace(/[^\d]/g, '').substring(0, 3);
    }
    
    // TNG PIN: Only allow 6 digits
    if (el.id === 'tng-pin') {
        el.value = el.value.replace(/[^\d]/g, '').substring(0, 6);
    }

    // Existing Card Number & Expiry formatting...
    if (el.id === 'card-number') {
        el.value = el.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
    }
    if (el.id === 'card-expiry') {
        el.value = el.value.replace(/[^\d]/g, '').replace(/(.{2})/, '$1/').trim();
    }
});