/* ajax.js
   Minimal fetch wrapper for backend API endpoints (native JS).
   Use api.get('/path'), api.post('/path', body)
*/

const API_BASE = "/Backend/api"; // adjust if backend api path berbeda

const api = {
  defaultHeaders() {
    return { Accept: "application/json" };
  },

  async get(path, options = {}) {
    const url = API_BASE + path;
    const res = await fetch(url, {
      method: "GET",
      credentials: "same-origin",
      headers: this.defaultHeaders(),
      ...options,
    });
    return handleResponse(res);
  },

  async post(path, body = {}, options = {}) {
    const url = API_BASE + path;
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
      // let browser set Content-Type for multipart
      fetchOptions.body = body;
    } else {
      headers["Content-Type"] = "application/json";
      fetchOptions.body = JSON.stringify(body);
    }

    const res = await fetch(url, fetchOptions);
    return handleResponse(res);
  },

  async download(path, options = {}) {
    const url = API_BASE + path;
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
    // try to parse json error
    if (contentType.includes("application/json")) {
      const err = await res.json().catch(() => ({ message: "Unknown error" }));
      throw new Error(err.message || "Server error");
    } else {
      const txt = await res.text().catch(() => null);
      throw new Error(txt || "Server error");
    }
  }
}

// expose api globally
window.api = api;
