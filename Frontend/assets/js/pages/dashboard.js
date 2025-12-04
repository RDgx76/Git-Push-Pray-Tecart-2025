/* dashboard.js
   Fetch dashboard data and draw charts
*/

document.addEventListener("DOMContentLoaded", () => {
  const salesChart = document.getElementById("chart-sales");
  const topProductsEl = document.getElementById("top-products");

  async function loadDashboard() {
    try {
      const res = await api.get("/dashboard.php"); // expects { salesLabels:[], salesData:[], stats: {...}, topProducts: [...] }
      // render metrics
      if (res.stats) {
        Object.keys(res.stats).forEach((key) => {
          const el = document.querySelector(`[data-stat="${key}"]`);
          if (el) el.textContent = res.stats[key];
        });
      }

      // render line chart
      if (salesChart && res.salesLabels && res.salesData) {
        CanvasChart.line(salesChart, res.salesLabels, res.salesData, {
          stroke: "#6c63ff",
          fill: "rgba(108,99,255,0.12)",
          dotColor: "#fff",
        });
      }

      // top products list
      if (topProductsEl && Array.isArray(res.topProducts)) {
        topProductsEl.innerHTML = res.topProducts
          .map(
            (p) => `
          <div class="card small">
            <div class="product-name">${p.name}</div>
            <div class="product-sold">Sold: ${p.sold}</div>
          </div>
        `
          )
          .join("");
      }
    } catch (err) {
      console.error("Load dashboard failed", err);
    }
  }

  loadDashboard();

  // Time range selector
  const rangeSelect = document.querySelector("#dashboardRange");
  if (rangeSelect) {
    rangeSelect.addEventListener("change", () => {
      // reload with query param
      const val = rangeSelect.value;
      // append ?range=val to api call
      api
        .get(`/dashboard.php?range=${encodeURIComponent(val)}`)
        .then((res) => {
          if (salesChart && res.salesLabels && res.salesData) {
            CanvasChart.line(salesChart, res.salesLabels, res.salesData, {
              stroke: "#6c63ff",
              fill: "rgba(108,99,255,0.12)",
            });
          }
        })
        .catch((e) => console.error(e));
    });
  }
});
