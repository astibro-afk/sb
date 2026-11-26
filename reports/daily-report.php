<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ежедневный отчет СБ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<style>
:root {
  --petrol: #0D7377;
  --petrol-dark: #0A5C5F;
  --petrol-light: #E0F2F1;
  --coral: #FF6B47;
  --coral-light: #FFE8E0;
  --amber: #FFB627;
  --emerald: #2EC4B6;
  --bg: #F0F7F7;
  --panel: rgba(255,255,255,0.72);
  --panel-solid: #FFFFFF;
  --text: #1A2E35;
  --text-secondary: #5A7A80;
  --text-muted: #9AB0B5;
  --border: rgba(13,115,119,0.12);
  --border-hover: rgba(13,115,119,0.25);
  --shadow-sm: 0 1px 3px rgba(13,115,119,0.06);
  --shadow-md: 0 4px 20px rgba(13,115,119,0.08);
  --shadow-lg: 0 12px 48px rgba(13,115,119,0.12);
  --shadow-open: 0 8px 32px rgba(13,115,119,0.14);
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 20px;
  --grad: linear-gradient(135deg, #0D7377 0%, #14A098 50%, #2EC4B6 100%);
  --grad-coral: linear-gradient(135deg, #FF6B47 0%, #FF9466 100%);
  --grad-progress: linear-gradient(90deg, #0D7377, #2EC4B6, #FF6B47);
  --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
  --ease-smooth: cubic-bezier(0.16, 1, 0.3, 1);
}

* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--bg);
  color: var(--text);
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  position: relative;
  overflow-x: hidden;
  min-height: 100vh;
}

.bg-blob {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.25;
  pointer-events: none;
  z-index: 0;
  animation: floatBlob 20s ease-in-out infinite;
}
.bg-blob:nth-child(1) { width: 500px; height: 500px; background: #0D7377; top: -10%; left: -5%; animation-delay: 0s; }
.bg-blob:nth-child(2) { width: 400px; height: 400px; background: #FF6B47; top: 30%; right: -8%; animation-delay: -5s; }
.bg-blob:nth-child(3) { width: 450px; height: 450px; background: #FFB627; bottom: -10%; left: 20%; animation-delay: -10s; }
.bg-blob:nth-child(4) { width: 350px; height: 350px; background: #2EC4B6; bottom: 20%; right: 15%; animation-delay: -15s; }
@keyframes floatBlob {
  0%, 100% { transform: translate(0,0) scale(1); }
  25% { transform: translate(40px,-30px) scale(1.05); }
  50% { transform: translate(-30px,20px) scale(0.95); }
  75% { transform: translate(20px,30px) scale(1.02); }
}

.app-header {
  background: var(--panel);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border-bottom: 1px solid var(--border);
  padding: 16px 32px;
  position: sticky; top: 0; z-index: 100;
}
.app-header-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between; gap: 20px;
}
.brand { display: flex; align-items: center; gap: 14px; }
.brand-logo {
  width: 44px; height: 44px; border-radius: var(--radius-sm);
  background: var(--grad);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; box-shadow: 0 4px 12px rgba(13,115,119,0.3);
  transition: transform 0.3s var(--ease-bounce);
}
.brand-logo:hover { transform: scale(1.1) rotate(-5deg); }
.brand-text h1 { font-size: 18px; font-weight: 800; letter-spacing: -0.3px; line-height: 1.2; color: var(--text); }
.brand-text span { font-size: 13px; color: var(--text-secondary); font-weight: 500; }
.header-actions { display: flex; gap: 10px; }

.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  border: none; border-radius: var(--radius-sm); padding: 10px 20px;
  font-size: 14px; font-weight: 600; cursor: pointer;
  transition: all 0.3s var(--ease-smooth); white-space: nowrap;
  font-family: inherit; position: relative; overflow: hidden;
}
.btn::after {
  content: ''; position: absolute; top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
  transition: left 0.5s;
}
.btn:hover::after { left: 100%; }
.btn-primary {
  background: var(--grad); color: #fff;
  box-shadow: 0 4px 12px rgba(13,115,119,0.3);
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(13,115,119,0.4); }
.btn-ghost {
  background: var(--petrol-light); color: var(--petrol);
  border: 1px solid var(--border);
}
.btn-ghost:hover { background: rgba(13,115,119,0.15); }
.btn-success { background: var(--emerald); color: #fff; }
.btn-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(46,196,182,0.35); }
.btn-danger {
  background: var(--coral-light); color: var(--coral);
  border: 1px solid rgba(255,107,71,0.2);
  padding: 6px 12px; font-size: 12px;
}
.btn-danger:hover { background: rgba(255,107,71,0.2); }
.btn-outline {
  background: var(--panel-solid); color: var(--text);
  border: 1px solid var(--border);
}
.btn-outline:hover { border-color: var(--petrol); color: var(--petrol); }
.btn-reset {
  background: var(--coral-light); color: var(--coral);
  border: 1px solid rgba(255,107,71,0.25);
}
.btn-reset:hover { background: rgba(255,107,71,0.18); transform: translateY(-1px); }
.btn-block { width: 100%; }
.btn svg { width: 16px; height: 16px; }

.progress-wrap {
  max-width: 1200px; margin: 0 auto; padding: 16px 32px 0;
  position: relative; z-index: 1;
}
.progress-card {
  background: var(--panel); backdrop-filter: blur(16px);
  border: 1px solid var(--border); border-radius: var(--radius-md);
  padding: 16px 20px; box-shadow: var(--shadow-sm);
}
.progress-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;
}
.progress-header span {
  font-size: 12px; font-weight: 700; color: var(--text-secondary);
  text-transform: uppercase; letter-spacing: 0.5px;
  display: flex; align-items: center; gap: 6px;
}
.progress-dot {
  width: 8px; height: 8px; border-radius: 50%; background: var(--coral);
  animation: pulseDot 1.5s ease-in-out infinite;
}
@keyframes pulseDot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.3); }
}
.progress-value {
  font-size: 18px; font-weight: 800; color: var(--petrol);
  font-family: 'JetBrains Mono', monospace;
}
.progress-track {
  height: 8px; background: rgba(13,115,119,0.08); border-radius: 4px; overflow: hidden; position: relative;
}
.progress-fill {
  height: 100%; background: var(--grad-progress); border-radius: 4px;
  transition: width 0.5s var(--ease-smooth);
  position: relative; overflow: hidden;
}
.progress-fill::after {
  content: ''; position: absolute; top: 0; left: -100%;
  width: 50%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  animation: shimmer 2s linear infinite;
}
@keyframes shimmer { 0% { left: -100%; } 100% { left: 200%; } }

.container {
  max-width: 1200px; margin: 0 auto; padding: 24px 32px 60px;
  position: relative; z-index: 1;
}

