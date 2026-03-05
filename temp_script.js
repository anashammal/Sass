
    // --- ط§ظ„ظ…طھط؛ظٹط±ط§طھ ط§ظ„ط¹ط§ظ…ط© ---
    let rowIdx = 0;
    let paymentIdx = 1;
    const storeTaxRates = []; 
    window.productsData = {}; 

    // Currency setup
    const defaultCurrencyCode = "dummy";
    const baseCurrencyId = "dummy";

    // --- ط¹ظ†ط¯ طھط­ظ…ظٹظ„ ط§ظ„طµظپط­ط© ---
    document.addEventListener("DOMContentLoaded", function() {
        console.log("âœ… Main Script Loaded");

        // ط¥ط¹ط¯ط§ط¯ ط¨ط­ط« ط§ظ„ظ…ظˆط±ط¯ظٹظ† (ظ…ط¹ ط§ظ„ط§ط®طھظٹط§ط± ط§ظ„طھظ„ظ‚ط§ط¦ظٹ)
        console.log("Initializing Supplier Search...");
        
        // Currency change listener
        document.getElementById('currency_id').addEventListener('change', function() {
            let selectedCode = this.options[this.selectedIndex].text.split(' â€” ')[0];
            document.querySelectorAll('.currency-label').forEach(el => el.innerText = selectedCode);
            // Optionally, we could show an exchange rate warning here if it's different from base currency
        });

        setupSearch('supplierSearchInput', 'supplierResults', "dummy", function(s) {
            console.log("Supplier Selected:", s);
            document.getElementById('supplierSearchInput').value = s.contact_name || s.company_name;
            document.getElementById('supplierId').value = s.id;
            
            // ًںں¦ًںں¥ًںں© ظ…ظ†ط·ظ‚ ط§ظ„ط±طµظٹط¯ ط§ظ„ط¬ط¯ظٹط¯
            let balance = parseFloat(s.current_balance || 0);
            let displayDiv = document.getElementById('supplierBalanceDisplay');
            document.getElementById('currentSupplierBalance').value = balance;

            if (balance > 0) {
                // ط£ط­ظ…ط±
                displayDiv.innerHTML = `<span class="text-danger fs-3"><i class="fas fa-arrow-down"></i> ظ„ظ‡ ط¹ظ„ظٹظ†ط§: ${formatNum(balance)}</span>`;
            } else if (balance < 0) {
                // ط£ط®ط¶ط±
                displayDiv.innerHTML = `<span class="text-success fs-3"><i class="fas fa-arrow-up"></i> ظ„ظ†ط§ ط¹ظ†ط¯ظ‡: ${formatNum(Math.abs(balance))}</span>`;
            } else {
                // ط£ط²ط±ظ‚
                displayDiv.innerHTML = `<span class="text-primary fs-3">ط§ظ„ط±طµظٹط¯: 0.00</span>`;
            }
            // ظ…ط³ط­ ط§ظ„ط¨ط­ط«
            document.getElementById('supplierResults').style.display = 'none';
        }, true); // true = طھظپط¹ظٹظ„ ط§ظ„ط§ط®طھظٹط§ط± ط§ظ„طھظ„ظ‚ط§ط¦ظٹ ظ„ظ„ظ…ظˆط±ط¯ظٹظ†

        // ط­ط³ط§ط¨ ط§ظ„ط±ط§ط¨ط· ط¯ظٹظ†ط§ظ…ظٹظƒظٹط§ظ‹ ظ…ط¹ ط§ط³طھط®ط¯ط§ظ… Alias ط¬ط¯ظٹط¯ ظ„طھط¬ظ†ط¨ ط§ظ„ط­ط¸ط±
        const basePath = window.location.pathname.split('/store-owner/')[0];
        const searchUrl = `${window.location.origin}${basePath}/store-owner/core/lookup`;
        console.log('Computed Search URL (Safe Alias):', searchUrl);

        setupSearch('productSearch', 'searchResults', searchUrl, function(p) {
            console.log("Product Selected:", p);
            addProductRow(p);
            // طھظپط±ظٹط؛ ط§ظ„ط­ظ‚ظ„
            let input = document.getElementById('productSearch');
            input.value = ''; 
            input.focus();
        }, true);
    // --- ًںں¢ ظƒظˆط¯ ظ…ط±ط§ظ‚ط¨ط© ظ†ط§ظپط°ط© ط¥ط¶ط§ظپط© ط§ظ„ظ…ظˆط±ط¯ ظ„ظ„ط¥ط¶ط§ظپط© ط§ظ„طھظ„ظ‚ط§ط¦ظٹط© ---
        const supplierFrame = document.getElementById('createSupplierFrame');
        if(supplierFrame) {
            supplierFrame.onload = function() {
                try {
                    const newUrl = supplierFrame.contentWindow.location.href;
                    
                    // ط§ظ„طھط­ظ‚ظ‚ ظ…ظ† ط§ظ„ط­ظپط¸ (ط®ط±ظˆط¬ ظ…ظ† طµظپط­ط© create/edit)
                    if (!newUrl.includes('create') && !newUrl.includes('edit')) {
                        console.log("âœ… طھظ… ط­ظپط¸ ط§ظ„ظ…ظˆط±ط¯طŒ ط§ظ„ط±ط§ط¨ط·: " + newUrl);
                        
                        // 1. ط¥ط؛ظ„ط§ظ‚ ط§ظ„ظ…ظˆط¯ط§ظ„
                        var modalEl = document.getElementById('addSupplierModal');
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if(modal) modal.hide();

                        // 2. ط§ط³طھط®ط±ط§ط¬ ID ط§ظ„ظ…ظˆط±ط¯ ظ…ظ† ط§ظ„ط±ط§ط¨ط· (ظ…ط«ط§ظ„: /contacts/50)
                        const match = newUrl.match(/contacts\/(\d+)/);
                        if (match && match[1]) {
                            const newContactId = match[1];
                            
                            // 3. ط¬ظ„ط¨ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظˆط±ط¯ ظˆطھط¹ط¨ط¦ط© ط§ظ„ط­ظ‚ظˆظ„
                            fetch(`dummy?term=${newContactId}`)
                                .then(r => r.json())
                                .then(data => {
                                     let contact = null;
                                     if(Array.isArray(data)) {
                                         contact = data.find(c => c.id == newContactId) || data[0];
                                     } else {
                                         contact = data;
                                     }

                                     if(contact) {
                                         // âœ… طھط¹ط¨ط¦ط© ط§ظ„ط¨ظٹط§ظ†ط§طھ ظپظٹ ط§ظ„ظپط§طھظˆط±ط© ظ…ط¨ط§ط´ط±ط©
                                         document.getElementById('supplierSearchInput').value = contact.contact_name || contact.company_name;
                                         document.getElementById('supplierId').value = contact.id;
                                         
                                         // طھط­ط¯ظٹط« ط§ظ„ط±طµظٹط¯
                                         let balance = parseFloat(contact.current_balance || 0);
                                         let displayDiv = document.getElementById('supplierBalanceDisplay');
                                         document.getElementById('currentSupplierBalance').value = balance;

                                         if (balance > 0) {
                                             displayDiv.innerHTML = `<span class="text-danger fs-3"><i class="fas fa-arrow-down"></i> ظ„ظ‡ ط¹ظ„ظٹظ†ط§: ${formatNum(balance)}</span>`;
                                         } else if (balance < 0) {
                                             displayDiv.innerHTML = `<span class="text-success fs-3"><i class="fas fa-arrow-up"></i> ظ„ظ†ط§ ط¹ظ†ط¯ظ‡: ${formatNum(Math.abs(balance))}</span>`;
                                         } else {
                                             displayDiv.innerHTML = `<span class="text-primary fs-3">ط§ظ„ط±طµظٹط¯: 0.00</span>`;
                                         }

                                         if(typeof toastr !== 'undefined') toastr.success('طھظ… ط§ط®طھظٹط§ط± ط§ظ„ظ…ظˆط±ط¯ ط§ظ„ط¬ط¯ظٹط¯ طھظ„ظ‚ط§ط¦ظٹط§ظ‹');
                                     }
                                })
                                .catch(err => console.error('ط®ط·ط£ ظپظٹ ط¬ظ„ط¨ ط§ظ„ظ…ظˆط±ط¯', err));
                        }
                    }
                } catch (e) {
                    console.log('Access restricted (Cross-origin) or loading...');
                }
            };
        }
    // true = طھظپط¹ظٹظ„ ط§ظ„ط§ط®طھظٹط§ط± ط§ظ„طھظ„ظ‚ط§ط¦ظٹ ظ„ظ„ظ…ظ†طھط¬ط§طھ
// --- ًںں¢ ظƒظˆط¯ ظ…ط±ط§ظ‚ط¨ط© ظ†ط§ظپط°ط© ط¥ط¶ط§ظپط© ط§ظ„ظ…ظ†طھط¬ ظ„ظ„ط¥ط¶ط§ظپط© ط§ظ„طھظ„ظ‚ط§ط¦ظٹط© ظ„ظ„ظپط§طھظˆط±ط© ---
        const frame = document.getElementById('createProductFrame');
        if(frame) {
            frame.onload = function() {
                try {
                    // ظ‚ط±ط§ط،ط© ط§ظ„ط±ط§ط¨ط· ط§ظ„ط­ط§ظ„ظٹ ط¯ط§ط®ظ„ ط§ظ„ظ€ iframe
                    const newUrl = frame.contentWindow.location.href;
                    
                    // ط¥ط°ط§ طھط؛ظٹط± ط§ظ„ط±ط§ط¨ط· ظˆظ„ظ… ظٹط¹ط¯ ظپظٹ طµظپط­ط© ط§ظ„ط¥ظ†ط´ط§ط، (create) ط£ظˆ ط§ظ„طھط¹ط¯ظٹظ„ (edit)
                    // ظپظ‡ط°ط§ ظٹط¹ظ†ظٹ ط£ظ† ط§ظ„ظ…ط³طھط®ط¯ظ… ط¶ط؛ط· ط­ظپط¸ ظˆطھظ… طھط­ظˆظٹظ„ظ‡
                    if (!newUrl.includes('create') && !newUrl.includes('edit')) {
                        console.log("âœ… طھظ… ط§ظ„ط­ظپط¸ ط¨ظ†ط¬ط§ط­طŒ ط§ظ„ط±ط§ط¨ط· ط§ظ„ط¬ط¯ظٹط¯: " + newUrl);
                        
                        // 1. ط¥ط؛ظ„ط§ظ‚ ط§ظ„ظ…ظˆط¯ط§ظ„
                        var myModalEl = document.getElementById('quickProductModal');
                        var modal = bootstrap.Modal.getInstance(myModalEl);
                        if(modal) modal.hide();

                        // 2. ظ…ط­ط§ظˆظ„ط© ط§ط³طھط®ط±ط§ط¬ ID ط§ظ„ظ…ظ†طھط¬ ظ…ظ† ط§ظ„ط±ط§ط¨ط· (ظ…ط«ط§ظ„: /products/15)
                        const match = newUrl.match(/products\/(\d+)/);
                        if (match && match[1]) {
                            const newProductId = match[1];
                            
                            // 3. ط¬ظ„ط¨ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظ†طھط¬ ط§ظ„ط¬ط¯ظٹط¯ ظˆط¥ط¶ط§ظپطھظ‡ ظ„ظ„ط¬ط¯ظˆظ„
                            fetch(`dummy?term=${newProductId}`)
                                .then(r => r.json())
                                .then(data => {
                                     // ط§ظ„طھط£ظƒط¯ ظ…ظ† ط£ظ† ط§ظ„ظ†طھظٹط¬ط© ظ…طµظپظˆظپط© ط£ظˆ ظƒط§ط¦ظ†
                                     let product = null;
                                     if(Array.isArray(data)) {
                                         // ط§ظ„ط¨ط­ط« ط¹ظ† ط§ظ„ظ…ظ†طھط¬ ط§ظ„ط°ظٹ ظٹط·ط§ط¨ظ‚ ط§ظ„ظ€ ID
                                         product = data.find(p => p.id == newProductId) || data[0];
                                     } else {
                                         product = data;
                                     }

                                     if(product) {
                                         addProductRow(product); // ط¥ط¶ط§ظپط© ظ„ظ„طµظپ
                                         
                                         // طھظ†ط¨ظٹظ‡ ظ†ط¬ط§ط­ (ط§ط®طھظٹط§ط±ظٹ)
                                         if(typeof toastr !== 'undefined') toastr.success('طھظ… ط¥ط¶ط§ظپط© ط§ظ„ظ…ظ†طھط¬ ط§ظ„ط¬ط¯ظٹط¯ ظ„ظ„ظپط§طھظˆط±ط©');
                                         else alert('طھظ… ط¥ط¶ط§ظپط© ط§ظ„ظ…ظ†طھط¬ ط§ظ„ط¬ط¯ظٹط¯ ظ„ظ„ظپط§طھظˆط±ط© ط¨ظ†ط¬ط§ط­!');
                                     }
                                })
                                .catch(err => console.error('ط®ط·ط£ ظپظٹ ط¬ظ„ط¨ ط§ظ„ظ…ظ†طھط¬ ط§ظ„ط¬ط¯ظٹط¯', err));
                        }
                    }
                } catch (e) {
                    console.log('ظ„ط§ ظٹظ…ظƒظ† ط§ظ„ظˆطµظˆظ„ ظ„ظ…ط­طھظˆظ‰ ط§ظ„ط¥ط·ط§ط± ط¨ط³ط¨ط¨ ط³ظٹط§ط³ط§طھ ط§ظ„ط£ظ…ط§ظ† (Cross-origin) ط£ظˆ ظ„ظ… ظٹطھظ… ط§ظ„طھط­ظ…ظٹظ„ ط¨ط¹ط¯.');
                }
            };
        }

        // --- ًںں¢ ظƒظˆط¯ ظ…ط±ط§ظ‚ط¨ط© ظ†ط§ظپط°ط© ط¥ط¶ط§ظپط© ط§ظ„ظˆط¬ط¨ط© ظ„ظ„ظ…ط·ط§ط¹ظ… ---
        const mealFrame = document.getElementById('createMealFrame');
        if(mealFrame) {
            mealFrame.onload = function() {
                try {
                    const newUrl = mealFrame.contentWindow.location.href;
                    if (!newUrl.includes('create') && !newUrl.includes('edit')) {
                        console.log("âœ… طھظ… ط­ظپط¸ ط§ظ„ظˆط¬ط¨ط© ط¨ظ†ط¬ط§ط­طŒ ط§ظ„ط±ط§ط¨ط· ط§ظ„ط¬ط¯ظٹط¯: " + newUrl);
                        
                        var mealModalEl = document.getElementById('quickMealModal');
                        var modal = bootstrap.Modal.getInstance(mealModalEl);
                        if(modal) modal.hide();

                        // ط§ظ„ظˆط¬ط¨ط§طھ ظˆط§ظ„ظ…ظƒظˆظ†ط§طھ طھط¹ط§ظ…ظ„ ظƒظ…ظ†طھط¬ط§طھ ظپظٹ ط§ظ„ظپط§طھظˆط±ط©
                        // ظ†ط­ط§ظˆظ„ ط§ط³طھط®ط±ط§ط¬ ID
                        const match = newUrl.match(/meals\/(\d+)/);
                        // ط£ظˆ products ط¥ط°ط§ ظƒط§ظ† ظ…ظƒظˆظ†ط§ظ‹ ط®ط§ظ…ط§ظ‹ ظˆطھظ… طھط­ظˆظٹظ„ظ‡ ظ„طµظپط­ط© ط§ظ„ظ…ظ†طھط¬ط§طھ (ظٹط¹طھظ…ط¯ ط¹ظ„ظ‰ ط§ظ„ظ†ط¸ط§ظ…)
                        // ظ„ظƒظ† ظ„ظ†ظپطھط±ط¶ ط£ظ†ظ‡ ط³ظٹط¹ظˆط¯ ظ„طµظپط­ط© ط§ظ„ظˆط¬ط¨ط§طھ ط£ظˆ ط§ظ„ظ…ظ†طھط¬ط§طھ.
                        // ظپظٹ ظ†ط¸ط§ظ…ظƒطŒ ط§ظ„ظˆط¬ط¨ط§طھ ظ‚ط¯ طھظƒظˆظ† ظپظٹ ط¬ط¯ظˆظ„ products ط£ظٹط¶ط§ظ‹ ط£ظˆ ظ…ظ†ظپطµظ„ط©.
                        // ط¥ط°ط§ ظƒط§ظ†طھ ظپظٹ products ظپط§ظ„ط±ط§ط¨ط· ط³ظٹظƒظˆظ† products/id. 
                        // ط¥ط°ط§ ظƒط§ظ†طھ meals/idطŒ ظپظ†ط­طھط§ط¬ endpoint ظ„ظ„ط¨ط­ط« ط¹ظ†ظ‡ط§.
                        // ظ„ظƒظ†ظƒ ظ‚ظ„طھ "ظˆط¬ط¨ط© ط£ظˆ ظ…ظƒظˆظ† ط®ط§ظ…"طŒ ظˆظƒظ„ط§ظ‡ظ…ط§ ظٹط®ط²ظ†ط§ظ† ظƒظ…ظ†طھط¬ط§طھ ط¹ط§ط¯ط©ظ‹.
                        
                        let newId = null;
                        if (match && match[1]) newId = match[1];
                        else {
                             const matchProd = newUrl.match(/products\/(\d+)/);
                             if (matchProd && matchProd[1]) newId = matchProd[1];
                        }

                        if (newId) {
                            fetch(`dummy?term=${newId}`) // ظ†ط³طھط®ط¯ظ… ظ†ظپط³ ط§ظ„ط¨ط­ط« ظ„ط£ظ† ط§ظ„ظˆط¬ط¨ط§طھ ظ…ظ†طھط¬ط§طھ
                                .then(r => r.json())
                                .then(data => {
                                     let item = null;
                                     if(Array.isArray(data)) item = data.find(p => p.id == newId) || data[0];
                                     else item = data;

                                     if(item) {
                                         addProductRow(item);
                                         if(typeof toastr !== 'undefined') toastr.success('طھظ… ط¥ط¶ط§ظپط© ط§ظ„ظˆط¬ط¨ط©/ط§ظ„ظ…ظƒظˆظ† ظ„ظ„ظپط§طھظˆط±ط©');
                                     }
                                })
                                .catch(err => console.error('ط®ط·ط£ ظپظٹ ط¬ظ„ط¨ ط§ظ„ظˆط¬ط¨ط©', err));
                        }
                    }
                } catch (e) {
                    console.log('Access restricted or loading...');
                }
            };
        }
    });

    // --- ط¯ظˆط§ظ„ ظ…ط³ط§ط¹ط¯ط© ---
    function parseMoney(value) {
        if (!value) return 0;
        let clean = String(value).replace(/[^0-9.]/g, ''); 
        return parseFloat(clean) || 0;
    }

    function formatNum(num) { 
        if (num === null || num === undefined || num === '') return 0;
        let val = parseFloat(num) || 0;
        return parseFloat(val.toFixed(4)); 
    }

    // --- ط¯ط§ظ„ط© ط¥ط¶ط§ظپط© طµظپ ط§ظ„ظ…ظ†طھط¬ ط§ظ„ظ…طµط­ط­ط© ---
    function addProductRow(product) {
        if (!product || !product.units || product.units.length === 0) {
            console.error("Product has no units or is null:", product);
            return;
        }
        document.getElementById('emptyState').style.display = 'none';
        window.productsData[rowIdx] = product;

        // طھط­ط¯ظٹط¯ ط§ظ„ظˆط­ط¯ط© ط§ظ„ط§ظپطھط±ط§ط¶ظٹط©
        let selectedUnitId = product.scanned_unit_id;
        if (!selectedUnitId) {
            let base = product.units.find(u => u.is_base_unit == 1) || product.units[0];
            selectedUnitId = base ? base.id : null;
        }
        if (!selectedUnitId) return;

        // ط­ط³ط§ط¨ط§طھ ط§ظ„طھظƒظ„ظپط© (طھط¹ط¯ظٹظ„: ط§ظ„ط§ط¹طھظ…ط§ط¯ ط¹ظ„ظ‰ ط³ط¹ط± ط§ظ„ظˆط­ط¯ط© ط§ظ„ظ…ط®طھط§ط±ط© ط£ظˆظ„ط§ظ‹)
        let selectedUnit = product.units.find(u => u.id == selectedUnitId);
        let selectedFactor = (selectedUnit.is_base_unit) ? 1 : (parseFloat(selectedUnit.conversion_factor) || 1);
        
        // 1. ط­ط³ط§ط¨ ط§ظ„طھظƒظ„ظپط© ط¨ظ†ط§ط،ظ‹ ط¹ظ„ظ‰ ط§ظ„ط¹ظ…ظ„ط© (ط¬ط¯ظٹط¯)
        let preferredPrice = parseFloat(selectedUnit.cost_price) || parseFloat(selectedUnit.purchase_price) || 0;
        let pCurrId = selectedUnit.purchase_currency_id || baseCurrencyId;
        let pRate = (pCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.purchase_exchange_rate) || parseFloat(selectedUnit.store_custom_purchase_rate) || ratesMap[pCurrId]?.exchange_rate || 1);
        
        // ×”×ھظƒظ„ظپط© ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© (TRY)
        let priceInBase = preferredPrice * pRate;
        // ط¬ظ„ط¨ ط³ط¹ط± طµط±ظپ ط§ظ„ظپط§طھظˆط±ط©
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        
        // ط­ط³ط§ط¨ ط§ظ„طھظƒظ„ظپط© ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© - ظ‡ظٹ ظ…ط§ ط³ظٹطھظ… ط¥ط¯ط®ط§ظ„ظ‡ ظپظٹ ط§ظ„ط­ظ‚ظ„ ط§ظ„ط£ط­ظ…ط±
        let calculatedCost = priceInBase / invRate; 

        // 2. ط¥ط°ط§ ظƒط§ظ†طھ طµظپط±طŒ ظ†ط­ط§ظˆظ„ ط§ط³طھظ†طھط§ط¬ظ‡ط§ ظ…ظ† ط£ظƒط¨ط± ظˆط­ط¯ط© (ط§ظ„ظ…ظ†ط·ظ‚ ط§ظ„ط§ط­طھظٹط§ط·ظٹ)
        if (calculatedCost === 0) {
            let maxUnit = product.units.reduce((prev, curr) => (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr);
            let maxUnitCost = parseFloat(maxUnit.cost_price) || parseFloat(maxUnit.purchase_price) || 0;
            let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
            
            let mCurrId = maxUnit.purchase_currency_id || baseCurrencyId;
            let mRate = (mCurrId == baseCurrencyId) ? 1 : (parseFloat(maxUnit.purchase_exchange_rate) || parseFloat(maxUnit.store_custom_purchase_rate) || ratesMap[mCurrId]?.exchange_rate || 1);
            
            // طھظƒظ„ظپط© ط§ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط© ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط©
            calculatedCost = (maxUnitCost * mRate / maxFactor) * selectedFactor;
        }

        // 3. ط­ط³ط§ط¨ طھظƒظ„ظپط© ط§ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط© (ط¨ط§ظ„ظ„ظٹط±ط©) ظ„ظ„ط§ط³طھط®ط¯ط§ظ… ظپظٹ ط¨ط§ظ‚ظٹ ط§ظ„ظˆط­ط¯ط§طھ
        let trueBaseCost = (selectedFactor > 0) ? (calculatedCost / selectedFactor) : 0;
        
        let initialBarcode = selectedUnit.barcode || '-';
        
        // --- طھطµط­ظٹط­ طھط­ظˆظٹظ„ ط³ط¹ط± ط§ظ„ط¨ظٹط¹ ط¥ظ„ظ‰ ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© ---
        let rawSellPrice = parseFloat(selectedUnit.sale_price) || 0;
        let sCurrId = selectedUnit.sell_currency_id || baseCurrencyId;
        let sRate = (sCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.sell_exchange_rate) || parseFloat(selectedUnit.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
        // ط³ط¹ط± ط§ظ„ط¨ظٹط¹ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© (TRY)
        let sellInBase = rawSellPrice * sRate;
        // ط³ط¹ط± ط§ظ„ط¨ظٹط¹ ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© - ظ‡ظˆ ظ…ط§ ط³ظٹطھظ… ط¥ط¯ط®ط§ظ„ظ‡ ظپظٹ ط§ظ„ط­ظ‚ظ„ ط§ظ„ط£ط®ط¶ط±
        let sellPrice = sellInBase / invRate;

        let profitPercent = (calculatedCost > 0) ? ((sellPrice - calculatedCost) / calculatedCost) * 100 : 0;
        
        let imgUrl = selectedUnit.image_url || product.main_image;
        let taxOptionsHtml = storeTaxRates.map(rate => `<option value="${rate}">${rate}%</option>`).join('');

        const tr = document.createElement('tr');
        tr.id = `row_${rowIdx}`;
        tr.className = "align-middle";

        let optionsHtml = product.units.filter(u => u.is_purchase == 1).map(u => {
            let isBase = u.is_base_unit == 1;
            let safeFactor = isBase ? 1 : (parseFloat(u.conversion_factor) || 1);
            let mathPrice = trueBaseCost * safeFactor;

            // طھط­ظˆظٹظ„ ط³ط¹ط± ط§ظ„ط¨ظٹط¹ ظ„ظƒظ„ ظˆط­ط¯ط© ط£ظٹط¶ط§ظ‹
            let rSell = parseFloat(u.sale_price) || 0;
            let rCurrId = u.sell_currency_id || baseCurrencyId;
            let rRate = (rCurrId == baseCurrencyId) ? 1 : (ratesMap[rCurrId]?.exchange_rate || 1);
            let rSellInBase = rSell * rRate;
            let rSellInInv = rSellInBase / invRate; // ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©

            let rProfit = (mathPrice > 0) ? ((rSellInInv - mathPrice) / mathPrice) * 100 : 0;

            return `<option value="${u.id}" 
                    data-barcode="${u.barcode || '-'}" 
                    data-price="${mathPrice.toFixed(4)}" 
                    data-sell="${formatNum(rSellInInv)}" 
                    data-profit="${formatNum(rProfit)}"
                    data-factor="${safeFactor}" 
                    data-img="${u.image_url || ''}" 
                    ${u.id == selectedUnitId ? 'selected' : ''}>
                    ${u.unit_name}
                    </option>`;
        }).join('');

        // ط¨ظ†ط§ط، ط§ظ„طµظپ (HTML) ط¨ط´ظƒظ„ طµط­ظٹط­ ط¨ط¯ظˆظ† طھظƒط±ط§ط± ط£ظˆ ظ‚ط·ط¹
        tr.innerHTML = `
            <td class="col-shrink">
                <div class="d-flex flex-column align-items-center gap-1">
                    <button type="button" class="btn btn-xs btn-info text-white p-0" style="width:18px; height:18px; font-size:9px;" onclick="toggleDetails(${rowIdx})"><i class="fas fa-chevron-down"></i></button>
                    <img src="${imgUrl}" id="img_${rowIdx}" class="product-thumb" alt="">
                </div>
            </td>
            <td class="product-col">
                <input type="hidden" name="items[${rowIdx}][product_id]" value="${product.id}">
                <div class="fw-bold small">${product.name}</div>
            </td>
            <td class="col-shrink"><input type="text" class="form-control form-control-sm text-center bg-white barcode-display input-barcode" id="barcode_${rowIdx}" value="${initialBarcode}" readonly></td>
            <td class="col-shrink">
                <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm unit-select input-unit" onchange="updateRowData(${rowIdx})">${optionsHtml}</select>
            </td>
            <td class="col-shrink"><input type="text" inputmode="decimal" name="items[${rowIdx}][quantity]" class="form-control form-control-sm text-center qty input-qty" value="1" oninput="calcTotals(${rowIdx})" onfocus="this.select()"></td>
            <td class="col-shrink">
                <input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price text-danger fw-bold input-price" value="${parseFloat(calculatedCost.toFixed(4))}" oninput="syncSubUnits(${rowIdx}, 'purchase')" onfocus="this.select()">
                <div class="cost-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1; color: black !important;">
                   <span class="cost-original d-block text-muted"></span>
                   <span class="cost-base d-block text-info fw-bold"></span>
                </div>
            </td>
            <td class="col-shrink"><input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary input-profit" value="${formatNum(profitPercent)}" oninput="calcSellPrice(${rowIdx})" onfocus="this.select()"></td>
            <td class="col-shrink">
                <div class="input-group input-group-sm discount-group input-discount">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="0" oninput="calcTotals(${rowIdx})">
                    <select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" onchange="calcTotals(${rowIdx})">
                        <option value="fixed" class="currency-label">dummy</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </td>
            <td class="col-shrink">
                <input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control form-control-sm text-center sell text-success fw-bold input-price" value="${formatNum(sellPrice)}" oninput="calcProfitPercent(${rowIdx})" onfocus="this.select()">
                <div class="sell-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1; color: black !important;">
                   <span class="sell-original d-block text-muted"></span>
                   <span class="sell-base d-block text-info fw-bold"></span>
                </div>
                <div class="main-warning-container mt-1" style="min-height:18px;"></div>
            </td>

            <td class="col-shrink">
                <input type="date" name="items[${rowIdx}][expiry_date]" class="form-control form-control-sm text-center px-1 input-expiry" title="طھط§ط±ظٹط® ط§ظ„ط§ظ†طھظ‡ط§ط،">
            </td>

            <td class="col-shrink">
                <input type="number" name="items[${rowIdx}][alert_days]" class="form-control form-control-sm text-center text-danger fw-bold px-1" style="width: 50px;" value="10" placeholder="10" title="dummy">
            </td>
            
            <td class="col-shrink"><select name="items[${rowIdx}][tax]" class="form-select form-select-sm tax bg-warning bg-opacity-10" onchange="calcTotals(${rowIdx})">${taxOptionsHtml}</select></td>
            <td class="col-shrink"><input type="text" class="form-control form-control-sm text-center bg-light fw-bold total input-total" readonly></td>
            <td class="col-shrink text-center"><button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="removeRow(${rowIdx})"><i class="fas fa-times"></i></button></td>
        `;
        
        document.getElementById('tableBody').appendChild(tr);

        // طµظپ ط§ظ„طھظپط§طµظٹظ„ ط§ظ„ظ…ط®ظپظٹط© (ظ…ظ‚ط³ظ… ظ„ط¹ظ…ظˆط¯ظٹظ†: ظˆط­ط¯ط§طھ + ط³ط¬ظ„)
        const detailsTr = document.createElement('tr');
        detailsTr.id = `details_${rowIdx}`;
        detailsTr.style.display = 'none';
        detailsTr.className = "bg-light";
        detailsTr.innerHTML = `
            <td colspan="14">
                <div class="p-3 border rounded bg-white">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-sitemap"></i> طھط­ط¯ظٹط« ط§ظ„ظˆط­ط¯ط§طھ ط§ظ„ظ…ط±طھط¨ط·ط©</h6>
                            <div id="related_units_container_${rowIdx}"></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-success mb-2"><i class="fas fa-history"></i> ط³ط¬ظ„ ط¢ط®ط± 5 ظ…ط´طھط±ظٹط§طھ</h6>
                            <div id="history_container_${rowIdx}" class="small">
                                <div class="text-center text-muted p-2"><i class="fas fa-spinner fa-spin"></i> ط¬ط§ط±ظٹ ط§ظ„ط¬ظ„ط¨...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>`;
        document.getElementById('tableBody').appendChild(detailsTr);

        renderRelatedUnits(rowIdx, selectedUnitId);
        renderHistory(rowIdx, product.id); // âœ… ط¬ظ„ط¨ ط§ظ„ط³ط¬ظ„
        updateDualPriceDisplay(rowIdx); // âœ… طھط­ط¯ظٹط« ط¹ط±ط¶ ط§ظ„ط¹ظ…ظ„طھظٹظ†
        calcTotals(rowIdx); 
        rowIdx++;
    }

    // ًںں¢ ط¯ط§ظ„ط© ط¬ظ„ط¨ ظˆط±ط³ظ… ط§ظ„ط³ط¬ظ„
    function renderHistory(idx, productId) {
        console.log(`[History] Fetching for Product ID: ${productId} (Row: ${idx})`);

        if (!productId) {
            console.error("[History] Product ID is missing!");
            document.getElementById(`history_container_${idx}`).innerHTML = '<span class="text-danger">ظ…ط¹ط±ظپ ط§ظ„ظ…ظ†طھط¬ ظ…ظپظ‚ظˆط¯</span>';
            return;
        }

        // ط§ط³طھط®ط¯ط§ظ… ط±ط§ط¨ط· ظ…ط¨ط§ط´ط± ظ„ظ„ظ‚ط¶ط§ط، ط¹ظ„ظ‰ ظ…ط´ط§ظƒظ„ ط§ظ„ظ€ replacement
        // ظ†ط³طھط®ط¯ظ… ط§ظ„ط±ط§ط¨ط· ط§ظ„ط£ط³ط§ط³ظٹ ط«ظ… ظ†ط¶ظٹظپ ط§ظ„ظ€ ID
        let baseUrl = "dummy";
        let url = baseUrl.replace(':id', productId);
        
        console.log(`[History] Request URL: ${url}`);

        fetch(url)
            .then(async res => {
                if (!res.ok) {
                    throw new Error("HTTP Status: " + res.status);
                }
                return res.json();
            })
            .then(data => {
                console.log(`[History] Data received:`, data);
                let container = document.getElementById(`history_container_${idx}`);
                if (data.length === 0) {
                    container.innerHTML = '<div class="alert alert-secondary p-1 m-0 text-center">ظ„ط§ ظٹظˆط¬ط¯ ط³ط¬ظ„ ظ…ط´طھط±ظٹط§طھ ط³ط§ط¨ظ‚</div>';
                    return;
                }

                let html = `
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ط§ظ„طھط§ط±ظٹط®</th>
                                <th> dummy </th>
                                <th>ط§ظ„ظˆط­ط¯ط©</th>
                                <th>ط§ظ„ط³ط¹ط±</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                data.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.date}</td>
                            <td class="text-truncate" style="max-width: 100px;" title="${item.supplier}">${item.supplier}</td>
                            <td>${item.unit} (${item.qty})</td>
                            <td class="fw-bold">${formatNum(item.price)}</td>
                        </tr>`;
                });

                html += '</tbody></table>';
                container.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById(`history_container_${idx}`).innerHTML = '<span class="text-danger">ط®ط·ط£ ظپظٹ ط¬ظ„ط¨ ط§ظ„ط³ط¬ظ„</span>';
            });
    }

    function updateRowData(idx) {
        let row = document.getElementById(`row_${idx}`);
        let select = row.querySelector('.unit-select');
        let opt = select.options[select.selectedIndex];
        
        let unitId = opt.value;
        let product = window.productsData[idx];

        let price = parseFloat(opt.getAttribute('data-price')) || 0;
        let barcode = opt.getAttribute('data-barcode');
        let sell = parseFloat(opt.getAttribute('data-sell')) || 0;
        let profit = parseFloat(opt.getAttribute('data-profit')) || 0;
        let imgUrl = opt.getAttribute('data-img');

        row.querySelector('.barcode-display').value = barcode;
        row.querySelector('.price').value = parseFloat(price.toFixed(4)); 
        row.querySelector('.sell').value = formatNum(sell);
        row.querySelector('.profit').value = formatNum(profit);
        
        let imgTag = document.getElementById(`img_${idx}`);
        if(imgTag && imgUrl) {
            imgTag.src = imgUrl;
        } else if (imgTag) {
            imgTag.src = product.main_image; 
        }

        renderRelatedUnits(idx, unitId);
        // ط¹ظ†ط¯ طھط؛ظٹظٹط± ط§ظ„ظˆط­ط¯ط© ظ„ط§ ظ†ط؛ظٹط± ط£ط³ط¹ط§ط± ط¨ط§ظ‚ظٹ ط§ظ„ظˆط­ط¯ط§طھطŒ ظپظ‚ط· ظ†ط¹ظٹط¯ ط±ط³ظ…ظ‡ط§
        updateDualPriceDisplay(idx);
        calcTotals(idx);
    }

    // ًںں¢ ط¹ط±ط¶ ط§ظ„ط³ط¹ط± ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£طµظ„ظٹط© ظˆظ…ط§ ظٹط¹ط§ط¯ظ„ظ‡ط§ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط©
    function updateDualPriceDisplay(idx) {
        let row = document.getElementById(`row_${idx}`);
        if (!row) return;
        
        let product = window.productsData[idx];
        let select = row.querySelector('.unit-select');
        let selectedUnitId = select.value;
        let unit = product.units.find(u => u.id == selectedUnitId);
        
        let invoiceCurrId = document.getElementById('currency_id').value;
        let bCode = "dummy";

        // 1. ظ…ط¹ط§ظ„ط¬ط© ط³ط¹ط± ط§ظ„ط´ط±ط§ط،
        let costOriginal = row.querySelector('.cost-original');
        let costBase = row.querySelector('.cost-base');
        
        if (unit && unit.purchase_currency_id) {
            let pPrice = parseFloat(unit.cost_price) || parseFloat(unit.purchase_price) || 0;
            let pSym = unit.purchase_currency_symbol || unit.purchase_currency_code || '';
            
            if (unit.purchase_currency_id == invoiceCurrId) {
                costOriginal.innerText = `(${pSym} ${pPrice})`;
                costBase.innerText = '';
            } else {
                costOriginal.innerText = `${pSym} ${pPrice}`;
                let bRate = (unit.purchase_currency_id == baseCurrencyId) ? 1 : (ratesMap[unit.purchase_currency_id]?.exchange_rate || 1);
                costBase.innerText = `= ${formatNum(pPrice * bRate)} ${bCode}`;
            }
        }

        // 2. ظ…ط¹ط§ظ„ط¬ط© ط³ط¹ط± ط§ظ„ط¨ظٹط¹
        let sellOriginal = row.querySelector('.sell-original');
        let sellBase = row.querySelector('.sell-base');

        if (unit && (unit.sell_currency_id || unit.sell_price_currency_id)) {
            let sCurrId = unit.sell_price_currency_id || unit.sell_currency_id;
            let sPrice = parseFloat(unit.selling_price) || parseFloat(unit.sale_price) || 0;
            let sSym = unit.sell_currency_symbol || unit.sell_currency_code || '';

            if (sCurrId == invoiceCurrId) {
                sellOriginal.innerText = `(${sSym} ${sPrice})`;
                sellBase.innerText = '';
            } else {
                sellOriginal.innerText = `${sSym} ${sPrice}`;
                let bRate = (sCurrId == baseCurrencyId) ? 1 : (ratesMap[sCurrId]?.exchange_rate || 1);
                sellBase.innerText = `= ${formatNum(sPrice * bRate)} ${bCode}`;
            }
        }
    }

    function renderRelatedUnits(idx, currentUnitId) {
        let product = window.productsData[idx];
        let container = document.getElementById(`related_units_container_${idx}`);
        container.innerHTML = '';

        let row = document.getElementById(`row_${idx}`);
        let mainPrice = parseFloat(row.querySelector('.price').value) || 0;
        let select = row.querySelector('.unit-select');
        let currentFactor = parseFloat(select.options[select.selectedIndex].getAttribute('data-factor')) || 1;
        let trueBaseCost = (currentFactor > 0) ? (mainPrice / currentFactor) : 0;

        let html = '';
        product.units.forEach(u => {
            if (u.id == currentUnitId) return; 

            let isBase = (u.is_base_unit == 1);
            let safeFactor = isBase ? 1 : (parseFloat(u.conversion_factor) || 1);
            let calculatedCost = trueBaseCost * safeFactor; 
            let rawSell = parseFloat(u.sale_price) || 0;
            let sCurrId = u.sell_currency_id || "dummy";
            let sRate = (sCurrId == "dummy") ? 1 : (parseFloat(u.sell_exchange_rate) || parseFloat(u.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
            
            let sInBase = rawSell * sRate;
            let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
            let uSell = sInBase / invRate;

            // ط¬ظ„ط¨ ط§ظ„ط±ط¨ط­ ط§ظ„ط£طµظ„ظٹ ظ…ظ† ظ‚ط§ط¹ط¯ط© ط§ظ„ط¨ظٹط§ظ†ط§طھ ظ„ظ„ظ…ظ‚ط§ط±ظ†ط©
            let originalProfit = parseFloat(u.profit_percent) || 0; 
            
            let uProfit = 0;
            if(calculatedCost > 0) uProfit = ((uSell - calculatedCost) / calculatedCost) * 100;
            
            let uImg = u.image_url || product.main_image;

            html += `
                <div class="row g-2 align-items-center mb-2 related-unit-row border-bottom pb-2" 
                     data-unit-id="${u.id}" 
                     data-factor="${safeFactor}"
                     data-original-profit="${originalProfit}"> dummy
                    
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][price]" class="hidden-sub-cost" value="${calculatedCost.toFixed(4)}">
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][selling_price]" class="hidden-sub-sell" value="${uSell}">
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][profit_percent]" class="hidden-sub-profit" value="${formatNum(uProfit)}">

                    <div class="col-md-2 d-flex align-items-center">
                        <img src="${uImg}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <div>
                            <span class="badge bg-secondary">${u.unit_name}</span>
                            <small class="d-block text-muted" style="font-size: 0.75rem;">(x${safeFactor})</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"> dummy </span>
                            <input type="text" class="form-control text-center bg-light sub-cost text-danger fw-bold" 
                                   value="${formatNum(calculatedCost)}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">ط±ط¨ط­ %</span>
                            <input type="text" inputmode="decimal" class="form-control text-center sub-profit" value="${formatNum(uProfit)}" oninput="calcSubUnitSell(this)" onfocus="this.select()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"> dummy </span>
                            <input type="text" inputmode="decimal" class="form-control text-center fw-bold sub-sell text-success" 
                                   value="${formatNum(uSell)}" oninput="calcSubUnitProfit(this)" onfocus="this.select()">
                        </div>
                        dummy
                        <div class="warning-container mt-1" style="min-height:20px;"></div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    }

   // ًںڑ€ طھط­ط¯ظٹط« ط°ظƒظٹ ظ„ظ„ظˆط­ط¯ط§طھ (طھظ… ط§ظ„طھط¹ط¯ظٹظ„ ظ„طھط­ط¯ظٹط« ط±ط¨ط­ ط§ظ„ظˆط­ط¯ط© ط§ظ„ط­ط§ظ„ظٹط© ط£ظٹط¶ط§ظ‹)
   function syncSubUnits(idx, source) {
        calcTotals(idx);
        let row = document.getElementById(`row_${idx}`);
        
        let mainPrice = parseFloat(row.querySelector('.price').value) || 0; 
        
        // طھط­ط¯ظٹط« ط±ط¨ط­ ط§ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط© ظˆط§ظ„طھط­ط°ظٹط± ط§ظ„ط®ط§طµ ط¨ظ‡ط§
        let mainSellInput = row.querySelector('.sell');
        let mainProfitInput = row.querySelector('.profit');
        let currentSell = parseFloat(mainSellInput.value) || 0;
        
        // ط¬ظ„ط¨ ط§ظ„ط±ط¨ط­ ط§ظ„ط£طµظ„ظٹ ظ„ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط©
        let select = row.querySelector('.unit-select');
        let selectedOption = select.options[select.selectedIndex];
        let originalMainProfit = parseFloat(selectedOption.getAttribute('data-profit')) || 0;

        let newMainProfit = 0;
        if (mainPrice > 0) {
            newMainProfit = ((currentSell - mainPrice) / mainPrice) * 100;
            mainProfitInput.value = formatNum(newMainProfit);
        }

        // ًںں¢ طھط­ط¯ظٹط« طھط­ط°ظٹط± ط§ظ„ظˆط­ط¯ط© ط§ظ„ط£ط³ط§ط³ظٹط© (ط¨ط¯ظˆظ† ط¥ط·ط§ط±)
        let mainWarningDiv = row.querySelector('.main-warning-container');
        mainWarningDiv.innerHTML = ''; 

        if (newMainProfit <= 0) {
            mainWarningDiv.innerHTML = `<span class="text-danger flash-warning">ط®ط³ط§ط±ط© âڑ ï¸ڈ</span>`;
        } 
        else if (newMainProfit < originalMainProfit - 0.1) {
            mainWarningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">ًں“‰ ط§ظ†ط®ظپط§ط¶ ط§ظ„ط±ط¨ط­</span>`;
        }

        // ---------------------------------------------------------

        let currentFactor = parseFloat(selectedOption.getAttribute('data-factor')) || 1;
        let costPerPiece = (currentFactor > 0) ? (mainPrice / currentFactor) : 0;

        let container = document.getElementById(`related_units_container_${idx}`);
        if(container) {
            container.querySelectorAll('.related-unit-row').forEach(subRow => {
                let subFactor = parseFloat(subRow.getAttribute('data-factor')) || 1;
                let originalSubProfit = parseFloat(subRow.getAttribute('data-original-profit')) || 0;
                
                let newSubCost = costPerPiece * subFactor;
                
                subRow.querySelector('.sub-cost').value = formatNum(newSubCost);
                subRow.querySelector('.hidden-sub-cost').value = newSubCost.toFixed(4);

                let currentSubSell = parseFloat(subRow.querySelector('.sub-sell').value) || 0;
                let newSubProfit = 0;
                if(newSubCost > 0) {
                    newSubProfit = ((currentSubSell - newSubCost) / newSubCost) * 100;
                }
                
                subRow.querySelector('.sub-profit').value = formatNum(newSubProfit);
                subRow.querySelector('.hidden-sub-profit').value = formatNum(newSubProfit);

                // ًںں¢ طھط­ط¯ظٹط« طھط­ط°ظٹط± ط§ظ„ظˆط­ط¯ط§طھ ط§ظ„ظپط±ط¹ظٹط© (ط¨ط¯ظˆظ† ط¥ط·ط§ط±)
                let subWarningDiv = subRow.querySelector('.warning-container');
                subWarningDiv.innerHTML = '';

                if (newSubProfit <= 0) {
                    subWarningDiv.innerHTML = `<span class="text-danger flash-warning">ط®ط³ط§ط±ط© âڑ ï¸ڈ</span>`;
                } 
                else if (newSubProfit < originalSubProfit - 0.1) {
                    subWarningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">ًں“‰ ط§ظ†ط®ظپط§ط¶ ط§ظ„ط±ط¨ط­</span>`;
                }
            });
        }
    }

    // ط¯ظˆط§ظ„ ط§ظ„ط­ط³ط§ط¨ ظ„ظ„ظˆط­ط¯ط§طھ ط§ظ„ظپط±ط¹ظٹط© (ظ…ط³طھظ‚ظ„ط© ظ„ظƒظ„ ظˆط­ط¯ط©)
    function calcSubUnitSell(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let profit = parseFloat(input.value) || 0;
        
        // طھط؛ظٹظٹط± ط§ظ„ط±ط¨ط­ -> ظٹط؛ظٹط± ط³ط¹ط± ط§ظ„ط¨ظٹط¹
        let sell = cost * (1 + profit / 100);
        row.querySelector('.sub-sell').value = formatNum(sell);
        row.querySelector('.hidden-sub-sell').value = sell.toFixed(2);
        row.querySelector('.hidden-sub-profit').value = profit;
        
        let warning = row.querySelector('.warning-msg');
        if(warning) warning.style.display = (profit <= 0) ? 'block' : 'none';
    }

    function calcSubUnitProfit(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let sell = parseFloat(input.value) || 0;

        let hiddenSell = row.querySelector('.hidden-sub-sell');
        if(hiddenSell) hiddenSell.value = sell;

        let newProfit = 0;
        if (cost > 0) {
            newProfit = ((sell - cost) / cost) * 100;
            row.querySelector('.sub-profit').value = formatNum(newProfit);
        }
        
        let hiddenProfit = row.querySelector('.hidden-sub-profit'); // طھط£ظƒط¯ ظ…ظ† ظˆط¬ظˆط¯ ظ‡ط°ط§ ط§ظ„ط­ظ‚ظ„ ط§ظ„ظ…ط®ظپظٹ
        if(hiddenProfit) hiddenProfit.value = newProfit; // طھط­ط¯ظٹط« ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ظ…ط®ظپظٹط© ظ„ظ„ط±ط¨ط­

        // ًںں¢ ظ…ظ†ط·ظ‚ ط¥ط®ظپط§ط،/ط¥ط¸ظ‡ط§ط± ط§ظ„طھط­ط°ظٹط± ظ„ط­ط¸ظٹط§ظ‹ ط¹ظ†ط¯ ط§ظ„ظƒطھط§ط¨ط© ظ„ظ„ظˆط­ط¯ط§طھ ط§ظ„ظپط±ط¹ظٹط©
        let originalProfit = parseFloat(row.getAttribute('data-original-profit')) || 0;
        let warningDiv = row.querySelector('.warning-container');
        
        warningDiv.innerHTML = ''; // ظ…ط³ط­ ط§ظ„ظ‚ط¯ظٹظ…

        if (newProfit <= 0) {
            warningDiv.innerHTML = `<span class="text-danger flash-warning">ط®ط³ط§ط±ط© âڑ ï¸ڈ</span>`;
        } 
        else if (newProfit < originalProfit - 0.1) {
            warningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">ًں“‰ ط§ظ†ط®ظپط§ط¶ ط§ظ„ط±ط¨ط­</span>`;
        }
    }

    // --- Search Logic ---
    function setupSearch(inputId, resultsId, url, onSelect, autoSelect = false) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        if(!input) return;

        let debounce;
        input.addEventListener('input', function() {
            clearTimeout(debounce);
            const term = this.value.trim();
            if(term.length < 1) { results.style.display='none'; return; }

            debounce = setTimeout(() => {
                fetch(`${url}?term=${term}`).then(r => r.json()).then(data => {
                    results.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach((item, index) => {
                            let div = document.createElement('a');
                            div.className = 'list-group-item list-group-item-action cursor-pointer';
                            div.innerHTML = item.contact_name || `${item.name} - <small>${item.sku || ''}</small>`;
                            div.onclick = function() { onSelect(item); results.style.display = 'none'; };
                            results.appendChild(div);
                            
                            // ط§ظ„ط§ط®طھظٹط§ط± ط§ظ„طھظ„ظ‚ط§ط¦ظٹ ط¥ط°ط§ ظƒط§ظ† ظ‡ظ†ط§ظƒ ظ†طھظٹط¬ط© ظˆط§ط­ط¯ط© ظپظ‚ط· (ط§ط®طھظٹط§ط±ظٹ)
                            if (autoSelect && data.length === 1 && term === item.sku) {
                                onSelect(item);
                                results.style.display = 'none';
                            }
                        });
                        results.style.display = 'block';
                    } else { results.style.display = 'none'; }
                });
            }, 300);
        });
        
        document.addEventListener("click", function (e) { 
            if (e.target !== input && e.target !== results) results.style.display = 'none'; 
        });
    }



    // --- ط¨ظ‚ظٹط© ط§ظ„ط¯ظˆط§ظ„ (calcTotals, removeRow, etc...) ط¶ط±ظˆط±ظٹ طھظƒظˆظ† ظ…ظˆط¬ظˆط¯ط© ---
    function calcTotals(idx) { 
        let row = document.getElementById(`row_${idx}`);
        if(!row) return;
        let qty = parseMoney(row.querySelector('.qty').value);
        let priceInvoice = parseMoney(row.querySelector('.price').value);
        let invoiceRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;

        let taxRate = parseMoney(row.querySelector('.tax').value);
        let discountVal = parseMoney(row.querySelector('.discount').value);
        let discountType = row.querySelector('.discount-type').value;

        // ط§ظ„ط­ط³ط§ط¨ط§طھ ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© ظ„ط£ظ† ط§ظ„ط£ط³ط¹ط§ط± ط£طµط¨ط­طھ ط¨ظ‡ط§
        let subTotalInv = qty * priceInvoice;
        let discountAmtInv = (discountType === 'percent') ? (subTotalInv * discountVal / 100) : discountVal;
        let afterDiscountInv = subTotalInv - discountAmtInv;
        let taxAmtInv = afterDiscountInv * (taxRate / 100);
        let finalTotalInv = afterDiscountInv + taxAmtInv;

        // ط­ظپط¸ ط¥ط¬ظ…ط§ظ„ظٹ ط§ظ„طµظپ ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© ظ„ط³ظ‡ظˆظ„ط© ط­ط³ط§ط¨ ط§ظ„ظ…ط¬ظ…ظˆط¹ ط§ظ„ظƒظ„ظٹ ظ„ط§ط­ظ‚ط§ظ‹
        row.setAttribute('data-total-invoice', finalTotalInv.toFixed(6));

        // ط¹ط±ط¶ ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© ظƒظ…ط§ ط·ظ„ط¨ ط§ظ„ظ…ط³طھط®ط¯ظ…
        let finalTotalBase = finalTotalInv * invoiceRate;
        row.querySelector('.total').value = formatNum(finalTotalBase);
        
        updateDualPriceDisplay(idx);
        calculateGrandTotal();
    }

    // ًںں¢ ط¯ط§ظ„ط© ط¥ط¹ط§ط¯ط© ط­ط³ط§ط¨ ظƒط§ظپط© ط§ظ„طµظپظˆظپ ط¹ظ†ط¯ طھط؛ظٹظٹط± ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©
    function recalculateAllRows() {
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        
        Object.keys(window.productsData).forEach(idx => {
            let row = document.getElementById(`row_${idx}`);
            if (!row) return;

            let product = window.productsData[idx];
            let select = row.querySelector('.unit-select');
            let selectedUnitId = select.value;
            let selectedUnit = product.units.find(u => u.id == selectedUnitId);
            
            if (!selectedUnit) return;

            let selectedFactor = (selectedUnit.is_base_unit) ? 1 : (parseFloat(selectedUnit.conversion_factor) || 1);
            
            // 1. ط­ط³ط§ط¨ ط§ظ„طھظƒظ„ظپط© ط¨ظ†ط§ط،ظ‹ ط¹ظ„ظ‰ ط§ظ„ط³ط¹ط± ط§ظ„ظ…ظپط¶ظ„ ظپظٹ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظ†طھط¬
            let preferredPrice = parseFloat(selectedUnit.cost_price) || parseFloat(selectedUnit.purchase_price) || 0;
            let pCurrId = selectedUnit.purchase_currency_id || "dummy";
            let pRate = (pCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.purchase_exchange_rate) || parseFloat(selectedUnit.store_custom_purchase_rate) || ratesMap[pCurrId]?.exchange_rate || 1);
            
            let priceInBase = preferredPrice * pRate;
            let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
            let calculatedCost = priceInBase / invRate;

            // 2. ظ…ط¹ط§ظ„ط¬ط© ط§ظ„ط­ط§ظ„ط© ط§ظ„ط§ط­طھظٹط§ط·ظٹط© (ط¥ط°ط§ ظƒط§ظ† ط§ظ„ط³ط¹ط± طµظپط±)
            if (calculatedCost === 0) {
                let maxUnit = product.units.reduce((prev, curr) => (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr);
                let maxUnitCost = parseFloat(maxUnit.cost_price) || parseFloat(maxUnit.purchase_price) || 0;
                let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
                let mCurrId = maxUnit.purchase_currency_id || baseCurrencyId;
                let mRate = (mCurrId == baseCurrencyId) ? 1 : (parseFloat(maxUnit.purchase_exchange_rate) || parseFloat(maxUnit.store_custom_purchase_rate) || ratesMap[mCurrId]?.exchange_rate || 1);
                
                let basePriceFallback = (maxUnitCost * mRate / maxFactor) * selectedFactor;
                calculatedCost = basePriceFallback / invRate;
            }

            // 3. طھط­ط¯ظٹط« ط­ظ‚ظ„ ط³ط¹ط± ط§ظ„ط´ط±ط§ط، ظپظٹ ط§ظ„طµظپ ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©
            row.querySelector('.price').value = parseFloat(calculatedCost.toFixed(4));

            // 4. طھط­ط¯ظٹط« ط³ط¹ط± ط§ظ„ط¨ظٹط¹ ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©
            let rawSellPrice = parseFloat(selectedUnit.sale_price) || 0;
            let sCurrId = selectedUnit.sell_currency_id || baseCurrencyId;
            let sRate = (sCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.sell_exchange_rate) || parseFloat(selectedUnit.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
            
            let sellInBase = rawSellPrice * sRate;
            let sellPrice = sellInBase / invRate;
            row.querySelector('.sell').value = formatNum(sellPrice);

            // 5. ظ†ط³ط¨ط© ط§ظ„ط±ط¨ط­
            let newProfitPercent = (calculatedCost > 0) ? ((sellPrice - calculatedCost) / calculatedCost) * 100 : 0;
            row.querySelector('.profit').value = formatNum(newProfitPercent);

            // 6. طھط­ط¯ظٹط« ظ‚ظٹظ… ط§ظ„ظ€ data attributes (ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©)
            let trueBaseCost = (selectedFactor > 0) ? (calculatedCost / selectedFactor) : 0;
            Array.from(select.options).forEach(opt => {
                let uId = opt.value;
                let u = product.units.find(ux => ux.id == uId);
                if (u) {
                    let fac = (u.is_base_unit) ? 1 : (parseFloat(u.conversion_factor) || 1);
                    let mPrice = trueBaseCost * fac;
                    let rs = parseFloat(u.sale_price) || 0;
                    let rci = u.sell_currency_id || baseCurrencyId;
                    let rr = (rci == baseCurrencyId) ? 1 : (parseFloat(u.sell_exchange_rate) || parseFloat(u.store_custom_sell_rate) || ratesMap[rci]?.exchange_rate || 1);
                    let rsiBase = rs * rr; 
                    let rsi = rsiBase / invRate; // ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©
                    let rp = (mPrice > 0) ? ((rsi - mPrice) / mPrice) * 100 : 0;

                    opt.setAttribute('data-price', mPrice.toFixed(4));
                    opt.setAttribute('data-sell', formatNum(rsi));
                    opt.setAttribute('data-profit', formatNum(rp));
                }
            });

            // 7. طھط­ط¯ظٹط« ط§ظ„ظˆط­ط¯ط§طھ ط§ظ„ظپط±ط¹ظٹط© (ط§ظ„طھظپط§طµظٹظ„)
            renderRelatedUnits(idx, selectedUnitId);
            
            // 8. طھط­ط¯ظٹط« ط§ظ„ط¹ط±ط¶ ط§ظ„ظ…ط²ط¯ظˆط¬ ظˆط§ظ„ط¥ط¬ظ…ط§ظ„ظٹ
            updateDualPriceDisplay(idx);
            calcTotals(idx);
        });
    }

    // ًںں¢ ط¹ط±ط¶ ط§ظ„ط³ط¹ط± ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£طµظ„ظٹط© ظˆظ…ط§ ظٹط¹ط§ط¯ظ„ظ‡ط§ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط©
    function updateDualPriceDisplay(idx) {
        let row = document.getElementById(`row_${idx}`);
        if (!row) return;
        
        let product = window.productsData[idx];
        let select = row.querySelector('.unit-select');
        let selectedUnitId = select.value;
        let unit = product.units.find(u => u.id == selectedUnitId);
        
        // 1. ط¬ظ„ط¨ ط§ظ„ظ‚ظٹظ… ط§ظ„ط­ط§ظ„ظٹط© ظ…ظ† ط§ظ„ط­ظ‚ظˆظ„ (ط§ظ„ط¢ظ† ط£طµط¨ط­طھ ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©)
        let priceInvoice = parseFloat(row.querySelector('.price').value) || 0;
        let sellInvoice = parseFloat(row.querySelector('.sell').value) || 0;

        // 2. ط§ظ„طھط­ظˆظٹظ„ ظ„ظ„ظٹط±ط© ط§ظ„طھط±ظƒظٹط© / ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط©
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        let priceBase = priceInvoice * invRate;
        let sellBase = sellInvoice * invRate;
        let baseCurrCode = "dummy";

        // ًںں¢ ط³ط¹ط± ط§ظ„ط´ط±ط§ط، ط§ظ„ط¥ط¶ط§ظپظٹ
        let costOriginal = row.querySelector('.cost-original');
        let costBaseEl = row.querySelector('.cost-base');
        
        // ط§ظ„ط³ط¹ط± ط¨ط§ظ„ط£طµظ„ظٹ (ظٹظˆط±ظˆ ظ…ط«ظ„ط§ظ‹)
        if (unit && unit.purchase_currency_id) {
            let pPrice = parseFloat(unit.cost_price) || parseFloat(unit.purchase_price) || 0;
            let pSym = unit.purchase_currency_symbol || unit.purchase_currency_code || '';
            costOriginal.innerText = `${pSym} ${pPrice}`;
        }
        // ط§ظ„ط³ط¹ط± ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© (TRY)
        if(costBaseEl) costBaseEl.innerText = `${formatNum(priceBase)} ${baseCurrCode}`;

        // ًںں¢ ط³ط¹ط± ط§ظ„ط¨ظٹط¹ ط§ظ„ط¥ط¶ط§ظپظٹ
        let sellOriginal = row.querySelector('.sell-original');
        let sellBaseEl = row.querySelector('.sell-base');

        // ط§ظ„ط³ط¹ط± ط¨ط§ظ„ط£طµظ„ظٹ
        if (unit && unit.sell_currency_id) {
            let sPrice = parseFloat(unit.sale_price) || 0;
            let sSym = unit.sell_currency_symbol || unit.sell_currency_code || '';
            sellOriginal.innerText = `${sSym} ${sPrice}`;
        }
        // ط§ظ„ط³ط¹ط± ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© (TRY)
        if(sellBaseEl) sellBaseEl.innerText = `${formatNum(sellBase)} ${baseCurrCode}`;
    }
    
    function removeRow(idx) {
        let row = document.getElementById(`row_${idx}`);
        let details = document.getElementById(`details_${idx}`);
        if (row) row.remove();
        if (details) details.remove();
        delete window.productsData[idx];
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let invoiceRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        let baseCurrCode = "dummy";

        // 1. ط­ط³ط§ط¨ ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹ ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© ط£ظˆظ„ط§ظ‹
        let subTotalInvoice = 0;
        document.querySelectorAll('tr[id^="row_"]').forEach(row => {
            subTotalInvoice += parseFloat(row.getAttribute('data-total-invoice')) || 0;
        });

        // 2. طھط­ظˆظٹظ„ ظˆط¹ط±ط¶ ط§ظ„ظ…ط¬ظ…ظˆط¹ ط§ظ„ظپط±ط¹ظٹ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© 
        let subTotalBase = subTotalInvoice * invoiceRate;
        document.getElementById('subTotalDisplay').innerHTML = `${formatNum(subTotalBase)} <span class="fs-6 text-muted">(${formatNum(subTotalInvoice)} Invoice)</span>`;

        // 3. طھط·ط¨ظٹظ‚ ط§ظ„ط®طµظ… (ظٹط¯ط®ظ„ ط§ظ„ظ…ط³طھط®ط¯ظ… ط§ظ„ط®طµظ… ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط©)
        let discountInput = parseMoney(document.getElementById('discountInput').value);
        let grandTotalInvoice = subTotalInvoice - discountInput; // ط§ظپطھط±ط§ط¶ط§ظ‹ ط§ظ„ط®طµظ… ظ‡ظ†ط§ fixed ط¨ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© ط¨ط§ظ„ظ…ط¬ظ…ظ„
        
        // 4. طھط­ظˆظٹظ„ ظˆط¹ط±ط¶ ط§ظ„طµط§ظپظٹ ط§ظ„ظ†ظ‡ط§ط¦ظٹ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط©
        let grandTotalBase = grandTotalInvoice * invoiceRate;
        document.getElementById('grandTotalDisplay').innerHTML = `${formatNum(grandTotalBase)} <span class="fs-6 text-muted">(${formatNum(grandTotalInvoice)} Invoice)</span>`;
        // ظ†ط­طھظپط¸ ط¨ظ‡ط°ظ‡ ط§ظ„ظ‚ظٹظ… ظ„ظ„ط¥ط³طھط®ط¯ط§ظ… ظپظٹ ط§ظ„ط¯ظپط¹ط§طھ ظ„ط§ط­ظ‚ط§ظ‹
        document.getElementById('grandTotalDisplay').setAttribute('data-grand-total', grandTotalBase);

        // ط­ط³ط§ط¨ ظ…ط¬ظ…ظˆط¹ ط§ظ„ظ…ط¯ظپظˆط¹ط§طھ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط©
        let totalPaid = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let amountInput = row.querySelector('.payment-input');
            let rateInp = row.querySelector('.rate-input'); // ظ‡ط°ط§ ظ„ظ„ط¹ط±ط¶ ظپظ‚ط· (ط§ظ„ظ…ط¹ط§ط¯ظ„)
            let hiddenRate = row.querySelector('.pay-rate-hidden'); // ظ‡ط°ط§ ط³ط¹ط± ط§ظ„طµط±ظپ ط§ظ„ط­ظ‚ظٹظ‚ظٹ
            let currSel = row.querySelector('.currency-select');
            if (!amountInput) return;
            let amount = parseMoney(amountInput.value);
            let isBase = currSel ? (currSel.options[currSel.selectedIndex]?.dataset?.isBase === '1') : true;
            let rate = hiddenRate ? (parseMoney(hiddenRate.value) || 1) : 1;
            
            let amtInBase = isBase ? amount : (rate > 0 ? amount * rate : 0);
            totalPaid += amtInBase;

            // طھط­ط¯ظٹط« ط­ظ‚ظ„ "ط§ظ„ظ…ط¹ط§ط¯ظ„" ظپظٹ ط§ظ„ظˆط§ط¬ظ‡ط©
            if (rateInp && !isBase) {
                rateInp.value = formatNum(amtInBase);
            }
        });
        
        const diff = parseFloat((totalPaid - grandTotalInBase).toFixed(2));
        const balDiv = document.getElementById('balanceAlert');
        const balLbl = document.getElementById('balanceLabel');
        const balAmt = document.getElementById('balanceAmount');
        const baseCurrCode = "dummy";

        balDiv.style.display = 'block';
        if (Math.abs(diff) < 0.01) {
            balDiv.className = 'alert p-2 text-center fw-bold alert-success';
            balLbl.innerText = 'ط®ط§ظ„طµ (طھظ… ط§ظ„ط¯ظپط¹ ط¨ط§ظ„ظƒط§ظ…ظ„)';
            balAmt.innerText = '';
        } else if (diff < 0) {
            balDiv.className = 'alert p-2 text-center fw-bold alert-danger';
            balLbl.innerText = 'ظ…طھط¨ظ‚ظٹ (ط¹ظ„ظٹظƒ):';
            balAmt.innerText = formatNum(Math.abs(diff)) + " " + baseCurrCode;
        } else {
            balDiv.className = 'alert p-2 text-center fw-bold alert-info';
            balLbl.innerText = 'ط±طµظٹط¯ (ظ„ظƒ):';
            balAmt.innerText = formatNum(diff) + " " + baseCurrCode;
        }
    }

    // ط¹ظ†ط¯ طھط؛ظٹظٹط± ط§ظ„ط¹ظ…ظ„ط© ظپظٹ طµظپ ط§ظ„ط¯ظپط¹
    // ظ…ط²ط§ظ…ظ†ط© ط³ط¹ط± ط§ظ„طµط±ظپ ظ…ط¹ ط§ظ„ط­ظ‚ظ„ ط§ظ„ظ…ط®ظپظٹ ظ„ظ„ظ€ form
    function syncRateHidden(rateInp) {
        let row = rateInp.closest('.payment-row');
        let hidden = row ? row.querySelector('.pay-rate-hidden') : null;
        if (hidden) hidden.value = parseFloat(rateInp.value) || 1;
    }

    // ط£ط³ط¹ط§ط± ط§ظ„طµط±ظپ ط§ظ„ظ…ط­ظ…ظ„ط© ظ…ط³ط¨ظ‚ط§ظ‹ ظ…ظ† ط§ظ„ط®ط§ط¯ظ…
    const preloadedRates = [];
    const ratesMap = {};
    preloadedRates.forEach(c => { ratesMap[c.id] = c; });

    function onInvoiceCurrencyChange(select) {
        let opt = select.options[select.selectedIndex];
        let currId = select.value;
        let currCode = opt.dataset.code;
        let baseCurrId = "dummy";
        let baseCurrCode = "dummy";

        // Update labels in UI (Priority: Symbol > Code)
        let label = opt.dataset.symbol || currCode;
        document.querySelectorAll('.currency-label').forEach(el => el.innerText = label);

        if (currId == baseCurrId) {
            document.getElementById('invoice_exchange_rate').value = 1;
            document.getElementById('invoice_rate_info').classList.add('d-none');
            calculateGrandTotal();
            return;
        }

        let currData = ratesMap[currId];
        let suggestedRate = currData ? parseFloat(currData.exchange_rate).toFixed(6) : '1.000000';

        Swal.fire({
            title: `ًں’± ط³ط¹ط± طµط±ظپ ط§ظ„ظپط§طھظˆط±ط©: ${currCode} â†” ${baseCurrCode}`,
            icon: 'info',
            html: `
                <div class="text-start mb-3">
                    <label class="form-label fw-bold">âœڈï¸ڈ ط³ط¹ط± طµط±ظپ (1 ${currCode} = طں ${baseCurrCode}):</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white fw-bold">1 ${currCode}</span>
                        <input type="text" inputmode="decimal" id="swalInvoiceRate" class="form-control text-center fw-bold fs-5" value="${suggestedRate}">
                        <span class="input-group-text fw-bold">${baseCurrCode}</span>
                    </div>
                </div>
            `,
            confirmButtonText: 'âœ… طھط£ظƒظٹط¯',
            showCancelButton: true,
            cancelButtonText: 'â‌Œ ط¥ظ„ط؛ط§ط،',
            preConfirm: () => {
                let val = parseFloat(document.getElementById('swalInvoiceRate').value);
                if (!val || val <= 0) {
                    Swal.showValidationMessage('âڑ ï¸ڈ ظٹط±ط¬ظ‰ ط¥ط¯ط®ط§ظ„ ط³ط¹ط± طµط±ظپ طµط­ظٹط­');
                    return false;
                }
                return val;
            }
        }).then(result => {
            if (result.isConfirmed) {
                let rate = result.value;
                document.getElementById('invoice_exchange_rate').value = rate;
                document.getElementById('selected_curr_code').innerText = currCode;
                document.getElementById('selected_curr_rate').innerText = rate;
                document.getElementById('invoice_rate_info').classList.remove('d-none');
                
                // طھط­ط¯ظٹط« ط§ظ„ظ…ط¨ط§ظ„ط؛ ط§ظ„ظ…ط¯ظپظˆط¹ط© ط§ظ„ظ…ظ‚طھط±ط­ط© ط¥ط°ط§ ظƒط§ظ†طھ ط§ظ„ظپط§طھظˆط±ط© ظپط§ط±ط؛ط© ط£ظˆ ط§ظ„ظ…طھط¨ظ‚ظٹ ظƒط¨ظٹط±
                recalculateAllRows();
                calculateGrandTotal();
            } else {
                select.value = baseCurrId;
                document.getElementById('invoice_exchange_rate').value = 1;
                document.getElementById('invoice_rate_info').classList.add('d-none');
                document.querySelectorAll('.currency-label').forEach(el => el.innerText = baseCurrCode);
                recalculateAllRows();
                calculateGrandTotal();
            }
        });
    }

    function updatePayRate(row, rate, currCode) {
        let rateRow = row.querySelector('.rate-row');
        let rateInput = row.querySelector('.rate-input');
        let hiddenInput = row.querySelector('.pay-rate-hidden');
        let noteSpan = row.querySelector('.rate-note');
        let baseCurrCode = 'dummy';

        if (hiddenInput) hiddenInput.value = rate;
        
        if (rate == 1) {
            if (rateRow) rateRow.classList.add('d-none');
        } else {
            if (rateRow) rateRow.classList.remove('d-none');
            // ظ…ظ„ط§ط­ط¸ط©: ط§ظ„ط­ط³ط§ط¨ ط§ظ„ظپط¹ظ„ظٹ ظٹطھظ… ظپظٹ calculateGrandTotal
            if (noteSpan) noteSpan.innerText = `1 ${currCode} = ${rate} ${baseCurrCode}`;
        }
    }

    function onPayCurrencyChange(select, idx) {
        let row = select.closest('.payment-row');
        let rateInput = row.querySelector('.rate-input');
        let selectedOpt = select.options[select.selectedIndex];
        let isBase = selectedOpt.dataset.isBase === '1';
        let currCode = selectedOpt.text.trim();
        let currId   = select.value;
        let baseCurrCode = 'dummy';
        let baseCurrId = 'dummy';

        // 1. ط­ط³ط§ط¨ ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹ ظˆط§ظ„ظ…طھط¨ظ‚ظٹ ط¨ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© (ظ…ط±ط¬ط¹ ظ…ظˆط­ط¯ ظ„ظƒط§ظپط© ط§ظ„ط­ط§ظ„ط§طھ)
        const invoiceId = document.getElementById('currency_id').value;
        const invoiceRateVal = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        const grandTotalInInvoice = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
        const grandTotalInBase = grandTotalInInvoice * invoiceRateVal;

        let otherPaidBase = 0;
        document.querySelectorAll('.payment-row').forEach(r2 => {
            if (r2 === row) return;
            let ai = r2.querySelector('.payment-input');
            let hr = r2.querySelector('.pay-rate-hidden');
            let cs = r2.querySelector('.currency-select');
            if (!ai) return;
            let amt = parseMoney(ai.value);
            let isB = cs ? (cs.options[cs.selectedIndex]?.dataset?.isBase === '1') : true;
            let r = hr ? (parseMoney(hr.value) || 1) : 1;
            otherPaidBase += isB ? amt : (r > 0 ? amt * r : 0);
        });
        
        let remainingInBase = grandTotalInBase - otherPaidBase;
        if (remainingInBase < 0.0001) remainingInBase = 0;
        const amountInput = row.querySelector('.payment-input');

        // 2. ط­ط§ظ„ط© ط§ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© (ظ…ط«ظ„ ط§ظ„ظ„ظٹط±ط© ط§ظ„طھط±ظƒظٹط©)
        if (isBase) {
            let hiddenCurr = row.querySelector('.pay-currency-id');
            if (hiddenCurr) hiddenCurr.value = currId;
            let hiddenRate = row.querySelector('.pay-rate-hidden');
            if (hiddenRate) hiddenRate.value = 1;

            if (rateInput) rateInput.value = 1;
            let rateRow = row.querySelector('.rate-row');
            if (rateRow) rateRow.classList.add('d-none');

            if (amountInput && remainingInBase > 1e-6) {
                amountInput.value = formatNum(remainingInBase);
            } else if (amountInput) {
                amountInput.value = 0;
            }

            calculateGrandTotal();
            return;
        }

        // 3. ط­ط§ظ„ط© ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© (ظ†ظپط³ ط³ط¹ط± ط§ظ„طµط±ظپ طھظ„ظ‚ط§ط¦ظٹط§ظ‹)
        if (currId == invoiceId) {
            let hiddenCurr = row.querySelector('.pay-currency-id');
            if (hiddenCurr) hiddenCurr.value = currId;
            
            updatePayRate(row, invoiceRateVal, currCode);

            let remInPaymentCurr = remainingInBase / invoiceRateVal;
            if (amountInput && remInPaymentCurr > 1e-6) {
                amountInput.value = formatNum(remInPaymentCurr);
            } else if (amountInput) {
                amountInput.value = 0;
            }

            calculateGrandTotal();
            return;
        }

        // 4. ط­ط§ظ„ط© ط§ظ„ط¹ظ…ظ„ط§طھ ط§ظ„ط£ط®ط±ظ‰ (طھط·ظ„ط¨ ط³ط¹ط± طµط±ظپ ظˆطھط¸ظ‡ط± ظ†ط§ظپط°ط© ظ…ظ†ط¨ط«ظ‚ط©)
        let currData = ratesMap[currId];
        let suggestedRate = currData ? parseFloat(currData.exchange_rate).toFixed(6) : '1.000000';

        Swal.fire({
            title: `ًں’± ط³ط¹ط± ط§ظ„طµط±ظپ: ${baseCurrCode} â†” ${currCode}`,
            icon: 'info',
            width: '36rem',
            html: `
                <div class="text-start mb-3">
                    <label class="form-label text-muted small">ًں“، ط§ظ„ط³ط¹ط± ط§ظ„ظ…ط³طھظˆط±ط¯ (ط§ظ„ظ…ظ‚طھط±ط­):</label>
                    <div class="input-group mb-1">
                        <span class="input-group-text bg-light fw-bold">1 ${currCode}</span>
                        <input type="number" id="swalSuggestedRate" class="form-control text-center text-info fw-bold" value="${suggestedRate}" readonly>
                        <span class="input-group-text">${baseCurrCode}</span>
                    </div>
                    <small class="text-muted">ط§ظ„ظ…طµط¯ط±: open.er-api.com</small>
                </div>
                <hr>
                <div class="text-start mb-3">
                    <label class="form-label fw-bold">âœڈï¸ڈ ط³ط¹ط± ط§ظ„طµط±ظپ ط§ظ„ظ…ط¹طھظ…ط¯ ظ„ظ„ظپط§طھظˆط±ط©:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white fw-bold">1 ${currCode}</span>
                        <input type="text" inputmode="decimal" id="swalConfirmedRate" class="form-control text-center fw-bold fs-5" value="${suggestedRate}">
                        <span class="input-group-text fw-bold">${baseCurrCode}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="document.getElementById('swalConfirmedRate').value=document.getElementById('swalSuggestedRate').value; updateCalcPreview(${remainingInBase})">
                        â†©ï¸ڈ ط§ط³طھط®ط¯ظ… ط§ظ„ظ…ظ‚طھط±ط­
                    </button>
                </div>
                <hr>
                <div class="alert alert-success p-2 text-center" id="calcPreview">
                    <div class="small text-muted mb-1">ًں’، ظ„طھط³ط¯ظٹط¯ ط§ظ„ظ…طھط¨ظ‚ظٹ:</div>
                    <div class="fw-bold fs-5">
                        <span class="text-danger">${remainingInBase.toFixed(2)} ${baseCurrCode}</span>
                        <span class="mx-2">â†گ</span>
                        <span class="text-success" id="calcResult">${(remainingInBase / parseFloat(suggestedRate)).toFixed(4)}</span>
                        <span class="text-success"> ${currCode}</span>
                    </div>
                </div>
            `,
            confirmButtonText: 'âœ… طھط£ظƒظٹط¯ ظˆط§ظ…ظ„ط£ ط§ظ„ظ…ط¨ظ„ط؛',
            cancelButtonText: 'â‌Œ ط¥ظ„ط؛ط§ط،',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            focusConfirm: false,
            didOpen: () => {
                let inp = document.getElementById('swalConfirmedRate');
                window.updateCalcPreview = function(rem) {
                    let r = parseFloat(inp.value) || 0;
                    let res = document.getElementById('calcResult');
                    if (res && r > 0) res.innerText = (rem / r).toFixed(4);
                };
                inp.addEventListener('input', function() {
                    let pos = this.selectionStart;
                    let old = this.value;
                    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\./g, '$1');
                    if (this.value !== old) this.setSelectionRange(pos - 1, pos - 1);
                    updateCalcPreview(remainingInBase);
                });
                inp.addEventListener('keydown', function(e) {
                    if (['e', 'E', '+', '-'].includes(e.key)) e.preventDefault();
                });
            },
            preConfirm: () => {
                let val = parseFloat(document.getElementById('swalConfirmedRate').value);
                if (!val || val <= 0) {
                    Swal.showValidationMessage('âڑ ï¸ڈ ظٹط±ط¬ظ‰ ط¥ط¯ط®ط§ظ„ ط³ط¹ط± طµط±ظپ طµط­ظٹط­ ط£ظƒط¨ط± ظ…ظ† طµظپط±');
                    return false;
                }
                return val;
            }
        }).then(result => {
            if (result.isConfirmed) {
                let rate = result.value;
                let hiddenCurr = row.querySelector('.pay-currency-id');
                if (hiddenCurr) hiddenCurr.value = currId;
                
                updatePayRate(row, rate, currCode);

                if (amountInput && remainingInBase > 0) {
                    amountInput.value = formatNum(remainingInBase / rate);
                }
                calculateGrandTotal();
            } else {
                // ط§ظ„ط¹ظˆط¯ط© ظ„ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط© ظپظٹ ط­ط§ظ„ ط§ظ„ط¥ظ„ط؛ط§ط،
                select.value = baseCurrId;
                onPayCurrencyChange(select, idx);
            }
        });
    }

    function addPaymentRow() {
        const invoiceRateVal = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        const grandTotalInInvoice = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
        const grandTotalInBase = grandTotalInInvoice * invoiceRateVal;

        let currentPaidInBase = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let amountInput = row.querySelector('.payment-input');
            let hiddenRate = row.querySelector('.pay-rate-hidden'); // ط§ظ„ط³ط¹ط± ط§ظ„ط­ظ‚ظٹظ‚ظٹ
            let currSel = row.querySelector('.currency-select');
            if (!amountInput) return;
            let amount = parseMoney(amountInput.value);
            let isBase = currSel ? (currSel.options[currSel.selectedIndex]?.dataset?.isBase === '1') : true;
            let rate = hiddenRate ? (parseMoney(hiddenRate.value) || 1) : 1;
            // ط§ظ„طھط­ظˆظٹظ„ ظ„ظ„ط¹ظ…ظ„ط© ط§ظ„ط£ط³ط§ط³ظٹط©
            currentPaidInBase += isBase ? amount : (rate > 0 ? amount * rate : 0);
        });

        let remainingInBase = grandTotalInBase - currentPaidInBase;
        if (remainingInBase < 0.001) remainingInBase = 0;
        
        const invoiceCurrId = document.getElementById('currency_id').value;
        const invoiceCurrCode = document.getElementById('currency_id').options[document.getElementById('currency_id').selectedIndex]?.dataset?.code || '';
        const baseCurrId = "dummy";

        let selectedCurrId = baseCurrId;
        let selectedRate = 1;
        let isNonBase = false;

        // ط¥ط°ط§ ظƒط§ظ†طھ ط§ظ„ظپط§طھظˆط±ط© ط¨ط¹ظ…ظ„ط© ط؛ظٹط± ط§ظ„ط£ط³ط§ط³ظٹط©طŒ ظ†ط¬ط¹ظ„ ط§ظ„ط¯ظپط¹ ط§ظ„ط£ظˆظ„ (ط£ظˆ ظƒظ„ ط¯ظپط¹ ط¬ط¯ظٹط¯) ظٹطھط¨ط¹ ط¹ظ…ظ„ط© ط§ظ„ظپط§طھظˆط±ط© ط§ظپطھط±ط§ط¶ظٹط§ظ‹
        if (invoiceCurrId != baseCurrId) {
            selectedCurrId = invoiceCurrId;
            selectedRate = invoiceRateVal;
            isNonBase = true;
        }

        // ط®ظٹط§ط±ط§طھ ط§ظ„ط¹ظ…ظ„ط§طھ
        let currencyOptions = `<option value="${baseCurrId}" data-is-base="1" ${selectedCurrId == baseCurrId ? 'selected' : ''}>dummy</option>`;
        for(let cur of []){
            if(true){
            currencyOptions += `<option value="dummy" data-code="dummy" data-is-base="0" ${selectedCurrId == "dummy" ? 'selected' : ''}>dummy</option>`;
            }
        }

        let finalDefaultVal = (remainingInBase > 0 && selectedRate > 0) ? formatNum(remainingInBase / selectedRate) : 0;
        
        const div = document.createElement('div');
        div.className = 'payment-row mb-2';
        div.innerHTML = `
            <div class="input-group mb-1">
                <select name="payments[${paymentIdx}][method]" class="form-select method-select" style="max-width: 120px;">
                    <option value="cash"> dummy </option>
                    <option value="card"> dummy </option>
                    <option value="bank"> dummy </option>
                </select>
                <input type="text" inputmode="decimal" name="payments[${paymentIdx}][amount]" class="form-control text-center payment-input" value="${finalDefaultVal}" oninput="calculateGrandTotal()" onfocus="this.select()">
                <select class="form-select currency-select pay-currency" style="max-width:110px;" onchange="onPayCurrencyChange(this, ${paymentIdx})">${currencyOptions}</select>
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.payment-row').remove(); calculateGrandTotal();"><i class="fas fa-trash"></i></button>
            </div>
            <input type="hidden" name="payments[${paymentIdx}][currency_id]" class="pay-currency-id" value="${selectedCurrId}">
            <input type="hidden" name="payments[${paymentIdx}][exchange_rate]" class="pay-rate-hidden" value="${selectedRate}">
            <div class="rate-row ${isNonBase ? '' : 'd-none'}">
                <div class="input-group input-group-sm">
                    <span class="input-group-text text-muted small">dummy (dummy)</span>
                    <input type="text" readonly class="form-control bg-light text-center fw-bold rate-input" value="0.00" placeholder="ط§ظ„ظ…ط¹ط§ط¯ظ„">
                    <span class="input-group-text rate-note small text-info">${isNonBase ? `1 ${invoiceCurrCode} = ${selectedRate} dummy` : ''}</span>
                </div>
            </div>
        `;
        document.getElementById('paymentsContainer').appendChild(div);
        paymentIdx++;
        calculateGrandTotal();
    }

    function toggleDetails(idx) {
        let row = document.getElementById(`details_${idx}`);
        if (row.style.display === 'none') row.style.display = 'table-row';
        else row.style.display = 'none';
    }
    
    function calcProfitPercent(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseMoney(row.querySelector('.price').value);
        let sell = parseMoney(row.querySelector('.sell').value);
        let profitInput = row.querySelector('.profit');
        
        let newProfit = 0;
        if(price > 0) {
            newProfit = ((sell - price) / price) * 100;
            profitInput.value = formatNum(newProfit);
        }

        // ًںں¢ ظ…ظ†ط·ظ‚ ط¥ط®ظپط§ط،/ط¥ط¸ظ‡ط§ط± ط§ظ„طھط­ط°ظٹط± ظ„ط­ط¸ظٹط§ظ‹ ط¹ظ†ط¯ ط§ظ„ظƒطھط§ط¨ط©
        let select = row.querySelector('.unit-select');
        let originalMainProfit = parseFloat(select.options[select.selectedIndex].getAttribute('data-profit')) || 0;
        let mainWarningDiv = row.querySelector('.main-warning-container');
        
        mainWarningDiv.innerHTML = ''; // ظ…ط³ط­ ط§ظ„ظ‚ط¯ظٹظ… ط¯ط§ط¦ظ…ط§ظ‹

        if (newProfit <= 0) {
            mainWarningDiv.innerHTML = `<span class="text-danger flash-warning">ط®ط³ط§ط±ط© âڑ ï¸ڈ</span>`;
        } 
        else if (newProfit < originalMainProfit - 0.1) {
            mainWarningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">ًں“‰ ط§ظ†ط®ظپط§ط¶ ط§ظ„ط±ط¨ط­</span>`;
        }
    }

    function calcSellPrice(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseMoney(row.querySelector('.price').value);
        let profit = parseMoney(row.querySelector('.profit').value);
        let sell = price * (1 + profit / 100);
        row.querySelector('.sell').value = formatNum(sell);
    }
    
    // ط§ظ„ط¯ط§ظ„ط© ط§ظ„ظ…ظپظ‚ظˆط¯ط©: checkBalanceAndSubmit
    function checkBalanceAndSubmit() {
        // ًں›‘ 1. ط§ظ„طھط­ظ‚ظ‚ ظ…ظ† ط§ط®طھظٹط§ط± ط§ظ„ظ…ظˆط±ط¯
        // ظ†طھط­ظ‚ظ‚ ظ…ظ† ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ظ…ط®ظپظٹط© (ID) ظˆظ„ظٹط³ ط§ظ„ظ†طµ ط§ظ„ط¸ط§ظ‡ط± ظپظ‚ط· ظ„ط¶ظ…ط§ظ† ط§ط®طھظٹط§ط± ظ…ظˆط±ط¯ طµط­ظٹط­
        let supplierId = document.getElementById('supplierId').value;
        let supplierInput = document.getElementById('supplierSearchInput');

        if (!supplierId || supplierId.trim() === '') {
            // طھظ„ظˆظٹظ† ط§ظ„ط­ظ‚ظ„ ط¨ط§ظ„ط£ط­ظ…ط±
            supplierInput.classList.add('is-invalid'); 
            
            // ط¥ط¸ظ‡ط§ط± ط±ط³ط§ظ„ط© ط®ط·ط£ (ظٹط¯ط¹ظ… SweetAlert ط£ظˆ ط§ظ„طھظ†ط¨ظٹظ‡ ط§ظ„ط¹ط§ط¯ظٹ)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'طھظ†ط¨ظٹظ‡',
                    text: 'ط§ظ„ط±ط¬ط§ط، ط§ط®طھظٹط§ط± ط§ظ„ظ…ظˆط±ط¯ ظ…ظ† ط§ظ„ظ‚ط§ط¦ظ…ط© ظ‚ط¨ظ„ ط­ظپط¸ ط§ظ„ظپط§طھظˆط±ط©!',
                    confirmButtonText: 'ط­ط³ظ†ط§ظ‹'
                });
            } else {
                alert('ط§ظ„ط±ط¬ط§ط، ط§ط®طھظٹط§ط± ط§ظ„ظ…ظˆط±ط¯ ظ…ظ† ط§ظ„ظ‚ط§ط¦ظ…ط© ظ‚ط¨ظ„ ط­ظپط¸ ط§ظ„ظپط§طھظˆط±ط©!');
            }
            return; // â›” ط¥ظٹظ‚ط§ظپ ط§ظ„ط¯ط§ظ„ط© ظ‡ظ†ط§
        } else {
            supplierInput.classList.remove('is-invalid');
        }

        // ًں›‘ 2. ط§ظ„طھط­ظ‚ظ‚ ظ…ظ† طھظˆط§ط±ظٹط® ط§ظ„ط§ظ†طھظ‡ط§ط، ظ„ظƒظ„ ط§ظ„ظ…ظ†طھط¬ط§طھ
        let expiryInputs = document.querySelectorAll('input[name$="[expiry_date]"]');
        let missingExpiry = false;

        // ط§ظ„طھط£ظƒط¯ ط£ظˆظ„ط§ظ‹ ظ…ظ† ظˆط¬ظˆط¯ ظ…ظ†طھط¬ط§طھ
        if (expiryInputs.length === 0) {
            if (typeof toastr !== 'undefined') toastr.error('ط§ظ„ظپط§طھظˆط±ط© ظپط§ط±ط؛ط©! ط£ط¶ظپ ظ…ظ†طھط¬ط§طھ ط£ظˆظ„ط§ظ‹.');
            else alert('ط§ظ„ظپط§طھظˆط±ط© ظپط§ط±ط؛ط©! ط£ط¶ظپ ظ…ظ†طھط¬ط§طھ ط£ظˆظ„ط§ظ‹.');
            return;
        }

        expiryInputs.forEach(input => {
            // ط§ظ„طھط­ظ‚ظ‚ ظ…ظ…ط§ ط¥ط°ط§ ظƒط§ظ† ط§ظ„ط­ظ‚ظ„ ظپط§ط±ط؛ط§ظ‹
            if (!input.value) {
                missingExpiry = true;
                input.classList.add('is-invalid'); // طھظ„ظˆظٹظ† ط§ظ„ط­ظ‚ظ„ ط§ظ„ظپط§ط±ط؛ ط¨ط§ظ„ط£ط­ظ…ط±
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (missingExpiry) {
             if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'طھط§ط±ظٹط® ط§ظ„ط§ظ†طھظ‡ط§ط، ظ…ط·ظ„ظˆط¨',
                    text: 'ظ„ط§ ظٹظ…ظƒظ† ط­ظپط¸ ط§ظ„ظپط§طھظˆط±ط©. ظٹظˆط¬ط¯ ظ…ظ†طھط¬ط§طھ ط¨ط¯ظˆظ† طھط§ط±ظٹط® ط§ظ†طھظ‡ط§ط،!',
                    confirmButtonText: 'ظ…ط±ط§ط¬ط¹ط© ط§ظ„ظ…ظ†طھط¬ط§طھ'
                });
             } else {
                 alert('ظ„ط§ ظٹظ…ظƒظ† ط­ظپط¸ ط§ظ„ظپط§طھظˆط±ط©. ظٹظˆط¬ط¯ ظ…ظ†طھط¬ط§طھ ط¨ط¯ظˆظ† طھط§ط±ظٹط® ط§ظ†طھظ‡ط§ط،!');
             }
            return; // â›” ط¥ظٹظ‚ط§ظپ ط§ظ„ط¯ط§ظ„ط© ظ‡ظ†ط§
        }

        // âœ… ط¥ط°ط§ طھط¬ط§ظˆط²ظ†ط§ ط§ظ„ظپط­ظˆطµط§طھ ط£ط¹ظ„ط§ظ‡طŒ ظ†ظƒظ…ظ„ ط§ظ„ظƒظˆط¯ ط§ظ„ط·ط¨ظٹط¹ظٹ ظ„ظ„ط­ط³ط§ط¨ط§طھ ظˆط§ظ„ظ…ظˆط¯ط§ظ„
        let grandTotal = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
        let totalPaid = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let amount = parseMoney(row.querySelector('.payment-input')?.value || 0);
            let hiddenRate = row.querySelector('.pay-rate-hidden');
            let currSel = row.querySelector('.currency-select');
            let isBase = currSel ? (currSel.options[currSel.selectedIndex]?.dataset?.isBase === '1') : true;
            let rate = hiddenRate ? (parseMoney(hiddenRate.value) || 1) : 1;
            totalPaid += isBase ? amount : (rate > 0 ? amount * rate : 0);
        });
        
        let diff = grandTotal - totalPaid;
        
        // ط¥ط°ط§ ظƒط§ظ† ط§ظ„ظ…ط¨ظ„ط؛ ط§ظ„ظ…ط¯ظپظˆط¹ ظٹط³ط§ظˆظٹ ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹ طھظ…ط§ظ…ط§ظ‹ (ط§ظ„ظپط§ط±ظ‚ ط´ط¨ظ‡ ظ…ط¹ط¯ظˆظ…)طŒ ط§ط­ظپط¸ ظ…ط¨ط§ط´ط±ط©
        if (Math.abs(diff) < 0.01) {
            ajaxSubmitPurchase();
            return;
        }

        // ط¥ط¹ط¯ط§ط¯ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظˆط¯ط§ظ„ (طھط£ظƒظٹط¯ ط§ظ„ط¯ظٹظ† ط£ظˆ ط§ظ„ط±طµظٹط¯)
        let oldBalance = parseFloat(document.getElementById('currentSupplierBalance').value) || 0;
        let newBalance = oldBalance + diff; 

        let modalOldBalance = document.getElementById('modalOldBalance');
        modalOldBalance.innerText = formatNum(oldBalance);
        modalOldBalance.className = "fw-bold " + (oldBalance >= 0 ? "text-danger" : "text-success");
        
        document.getElementById('modalGrandTotal').innerText = formatNum(grandTotal);
        document.getElementById('modalPaid').innerText = formatNum(totalPaid);
        document.getElementById('modalDiff').innerText = formatNum(diff);

        let balText = "";
        let colorClass = "";
        
        if (newBalance > 0) {
            colorClass = "text-danger";
            balText = `ط³ظٹطµط¨ط­ ظ„ظ‡ ط¹ظ„ظٹظ†ط§: ${formatNum(newBalance)} (ط¯ظٹظ†)`;
        } else if (newBalance < 0) {
            colorClass = "text-success";
            balText = `ط³ظٹطµط¨ط­ ظ„ظ†ط§ ط¹ظ†ط¯ظ‡: ${formatNum(Math.abs(newBalance))} (ط±طµظٹط¯)`;
        } else {
            colorClass = "text-primary";
            balText = "ط§ظ„ط±طµظٹط¯ ط³ظٹطµط¨ط­ 0.00 (ط®ط§ظ„طµ)";
        }

        let modalNewBalance = document.getElementById('modalNewBalance');
        modalNewBalance.innerText = balText;
        modalNewBalance.className = "fs-4 fw-bold " + colorClass;

        let msg = "";
        if (diff > 0) {
            msg = "âڑ ï¸ڈ ط§ظ„ظ…ط¨ظ„ط؛ ط§ظ„ظ…ط¯ظپظˆط¹ ط£ظ‚ظ„ ظ…ظ† ط§ظ„ظپط§طھظˆط±ط©. ط³ظٹطھظ… ط¥ط¶ط§ظپط© ط§ظ„ظپط§ط±ظ‚ ط¥ظ„ظ‰ ط§ظ„ط¯ظٹظ†.";
        } else {
            msg = "âڑ ï¸ڈ ط§ظ„ظ…ط¨ظ„ط؛ ط§ظ„ظ…ط¯ظپظˆط¹ ط£ظƒط¨ط± ظ…ظ† ط§ظ„ظپط§طھظˆط±ط©. ط³ظٹطھظ… ط¥ط¶ط§ظپط© ط§ظ„ظپط§ط±ظ‚ ظƒط±طµظٹط¯ ظ„ظƒ.";
        }
        document.getElementById('modalMessage').innerText = msg;

        var myModal = new bootstrap.Modal(document.getElementById('balanceConfirmModal'));
        myModal.show();
    }

    function ajaxSubmitPurchase() {
        const form = document.getElementById('purchaseForm');
        const formData = new FormData(form);
        
        const btn = document.getElementById('confirmSaveBtn');
        const originalText = btn ? btn.innerHTML : '';
        if(btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ط¬ط§ط±ظٹ ط§ظ„ط­ظپط¸...';
            btn.disabled = true;
        }

        Swal.fire({title: 'ط¬ط§ط±ظٹ ط­ظپط¸ ط§ظ„ظپط§طھظˆط±ط©...', didOpen: () => Swal.showLoading()});

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': 'dummy'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.close();
                // ط¥ط°ط§ ظƒط§ظ† ظ‡ظ†ط§ظƒ ط¨ظٹط§ظ†ط§طھ ظˆط§طھط³ط§ط¨ ط£ظˆ ط¥ظٹظ…ظٹظ„طŒ ط§ط¹ط±ط¶ ط®ظٹط§ط±ط§طھ ط§ظ„ظ…ط´ط§ط±ظƒط©
                if (data.whatsapp_data) {
                    Swal.fire({
                        title: 'طھظ… ط§ظ„ط­ظپط¸ ط¨ظ†ط¬ط§ط­',
                        text: 'ظƒظٹظپ طھط±ط؛ط¨ ظپظٹ ظ…ط´ط§ط±ظƒط© ط§ظ„ظپط§طھظˆط±ط© ظ…ط¹ ط§ظ„ظ…ظˆط±ط¯طں',
                        icon: 'success',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fab fa-whatsapp"></i> ظˆط§طھط³ط§ط¨',
                        denyButtonText: '<i class="fas fa-envelope"></i> ط¥ظٹظ…ظٹظ„',
                        cancelButtonText: 'ط¥ط؛ظ„ط§ظ‚ ظˆظ…طھط§ط¨ط¹ط©',
                        confirmButtonColor: '#25d366',
                        denyButtonColor: '#007bff',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            triggerWhatsappPrompt(
                                data.whatsapp_data.phone, 
                                data.whatsapp_data.message, 
                                "ط¥ط±ط³ط§ظ„ ظپط§طھظˆط±ط© ط§ظ„ظ…ط´طھط±ظٹط§طھ ظ„ظ„ظ…ظˆط±ط¯",
                                data.whatsapp_data.pdf_url,
                                data.whatsapp_data.pdf_filename
                            );
                        } else if (result.isDenied) {
                            triggerEmailPrompt(
                                data.supplier_email || '', 
                                data.whatsapp_data.message, 
                                "ظپط§طھظˆط±ط© ظ…ط´طھط±ظٹط§طھ - " + (data.invoice_no || ''), 
                                data.pdf_url,
                                data.pdf_filename
                            );
                        } else {
                            window.location.href = "dummy";
                        }
                        
                        // ظ†ط±ط§ظ‚ط¨ ط¥ط؛ظ„ط§ظ‚ ط§ظ„ظ…ظˆط¯ط§ظ„ط§طھ ظ„ظ„ط¹ظˆط¯ط© ظ„ظ„ظپظ‡ط±ط³
                        $(document).one('hidden.bs.modal', '#globalWhatsappModal, #globalEmailModal', function() {
                            window.location.href = "dummy";
                        });
                    });
                } else {
                    Swal.fire({icon: 'success', title: 'طھظ… ط§ظ„ط­ظپط¸ ط¨ظ†ط¬ط§ط­', timer: 1500, showConfirmButton: false})
                    .then(() => {
                        window.location.href = "dummy";
                    });
                }
            } else {
                Swal.fire({icon: 'error', title: 'ط®ط·ط£', text: data.message || 'ط­ط¯ط« ط®ط·ط£ ط؛ظٹط± ظ…طھظˆظ‚ط¹'});
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({icon: 'error', title: 'ط®ط·ط£', text: 'ظپط´ظ„ ط§ظ„ط§طھطµط§ظ„ ط¨ط§ظ„ط³ظٹط±ظپط±'});
        })
        .finally(() => {
            if(btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
// --- ط¯ط§ظ„ط© ظپطھط­ ظ†ط§ظپط°ط© ط¥ط¶ط§ظپط© ط§ظ„ظ…ظ†طھط¬ ---
    function openCreateProductModal() {
        console.log("Opening Product Modal...");
        const frame = document.getElementById('createProductFrame');
        if (!frame) return console.error("createProductFrame not found");
        
        frame.src = "dummy?iframe=1"; 
        
        const modalEl = document.getElementById('quickProductModal');
        if (!modalEl) return console.error("quickProductModal not found");

        if (typeof bootstrap !== 'undefined') {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            myModal.show();
        } else {
            console.error("Bootstrap is not defined!");
            alert("ط®ط·ط£ ظپظٹ طھط­ظ…ظٹظ„ ظ…ظƒطھط¨ط© Bootstrap");
        }
    }

// --- ط¯ط§ظ„ط© ظپطھط­ ظ†ط§ظپط°ط© ط¥ط¶ط§ظپط© ط§ظ„ظ…ظˆط±ط¯ ---
    function openCreateSupplierModal() {
        console.log("Opening Supplier Modal...");
        const frame = document.getElementById('createSupplierFrame');
        if (!frame) return console.error("createSupplierFrame not found");

        frame.src = "dummy?type=supplier&iframe=1"; 
        
        const modalEl = document.getElementById('addSupplierModal');
        if (!modalEl) return console.error("addSupplierModal not found");

        if (typeof bootstrap !== 'undefined') {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            myModal.show();
        } else {
            console.error("Bootstrap is not defined!");
            alert("ط®ط·ط£ ظپظٹ طھط­ظ…ظٹظ„ ظ…ظƒطھط¨ط© Bootstrap");
        }
    }

    // --- ط¯ط§ظ„ط© ظپطھط­ ظ†ط§ظپط°ط© ط¥ط¶ط§ظپط© ظˆط¬ط¨ط© (ظ„ظ„ظ…ط·ط§ط¹ظ…) ---
    function openCreateMealModal() {
        console.log("Opening Meal Modal...");
        const frame = document.getElementById('createMealFrame');
        if (!frame) return console.error("createMealFrame not found");

        frame.src = "dummy?iframe=1"; 
        
        const modalEl = document.getElementById('quickMealModal');
        if (!modalEl) return console.error("quickMealModal not found");

        if (typeof bootstrap !== 'undefined') {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            myModal.show();
        } else {
            console.error("Bootstrap is not defined!");
            alert("ط®ط·ط£ ظپظٹ طھط­ظ…ظٹظ„ ظ…ظƒطھط¨ط© Bootstrap");
        }
    }

