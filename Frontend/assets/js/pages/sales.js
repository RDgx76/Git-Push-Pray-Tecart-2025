/* sales.js
   POS front-end logic for cashier (no external libs)
   - product search (by code or name)
   - add/update/remove items in cart
   - calculate totals, discount, taxes
   - open payment modal and submit transaction
*/

document.addEventListener("DOMContentLoaded", () => {
  const productSearch = document.querySelector("#productSearch");
  const productList = document.querySelector("#productList");
  const cartEl = document.querySelector("#cartItems");
  const subtotalEl = document.querySelector("#subtotal");
  const discountEl = document.querySelector("#discount");
  const taxEl = document.querySelector("#tax");
  const totalEl = document.querySelector("#total");
  const payBtn = document.querySelector("#btnPay");
  const clearBtn = document.querySelector("#btnClearCart");

  let cart = []; // {id, code, name, price, qty}

  async function searchProducts(q) {
    if (!q) {
      productList.innerHTML = "";
      return;
    }
    try {
      const res = await api.get(
        `/search-product.php?q=${encodeURIComponent(q)}`
      );
      // res expected array of products
      productList.innerHTML = res
        .map(
          (p) => `
        <div class="product-item" data-id="${p.id}" data-code="${
            p.code
          }" data-price="${p.price}">
          <div class="p-name">${p.name}</div>
          <div class="p-meta">Rp ${Number(p.price).toLocaleString()}</div>
        </div>
      `
        )
        .join("");
    } catch (e) {
      console.error(e);
    }
  }

  // debounce search
  if (productSearch) {
    productSearch.addEventListener(
      "input",
      bytemart.debounce((e) => {
        searchProducts(e.target.value.trim());
      }, 220)
    );
  }

  // add product by clicking
  productList &&
    productList.addEventListener("click", (e) => {
      const item = e.target.closest(".product-item");
      if (!item) return;
      const id = item.getAttribute("data-id");
      const code = item.getAttribute("data-code");
      const price = parseFloat(item.getAttribute("data-price") || 0);
      const name = item.querySelector(".p-name").textContent;
      addToCart({ id, code, price, name });
    });

  function addToCart(product) {
    const found = cart.find((c) => c.id === product.id);
    if (found) {
      found.qty += 1;
    } else {
      cart.push(Object.assign({}, product, { qty: 1 }));
    }
    renderCart();
  }

  function removeFromCart(id) {
    cart = cart.filter((c) => c.id !== id);
    renderCart();
  }

  function updateQty(id, qty) {
    const item = cart.find((c) => c.id === id);
    if (!item) return;
    item.qty = Math.max(1, parseInt(qty, 10) || 1);
    renderCart();
  }

  function calculateTotals() {
    const subtotal = cart.reduce((s, it) => s + Number(it.price) * it.qty, 0);
    const discountVal = parseFloat(discountEl ? discountEl.value || 0 : 0) || 0;
    const taxPercent = parseFloat(taxEl ? taxEl.value || 0 : 0) || 0;
    const discounted = Math.max(0, subtotal - discountVal);
    const taxAmount = (taxPercent / 100) * discounted;
    const total = discounted + taxAmount;
    return { subtotal, discountVal, taxPercent, taxAmount, total };
  }

  function renderCart() {
    cartEl.innerHTML = cart
      .map(
        (it) => `
      <div class="cart-row" data-id="${it.id}">
        <div class="cart-name">${it.name}</div>
        <div class="cart-qty">
          <input class="cart-qty-input" type="number" min="1" value="${
            it.qty
          }" />
        </div>
        <div class="cart-price">Rp ${Number(
          it.price * it.qty
        ).toLocaleString()}</div>
        <button class="btn btn-danger btn-remove" data-remove="${
          it.id
        }">x</button>
      </div>
    `
      )
      .join("");

    const totals = calculateTotals();
    subtotalEl.textContent = `Rp ${Math.round(
      totals.subtotal
    ).toLocaleString()}`;
    totalEl.textContent = `Rp ${Math.round(totals.total).toLocaleString()}`;
  }

  // cart interactions (delegate)
  cartEl.addEventListener("input", (e) => {
    const input = e.target.closest(".cart-qty-input");
    if (input) {
      const row = input.closest(".cart-row");
      const id = row.getAttribute("data-id");
      updateQty(id, input.value);
    }
  });

  cartEl.addEventListener("click", (e) => {
    const rem = e.target.closest("[data-remove]");
    if (rem) {
      const id = rem.getAttribute("data-remove");
      removeFromCart(id);
    }
  });

  // discount and tax change
  [discountEl, taxEl].forEach((el) => {
    if (!el) return;
    el.addEventListener(
      "input",
      bytemart.debounce(() => {
        renderCart();
      }, 150)
    );
  });

  // clear cart
  if (clearBtn) {
    clearBtn.addEventListener("click", () => {
      cart = [];
      renderCart();
    });
  }

  // pay: open payment modal. Assume modal has id #modalPayment and a form inside with id #formPayment
  if (payBtn) {
    payBtn.addEventListener("click", () => {
      const modal = document.getElementById("modalPayment");
      if (!modal) return;
      // populate payment summary
      const totals = calculateTotals();
      modal.querySelector("[data-pay-subtotal]").textContent = `Rp ${Math.round(
        totals.subtotal
      ).toLocaleString()}`;
      modal.querySelector("[data-pay-total]").textContent = `Rp ${Math.round(
        totals.total
      ).toLocaleString()}`;
      modal.classList.add("active");
    });
  }

  // submit payment
  const formPayment = document.querySelector("#formPayment");
  if (formPayment) {
    formPayment.addEventListener("submit", async (e) => {
      e.preventDefault();
      const modal = document.getElementById("modalPayment");

      // collect payload
      const totals = calculateTotals();
      const paymentMethod =
        formPayment.querySelector('[name="payment_method"]').value || "cash";
      const received = parseFloat(
        formPayment.querySelector('[name="received"]').value || 0
      );
      const change =
        paymentMethod === "cash" ? Math.max(0, received - totals.total) : 0;

      if (paymentMethod === "cash" && received < totals.total) {
        alert("Uang diterima kurang dari total!");
        return;
      }

      const payload = {
        items: cart.map((i) => ({
          product_id: i.id,
          qty: i.qty,
          price: i.price,
        })),
        subtotal: totals.subtotal,
        discount: totals.discountVal,
        tax: totals.taxAmount,
        total: totals.total,
        payment_method: paymentMethod,
        received,
        change,
      };

      try {
        const res = await api.post("/save-transaction.php", payload);
        // handle success: close modal, clear cart, show receipt or notification
        modal.classList.remove("active");
        cart = [];
        renderCart();
        if (res && res.receipt_id) {
          // open receipt in new tab
          window.open(
            `/Frontend/templates/cashier/receipt.php?id=${res.receipt_id}`,
            "_blank"
          );
        } else {
          alert("Transaksi berhasil disimpan");
        }
      } catch (err) {
        console.error(err);
        alert("Gagal menyimpan transaksi: " + err.message);
      }
    });
  }

  // initial render
  renderCart();
});
