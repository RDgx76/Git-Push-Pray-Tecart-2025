/* inventory.js
   Inventory management UI: search/filter, add/edit/delete product (frontend).
   Expects backend endpoints: upload-product.php, delete-product.php, get-product.php
*/

document.addEventListener("DOMContentLoaded", () => {
  const productTable = document.querySelector("#productTable");
  const btnAdd = document.querySelector("#btnAddProduct");
  const modalAdd = document.getElementById("modalAddProduct");
  const formAdd = document.querySelector("#formAddProduct");

  async function loadProducts(q = "") {
    try {
      const res = await api.get(`/products.php?q=${encodeURIComponent(q)}`);
      // res should be array of products
      if (!Array.isArray(res)) return;
      productTable.querySelector("tbody").innerHTML = res
        .map(
          (p) => `
        <tr data-id="${p.id}">
          <td>${p.sku || ""}</td>
          <td>${p.name}</td>
          <td>${p.category || "-"}</td>
          <td>${p.stock}</td>
          <td>Rp ${Number(p.price).toLocaleString()}</td>
          <td>
            <button class="btn btn-primary btn-edit" data-edit="${
              p.id
            }">Edit</button>
            <button class="btn btn-danger btn-delete" data-delete="${
              p.id
            }">Hapus</button>
          </td>
        </tr>
      `
        )
        .join("");
    } catch (e) {
      console.error(e);
    }
  }

  if (btnAdd && modalAdd) {
    btnAdd.addEventListener("click", () => modalAdd.classList.add("active"));
  }

  if (formAdd) {
    formAdd.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(formAdd);
      try {
        const res = await api.post("/upload-product.php", fd);
        if (res.success) {
          modalAdd.classList.remove("active");
          formAdd.reset();
          loadProducts();
          alert("Produk berhasil ditambahkan");
        } else {
          alert("Gagal: " + (res.message || "Unknown"));
        }
      } catch (err) {
        console.error(err);
        alert("Error saat menambah produk: " + err.message);
      }
    });
  }

  // delegate edit / delete buttons
  productTable.addEventListener("click", async (e) => {
    const editBtn = e.target.closest("[data-edit]");
    const delBtn = e.target.closest("[data-delete]");
    if (editBtn) {
      const id = editBtn.getAttribute("data-edit");
      // fetch product and open edit modal (assume modalEdit exists)
      try {
        const product = await api.get(
          `/get-product.php?id=${encodeURIComponent(id)}`
        );
        const modal = document.getElementById("modalEditProduct");
        if (!modal) return;
        modal.querySelector('[name="id"]').value = product.id;
        modal.querySelector('[name="name"]').value = product.name;
        modal.querySelector('[name="price"]').value = product.price;
        modal.querySelector('[name="stock"]').value = product.stock;
        modal.classList.add("active");
      } catch (err) {
        console.error(err);
      }
    } else if (delBtn) {
      const id = delBtn.getAttribute("data-delete");
      if (!confirm("Hapus produk ini?")) return;
      try {
        const res = await api.post("/delete-product.php", { id });
        if (res.success) {
          loadProducts();
        } else {
          alert("Gagal hapus: " + (res.message || "Unknown"));
        }
      } catch (err) {
        console.error(err);
        alert("Error: " + err.message);
      }
    }
  });

  // edit form submit (assume form id formEditProduct)
  const formEdit = document.querySelector("#formEditProduct");
  if (formEdit) {
    formEdit.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(formEdit);
      try {
        const res = await api.post("/update-product.php", fd);
        if (res.success) {
          document
            .getElementById("modalEditProduct")
            .classList.remove("active");
          loadProducts();
        } else {
          alert("Gagal update: " + (res.message || "Unknown"));
        }
      } catch (err) {
        console.error(err);
        alert("Error: " + err.message);
      }
    });
  }

  // quick search input
  const searchInput = document.querySelector("#inventorySearch");
  if (searchInput) {
    searchInput.addEventListener(
      "input",
      bytemart.debounce((e) => {
        loadProducts(e.target.value.trim());
      }, 220)
    );
  }

  // initial load
  loadProducts();
});