.card {
  background: var(--panel); backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  border: 1px solid var(--border); border-radius: var(--radius-md);
  margin-bottom: 16px; box-shadow: var(--shadow-sm);
  transition: box-shadow 0.4s var(--ease-smooth), transform 0.3s var(--ease-smooth);
  overflow: hidden; position: relative;
  animation: cardSlideIn 0.5s var(--ease-smooth) both;
}
.card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.card::before {
  content: ''; position: absolute; left: 0; top: 0; bottom: 0;
  width: 4px; background: var(--border);
  transition: background 0.3s var(--ease-smooth), height 0.4s var(--ease-bounce);
  transform-origin: top; transform: scaleY(0);
}
.card.open::before { background: var(--grad); transform: scaleY(1); }
.card.open { box-shadow: var(--shadow-open); }
.card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 22px; cursor: pointer; user-select: none;
  transition: background 0.2s;
}
.card-header:hover { background: rgba(13,115,119,0.03); }
.card.open .card-header { background: linear-gradient(135deg, rgba(13,115,119,0.05), rgba(46,196,182,0.05)); }
.card-title { display: flex; align-items: center; gap: 12px; }
.card-icon {
  width: 36px; height: 36px; border-radius: var(--radius-sm);
  background: var(--petrol-light); color: var(--petrol);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  transition: all 0.4s var(--ease-bounce);
}
.card-icon svg { width: 18px; height: 18px; stroke-width: 2; }
.card.open .card-icon { background: var(--grad); color: #fff; transform: scale(1.1) rotate(-6deg); }
.card-title h3 { font-size: 15px; font-weight: 700; color: var(--text); }
.chevron {
  width: 32px; height: 32px; border-radius: 50%;
  background: rgba(13,115,119,0.06); color: var(--text-secondary);
  display: flex; align-items: center; justify-content: center;
  transition: all 0.4s var(--ease-smooth); flex-shrink: 0;
}
.chevron svg { width: 16px; height: 16px; transition: transform 0.4s var(--ease-bounce); }
.card.open .chevron { background: var(--grad); color: #fff; }
.card.open .chevron svg { transform: rotate(180deg); }

.card-body-wrap {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.5s var(--ease-smooth);
}
.card.open .card-body-wrap { grid-template-rows: 1fr; }
.card-body-inner {
  overflow: hidden;
  opacity: 0;
  transform: translateY(-8px);
  transition: opacity 0.3s ease 0.1s, transform 0.3s ease 0.1s;
}
.card.open .card-body-inner { opacity: 1; transform: translateY(0); }
.card-body { padding: 22px; }

.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }
label {
  display: block; font-size: 13px; font-weight: 600; color: var(--text);
  margin-bottom: 6px;
}
input[type="text"], input[type="number"], input[type="date"], input[type="month"], input[type="week"], textarea, select {
  width: 100%; padding: 12px 14px;
  border: 1.5px solid var(--border); border-radius: var(--radius-sm);
  font-size: 14px; font-family: inherit;
  background: rgba(255,255,255,0.7); color: var(--text);
  transition: all 0.3s var(--ease-smooth);
}
input[type="text"]::placeholder, input[type="number"]::placeholder, input[type="date"]::placeholder, input[type="month"]::placeholder, input[type="week"]::placeholder, textarea::placeholder { color: var(--text-muted); }
input[type="text"]:hover, input[type="number"]:hover, input[type="date"]:hover, input[type="month"]:hover, input[type="week"]:hover, textarea:hover, select:hover { border-color: var(--border-hover); }
input[type="text"]:focus, input[type="number"]:focus, input[type="date"]:focus, input[type="month"]:focus, input[type="week"]:focus, textarea:focus, select:focus {
  outline: none; border-color: var(--petrol);
  box-shadow: 0 0 0 4px rgba(13,115,119,0.1);
  background: #fff;
}
input[type="date"]::-webkit-calendar-picker-indicator,
input[type="week"]::-webkit-calendar-picker-indicator,
input[type="month"]::-webkit-calendar-picker-indicator {
  filter: invert(30%) sepia(60%) saturate(600%) hue-rotate(150deg);
  cursor: pointer; opacity: 0.6; transition: opacity 0.2s;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover,
input[type="week"]::-webkit-calendar-picker-indicator:hover,
input[type="month"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }
textarea { resize: vertical; min-height: 72px; line-height: 1.5; }
select {
  cursor: pointer; appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%230D7377' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 14px center; padding-right: 40px;
}
select.empty { color: var(--text-muted); }

.row { display: flex; gap: 14px; }
.row .form-group { flex: 1; margin-bottom: 0; }
.row + .row { margin-top: 14px; }
.fields-note {
  font-size: 12px; color: var(--text-muted); margin: -6px 0 16px; padding-left: 2px;
}

.preview-section { margin-top: 40px; display: none; }
.preview-section.active { display: block; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.preview-header { text-align: center; margin-bottom: 24px; }
.preview-header h2 { font-size: 20px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.preview-header p { font-size: 13px; color: var(--text-secondary); }
.page-wrap { display: flex; justify-content: center; padding: 20px 0; }

.page {
  width: 210mm; min-height: 297mm; background: #fff;
  padding: 20mm 25mm 25mm 30mm; box-shadow: var(--shadow-lg);
  font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; color: #000;
}
.page h1 { font-size: 12pt; font-weight: bold; text-align: center; margin: 0 0 16pt 0; line-height: 1.35; }
.page p { margin: 0 0 5pt 0; text-indent: 1.25cm; text-align: justify; }
.page p.no-indent { text-indent: 0; }
.page strong { font-weight: bold; }
.page .ph { color: #000000; font-weight: bold; }
.page .sec-title { margin-top: 10pt; }

.sticky-bar {
  position: sticky; bottom: 0;
  background: var(--panel); backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  border-top: 1px solid var(--border); padding: 14px 32px; z-index: 90;
}
.sticky-bar-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; justify-content: space-between; align-items: center; gap: 16px;
}
.sticky-info { font-size: 13px; color: var(--text-secondary); }
.sticky-info strong { color: var(--text); }

.app-footer {
  text-align: center; padding: 20px 32px 28px;
  font-size: 13px; color: var(--text-muted);
  position: relative; z-index: 1;
}
.app-footer .version {
  font-family: 'JetBrains Mono', monospace; font-weight: 700;
  color: var(--petrol); font-size: 13px;
}

.save-toast {
  position: fixed; bottom: 80px; right: 24px;
  background: var(--emerald); color: #fff;
  padding: 12px 20px; border-radius: var(--radius-sm);
  font-size: 14px; font-weight: 600; z-index: 9999;
  display: flex; align-items: center; gap: 8px;
  box-shadow: 0 8px 24px rgba(46,196,182,0.35);
  opacity: 0; transform: translateY(20px);
  transition: all 0.4s var(--ease-bounce); pointer-events: none;
}
.save-toast.show { opacity: 1; transform: translateY(0); }
.save-toast svg { width: 18px; height: 18px; }

.download-overlay {
  position: fixed; inset: 0; z-index: 99999;
  background: rgba(13,115,119,0.15);
  backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none;
  transition: opacity 0.3s ease;
}
.download-overlay.show { opacity: 1; pointer-events: all; }
.download-card {
  background: #fff; border-radius: var(--radius-lg);
  padding: 40px 48px; box-shadow: 0 24px 80px rgba(13,115,119,0.2);
  text-align: center; max-width: 400px; width: 90%;
}
.download-icon {
  width: 64px; height: 64px; margin: 0 auto 20px;
  border-radius: 50%; background: var(--grad);
  display: flex; align-items: center; justify-content: center; color: #fff;
  animation: spin 1s linear infinite;
}
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.download-icon svg { width: 28px; height: 28px; }
.download-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 20px; }
.download-progress-track {
  height: 8px; background: rgba(13,115,119,0.08); border-radius: 4px; overflow: hidden; margin-bottom: 8px;
}
.download-progress-fill {
  height: 100%; background: var(--grad); border-radius: 4px;
  width: 0%; transition: width 0.1s linear;
}
.download-progress-text {
  font-size: 13px; font-weight: 700; color: var(--petrol);
  font-family: 'JetBrains Mono', monospace;
}

.summary-overlay {
  position: fixed; inset: 0; z-index: 99998;
  background: rgba(13,115,119,0.3); backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
}
.summary-overlay.show { opacity: 1; pointer-events: all; }
.summary-card {
  background: #fff; border-radius: var(--radius-lg);
  padding: 32px 40px; box-shadow: 0 24px 80px rgba(13,115,119,0.2);
  max-width: 480px; width: 90%; animation: popIn 0.3s var(--ease-bounce);
}
@keyframes popIn {
  from { opacity: 0; transform: translateX(-12px); }
  to { opacity: 1; transform: translateX(0); }
}
.summary-title { font-size: 20px; font-weight: 800; color: var(--text); margin-bottom: 4px; }
.summary-subtitle { font-size: 14px; color: var(--text-secondary); margin-bottom: 20px; }
.summary-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
.summary-table td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid var(--border); }
.summary-table td:first-child { color: var(--text-secondary); }
.summary-table td:last-child { font-weight: 600; text-align: right; color: var(--text); }
.summary-actions { display: flex; gap: 12px; justify-content: flex-end; }

.scroll-top-btn {
  position: fixed; bottom: 80px; right: 24px;
  width: 44px; height: 44px; border-radius: 50%;
  background: var(--grad); color: #fff; border: none;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; z-index: 95; box-shadow: var(--shadow-md);
  opacity: 0; pointer-events: none; transition: all 0.3s var(--ease-bounce);
}
.scroll-top-btn.show { opacity: 1; pointer-events: all; }
.scroll-top-btn:hover { transform: translateY(-4px) scale(1.1); box-shadow: var(--shadow-lg); }
.scroll-top-btn svg { width: 20px; height: 20px; }

::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(13,115,119,0.2); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: rgba(13,115,119,0.35); }

@media (max-width: 768px) {
  .app-header-inner { flex-direction: column; align-items: flex-start; }
  .container, .progress-wrap { padding-left: 16px; padding-right: 16px; }
  .row { flex-direction: column; gap: 0; }
  .row + .row { margin-top: 0; }
  .row .form-group { margin-bottom: 14px; }
  .page-wrap { justify-content: flex-start; overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .page { width: 210mm; min-width: 210mm; padding: 18mm 16mm 20mm; }
  .sticky-bar-inner { flex-direction: column; align-items: stretch; }
  .sticky-info { text-align: center; }
}

@media (max-width: 900px) {
  .app-header-inner { flex-wrap: wrap; gap: 10px; }
  .app-header { padding: 14px 16px; }
  .header-actions { flex-wrap: wrap; }
  .header-actions .btn { padding: 8px 14px; font-size: 12px; }
  .brand-text h1 { font-size: 16px; }
}
@media (max-width: 768px) {
  input[type="text"], input[type="number"], input[type="date"], input[type="month"], input[type="week"], textarea, select { font-size: 16px; padding: 10px 12px; }
  .card-body { padding: 16px; }
  .card-header { padding: 14px 16px; }
  .sum-table { min-width: 520px; }
}
@media (max-width: 480px) {
  .header-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
  .header-actions .btn { width: 100%; font-size: 11px; padding: 8px 6px; }
  .brand-text h1 { font-size: 15px; }
  .card-body { padding: 14px; }
  .card-header { padding: 12px 14px; }
  .app-footer { padding: 16px 16px 22px; font-size: 12px; }
}
  .dyn-spoiler{margin:14px 0 4px;border:1px solid var(--border);border-radius:12px;background:rgba(255,255,255,0.55);overflow:hidden;transition:box-shadow .25s}
  .dyn-spoiler.open{box-shadow:var(--shadow-sm)}
  .dyn-head{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;color:var(--petrol);font-weight:600;user-select:none;transition:background .2s}
  .dyn-head:hover{background:var(--petrol-light)}
  .dyn-arrow{transition:transform .25s;display:inline-block}
  .dyn-spoiler.open .dyn-arrow{transform:rotate(90deg)}
  .dyn-body{display:none;padding:4px 14px 10px;flex-direction:column;gap:10px}
  .dyn-spoiler.open .dyn-body{display:flex}
  .dyn-row{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;padding:10px;border:1px dashed var(--border-hover);border-radius:10px;background:#fff}
  .dyn-row .form-group{flex:1;min-width:130px;margin:0}
  .dyn-del{background:var(--coral-light);color:var(--coral);border:none;border-radius:8px;width:34px;height:38px;font-size:15px;cursor:pointer;flex-shrink:0;transition:all .2s}
  .dyn-del:hover{background:var(--coral);color:#fff}
  .dyn-add{margin:0 14px 12px;background:var(--petrol-light);color:var(--petrol-dark);border:1px dashed var(--petrol);border-radius:10px;padding:8px 14px;cursor:pointer;font-weight:600;transition:all .2s}
  .dyn-add:hover{background:var(--petrol);color:#fff}

/* --- v1.74: бейдж заполнения на карточках --- */
.card-count {
  font-size: 11px; font-weight: 600; line-height: 1;
  color: var(--text-secondary); background: rgba(13,115,119,0.08);
  border-radius: 999px; padding: 4px 9px; white-space: nowrap; flex-shrink: 0;
  transition: all 0.3s var(--ease-smooth);
}
.card-count.started { background: rgba(217,119,6,0.14); color: #b45309; }
.card-count.done { background: rgba(16,185,129,0.14); color: #047857; }

/* --- v1.74: селект + кнопка редактирования списка --- */
.select-wrap { display: flex; gap: 8px; align-items: stretch; }
.select-wrap select { flex: 1; }
.list-edit-btn {
  width: 38px; border: 1px solid rgba(13,115,119,0.25); background: #fff;
  color: var(--petrol); border-radius: var(--radius-sm); cursor: pointer;
  font-size: 15px; flex-shrink: 0; transition: all 0.2s;
}
.list-edit-btn:hover { background: var(--petrol-light); }

/* --- v1.74: списки внутри модалок --- */
.archive-card { max-width: 560px; max-height: 82vh; overflow: auto; }
.archive-list { max-height: 320px; overflow: auto; margin: 4px 0 8px; display: flex; flex-direction: column; gap: 8px; }
.archive-item {
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
  padding: 10px 14px; border: 1px solid rgba(13,115,119,0.15);
  border-radius: var(--radius-sm); background: #fff;
}
.archive-item-info { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.archive-item-title { font-weight: 600; font-size: 14px; color: var(--text); }
.archive-item-sub { font-size: 12px; color: var(--text-secondary); }
.archive-item-actions { display: flex; gap: 6px; flex-shrink: 0; }
.mini-btn {
  border: 1px solid rgba(13,115,119,0.3); background: #fff; color: var(--petrol);
  border-radius: var(--radius-sm); padding: 5px 10px; cursor: pointer;
  font-size: 12px; transition: all 0.2s; white-space: nowrap;
}
.mini-btn:hover { background: var(--petrol-light); }
.mini-btn:disabled { opacity: 0.4; cursor: default; }
.mini-btn.danger { border-color: rgba(220,38,38,0.35); color: #b91c1c; }
.mini-btn.danger:hover { background: rgba(220,38,38,0.08); }
.archive-empty {
  padding: 24px; text-align: center; color: var(--text-secondary); font-size: 14px;
  border: 1px dashed rgba(13,115,119,0.25); border-radius: var(--radius-sm);
}
.list-edit-row { display: flex; gap: 8px; margin: 4px 0 12px; }
.list-edit-row input {
  flex: 1; padding: 10px 12px; border: 1px solid rgba(13,115,119,0.25);
  border-radius: var(--radius-sm); font-size: 14px; outline: none;
}
.list-edit-row input:focus { border-color: var(--petrol); }

/* --- v1.74: сводная таблица в предпросмотре --- */
  .arc-status { display: inline-block; margin-left: 10px; padding: 2px 9px; border-radius: 8px; font-size: 11px; font-weight: 700; vertical-align: 1px; }
  .arc-status.ok { background: rgba(46,196,182,0.16); color: #1F9C90; }
  .arc-status.bad { background: rgba(255,107,71,0.16); color: #E0512E; }
/* ===== v1.97: перевёрстка окна архива (шире и стильнее) ===== */
.archive-card--wide { max-width: 672px; }
.archive-card--wide .summary-title {
  font-size: 24px; letter-spacing: -0.4px;
  display: flex; align-items: center; gap: 10px;
}
.archive-card--wide .summary-title::before {
  content: ''; width: 6px; height: 24px; border-radius: 4px;
  background: var(--grad); flex-shrink: 0;
}
.archive-card--wide .summary-subtitle { margin-bottom: 16px; }
.archive-card--wide .row { gap: 14px; }
.archive-list { max-height: 380px; gap: 10px; padding-right: 4px; }
.archive-list::-webkit-scrollbar { width: 8px; }
.archive-list::-webkit-scrollbar-track { background: transparent; }
.archive-list::-webkit-scrollbar-thumb { background: rgba(13,115,119,0.25); border-radius: 4px; }
.archive-list::-webkit-scrollbar-thumb:hover { background: rgba(13,115,119,0.4); }
.archive-item {
  padding: 12px 16px; border-radius: 12px;
  border: 1px solid var(--border); border-left: 4px solid var(--petrol);
  background: #fff; box-shadow: var(--shadow-sm);
  transition: transform 0.25s var(--ease-smooth), box-shadow 0.25s var(--ease-smooth), border-color 0.25s;
}
.archive-item:hover {
  transform: translateY(-1px); box-shadow: var(--shadow-md);
  border-left-color: var(--emerald);
  background: linear-gradient(90deg, rgba(224,242,241,0.35), #fff 45%);
}
.archive-item-title { font-size: 15px; }
.archive-item-sub { line-height: 1.45; }
.archive-item-actions .mini-btn {
  padding: 6px 12px; font-size: 12.5px; font-weight: 600;
  box-shadow: 0 1px 2px rgba(13,115,119,0.08);
}
.archive-card--wide .mini-btn:hover { border-color: var(--petrol); }

  /* ---------- Навигация-оглавление (v1.87) ---------- */
  .app-nav {
    position: fixed; left: 14px; top: 50%; transform: translateY(-50%);
    z-index: 60; display: flex; flex-direction: column; gap: 2px;
    background: rgba(255,255,255,0.92); border: 1px solid var(--border);
    border-radius: 14px; padding: 8px 6px;
    box-shadow: 0 8px 24px rgba(13,115,119,0.12);
    backdrop-filter: blur(6px);
  }
  .app-nav-item {
    display: flex; align-items: center; gap: 6px;
    padding: 5px 8px; border-radius: 8px; font-size: 11px; font-family: inherit;
    color: var(--text-muted); cursor: pointer; white-space: nowrap;
    transition: all 0.15s; border: none; background: transparent; text-align: left;
  }
  .app-nav-item:hover { background: var(--peach); color: var(--text); }
  .app-nav-item.active { background: var(--peach); color: var(--brand); font-weight: 600; }
  .app-nav-item .nav-check { color: var(--brand); font-weight: 700; visibility: hidden; width: 11px; flex-shrink: 0; }
  .app-nav-item.done .nav-check { visibility: visible; }
  .nav-toggle-btn {
    display: none; position: fixed; left: 14px; bottom: 90px; z-index: 60;
    width: 40px; height: 40px; border-radius: 50%;
    border: 1px solid var(--border); background: rgba(255,255,255,0.95);
    box-shadow: 0 8px 24px rgba(13,115,119,0.18); cursor: pointer;
    align-items: center; justify-content: center; color: var(--brand);
  }
  .nav-toggle-btn svg { width: 18px; height: 18px; }
  .nav-toggle-btn:hover { background: var(--peach); }
  @media (max-width: 1280px) {
    .app-nav { display: none; max-height: 80vh; overflow: auto; }
    .app-nav.mobile-open { display: flex; }
    .nav-toggle-btn { display: flex; }
  }

  .card.nav-focus {
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.25), var(--shadow-sm);
    transition: box-shadow 0.35s ease, border-color 0.35s ease;
  }

@media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; } }

.quality-warn{margin:12px 0;padding:12px 14px;border:1px solid #e6c56a;border-radius:10px;background:#fff8e5;color:#5d4710;font-size:13px;line-height:1.5;max-height:220px;overflow:auto}.quality-warn.ok{border-color:#9bc9a5;background:#edf8ef;color:#205b2b}.quality-warn strong{display:block;margin-bottom:5px}.quality-warn ul{margin:5px 0 0;padding-left:20px}.quality-warn li{margin:3px 0}

/* Единая шапка SB — размеры и оформление как на главной странице */
.sb-topbar{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.95);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid #DCE9E7}
.sb-top-inner{max-width:1240px;margin:0 auto;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.sb-brand{display:flex;align-items:center;gap:12px;text-decoration:none;color:#1A3438;min-width:0}
.sb-logo{width:42px;height:42px;flex:0 0 42px;border-radius:12px;background:linear-gradient(135deg,#0d7377,#2ec4b6);display:grid;place-items:center;color:#fff;font-size:18px;font-weight:800}
.sb-brand-text{display:flex;flex-direction:column;min-width:0}
.sb-brand-text strong{font-size:16px;line-height:1.2;font-weight:800}
.sb-brand-text small{font-size:11px;color:#5A777B;margin-top:3px}
.sb-header-right{display:flex;align-items:center;justify-content:flex-end;gap:9px;flex-wrap:wrap}
.sb-user-chip{padding:6px 11px;border:1px solid #DCE9E7;border-radius:9px;background:#f8fcfb;display:flex;flex-direction:column;line-height:1.3}
.sb-user-name{font-size:12px;font-weight:750}
.sb-user-meta{font-size:10px;color:#5A777B}
.sb-header-btn{border:1px solid #DCE9E7;background:#fff;color:#1A3438;border-radius:9px;padding:8px 12px;font:600 12px Inter,Arial,sans-serif;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:7px;text-decoration:none;white-space:nowrap}
.sb-header-btn:hover{border-color:#9bc9c5;color:#0D7377}
.sb-header-btn #notificationBadge{min-width:17px;height:17px;padding:0 5px;border-radius:10px;background:#d94841;color:#fff;font-size:10px;line-height:17px;text-align:center;font-weight:700}
.sb-nav{border-top:1px solid #f0f4f3}
.sb-nav-inner{max-width:1240px;margin:0 auto;padding:0 24px;display:flex;gap:5px;overflow:auto}
.sb-nav a{padding:10px 14px;color:#71878a;font-size:12px;font-weight:650;white-space:nowrap;border-bottom:2px solid transparent;text-decoration:none}
.sb-nav a:hover,.sb-nav a.active{color:#0D7377;border-bottom-color:#0D7377}
@media(max-width:850px){.sb-top-inner{padding:11px 15px}.sb-nav-inner{padding:0 10px}}
@media(max-width:650px){.sb-top-inner{align-items:flex-start;flex-direction:column;gap:10px}.sb-header-right{width:100%;justify-content:flex-start}.sb-user-chip{max-width:100%}}

</style>
<style>
  .dup-warn{margin:12px 0;padding:10px 14px;border-radius:10px;background:#FFF7E0;border:1px solid rgba(255,182,39,.55);color:#7A5200;font-size:13px;line-height:1.45}
  .hist-overlay{position:fixed;inset:0;background:rgba(10,40,43,.45);display:flex;align-items:center;justify-content:center;z-index:400;padding:20px;opacity:0;transition:opacity .25s}
  .hist-overlay.show{opacity:1}
  .hist-card{background:#fff;border-radius:16px;max-width:540px;width:100%;max-height:80vh;overflow:auto;padding:22px 24px;box-shadow:0 12px 48px rgba(13,115,119,.28)}
  .hist-title{font-size:18px;font-weight:800;color:#1A2E35;margin-bottom:6px}
  .hist-sub{font-size:13px;color:#5A7A80;margin-bottom:14px;line-height:1.4}
  .hist-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border:1px solid rgba(13,115,119,.12);border-radius:10px;margin-bottom:8px}
  .hist-item-info{display:flex;flex-direction:column;gap:2px}
  .hist-when{font-weight:700;font-size:13px;color:#1A2E35}
  .hist-fio{font-size:12px;color:#5A7A80}
  .hist-actions{margin-top:14px;display:flex;justify-content:flex-end}

  /* ===== v1.96: дашборд месяца (вертикальная вёрстка) ===== */
  .archive-stats{display:flex;flex-direction:column;gap:10px;margin:4px 0 14px}
  .astat{background:var(--panel-solid);border:1px solid var(--border);border-radius:var(--radius-md);padding:14px 18px;box-shadow:var(--shadow-sm)}
  .astat-head{display:flex;justify-content:space-between;align-items:baseline;gap:12px;flex-wrap:wrap}
  .astat-label{font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-secondary)}
  .astat-val{font-size:26px;font-weight:800;color:var(--petrol);font-family:'JetBrains Mono',monospace}
  .astat-sub{font-size:12px;color:var(--text-muted);margin-top:2px}
  .astat-bar{height:8px;border-radius:4px;background:rgba(13,115,119,.08);overflow:hidden;margin-top:10px}
  .astat-bar-fill{height:100%;border-radius:4px;background:var(--grad);transition:width .5s var(--ease-smooth)}
  .astat.ok .astat-val{color:var(--emerald)}
  .astat.bad .astat-val{color:var(--coral)}
  .astat-top{margin-top:10px;display:flex;flex-direction:column;gap:8px}
  .astat-row{display:flex;align-items:center;gap:10px}
  .astat-rank{width:22px;height:22px;border-radius:6px;background:var(--petrol-light);color:var(--petrol);font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .astat-name{flex:0 0 auto;min-width:180px;font-size:13px;font-weight:600;color:var(--text)}
  .astat-track{flex:1;height:8px;border-radius:4px;background:rgba(13,115,119,.08);overflow:hidden}
  .astat-track-fill{height:100%;border-radius:4px;background:var(--grad);transition:width .5s var(--ease-smooth)}
  .astat-nums{flex:0 0 auto;font-size:12px;color:var(--text-secondary);font-family:'JetBrains Mono',monospace;white-space:nowrap}
  .astat-empty{font-size:13px;color:var(--text-muted);padding:6px 0}
  @media (max-width:640px){.astat-name{min-width:120px;font-size:12px}.astat-val{font-size:22px}}
.app-footer{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:12px;padding:14px 20px 22px;color:var(--text-muted);font-size:11px}
.footer-admin-actions{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:7px}
.app-footer .footer-admin-btn{width:auto;min-height:30px;padding:6px 11px;border:1px solid var(--border);border-radius:8px;background:rgba(255,255,255,.72);color:var(--text-muted);font:600 11px Inter,sans-serif;cursor:pointer;white-space:nowrap;transition:background .15s ease,border-color .15s ease,color .15s ease}
.app-footer .footer-admin-btn:hover{background:#fff;border-color:var(--border-hover);color:var(--petrol)}
@media(max-width:620px){.app-footer{gap:8px;padding:14px 12px 20px}.footer-admin-actions{width:100%}}
.btn-test-footer{border:1px solid var(--border);background:transparent;color:var(--text-muted);border-radius:6px;padding:4px 8px;font:500 10px Inter,sans-serif;cursor:pointer;opacity:.72}.btn-test-footer:hover{opacity:1;color:var(--petrol);border-color:var(--border-hover);background:rgba(255,255,255,.65)}

input[type="number"] { text-align: right; font-variant-numeric: tabular-nums; }
input[type="number"]:focus,
input[type="text"]:focus,
input[type="date"]:focus,
input[type="month"]:focus, input[type="week"]:focus,
textarea:focus,
select:focus {
  border-color: var(--petrol);
  box-shadow: 0 0 0 4px rgba(13,115,119,.16), 0 0 0 1px var(--petrol);
}
.number-field-wrap { position: relative; display: block; width: 100%; }
.number-field-wrap .number-field-display {
  position: absolute; z-index: 1; inset: 0;
  display: flex; align-items: center; justify-content: flex-end;
  padding: 0 14px; overflow: hidden; white-space: nowrap;
  color: var(--text); pointer-events: none;
  font: inherit; font-size: 14px; text-align: right;
  font-variant-numeric: tabular-nums;
}
.number-field-wrap:not(.is-focused) input[type="number"] {
  color: transparent !important;
  caret-color: transparent;
}
.number-field-wrap.is-focused .number-field-display { display: none; }
.number-field-wrap input[type="number"] { width: 100%; }
textarea[data-field] {
  display: block; width: 100%; min-height: 72px;
  resize: none; overflow-y: hidden; line-height: 1.5;
}
@media (max-width: 768px) {
.number-field-wrap .number-field-display { padding-left: 12px; padding-right: 12px; }
}

</style>
<style id="ui-layout-v135">
/* v1.3.5 — сохранена исходная палитра v1.3.1; переработана компоновка полей */

/* Карточки остаются в логическом порядке, но получают более чёткую внутреннюю структуру. */
.container { max-width: 1160px; padding-left: 26px; padding-right: 26px; }
.card { margin-bottom: 15px; }
.card-header { min-height: 62px; padding: 16px 20px; gap: 14px; }
.card-title { min-width: 0; flex: 1 1 auto; align-items: center; gap: 12px; }
.card-title h3 { min-width: 0; line-height: 1.35; }
.card-count { margin-left: auto; }
.card-body { padding: 21px 22px 23px; }

/* Каждая исходная строка — самостоятельная группа связанных показателей.
   Группы выровнены слева, а поля имеют одинаковую компактную ширину.
   В отличие от предыдущей версии, элементы не центрируются по карточке. */
.card-body > .row {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  align-items: stretch;
  gap: 12px 16px;
  width: fit-content;
  max-width: 100%;
  margin: 0 0 14px;
  padding: 14px 15px;
  border: 1px solid var(--border);
  border-left: 3px solid var(--petrol);
  border-radius: 12px;
  background: rgba(224, 242, 241, .34);
  box-sizing: border-box;
}
.card-body > .row:last-of-type { margin-bottom: 14px; }
.card-body > .row .form-group {
  display: flex;
  flex: 0 1 258px;
  flex-direction: column;
  justify-content: flex-end;
  width: 258px;
  max-width: 100%;
  min-width: 0;
  min-height: 78px;
  margin: 0;
}
.card-body > .row label {
  display: flex;
  align-items: flex-end;
  min-height: 36px;
  margin: 0 0 7px;
  line-height: 1.32;
  font-size: 12.5px;
}
.card-body > .row input[type="text"],
.card-body > .row input[type="number"],
.card-body > .row input[type="date"],
.card-body > .row input[type="month"], input[type="week"],
.card-body > .row select,
.card-body > .row .number-field-wrap,
.card-body > .row .select-wrap {
  width: 100%;
  max-width: 258px;
  min-width: 0;
  box-sizing: border-box;
}
.card-body > .row input[type="text"],
.card-body > .row input[type="number"],
.card-body > .row input[type="date"],
.card-body > .row input[type="month"], input[type="week"],
.card-body > .row select {
  height: 42px;
  min-height: 42px;
  padding: 10px 12px;
  text-align: left !important;
}
.card-body > .row input[type="number"] {
  direction: ltr;
  text-align: left !important;
  font-variant-numeric: tabular-nums;
}
.card-body > .row .number-field-wrap .number-field-display {
  display: flex;
  align-items: center;
  justify-content: flex-start !important;
  height: 42px;
  padding: 0 12px !important;
  text-align: left !important;
  direction: ltr;
  font-variant-numeric: tabular-nums;
}

/* Свободный текст не смешивается с числовыми блоками. */
.card-body > .form-group {
  width: min(100%, 820px);
  margin: 17px 0 2px;
}
.card-body > .form-group label {
  margin-bottom: 7px;
  font-size: 12.5px;
}
.card-body > .form-group textarea,
.card-body > .form-group textarea[data-field] {
  display: block;
  width: 100%;
  max-width: 100%;
  min-height: 86px;
  padding: 11px 13px;
  text-align: left;
}

/* Вложенные списки используют ту же сетку и цветовую систему. */
.dyn-spoiler {
  margin: 15px 0 5px;
  border-radius: 12px;
  border-color: var(--border);
  background: rgba(255,255,255,.72);
}
.dyn-head { padding: 12px 15px; }
.dyn-body { padding: 10px 13px 13px; gap: 10px; }
.dyn-row {
  justify-content: flex-start;
  align-items: flex-end;
  gap: 12px 15px;
  padding: 12px 13px;
  border: 1px solid var(--border);
  border-left: 3px solid var(--petrol);
  border-radius: 10px;
  background: rgba(224,242,241,.28);
}
.dyn-row .form-group {
  display: flex;
  flex: 0 1 258px;
  flex-direction: column;
  justify-content: flex-end;
  width: 258px;
  max-width: 100%;
  min-width: 0;
  min-height: 76px;
  margin: 0;
}
.dyn-row .form-group label { min-height: 34px; margin-bottom: 7px; }
.dyn-row input { width: 100%; height: 42px; text-align: left !important; }
.dyn-del { flex: 0 0 38px; width: 38px; height: 42px; }
.dyn-add { margin: 0 14px 13px; }

/* Адаптация без изменения порядка полей. */
@media (max-width: 900px) {
  .container { padding-left: 20px; padding-right: 20px; }
  .card-body { padding: 18px 17px 20px; }
  .card-body > .row { gap: 11px 13px; padding: 12px 13px; }
  .card-body > .row .form-group,
  .dyn-row .form-group { flex-basis: 240px; width: 240px; }
  .card-body > .row input[type="text"],
  .card-body > .row input[type="number"],
  .card-body > .row input[type="date"],
  .card-body > .row input[type="month"], input[type="week"],
  .card-body > .row select,
  .card-body > .row .number-field-wrap,
  .card-body > .row .select-wrap { max-width: 240px; }
}
@media (max-width: 640px) {
  .container { padding-left: 12px; padding-right: 12px; }
  .card-header { padding: 13px 14px; gap: 9px; }
  .card-title { gap: 9px; }
  .card-title h3 { font-size: 13px; }
  .card-body { padding: 14px 12px 16px; }
  .card-body > .row {
    gap: 10px;
    padding: 12px;
    margin-bottom: 11px;
  }
  .card-body > .row .form-group,
  .dyn-row .form-group {
    flex: 1 1 100%;
    width: 100%;
    max-width: 100%;
    min-height: 0;
  }
  .card-body > .row label,
  .dyn-row .form-group label { min-height: 0; margin-bottom: 5px; }
  .card-body > .row input[type="text"],
  .card-body > .row input[type="number"],
  .card-body > .row input[type="date"],
  .card-body > .row input[type="month"], input[type="week"],
  .card-body > .row select,
  .card-body > .row .number-field-wrap,
  .card-body > .row .select-wrap { max-width: 100%; }
  .card-body > .form-group { margin-top: 14px; }
  .dyn-row { padding: 10px; }
  .dyn-del { flex: 0 0 38px; }
}
</style>


<style id="ui-layout-v136">
/* v1.3.6 — карточки собраны в аккуратную сетку. Палитра v1.3.1 сохранена. */

/* Более чистый фон: цвет остаётся в акцентах интерфейса, а не в крупных пятнах. */
.bg-blob { display: none !important; }
body { background: #F3F8F7; }

/* Рабочая область */
.container { max-width: 1180px; padding: 18px 26px 118px; }
.card {
  margin: 0 0 16px;
  border: 1px solid #DDEBE9;
  border-radius: 15px;
  background: #FFFFFF;
  box-shadow: 0 3px 12px rgba(13, 72, 73, .045);
  overflow: hidden;
}
.card:hover { transform: none; box-shadow: 0 5px 16px rgba(13, 72, 73, .075); }
.card::before { display: none !important; }
.card.open { border-color: #B9D8D4; box-shadow: 0 5px 18px rgba(13, 72, 73, .075); }
.card-header {
  min-height: 64px;
  padding: 14px 19px;
  background: #FFFFFF;
  border-bottom: 1px solid transparent;
  gap: 13px;
}
.card-header:hover { background: #FBFEFD; }
.card.open .card-header { background: #F1F9F8; border-bottom-color: #E0EFED; }
.card-title { min-width: 0; flex: 1 1 auto; gap: 12px; }
.card-icon {
  width: 36px; height: 36px; flex: 0 0 36px;
  border-radius: 10px;
  background: #E5F3F1; color: #0D7377;
  box-shadow: inset 0 0 0 1px rgba(13,115,119,.06);
}
.card.open .card-icon { background: #0D7377; color: #FFFFFF; }
.card-title h3 { color: #203D40; font-size: 14px; font-weight: 700; line-height: 1.4; }
.card-count {
  margin-left: auto; padding: 4px 9px;
  color: #527274; background: #EDF5F4; border: 1px solid #DCEBE8;
  border-radius: 7px; font-size: 11px; font-weight: 700;
}
.card-count.started { color: #986313; background: #FFF7E8; border-color: #F2E4C6; }
.card-count.done { color: #167C69; background: #E9F7F2; border-color: #CFECE2; }
.chevron { width: 30px; height: 30px; border-radius: 9px; color: #477477; background: #EAF4F2; }
.card.open .chevron { color: #FFFFFF; background: #0D7377; }
.card-body { 
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: 12px;
  padding: 18px 19px 20px;
}

/* Строки становятся компактными карточками. Две группы — две колонки,
   три группы — одна широкая карточка с тремя равными колонками. */
.card-body > .row {
  display: flex;
  flex: 0 1 calc(50% - 6px);
  flex-wrap: nowrap;
  align-content: start;
  align-items: stretch;
  gap: 10px 13px;
  width: calc(50% - 6px);
  max-width: calc(50% - 6px);
  min-width: 0;
  margin: 0;
  padding: 13px 14px 14px;
  border: 1px solid #E2EFED;
  border-top: 3px solid #0D9996;
  border-left: 1px solid #E2EFED;
  border-radius: 12px;
  background: #FAFDFC;
  box-sizing: border-box;
  transition: background .16s ease, border-color .16s ease, box-shadow .16s ease;
}
.card-body > .row:hover {
  background: #F6FBFA;
  border-color: #CFE4E0;
  border-top-color: #0D7377;
  box-shadow: 0 2px 8px rgba(13,115,119,.045);
}
.card-body > .row:has(> .form-group:nth-child(3)) {
  flex: 1 1 100%;
  width: 100%; max-width: 100%;
  border-top-color: #FF8A68;
}
.card-body > .row:has(> .form-group:only-child) {
  flex: 0 1 calc(50% - 6px);
  width: calc(50% - 6px); max-width: calc(50% - 6px);
  border-top-color: #FFB627;
}
.card-body > .row .form-group {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  flex: 1 1 0;
  width: 0; max-width: none; min-width: 0; min-height: 0;
  margin: 0;
}
.card-body > .row label {
  display: flex;
  align-items: flex-start;
  min-height: 36px;
  margin: 0 0 6px;
  color: #476263;
  font-size: 11.5px;
  font-weight: 650;
  line-height: 1.35;
}
.card-body > .row input[type="text"],
.card-body > .row input[type="number"],
.card-body > .row input[type="date"],
.card-body > .row input[type="month"], input[type="week"],
.card-body > .row select,
.card-body > .row .number-field-wrap,
.card-body > .row .select-wrap {
  display: block;
  width: 100%; max-width: none; min-width: 0;
  box-sizing: border-box;
}
.card-body > .row input[type="text"],
.card-body > .row input[type="number"],
.card-body > .row input[type="date"],
.card-body > .row input[type="month"], input[type="week"],
.card-body > .row select {
  height: 41px; min-height: 41px;
  padding: 9px 11px;
  border: 1px solid #D6E7E4;
  border-radius: 8px;
  background: #FFFFFF;
  color: #182F31;
  text-align: left !important;
  box-shadow: none;
}
.card-body > .row input:focus,
.card-body > .row select:focus {
  border-color: #0D9996;
  box-shadow: 0 0 0 3px rgba(13,153,150,.10);
}
.card-body > .row input[type="number"] { direction: ltr; font-variant-numeric: tabular-nums; }
.card-body > .row .number-field-wrap { max-width: none; }
.card-body > .row .number-field-wrap .number-field-display {
  display: flex; justify-content: flex-start !important; align-items: center;
  width: 100%; height: 41px; padding: 0 11px !important;
  border: 1px solid #D6E7E4; border-radius: 8px;
  background: #FFFFFF; color: #182F31;
  text-align: left !important; direction: ltr;
  font-variant-numeric: tabular-nums;
}

/* Реквизиты остаются одной широкой строкой. */
#card0 .card-body > .row { flex-basis: 100%; width: 100%; max-width: 100%; border-top-color: #0D7377; }
#card0 .card-body > .row .form-group { flex-basis: 0; }

/* Три парных блока в разделе хищений балансируются симметрично. */
#card10 .card-body > .row:nth-of-type(3) { margin-left: auto; margin-right: auto; }

/* Комментарии и подробные списки не смешиваются с карточками показателей. */
.card-body > .form-group {
  flex: 1 0 100%;
  width: 100%; max-width: 100%;
  margin: 5px 0 0;
  padding: 14px 15px 15px;
  border: 1px solid #E7ECE8;
  border-left: 3px solid #FFB627;
  border-radius: 11px;
  background: #FFFEFA;
}
.card-body > .form-group label { margin: 0 0 7px; color: #426263; font-size: 12px; font-weight: 700; }
.card-body > .form-group textarea,
.card-body > .form-group textarea[data-field] {
  display: block; width: 100%; max-width: 100%; min-height: 82px;
  padding: 11px 12px; border: 1px solid #D6E7E4; border-radius: 8px;
  background: #FFFFFF; color: #182F31; text-align: left;
}
.dyn-spoiler {
  flex: 1 0 100%;
  width: 100%;
  margin: 1px 0 0;
  border: 1px solid #E0ECEA; border-radius: 12px;
  background: #F9FCFB; box-shadow: none;
}
.dyn-head { padding: 13px 15px; color: #0D7377; font-size: 12.5px; font-weight: 700; }
.dyn-head:hover { background: #EFF8F6; }
.dyn-body { padding: 12px 13px 14px; gap: 10px; }
.dyn-row {
  display: flex; flex-wrap: wrap;
  align-items: end; gap: 12px;
  padding: 12px; border: 1px solid #E2EFED; border-top: 2px solid #2EC4B6;
  border-radius: 10px; background: #FFFFFF;
}
.dyn-row .form-group { flex: 1 1 calc(50% - 26px); width: auto; min-width: 0; max-width: none; margin: 0; }
.dyn-row .form-group label { min-height: 30px; margin: 0 0 6px; color: #476263; }
.dyn-row input { width: 100%; height: 40px; text-align: left !important; }
.dyn-del { flex: 0 0 38px; width: 38px; height: 40px; }
.dyn-add { margin: 0 13px 13px; }

/* Верхняя панель и прогресс — нейтральные, акцентные цвета сохранены в действиях. */
.app-header { background: rgba(255,255,255,.97); border-bottom: 1px solid #DCEBE8; }
.brand-logo { background: #0D7377; box-shadow: none; }
.progress-card { background: #FFFFFF; border: 1px solid #DCEBE8; box-shadow: 0 2px 8px rgba(13,115,119,.04); }
.progress-track { background: #E6F0EE; }
.progress-fill { background: linear-gradient(90deg, #0D7377, #2EC4B6); }

/* Узкие экраны: последовательная одна колонка, без горизонтальной прокрутки. */
@media (max-width: 900px) {
  .container { padding-left: 18px; padding-right: 18px; }
  .card-body { padding: 15px; gap: 10px; }
  .card-body > .row { padding: 12px; gap: 9px 10px; }
}
@media (max-width: 680px) {
  .container { padding: 12px 11px 108px; }
  .card { margin-bottom: 11px; border-radius: 12px; }
  .card-header { min-height: 56px; padding: 11px 12px; gap: 8px; }
  .card-icon { width: 31px; height: 31px; flex-basis: 31px; }
  .card-title h3 { font-size: 12.5px; }
  .card-body { display: flex; flex-direction: column; align-items: stretch; padding: 11px; gap: 9px; }
  .card-body > .row,
  .card-body > .row:has(> .form-group:nth-child(3)),
  .card-body > .row:has(> .form-group:only-child),
  #card0 .card-body > .row {
    flex: 1 1 100%; width: 100%; max-width: 100%;
    flex-direction: column; gap: 9px; padding: 11px;
  }
  .card-body > .row .form-group, #card0 .card-body > .row .form-group { flex: 1 1 auto; width: 100%; }
  .card-body > .row label { min-height: 0; margin-bottom: 4px; }
  .card-body > .form-group { padding: 11px; }
  .dyn-row { display: flex; flex-direction: column; align-items: stretch; }
  .dyn-row .form-group { flex: 1 1 auto; width: 100%; }
  .dyn-del { align-self: flex-end; }
}
</style>


<style id="ui-layout-v137">
/* v1.3.7 — каждый показатель оформлен самостоятельной карточкой;
   родительские строки объединяют только логически связанные показатели. */

.container { max-width: 1180px; padding: 18px 26px 118px; }
body { background: #F3F8F7; }

/* Основной раздел — лёгкая большая карточка с ясной шапкой. */
.card {
  margin: 0 0 17px;
  border: 1px solid #D8E9E6;
  border-radius: 15px;
  background: #FFFFFF;
  box-shadow: 0 3px 12px rgba(13,72,73,.045);
  overflow: hidden;
}
.card:hover { transform: none; box-shadow: 0 4px 14px rgba(13,72,73,.07); }
.card::before { display: none !important; }
.card.open { border-color: #B9D8D4; box-shadow: 0 5px 18px rgba(13,72,73,.075); }
.card-header {
  min-height: 62px;
  padding: 13px 18px;
  background: #FFFFFF;
  border-bottom: 1px solid transparent;
  gap: 12px;
}
.card-header:hover { background: #FBFEFD; }
.card.open .card-header { background: #F0F8F7; border-bottom-color: #E0EFED; }
.card-title { min-width: 0; flex: 1 1 auto; gap: 11px; align-items: center; }
.card-icon {
  width: 35px; height: 35px; flex: 0 0 35px;
  border-radius: 10px; background: #E5F3F1; color: #0D7377;
  box-shadow: inset 0 0 0 1px rgba(13,115,119,.06);
  transform: none !important;
}
.card.open .card-icon { background: #0D7377; color: #FFFFFF; }
.card-title h3 { min-width: 0; color: #203D40; font-size: 14px; font-weight: 700; line-height: 1.38; }
.card-count {
  margin-left: auto; padding: 4px 9px; border-radius: 7px;
  color: #527274; background: #EDF5F4; border: 1px solid #DCEBE8;
  font-size: 11px; font-weight: 700; white-space: nowrap;
}
.card-count.started { color: #986313; background: #FFF7E8; border-color: #F2E4C6; }
.card-count.done { color: #167C69; background: #E9F7F2; border-color: #CFECE2; }
.chevron { width: 30px; height: 30px; border-radius: 9px; color: #477477; background: #EAF4F2; }
.card.open .chevron { color: #FFFFFF; background: #0D7377; }

/* Внутреннее поле раздела становится нейтральной подложкой. */
.card-body {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 13px;
  padding: 17px 18px 19px;
  background: #F8FBFA;
}

/* Строка объединяет показатели, но сама больше не выглядит отдельной карточкой. */
.card-body > .row {
  display: flex;
  flex: 1 1 100%;
  flex-wrap: nowrap;
  align-items: stretch;
  gap: 12px;
  width: 100%; max-width: 100%; min-width: 0;
  margin: 0;
  padding: 0;
  border: 0; border-radius: 0;
  background: transparent;
  box-shadow: none;
}
.card-body > .row:hover { background: transparent; border: 0; box-shadow: none; }
.card-body > .row:has(> .form-group:only-child) {
  flex: 0 1 calc(50% - 7px);
  width: calc(50% - 7px); max-width: calc(50% - 7px);
}
.card-body > .row .form-group {
  position: relative;
  display: flex;
  flex: 1 1 0;
  flex-direction: column;
  justify-content: flex-start;
  width: 0; max-width: none; min-width: 0;
  min-height: 102px;
  margin: 0;
  padding: 12px 14px 13px;
  border: 1px solid #DFEBE9;
  border-top: 3px solid #0D9996;
  border-radius: 11px;
  background: #FFFFFF;
  box-shadow: 0 1px 3px rgba(13,72,73,.025);
  box-sizing: border-box;
  transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
}
.card-body > .row:nth-of-type(2n) .form-group { border-top-color: #FF8A68; }
.card-body > .row:nth-of-type(3n) .form-group { border-top-color: #FFB627; }
.card-body > .row .form-group:focus-within {
  border-color: #9CCFC8;
  border-top-color: #0D7377;
  box-shadow: 0 0 0 3px rgba(13,153,150,.08);
}
.card-body > .row label {
  display: flex;
  align-items: flex-start;
  min-height: 34px;
  margin: 0 0 8px;
  color: #476263;
  font-size: 11.5px;
  font-weight: 650;
  line-height: 1.38;
}
.card-body > .row input[type="text"],
.card-body > .row input[type="number"],
.card-body > .row input[type="date"],
.card-body > .row input[type="month"], input[type="week"],
.card-body > .row select {
  display: block;
  width: 100%; max-width: 270px; min-width: 0;
  height: 40px; min-height: 40px;
  box-sizing: border-box;
  padding: 8px 11px;
  border: 1px solid #D6E7E4;
  border-radius: 8px;
  background: #FFFFFF;
  color: #182F31;
  text-align: left !important;
  box-shadow: none;
}
.card-body > .row input:focus,
.card-body > .row select:focus {
  border-color: #0D9996;
  box-shadow: 0 0 0 3px rgba(13,153,150,.10);
}
.card-body > .row input[type="number"] { direction: ltr; font-variant-numeric: tabular-nums; }
.card-body > .row .number-field-wrap {
  display: block !important;
  position: relative;
  flex: 0 0 auto;
  width: 100% !important; max-width: 270px !important; min-width: 0;
  height: 40px;
}
.card-body > .row .number-field-wrap input[type="number"] {
  display: block; width: 100% !important; max-width: 100% !important;
  height: 40px; min-height: 40px; box-sizing: border-box;
  text-align: left !important;
}
.card-body > .row .number-field-wrap .number-field-display {
  position: absolute; inset: 0;
  display: flex; justify-content: flex-start !important; align-items: center;
  width: 100%; max-width: 100%; height: 40px;
  box-sizing: border-box; padding: 0 11px !important;
  overflow: hidden; white-space: nowrap;
  border: 1px solid #D6E7E4; border-radius: 8px;
  background: #FFFFFF; color: #182F31;
  text-align: left !important; direction: ltr;
  font-variant-numeric: tabular-nums;
}

/* Обёртки селектов и числовых полей занимают всю доступную ширину карточки. */
.card-body > .row .select-wrap {
  display: flex; flex: 0 0 auto; width: 100%; max-width: 270px; min-width: 0;
}
.card-body > .row .select-wrap select { width: 100%; max-width: 100%; min-width: 0; }

/* Общая информация: три равных поля в одной строке. */
#card0 .card-body > .row { flex: 1 1 100%; width: 100%; max-width: 100%; }
#card0 .card-body > .row .form-group { flex: 1 1 0; min-height: 96px; }
#card0 .card-body > .row .form-group select,
#card0 .card-body > .row .form-group input[type="date"] { max-width: 270px; }

/* Тройные наборы — три одинаковых показателя, с единой высотой карточек. */
.card-body > .row:has(> .form-group:nth-child(3)) .form-group { min-height: 102px; }
.card-body > .row:has(> .form-group:nth-child(3)) input,
.card-body > .row:has(> .form-group:nth-child(3)) select,
.card-body > .row:has(> .form-group:nth-child(3)) .number-field-wrap { max-width: 100%; }

/* Поля комментариев — самостоятельные широкие блоки, без смешения с метриками. */
.card-body > .form-group {
  flex: 1 0 100%; width: 100%; max-width: 100%;
  margin: 0; padding: 13px 15px 14px;
  border: 1px solid #E7ECE8;
  border-left: 3px solid #FFB627;
  border-radius: 11px;
  background: #FFFEFA;
}
.card-body > .form-group label { margin: 0 0 7px; color: #426263; font-size: 12px; font-weight: 700; }
.card-body > .form-group textarea,
.card-body > .form-group textarea[data-field] {
  display: block; width: 100%; max-width: 100%; min-height: 78px;
  padding: 10px 12px; border: 1px solid #D6E7E4; border-radius: 8px;
  background: #FFFFFF; color: #182F31; text-align: left;
}

/* Динамические списки в том же визуальном языке. */
.dyn-spoiler {
  flex: 1 0 100%; width: 100%; margin: 0;
  border: 1px solid #DFEBE9; border-radius: 11px;
  background: #FFFFFF; box-shadow: none;
}
.dyn-head { padding: 12px 15px; color: #0D7377; font-size: 12.5px; font-weight: 700; }
.dyn-head:hover { background: #F0F8F7; }
.dyn-body { padding: 12px 13px 14px; gap: 10px; }
.dyn-row {
  display: flex; flex-wrap: wrap; align-items: end; gap: 12px;
  padding: 12px; border: 1px solid #DFEBE9; border-top: 3px solid #2EC4B6;
  border-radius: 10px; background: #FAFDFC;
}
.dyn-row .form-group { flex: 1 1 calc(50% - 26px); width: auto; min-width: 0; max-width: none; margin: 0; }
.dyn-row .form-group label { min-height: 30px; margin: 0 0 6px; color: #476263; }
.dyn-row input { width: 100%; max-width: 270px; height: 40px; text-align: left !important; }
.dyn-del { flex: 0 0 38px; width: 38px; height: 40px; }
.dyn-add { margin: 0 13px 13px; }

.app-header { background: rgba(255,255,255,.97); border-bottom: 1px solid #DCEBE8; }
.brand-logo { background: #0D7377; box-shadow: none; }
.progress-card { background: #FFFFFF; border: 1px solid #DCEBE8; box-shadow: 0 2px 8px rgba(13,115,119,.04); }
.progress-track { background: #E6F0EE; }
.progress-fill { background: linear-gradient(90deg, #0D7377, #2EC4B6); }

/* На стандартных ноутбуках боковая навигация сворачивается,
   чтобы не перекрывать левый край карточек. */
@media (max-width: 1500px) {
  .app-nav { display: none; max-height: 80vh; overflow: auto; }
  .app-nav.mobile-open { display: flex; }
  .nav-toggle-btn { display: flex; }
}

@media (max-width: 900px) {
  .container { padding-left: 17px; padding-right: 17px; }
  .card-body { padding: 14px; gap: 11px; }
  .card-body > .row { gap: 10px; }
  .card-body > .row .form-group { padding: 11px 12px 12px; }
}
@media (max-width: 680px) {
  .container { padding: 12px 11px 108px; }
  .card { margin-bottom: 11px; border-radius: 12px; }
  .card-header { min-height: 56px; padding: 11px 12px; gap: 8px; }
  .card-icon { width: 31px; height: 31px; flex-basis: 31px; }
  .card-title h3 { font-size: 12.5px; }
  .card-body { display: flex; flex-direction: column; align-items: stretch; padding: 10px; gap: 9px; }
  .card-body > .row,
  .card-body > .row:has(> .form-group:only-child),
  #card0 .card-body > .row {
    flex: 1 1 100%; width: 100%; max-width: 100%;
    flex-direction: column; gap: 8px;
  }
  .card-body > .row .form-group,
  #card0 .card-body > .row .form-group { flex: 1 1 auto; width: 100%; min-height: 0; }
  .card-body > .row label { min-height: 0; margin-bottom: 6px; }
  .card-body > .row input,
  .card-body > .row select,
  .card-body > .row .number-field-wrap { max-width: 100% !important; }
  .card-body > .form-group { padding: 11px; }
  .dyn-row { display: flex; flex-direction: column; align-items: stretch; }
  .dyn-row .form-group { flex: 1 1 auto; width: 100%; }
  .dyn-del { align-self: flex-end; }
}
</style>


<style id="ui-fix-v138">
/* v1.3.8 — три поля динамических проверок всегда стоят в одной строке. */
.dyn-row {
  display: grid !important;
  grid-template-columns: repeat(3, minmax(0, 1fr)) 38px;
  align-items: end;
  gap: 10px;
  flex-wrap: nowrap;
  width: 100%;
  box-sizing: border-box;
}
.dyn-row .form-group {
  display: flex;
  flex: none !important;
  flex-direction: column;
  width: auto !important;
  min-width: 0;
  max-width: none;
  margin: 0;
}
.dyn-row .form-group label {
  display: block;
  min-height: 32px;
  margin: 0 0 6px;
  font-size: 11.5px;
  line-height: 1.32;
  overflow-wrap: anywhere;
}
.dyn-row .form-group input,
.dyn-row .form-group input[type="text"],
.dyn-row .form-group input[type="date"],
.dyn-row .form-group input[type="number"] {
  display: block;
  width: 100% !important;
  max-width: none !important;
  min-width: 0;
  height: 40px;
  min-height: 40px;
  box-sizing: border-box;
  padding: 8px 9px;
  text-align: left !important;
}
.dyn-row .dyn-del {
  grid-column: 4;
  align-self: end;
  justify-self: end;
  flex: none;
  width: 38px;
  height: 40px;
  margin: 0;
}
@media (max-width: 680px) {
  /* На телефоне три поля также остаются в одной строке; колонки сжимаются симметрично. */
  .dyn-body { padding: 9px 8px 11px; }
  .dyn-row {
    grid-template-columns: repeat(3, minmax(0, 1fr)) 32px;
    gap: 6px;
    padding: 9px 8px;
  }
  .dyn-row .form-group label {
    min-height: 38px;
    margin-bottom: 5px;
    font-size: 10px;
    line-height: 1.25;
  }
  .dyn-row .form-group input,
  .dyn-row .form-group input[type="text"],
  .dyn-row .form-group input[type="date"],
  .dyn-row .form-group input[type="number"] {
    height: 37px;
    min-height: 37px;
    padding: 6px 5px;
    font-size: 11px;
  }
  .dyn-row .dyn-del { width: 32px; height: 37px; font-size: 13px; }
}
  .chop-checks { display: flex; flex-direction: column; gap: 8px; }
  .chop-post-row {
    display: grid;
    grid-template-columns: minmax(160px, 195px) minmax(0, 1fr);
    align-items: center;
    gap: 4px 14px;
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: rgba(255,255,255,0.55);
    transition: border-color 0.2s, background 0.2s;
  }
  .chop-post-row:hover { border-color: var(--border-hover); }
  .chop-post-row.unchecked { border-color: rgba(255,107,71,0.35); background: rgba(255,107,71,0.05); }
  .chop-checks .check-item { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; background: var(--panel-solid); font-size: 13px; font-weight: 500; transition: border-color 0.2s; }
  .chop-checks .check-item:hover { border-color: var(--border-hover); }
  .chop-checks .check-item input { width: 16px; height: 16px; accent-color: var(--coral); cursor: pointer; }
  .post-time {
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    font-size: 12px; color: var(--text-secondary); font-weight: 500;
    min-width: 0;
  }
  .post-time input[type="date"],
  .post-time input[type="time"] {
    border: 1px solid var(--border); border-radius: 8px;
    padding: 5px 8px; font-size: 12px;
    font-family: 'JetBrains Mono', monospace;
    background: var(--panel-solid); color: var(--text);
    width: 118px; box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .post-time input[type="time"] { width: 84px; }
  .post-time input[type="date"]:hover,
  .post-time input[type="time"]:hover { border-color: var(--border-hover); }
  .post-time input[type="date"]:focus,
  .post-time input[type="time"]:focus { outline: none; border-color: var(--petrol); box-shadow: 0 0 0 3px rgba(13,115,119,0.12); background: #fff; }
  .post-hint { display: block; grid-column: 2; margin: 0; font-size: 11.5px; color: var(--coral); font-weight: 500; }
  @media (max-width: 768px) {
    .chop-post-row { grid-template-columns: 1fr; }
    .post-hint { grid-column: 1; margin-top: 2px; }
  }

</style>


<style id="validation-and-draft-ui">
.live-validation{margin-top:12px;padding:9px 12px;border-radius:9px;font-size:12px;line-height:1.45;background:#f3f7f7;color:#526d70;border:1px solid #e1ecea}
.live-validation.has-issues{background:#fff8ed;border-color:#f1dfbd;color:#805b20}
.live-validation.is-ok{background:#eef8f3;border-color:#d6ebdf;color:#246b4d}
.live-validation summary{cursor:pointer;font-weight:700;outline:none}
.live-validation ul{margin:8px 0 6px 18px;padding:0}.live-validation li{margin:3px 0}
.validation-invalid{border-color:#d95b45!important;box-shadow:0 0 0 3px rgba(217,91,69,.10)!important;background:#fffafa!important}
@media(max-width:680px){.live-validation{font-size:11px;padding:8px 10px}}
</style>
<link rel="stylesheet" href="../site-theme.css">
<link rel="stylesheet" href="../site-header.css">
</head>
<body>
<div class="bg-blob"></div>
<div class="bg-blob"></div>
<div class="bg-blob"></div>
<div class="bg-blob"></div>

<header class="sb-topbar">
  <div class="sb-top-inner">
    <a class="sb-brand" href="../index.html">
      <span class="sb-logo">СБ</span>
      <span class="sb-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span>
    </a>
    <div class="sb-header-right">
      <div class="sb-user-chip" id="userChip">
        <span class="sb-user-name user-chip-name"></span>
        <span class="sb-user-meta user-chip-role"></span>
      </div>
      <a class="sb-header-btn" id="btnHeaderManage" href="../users.php" style="display:none">Управление</a>
      <a class="sb-header-btn" id="btnNotifications" href="../notifications.php" aria-label="Уведомления">Уведомления <span id="notificationBadge" hidden></span></a>
      <button class="sb-header-btn" id="btnLogout" type="button">Выход</button>
    </div>
  </div>
  <nav class="sb-nav" aria-label="Основная навигация">
    <div class="sb-nav-inner">
      <a href="../index.html">Главная</a>
      <a class="active" href="../reports.php">Отчёты</a>
      <a href="../tasks.php">Задачи</a>
      <a href="../employees.php">Сотрудники</a>
      <a href="../notifications.php">Уведомления</a>
    </div>
  </nav>
</header>


<div class="progress-wrap">
  <div class="progress-card">
    <div class="progress-header">
      <span><span class="progress-dot"></span>Прогресс заполнения отчета</span>
      <span class="progress-value" id="progressValue">0%</span>
    </div>
    <div class="progress-track"><div class="progress-fill" id="progressFill" style="width:0%"></div></div>
    <div class="live-validation" id="liveValidation" aria-live="polite"></div>
  </div>
</div>

<div class="container">

  <div class="card open" id="card0">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></div>
        <h3>Общая информация</h3>
        <span class="card-count" data-count="card0">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group">
          <label>Объект (ТО)</label>
          <div class="select-wrap">
            <select id="b0_to" data-field class="empty">
              <option value="">Выберите объект</option>
              <option value="Элиста 1 Ленина">Элиста 1 Ленина</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Составитель (ФИО)</label>
          <div class="select-wrap">
            <select id="b0_fio" data-field class="empty">
              <option value="">Загрузка сотрудников...</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Дата</label>
          <input type="date" id="b0_date" data-field>
        </div>
      </div>
    </div></div></div>
  </div>

  <div class="card" id="card1">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg></div>
        <h3>1. Проверка кассовых операций (удаление позиций из чека, удаление чека, возвраты, низкий вес товара)</h3>
        <span class="card-count" data-count="card1">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Отмена позиций в чеке — проверок, шт.</label><input type="number" id="b1_pos_checks" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>нарушений, шт.</label><input type="number" id="b1_pos_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b1_pos_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Анулирование чека в отсутствии администратора — проверок, шт.</label><input type="number" id="b1_chek_checks" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>нарушений, шт.</label><input type="number" id="b1_chek_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b1_chek_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Фиктивные возвраты — проверок, шт.</label><input type="number" id="b1_ret_checks" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>нарушений, шт.</label><input type="number" id="b1_ret_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b1_ret_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Продажа товара с заниженным весом — проверок, шт.</label><input type="number" id="b1_weight_checks" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>нарушений, шт.</label><input type="number" id="b1_weight_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b1_weight_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b1_comment" data-field placeholder="Пояснения по проверкам кассовых операций"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card2">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 10h.01M15 10h.01"/><path d="M9 15c1 1 2 1.5 3 1.5s2-.5 3-1.5"/></svg></div>
        <h3>2. Работа с FaceID, реагирование, пополнение базы данных</h3>
        <span class="card-count" data-count="card2">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Срабатываний, шт.</label><input type="number" id="b2_triggers" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>Отработано (реагирование), шт.</label><input type="number" id="b2_worked" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>Пополнено базы данных, чел.</label><input type="number" id="b2_db" data-field placeholder="чел." min="0"></div>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b2_comment" data-field placeholder="Пояснения по работе с FaceID"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card3">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7h12M8 12h12M8 17h12"/><path d="M4 7h.01M4 12h.01M4 17h.01"/></svg></div>
        <h3>3. Контроль за перемещениями ТМЦ (внутри объекта, между объектами)</h3>
        <span class="card-count" data-count="card3">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Внутри объекта — проверок, шт.</label><input type="number" id="b3_in_checks" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>нарушений, шт.</label><input type="number" id="b3_in_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b3_in_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Между объектами — проверок, шт.</label><input type="number" id="b3_between_checks" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>нарушений, шт.</label><input type="number" id="b3_between_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b3_between_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b3_comment" data-field placeholder="Пояснения по перемещениям ТМЦ"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card4">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 14l2 2 4-4"/></svg></div>
        <h3>4. Контрольные проверки проведённых инвентаризаций операционной службой</h3>
        <span class="card-count" data-count="card4">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row"><div class="form-group"><label>Проведено контрольных проверок, шт.</label><input type="number" id="b4_checks" data-field placeholder="шт." min="0"></div></div>
      <div class="row">
        <div class="form-group"><label>Выявленные расхождения — количество, шт.</label><input type="number" id="b4_dev_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b4_dev_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="dyn-spoiler" id="b4_inv_spoiler">
        <div class="dyn-head"><span class="dyn-arrow">▸</span>Инвентаризации</div>
        <div class="dyn-body" id="b4_inv_list"></div>
        <button type="button" class="dyn-add" data-add="b4_inv">+ Добавить инвентаризацию</button>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b4_comment" data-field placeholder="Пояснения по контрольным проверкам инвентаризаций"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card5">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg></div>
        <h3>5. Выгрузка виртуальных остатков, предоставление информации операционной службе для пересчёта</h3>
        <span class="card-count" data-count="card5">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Дата выгрузки</label><input type="date" id="b5_date" data-field></div>
        <div class="form-group"><label>Позиций, ед.</label><input type="number" id="b5_positions" data-field placeholder="ед." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b5_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Служебная записка на ДГМ — дата</label><input type="date" id="b5_sz_date" data-field></div>
        <div class="form-group">
          <label>Копия Директору Группы</label>
          <select id="b5_copy" data-field class="empty">
            <option value="">Выберите статус</option>
            <option value="направлена">направлена</option>
            <option value="не направлена">не направлена</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b5_comment" data-field placeholder="Пояснения по виртуальным остаткам"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card6">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
        <h3>6. Проверка качества поставки ТМЦ с РЦ (сырьё для производства, сыры, колбасы, мясо и т.д.)</h3>
        <span class="card-count" data-count="card6">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Проведено проверок, шт.</label><input type="number" id="b6_checks" data-field placeholder="шт." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Нарушения по качеству — количество, шт.</label><input type="number" id="b6_q_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b6_q_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Нарушения по количеству — количество, шт.</label><input type="number" id="b6_qty_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b6_qty_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Нарушения по срокам годности — количество, шт.</label><input type="number" id="b6_sg_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b6_sg_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="dyn-spoiler" id="b6_sup_spoiler">
        <div class="dyn-head"><span class="dyn-arrow">▸</span>Проверка поставки</div>
        <div class="dyn-body" id="b6_sup_list"></div>
        <button type="button" class="dyn-add" data-add="b6_sup">+ Добавить проверку</button>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b6_comment" data-field placeholder="Пояснения по поставкам с РЦ"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card7">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <h3>7. Проверка качества поставки ТМЦ от прямых поставщиков</h3>
        <span class="card-count" data-count="card7">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Проведено проверок, шт.</label><input type="number" id="b7_checks" data-field placeholder="шт." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Нарушения по качеству — количество, шт.</label><input type="number" id="b7_q_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b7_q_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Нарушения по количеству — количество, шт.</label><input type="number" id="b7_qty_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b7_qty_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Нарушения по срокам годности — количество, шт.</label><input type="number" id="b7_sg_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b7_sg_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="dyn-spoiler" id="b7_sup_spoiler">
        <div class="dyn-head"><span class="dyn-arrow">▸</span>Проверка поставки</div>
        <div class="dyn-body" id="b7_sup_list"></div>
        <button type="button" class="dyn-add" data-add="b7_sup">+ Добавить проверку</button>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b7_comment" data-field placeholder="Пояснения по поставкам от прямых поставщиков"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card8">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></div>
        <h3>8. Проверка качества списания ТМЦ</h3>
        <span class="card-count" data-count="card8">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Проведено проверок, шт.</label><input type="number" id="b8_checks" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>Выявленные нарушения — количество, шт.</label><input type="number" id="b8_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>на сумму, руб.</label><input type="number" id="b8_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="dyn-spoiler" id="b8_sp_spoiler">
        <div class="dyn-head"><span class="dyn-arrow">▸</span>Проверка списаний</div>
        <div class="dyn-body" id="b8_sp_list"></div>
        <button type="button" class="dyn-add" data-add="b8_sp">+ Добавить проверку</button>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b8_comment" data-field placeholder="Пояснения по проверкам списания ТМЦ"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card9">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8M16 17H8M10 9H8"/></svg></div>
        <h3>9. Количество служебных записок по выявленным нарушениям на ДГМ</h3>
        <span class="card-count" data-count="card9">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Количество служебных записок, шт.</label><input type="number" id="b9_count" data-field placeholder="шт." min="0"></div>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b9_comment" data-field placeholder="Пояснения по служебным запискам"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card10">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
        <h3>10. Хищения (уголовные, административные)</h3>
        <span class="card-count" data-count="card10">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="row">
        <div class="form-group"><label>Уголовные преступления — количество, шт.</label><input type="number" id="b10_crim_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>выявлены нарушения на сумму, руб.</label><input type="number" id="b10_crim_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Административные правонарушения — количество, шт.</label><input type="number" id="b10_adm_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>выявлены нарушения на сумму, руб.</label><input type="number" id="b10_adm_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Разбор на месте — количество, шт.</label><input type="number" id="b10_local_qty" data-field placeholder="шт." min="0"></div>
        <div class="form-group"><label>выявлены нарушения на сумму, руб.</label><input type="number" id="b10_local_sum" data-field placeholder="руб." min="0"></div>
      </div>
      <div class="form-group"><label>Описание значимых правонарушений</label><textarea id="b10_comment" data-field placeholder="Описание значимых правонарушений"></textarea></div>
    </div></div></div>
  </div>

  <div class="card" id="card11">
    <div class="card-header" data-toggle>
      <div class="card-title">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <h3>11. Работа ООО ЧОП «Артакс+»</h3>
        <span class="card-count" data-count="card11">–</span>
      </div>
      <div class="chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
    </div>
    <div class="card-body-wrap"><div class="card-body-inner"><div class="card-body">
      <div class="form-group">
        <label>Посты ЧОП (отметьте посты, где присутствовал сотрудник ЧОО)</label>
        <div class="chop-checks">
          <div class="chop-post-row">
            <label class="check-item"><input type="checkbox" id="b11_post_cash" data-field checked> Кассовая зона</label>
            <span class="post-time" id="wrap_b11_time_cash" style="display:none">с&nbsp;<input type="date" id="b11_dfrom_cash" data-field>&nbsp;<input type="time" id="b11_from_cash" data-field>&nbsp;до&nbsp;<input type="date" id="b11_dto_cash" data-field>&nbsp;<input type="time" id="b11_to_cash" data-field></span>
            <span class="post-hint" id="hint_b11_time_cash" style="display:none">введите дату и время отсутствия сотрудника ЧОО</span>
          </div>
          <div class="chop-post-row">
            <label class="check-item"><input type="checkbox" id="b11_post_kso" data-field checked> КСО</label>
            <span class="post-time" id="wrap_b11_time_kso" style="display:none">с&nbsp;<input type="date" id="b11_dfrom_kso" data-field>&nbsp;<input type="time" id="b11_from_kso" data-field>&nbsp;до&nbsp;<input type="date" id="b11_dto_kso" data-field>&nbsp;<input type="time" id="b11_to_kso" data-field></span>
            <span class="post-hint" id="hint_b11_time_kso" style="display:none">введите дату и время отсутствия сотрудника ЧОО</span>
          </div>
          <div class="chop-post-row">
            <label class="check-item"><input type="checkbox" id="b11_post_entr" data-field checked> Служебный вход</label>
            <span class="post-time" id="wrap_b11_time_entr" style="display:none">с&nbsp;<input type="date" id="b11_dfrom_entr" data-field>&nbsp;<input type="time" id="b11_from_entr" data-field>&nbsp;до&nbsp;<input type="date" id="b11_dto_entr" data-field>&nbsp;<input type="time" id="b11_to_entr" data-field></span>
            <span class="post-hint" id="hint_b11_time_entr" style="display:none">введите дату и время отсутствия сотрудника ЧОО</span>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="form-group"><label>Проверка УЧО у сотрудника ЧОО</label>
          <select id="b11_uch" data-field>
            <option value="">— не выбрано —</option>
            <option value="ok">УЧО в наличии</option>
            <option value="none">УЧО отсутствует</option>
          </select>
        </div>
        <div class="form-group" id="wrap_b11_uch_fio" style="display:none"><label>ФИО сотрудника ЧОО без УЧО</label><input type="text" id="b11_uch_fio" data-field placeholder="ФИО сотрудника"></div>
      </div>
      <div class="row">
        <div class="form-group"><label>Проведение инструктажа</label>
          <select id="b11_instr" data-field>
          <option value="">— не выбрано —</option>
          <option value="none">Инструктаж не проводился</option>
          <option value="t1">О запрете применения физической силы к покупателям, посетителям и персоналу</option>
          <option value="t2">Порядок действий сотрудника ЧОО при ЧП/ЧС</option>
          <option value="t3">Обеспечение внутриобъектового и пропускного режимов</option>
          <option value="t4">Правила осмотра личных вещей персонала и посетителей</option>
          <option value="t5">Правила обхода террритории ГМ</option>
          <option value="t6">Действия при обнаружении признаков правонарушения (взлом, проникновение)</option>
          <option value="t7">Оказание первой помощи пострадавшим</option>
            <option value="other">Другая тема инструктажа</option>
          </select>
        </div>
        <div class="form-group" id="wrap_b11_instr_topic" style="display:none"><label>Тема инструктажа</label><input type="text" id="b11_instr_topic" data-field placeholder="Укажите тему инструктажа"></div>
      </div>
      <div class="form-group"><label>Комментарий</label><textarea id="b11_comment" data-field placeholder="Пояснения по работе ЧОП «Артакс+»"></textarea></div>
    </div></div></div>
  </div>

  <section class="preview-section" id="previewSection">
    <div class="preview-header">
      <h2>Предпросмотр документа</h2>
      <p>Проверьте сохранённый документ перед отправкой</p>
    </div>
    <div class="page-wrap"><div class="page" id="pageContent"></div></div>
  </section>
</div>

<div class="sticky-bar">
  <div class="sticky-bar-inner">
    <div class="sticky-info">Заполнено <strong id="stickDone">0</strong> из <strong id="stickTotal">0</strong> полей</div>
    <div class="header-actions">
      <button class="btn btn-reset" id="btnReset" type="button">Сброс данных</button>
      <button class="btn btn-ghost" id="btnPreview2">Предпросмотр</button>
      <button class="btn btn-ghost" id="btnChangeFolder2" type="button">Изменить папку сохранения</button>
      <button class="btn btn-primary" id="btnDownload2">Скачать Word</button>
    </div>
  </div>
</div>

<footer class="app-footer">
  <div class="footer-admin-actions">
    <button class="btn btn-ghost footer-admin-btn" id="btnUsers" type="button" style="display:none">Управление пользователями</button>
    <button class="btn btn-ghost footer-admin-btn" id="btnAddUser" type="button" style="display:none">Добавить пользователя</button>
  </div>
  <button class="btn-test-footer" id="btnTest" type="button" title="Заполнить форму тестовыми данными">Тест-заполнение</button>
  <span>ver <span class="version">2.0.1</span></span>
</footer>

<div class="save-toast" id="saveToast">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
  Данные сохранены
</div>

<div class="summary-overlay" id="summaryOverlay">
  <div class="summary-card">
    <div class="summary-title">Сводка отчета</div>
    <div class="summary-subtitle">Проверьте данные перед скачиванием</div>
    <div class="dup-warn" id="sumDupWarn" style="display:none"></div>
    <div class="quality-warn" id="sumQualityWarn" style="display:none" role="status" aria-live="polite"></div>
    <table class="summary-table">
      <tr><td>Объект (ТО)</td><td id="sumTo">—</td></tr>
      <tr><td>Составитель</td><td id="sumFio">—</td></tr>
      <tr><td>Дата</td><td id="sumDate">—</td></tr>
      <tr><td>Заполнено полей</td><td id="sumFields">0 из 0</td></tr>
    </table>
    <div class="summary-actions">
      <button class="btn btn-outline" id="summaryCancel">Отмена</button>
      <button class="btn btn-primary" id="summaryConfirm">Скачать Word</button>
    </div>
  </div>
</div>

<div class="download-overlay" id="downloadOverlay">
  <div class="download-card">
    <div class="download-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg></div>
    <div class="download-title">Формирую и сохраняю отчет</div>
    <div class="download-progress-track"><div class="download-progress-fill" id="dlFill"></div></div>
    <div class="download-progress-text" id="dlText">0%</div>
  </div>
</div>

<div class="summary-overlay" id="listOverlay">
  <div class="summary-card archive-card">
    <div class="summary-title" id="listModalTitle">Редактирование списка</div>
    <div class="summary-subtitle">Добавляйте и удаляйте варианты — изменения сохранятся в этом браузере</div>
    <div class="list-edit-row">
      <input type="text" id="listNewValue" placeholder="Новый вариант">
      <button class="btn btn-primary" id="listAddBtn">Добавить</button>
    </div>
    <div class="archive-list" id="listItems"></div>
    <div class="summary-actions">
      <button class="btn btn-outline" id="listClose">Закрыть</button>
    </div>
  </div>
</div>

<nav class="app-nav" id="appNav" aria-label="Оглавление"></nav>
<button class="nav-toggle-btn" id="navToggle" type="button" title="Оглавление"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>

<button class="scroll-top-btn" id="scrollTopBtn">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>


<script src="../report-document.js"></script>
<script>
  /* Оптимизированная сборка 1.2.2: исправления архива/аналитики/восстановления; ускорение ввода, прокрутки и XLSX. */
(function() {
  'use strict';

  var KEY = 'spravka_control_v154';

  // Серверное хранилище: данные формы, динамические строки, списки и архив
  // находятся в MySQL через api.php. localStorage используется только для
  // однократного переноса старых данных и затем очищается.
  var serverStore = (function() {
    var cache = Object.create(null);
    var queue = Promise.resolve();
    var allowed = [KEY, KEY + '__dyn', KEY + '__lists', KEY + '__archive'];
    var initialized = false;

    async function request(action, body) {
      var options = { method: body ? 'POST' : 'GET', credentials: 'same-origin',
        cache: 'no-store', headers: { 'Accept': 'application/json' } };
      if (body) {
        options.headers['Content-Type'] = 'application/json';
        if (window.__SB_CSRF && !body.csrf) body = Object.assign({}, body, { csrf: window.__SB_CSRF });
        options.body = JSON.stringify(body);
      }
      var response = await fetch('../api.php?action=' + encodeURIComponent(action), options);
      var result;
      try { result = await response.json(); }
      catch (e) { throw new Error('Сервер вернул некорректный ответ. Проверьте api.php.'); }
      if (result && result.csrf) window.__SB_CSRF = result.csrf;
      if (response.status === 401 && result && result.need_login) {
        if (typeof showLoginOverlay === 'function') showLoginOverlay();
        throw new Error('Требуется вход. Обновите страницу.');
      }
      if (!response.ok || !result || result.ok !== true) {
        throw new Error((result && result.error) || ('Ошибка сервера: HTTP ' + response.status));
      }
      return result;
    }

    function parseStored(value) {
      try { return JSON.parse(value); } catch (e) { return value; }
    }

    async function writeRemote(key, value) {
      await request('save', { key: key, value: value });
      if (key === KEY + '__archive') {
        var latest = await request('load');
        cache[key] = (latest.data && latest.data[key]) || [];
      } else {
        cache[key] = value;
      }
    }

    async function init() {
      if (initialized) return;
      var status = await request('status');
      if (!status.authenticated) {
        // Полноценная авторизация: ждём ввода логина и пароля в форме входа.
        await sbWaitForLogin();
      }

      var loaded = await request('load');
      cache = loaded.data || Object.create(null);
      window.__SB_CSRF = loaded.csrf || window.__SB_CSRF;
      if (typeof applyRole === 'function') applyRole(loaded.user || null, loaded.objects || [], loaded.employees || []);

      // Однократный перенос старых данных из прежней версии сайта.
      // Серверные значения всегда имеют приоритет над локальными.
      for (var i = 0; i < allowed.length; i++) {
        var key = allowed[i];
        var existsOnServer = Object.prototype.hasOwnProperty.call(cache, key);
        var emptyServerArchive = key === KEY + '__archive' && Array.isArray(cache[key]) && cache[key].length === 0;
        if (existsOnServer && !emptyServerArchive) continue;
        var oldValue = null;
        try { oldValue = window.localStorage.getItem(key); } catch (e) {}
        if (oldValue !== null) await writeRemote(key, parseStored(oldValue));
      }
      // После успешной загрузки/переноса локальные копии удаляются.
      allowed.forEach(function(key) {
        try { window.localStorage.removeItem(key); } catch (e) {}
      });
      initialized = true;
    }

    function getItem(key) {
      if (!Object.prototype.hasOwnProperty.call(cache, key)) return null;
      return JSON.stringify(cache[key]);
    }

    function enqueue(task) {
      queue = queue.catch(function() {}).then(task);
      queue.catch(function(error) {
        console.error('Ошибка серверного сохранения:', error);
        if (typeof showServerError === 'function') showServerError(error);
      });
      return queue;
    }

    function setItem(key, value) {
      var parsed = parseStored(value);
      cache[key] = parsed;
      return enqueue(function() { return writeRemote(key, parsed); });
    }

    function removeItem(key) {
      delete cache[key];
      return enqueue(async function() {
        await request('delete', { key: key });
      });
    }

    function deleteArchiveRecord(record) {
      return enqueue(async function() {
        await request('archive_delete', {
          date: record && record.data ? (record.data.b0_date || '') : '',
          object: record && record.data ? (record.data.b0_to || '') : '',
          t: record ? (record.t || 0) : 0,
          fio: record ? (record.fio || '') : '',
          owner_user_id: record ? (record.owner_user_id || 0) : 0
        });
        var latest = await request('load');
        cache[KEY + '__archive'] = (latest.data && latest.data[KEY + '__archive']) || [];
      });
    }

    return {
      init: init,
      getItem: getItem,
      setItem: setItem,
      removeItem: removeItem,
      deleteArchiveRecord: deleteArchiveRecord,
      flush: function() { return queue; }
    };
  })();

  // ================= ПРОВЕРКА ФАЙЛОВ И СИНХРОНИЗАЦИЯ АРХИВА (v1.84) =================
  // Имена файлов восстанавливаются по тем же правилам, какими они создавались при сохранении.

function safeFname(x) { return String(x || '').replace(/[\\\\/:*?"<>|]/g, '_').replace(/\\s+/g, '_').trim() || 'Не указано'; }

  function wordFnameFor(data) {
    return 'Отчет_контроль_' + safeFname(data.b0_to || 'объект') +
      (data.b0_fio ? '_' + safeFname(data.b0_fio) : '') +
      (data.b0_date ? '_' + fdate(data.b0_date) : '') + '.doc';
  }

  function jsonNameFor(data) {
    return 'архив_' + (data.b0_date || 'без-даты') + '_' + safeName(data.b0_to || 'объект') + '_' + safeName(data.b0_fio || 'сотрудник') + '.json';
  }

  function fioDirName(data) {
    return String(data.b0_fio || '').replace(/_/g, ' ') || 'Сотрудник не указан';
  }

  function monthDirName(data) {
    return data.b0_date ? data.b0_date.slice(0, 7) : new Date().toISOString().slice(0, 7);
  }

  async function fileExists(handle) {
    try { await handle.getFile(); return true; }
    catch (e) { return false; }
  }

  async function checkRecordFiles(root, it) {
    var d = (it && it.data) || {};
    var res = { word: false, json: false };
    try {
      var eh = await root.getDirectoryHandle(fioDirName(d));
      var mh = await eh.getDirectoryHandle(monthDirName(d));
      res.word = await fileExists(await mh.getFileHandle(wordFnameFor(d)));
    } catch (e) { res.word = false; }
    try {
      var ad = await root.getDirectoryHandle('Архив_СБ');
      res.json = await fileExists(await ad.getFileHandle(jsonNameFor(d)));
    } catch (e) { res.json = false; }
    return res;
  }

  async function deleteRecordFiles(root, it) {
    var d = (it && it.data) || {};
    var gone = [];
    try {
      var ad = await root.getDirectoryHandle('Архив_СБ');
      await ad.removeEntry(jsonNameFor(d));
      gone.push('JSON-запись');
    } catch (e) {}
    try {
      var eh = await root.getDirectoryHandle(fioDirName(d));
      var mh = await eh.getDirectoryHandle(monthDirName(d));
      await mh.removeEntry(wordFnameFor(d));
      gone.push('Word-отчет');
    } catch (e) {}
    try {
      var eh2 = await root.getDirectoryHandle(fioDirName(d));
      var mh2 = await eh2.getDirectoryHandle(monthDirName(d));
      await mh2.removeEntry(xNameFor(d));
      gone.push('Excel-отчет');
    } catch (e) {}
    return gone;
  }




  function $(s) { return document.querySelector(s); }
  function $$(s) { return Array.prototype.slice.call(document.querySelectorAll(s)); }

  function esc(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function v(id) {
    var el = document.getElementById(id);
    return el ? String(el.value).trim() : '';
  }

  function has(x) {
    if (x === '') return false;
    var n = parseFloat(x);
    return !(isNaN(n)) ? n !== 0 : false;
  }

  function num(s) {
    var n = parseFloat(s);
    if (isNaN(n)) return s;
    return n.toLocaleString('ru-RU');
  }

  function num2(s) {
    var x = parseFloat(String(s || '').replace(',', '.'));
    return isNaN(x) ? 0 : x;
  }

  function fdate(s) {
    if (!s) return '';
    var p = s.split('-');
    if (p.length === 3) return p[2] + '.' + p[1] + '.' + p[0];
    return s;
  }

  function ph(x) {
    return x ? esc(x) : '<span class="ph">[не заполнено]</span>';
  }

  // Логика парных строк (количество + сумма):
  // qty+sum -> "N шт. — выявлены нарушения на сумму S руб."
  // qty only -> "N шт. — нарушений не выявлено"
  // sum only -> "выявлены нарушения на сумму S руб."
  // none -> строка не выводится
  function pair(label, qtyId, sumId, unit, neutral) {
    var q = v(qtyId), s = v(sumId);
    var hq = has(q), hs = has(s);
    if (!hq && !hs) return null;
    if (neutral) {
      if (hq && hs) return label + ': ' + q + ' ' + unit + ' на сумму ' + num(s) + ' руб.';
      if (hq) return label + ': ' + q + ' ' + unit;
      return label + ': на сумму ' + num(s) + ' руб.';
    }
    if (hq && hs) return label + ': ' + q + ' ' + unit + ' — выявлены нарушения на сумму ' + num(s) + ' руб.';
    if (hq) return label + ': ' + q + ' ' + unit + ' — нарушений не выявлено';
    return label + ': выявлены нарушения на сумму ' + num(s) + ' руб.';
  }

  function single(label, id, unit) {
    if (!has(v(id))) return null;
    return label + ': ' + v(id) + ' ' + unit;
  }

  function dateline(label, value) {
    if (!value) return null;
    return label + ': ' + value;
  }

  function dynline(prefix, key, devNote) {
    var rows = dynData[key] || [];
    var out = [];
    for (var i = 0; i < rows.length; i++) {
      var r = rows[i], parts = [];
      if (has(r.num)) parts.push('№ ' + r.num);
      if (has(r.date)) parts.push('от ' + fdate(r.date));
      if (has(r.sum)) parts.push('на сумму ' + num(r.sum) + ' руб.');
      if (has(r.cnt)) parts.push('проверено позиций: ' + r.cnt);
      if (has(r.dev)) parts.push('выявлено расхождений, руб.: ' + r.dev);
      else if (devNote && parts.length) parts.push('нарушений не выявлено');
      if (!parts.length) continue;
      var ln = prefix + ': ' + parts.join(', ');
      if (!/\.\s*$/.test(ln)) ln += '.';
      out.push(ln);
    }
    return out;
  }

  // Формирование краткого текста по результатам проверок и выявленным нарушениям.
  // Проверок нет, но нарушения есть -> строка «выявлено нарушений…»; проверок нет и нарушений нет -> строка не выводится.
  function checkPair(label, chkId, qtyId, sumId, vword) {
    vword = vword || 'нарушений';
    var c = num2(v(chkId)), q = num2(v(qtyId)), s = num2(v(sumId));
    if (!c && !q && !s) return null;
    if (!c) {
      if (q || s) return label + ': выявлено ' + vword + ': ' + num(q) + ' шт.' + (s ? ', на сумму ' + num(s) + ' руб.' : '') + '.';
      return null;
    }
    var base = label + ': проведено проверок: ' + num(c) + ' шт.';
    if (!q && !s) return base + ' ' + vword.charAt(0).toUpperCase() + vword.slice(1) + ' не выявлено.';
    return base + ' Выявлено ' + vword + ': ' + num(q) + ' шт.' + (s ? ', на сумму ' + num(s) + ' руб.' : '') + '.';
  }
  function checkMulti(chkId, pairs) {
    var c = num2(v(chkId));
    var totQ = 0, totS = 0, parts = [], any = false;
    for (var i = 0; i < pairs.length; i++) {
      var q = num2(v(pairs[i][1])), s = num2(v(pairs[i][2]));
      if (q || s) {
        any = true;
        totQ += q; totS += s;
        parts.push(pairs[i][0] + ' — ' + num(q) + ' шт.' + (s ? ', ' + num(s) + ' руб.' : ''));
      }
    }
    if (!c && !any) return null;
    if (!c) {
      var o0 = 'Выявлено нарушений: ' + num(totQ) + ' шт.' + (totS ? ', на сумму ' + num(totS) + ' руб.' : '');
      if (parts.length > 1) o0 += ' В том числе: ' + parts.join('; ');
      return o0 + '.';
    }
    if (!any) return 'Проведено проверок: ' + num(c) + ' шт. Нарушений не выявлено.';
    var o = 'Проведено проверок: ' + num(c) + ' шт. Выявлено нарушений: ' + num(totQ) + ' шт.' + (totS ? ', на сумму ' + num(totS) + ' руб.' : '');
    if (parts.length > 1) o += ' В том числе: ' + parts.join('; ');
    return o + '.';
  }

  // Автоматическая проверка логической согласованности показателей.
  // Все найденные несоответствия являются предупреждениями: пользователь может продолжить экспорт.
  function validateReport() {
    var issues = [];
    function val(id) { return num2(v(id)); }
    function add(msg) { issues.push(msg); }
    var numeric = $$('input[type="number"][data-field]');
    numeric.forEach(function(el) {
      if (el.value.trim() === '') return;
      var n = Number(String(el.value).replace(',', '.'));
      if (!isFinite(n)) add('Поле «' + ((el.closest('.form-group') || el.parentElement).querySelector('label') || {}).textContent?.trim() + '» содержит некорректное число.');
      else if (n < 0) add('Отрицательное значение в поле «' + (((el.closest('.form-group') || el.parentElement).querySelector('label') || {}).textContent || el.id).trim() + '».');
      var label = ((el.closest('.form-group') || el.parentElement).querySelector('label') || {}).textContent || '';
      if (/(количество|проверок|срабатываний|позиций|записок|пополнено|шт\.|чел\.|поз\.|ед\.)/i.test(label) && n % 1 !== 0)
        add('В поле «' + label.trim() + '» указано дробное значение, хотя ожидается целое количество.');
    });
    function pair(qid, sid, title) {
      var q = val(qid), amount = val(sid);
      if (amount > 0 && q === 0) add(title + ': указана сумма, но количество нарушений равно нулю.');
    }
    pair('b1_pos_qty','b1_pos_sum','Отмена позиций в чеке');
    pair('b1_chek_qty','b1_chek_sum','Анулирование чека в отсутствии администратора');
    pair('b1_ret_qty','b1_ret_sum','Фиктивные возвраты');
    pair('b1_weight_qty','b1_weight_sum','Продажа товара с заниженным весом');
    pair('b3_in_qty','b3_in_sum','Перемещения внутри объекта');
    pair('b3_between_qty','b3_between_sum','Перемещения между объектами');
    pair('b4_dev_qty','b4_dev_sum','Контрольные проверки инвентаризаций');
    pair('b8_qty','b8_sum','Проверки списания ТМЦ');
    pair('b10_crim_qty','b10_crim_sum','Уголовные преступления');
    pair('b10_adm_qty','b10_adm_sum','Административные правонарушения');
    pair('b10_local_qty','b10_local_sum','Разбор на месте');
    function checkRelation(checkId, qtyIds, title) {
      var checks = val(checkId), qty = 0;
      qtyIds.forEach(function(id) { qty += val(id); });
      if (qty > 0 && checks === 0) add(title + ': выявлены нарушения, но количество проверок равно нулю.');
      else if (checks > 0 && qty > checks) add(title + ': количество нарушений превышает количество проверок. Проверьте данные (в одной проверке может быть несколько нарушений).');
    }
    checkRelation('b3_in_checks',['b3_in_qty'],'Перемещения внутри объекта');
    checkRelation('b3_between_checks',['b3_between_qty'],'Перемещения между объектами');
    checkRelation('b4_checks',['b4_dev_qty'],'Контрольные проверки инвентаризаций');
    checkRelation('b6_checks',['b6_q_qty','b6_qty_qty','b6_sg_qty'],'Поставки с РЦ');
    checkRelation('b7_checks',['b7_q_qty','b7_qty_qty','b7_sg_qty'],'Поставки от прямых поставщиков');
    checkRelation('b8_checks',['b8_qty'],'Проверки списания ТМЦ');
    // Проверка динамических строк поставок и списаний.
    [['b6_sup','Поставки с РЦ'],['b7_sup','Прямые поставщики'],['b8_sp','Списания ТМЦ']].forEach(function(spec) {
      (dynData[spec[0]] || []).forEach(function(row, i) {
        var cnt = num2(row.cnt), dev = num2(row.dev);
        if (dev > 0 && cnt === 0) add(spec[1] + ', строка ' + (i+1) + ': указана сумма расхождений, но не указано количество проверенных позиций.');
        if (cnt < 0 || dev < 0) add(spec[1] + ', строка ' + (i+1) + ': обнаружено отрицательное значение.');
      });
    });
    var today = new Date(); today.setHours(0,0,0,0);
    if (v('b0_date')) {
      var reportDate = new Date(v('b0_date') + 'T00:00:00');
      if (!isNaN(reportDate.getTime()) && reportDate > today) add('Дата отчёта находится в будущем. Проверьте дату.');
    }
    if (!v('b0_to')) add('Не выбран объект (ТО).');
    if (!v('b0_fio')) add('Не указан составитель отчёта.');
    if (!v('b0_date')) add('Не указана дата отчёта.');
    B11_POSTS.forEach(function(p) {
      var cb = $('#b11_post_' + p[0]);
      if (cb && !cb.checked && !(v('b11_from_' + p[0]) || v('b11_to_' + p[0]))) add('Раздел 11: на посту «' + p[1] + '» не указаны дата и время отсутствия сотрудника ЧОО.');
    });
    if (v('b11_uch') === 'none' && !v('b11_uch_fio')) add('Раздел 11: выбрано «УЧО отсутствует», но не указано ФИО сотрудника ЧОО.');
    if (v('b11_instr') === 'other' && !v('b11_instr_topic')) add('Раздел 11: выбрана «Другая тема инструктажа», но тема не заполнена.');
    return issues;
  }

  var validationTouched = false;
  function updateValidationPanel() {
    var box = $('#liveValidation');
    if (!box || !validationTouched) return;
    var issues = validateReport();
    var required = ['b0_to','b0_fio','b0_date'];
    required.forEach(function(id) { var el = document.getElementById(id); if (el) el.classList.toggle('validation-invalid', !String(el.value || '').trim()); });
    $('input[type="number"][data-field]').forEach(function(el) {
      var raw = String(el.value || '').trim(), n = Number(raw.replace(',', '.'));
      var label = ((el.closest('.form-group') || el.parentElement).querySelector('label') || {}).textContent || '';
      var invalid = raw !== '' && (!isFinite(n) || n < 0 || (/(количество|проверок|срабатываний|позиций|записок|пополнено|шт\\.|чел\\.|поз\\.|ед\\.)/i.test(label) && n % 1 !== 0));
      el.classList.toggle('validation-invalid', invalid);
    });
    if (issues.length) {
      box.className = 'live-validation has-issues';
      box.innerHTML = '<details><summary>Проверка данных: замечаний — ' + issues.length + '</summary><ul>' + issues.map(function(issue) { return '<li>' + esc(issue) + '</li>'; }).join('') + '</ul><span>Проверьте значения перед сохранением или скачиванием отчёта.</span></details>';
    } else {
      box.className = 'live-validation is-ok';
      box.textContent = 'Проверка данных пройдена — замечаний нет.';
    }
  }

  // ---------- Блок 11: работа ООО ЧОП «Артакс+» ----------
  var B11_POSTS = [['cash', 'Кассовая зона'], ['kso', 'КСО'], ['entr', 'Служебный вход']];
  var INSTR_TOPICS = {
    t1: 'О запрете применения физической силы к покупателям, посетителям и персоналу',
    t2: 'Порядок действий сотрудника ЧОО при ЧП/ЧС',
    t3: 'Обеспечение внутриобъектового и пропускного режимов',
    t4: 'Правила осмотра личных вещей персонала и посетителей',
    t5: 'Правила обхода террритории ГМ',
    t6: 'Действия при обнаружении признаков правонарушения (взлом, проникновение)',
    t7: 'Оказание первой помощи пострадавшим',
    t8: 'Другая тема инструктажа'
  };
  // Семантика галочек: value '' — пост отмечен (норма), value '1' — пост снят (нарушение).
  function b11Sync() {
    B11_POSTS.forEach(function(p) {
      var cb = $('#b11_post_' + p[0]);
      if (!cb) return;
      cb.checked = cb.value !== '1';
      cb.value = cb.checked ? '' : '1';
      var w = $('#wrap_b11_time_' + p[0]);
      if (w) {
        w.style.display = cb.checked ? 'none' : '';
        var rowEl = w.closest('.chop-post-row');
        if (rowEl) rowEl.classList.toggle('unchecked', !cb.checked);
      }
      if (!cb.checked) {
        var rd = v('b0_date');
        if (rd) {
          var dF = $('#b11_dfrom_' + p[0]), dT = $('#b11_dto_' + p[0]);
          if (dF && !dF.value) dF.value = rd;
          if (dT && !dT.value) dT.value = rd;
        }
      }
      var hh = $('#hint_b11_time_' + p[0]);
      if (hh) hh.style.display = cb.checked ? 'none' : '';
    });
    var wU = $('#wrap_b11_uch_fio');
    if (wU) wU.style.display = v('b11_uch') === 'none' ? '' : 'none';
    var wT = $('#wrap_b11_instr_topic');
    if (wT) wT.style.display = v('b11_instr') === 'other' ? '' : 'none';
  }
  function chopText() {
    var lines = [], missing = [];
    B11_POSTS.forEach(function(p) {
      var cb = $('#b11_post_' + p[0]);
      if (cb && !cb.checked) {
        var from = v('b11_from_' + p[0]), to = v('b11_to_' + p[0]);
        var t = '';
        if (from || to) {
          var rd = v('b0_date');
          var dF = v('b11_dfrom_' + p[0]) || rd;
          var dT = v('b11_dto_' + p[0]) || dF;
          t = ' (с ' + (fdate(dF) || '--.--.----') + ' г. ' + (from || '--:--') + ' до ' + (fdate(dT) || '--.--.----') + ' г. ' + (to || '--:--') + ')';
        }
        missing.push('На посту «' + p[1] + '» отсутствовал сотрудник ЧОО' + t + '.');
      }
    });
    if (missing.length) missing.forEach(function(m) { lines.push(m); lines.push('Составлен соответствующий акт о выявленном нарушении.'); });
    else lines.push('Посты ЧОП: нарушений нет — все посты укомплектованы сотрудниками ЧОО.');
    var uch = v('b11_uch');
    if (uch === 'ok') lines.push('Проверка УЧО у сотрудника ЧОО: нарушений не выявлено.');
    else if (uch === 'none') { lines.push('Проверка УЧО: УЧО отсутствует' + (v('b11_uch_fio') ? ' у сотрудника ЧОО ' + v('b11_uch_fio') : '') + '.'); lines.push('Составлен соответствующий акт о выявленном нарушении.'); }
    var instr = v('b11_instr');
    if (instr === 'none') lines.push('Инструктаж сотрудников ЧОП: не проводился.');
    else if (instr === 'other') { if (v('b11_instr_topic')) lines.push('Проведён инструктаж по теме: «' + v('b11_instr_topic') + '».'); }
    else if (instr) lines.push('Проведён инструктаж по теме: «' + INSTR_TOPICS[instr] + '».');
    return lines;
  }
  function chopDayText(d) {
    if (!d || d.b11_uch === undefined) return '';
    var parts = [], unchecked = [];
    B11_POSTS.forEach(function(p) { if (d['b11_post_' + p[0]] === '1') unchecked.push(p); });
    if (unchecked.length === B11_POSTS.length) {
      // Совместимость с записями v1.3.15: там '1' означало «пост отмечен».
      var anyTime = B11_POSTS.some(function(p) { return d['b11_from_' + p[0]] || d['b11_to_' + p[0]]; });
      if (!anyTime) { parts.push('Посты ЧОП: нарушений нет'); unchecked = []; }
    }
    unchecked.forEach(function(p) {
      var from = d['b11_from_' + p[0]], to = d['b11_to_' + p[0]];
      var t = '';
      if (from || to) {
        var rd = d.b0_date || d.date || '';
        var dF = d['b11_dfrom_' + p[0]] || rd;
        var dT = d['b11_dto_' + p[0]] || dF;
        t = ' (с ' + (fdate(dF) || '--.--.----') + ' г. ' + (from || '--:--') + ' до ' + (fdate(dT) || '--.--.----') + ' г. ' + (to || '--:--') + ')';
      }
      parts.push('На посту «' + p[1] + '» отсутствовал сотрудник ЧОО' + t);
      parts.push('Составлен соответствующий акт о выявленном нарушении');
    });
    if (!unchecked.length && !parts.length) parts.push('Посты ЧОП: нарушений нет');
    if (d.b11_uch === 'ok') parts.push('УЧО: нарушений не выявлено');
    else if (d.b11_uch === 'none') { parts.push('УЧО отсутствует' + (d.b11_uch_fio ? ' (' + d.b11_uch_fio + ')' : '')); parts.push('Составлен соответствующий акт о выявленном нарушении'); }
    if (d.b11_instr === 'none') parts.push('инструктаж не проводился');
    else if (d.b11_instr === 'other') { if (d.b11_instr_topic) parts.push('инструктаж: «' + d.b11_instr_topic + '»'); }
    else if (d.b11_instr) parts.push('инструктаж: «' + (INSTR_TOPICS[d.b11_instr] || d.b11_instr) + '»');
    return parts.join('. ');
  }

  function buildDoc() {
    var data = {};
    $$('[data-field]').forEach(function(el) { data[el.id] = el.value; });
    return window.SBDocument.build(data, dynData);
  }

    function docCss() {
    return '' +
      '@page{size:A4;margin:1.5cm 1.6cm 1.5cm 1.5cm} ' +
      'html,body{margin:0;padding:0;background:#fff} ' +
      'body{font-family:Arial,Helvetica,sans-serif;color:#000;background:#fff} ' +
      '.doc-wrap{font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.38;color:#000;width:100%;mso-line-height-rule:at-least} ' +
      '.doc-wrap p{orphans:3;widows:3;color:#000} ' +
      '.doc-brand{margin:0 0 17pt 0;padding:0 0 6pt 0;border-bottom:.55pt solid #777;font-size:8.5pt;line-height:1.2;font-weight:bold;letter-spacing:.3pt;color:#000;text-transform:uppercase} ' +
      '.doc-meta{margin:0 0 17pt 0;padding:0} ' +
      '.doc-meta p{margin:4pt 0;font-size:9.7pt;line-height:1.3;color:#000} ' +
      '.doc-meta strong{font-weight:bold;color:#000} ' +
      '.doc-section{margin:0 0 11pt 0;padding:0;page-break-inside:auto} ' +
      '.sec-title{margin:18pt 0 8pt 0;padding:0 0 5pt 0;font-size:11pt;line-height:1.22;font-weight:bold;color:#000;background:#fff;border:0;border-bottom:.65pt solid #888;text-indent:0;text-align:left;page-break-after:avoid;mso-pagination:keep-with-next} ' +
      '.doc-line{margin:0 0 6pt 11pt;padding:0;text-indent:0;font-size:10pt;line-height:1.38;color:#000;page-break-inside:avoid} ' +
      '.doc-line-label{font-weight:bold;color:#000} ' +
      '.doc-line-sub{margin:0 0 5pt 22pt;padding:0;font-size:9.7pt;line-height:1.34;color:#000;page-break-inside:avoid} ' +
      '.doc-note{margin:8pt 0 10pt 11pt;padding:0 0 0 7pt;border-left:.6pt solid #888;font-size:9.5pt;line-height:1.38;color:#000;text-indent:0;text-align:left} ' +
      '.doc-note strong{font-weight:bold;color:#000} ' +
      '.doc-totals{margin:19pt 0 0 0;padding:9pt 0 0 0;border-top:.9pt solid #555;page-break-inside:avoid} ' +
      '.doc-totals-title{margin:0 0 8pt 0;font-size:11pt;line-height:1.2;font-weight:bold;color:#000} ' +
      '.doc-total-line{margin:4pt 0 4pt 11pt;font-size:10pt;line-height:1.35;color:#000} ' +
      '.doc-total-line strong{font-weight:bold;color:#000} ' +
      '.doc-sign{margin:23pt 0 0 0;padding:9pt 0 0 0;border-top:.6pt solid #777;font-size:9.7pt;line-height:1.45;color:#000;page-break-inside:avoid} ' +
      '.doc-sign p{margin:5pt 0;color:#000} ' +
      '.doc-ver{margin:15pt 0 0 0;padding-top:4pt;border-top:.4pt solid #999;font-size:7.5pt;color:#000;text-align:right;font-family:Arial,Helvetica,sans-serif}';
  }
  function docMetaTable(rows) {
    var h = '<div class="doc-meta">';
    for (var i = 0; i < rows.length; i++) {
      h += '<p style="margin:4pt 0;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:9.7pt;line-height:1.3;color:#000000;"><strong style="font-weight:bold;color:#000000;">' + rows[i][0] + ':</strong> ' + rows[i][1] + '</p>';
    }
    return h + '</div>';
  }
  function docMasthead(tag) {
    return '<p class="doc-brand" style="margin:0 0 15pt 0;padding:0 0 5pt 0;border-bottom:.6pt solid #777777;font-family:Arial,Helvetica,sans-serif;font-size:8.5pt;line-height:1.2;font-weight:bold;letter-spacing:.35pt;color:#000000;text-transform:uppercase;">Группа БФ Волгоград 1&nbsp;&nbsp; / &nbsp;&nbsp;' + (tag || 'СЛУЖЕБНЫЙ ДОКУМЕНТ') + '</p>';
  }
  function docLineHtml(line) {
    var raw = String(line || '');
    var chunks = raw.split(/\s+(?=Выявлено(?: нарушений| расхождений)?\s*:|В том числе\s*:)/);
    var h = '';
    for (var c = 0; c < chunks.length; c++) {
      var part = chunks[c].trim();
      if (!part) continue;
      var colon = part.indexOf(':');
      if (colon > 0 && colon < 90 && part.slice(colon + 1, colon + 2) === ' ') {
        var label = part.slice(0, colon + 1);
        var value = part.slice(colon + 1).trim();
        h += '<p class="doc-line" style="margin:0 0 6pt 11pt;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.38;color:#000000;page-break-inside:avoid;"><strong style="font-weight:bold;color:#000000;">' + esc(label) + '</strong>' + (value ? ' ' + esc(value) : '') + '</p>';
      } else {
        h += '<p class="doc-line-sub" style="margin:0 0 5pt 22pt;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:9.7pt;line-height:1.34;color:#000000;page-break-inside:avoid;">' + esc(part) + '</p>';
      }
    }
    return h;
  }
  function docSection(title, lines, notes) {
    var h = '<div class="doc-section" style="margin:0 0 11pt 0;padding:0;">';
    h += '<p class="sec-title" style="margin:18pt 0 8pt 0;padding:0 0 5pt 0;border-bottom:.65pt solid #888888;font-family:Arial,Helvetica,sans-serif;font-size:11pt;line-height:1.22;font-weight:bold;color:#000000;text-align:left;text-indent:0;page-break-after:avoid;">' + esc(title) + '</p>';
    if (lines && lines.length) {
      for (var i = 0; i < lines.length; i++) {
        if (lines[i]) h += docLineHtml(lines[i]);
      }
    }
    if (notes && notes.length) for (var j = 0; j < notes.length; j++) {
      h += '<p class="doc-note" style="margin:8pt 0 10pt 11pt;padding:0 0 0 7pt;border-left:.6pt solid #888888;font-family:Arial,Helvetica,sans-serif;font-size:9.5pt;line-height:1.38;color:#000000;"><strong style="font-weight:bold;color:#000000;">Комментарий:</strong> ' + esc(notes[j]) + '</p>';
    }
    return h + '</div>';
  }
  function ensureDocCss() {
    if (document.getElementById('docCssInjected')) return;
    var el = document.createElement('style');
    el.id = 'docCssInjected';
    el.textContent = docCss();
    document.head.appendChild(el);
  }


  function sumDetailSpec() {
    return [
      { t: '1. Кассовые операции', items: [
        // M: количество в поле «количество» — проверено; сумма > 0 — одно выявленное нарушение по строке.
        ['Отмена позиций в чеке', 'M', ['f','b1_pos_qty'], ['f','b1_pos_sum']],
        ['Анулирование чека в отсутствии администратора', 'M', ['f','b1_chek_qty'], ['f','b1_chek_sum']],
        ['Фиктивные возвраты', 'M', ['f','b1_ret_qty'], ['f','b1_ret_sum']],
        ['Продажа товара с заниженным весом', 'M', ['f','b1_weight_qty'], ['f','b1_weight_sum']]
      ]},
      { t: '2. Работа с FaceID, реагирование, пополнение базы данных', items: [
        ['Срабатываний', 'S', ['f','b2_triggers'], null],
        ['Отработано (реагирование)', 'S', ['f','b2_worked'], null],
        ['Пополнено базы данных', 'S', ['f','b2_db'], null]
      ]},
      { t: '3. Перемещения ТМЦ (внутри объекта, между объектами)', items: [
        ['Проверок перемещений внутри объекта', 'P', ['f','b3_in_checks'], null],
        ['Нарушения внутри объекта', 'V', ['f','b3_in_qty'], ['f','b3_in_sum']],
        ['Проверок перемещений между объектами', 'P', ['f','b3_between_checks'], null],
        ['Нарушения между объектами', 'V', ['f','b3_between_qty'], ['f','b3_between_sum']]
      ]},
      { t: '4. Контрольные проверки проведённых инвентаризаций', items: [
        ['Проведено контрольных проверок', 'P', ['f','b4_checks'], null],
        ['Выявленные расхождения', 'V', ['f','b4_dev_qty'], ['f','b4_dev_sum']],
        ['Инвентаризаций по строкам, шт.', 'S', ['d','b4_inv','CNT'], null],
        ['Сумма по строкам инвентаризаций, руб.', 'S', null, ['d','b4_inv','sum']]
      ]},
      { t: '5. Выгрузка виртуальных остатков', items: [
        ['Выгружено виртуальных остатков, поз.', 'S', ['f','b5_positions'], null],
        ['Сумма выгрузки, руб.', 'S', null, ['f','b5_sum']]
      ]},
      { t: '6. Поставки с РЦ', items: [
        ['Проведено проверок', 'P', ['f','b6_checks'], null],
        ['Нарушения по качеству', 'V', ['f','b6_q_qty'], ['f','b6_q_sum']],
        ['Нарушения по количеству', 'V', ['f','b6_qty_qty'], ['f','b6_qty_sum']],
        ['Нарушения по срокам годности', 'V', ['f','b6_sg_qty'], ['f','b6_sg_sum']],
        ['Проверено позиций по строкам поставок', 'C', ['d','b6_sup','cnt'], null],
        ['Расхождения по строкам поставок, руб.', 'R', null, ['d','b6_sup','dev']]
      ]},
      { t: '7. Поставки от прямых поставщиков', items: [
        ['Проведено проверок', 'P', ['f','b7_checks'], null],
        ['Нарушения по качеству', 'V', ['f','b7_q_qty'], ['f','b7_q_sum']],
        ['Нарушения по количеству', 'V', ['f','b7_qty_qty'], ['f','b7_qty_sum']],
        ['Нарушения по срокам годности', 'V', ['f','b7_sg_qty'], ['f','b7_sg_sum']],
        ['Проверено позиций по строкам поставок', 'C', ['d','b7_sup','cnt'], null],
        ['Расхождения по строкам поставок, руб.', 'R', null, ['d','b7_sup','dev']]
      ]},
      { t: '8. Проверка качества списания ТМЦ', items: [
        ['Проведено проверок', 'P', ['f','b8_checks'], null],
        ['Выявленные нарушения', 'V', ['f','b8_qty'], ['f','b8_sum']],
        ['Проверено позиций по строкам списаний', 'C', ['d','b8_sp','cnt'], null],
        ['Расхождения по строкам списаний, руб.', 'R', null, ['d','b8_sp','dev']]
      ]},
      { t: '9. Служебные записки на ДГМ', items: [
        ['Количество записок', 'S', ['f','b9_count'], null]
      ]},
      { t: '10. Хищения (уголовные, административные)', items: [
        ['Уголовные преступления', 'V', ['f','b10_crim_qty'], ['f','b10_crim_sum']],
        ['Административные правонарушения', 'V', ['f','b10_adm_qty'], ['f','b10_adm_sum']],
        ['Разбор на месте', 'V', ['f','b10_local_qty'], ['f','b10_local_sum']]
      ]}
    ];
  }
  function sumDetailAgg(sp, it) {
    if (!sp) return null;
    if (sp[0] === 'f') {
      var x;
      if (it) x = recNum(it.data, sp[1]);
      else { var y = parseFloat(String(v(sp[1]) || '').replace(',', '.')); x = isNaN(y) ? 0 : y; }
      return x !== 0 ? x : null;
    }
    var rows = it ? ((it.dyn && it.dyn[sp[1]]) || []) : (dynData[sp[1]] || []);
    if (!rows.length) return null;
    if (sp[2] === 'CNT') return rows.length;
    var s2 = 0, any2 = false;
    for (var r = 0; r < rows.length; r++) {
      var y2 = parseFloat(String(rows[r][sp[2]] || '').replace(',', '.'));
      if (!isNaN(y2) && y2 !== 0) { s2 += y2; any2 = true; }
    }
    return any2 ? s2 : null;
  }
  function sumDetailData(recs) {
    var list = recs || [null];
    var out = [];
    sumDetailSpec().forEach(function(sec) {
      var items = [];
      sec.items.forEach(function(it) {
        var q = null, s = null, violations = 0;
        for (var i = 0; i < list.length; i++) {
          var a = sumDetailAgg(it[2], list[i]), b = sumDetailAgg(it[3], list[i]);
          if (a !== null) q = (q || 0) + a;
          if (b !== null) {
            s = (s || 0) + b;
            // Для кассовых операций каждая строка с суммой > 0 означает одно нарушение.
            if (it[1] === 'M' && b > 0) violations++;
          }
        }
        if (q === null && s === null) return;
        items.push({ t: it[0], k: it[1], q: q, s: s, v: it[1] === 'M' ? violations : null });
      });
      if (items.length) out.push({ t: sec.t, items: items });
    });
    return out;
  }
  function sumDetailTotals(data) {
    // P — проверки; M — кассовые проверки (количество) и 1 нарушение на строку при сумме > 0;
    // V — количество нарушений; R — только денежные расхождения, без увеличения количества нарушений.
    var tp = 0, tv = 0, ts = 0, any = false;
    (data || []).forEach(function(sec) {
      sec.items.forEach(function(it) {
        if (it.k === 'P') { if (it.q !== null) { tp += it.q; any = true; } }
        else if (it.k === 'M') {
          if (it.q !== null) { tp += it.q; any = true; }
          if (it.v) { tv += it.v; any = true; }
          if (it.s !== null) { ts += it.s; any = true; }
        }
        else if (it.k === 'V') { if (it.q !== null) { tv += it.q; any = true; } if (it.s !== null) { ts += it.s; any = true; } }
        else if (it.k === 'R') { if (it.s !== null) { ts += it.s; any = true; } }
      });
    });
    return any ? { p: tp, v: tv, s: ts } : null;
  }
  function sumDetailFlat() {
    var flat = [];
    sumDetailSpec().forEach(function(sec) {
      sec.items.forEach(function(it) { flat.push({ sec: sec.t, t: it[0], k: it[1], q: it[2], s: it[3] }); });
    });
    return flat;
  }

  function docHtml(d) { return window.SBDocument.docHtml(d); }

    function wordHtml(d) { return window.SBDocument.wordHtml(d); }

    // ---------- Прогресс ----------
  var progressCache = null;
  function getProgressCache() {
    if (progressCache) return progressCache;
    var fields = Array.prototype.slice.call(document.querySelectorAll('[data-field]'));
    var cards = Array.prototype.slice.call(document.querySelectorAll('.card')).map(function(card) {
      return { fields: Array.prototype.slice.call(card.querySelectorAll('[data-field]')), badge: card.querySelector('.card-count') };
    });
    var nav = Array.prototype.slice.call(document.querySelectorAll('.app-nav-item')).map(function(btn) {
      var card = document.getElementById(btn.getAttribute('data-card'));
      return { btn: btn, fields: card ? Array.prototype.slice.call(card.querySelectorAll('[data-field]')) : [] };
    });
    progressCache = { fields: fields, selects: fields.filter(function(el) { return el.tagName === 'SELECT'; }), cards: cards, nav: nav };
    return progressCache;
  }
  function fieldVisible(el) {
    var n = el.parentElement;
    while (n) {
      if (n.style && n.style.display === 'none') return false;
      n = n.parentElement;
    }
    return true;
  }
  function updateProgress() {
    var cache = getProgressCache(), vis = [];
    for (var i0 = 0; i0 < cache.fields.length; i0++) if (fieldVisible(cache.fields[i0])) vis.push(cache.fields[i0]);
    var total = vis.length, done = 0;
    for (var i = 0; i < total; i++) if (String(vis[i].value).trim() !== '') done++;
    var pct = total ? Math.round(done / total * 100) : 0;
    $('#progressValue').textContent = pct + '%';
    $('#progressFill').style.width = pct + '%';
    $('#stickDone').textContent = done;
    $('#stickTotal').textContent = total;
    for (var j = 0; j < cache.selects.length; j++) cache.selects[j].classList.toggle('empty', cache.selects[j].value === '');
    for (var c = 0; c < cache.cards.length; c++) {
      var item = cache.cards[c], badge = item.badge;
      if (!badge || !item.fields.length) continue;
      var vf = [], d2 = 0;
      for (var f = 0; f < item.fields.length; f++) if (fieldVisible(item.fields[f])) vf.push(item.fields[f]);
      for (var f2 = 0; f2 < vf.length; f2++) if (String(vf[f2].value).trim() !== '') d2++;
      badge.textContent = d2 + '/' + vf.length;
      badge.classList.toggle('done', vf.length > 0 && d2 === vf.length);
      badge.classList.toggle('started', d2 > 0 && d2 < vf.length);
    }
    refreshNav();
    return { done: done, total: total };
  }

  // ---------- Сохранение ----------
  var toastTimer;
  function showToast() {
    var t = $('#saveToast');
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function() { t.classList.remove('show'); }, 1800);
  }

  function showServerError(error) {
    var t = $('#saveToast');
    if (!t) return;
    t.textContent = 'Ошибка сохранения на сервере. Проверьте соединение.';
    t.style.background = '#c0392b';
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function() {
      t.classList.remove('show');
      t.textContent = 'Данные сохранены';
      t.style.background = '';
    }, 5000);
  }

  async function saveAll() {
    var data = {};
    $$('[data-field]').forEach(function(el) { data[el.id] = el.value; });
    try {
      await serverStore.setItem(KEY, JSON.stringify(data));
      showToast();
    } catch (e) { showServerError(e); }
  }

  function loadAll() {
    var data;
    try { data = JSON.parse(serverStore.getItem(KEY) || '{}'); } catch (e) { data = {}; }
    $$('[data-field]').forEach(function(el) {
      if (data[el.id] !== undefined) el.value = data[el.id];
    });
    b11Sync();
  }

  function resetAll() {
    $$('[data-field]').forEach(function(el) { el.value = ''; });
    b11Sync();
    document.querySelectorAll('textarea[data-field]').forEach(function(el) { el.style.height = '72px'; });
    document.querySelectorAll('.number-field-wrap').forEach(function(wrap) {
      var input = wrap.querySelector('input[type=number]');
      var display = wrap.querySelector('.number-field-display');
      if (input && display) display.textContent = input.getAttribute('placeholder') || '';
    });
    try { serverStore.removeItem(KEY); serverStore.removeItem(KEY + '__dyn'); } catch (e) {}
    dynData = { b4_inv: [], b6_sup: [], b7_sup: [], b8_sp: [] };
    for (var dk in DYN) renderDyn(dk);
    $$('.card').forEach(function(c) { c.classList.remove('open'); });
    $$('.dyn-spoiler').forEach(function(sp) { sp.classList.remove('open'); });
    updateProgress();
    if ($('#previewSection').classList.contains('active')) buildPreview();
    requestAnimationFrame(function() { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  // ---------- Динамические строки (спойлеры) ----------
  var DYN = {
    b4_inv: { title: 'Инвентаризация', fields: [
      { k: 'num', l: 'Номер инвентаризации', t: 'text' },
      { k: 'date', l: 'Дата', t: 'date' },
      { k: 'sum', l: 'Сумма, руб.', t: 'number' }
    ]},
    b6_sup: { title: 'Проверка поставки', fields: [
      { k: 'num', l: 'Номер РО', t: 'text' },
      { k: 'cnt', l: 'Проверено позиций', t: 'number' },
      { k: 'dev', l: 'Выявлено расхождений, руб.', t: 'number' }
    ]},
    b7_sup: { title: 'Проверка поставки', fields: [
      { k: 'num', l: 'Номер прихода', t: 'text' },
      { k: 'cnt', l: 'Проверено позиций', t: 'number' },
      { k: 'dev', l: 'Выявлено расхождений, руб.', t: 'number' }
    ]},
    b8_sp: { title: 'Проверка списаний', fields: [
      { k: 'num', l: 'Номер списания', t: 'text' },
      { k: 'cnt', l: 'Проверено позиций', t: 'number' },
      { k: 'dev', l: 'Выявлено расхождений, руб.', t: 'number' }
    ]}
  };
  var dynData = { b4_inv: [], b6_sup: [], b7_sup: [], b8_sp: [] };

  var dynSaveTimer = null;
  function saveDynNow() {
    clearTimeout(dynSaveTimer);
    dynSaveTimer = null;
    return serverStore.setItem(KEY + '__dyn', JSON.stringify(dynData));
  }
  function saveDyn() {
    clearTimeout(dynSaveTimer);
    dynSaveTimer = setTimeout(saveDynNow, 350);
  }
  function loadDyn() {
    var d = {};
    try { d = JSON.parse(serverStore.getItem(KEY + '__dyn') || '{}') || {}; } catch (e) { d = {}; }
    for (var k in DYN) { if (d[k] && d[k].length) dynData[k] = d[k]; }
    for (var k2 in DYN) renderDyn(k2);
  }
  function renderDyn(key) {
    var list = document.getElementById(key + '_list');
    if (!list) return;
    list.innerHTML = '';
    dynData[key].forEach(function(row, idx) {
      var div = document.createElement('div');
      div.className = 'dyn-row';
      DYN[key].fields.forEach(function(f) {
        var g = document.createElement('div');
        g.className = 'form-group';
        var lab = document.createElement('label');
        lab.textContent = f.l;
        var inp = document.createElement('input');
        inp.type = f.t;
        inp.value = row[f.k] || '';
        inp.addEventListener('input', function() { row[f.k] = inp.value; saveDyn(); });
        g.appendChild(lab);
        g.appendChild(inp);
        div.appendChild(g);
      });
      var del = document.createElement('button');
      del.type = 'button';
      del.className = 'dyn-del';
      del.textContent = '✕';
      del.title = 'Удалить строку';
      del.addEventListener('click', function() { dynData[key].splice(idx, 1); saveDynNow(); renderDyn(key); });
      div.appendChild(del);
      list.appendChild(div);
    });
  }
  function addDyn(key) {
    dynData[key].push({});
    saveDynNow();
    renderDyn(key);
    var sp = document.getElementById(key + '_spoiler');
    if (sp && !sp.classList.contains('open')) {
      sp.classList.add('open');
    }
  }

  // ---------- Предпросмотр ----------
  function buildPreview() {
    var d = buildDoc();
    $('#pageContent').innerHTML = docHtml(d);
  }

  function showPreview() {
    buildPreview();
    var ps = $('#previewSection');
    if (!ps.classList.contains('active')) ps.classList.add('active');
    var hdrP = document.querySelector('.app-header');
    var offP = (hdrP ? hdrP.offsetHeight : 0) + 6;
    window.scrollTo({ top: ps.getBoundingClientRect().top + window.pageYOffset - offP, behavior: 'smooth' });
  }

  // ---------- Сводка и скачивание ----------
  function openSummary() {
    var d = buildDoc();
    var p = updateProgress();
    $('#sumTo').textContent = d.to || '—';
    $('#sumFio').textContent = d.fio || '—';
    $('#sumDate').textContent = d.date ? fdate(d.date) : '—';
    $('#sumFields').textContent = p.done + ' из ' + p.total;
    var dup0 = findDup(d.date, d.to);
    var w = $('#sumDupWarn');
    if (w) {
      if (dup0) {
        var wt = '';
        try { wt = new Date(dup0.t).toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }); } catch (e0) {}
        w.innerHTML = '<strong>Внимание:</strong> отчет за эту дату и объект уже сохранен' +
          (wt ? ' (' + wt : '') + (dup0.fio && dup0.fio !== '—' ? ', составитель: ' + esc(dup0.fio) : '') + (wt ? ')' : '') +
          '. Скачивание перезапишет его — прежняя версия сохранится в истории архива.';
        w.style.display = 'block';
      } else { w.style.display = 'none'; }
    }
    var quality = validateReport();
    var qw = $('#sumQualityWarn');
    if (qw) {
      if (quality.length) {
        qw.classList.remove('ok');
        qw.innerHTML = '<strong>Проверка данных: найдено замечаний — ' + quality.length + '</strong><ul>' + quality.map(function(x) { return '<li>' + esc(x) + '</li>'; }).join('') + '</ul><span>Это предупреждения, а не блокировка: вы можете исправить данные или продолжить скачивание.</span>';
        qw.style.display = 'block';
      } else {
        qw.classList.add('ok');
        qw.innerHTML = '<strong>Проверка данных пройдена</strong>Логических противоречий не обнаружено.';
        qw.style.display = 'block';
      }
    }
    $('#summaryOverlay').classList.add('show');
  }

  function closeSummary() { $('#summaryOverlay').classList.remove('show'); }

  // Запоминаем выбранную папку в IndexedDB, чтобы не выбирать её при каждом сохранении.
  var folderDbPromise;
  function openFolderDb() {
    if (folderDbPromise) return folderDbPromise;
    folderDbPromise = new Promise(function(resolve, reject) {
      if (!window.indexedDB) { reject(new Error('IndexedDB недоступна')); return; }
      var req = indexedDB.open('spravka_sb_folder_settings', 1);
      req.onupgradeneeded = function() { req.result.createObjectStore('settings'); };
      req.onsuccess = function() { resolve(req.result); };
      req.onerror = function() { reject(req.error || new Error('Не удалось открыть хранилище папки')); };
    });
    return folderDbPromise;
  }
  async function getSavedFolder() {
    var db = await openFolderDb();
    return new Promise(function(resolve, reject) {
      var tx = db.transaction('settings', 'readonly');
      var req = tx.objectStore('settings').get('targetFolder');
      req.onsuccess = function() { resolve(req.result || null); };
      req.onerror = function() { reject(req.error); };
    });
  }
  async function rememberFolder(handle) {
    var db = await openFolderDb();
    return new Promise(function(resolve, reject) {
      var tx = db.transaction('settings', 'readwrite');
      tx.objectStore('settings').put(handle, 'targetFolder');
      tx.oncomplete = function() { resolve(); };
      tx.onerror = function() { reject(tx.error || new Error('Не удалось запомнить папку')); };
    });
  }
  async function chooseOrReuseFolder() {
    if (!window.showDirectoryPicker) return null;
    var saved = null;
    try { saved = await getSavedFolder(); } catch (e) { console.warn('Не удалось прочитать сохранённую папку', e); }
    if (saved) {
      try {
        var permission = await saved.queryPermission({ mode: 'readwrite' });
        if (permission !== 'granted') permission = await saved.requestPermission({ mode: 'readwrite' });
        if (permission === 'granted') return saved;
      } catch (e) { console.warn('Повторное разрешение на папку не получено', e); }
    }
    try {
      // Папка выбирается один раз; её дескриптор сохраняется для следующих запусков.
      var chosen = await window.showDirectoryPicker({ mode: 'readwrite' });
      try { await rememberFolder(chosen); }
      catch (e) { console.warn('Папка выбрана, но не удалось сохранить выбор для следующих запусков', e); }
      return chosen;
    } catch (e) {
      if (e && e.name === 'AbortError') return null;
      throw e;
    }
  }

  // Ручная смена папки сохранения без сброса данных отчета.
  async function changeSaveFolder() {
    if (!window.showDirectoryPicker) {
      alert('Выбор папки не поддерживается этим браузером. Откройте конструктор в актуальном Chrome или Edge.');
      return;
    }
    try {
      var chosen = await window.showDirectoryPicker({ mode: 'readwrite' });
      await rememberFolder(chosen);
      alert('Папка сохранения изменена. Следующие отчеты будут сохраняться в выбранную папку.');
    } catch (e) {
      if (e && e.name === 'AbortError') return;
      console.error(e);
      alert('Не удалось изменить папку сохранения. Проверьте разрешения браузера и попробуйте ещё раз.');
    }
  }

  var dlTimer;
  var downloadInProgress = false;
  async function startDownload() {
    if (downloadInProgress) return;
    downloadInProgress = true;
    var confirmBtn = $('#summaryConfirm');
    if (confirmBtn) confirmBtn.disabled = true;
    try {
      var dchk = buildDoc();
      var dup = findDup(dchk.date, dchk.to);
      if (dup && !confirm('Отчет за эту дату и объект уже сохранен в архиве.\n\nOK — перезаписать (прежняя версия сохранится в истории)\nОтмена — вернуться к проверке')) return;
      closeSummary();
      var dirHandle = null;
      if (window.showDirectoryPicker) {
        try {
          dirHandle = await chooseOrReuseFolder();
          if (!dirHandle) return;
        } catch (e) {
          console.error(e);
          alert('Не удалось получить доступ к папке. Выберите папку повторно или проверьте разрешения браузера.');
          return;
        }
      } else {
        alert('Этот браузер не поддерживает сохранение напрямую в папки. Отчет будет скачан обычным способом. Для сохранения по папкам откройте файл в актуальном Chrome или Edge.');
      }
      var ov = $('#downloadOverlay');
      ov.classList.add('show');
      var fill = $('#dlFill'), txt = $('#dlText'), pct = 0;
      fill.style.width = '0%'; txt.textContent = '0%';
      clearInterval(dlTimer);
      await new Promise(function(resolve) {
        dlTimer = setInterval(function() {
          pct += Math.floor(Math.random() * 12) + 6;
          if (pct >= 100) {
            pct = 100;
            clearInterval(dlTimer);
            setTimeout(async function() {
              try { await doSave(dirHandle); }
              catch (e) { console.error(e); alert('Не удалось завершить сохранение Word или серверного архива. Проверьте соединение с сервером и разрешение на запись в папку.'); }
              finally {
                ov.classList.remove('show');
                resolve();
              }
            }, 350);
          }
          fill.style.width = pct + '%';
          txt.textContent = pct + '%';
        }, 110);
      });
    } finally {
      downloadInProgress = false;
      if (confirmBtn) confirmBtn.disabled = false;
    }
  }

  async function doSave(rootHandle) {
    var d = buildDoc();
    var html = wordHtml(d);
    var safe = function(x) { return String(x || '').replace(/[\\\\/:*?"<>|]/g, '_').replace(/\\s+/g, '_').trim() || 'Не указано'; };
    var fname = 'Отчет_контроль_' + safe(d.to || 'объект') +
      (d.fio ? '_' + safe(d.fio) : '') +
      (d.date ? '_' + fdate(d.date) : '') + '.doc';
    var blob = new Blob(['\\ufeff' + html], { type: 'application/msword;charset=utf-8' });
    if (rootHandle) {
      var employeeName = safe(d.fio || 'Сотрудник не указан').replace(/_/g, ' ');
      var monthName = d.date ? d.date.slice(0, 7) : new Date().toISOString().slice(0, 7);
      var employeeHandle = await rootHandle.getDirectoryHandle(employeeName, { create: true });
      var monthHandle = await employeeHandle.getDirectoryHandle(monthName, { create: true });
      var fileHandle = await monthHandle.getFileHandle(fname, { create: true });
      var writable = await fileHandle.createWritable();
      await writable.write(blob);
      await writable.close();
    } else {
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = fname;
      document.body.appendChild(a);
      a.click();
      setTimeout(function() { URL.revokeObjectURL(url); a.remove(); }, 800);
    }
    saveToArchive(true);
    // Подтверждаем запись отчета и архива на сервере до завершения сохранения.
    await serverStore.flush();
  }


  // ---------- Сводная таблица итогов (v1.74) ----------
  function nsum(list, dynKey, dynField) {
    var s = 0, any = false;
    for (var i = 0; i < list.length; i++) {
      var x = parseFloat(String(v(list[i])).replace(',', '.'));
      if (!isNaN(x) && x !== 0) { s += x; any = true; }
    }
    if (dynKey) {
      var rows = dynData[dynKey] || [];
      for (var j = 0; j < rows.length; j++) {
        var y = parseFloat(String(rows[j][dynField] || '').replace(',', '.'));
        if (!isNaN(y) && y !== 0) { s += y; any = true; }
      }
    }
    return any ? s : null;
  }

  function sumTableHtml(data) {
    // Текстовое представление показателей: сохраняет роли и итоговые расчёты, не использует таблицы.
    if (!data || !data.length) return '';
    // Единое правило подсчёта:
    // P — количество проведённых проверок;
    // V — количество выявленных нарушений (каждое нарушение отдельно);
    // R — только сумма денежных расхождений, в количество нарушений не входит.
    function secAgg(items) {
      var totals = sumDetailTotals([{ t: '', items: items }]);
      return totals
        ? { p: totals.p, v: totals.v, s: totals.s, has: true }
        : { p: 0, v: 0, s: 0, has: false };
    }
    function refVal(it) { return it.k === 'S' ? (it.q !== null ? it.q : it.s) : (it.k === 'C' ? it.q : null); }
    function labeled(label, value, suffix) {
      return value !== null && value !== undefined ? label + ': ' + num(value) + (suffix || '') : '';
    }
    var h = '', T = { p: 0, v: 0, s: 0 };
    for (var i = 0; i < data.length; i++) {
      h += '<p class="doc-sum-section">' + esc(data[i].t) + '</p>';
      for (var j = 0; j < data[i].items.length; j++) {
        var it = data[i].items[j], parts = [];
        if ((it.k === 'P' || it.k === 'M') && it.q !== null) parts.push(labeled('Проверено', it.q, ''));
        if (it.k === 'V' && it.q !== null) parts.push(labeled('Выявлено нарушений', it.q, ''));
        if (it.k === 'M' && it.v) parts.push(labeled('Выявлено нарушений', it.v, ''));
        if ((it.k === 'V' || it.k === 'M' || it.k === 'R') && it.s !== null) parts.push(labeled('Сумма нарушений и расхождений', it.s, ' руб.'));
        var rv = refVal(it);
        if (rv !== null) parts.push(labeled('Справочно', rv, ''));
        h += '<p class="doc-sum-item"><strong>' + esc(it.t) + '</strong></p>';
        if (parts.length) h += '<p class="doc-sum-detail">' + parts.map(esc).join(';&nbsp;&nbsp; ') + '</p>';
      }
      var ra = secAgg(data[i].items);
      if (ra.has) {
        var secParts = [];
        secParts.push('Проверено: ' + num(ra.p));
        secParts.push('Выявлено нарушений: ' + num(ra.v));
        secParts.push('Сумма нарушений и расхождений: ' + num(ra.s) + ' руб.');
        h += '<p class="doc-sum-detail"><strong>Итого по разделу:</strong> ' + secParts.map(esc).join(';&nbsp;&nbsp; ') + '</p>';
        T.p += ra.p; T.v += ra.v; T.s += ra.s;
      }
    }
    h += '<p class="doc-sum-total" style="margin:7pt 0 3pt 0;padding:5pt 0 0 0;border-top:.8pt solid #555555;font-family:Arial,Helvetica,sans-serif;font-size:10pt;font-weight:bold;color:#000000;"><strong>ИТОГО</strong></p>';
    h += '<p class="doc-sum-detail"><strong>Проверено:</strong> ' + num(T.p) + '&nbsp;&nbsp; <strong>Выявлено нарушений:</strong> ' + num(T.v) + '&nbsp;&nbsp; <strong>Сумма нарушений и расхождений:</strong> ' + num(T.s) + ' руб.</p>';
    h += '<p class="doc-sum-detail">Каждое выявленное нарушение учитывается отдельно; одна проверка может дать несколько нарушений. Справочные показатели приведены отдельно и в итоговые суммы не включаются.</p>';
    return h;
  }
  // v1.1.5: единый рендер детальной таблицы «Проверено | Выявлено | Справочно | Сумма» для дневного и сводного Word
  function summaryTableHtml() { return sumTableHtml(sumDetailData(null)); }

  // ---------- Редактируемые списки (v1.74) ----------
  var LKEY = KEY + '__lists';
  var LISTS = { to: [], fio: [] };
  var listKey = null;

  function selId(k) { return k === 'to' ? 'b0_to' : 'b0_fio'; }
  function selLabel(k) { return k === 'to' ? 'Объект (ТО)' : 'Составитель (ФИО)'; }

  function initLists() {
    ['to', 'fio'].forEach(function(k) {
      var sel = document.getElementById(selId(k));
      var cur = [];
      for (var i = 0; i < sel.options.length; i++) if (sel.options[i].value) cur.push(sel.options[i].value);
      LISTS[k] = cur;
    });
    var saved;
    try { saved = JSON.parse(serverStore.getItem(LKEY) || 'null'); } catch (e) { saved = null; }
    if (saved && saved.to && saved.to.length) LISTS.to = saved.to;
    if (saved && saved.fio && saved.fio.length) LISTS.fio = saved.fio;
    renderListOptions('to');
    renderListOptions('fio');
  }

  function renderListOptions(k) {
    var sel = document.getElementById(selId(k));
    var cur = sel.value;
    sel.innerHTML = '';
    var o0 = document.createElement('option');
    o0.value = ''; o0.textContent = k === 'to' ? 'Выберите объект' : 'Выберите сотрудника';
    sel.appendChild(o0);
    LISTS[k].forEach(function(it) {
      var o = document.createElement('option');
      o.value = it; o.textContent = it;
      sel.appendChild(o);
    });
    if (cur) {
      if (LISTS[k].indexOf(cur) < 0) {
        var o2 = document.createElement('option');
        o2.value = cur; o2.textContent = cur;
        sel.appendChild(o2);
      }
      sel.value = cur;
    }
    if (sel.value === '') sel.classList.add('empty');
    else sel.classList.remove('empty');
  }

  function saveLists() {
    return serverStore.setItem(LKEY, JSON.stringify(LISTS));
  }

  function openListEditor(k) {
    listKey = k;
    $('#listModalTitle').textContent = 'Список: ' + selLabel(k);
    $('#listNewValue').value = '';
    renderListItems();
    $('#listOverlay').classList.add('show');
    $('#listNewValue').focus();
  }

  function renderListItems() {
    var box = $('#listItems');
    box.innerHTML = '';
    if (!LISTS[listKey].length) {
      box.innerHTML = '<div class="archive-empty">Список пуст</div>';
      return;
    }
    LISTS[listKey].forEach(function(it, idx) {
      var row = document.createElement('div');
      row.className = 'archive-item';
      var info = document.createElement('div');
      info.className = 'archive-item-info';
      info.innerHTML = '<span class="archive-item-title">' + esc(it) + '</span>';
      var act = document.createElement('div');
      act.className = 'archive-item-actions';
      var del = document.createElement('button');
      del.type = 'button';
      del.className = 'mini-btn danger';
      del.textContent = 'Удалить';
      del.disabled = LISTS[listKey].length <= 1;
      del.addEventListener('click', function() {
        LISTS[listKey].splice(idx, 1);
        saveLists();
        renderListItems();
        renderListOptions(listKey);
      });
      act.appendChild(del);
      row.appendChild(info);
      row.appendChild(act);
      box.appendChild(row);
    });
  }

  function addListItem() {
    var val = $('#listNewValue').value.trim();
    if (!val) return;
    if (LISTS[listKey].indexOf(val) >= 0) { $('#listNewValue').value = ''; return; }
    LISTS[listKey].push(val);
    $('#listNewValue').value = '';
    saveLists();
    renderListItems();
    renderListOptions(listKey);
    $('#listNewValue').focus();
  }

  // ---------- Архив отчетов (v1.74) ----------
  var AKEY = KEY + '__archive';

  function getArchive() {
    try { return JSON.parse(serverStore.getItem(AKEY) || '[]') || []; } catch (e) { return []; }
  }
  function setArchive(a) {
    // Дедупликация: по паре «дата + объект» остаётся самая свежая запись (v1.96).
    // Перезаписанные версии не теряются — переносятся в поле hist новой записи.
    var seen = Object.create(null), out = [];
    // Массив хранится от новых записей к старым: первая встреченная запись — актуальная.
    for (var i = 0; i < a.length; i++) {
      var it = a[i];
      var dt = (it && it.data && (it.data.b0_date || it.data.date)) || '';
      var ob = (it && it.data && (it.data.b0_to || it.data.to)) || '';
      // Неполные черновики не должны перезаписывать друг друга по пустому ключу.
      if (!dt && !ob) { out.push(it); continue; }
      var key = String(dt) + '|' + String(ob);
      if (seen[key]) continue;
      seen[key] = true;
      out.push(it);
    }
    serverStore.setItem(AKEY, JSON.stringify(out));
  }

  function saveToArchive(silent) {
    var data = {};
    $$('[data-field]').forEach(function(el) { data[el.id] = el.value; });
    var d = buildDoc();
    var done = 0, total = 0;
    for (var k in data) { total++; if (String(data[k]).trim() !== '') done++; }
    var snap = buildSnap(d);
    var a = getArchive();
    var dkey = String(d.date || '') + '|' + String(d.to || '');
    var ex = null;
    for (var ei = 0; ei < a.length; ei++) {
      var kd = (a[ei].data && a[ei].data.b0_date) || '';
      var ko = (a[ei].data && a[ei].data.b0_to) || '';
      if (String(kd) + '|' + String(ko) === dkey) { ex = a[ei]; break; }
    }
    if (ex) {
      snap.hist = [{ t: ex.t, label: ex.label, fio: ex.fio, done: ex.done, total: ex.total,
        data: ex.data, dyn: ex.dyn }].concat(ex.hist || []).slice(0, 5);
    }
    a.unshift(snap);
    if (a.length > 400) a = a.slice(0, 400);
    setArchive(a);
    if (!silent) {
    }
  }

  // ================= СВОДНАЯ ЗА МЕСЯЦ (v1.74) =================
  var MONTHS_RU = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];

  function safeName(x) {
    return String(x || '').replace(/[\/\\:*?"<>|]/g, '_').replace(/\s+/g, ' ').trim() || 'Не указано';
  }
  function recNum(src2, id) {
    var x = parseFloat(String((src2 || {})[id] || '').replace(',', '.'));
    return isNaN(x) ? 0 : x;
  }
  function dynSum(dyn, key, field) {
    var s = 0, rows = (dyn && dyn[key]) || [];
    for (var i = 0; i < rows.length; i++) {
      var x = parseFloat(String(rows[i][field] || '').replace(',', '.'));
      if (!isNaN(x)) s += x;
    }
    return s;
  }
  function dynCount(dyn, key) {
    return ((dyn && dyn[key]) || []).length;
  }
  function buildSnap(d) {
    var data = {};
    $$('[data-field]').forEach(function(el) { data[el.id] = el.value; });
    var done = 0, total = 0;
    for (var k in data) { total++; if (String(data[k]).trim() !== '') done++; }
    return {
      ver: VERSION,
      t: Date.now(),
      label: (d.to || 'объект') + (d.date ? ', ' + fdate(d.date) : ''),
      fio: d.fio || '—',
      done: done, total: total,
      data: data,
      dyn: JSON.parse(JSON.stringify(dynData))
    };
  }
function monthRecords(month, object) {
    var a = getArchive(), latest = Object.create(null);
    for (var i = 0; i < a.length; i++) {
      var it = a[i];
      if (!it || !it.data) continue;
      var d = it.data.b0_date || it.data.date || '';
      var ob = String(it.data.b0_to || it.data.to || '');
      if (!d || d.slice(0, 7) !== month) continue;
      if (object && ob !== object) continue;
      // При выборе «Все объекты» записи одного дня от разных объектов независимы.
      var key = d + '|' + ob;
      if (!latest[key] || (Number(it.t) || 0) > (Number(latest[key].t) || 0)) latest[key] = it;
    }
    return Object.keys(latest).sort().map(function(k) { return latest[k]; });
  }
  function isoWeekRange(weekValue) {
    var m = /^(\d{4})-W(\d{2})$/.exec(weekValue || '');
    if (!m) return null;
    var y = +m[1], w = +m[2];
    var jan4 = new Date(Date.UTC(y, 0, 4));
    var day = jan4.getUTCDay() || 7;
    var monday = new Date(jan4.getTime() - (day - 1) * 86400000 + (w - 1) * 7 * 86400000);
    var sunday = new Date(monday.getTime() + 6 * 86400000);
    function iso(d) { return d.getUTCFullYear() + '-' + String(d.getUTCMonth()+1).padStart(2,'0') + '-' + String(d.getUTCDate()).padStart(2,'0'); }
    var check = new Date(Date.UTC(monday.getUTCFullYear(), monday.getUTCMonth(), monday.getUTCDate() + 3));
    if (check.getUTCFullYear() !== y || w < 1 || w > 53) return null;
    return { start: iso(monday), end: iso(sunday), label: w + ' неделя ' + y + ' года', key: y + '-W' + String(w).padStart(2,'0'), folder: 'Неделя_' + y + '_W' + String(w).padStart(2,'0') };
  }
  function currentIsoWeekValue() {
    var d = new Date(), u = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    u.setUTCDate(u.getUTCDate() + 4 - (u.getUTCDay() || 7));
    var y = u.getUTCFullYear(), jan1 = new Date(Date.UTC(y,0,1));
    var w = Math.ceil((((u - jan1) / 86400000) + 1) / 7);
    return y + '-W' + String(w).padStart(2,'0');
  }
  function selectedSummaryPeriod() {
    if ($('#sumPeriodType').value === 'week') return isoWeekRange($('#sumWeek').value);
    var month = $('#sumMonth').value;
    if (!month) return null;
    var p = month.split('-'), y = +p[0], m = +p[1];
    var last = new Date(y, m, 0).getDate();
    return { start: month + '-01', end: month + '-' + String(last).padStart(2,'0'), key: month, folder: month,
      label: (MONTHS_RU[m-1] || month) + ' ' + y };
  }
  function periodRecords(period, object) {
    if (!period) return [];
    var a = getArchive(), latest = Object.create(null);
    for (var i = 0; i < a.length; i++) {
      var it = a[i]; if (!it || !it.data) continue;
      var d = it.data.b0_date || it.data.date || '';
      var ob = String(it.data.b0_to || it.data.to || '');
      if (!d || d < period.start || d > period.end || (object && ob !== object)) continue;
      var key = d + '|' + ob;
      if (!latest[key] || (Number(it.t)||0) > (Number(latest[key].t)||0)) latest[key] = it;
    }
    return Object.keys(latest).sort().map(function(k){ return latest[k]; });
  }

  // ===== v1.96: дашборд месяца (вертикальная вёрстка) =====
  function daysInMonth(ym) { // v1.2.1: все дни без выходных; текущий месяц — до сегодняшнего числа, прошлые — целиком
    if (!ym) return 0;
    var p = ym.split('-'), y = +p[0], m = +p[1];
    if (!y || !m) return 0;
    var now = new Date();
    var total = new Date(y, m, 0).getDate();
    return (y === now.getFullYear() && m === now.getMonth() + 1) ? Math.min(now.getDate(), total) : total;
  }

  function buildSummaryWord(recs, periodLabel, object) {
    function S(id) { var s = 0; for (var i = 0; i < recs.length; i++) s += recNum(recs[i].data, id); return s; }
    function D(key, f) { var s = 0; for (var i = 0; i < recs.length; i++) s += dynSum(recs[i].dyn, key, f); return s; }
    function DC(key) { var n = 0; for (var i = 0; i < recs.length; i++) n += dynCount(recs[i].dyn, key); return n; }
    function uniq(a) {
      var o = [], seen = {};
      for (var i = 0; i < a.length; i++) { if (a[i] && !seen[a[i]]) { seen[a[i]] = 1; o.push(a[i]); } }
      return o;
    }
    function DL(key, prefix) {
      var out = [];
      for (var i = 0; i < recs.length; i++) {
        var rows = (recs[i].dyn && recs[i].dyn[key]) || [];
        for (var j = 0; j < rows.length; j++) {
          var r = rows[j], parts = [];
          if (r.num) parts.push('№ ' + r.num);
          if (r.date) parts.push('от ' + fdate(r.date));
          if (r.cnt) parts.push('проверено позиций: ' + r.cnt);
          if (r.dev) parts.push('расхождения: ' + num(r.dev) + ' руб.');
          if (r.sum) parts.push('сумма: ' + num(r.sum) + ' руб.');
          if (!parts.length) continue;
          out.push(prefix + ': ' + parts.join(', ') + ' (дневной отчет от ' + fdate(recs[i].data.b0_date) + ').');
        }
      }
      return out;
    }
    function pairLine(label, qid, sid, unit) {
      var q = S(qid), s = S(sid);
      if (!q && !s) return null;
      if (q && s) return label + ': ' + num(q) + ' ' + unit + ' — нарушения на сумму ' + num(s) + ' руб.';
      if (q) return label + ': ' + num(q) + ' ' + unit + ' — нарушений не выявлено';
      return label + ': нарушения на сумму ' + num(s) + ' руб.';
    }
    // Формирование сводного текста по результатам проверок за период.
    function checkPairS(label, chkId, qtyId, sumId, vword) {
      vword = vword || 'нарушений';
      var c = S(chkId), q = S(qtyId), s = S(sumId);
      if (!c && !q && !s) return null;
      if (!c) {
        if (q || s) return label + ': выявлено ' + vword + ': ' + num(q) + ' шт.' + (s ? ', на сумму ' + num(s) + ' руб.' : '') + '.';
        return null;
      }
      var base = label + ': проведено проверок: ' + num(c) + ' шт.';
      if (!q && !s) return base + ' ' + vword.charAt(0).toUpperCase() + vword.slice(1) + ' не выявлено.';
      return base + ' Выявлено ' + vword + ': ' + num(q) + ' шт.' + (s ? ', на сумму ' + num(s) + ' руб.' : '') + '.';
    }
    function checkMultiS(chkId, pairs) {
      var c = S(chkId);
      var totQ = 0, totS = 0, parts = [], any = false;
      for (var i = 0; i < pairs.length; i++) {
        var q = S(pairs[i][1]), s = S(pairs[i][2]);
        if (q || s) {
          any = true;
          totQ += q; totS += s;
          parts.push(pairs[i][0] + ' — ' + num(q) + ' шт.' + (s ? ', ' + num(s) + ' руб.' : ''));
        }
      }
      if (!c && !any) return null;
      if (!c) {
        var o0 = 'Выявлено нарушений: ' + num(totQ) + ' шт.' + (totS ? ', на сумму ' + num(totS) + ' руб.' : '');
        if (parts.length > 1) o0 += ' В том числе: ' + parts.join('; ');
        return o0 + '.';
      }
      if (!any) return 'Проведено проверок: ' + num(c) + ' шт. Нарушений не выявлено.';
      var o = 'Проведено проверок: ' + num(c) + ' шт. Выявлено нарушений: ' + num(totQ) + ' шт.' + (totS ? ', на сумму ' + num(totS) + ' руб.' : '');
      if (parts.length > 1) o += ' В том числе: ' + parts.join('; ');
      return o + '.';
    }

    var obj = object || (recs.length ? recs[0].data.b0_to : '') || 'Все объекты';
    var first = recs.length ? fdate(recs[0].data.b0_date) : '';
    var last = recs.length ? fdate(recs[recs.length - 1].data.b0_date) : '';
    var mTitle = periodLabel;

    var secs = [];
    function sec(t, lines) {
      lines = lines.filter(Boolean);
      secs.push({ t: t, lines: lines });
    }

    sec('1. Проверка кассовых операций (удаление позиций из чека, удаление чека, возвраты, низкий вес товара)', [
      checkPairS('Отмена позиций в чеке', 'b1_pos_checks', 'b1_pos_qty', 'b1_pos_sum'),
      checkPairS('Анулирование чека в отсутствии администратора', 'b1_chek_checks', 'b1_chek_qty', 'b1_chek_sum'),
      checkPairS('Фиктивные возвраты', 'b1_ret_checks', 'b1_ret_qty', 'b1_ret_sum'),
      checkPairS('Продажа товара с заниженным весом', 'b1_weight_checks', 'b1_weight_qty', 'b1_weight_sum')
    ]);

    sec('2. Работа с FaceID, реагирование, пополнение базы данных',
      (S('b2_triggers') || S('b2_worked') || S('b2_db')) ? [
        'Срабатываний за период: ' + num(S('b2_triggers')) + ' шт.',
        'Отработано (реагирование): ' + num(S('b2_worked')) + ' шт.',
        'Пополнено базы данных: ' + num(S('b2_db')) + ' чел.'
      ] : []);

    sec('3. Контроль за перемещениями ТМЦ (внутри объекта, между объектами)', [
      checkPairS('Перемещения внутри объекта', 'b3_in_checks', 'b3_in_qty', 'b3_in_sum'),
      checkPairS('Перемещения между объектами', 'b3_between_checks', 'b3_between_qty', 'b3_between_sum')
    ]);

    sec('4. Контрольные проверки проведённых инвентаризаций операционной службой', [
      checkPairS('Контрольные проверки инвентаризаций', 'b4_checks', 'b4_dev_qty', 'b4_dev_sum', 'расхождений'),
      DC('b4_inv') ? 'Отмечено инвентаризаций (по строкам): ' + DC('b4_inv') + ', суммарно по строкам: ' + num(D('b4_inv', 'sum')) + ' руб.' : null
    ].concat(DL('b4_inv', 'Инвентаризация')));

    var s5 = [];
    if (S('b5_positions') || S('b5_sum'))
      s5.push('Выгружено виртуальных остатков за период: ' + num(S('b5_positions')) + ' поз. на сумму ' + num(S('b5_sum')) + ' руб.');
    var d5 = [], sz5 = [], copyYes = 0, copyNo = 0;
    for (var i5 = 0; i5 < recs.length; i5++) {
      var dd = recs[i5].data;
      if (dd.b5_date) d5.push(fdate(dd.b5_date));
      if (dd.b5_sz_date) sz5.push(fdate(dd.b5_sz_date));
      if (dd.b5_copy === 'направлена') copyYes++;
      else if (dd.b5_copy === 'не направлена') copyNo++;
    }
    if (d5.length) s5.push('Даты выгрузки: ' + uniq(d5).join(', '));
    if (sz5.length) s5.push('Служебные записки на ДГМ: ' + sz5.length + ' шт. (даты: ' + uniq(sz5).join(', ') + ')');
    if (copyYes || copyNo) s5.push('Копия Директору Группы: направлена — ' + copyYes + ', не направлена — ' + copyNo);
    sec('5. Выгрузка виртуальных остатков, предоставление информации операционной службе для пересчёта', s5);

    sec('6. Проверка качества поставки ТМЦ с РЦ (сырьё для производства, сыры, колбасы, мясо и т.д.)', [
      checkMultiS('b6_checks', [
        ['по качеству', 'b6_q_qty', 'b6_q_sum'],
        ['по количеству', 'b6_qty_qty', 'b6_qty_sum'],
        ['по срокам годности', 'b6_sg_qty', 'b6_sg_sum']
      ]),
      DC('b6_sup') ? 'Проверки поставок (по строкам): ' + DC('b6_sup') + ', проверено позиций: ' + num(D('b6_sup', 'cnt')) + ', расхождения: ' + num(D('b6_sup', 'dev')) + ' руб.' : null
    ].concat(DL('b6_sup', 'Проверка поставки')));

    sec('7. Проверка качества поставки ТМЦ от прямых поставщиков', [
      checkMultiS('b7_checks', [
        ['по качеству', 'b7_q_qty', 'b7_q_sum'],
        ['по количеству', 'b7_qty_qty', 'b7_qty_sum'],
        ['по срокам годности', 'b7_sg_qty', 'b7_sg_sum']
      ]),
      DC('b7_sup') ? 'Проверки поставок (по строкам): ' + DC('b7_sup') + ', проверено позиций: ' + num(D('b7_sup', 'cnt')) + ', расхождения: ' + num(D('b7_sup', 'dev')) + ' руб.' : null
    ].concat(DL('b7_sup', 'Проверка поставки')));

    sec('8. Проверка качества списания ТМЦ', [
      checkMultiS('b8_checks', [['нарушений', 'b8_qty', 'b8_sum']]),
      DC('b8_sp') ? 'Проверки списаний (по строкам): ' + DC('b8_sp') + ', проверено позиций: ' + num(D('b8_sp', 'cnt')) + ', расхождения: ' + num(D('b8_sp', 'dev')) + ' руб.' : null
    ].concat(DL('b8_sp', 'Проверка списаний')));

    sec('9. Количество служебных записок по выявленным нарушениям на ДГМ',
      S('b9_count') ? ['Количество служебных записок за период: ' + num(S('b9_count')) + ' шт.'] : []);

    sec('10. Хищения (уголовные, административные)', [
      pairLine('Уголовные преступления', 'b10_crim_qty', 'b10_crim_sum', 'шт.'),
      pairLine('Административные правонарушения', 'b10_adm_qty', 'b10_adm_sum', 'шт.'),
      pairLine('Разбор на месте', 'b10_local_qty', 'b10_local_sum', 'шт.')
    ]);

    sec('11. Работа ООО ЧОП «Артакс+»', (function() {
      var lines = [];
      for (var ic = 0; ic < recs.length; ic++) {
        var d = recs[ic] && recs[ic].data, dt = fdate(d && d.b0_date || ''), txt = chopDayText(d);
        if (txt) lines.push((dt ? dt + ': ' : '') + txt + '.');
      }
      return lines;
    })());

    var detail = sumDetailData(recs);
    // v1.1.5: detailTot (мёртвый код) убран

    var cbm = [['1','b1_comment'],['2','b2_comment'],['3','b3_comment'],['4','b4_comment'],['5','b5_comment'],['6','b6_comment'],['7','b7_comment'],['8','b8_comment'],['9','b9_comment'],['10','b10_comment'],['11','b11_comment']];
    var cm = [];
    for (var ic = 0; ic < recs.length; ic++) {
      var cdata = recs[ic].data, cdate = fdate(cdata.b0_date || '');
      for (var k2 = 0; k2 < cbm.length; k2++) {
        if (cdata[cbm[k2][1]]) cm.push(cdate + ' (раздел ' + cbm[k2][0] + '): ' + cdata[cbm[k2][1]]);
      }
    }

    var out = '<div class="doc-wrap">';
    out += docMasthead('СВОДНЫЙ ОТЧЕТ');
    out += '<h1 style="margin:0 0 4pt 0;font-family:Calibri,Arial,sans-serif;font-size:17pt;line-height:1.15;font-weight:bold;color:#000000;text-align:left;page-break-after:avoid;">Сводный отчёт по итогам контрольных мероприятий</h1>';
    out += '<p style="margin:0 0 15pt 0;font-family:Calibri,Arial,sans-serif;font-size:10pt;color:#000000;text-align:left;">за ' + esc(mTitle) + '</p>';
    var uniqDays = uniq(recs.map(function(r) { return r && r.data ? r.data.b0_date : ''; })).length;
    out += docMetaTable([['Объект (ТО)', esc(obj)], ['Период', esc(first) + ' — ' + esc(last)], ['Записей в своде', String(recs.length) + (uniqDays > 1 ? ' (за ' + uniqDays + ' дн.)' : '')]]);
    for (var is2 = 0; is2 < secs.length; is2++) {
      if (!secs[is2].lines.length) continue;
      out += docSection(secs[is2].t, secs[is2].lines, null);
    }
    // В разделах уже показаны агрегированные данные за период.
    // Не повторяем подробные строки второй раз — оставляем только общий итог.
    var periodTotals = sumDetailTotals(detail);
    if (periodTotals) {
      out += '<div class="doc-totals" style="margin:19pt 0 0 0;padding:9pt 0 0 0;border-top:.9pt solid #555555;page-break-inside:avoid;">';
      out += '<p class="doc-totals-title" style="margin:0 0 8pt 0;font-family:Arial,Helvetica,sans-serif;font-size:11pt;line-height:1.2;font-weight:bold;color:#000000;">ИТОГО ЗА ПЕРИОД</p>';
      out += '<p class="doc-total-line" style="margin:4pt 0 4pt 11pt;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.35;color:#000000;"><strong>Проверено:</strong> ' + num(periodTotals.p) + '</p>';
      out += '<p class="doc-total-line" style="margin:4pt 0 4pt 11pt;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.35;color:#000000;"><strong>Выявлено нарушений:</strong> ' + num(periodTotals.v) + '</p>';
      out += '<p class="doc-total-line" style="margin:4pt 0 4pt 11pt;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.35;color:#000000;"><strong>Сумма нарушений и расхождений:</strong> ' + num(periodTotals.s) + ' руб.</p>';
      out += '</div>';
    }

    if (cm.length) {
      out += '<p class="sec-title">Комментарии по дням</p>';
      for (var kc = 0; kc < cm.length; kc++) {
        var ci = cm[kc].indexOf(': ');
        out += '<p class="doc-note"><span class="doc-cmt-date">' + esc(ci >= 0 ? cm[kc].slice(0, ci + 1) : cm[kc]) + '</span> ' + esc(ci >= 0 ? cm[kc].slice(ci + 2) : '') + '</p>';
      }
    }

    out += '<div class="doc-sign"><p><strong>Сводный отчёт составил:</strong> _______________________</p><p><strong>Подпись:</strong> ____________________</p></div>';
    out += '</div>';
    return '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">' +
      '<head><meta charset="utf-8"><title>Сводный отчет СБ</title>' +
      '<style>' + docCss() + '<\/style></head><body>' + out + '</body></ht' + 'ml>';
  }
  // Прогресс сборки сводной (v1.86): окно показывается после подготовки,
  // файл записывается только когда полоса доходит до 100%.
  function summaryDlStart(after) {
    var ov = $('#downloadOverlay');
    ov.classList.add('show');
    var fill = $('#dlFill'), txt = $('#dlText'), pct = 0;
    fill.style.width = '0%'; txt.textContent = '0%';
    clearInterval(dlTimer);
    dlTimer = setInterval(function() {
      pct += Math.floor(Math.random() * 12) + 6;
      if (pct >= 100) {
        pct = 100;
        clearInterval(dlTimer);
        setTimeout(after, 350);
      }
      fill.style.width = pct + '%';
      txt.textContent = pct + '%';
    }, 110);
  }
  async function saveSummaryDoc() {
    var period = selectedSummaryPeriod();
    if (!period) { alert('Выберите месяц или неделю для сводной.'); return; }
    var object = $('#sumObject').value;
    var recs = periodRecords(period, object);
    if (!recs.length) {
      alert('За ' + period.label + (object ? ' (объект: ' + object + ')' : '') + ' записей в архиве нет.\nСначала скачайте дневные отчеты или сохраните их в архив.');
      return;
    }
    var html = buildSummaryWord(recs, period.label, object);
    var fname = 'Сводный_отчет_СБ_' + (object ? safeName(object) + '_' : '') + period.key + '.doc';
    var blob = new Blob(['\ufeff' + html], { type: 'application/msword;charset=utf-8' });
    var dirHandle = null;
    if (window.showDirectoryPicker) {
      try {
        dirHandle = await chooseOrReuseFolder();
        if (!dirHandle) return;
      } catch (e) {
        console.error(e);
        alert('Не удалось получить доступ к папке. Сводная будет скачана обычным способом.');
      }
    }
    summaryDlStart(async function() {
      var saved = false;
      if (dirHandle) {
        try {
          var sumHandle = await dirHandle.getDirectoryHandle('Сводные', { create: true });
          var monthHandle = await sumHandle.getDirectoryHandle(period.folder, { create: true });
          var fileHandle = await monthHandle.getFileHandle(fname, { create: true });
          var writable = await fileHandle.createWritable();
          await writable.write(blob);
          await writable.close();
          saved = true;
        } catch (e) {
          console.error(e);
          alert('Не удалось сохранить сводную в папку. Файл будет скачан обычным способом.');
        }
      }
      if (!saved) {
        var url = URL.createObjectURL(blob);
        var a2 = document.createElement('a');
        a2.href = url; a2.download = fname;
        document.body.appendChild(a2);
        a2.click();
        setTimeout(function() { URL.revokeObjectURL(url); a2.remove(); }, 800);
      }
      $('#downloadOverlay').classList.remove('show');
      showToast();
    });
  }
  function testFill() {
    if (!confirm('Заполнить все поля тестовыми данными? Текущие данные будут перезаписаны.')) return;
    var fishes = [
      'Плановые контрольные мероприятия проведены в полном объёме.',
      'Замечаний по результатам проверки нет.',
      'Выявленные нарушения зафиксированы и переданы руководству.',
      'Дополнительные проверки не требовались.',
      'Материалы по проверкам переданы в операционную службу.'
    ];
    function rf() { return fishes[Math.floor(Math.random() * fishes.length)]; }
    function rnd(max) { return Math.floor(Math.random() * (max + 1)); }
    function rsum() { return String(rnd(80) * 1000 + rnd(999)); }
    function rdate() {
      var now = new Date();
      var day = 1 + rnd(27);
      var m = String(now.getMonth() + 1); if (m.length < 2) m = '0' + m;
      return now.getFullYear() + '-' + m + '-' + (day < 10 ? '0' : '') + day;
    }
    function set(id, val) { var el = document.getElementById(id); if (el) el.value = val; }
    var toSel = document.getElementById('b0_to');
    if (toSel && toSel.options.length > 1) toSel.selectedIndex = 1 + rnd(toSel.options.length - 2);
    var fioSel = document.getElementById('b0_fio');
    if (fioSel && fioSel.options.length > 1) fioSel.selectedIndex = 1 + rnd(fioSel.options.length - 2);
    set('b0_date', rdate());
    $$('input[type="number"][data-field]').forEach(function(el) { el.value = String(rnd(40)); });
    ['b1_pos_sum','b1_chek_sum','b1_ret_sum','b1_weight_sum','b3_in_sum','b3_between_sum','b4_dev_sum','b5_sum','b6_q_sum','b6_qty_sum','b6_sg_sum','b7_q_sum','b7_qty_sum','b7_sg_sum','b8_sum','b10_crim_sum','b10_adm_sum','b10_local_sum'].forEach(function(id) { set(id, rsum()); });
    set('b5_positions', String(rnd(500)));
    set('b5_date', rdate());
    set('b5_sz_date', rdate());
    set('b5_copy', 'направлена');
    ['b1_comment','b2_comment','b3_comment','b4_comment','b5_comment','b6_comment','b7_comment','b8_comment','b9_comment','b10_comment','b11_comment'].forEach(function(id) { set(id, rf()); });
    for (var key in DYN) {
      dynData[key] = [];
      for (var i = 0; i < 2; i++) {
        var row = {};
        DYN[key].fields.forEach(function(f) {
          if (f.t === 'date') row[f.k] = rdate();
          else if (f.t === 'number') row[f.k] = String(rnd(50));
          else row[f.k] = 'Т-' + (100 + rnd(899));
        });
        dynData[key].push(row);
      }
      renderDyn(key);
      var sp = document.getElementById(key + '_spoiler');
      if (sp) sp.classList.add('open');
    }
    $$('.card').forEach(function(c) { c.classList.add('open'); });
    B11_POSTS.forEach(function(p) {
      var cb = document.getElementById('b11_post_' + p[0]);
      if (!cb) return;
      var off = Math.random() < 0.3;
      cb.checked = !off;
      cb.value = off ? '1' : '';
      if (off) {
        var fi = document.getElementById('b11_from_' + p[0]);
        var ti = document.getElementById('b11_to_' + p[0]);
        if (fi) fi.value = '09:00';
        if (ti) ti.value = '18:00';
        var dF = document.getElementById('b11_dfrom_' + p[0]);
        var dT = document.getElementById('b11_dto_' + p[0]);
        if (dF && !dF.value) dF.value = v('b0_date') || new Date().toISOString().slice(0, 10);
        if (dT && !dT.value) dT.value = v('b0_date') || new Date().toISOString().slice(0, 10);
      }
    });
    set('b11_uch', Math.random() < 0.6 ? 'ok' : 'none');
    if (v('b11_uch') === 'none') set('b11_uch_fio', 'Иванов Иван Иванович');
    var _tk = ['t1', 't2', 't3', 't4', 't5', 't6', 't7', 't8'][Math.floor(Math.random() * 8)];
    if (Math.random() < 0.2) { set('b11_instr', 'other'); set('b11_instr_topic', 'Взаимодействие с операционной службой'); }
    else set('b11_instr', _tk);
    b11Sync();
    saveAll(); saveDyn(); updateProgress();
    if ($('#previewSection').classList.contains('active')) buildPreview();
  }

  // ---------- Навигация-оглавление (v1.87) ----------
  var NAV_ITEMS = [
    ['card0', 'Общая информация'],
    ['card1', '1. Кассовые операции'],
    ['card2', '2. FaceID'],
    ['card3', '3. Перемещения ТМЦ'],
    ['card4', '4. Инвентаризации'],
    ['card5', '5. Виртуальные остатки'],
    ['card6', '6. Поставки с РЦ'],
    ['card7', '7. Прямые поставщики'],
    ['card8', '8. Списания ТМЦ'],
    ['card9', '9. Записки на ДГМ'],
    ['card10', '10. Хищения'],
    ['card11', '11. ЧОП «Артакс+»']
  ];
  function buildNav() {
    var nav = $('#appNav');
    if (!nav) return;
    var html = '';
    for (var i = 0; i < NAV_ITEMS.length; i++) {
      html += '<button type="button" class="app-nav-item" data-card="' + NAV_ITEMS[i][0] + '"><span class="nav-check">\u2713</span><span>' + esc(NAV_ITEMS[i][1]) + '</span></button>';
    }
    nav.innerHTML = html;
    $$('.app-nav-item').forEach(function(b) {
      b.addEventListener('click', function() {
        var card = document.getElementById(b.getAttribute('data-card'));
        if (!card) return;
        if (!card.classList.contains('open')) card.classList.add('open');
        scrollToCard(card);
        focusCards(card);
        nav.classList.remove('mobile-open');
      });
    });
    refreshNav();
  }
  function scrollToCard(card) {
    var hdr = document.querySelector('.app-header');
    var off = (hdr ? hdr.offsetHeight : 0) + 6;
    var top = card.getBoundingClientRect().top + window.pageYOffset - off;
    window.scrollTo({ top: top, behavior: 'smooth' });
    // Досчёт после завершения анимации раскрытия карточки (0.5s)
    clearTimeout(card.__scrT);
    card.__scrT = setTimeout(function() {
      var t2 = card.getBoundingClientRect().top + window.pageYOffset - off;
      if (Math.abs(t2 - window.pageYOffset) > 2) window.scrollTo({ top: t2, behavior: 'smooth' });
    }, 560);
  }
  function focusCards(active) {
    var all = document.querySelectorAll('.card');
    for (var i = 0; i < all.length; i++) {
      all[i].classList.remove('nav-focus');
      if (all[i] === active) all[i].classList.add('nav-focus');
    }
    clearTimeout(focusCards._t);
    focusCards._t = setTimeout(function() {
      var cs = document.querySelectorAll('.card');
      for (var j = 0; j < cs.length; j++) cs[j].classList.remove('nav-focus');
    }, 1800);
  }
  function refreshNav() {
    if (!$('#appNav')) return;
    var navItems = getProgressCache().nav;
    for (var n = 0; n < navItems.length; n++) {
      var item = navItems[n], fs = [], d2 = 0;
      for (var i0 = 0; i0 < item.fields.length; i0++) if (fieldVisible(item.fields[i0])) fs.push(item.fields[i0]);
      for (var i = 0; i < fs.length; i++) if (String(fs[i].value).trim() !== '') d2++;
      item.btn.classList.toggle('done', fs.length > 0 && d2 === fs.length);
    }
  }
  function initNav() {
    buildNav();
    var nav = $('#appNav');
    $('#navToggle').addEventListener('click', function() { nav.classList.toggle('mobile-open'); });
    // Активный пункт навигации обновляется общим обработчиком прокрутки ниже.
  }

  // ---------- Инициализация ----------
  async function init() {
    await serverStore.init();
    var savedDraft = {};
    var savedDynDraft = {};
    try { savedDraft = JSON.parse(serverStore.getItem(KEY) || '{}') || {}; } catch (e) {}
    try { savedDynDraft = JSON.parse(serverStore.getItem(KEY + '__dyn') || '{}') || {}; } catch (e) {}
    var hasDraft = Object.keys(savedDraft).some(function(k) { return String(savedDraft[k] == null ? '' : savedDraft[k]).trim() !== ''; }) || Object.keys(savedDynDraft).some(function(k) { return Array.isArray(savedDynDraft[k]) && savedDynDraft[k].length > 0; });
    if (hasDraft) {
      var resumeDraft = confirm('Найден незавершённый черновик отчёта, сохранённый ранее в этом браузере.\n\nПродолжить заполнение черновика?\n\nНажмите «Отмена», чтобы удалить черновик и начать новый отчёт.');
      if (!resumeDraft) {
        await serverStore.removeItem(KEY);
        await serverStore.removeItem(KEY + '__dyn');
      } else {
        validationTouched = true;
      }
    }
    loadAll();
    loadDyn();
    initLists();
    b11Sync();
    $$('#card11 input[type=checkbox]').forEach(function(cb) {
      cb.addEventListener('change', function() { cb.value = cb.checked ? '' : '1'; b11Sync(); });
    });
    $('#b11_uch').addEventListener('change', b11Sync);
    $('#b11_instr').addEventListener('change', b11Sync);

    $$('.card-header').forEach(function(h) {
      h.addEventListener('click', function() {
        h.parentElement.classList.toggle('open');
      });
    });

    $$('[data-field]').forEach(function(el) {
      el.addEventListener('input', function() {
        updateProgress();
        validationTouched = true;
        updateValidationPanel();
        clearTimeout(window._saveT);
        window._saveT = setTimeout(saveAll, 700);
      });
      el.addEventListener('change', function() {
        updateProgress();
        validationTouched = true;
        updateValidationPanel();
        clearTimeout(window._saveT);
        window._saveT = setTimeout(saveAll, 250);
      });
    });

    $$('.dyn-head').forEach(function(h) {
      h.addEventListener('click', function() {
        var sp = h.parentElement;
        sp.classList.toggle('open');
      });
    });
    $$('[data-add]').forEach(function(b) {
      b.addEventListener('click', function() { addDyn(b.getAttribute('data-add')); });
    });

    if ($('#btnPreview')) $('#btnPreview').addEventListener('click', showPreview);
    $('#btnPreview2').addEventListener('click', showPreview);
    $('#btnReset').addEventListener('click', function() {
      if (confirm('Сбросить все данные отчета?')) {
        resetAll();
        serverStore.flush().then(function() { window.location.reload(); });
      }
    });
    if ($('#btnDownload')) $('#btnDownload').addEventListener('click', openSummary);
    $('#btnDownload2').addEventListener('click', openSummary);
    $('#btnChangeFolder2').addEventListener('click', changeSaveFolder);
    $('#summaryCancel').addEventListener('click', closeSummary);
    $('#summaryConfirm').addEventListener('click', startDownload);
    $('#summaryOverlay').addEventListener('click', function(e) {
      if (e.target === this) closeSummary();
    });

    $('#btnTest').addEventListener('click', testFill);

    $$('.list-edit-btn').forEach(function(b) {
      b.addEventListener('click', function() { openListEditor(b.getAttribute('data-list')); });
    });
    $('#listAddBtn').addEventListener('click', addListItem);
    $('#listNewValue').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') { e.preventDefault(); addListItem(); }
    });
    $('#listClose').addEventListener('click', function() { $('#listOverlay').classList.remove('show'); });
    $('#listOverlay').addEventListener('click', function(e) {
      if (e.target === this) this.classList.remove('show');
    });

    initNav();
    var scrollFrame = 0;
    window.addEventListener('scroll', function() {
      if (scrollFrame) return;
      scrollFrame = requestAnimationFrame(function() {
        scrollFrame = 0;
        var btn = $('#scrollTopBtn');
        btn.classList.toggle('show', window.pageYOffset > 400);
        var cur = -1;
        for (var i = 0; i < NAV_ITEMS.length; i++) {
          var card = document.getElementById(NAV_ITEMS[i][0]);
          if (card && card.getBoundingClientRect().top <= 140) cur = i;
        }
        var navItems = getProgressCache().nav;
        for (var j = 0; j < navItems.length; j++) navItems[j].btn.classList.toggle('active', j === cur);
      });
    }, { passive: true });
    $('#scrollTopBtn').addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    updateProgress();
    if (validationTouched) updateValidationPanel();
  }


  // ================= v1.96: контроль дублей + Excel + дашборд =================
  var VERSION = '1.3.20'; // Добавлен блок 11: работа ООО ЧОП «Артакс+» (посты, УЧО, инструктаж) нарушений
  // v1.1.1: сравнение версий по компонентам; записи старой схемы (1.99, 1.101 и т.п.) всегда «старые»
  function verParts(s2) { return String(s2 || '').trim().split('.'); }
  function cmpParts(a, b) { for (var i = 0; i < Math.max(a.length, b.length); i++) { var x = parseInt(a[i] || '0', 10) || 0, y = parseInt(b[i] || '0', 10) || 0; if (x !== y) return x < y ? -1 : 1; } return 0; }
  function verIsOld(v) {
    var s2 = String((v == null ? '' : v)).trim();
    if (!s2) return true;
    if (s2 === VERSION) return false;
    if (!/^\d+(\.\d+)*$/.test(s2)) return true;
    if (verParts(s2).length < 3) return true; // схема 1.99 / 1.101 — до перехода на 1.x.y
    return cmpParts(verParts(s2), verParts(VERSION)) < 0;
  }
  var HIST_MAX = 5;

  // ---------- Дубли ----------
  function snapKeyOf(snap) {
    var dd = (snap && snap.data) || {};
    return String(dd.b0_date || '') + '|' + String(dd.b0_to || '');
  }
  function findDup(date, to) {
    var a = getArchive();
    var key = String(date || '') + '|' + String(to || '');
    for (var i = 0; i < a.length; i++) if (snapKeyOf(a[i]) === key) return a[i];
    return null;
  }

  function openHistory(idx) {
    var a = getArchive();
    var it = a[idx];
    if (!it || !it.hist || !it.hist.length) return;
    var ov = document.createElement('div');
    ov.className = 'hist-overlay';
    var card = document.createElement('div');
    card.className = 'hist-card';
    var h = '<div class="hist-title">История версий</div>' +
      '<div class="hist-sub">' + esc(it.label) + '<br>Перезаписанные версии отчета. «Восстановить» загрузит данные версии в форму.</div>';
    it.hist.forEach(function(hv, hi) {
      var when = '';
      try { when = new Date(hv.t).toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit' }); } catch (e0) {}
      h += '<div class="hist-item"><div class="hist-item-info"><span class="hist-when">' + esc(when) + '</span>' +
        '<span class="hist-fio">Составитель: ' + esc(hv.fio || '—') + ' · заполнено ' + hv.done + ' из ' + hv.total + '</span></div>' +
        '<button type="button" class="mini-btn hist-restore" data-hi="' + hi + '">Восстановить</button></div>';
    });
    h += '<div class="hist-actions"><button type="button" class="btn btn-outline" id="histClose">Закрыть</button></div>';
    card.innerHTML = h;
    ov.appendChild(card);
    document.body.appendChild(ov);
    requestAnimationFrame(function() { ov.classList.add('show'); });
    function closeHist() { ov.classList.remove('show'); setTimeout(function() { ov.remove(); }, 250); }
    card.querySelector('#histClose').addEventListener('click', closeHist);
    ov.addEventListener('click', function(e) { if (e.target === ov) closeHist(); });
    card.addEventListener('click', function(e) {
      var btn = e.target.closest ? e.target.closest('.hist-restore') : null;
      if (!btn) return;
      var hv = it.hist[parseInt(btn.getAttribute('data-hi'), 10)];
      if (!hv) return;
      $$('[data-field]').forEach(function(el) {
        el.value = (hv.data && hv.data[el.id] !== undefined) ? hv.data[el.id] : '';
      });
      dynData = JSON.parse(JSON.stringify(hv.dyn || { b4_inv: [], b6_sup: [], b7_sup: [], b8_sp: [] }));
      Object.keys(DYN).forEach(function(k) { renderDyn(k); });
      saveAll();
      saveDynNow();
      updateProgress();
      buildPreview();
      closeHist();
      showToast();
    });
  }

  // ---------- Генератор XLSX (без внешних библиотек, полностью офлайн) ----------
  function crc32(data) {
    var T = window.__sbCrc32T;
    if (!T) {
      T = [];
      for (var n = 0; n < 256; n++) {
        var c = n;
        for (var k = 0; k < 8; k++) c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
        T[n] = c >>> 0;
      }
      window.__sbCrc32T = T;
    }
    var crc = 0 ^ (-1);
    for (var i = 0; i < data.length; i++) crc = (crc >>> 8) ^ T[(crc ^ data[i]) & 0xFF];
    return (crc ^ (-1)) >>> 0;
  }
  function utf8Bytes(s) {
    if (window.TextEncoder) return new TextEncoder().encode(s);
    var out = [];
    unescape(encodeURIComponent(s)).split('').forEach(function(ch) { out.push(ch.charCodeAt(0)); });
    return new Uint8Array(out);
  }
  function xlEsc(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  // sheets: [{name, rows}] — значение ячейки: число => numeric, иначе текст
  function buildXlsx(sheets) {
    return window.SBDocument.buildXlsx(sheets);
  }

  function xNameFor(data) { return wordFnameFor(data).replace(/\.doc$/, '.xlsx'); }
  async function saveBlobSmart(blob, fname, dirs) {
    var dirHandle = null;
    if (window.showDirectoryPicker) {
      try { dirHandle = await chooseOrReuseFolder(); } catch (e0) { dirHandle = null; }
    }
    if (!dirHandle) {
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url; a.download = fname;
      document.body.appendChild(a); a.click();
      setTimeout(function() { URL.revokeObjectURL(url); a.remove(); }, 800);
      return;
    }
    try {
      var cur = dirHandle;
      for (var i = 0; i < dirs.length; i++) cur = await cur.getDirectoryHandle(dirs[i], { create: true });
      var fh = await cur.getFileHandle(fname, { create: true });
      var w = await fh.createWritable();
      await w.write(blob);
      await w.close();
    } catch (e) {
      console.error(e);
      var url2 = URL.createObjectURL(blob);
      var a2 = document.createElement('a');
      a2.href = url2; a2.download = fname;
      document.body.appendChild(a2); a2.click();
      setTimeout(function() { URL.revokeObjectURL(url2); a2.remove(); }, 800);
    }
  }

  function sumSpecRows(data, dyn) {
    function n1(list) {
      var s = 0, any = false;
      for (var i = 0; i < list.length; i++) {
        var x = parseFloat(String((data && data[list[i]]) || '').replace(',', '.'));
        if (!isNaN(x) && x !== 0) { s += x; any = true; }
      }
      return any ? s : null;
    }
    function nd(dynKey, dynField) {
      var s = 0, any = false;
      var rows = (dyn && dyn[dynKey]) || [];
      for (var j = 0; j < rows.length; j++) {
        var y = parseFloat(String(rows[j][dynField] || '').replace(',', '.'));
        if (!isNaN(y) && y !== 0) { s += y; any = true; }
      }
      return any ? s : null;
    }
    var spec = [
      ['1. Кассовые операции', ['b1_pos_qty', 'b1_chek_qty', 'b1_ret_qty', 'b1_weight_qty'], ['b1_pos_sum', 'b1_chek_sum', 'b1_ret_sum', 'b1_weight_sum'], null],
      ['3. Перемещения ТМЦ', ['b3_in_qty', 'b3_between_qty'], ['b3_in_sum', 'b3_between_sum'], null],
      ['4. Инвентаризации', ['b4_dev_qty'], ['b4_dev_sum'], ['b4_inv', 'sum']],
      ['6. Поставки с РЦ', ['b6_q_qty', 'b6_qty_qty', 'b6_sg_qty'], ['b6_q_sum', 'b6_qty_sum', 'b6_sg_sum'], ['b6_sup', 'dev']],
      ['7. Прямые поставщики', ['b7_q_qty', 'b7_qty_qty', 'b7_sg_qty'], ['b7_q_sum', 'b7_qty_sum', 'b7_sg_sum'], ['b7_sup', 'dev']],
      ['8. Списания ТМЦ', ['b8_qty'], ['b8_sum'], ['b8_sp', 'dev']],
      ['10. Хищения', ['b10_crim_qty', 'b10_adm_qty', 'b10_local_qty'], ['b10_crim_sum', 'b10_adm_sum', 'b10_local_sum'], null]
    ];
    var out = [], tq = 0, ts = 0;
    spec.forEach(function(sp) {
      var q = n1(sp[1]);
      var sm = n1(sp[2]);
      if (sp[3]) { var dy = nd(sp[3][0], sp[3][1]); if (dy !== null) sm = (sm === null ? 0 : sm) + dy; }
      if (q === null && sm === null) return;
      out.push([sp[0], (q === null ? '' : q), (sm === null ? '' : sm)]);
      tq += (q || 0); ts += (sm || 0);
    });
    return { rows: out, tq: tq, ts: ts, any: out.length > 0 };
  }

  async function exportSummaryExcel() {
    var period = selectedSummaryPeriod();
    if (!period) { alert('Выберите месяц или неделю для сводной.'); return; }
    var object = $('#sumObject').value;
    var recs = periodRecords(period, object);
    if (!recs.length) {
      alert('За ' + period.label + (object ? ' (объект: ' + object + ')' : '') + ' записей в архиве нет.');
      return;
    }
    function cn(nn) { var s2 = ''; nn++; while (nn) { var md = (nn - 1) % 26; s2 = String.fromCharCode(65 + md) + s2; nn = Math.floor((nn - md - 1) / 26); } return s2; }

    var flat = sumDetailFlat();
    var detail = sumDetailData(recs);
    // v1.1.5: detailTot (мёртвый код) убран
    // === Лист «Сводка» — детально по показателям ===
    var colsSum = [64, 13, 13, 26, 15];
    var rowsSum = [
      [{ v: 'Сводный отчет СБ за ' + period.label + (object ? ' — ' + object : ''), s: 1 }],
      []
    ];
    rowsSum.push([{ v: 'Показатель', s: 3 }, { v: 'Проверено', s: 3 }, { v: 'Выявлено, шт.', s: 3 }, { v: 'Сумма нарушений и расхождений, руб.', s: 3 }, { v: 'Справочно', s: 3 }]);
    // v1.1.5: роли P/V/R/S/C; итоги — только проверки, нарушения и суммы расхождений
    var merges = ['A1:E1'];
    function sumCol(it) {
      return {
        p: (it.k === 'P' || it.k === 'M') ? it.q : null,
        v: it.k === 'V' ? it.q : (it.k === 'M' ? (it.v || 0) : null),
        s: (it.k === 'V' || it.k === 'M' || it.k === 'R') ? it.s : null,
        r: (it.k === 'S' || it.k === 'C') ? (it.q !== null ? it.q : it.s) : null
      };
    }
    var TT = { p: 0, v: 0, s: 0, has: false };
    for (var i = 0; i < detail.length; i++) {
      var rn = rowsSum.length + 1;
      merges.push('A' + rn + ':E' + rn);
      rowsSum.push([{ v: detail[i].t, s: 10 }]);
      var ra = { p: 0, v: 0, s: 0, has: false };
      for (var j = 0; j < detail[i].items.length; j++) {
        var c2 = sumCol(detail[i].items[j]);
        rowsSum.push([
          { v: '   ' + detail[i].items[j].t, s: 4 },
          c2.p !== null ? { v: c2.p, s: 5 } : { v: '', s: 4 },
          c2.v !== null ? { v: c2.v, s: 5 } : { v: '', s: 4 },
          c2.s !== null ? { v: c2.s, s: 6 } : { v: '', s: 4 },
          c2.r !== null ? { v: c2.r, s: (detail[i].items[j].k === 'S' && detail[i].items[j].q === null) ? 6 : 5 } : { v: '', s: 4 }
        ]);
        if (c2.p !== null) { ra.p += c2.p; ra.has = true; }
        if (c2.v !== null) { ra.v += c2.v; ra.has = true; }
        if (c2.s !== null) { ra.s += c2.s; ra.has = true; }
      }
      if (ra.has) {
        rowsSum.push([
          { v: 'Итого по разделу', s: 7 },
          { v: ra.p, s: 8 }, { v: ra.v, s: 8 }, { v: ra.s, s: 9 },
          { v: '', s: 7 }
        ]);
        TT.p += ra.p; TT.v += ra.v; TT.s += ra.s; TT.has = true;
      }
    }
    rowsSum.push([
      { v: 'ИТОГО', s: 7 },
      TT.has ? { v: TT.p, s: 8 } : { v: '', s: 7 },
      TT.has ? { v: TT.v, s: 8 } : { v: '', s: 7 },
      TT.has ? { v: TT.s, s: 9 } : { v: '', s: 7 },
      { v: '', s: 7 }
    ]);
    rowsSum.push([{ v: 'Каждое выявленное нарушение учитывается отдельно; одна проверка может дать несколько нарушений. Справочные показатели приведены отдельно и в итоговые суммы не включаются.', s: 2 }]);

    // === Лист «По дням» — все данные каждого дневного отчета ===
var colIdx = [];
    // v1.1.5: P→«Проверено», V→«Выявлено, шт.»+«Сумма», R→«Сумма, руб.», S/C→«Справочно»
    var ci = 3;
    flat.forEach(function(f) {
      // v1.1.5: P→«Проверено», V→«Выявлено»+сумма, R→только сумма, S/C→справочно
      var e = { f: f, q: null, s: null, span: 0 };
      if (f.k === 'V' || f.k === 'S') { if (f.q) { e.q = ci; ci++; } }
      else if (f.q) { e.q = ci; ci++; }
      if (f.s) { e.s = ci; ci++; }
      e.span = (e.q !== null ? 1 : 0) + (e.s !== null ? 1 : 0);
      colIdx.push(e);
    });
    var nCols = ci + 15;
    var colsDaily = [12, 22, 22];
    colIdx.forEach(function(e) {
      if (e.q !== null) colsDaily.push(e.f.k === 'P' ? 15 : 17);
      if (e.s !== null) colsDaily.push(17);
    });
    colsDaily.push(14, 16, 20);
    colsDaily.push(34);
    for (var cb2 = 0; cb2 < 11; cb2++) colsDaily.push(30);

    var mergesDaily = ['A1:' + cn(nCols - 1) + '1'];
    var row1 = [{ v: 'Ежедневный отчет СБ — детально за ' + period.label + (object ? ' — ' + object : ''), s: 1 }];
    var row2 = [], row3 = [], row4 = [];
    for (var z = 0; z < nCols; z++) { row2.push(null); row3.push(null); row4.push(null); }
    row2[0] = { v: 'Дата', s: 3 }; row2[1] = { v: 'Объект', s: 3 }; row2[2] = { v: 'Составитель', s: 3 };
    for (var z2 = 0; z2 < 3; z2++) mergesDaily.push(cn(z2) + '2:' + cn(z2) + '4');
    // Строка 2 — названия разделов, объединённые по колонкам раздела
    var k2 = 0;
    while (k2 < flat.length) {
      var secT = flat[k2].sec;
      var e0 = colIdx[k2];
      var c0 = e0.q !== null ? e0.q : e0.s;
      row2[c0] = { v: secT, s: 3 };
      var cEnd = c0;
      var k3 = k2;
      while (k3 < flat.length && flat[k3].sec === secT) {
        var eS = colIdx[k3];
        cEnd = Math.max(cEnd, eS.q !== null ? eS.q : eS.s);
        k3++;
      }
      if (cEnd > c0) mergesDaily.push(cn(c0) + '2:' + cn(cEnd) + '2');
      k2 = k3;
    }
    // Строка 3 — названия показателей (у парных объединены), строка 4 — тип колонки
    function typeLabel(kk) {
      if (kk === 'P') return 'Проверено';
      if (kk === 'V') return 'Выявлено, шт.';
      return 'Справочно';
    }
    colIdx.forEach(function(e) {
      if (e.span > 1) mergesDaily.push(cn(e.q) + '3:' + cn(e.s) + '3');
      if (e.q !== null) { row3[e.q] = { v: e.f.t, s: 3 }; row4[e.q] = { v: typeLabel(e.f.k === 'M' ? 'P' : e.f.k), s: 3 }; }
      if (e.s !== null) { if (e.q === null) row3[e.s] = { v: e.f.t, s: 3 }; row4[e.s] = { v: 'Сумма, руб.', s: 3 }; }
    });
    row2[ci] = { v: 'Дата выгрузки', s: 3 };
    row2[ci + 1] = { v: 'СЗ на ДГМ (дата)', s: 3 };
    row2[ci + 2] = { v: 'Копия Директору Группы', s: 3 };
    row2[ci + 3] = { v: 'ЧОП «Артакс+»', s: 3 };
    for (var cb3 = 0; cb3 < 11; cb3++) row2[ci + 4 + cb3] = { v: 'Комментарии (р. ' + (cb3 + 1) + ')', s: 3 };
    for (var z3 = ci; z3 < nCols; z3++) mergesDaily.push(cn(z3) + '2:' + cn(z3) + '4');
    var rowsDaily = [];
    rowsDaily.push(row1, row2, row3, row4);

    var colTot = {}, colAny = {};
    colIdx.forEach(function(e) { if (e.q !== null) colTot[e.q] = 0; if (e.s !== null) colTot[e.s] = 0; });
    recs.forEach(function(it) {
      var dr = [];
      for (var z4 = 0; z4 < nCols; z4++) dr.push(null);
      dr[0] = { v: it.data && it.data.b0_date ? fdate(it.data.b0_date) : '', s: 4 };
      dr[1] = { v: (it.data && it.data.b0_to) || '', s: 4 };
      dr[2] = { v: it.fio || '', s: 4 };
      colIdx.forEach(function(e) {
        var cq = e.q !== null ? sumDetailAgg(e.f.q, it) : null;
        var cs = e.s !== null ? sumDetailAgg(e.f.s, it) : null;
        if (cq !== null) { dr[e.q] = { v: cq, s: 5 }; colTot[e.q] += cq; colAny[e.q] = true; }
        if (cs !== null) { dr[e.s] = { v: cs, s: 6 }; colTot[e.s] += cs; colAny[e.s] = true; }
      });
      dr[ci] = { v: it.data && it.data.b5_date ? fdate(it.data.b5_date) : '', s: 4 };
      dr[ci + 1] = { v: it.data && it.data.b5_sz_date ? fdate(it.data.b5_sz_date) : '', s: 4 };
      dr[ci + 2] = { v: (it.data && it.data.b5_copy) || '', s: 4 };
      dr[ci + 3] = { v: chopDayText(it.data || {}), s: 4 };
      for (var cb4 = 0; cb4 < 11; cb4++) dr[ci + 4 + cb4] = { v: (it.data && it.data['b' + (cb4 + 1) + '_comment']) || '', s: 4 };
      rowsDaily.push(dr);
    });

    var tr = [];
    for (var z5 = 0; z5 < nCols; z5++) tr.push(null);
    tr[0] = { v: 'ИТОГО', s: 7 };
    colIdx.forEach(function(e) {
      if (e.q !== null && colAny[e.q]) tr[e.q] = { v: colTot[e.q], s: 8 };
      if (e.s !== null && colAny[e.s]) tr[e.s] = { v: colTot[e.s], s: 9 };
    });
    rowsDaily.push(tr);

    var blob = buildXlsx([
      { name: 'Сводка', rows: rowsSum, opts: { cols: colsSum, merge: merges } },
      { name: 'По дням', rows: rowsDaily, opts: { cols: colsDaily, merge: mergesDaily, freeze: 4, freezeX: 4 } }
    ]);
    var fname = 'Сводный_отчет_СБ_' + (object ? safeName(object) + '_' : '') + period.key + '.xlsx';
    // v1.1.3: сохранение сводной Excel через окно «Формирую отчет» (как в сводном Word)
    // v1.1.7: закрытие окна загрузки после сохранения + перехват ошибок (как в сводном Word)
    summaryDlStart(async function() {
      try {
        await saveBlobSmart(blob, fname, ['Сводные', period.folder]);
        showToast();
      } catch (eX) {
        console.error('Ошибка сохранения сводной Excel:', eX);
        showToast();
      } finally {
        $('#downloadOverlay').classList.remove('show');
      }
    });
  }

  function initPatch() {
    var x4 = document.getElementById('btnSumExcel');
    if (x4) x4.addEventListener('click', exportSummaryExcel);
  }

  // v1.3.9: денежные поля используют тот же стандартный вид input[type="number"], что и поля количества.
  // Отдельная визуальная обёртка для сумм отключена; автоматический подбор высоты комментариев сохранён.
  function initPracticalUX() {
    function resizeComment(el) {
      if (!el || el.tagName !== 'TEXTAREA') return;
      el.style.height = 'auto';
      el.style.height = Math.max(72, el.scrollHeight) + 'px';
    }

    document.querySelectorAll('textarea[data-field]').forEach(function(el) {
      resizeComment(el);
      el.addEventListener('input', function() { resizeComment(el); });
    });

    // Поддержка комментариев, добавляемых динамически.
    var observerRoot = document.querySelector('.container') || document.body;
    if (window.MutationObserver && observerRoot) {
      var observer = new MutationObserver(function(records) {
        records.forEach(function(record) {
          record.addedNodes.forEach(function(node) {
            if (!node || node.nodeType !== 1) return;
            if (node.matches && node.matches('textarea[data-field]')) {
              resizeComment(node);
              node.addEventListener('input', function() { resizeComment(node); });
            }
            if (node.querySelectorAll) {
              node.querySelectorAll('textarea[data-field]').forEach(function(el) {
                resizeComment(el);
                el.addEventListener('input', function() { resizeComment(el); });
              });
            }
          });
        });
      });
      observer.observe(observerRoot, { childList: true, subtree: true });
    }

    requestAnimationFrame(function() {
      document.querySelectorAll('textarea[data-field]').forEach(resizeComment);
    });
  }

  // v1.3.8: колесо мыши прокручивает страницу, но не меняет число в активном поле.
  document.addEventListener('wheel', function(event) {
    var target = event.target;
    if (target && target.matches && target.matches('input[type="number"]') && document.activeElement === target) {
      target.blur();
    }
  }, { capture: true, passive: true });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPatch);
  else initPatch();

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPracticalUX);
  else initPracticalUX();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      init().catch(function(error) {
        console.error('Не удалось запустить приложение:', error);
        alert('Не удалось подключиться к серверному хранилищу. ' + (error.message || 'Проверьте настройки api.php и базы данных.'));
      });
    });
  } else {
    init().catch(function(error) {
      console.error('Не удалось запустить приложение:', error);
      alert('Не удалось подключиться к серверному хранилищу. ' + (error.message || 'Проверьте настройки api.php и базы данных.'));
    });
  }

  // ================= АВТОРИЗАЦИЯ И РОЛИ (sb-auth) =================
  window.__SB_USER = window.__SB_USER || null;
  window.__SB_CSRF = window.__SB_CSRF || null;

  function sbFetch(action, body) {
    var options = { method: body ? 'POST' : 'GET', credentials: 'same-origin',
      cache: 'no-store', headers: { 'Accept': 'application/json' } };
    if (body) {
      if (window.__SB_CSRF && !body.csrf) body = Object.assign({}, body, { csrf: window.__SB_CSRF });
      if (action === 'login') {
        // Обычная form-urlencoded отправка вместо application/json.
        options.headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
        options.body = new URLSearchParams(body).toString();
      } else {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
      }
    }
    return fetch('../api.php?action=' + encodeURIComponent(action), options)
      .then(function(r) {
        return r.text().then(function(raw) {
          var result;
          try { result = JSON.parse(raw); }
          catch (_) {
            if (/<html|<!doctype html/i.test(raw)) {
              throw new Error('Хостинг вернул HTML-проверку вместо ответа сервера. Обновите страницу и попробуйте ещё раз.');
            }
            throw new Error('Сервер вернул некорректный ответ (HTTP ' + r.status + ').');
          }
          if (!r.ok && !result.error) result.error = 'Ошибка сервера (HTTP ' + r.status + ').';
          return result;
        });
      })
      .then(function(result) {
        if (result && result.csrf) window.__SB_CSRF = result.csrf;
        return result;
      });
  }

  function roleLabel(role) {
    return role === 'admin' ? 'Администратор' : (role === 'moderator' ? 'Модератор' : 'Пользователь');
  }

  function showLoginOverlay() { var ov = $('#loginOverlay'); if (ov) ov.classList.add('show'); }

  function sbWaitForLogin() {
    showLoginOverlay();
    return new Promise(function(resolve) { window.__sbLoginDone = resolve; });
  }

  function applyRole(user, objects, employees) {
    window.__SB_USER = user;
    if (!user) return;
    var assignedNames = Array.isArray(user.object_names) ? user.object_names : (user.object_name ? [user.object_name] : []);
    var assignedIds = Array.isArray(user.object_ids) ? user.object_ids.map(Number) : (user.object_id ? [Number(user.object_id)] : []);
    var chip = $('#userChip');
    if (chip) {
      chip.style.display = 'flex';
      var cn = chip.querySelector('.user-chip-name');
      var cr = chip.querySelector('.user-chip-role');
      if (cn) cn.textContent = user.full_name || user.username;
      if (cr) cr.textContent = roleLabel(user.role) + (assignedNames.length ? ' · ' + assignedNames.join(', ') : '');
    }
    var btnEmployees = $('#btnEmployees');
    if (btnEmployees) btnEmployees.style.display = '';
    var btnTasks = $('#btnTasks');
    if (btnTasks) btnTasks.style.display = '';
    var btnNotifications = $('#btnNotifications');
    if (btnNotifications) {
      btnNotifications.style.display = '';
      sbFetch('notifications_data').then(function(data) {
        if (data && data.ok) {
          var badge = $('#notificationBadge');
          var unreadCount = Number(data.unread) || 0;
          if (badge) {
            badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            badge.style.display = unreadCount > 0 ? 'block' : 'none';
          }
          btnNotifications.setAttribute('aria-label', 'Уведомления' + (unreadCount ? ': ' + unreadCount + ' непрочитанных' : ''));
        }
      }).catch(function() {});
    }
    var btnHeaderManage = $('#btnHeaderManage');
    if (btnHeaderManage) btnHeaderManage.style.display = (user.role === 'admin') ? '' : 'none';
    var btnUsers = $('#btnUsers');
    if (btnUsers) btnUsers.style.display = (user.role === 'admin') ? '' : 'none';
    var btnAddUser = $('#btnAddUser');
    if (btnAddUser) btnAddUser.style.display = (user.role === 'admin' || user.role === 'moderator') ? '' : 'none';

    var selTo = $('#b0_to');
    if (selTo) {
      var currentObject = selTo.value;
      selTo.innerHTML = '<option value="">Выберите объект</option>';
      (objects || []).forEach(function(obj) {
        if (Number(obj.is_active) === 1) selTo.appendChild(new Option(obj.name, obj.name));
      });
      if (currentObject && Array.from(selTo.options).some(function(option) { return option.value === currentObject; })) {
        selTo.value = currentObject;
      } else if (assignedNames.length && user.role !== 'admin') {
        selTo.value = assignedNames[0];
      }
      if (user.role !== 'admin') {
        selTo.disabled = assignedNames.length <= 1;
      } else {
        selTo.disabled = false;
      }
      selTo.classList.toggle('empty', !selTo.value);
    }

    var selFio = $('#b0_fio');
    if (selFio) {
      function refreshEmployeeOptions() {
        var previous = selFio.value;
        selFio.innerHTML = '<option value="">Выберите сотрудника</option>';
        if (user.role === 'user' && user.full_name) {
          selFio.appendChild(new Option(user.full_name, user.full_name));
          selFio.value = user.full_name;
          selFio.disabled = true;
        } else {
          var selectedObject = selTo ? selTo.value : '';
          var selectedObjectId = null;
          if (selectedObject && objects) {
            var obj = objects.find(function(item) { return item.name === selectedObject; });
            if (obj) selectedObjectId = Number(obj.id);
          }
          var seen = Object.create(null);
          (employees || []).forEach(function(employee) {
            if (!employee.full_name) return;
            if (selectedObjectId !== null && Number(employee.object_id) !== selectedObjectId) return;
            if (selectedObjectId === null) return;
            if (seen[employee.full_name]) return;
            seen[employee.full_name] = true;
            selFio.appendChild(new Option(employee.full_name, employee.full_name));
          });
          selFio.disabled = false;
          if (previous && Array.from(selFio.options).some(function(option) { return option.value === previous; })) {
            selFio.value = previous;
          }
        }
        selFio.classList.toggle('empty', !selFio.value);
      }
      refreshEmployeeOptions();
      if (selTo) selTo.onchange = refreshEmployeeOptions;
    }
  }

  function applyViewMode(viewOnly, record) {
    var banner = $('#viewOnlyBanner');
    if (banner) {
      if (viewOnly) {
        var who = (record && (record.owner_name || record.fio)) ? ' — составитель: ' + (record.owner_name || record.fio) : '';
        banner.textContent = 'Режим просмотра: отчёт принадлежит другому сотруднику' + who + '. Редактирование запрещено.';
        banner.style.display = 'block';
      } else {
        banner.style.display = 'none';
      }
    }
    var u = window.__SB_USER;
    $$('[data-field]').forEach(function(el) {
      if (viewOnly) { el.disabled = true; return; }
      var isFioLock = el.id === 'b0_fio' && u && u.role === 'user' && u.full_name;
      var assignedCount = u && Array.isArray(u.object_ids) ? u.object_ids.length : (u && u.object_id ? 1 : 0);
      var isToLock = el.id === 'b0_to' && u && u.role !== 'admin' && assignedCount <= 1;
      if (!isFioLock && !isToLock) el.disabled = false;
    });
    var vis = viewOnly ? 'none' : '';
    ['btnDownload', 'btnDownload2', 'btnReset', 'btnPreview', 'btnPreview2', 'btnTest'].forEach(function(id) {
      var b = document.getElementById(id);
      if (b) b.style.display = vis;
    });
    $$('.dyn-add').forEach(function(b) { b.style.display = vis; });
  }

  (function bindAuthUI() {
    function bind() {
    var form = $('#loginForm');
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = $('#loginSubmit');
        var msg = $('#loginError');
        if (msg) { msg.style.display = 'none'; msg.textContent = ''; }
        btn.disabled = true;
        btn.textContent = 'Проверяем...';
        sbFetch('status')
          .then(function() {
            return sbFetch('login', { username: $('#loginUser').value.trim(), password: $('#loginPass').value });
          })
          .then(function(result) {
            if (result && result.ok) { window.location.reload(); return; }
            throw new Error((result && result.error) || 'Не удалось войти.');
          })
          .catch(function(err) {
            if (msg) { msg.textContent = (err && err.message) || 'Ошибка входа.'; msg.style.display = 'block'; }
            btn.disabled = false;
            btn.textContent = 'Войти';
          });
      });
    }
    var lo = $('#btnLogout');
    if (lo) lo.addEventListener('click', function() {
      sbFetch('logout', {})
        .then(function() { window.location.reload(); })
        .catch(function() { window.location.reload(); });
    });
    var bu = $('#btnUsers');
    if (bu) bu.addEventListener('click', function() { window.open('../users.php', '_blank'); });
    var bau = $('#btnAddUser');
    if (bau) bau.addEventListener('click', function() { window.open('../add-user.php', '_blank'); });
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', bind);
    } else {
      bind();
    }
  })();
})();
</script>

<style>
.login-overlay { position: fixed; inset: 0; z-index: 99999; background: rgba(13,115,119,0.45);
  backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); display: flex;
  align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity .3s ease; }
.login-overlay.show { opacity: 1; pointer-events: all; }
.login-card { background: #fff; border-radius: 20px; padding: 36px 40px;
  box-shadow: 0 24px 80px rgba(13,115,119,.25); width: 360px; max-width: 92%;
  animation: popIn .3s cubic-bezier(.34,1.56,.64,1); }
.login-card h2 { color: #0A5C5F; font-size: 22px; margin-bottom: 4px; }
.login-card .login-sub { color: #5A7A80; font-size: 13px; margin-bottom: 18px; }
.login-field { margin-bottom: 12px; }
.login-field label { display: block; font-size: 12px; font-weight: 700; color: #5A7A80;
  text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.login-field input { width: 100%; box-sizing: border-box; padding: 11px 14px;
  border: 1px solid rgba(13,115,119,.25); border-radius: 10px; font-size: 14px; font-family: inherit; }
.login-field input:focus { outline: none; border-color: #0D7377; }
.login-error { display: none; background: #FFE8E0; color: #C24A2E; border-radius: 8px;
  padding: 8px 12px; font-size: 13px; margin-bottom: 12px; }
.user-chip { display: none; flex-direction: column; align-items: flex-start; line-height: 1.3;
  padding: 6px 14px; border-radius: 10px; background: rgba(13,115,119,.08);
  border: 1px solid rgba(13,115,119,.15); }
.user-chip-name { font-size: 13px; font-weight: 700; color: #1A2E35; }
.user-chip-role { font-size: 11px; color: #5A7A80; }
.viewonly-banner { display: none; position: fixed; top: 86px; left: 50%; transform: translateX(-50%);
  z-index: 900; max-width: 92%; padding: 12px 18px; background: #FFB627; color: #5A4A00;
  border-radius: 12px; font-size: 14px; font-weight: 600; box-shadow: 0 6px 20px rgba(255,182,39,.35); }

/* Компактная шапка: кнопки занимают ширину по содержимому */
.app-header {
  padding: 10px 22px !important;
  background: rgba(255,255,255,.98);
  border-bottom: 1px solid rgba(19,67,67,.08);
  box-shadow: 0 3px 14px rgba(19,67,67,.06);
}
.app-header-inner {
  max-width: 1360px;
  min-height: 0;
  display: grid;
  grid-template-columns: 230px minmax(0,1fr);
  align-items: center;
  gap: 18px;
}
.brand { gap: 11px; min-width: 0; }
.brand-logo {
  width: 42px; height: 42px; flex: 0 0 42px;
  border-radius: 12px;
  box-shadow: 0 3px 9px rgba(19,67,67,.12);
}
.brand-logo svg { width: 21px; height: 21px; }
.brand-text h1 { font-size: 16px; line-height: 1.2; letter-spacing: -.25px; }
.brand-text span { display: block; margin-top: 3px; font-size: 11px; line-height: 1.2; }
.header-actions {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  gap: 7px;
}
.header-actions .btn {
  width: auto;
  min-width: 0;
  min-height: 32px;
  padding: 6px 11px;
  gap: 6px;
  border-radius: 8px;
  font-size: 12px;
  line-height: 1.2;
  white-space: nowrap;
  text-align: center;
  box-shadow: 0 1px 3px rgba(19,67,67,.07);
  transition: transform .15s ease, box-shadow .15s ease;
}
.header-actions .btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(19,67,67,.12);
}
.header-actions .btn svg { width: 14px; height: 14px; flex: 0 0 auto; }
.header-actions .user-chip {
  width: auto;
  max-width: 190px;
  min-width: 110px;
  min-height: 32px;
  padding: 5px 10px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 2px;
  line-height: 1.1;
  overflow: hidden;
}
.header-actions .user-chip-name {
  max-width: 100%;
  font-size: 11px;
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.header-actions .user-chip-role {
  max-width: 100%;
  font-size: 9px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
@media (max-width: 900px) {
  .app-header { padding: 9px 14px !important; }
  .app-header-inner { grid-template-columns: 180px minmax(0,1fr); gap: 12px; }
  .brand { gap: 8px; }
  .brand-logo { width: 38px; height: 38px; flex-basis: 38px; }
  .brand-text h1 { font-size: 14px; }
  .brand-text span { font-size: 10px; }
  .header-actions { gap: 6px; }
  .header-actions .btn { min-height: 30px; padding: 5px 9px; font-size: 11px; }
}
@media (max-width: 620px) {
  .app-header { padding: 10px 12px !important; }
  .app-header-inner { grid-template-columns: 1fr; gap: 9px; }
  .brand { justify-content: center; }
  .header-actions { justify-content: center; gap: 6px; }
  .header-actions .btn { min-height: 32px; padding: 6px 9px; font-size: 11px; }
  .header-actions .user-chip { max-width: 100%; }
}

/* Все элементы шапки выровнены по центру */
.app-header-inner {
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  text-align: center;
  gap: 12px;
}
.app-header .brand {
  justify-content: center;
  width: 100%;
  text-align: center;
}
.app-header .brand-text { text-align: center; }
.app-header .header-actions {
  justify-content: center !important;
  align-items: center;
  width: 100%;
}
@media (max-width: 480px) {
  .app-header .header-actions {
    display: flex !important;
    flex-wrap: wrap;
    justify-content: center !important;
  }
  .app-header .header-actions .btn { width: auto; }
}

/* Логотип и название — единый блок; сам блок расположен по центру шапки */
.app-header .brand {
  width: auto !important;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  text-align: left;
  gap: 14px;
  margin: 0 auto;
}
.app-header .brand-text { text-align: left !important; }
</style>

<div class="viewonly-banner" id="viewOnlyBanner"></div>

<div class="login-overlay" id="loginOverlay">
  <div class="login-card">
    <h2>Ежедневный отчет СБ</h2>
    <div class="login-sub">Вход по личной учетной записи</div>
    <div class="login-error" id="loginError"></div>
    <form id="loginForm" autocomplete="off">
      <div class="login-field"><label>Логин</label><input type="text" id="loginUser" required autofocus></div>
      <div class="login-field"><label>Пароль</label><input type="password" id="loginPass" required></div>
      <button type="submit" class="btn btn-primary btn-block" id="loginSubmit">Войти</button>
    </form>
  </div>
</div>
<script>
(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('../api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,30000);document.addEventListener('visibilitychange',ping);})();
</script>
</body>
</html>