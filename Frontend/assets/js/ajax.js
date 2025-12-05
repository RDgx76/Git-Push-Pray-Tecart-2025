/* ajax.js
   Minimal fetch wrapper for backend API endpoints.
   Mengarah ke Backend/routes/api.php dengan parameter ?action=
*/

const API_BASE = "/Backend/routes/api.php";

// Mapping endpoint lama (JS) ke action baru (PHP)
function mapAction(path) {
  // Hapus slash depan jika ada
  let cleanPath = path.startsWith("/") ? path.slice(1) : path;

  // Pisahkan query param jika ada (misal: products.php?q=abc)
  const queryIndex = cleanPath.indexOf("?");
  let queryParams = "";
  if (queryIndex !== -1) {
    queryParams = cleanPath.substring(queryIndex); // ?q=abc
    cleanPath = cleanPath.substring(0, queryIndex); // products.php
  }

  // Peta translasi
  const actionMap = {
    "products.php": "get_products",
    "search-product.php": "search_product",
    "performance.php": "get_performance",
    "staff-list.php": "get_staff",
    "dashboard.php": "get_sales_today", // Estimasi
  };

  // Ambil action dari map, atau gunakan nama file tanpa .php
  let action = actionMap[cleanPath] || cleanPath.replace(".php", "");

  // Gabungkan query param lama dengan & bukan ? karena kita sudah pakai ?action=
  if (queryParams.startsWith("?")) {
    queryParams = "&" + queryParams.slice(1);
  }

  return { action, queryParams };
}

const api = {
  defaultHeaders() {
    return { Accept: "application/json" };
  },

  async get(path, options = {}) {
    const { action, queryParams } = mapAction(path);
    // Construct URL: /Backend/routes/api.php?action=nama_action&param=nilai
    const url = `${API_BASE}?action=${action}${queryParams}`;

    const res = await fetch(url, {
      method: "GET",
      credentials: "same-origin",
      headers: this.defaultHeaders(),
      ...options,
    });
    return handleResponse(res);
  },

  async post(path, body = {}, options = {}) {
    // Note: Jika POST ke endpoint yang tidak ada di api.php switch-case, ini mungkin perlu penyesuaian di index.php
    const { action, queryParams } = mapAction(path);
    const url = `${API_BASE}?action=${action}${queryParams}`;

    const headers = Object.assign(
      { Accept: "application/json" },
      options.headers || {}
    );
    let fetchOptions = {
      method: "POST",
      credentials: "same-origin",
      headers,
    };

    if (body instanceof FormData) {
      fetchOptions.body = body;
    } else {
      headers["Content-Type"] = "application/json";
      fetchOptions.body = JSON.stringify(body);
    }

    const res = await fetch(url, fetchOptions);
    return handleResponse(res);
  },

  async download(path, options = {}) {
    const { action, queryParams } = mapAction(path);
    const url = `${API_BASE}?action=${action}${queryParams}`;

    const res = await fetch(url, { method: "GET", credentials: "same-origin" });
    if (!res.ok) throw new Error("Download failed");
    return res.blob();
  },
};

async function handleResponse(res) {
  const contentType = res.headers.get("Content-Type") || "";
  if (res.ok) {
    if (contentType.includes("application/json")) {
      return res.json();
    }
    return res.text();
  } else {
    if (contentType.includes("application/json")) {
      const err = await res.json().catch(() => ({ message: "Unknown error" }));
      throw new Error(err.message || "Server error");
    } else {
      const txt = await res.text().catch(() => null);
      throw new Error(txt || "Server error");
    }
  }
}

window.api = api;
