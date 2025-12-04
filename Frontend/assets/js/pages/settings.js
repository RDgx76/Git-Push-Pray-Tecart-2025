/* settings.js
   Store settings page: load current settings, save changes
*/

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formStoreSettings");
  async function load() {
    try {
      const res = await api.get("/get-settings.php");
      if (!res) return;
      Object.keys(res).forEach((k) => {
        const el = form.querySelector(`[name="${k}"]`);
        if (el) el.value = res[k];
      });
    } catch (err) {
      console.error(err);
    }
  }

  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      try {
        const res = await api.post("/change-settings.php", fd);
        if (res.success) {
          alert("Pengaturan disimpan");
        } else {
          alert("Gagal: " + (res.message || "Unknown"));
        }
      } catch (err) {
        console.error(err);
      }
    });
  }

  load();
});
