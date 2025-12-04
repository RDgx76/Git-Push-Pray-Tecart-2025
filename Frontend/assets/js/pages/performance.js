/* performance.js
   Performance page: fetch performance data per employee and allow export
*/

document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#performanceTable tbody");
  const dateFrom = document.querySelector("#perfFrom");
  const dateTo = document.querySelector("#perfTo");
  const btnExport = document.querySelector("#btnExportPerformance");

  async function load() {
    const from = dateFrom ? dateFrom.value : "";
    const to = dateTo ? dateTo.value : "";
    try {
      const res = await api.get(
        `/performance.php?from=${encodeURIComponent(
          from
        )}&to=${encodeURIComponent(to)}`
      );
      if (!Array.isArray(res)) return;
      tableBody.innerHTML = res
        .map(
          (r) => `
        <tr>
          <td>${r.staff_name}</td>
          <td>${r.transactions}</td>
          <td>Rp ${Number(r.total).toLocaleString()}</td>
        </tr>
      `
        )
        .join("");
    } catch (err) {
      console.error(err);
    }
  }

  [dateFrom, dateTo].forEach((el) => {
    if (!el) return;
    el.addEventListener("change", bytemart.debounce(load, 200));
  });

  if (btnExport) {
    btnExport.addEventListener("click", async () => {
      const from = dateFrom ? dateFrom.value : "";
      const to = dateTo ? dateTo.value : "";
      try {
        const blob = await api.download(
          `/export-performance.php?from=${encodeURIComponent(
            from
          )}&to=${encodeURIComponent(to)}`
        );
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `performance_${from || "start"}_${to || "end"}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
      } catch (err) {
        console.error(err);
        alert("Gagal ekspor: " + err.message);
      }
    });
  }

  load();
});
