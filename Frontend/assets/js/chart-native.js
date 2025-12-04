/* chart-native.js
   Tiny chart drawing helpers using Canvas 2D API.
   Supports simple line and bar charts. Not a full-featured library.
*/

const CanvasChart = (function () {
  function clearCanvas(ctx, canvas) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }

  function fitCanvas(canvas) {
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    const ctx = canvas.getContext("2d");
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    return ctx;
  }

  function drawGrid(ctx, w, h, options = {}) {
    const cols = options.cols || 5;
    const rows = options.rows || 4;
    ctx.save();
    ctx.strokeStyle = options.color || "rgba(255,255,255,0.04)";
    ctx.lineWidth = 1;
    for (let i = 1; i <= cols; i++) {
      const x = (w / (cols + 1)) * i;
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, h);
      ctx.stroke();
    }
    for (let j = 1; j <= rows; j++) {
      const y = (h / (rows + 1)) * j;
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(w, y);
      ctx.stroke();
    }
    ctx.restore();
  }

  function drawLineChart(canvas, labels = [], data = [], options = {}) {
    if (!canvas) return;
    const ctx = fitCanvas(canvas);
    const w = canvas.clientWidth,
      h = canvas.clientHeight;
    clearCanvas(ctx, canvas);

    const padding = 30;
    const max = options.max || Math.max(...data, 1);
    const min = options.min || 0;
    const range = max - min || 1;

    // grid
    drawGrid(ctx, w, h, {
      cols: labels.length,
      rows: 4,
      color: options.gridColor,
    });

    // compute points
    const points = data.map((v, i) => {
      const x =
        padding + (i / Math.max(1, data.length - 1)) * (w - padding * 2);
      const y = h - padding - ((v - min) / range) * (h - padding * 2);
      return { x, y, v };
    });

    // area fill
    ctx.beginPath();
    ctx.moveTo(points[0].x, h - padding);
    points.forEach((p) => ctx.lineTo(p.x, p.y));
    ctx.lineTo(points[points.length - 1].x, h - padding);
    ctx.closePath();
    ctx.fillStyle = options.fill || "rgba(108,99,255,0.12)";
    ctx.fill();

    // line
    ctx.beginPath();
    ctx.lineWidth = options.lineWidth || 2.5;
    ctx.strokeStyle = options.stroke || "#6c63ff";
    ctx.moveTo(points[0].x, points[0].y);
    points.forEach((p) => ctx.lineTo(p.x, p.y));
    ctx.stroke();

    // dots
    ctx.fillStyle = options.dotColor || "#fff";
    points.forEach((p) => {
      ctx.beginPath();
      ctx.arc(p.x, p.y, options.dotRadius || 3.5, 0, Math.PI * 2);
      ctx.fill();
    });
  }

  function drawBarChart(canvas, labels = [], data = [], options = {}) {
    if (!canvas) return;
    const ctx = fitCanvas(canvas);
    const w = canvas.clientWidth,
      h = canvas.clientHeight;
    clearCanvas(ctx, canvas);

    const padding = 24;
    const max = options.max || Math.max(...data, 1);
    const min = options.min || 0;
    const range = max - min || 1;

    const barWidth = ((w - padding * 2) / data.length) * 0.7;
    const gap = (w - padding * 2) / data.length - barWidth;

    // grid
    drawGrid(ctx, w, h, {
      cols: labels.length,
      rows: 4,
      color: options.gridColor,
    });

    data.forEach((v, i) => {
      const x = padding + i * (barWidth + gap) + gap / 2;
      const barHeight = ((v - min) / range) * (h - padding * 2);
      const y = h - padding - barHeight;

      // draw bar
      ctx.beginPath();
      const grad = ctx.createLinearGradient(x, y, x, h - padding);
      grad.addColorStop(0, options.startColor || "#4a6bff");
      grad.addColorStop(1, options.endColor || "#00e8ff");
      ctx.fillStyle = grad;
      ctx.fillRect(x, y, barWidth, barHeight);

      // value label
      ctx.fillStyle = options.valueColor || "#fff";
      ctx.font = "12px Inter, Arial";
      ctx.fillText(String(v), x, y - 8);
    });
  }

  return {
    line: drawLineChart,
    bar: drawBarChart,
  };
})();
