(() => {
    const app = document.querySelector('#cashier-app');

    if (!app) {
        return;
    }

    const searchInput = document.querySelector('#product-search');
    const productResults = document.querySelector('#product-results');
    const cartLines = document.querySelector('#cart-lines');
    const message = document.querySelector('#cashier-message');
    const paidInput = document.querySelector('#paid-amount');
    const cart = new Map();
    let heldTransactionId = null;
    let searchTimer = null;
    let searchController = null;

    const sampleSearchTerms = ['Indomie Goreng', 'Aqua 600ml', 'Teh Botol Sosro', 'Beras Premium 5kg'];

    const currency = (amount) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount || 0);

    const moneyInCents = (amount) => Math.round((Number(amount) || 0) * 100);

    const showMessage = (text, isError = false) => {
        message.textContent = text;
        message.className = `rounded-md border px-4 py-3 text-sm ${isError ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-900'}`;
    };

    const clearMessage = () => {
        message.textContent = '';
        message.className = 'hidden rounded-md border px-4 py-3 text-sm';
    };

    const renderSearchResults = (products) => {
        productResults.replaceChildren();

        if (!products.length) {
            const empty = document.createElement('p');
            empty.className = 'px-4 py-3 text-sm text-stone-500';
            empty.textContent = `Produk tidak ditemukan. Contoh: ${sampleSearchTerms.join(', ')}.`;
            productResults.append(empty);
            productResults.classList.remove('hidden');
            return;
        }

        products.forEach((product) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.role = 'option';
            button.className = 'flex w-full items-center justify-between gap-4 border-b border-stone-100 px-4 py-3 text-left last:border-0 hover:bg-stone-50 disabled:cursor-not-allowed disabled:opacity-50';
            button.disabled = product.stock < 1;

            const description = document.createElement('span');
            description.className = 'min-w-0';
            const name = document.createElement('span');
            name.className = 'block truncate text-sm font-medium text-stone-900';
            name.textContent = product.name;
            const meta = document.createElement('span');
            meta.className = 'mt-1 block text-xs text-stone-500';
            meta.textContent = `${product.category}${product.barcode ? ` · ${product.barcode}` : ''} · Stok ${product.stock}`;
            description.append(name, meta);

            const price = document.createElement('span');
            price.className = 'shrink-0 text-sm font-semibold tabular-nums text-stone-900';
            price.textContent = currency(product.price);
            button.append(description, price);
            button.addEventListener('click', () => addProduct(product));
            productResults.append(button);
        });

        productResults.classList.remove('hidden');
    };

    const searchProducts = async (term) => {
        if (searchController) {
            searchController.abort();
        }

        searchController = new AbortController();
        const url = new URL(app.dataset.searchUrl, window.location.origin);
        url.searchParams.set('q', term);

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: searchController.signal,
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Pencarian produk gagal.');
            }

            renderSearchResults(data.products);
        } catch (error) {
            if (error.name !== 'AbortError') {
                showMessage(error.message, true);
            }
        }
    };

    const addProduct = (product) => {
        clearMessage();
        productResults.classList.add('hidden');
        searchInput.value = '';

        if (product.stock < 1) {
            showMessage('Stok produk sedang kosong.', true);
            return;
        }

        const current = cart.get(product.id);
        if (current && current.qty >= product.stock) {
            showMessage(`Stok ${product.name} hanya ${product.stock}.`, true);
            return;
        }

        cart.set(product.id, {
            ...product,
            qty: current ? current.qty + 1 : 1,
        });
        renderCart();
        searchInput.focus();
    };

    const changeQuantity = (productId, change) => {
        const product = cart.get(productId);
        if (!product) {
            return;
        }

        const nextQuantity = product.qty + change;
        if (nextQuantity < 1) {
            cart.delete(productId);
        } else if (nextQuantity > product.stock) {
            showMessage(`Stok ${product.name} hanya ${product.stock}.`, true);
            return;
        } else {
            product.qty = nextQuantity;
        }

        renderCart();
    };

    const renderCart = () => {
        cartLines.replaceChildren();
        const products = [...cart.values()];
        document.querySelector('#cart-count').textContent = `${products.reduce((sum, product) => sum + product.qty, 0)} barang`;

        if (!products.length) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = 5;
            emptyCell.className = 'px-4 py-12 text-center text-sm text-stone-500';
            emptyCell.textContent = 'Keranjang masih kosong. Cari atau scan produk untuk memulai.';
            emptyRow.append(emptyCell);
            cartLines.append(emptyRow);
        }

        products.forEach((product) => {
            const row = document.createElement('tr');
            row.className = 'align-middle';

            const nameCell = document.createElement('td');
            nameCell.className = 'px-4 py-3';
            const name = document.createElement('span');
            name.className = 'block font-medium text-stone-900';
            name.textContent = product.name;
            const code = document.createElement('span');
            code.className = 'mt-1 block text-xs text-stone-500';
            code.textContent = product.barcode || product.category || '';
            nameCell.append(name, code);

            const priceCell = document.createElement('td');
            priceCell.className = 'whitespace-nowrap px-4 py-3 text-stone-700';
            priceCell.textContent = currency(product.price);

            const quantityCell = document.createElement('td');
            quantityCell.className = 'px-4 py-3';
            const quantityControl = document.createElement('div');
            quantityControl.className = 'inline-flex items-center gap-2';
            const minus = document.createElement('button');
            minus.type = 'button';
            minus.title = 'Kurangi jumlah';
            minus.setAttribute('aria-label', `Kurangi jumlah ${product.name}`);
            minus.className = 'grid size-8 place-items-center rounded border border-stone-300 text-lg text-stone-700 hover:bg-stone-50';
            minus.textContent = '−';
            minus.addEventListener('click', () => changeQuantity(product.id, -1));
            const quantity = document.createElement('span');
            quantity.className = 'min-w-6 text-center font-medium tabular-nums';
            quantity.textContent = product.qty;
            const plus = document.createElement('button');
            plus.type = 'button';
            plus.title = 'Tambah jumlah';
            plus.setAttribute('aria-label', `Tambah jumlah ${product.name}`);
            plus.className = 'grid size-8 place-items-center rounded border border-stone-300 text-lg text-stone-700 hover:bg-stone-50';
            plus.textContent = '+';
            plus.addEventListener('click', () => changeQuantity(product.id, 1));
            quantityControl.append(minus, quantity, plus);
            quantityCell.append(quantityControl);

            const subtotalCell = document.createElement('td');
            subtotalCell.className = 'whitespace-nowrap px-4 py-3 text-right font-medium tabular-nums text-stone-900';
            subtotalCell.textContent = currency(product.price * product.qty);

            const removeCell = document.createElement('td');
            removeCell.className = 'px-4 py-3 text-right';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.title = 'Hapus barang';
            remove.setAttribute('aria-label', `Hapus ${product.name}`);
            remove.className = 'rounded px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50';
            remove.textContent = 'Hapus';
            remove.addEventListener('click', () => {
                cart.delete(product.id);
                renderCart();
            });
            removeCell.append(remove);
            row.append(nameCell, priceCell, quantityCell, subtotalCell, removeCell);
            cartLines.append(row);
        });

        updateTotals();
    };

    const updateTotals = () => {
        const subtotalCents = [...cart.values()].reduce((sum, product) => sum + moneyInCents(product.price) * product.qty, 0);
        const percent = Math.min(100, Math.max(0, Number(document.querySelector('#discount-percent').value) || 0));
        const fixedDiscountCents = Math.max(0, moneyInCents(document.querySelector('#discount-amount').value));
        const percentDiscountCents = Math.round(subtotalCents * percent / 100);
        const discountCents = Math.min(subtotalCents, percentDiscountCents + fixedDiscountCents);
        const taxableCents = subtotalCents - discountCents;
        const taxPercent = Math.min(100, Math.max(0, Number(document.querySelector('#tax-percent').value) || 0));
        const taxCents = Math.round(taxableCents * taxPercent / 100);
        const otherFeeCents = Math.max(0, moneyInCents(document.querySelector('#other-fee').value));
        const totalCents = taxableCents + taxCents + otherFeeCents;
        const paidCents = Math.max(0, moneyInCents(paidInput.value));
        const changeCents = Math.max(0, paidCents - totalCents);

        document.querySelector('#summary-subtotal').textContent = currency(subtotalCents / 100);
        document.querySelector('#summary-discount').textContent = `− ${currency(discountCents / 100)}`;
        document.querySelector('#summary-tax').textContent = currency(taxCents / 100);
        document.querySelector('#summary-total').textContent = currency(totalCents / 100);
        document.querySelector('#summary-change').textContent = currency(changeCents / 100);

        const status = document.querySelector('#payment-status');
        const payButton = document.querySelector('#pay-transaction');
        if (!cart.size) {
            status.textContent = 'Tambahkan barang untuk memulai transaksi.';
            status.className = 'text-sm text-stone-500';
            payButton.disabled = true;
        } else if (paidCents < totalCents) {
            status.textContent = `Uang kurang ${currency((totalCents - paidCents) / 100)}.`;
            status.className = 'text-sm font-medium text-red-700';
            payButton.disabled = true;
        } else {
            status.textContent = 'Uang cukup. Transaksi siap dibayar.';
            status.className = 'text-sm font-medium text-emerald-700';
            payButton.disabled = false;
        }
    };

    const requestData = async (url, options = {}) => {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': app.dataset.csrf,
                ...(options.headers || {}),
            },
        });
        const data = await response.json();

        if (!response.ok) {
            const validationMessage = Object.values(data.errors || {}).flat()[0];
            throw new Error(validationMessage || data.message || 'Permintaan tidak berhasil.');
        }

        return data;
    };

    const saveTransaction = async (action) => {
        clearMessage();
        const data = {
            action,
            transaction_id: heldTransactionId,
            customer_id: document.querySelector('#customer-id').value || null,
            items: [...cart.values()].map((product) => ({ product_id: product.id, qty: product.qty })),
            discount_percent: Number(document.querySelector('#discount-percent').value) || 0,
            discount_amount: Number(document.querySelector('#discount-amount').value) || 0,
            tax_percent: Number(document.querySelector('#tax-percent').value) || 0,
            other_fee: Number(document.querySelector('#other-fee').value) || 0,
        };

        if (action === 'paid') {
            data.paid_amount = Number(paidInput.value) || 0;
            data.payment_method = document.querySelector('#payment-method').value;
        }

        try {
            const result = await requestData(app.dataset.storeUrl, {
                method: 'POST',
                body: JSON.stringify(data),
            });

            if (action === 'paid') {
                window.location.assign(result.receipt_url);
                return;
            }

            showMessage(result.message);
            window.location.reload();
        } catch (error) {
            showMessage(error.message, true);
        }
    };

    const loadHeldTransaction = async (transactionId) => {
        clearMessage();
        try {
            const url = app.dataset.resumeUrl.replace('__TRANSACTION__', transactionId);
            const result = await requestData(url, { method: 'GET' });
            heldTransactionId = result.transaction.id;
            cart.clear();
            result.transaction.details.forEach((detail) => {
                cart.set(detail.product_id, {
                    id: detail.product_id,
                    name: detail.name,
                    barcode: detail.barcode,
                    price: detail.price,
                    stock: detail.stock,
                    qty: detail.qty,
                });
            });
            document.querySelector('#customer-id').value = result.transaction.customer_id || '';
            document.querySelector('#discount-percent').value = result.transaction.discount_percent || 0;
            document.querySelector('#discount-amount').value = result.transaction.discount_amount || 0;
            document.querySelector('#tax-percent').value = result.transaction.tax_percent ?? 11;
            paidInput.value = '';
            renderCart();
            showMessage('Transaksi ditahan berhasil dibuka kembali.');
            searchInput.focus();
        } catch (error) {
            showMessage(error.message, true);
        }
    };

    const cancelTransaction = async () => {
        if (!cart.size && !heldTransactionId) {
            return;
        }

        if (!window.confirm('Batalkan transaksi yang sedang dibuka?')) {
            return;
        }

        if (heldTransactionId) {
            try {
                const url = app.dataset.destroyUrl.replace('__TRANSACTION__', heldTransactionId);
                await requestData(url, { method: 'DELETE' });
            } catch (error) {
                showMessage(error.message, true);
                return;
            }
        }

        cart.clear();
        heldTransactionId = null;
        document.querySelector('#customer-id').value = '';
        document.querySelector('#discount-percent').value = 0;
        document.querySelector('#discount-amount').value = 0;
        document.querySelector('#tax-percent').value = 11;
        document.querySelector('#other-fee').value = 0;
        document.querySelector('#payment-method').value = 'cash';
        paidInput.value = '';
        renderCart();
        clearMessage();
        searchInput.focus();
    };

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        const term = searchInput.value.trim();
        if (!term) {
            productResults.classList.add('hidden');
            return;
        }

        searchTimer = window.setTimeout(() => searchProducts(term), 180);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            const firstResult = productResults.querySelector('button:not(:disabled)');
            if (firstResult) {
                event.preventDefault();
                firstResult.click();
            }
        }
    });

    document.addEventListener('click', (event) => {
        if (!productResults.contains(event.target) && event.target !== searchInput) {
            productResults.classList.add('hidden');
        }
    });

    document.querySelectorAll('#discount-percent, #discount-amount, #tax-percent, #other-fee, #paid-amount')
        .forEach((input) => input.addEventListener('input', updateTotals));

    document.querySelector('#hold-transaction').addEventListener('click', () => saveTransaction('held'));
    document.querySelector('#pay-transaction').addEventListener('click', () => saveTransaction('paid'));
    document.querySelector('#cancel-transaction').addEventListener('click', cancelTransaction);
    document.querySelector('#clear-cart').addEventListener('click', cancelTransaction);
    document.querySelectorAll('[data-resume-id]').forEach((button) => {
        button.addEventListener('click', () => loadHeldTransaction(button.dataset.resumeId));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'F2') {
            event.preventDefault();
            searchInput.focus();
        } else if (event.key === 'F4') {
            event.preventDefault();
            paidInput.focus();
        } else if (event.key === 'Escape') {
            productResults.classList.add('hidden');
            cancelTransaction();
        }
    });

    renderCart();
})();