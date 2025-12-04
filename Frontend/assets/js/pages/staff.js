/* staff.js
   Staff (employee) management UI interactions.
   Endpoints expected: staff-list.php, add-staff.php, update-staff.php, toggle-staff.php, reset-password.php
*/

document.addEventListener("DOMContentLoaded", () => {
  const staffTable = document.querySelector("#staffTable");
  const addBtn = document.querySelector("#btnAddStaff");
  const modalAdd = document.getElementById("modalAddStaff");
  const formAdd = document.querySelector("#formAddStaff");

  async function loadStaff() {
    try {
      const res = await api.get("/staff-list.php");
      staffTable.querySelector("tbody").innerHTML = res
        .map(
          (s) => `
        <tr data-id="${s.id}">
          <td>${s.username}</td>
          <td>${s.name}</td>
          <td>${s.role}</td>
          <td>${s.active ? "Active" : "Disabled"}</td>
          <td>
            <button class="btn btn-primary" data-edit="${s.id}">Edit</button>
            <button class="btn" data-reset="${s.id}">Reset PW</button>
            <button class="btn btn-danger" data-toggle="${s.id}">${
            s.active ? "Disable" : "Enable"
          }</button>
          </td>
        </tr>
      `
        )
        .join("");
    } catch (err) {
      console.error(err);
    }
  }

  if (addBtn)
    addBtn.addEventListener("click", () => modalAdd.classList.add("active"));

  if (formAdd) {
    formAdd.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(formAdd);
      try {
        const res = await api.post("/add-staff.php", fd);
        if (res.success) {
          modalAdd.classList.remove("active");
          formAdd.reset();
          loadStaff();
        } else alert(res.message || "Gagal menambah pegawai");
      } catch (err) {
        console.error(err);
      }
    });
  }

  staffTable.addEventListener("click", async (e) => {
    const edit = e.target.closest("[data-edit]");
    const reset = e.target.closest("[data-reset]");
    const toggle = e.target.closest("[data-toggle]");
    if (edit) {
      const id = edit.getAttribute("data-edit");
      const data = await api.get(`/get-staff.php?id=${encodeURIComponent(id)}`);
      const modal = document.getElementById("modalEditStaff");
      if (!modal) return;
      modal.querySelector('[name="id"]').value = data.id;
      modal.querySelector('[name="name"]').value = data.name;
      modal.querySelector('[name="role"]').value = data.role;
      modal.classList.add("active");
    } else if (reset) {
      const id = reset.getAttribute("data-reset");
      if (!confirm("Reset password pegawai ini?")) return;
      try {
        const res = await api.post("/reset-password.php", { id });
        alert(res.message || "Password telah direset");
      } catch (err) {
        console.error(err);
        alert("Error");
      }
    } else if (toggle) {
      const id = toggle.getAttribute("data-toggle");
      if (!confirm("Ubah status akun?")) return;
      try {
        const res = await api.post("/toggle-staff.php", { id });
        loadStaff();
      } catch (err) {
        console.error(err);
      }
    }
  });

  // edit form
  const formEdit = document.querySelector("#formEditStaff");
  if (formEdit) {
    formEdit.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(formEdit);
      const res = await api.post("/update-staff.php", fd);
      if (res.success) {
        document.getElementById("modalEditStaff").classList.remove("active");
        loadStaff();
      } else alert(res.message || "Gagal update");
    });
  }

  loadStaff();
});
