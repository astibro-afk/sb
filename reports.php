<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0D7377">
<title>Отчёты сотрудников — СБ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--p:#0D7377;--pd:#0A5C5F;--bg:#F0F7F7;--text:#1A2E35;--muted:#5A7A80;--line:rgba(13,115,119,.14);--grad:linear-gradient(135deg,#0D7377,#14A098 55%,#2EC4B6)}
*{box-sizing:border-box}body{margin:0;min-height:100vh;background:radial-gradient(circle at 10% 0%,rgba(46,196,182,.16),transparent 30%),var(--bg);font-family:Inter,Arial,sans-serif;color:var(--text)}
header{background:rgba(255,255,255,.88);border-bottom:1px solid var(--line);padding:17px 24px;position:sticky;top:0;z-index:5;backdrop-filter:blur(14px)}
.head,main{max-width:1180px;margin:auto}.head{display:flex;align-items:center;justify-content:space-between;gap:16px}.brand{display:flex;align-items:center;gap:13px}.logo{width:42px;height:42px;border-radius:12px;background:var(--grad);display:grid;place-items:center;color:white;font-weight:800}.brand h1{font-size:17px;margin:0}.brand small{color:var(--muted)}
main{padding:28px 22px 60px}.panel{background:rgba(255,255,255,.88);border:1px solid var(--line);border-radius:18px;padding:22px;margin-bottom:18px;box-shadow:0 5px 24px rgba(13,115,119,.06)}
h2{font-size:21px;margin:0 0 7px}p.sub{color:var(--muted);margin:0 0 20px;font-size:13px}
.filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;align-items:end}.field label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin:0 0 6px}.field input,.field select{width:100%;height:42px;border:1px solid var(--line);border-radius:10px;padding:0 11px;background:white;color:var(--text);font:inherit;font-size:14px;min-width:0}
button,.linkbtn{border:0;border-radius:10px;padding:11px 16px;font:600 13px Inter,Arial;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px}.primary{background:var(--grad);color:white}.secondary{background:#E0F2F1;color:var(--pd)}.outline{background:white;color:var(--text);border:1px solid var(--line)}button:disabled{opacity:.45;cursor:not-allowed}
.actions{display:flex;gap:8px;flex-wrap:wrap}.status{font-size:13px;color:var(--muted);margin-top:12px}.status.error{color:#b42318}.summary{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}.pill{background:#E0F2F1;color:var(--pd);padding:7px 11px;border-radius:20px;font-size:12px;font-weight:700}
.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:720px}th,td{text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);font-size:13px;vertical-align:middle}th{font-size:11px;text-transform:uppercase;letter-spacing:.35px;color:var(--muted);background:#F7FBFB;position:sticky;top:0}tr:hover td{background:#FAFDFD}.empty{text-align:center;padding:32px;color:var(--muted)}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(12,35,40,.48);z-index:20;align-items:center;justify-content:center;padding:18px}.modal-backdrop.open{display:flex}.modal{width:min(460px,100%);background:#fff;border-radius:18px;padding:22px;box-shadow:0 20px 70px rgba(0,0,0,.22)}.modal-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}.modal h2{margin:0;font-size:20px}.modal .actions{display:grid;grid-template-columns:1fr 1fr}.modal .actions button{min-height:48px}@media(max-width:440px){.modal .actions{grid-template-columns:1fr}}
@media(max-width:760px){header{padding:13px 15px}.head{align-items:flex-start}.brand h1{font-size:15px}main{padding:18px 13px 40px}.panel{padding:16px}.filters{grid-template-columns:repeat(2,minmax(0,1fr))}.detail-grid{grid-template-columns:1fr}}
@media(max-width:440px){.filters{grid-template-columns:1fr}.head .outline{padding:9px 10px}}
</style>
</head>
<body>
<header><div class="head"><div class="brand"><div class="logo">СБ</div><div><h1>Отчёты сотрудников</h1><small>Архив ежедневных отчётов</small></div></div><a class="linkbtn outline" href="index.html">К ежедневному отчёту</a></div></header>
<main>
<section class="panel">
<h2>Поиск отчётов</h2><p class="sub">Выберите период и при необходимости ограничьте список объектом или сотрудником.</p>
<div class="filters">
<div class="field"><label for="from">С даты</label><input type="date" id="from"></div>
<div class="field"><label for="to">По дату</label><input type="date" id="to"></div>
<div class="field"><label for="object">Торговый объект</label><select id="object"><option value="">Все доступные объекты</option></select></div>
<div class="field"><label for="employee">Сотрудник</label><select id="employee"><option value="">Все сотрудники</option></select></div>
</div>
<div class="actions" style="margin-top:16px"><button class="primary" id="search">Показать отчёты</button></div>
<div class="status" id="status" role="status"></div>
</section>
<section class="panel">
<div class="summary" id="summary"></div>
<div class="table-wrap"><table><thead><tr><th>Дата</th><th>Объект</th><th>Сотрудник</th><th>Автор записи</th><th>Обновлён</th><th>Действие</th></tr></thead><tbody id="rows"><tr><td colspan="6" class="empty">Загрузка отчётов…</td></tr></tbody></table></div>
</section>
<div class="modal-backdrop" id="downloadModal" aria-hidden="true"><div class="modal" role="dialog" aria-modal="true"><div class="modal-head"><div><h2>Скачать отчёт</h2><p class="sub" id="downloadMeta" style="margin:5px 0 0"></p></div><button class="outline" id="closeModal" type="button">Закрыть</button></div><p class="sub" style="margin:16px 0 10px">Выберите формат документа:</p><div class="actions"><button class="primary" id="word" type="button">Word (.docx)</button><button class="secondary" id="excel" type="button">Excel (.xlsx)</button></div><div class="status" id="downloadStatus"></div></div></div>
</main>
<script>
(function(){
'use strict';
const $=id=>document.getElementById(id);
let allRecords=[], selected=null, currentUser=null;
function loadScript(src,test){return new Promise((resolve,reject)=>{if(test())return resolve();const s=document.createElement('script');s.src=src;s.async=true;s.onload=()=>test()?resolve():reject(new Error('Библиотека не загрузилась.'));s.onerror=()=>reject(new Error('Не удалось загрузить библиотеку. Проверьте интернет.'));document.head.appendChild(s);});}
const today=new Date(), iso=d=>{const x=new Date(d.getTime()-d.getTimezoneOffset()*60000);return x.toISOString().slice(0,10)};
$('from').value='2000-01-01';$('to').value=iso(today);
function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function val(v){if(v==null||v==='')return '—';if(typeof v==='object')return JSON.stringify(v);return String(v);}
function setStatus(s,error){$('status').textContent=s;$('status').className='status'+(error?' error':'');}
async function load(){
 setStatus('Загружаем архив…');
 const qs=new URLSearchParams({action:'reports_data',from:$('from').value,to:$('to').value});
 if($('object').value)qs.set('object_ids',$('object').value);
 const response=await fetch('api.php?'+qs.toString(),{credentials:'same-origin',cache:'no-store'});
 const data=await response.json();
 if(!response.ok||!data.ok)throw new Error(data.error||'Не удалось загрузить отчёты.');
 currentUser=data.user;allRecords=data.records||[];
 const objects=data.objects||[];
 if(!$('object').dataset.ready){objects.forEach(o=>{const op=document.createElement('option');op.value=o.id;op.textContent=o.name;$('object').appendChild(op)});$('object').dataset.ready='1';}
 const employees=new Map();
 allRecords.forEach(r=>{if(r.owner_user_id)employees.set(String(r.owner_user_id),r.author||r.employee||('Сотрудник #'+r.owner_user_id));});
 const old=$('employee').value;$('employee').innerHTML='<option value="">Все сотрудники</option>';
 [...employees].sort((a,b)=>a[1].localeCompare(b[1],'ru')).forEach(([id,name])=>{const op=document.createElement('option');op.value=id;op.textContent=name;$('employee').appendChild(op)});
 if(employees.has(old))$('employee').value=old;
 render();
 setStatus('Найдено отчётов: '+allRecords.length+'.');
}
function render(){
 const objectId=$('object').value, employeeId=$('employee').value;
 const filtered=allRecords.filter(r=>(!objectId||(r.object_id?String(r.object_id)===objectId:true))&&(!employeeId||String(r.owner_user_id||'')===employeeId));
 $('summary').innerHTML='<span class="pill">Отчётов: '+filtered.length+'</span><span class="pill">Объектов: '+new Set(filtered.map(r=>r.object)).size+'</span><span class="pill">Сотрудников: '+new Set(filtered.map(r=>r.owner_user_id||r.author)).size+'</span>';
 $('rows').innerHTML=filtered.length?filtered.map((r)=>'<tr><td>'+esc(r.date)+'</td><td>'+esc(r.object)+'</td><td>'+esc(r.employee)+'</td><td>'+esc(r.author)+'</td><td>'+esc(r.updated_at||'')+'</td><td><div class="actions"><button class="secondary" data-key="'+esc(r.record_key)+'">Открыть</button><button class="outline" data-download="'+esc(r.record_key)+'">Скачать</button></div></td></tr>').join(''):'<tr><td colspan="6" class="empty">За выбранный период отчётов нет.</td></tr>';
 $('rows').querySelectorAll('button[data-key]').forEach(b=>b.addEventListener('click',()=>openRecord(b.dataset.key)));
 $('rows').querySelectorAll('button[data-download]').forEach(b=>b.addEventListener('click',()=>showDownload(b.dataset.download)));
}
function openRecord(key){showDownload(key);}
function showDownload(key){selected=allRecords.find(r=>r.record_key===key);if(!selected)return;$('downloadMeta').textContent=[selected.date,selected.object,selected.employee].filter(Boolean).join(' · ');$('downloadStatus').textContent='';$('downloadModal').classList.add('open');$('downloadModal').setAttribute('aria-hidden','false');}
function closeDownload(){$('downloadModal').classList.remove('open');$('downloadModal').setAttribute('aria-hidden','true');}
$('closeModal').addEventListener('click',closeDownload);
$('downloadModal').addEventListener('click',e=>{if(e.target===$('downloadModal'))closeDownload();});
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
    function colName(nn) {
      var s2 = ''; nn++;
      while (nn) { var md = (nn - 1) % 26; s2 = String.fromCharCode(65 + md) + s2; nn = Math.floor((nn - md - 1) / 26); }
      return s2;
    }
        function sheetXml(rows, opts) {
      opts = opts || {};
      var out = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
      if (opts.freeze || opts.freezeX) { var ySp = opts.freeze || 0, xSp = opts.freezeX || 0; out += '<sheetViews><sheetView workbookViewId="0"><pane' + (xSp ? ' xSplit="' + xSp + '"' : '') + (ySp ? ' ySplit="' + ySp + '"' : '') + ' topLeftCell="' + colName(xSp) + (ySp + 1) + '" activePane="bottomRight" state="frozen"/></sheetView></sheetViews>'; }
      out += '<sheetFormatPr defaultRowHeight="23"/>'; 
      if (opts.cols && opts.cols.length) {
        out += '<cols>';
        for (var ci = 0; ci < opts.cols.length; ci++) out += '<col min="' + (ci + 1) + '" max="' + (ci + 1) + '" width="' + opts.cols[ci] + '" customWidth="1"/>';
        out += '</cols>';
      }
      out += '<sheetData>';
      for (var r = 0; r < rows.length; r++) {
        out += '<row r="' + (r + 1) + '"' + (r === 0 ? ' ht="36" customHeight="1"' : ((r === 1 || r === 2 || r === 3) ? ' ht="32" customHeight="1"' : ' ht="23" customHeight="1"')) + '>';
        var ra = rows[r] || [];
        for (var c = 0; c < ra.length; c++) {
          var cell = ra[c];
          if (cell === null || cell === undefined || cell === '') continue;
          var isObj = (cell && typeof cell === 'object' && !Array.isArray(cell));
          var val = isObj ? cell.v : cell;
          var st = isObj ? cell.s : undefined;
          if (val === null || val === undefined || val === '') continue;
          var ref = colName(c) + (r + 1);
          if (typeof val === 'number' && isFinite(val)) out += '<c r="' + ref + '"' + (st ? ' s="' + st + '"' : '') + '><v>' + val + '</v></c>';
          else out += '<c r="' + ref + '"' + (st ? ' s="' + st + '"' : '') + ' t="inlineStr"><is><t xml:space="preserve">' + xlEsc(String(val)) + '</t></is></c>';
        }
        out += '</row>';
      }
      out += '</sheetData>';
      if (opts.merge && opts.merge.length) out += '<mergeCells count="' + opts.merge.length + '">' + opts.merge.map(function(m) { return '<mergeCell ref="' + m + '"/>'; }).join('') + '</mergeCells>';
      out += '<printOptions horizontalCentered="0"/><pageMargins left="0.28" right="0.28" top="0.55" bottom="0.55" header="0.2" footer="0.2"/><pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/><headerFooter><oddFooter><![CDATA[&LГруппа БФ Волгоград 1&RСтраница &P из &N]]></oddFooter></headerFooter>';
      out += '</worksheet>';
      return out;
    }
    var parts = [
      ['[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' +
        sheets.map(function(_, si) { return '<Override PartName="/xl/worksheets/sheet' + (si + 1) + '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'; }).join('') + '</Types>'],
      ['_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>'],
      ['xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>' +
        sheets.map(function(s, si) { return '<sheet name="' + xlEsc(s.name) + '" sheetId="' + (si + 1) + '" r:id="rId' + (si + 1) + '"/>'; }).join('') + '</sheets></workbook>'],
      ['xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
        sheets.map(function(_, si) { return '<Relationship Id="rId' + (si + 1) + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' + (si + 1) + '.xml"/>'; }).join('') +
        '<Relationship Id="rIdS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>'],
      ['xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">\n<numFmts count="2"><numFmt numFmtId="164" formatCode="#,##0"/><numFmt numFmtId="165" formatCode="#,##0.00"/></numFmts>\n<fonts count="5">\n<font><sz val="10"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><b/><sz val="10"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><b/><sz val="16"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><b/><sz val="9"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><i/><sz val="9"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n</fonts>\n<fills count="7">\n<fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFE9EBED"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFF0F1F2"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFE5E7E9"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFF8F9FA"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>\n</fills>\n<borders count="4">\n<border><left/><right/><top/><bottom/><diagonal/></border>\n<border><left style="thin"><color rgb="FFD9DDE0"/></left><right style="thin"><color rgb="FFD9DDE0"/></right><top style="thin"><color rgb="FFD9DDE0"/></top><bottom style="thin"><color rgb="FFD9DDE0"/></bottom><diagonal/></border>\n<border><left style="thin"><color rgb="FFC9CDD1"/></left><right style="thin"><color rgb="FFC9CDD1"/></right><top style="thin"><color rgb="FFC9CDD1"/></top><bottom style="thin"><color rgb="FFC9CDD1"/></bottom><diagonal/></border>\n<border><left/><right/><top/><bottom style="medium"><color rgb="FF9EA4AA"/></bottom><diagonal/></border>\n</borders>\n<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>\n<cellXfs count="13">\n<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>\n<xf numFmtId="0" fontId="2" fillId="0" borderId="3" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1" indent="1"/></xf>\n<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>\n<xf numFmtId="0" fontId="1" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>\n<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1" indent="1"/></xf>\n<xf numFmtId="164" fontId="0" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="165" fontId="0" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="0" fontId="3" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>\n<xf numFmtId="164" fontId="3" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="165" fontId="3" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="0" fontId="3" fillId="3" borderId="3" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1" indent="1"/></xf>\n<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="0" fontId="3" fillId="4" borderId="2" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n</cellXfs></styleSheet>'],
    ];
    sheets.forEach(function(s, si) { parts.push(['xl/worksheets/sheet' + (si + 1) + '.xml', sheetXml(s.rows, s.opts)]); });

    var chunks = [], central = [], offset = 0;
    function pack(values) {
      var out = new Uint8Array(values.length), view = new DataView(out.buffer);
      for (var i = 0; i < values.length; i++) view.setUint8(i, values[i] & 255);
      chunks.push(out); offset += out.length;
    }
    function u16(v) { return [v & 255, (v >>> 8) & 255]; }
    function u32(v) { return [v & 255, (v >>> 8) & 255, (v >>> 16) & 255, (v >>> 24) & 255]; }
    function ascii(str) { var out = new Uint8Array(str.length); for (var i = 0; i < str.length; i++) out[i] = str.charCodeAt(i) & 255; return out; }
    function pushBytes(arr) { var out = arr instanceof Uint8Array ? arr : new Uint8Array(arr); chunks.push(out); offset += out.length; }
    parts.forEach(function(p) {
      var name = p[0], nameBytes = ascii(name), data = utf8Bytes(p[1]);
      var crc = crc32(data), off = offset;
      pack([].concat(u32(0x04034b50), u16(20), u16(0), u16(0), u16(0), u16(0), u32(crc), u32(data.length), u32(data.length), u16(nameBytes.length), u16(0)));
      pushBytes(nameBytes); pushBytes(data);
      central.push({ nameBytes: nameBytes, crc: crc, size: data.length, off: off });
    });
    var cdOffset = offset;
    central.forEach(function(c) {
      pack([].concat(u32(0x02014b50), u16(20), u16(20), u16(0), u16(0), u16(0), u16(0), u32(c.crc), u32(c.size), u32(c.size), u16(c.nameBytes.length), u16(0), u16(0), u16(0), u16(0), u32(0), u32(c.off)));
      pushBytes(c.nameBytes);
    });
    var cdSize = offset - cdOffset;
    pack([].concat(u32(0x06054b50), u16(0), u16(0), u16(central.length), u16(central.length), u32(cdSize), u32(cdOffset), u16(0)));
    return new Blob(chunks, { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  }


const FIELD_LABELS={
 b1_pos_checks:'Отмена позиций — проверено',b1_pos_qty:'Отмена позиций — нарушений',b1_pos_sum:'Отмена позиций — сумма ущерба, руб.',
 b1_chek_checks:'Аннулирование чека — проверено',b1_chek_qty:'Аннулирование чека — нарушений',b1_chek_sum:'Аннулирование чека — сумма ущерба, руб.',
 b1_ret_checks:'Фиктивные возвраты — проверено',b1_ret_qty:'Фиктивные возвраты — нарушений',b1_ret_sum:'Фиктивные возвраты — сумма ущерба, руб.',
 b1_weight_checks:'Заниженный вес — проверено',b1_weight_qty:'Заниженный вес — нарушений',b1_weight_sum:'Заниженный вес — сумма ущерба, руб.',
 b2_triggers:'Срабатываний FaceID, шт.',b2_worked:'Отработано, шт.',b2_db:'Пополнено базы данных, чел.',
 b3_in_checks:'Перемещения внутри объекта — проверок',b3_in_qty:'Перемещения внутри объекта — нарушений',b3_in_sum:'Перемещения внутри объекта — сумма, руб.',
 b3_between_checks:'Перемещения между объектами — проверок',b3_between_qty:'Перемещения между объектами — нарушений',b3_between_sum:'Перемещения между объектами — сумма, руб.',
 b4_checks:'Инвентаризации — проверок',b4_dev_qty:'Инвентаризации — расхождений',b4_dev_sum:'Инвентаризации — сумма, руб.',
 b5_date:'Дата выгрузки виртуальных остатков',b5_positions:'Выгружено виртуальных остатков, поз.',b5_sum:'Сумма виртуальных остатков, руб.',b5_sz_date:'СЗ на ДГМ от',b5_copy:'Копия Директору Группы',
 b6_checks:'Поставки с РЦ — проверок',b6_q_qty:'Поставки с РЦ — качество, нарушений',b6_q_sum:'Поставки с РЦ — качество, руб.',b6_qty_qty:'Поставки с РЦ — количество, нарушений',b6_qty_sum:'Поставки с РЦ — количество, руб.',b6_sg_qty:'Поставки с РЦ — сроки годности, нарушений',b6_sg_sum:'Поставки с РЦ — сроки годности, руб.',
 b7_checks:'Прямые поставщики — проверок',b7_q_qty:'Прямые поставщики — качество, нарушений',b7_q_sum:'Прямые поставщики — качество, руб.',b7_qty_qty:'Прямые поставщики — количество, нарушений',b7_qty_sum:'Прямые поставщики — количество, руб.',b7_sg_qty:'Прямые поставщики — сроки годности, нарушений',b7_sg_sum:'Прямые поставщики — сроки годности, руб.',
 b8_checks:'Списания — проверок',b8_qty:'Списания — нарушений',b8_sum:'Списания — сумма, руб.',
 b9_count:'Количество служебных записок, шт.',
 b10_crim_qty:'Уголовные — нарушений',b10_crim_sum:'Уголовные — ущерб, руб.',b10_adm_qty:'Административные — нарушений',b10_adm_sum:'Административные — ущерб, руб.',b10_local_qty:'Разбор на месте — случаев',b10_local_sum:'Разбор на месте — ущерб, руб.',
 b11_uch:'Проверка УЧО',b11_uch_fio:'ФИО сотрудника ЧОО без УЧО',b11_instr:'Инструктаж',b11_instr_topic:'Тема инструктажа'
};
const SECTIONS=[
 ['1. Проверка кассовых операций',['b1_pos_checks','b1_pos_qty','b1_pos_sum','b1_chek_checks','b1_chek_qty','b1_chek_sum','b1_ret_checks','b1_ret_qty','b1_ret_sum','b1_weight_checks','b1_weight_qty','b1_weight_sum']],
 ['2. Работа с FaceID',['b2_triggers','b2_worked','b2_db']],
 ['3. Контроль перемещений ТМЦ',['b3_in_checks','b3_in_qty','b3_in_sum','b3_between_checks','b3_between_qty','b3_between_sum']],
 ['4. Контроль инвентаризаций',['b4_checks','b4_dev_qty','b4_dev_sum']],
 ['5. Виртуальные остатки',['b5_date','b5_positions','b5_sum','b5_sz_date','b5_copy']],
 ['6. Поставки с РЦ',['b6_checks','b6_q_qty','b6_q_sum','b6_qty_qty','b6_qty_sum','b6_sg_qty','b6_sg_sum']],
 ['7. Поставки от прямых поставщиков',['b7_checks','b7_q_qty','b7_q_sum','b7_qty_qty','b7_qty_sum','b7_sg_qty','b7_sg_sum']],
 ['8. Списание ТМЦ',['b8_checks','b8_qty','b8_sum']],
 ['9. Служебные записки',['b9_count']],
 ['10. Хищения и правонарушения',['b10_crim_qty','b10_crim_sum','b10_adm_qty','b10_adm_sum','b10_local_qty','b10_local_sum']],
 ['11. Работа ООО ЧОП «Артакс+»',['b11_post_cash','b11_dfrom_cash','b11_from_cash','b11_dto_cash','b11_to_cash','b11_post_kso','b11_dfrom_kso','b11_from_kso','b11_dto_kso','b11_to_kso','b11_post_entr','b11_dfrom_entr','b11_from_entr','b11_dto_entr','b11_to_entr','b11_uch','b11_uch_fio','b11_instr','b11_instr_topic']]
];
const COMMENT_KEYS=Array.from({length:11},(_,i)=>'b'+(i+1)+'_comment');
function recordSections(){
 const d=selected.data||{},dyn=selected.dyn||{},out=[];
 SECTIONS.forEach(([title,keys])=>{
  const lines=[];
  keys.forEach(k=>{const val=d[k];if(val!==undefined&&val!==null&&String(val).trim()!=='')lines.push(FIELD_LABELS[k]||k+': '+String(val));if(val!==undefined&&val!==null&&String(val).trim()!=='')lines[lines.length-1]+=': '+String(val);});
  const dk=title.startsWith('4.')?'b4_inv':title.startsWith('6.')?'b6_sup':title.startsWith('7.')?'b7_sup':title.startsWith('8.')?'b8_sp':null;
  if(dk&&Array.isArray(dyn[dk]))dyn[dk].forEach((r,n)=>{const parts=Object.entries(r||{}).filter(([,v])=>v!==''&&v!=null).map(([k,v])=>({num:'Номер',date:'Дата',sum:'Сумма, руб.',cnt:'Проверено позиций',dev:'Расхождения, руб.'}[k]||k)+': '+v);if(parts.length)lines.push('Дополнительная запись '+(n+1)+': '+parts.join('; '));});
  if(lines.length)out.push({title,lines});
 });
 COMMENT_KEYS.forEach((k,i)=>{if(d[k]){let sec=out.find(x=>x.title.startsWith((i+1)+'.'));if(!sec){sec={title:(i+1)+'. Комментарий',lines:[]};out.push(sec);}sec.note=sec.note||[];sec.note.push(String(d[k]));}});
 return out;
}
function wordReportHtml(){
 const sections=recordSections(),d=selected.data||{};
 let out='<div class="doc-wrap">'+docMasthead('ЕЖЕДНЕВНЫЙ ОТЧЕТ');
 out+='<h1 style="margin:0 0 4pt 0;font-family:Calibri,Arial,sans-serif;font-size:17pt;line-height:1.15;font-weight:bold;color:#000;text-align:left;">Отчёт по итогам контрольных мероприятий</h1>';
 out+='<p style="margin:0 0 15pt 0;font-family:Calibri,Arial,sans-serif;font-size:10pt;color:#000;">Ежедневный отчет за '+esc(selected.date||'')+'</p>';
 out+=docMetaTable([['Объект (ТО)',esc(selected.object||'—')],['Составитель',esc(selected.employee||'—')],['Дата',esc(selected.date||'—')],['Автор записи',esc(selected.author||'—')]]);
 sections.forEach(s=>{out+=docSection(s.title,s.lines,s.note||[]);});
 out+='<div class="doc-sign"><p><strong>Отчёт составил:</strong> '+esc(selected.employee||'_______________________')+'</p><p><strong>Подпись:</strong> ____________________</p></div></div>';
 return '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="utf-8"><title>Отчёт по итогам контрольных мероприятий</title><style>'+docCss()+'</style></head><body>'+out+'</body></html>';
}
function styledExcelRows(){
 const rows=[[{v:'ЕЖЕДНЕВНЫЙ ОТЧЁТ СБ — '+(selected.date||''),s:1},null],[{v:'Объект (ТО)',s:3},{v:selected.object||'—',s:4}],[{v:'Составитель',s:3},{v:selected.employee||'—',s:4}],[{v:'Дата',s:3},{v:selected.date||'—',s:4}],[{v:'Автор записи',s:3},{v:selected.author||'—',s:4}],[{v:'Показатель',s:3},{v:'Значение',s:3}]];
 const merges=['A1:B1'];
 recordSections().forEach(sec=>{const n=rows.length+1;rows.push([{v:sec.title,s:10},null]);merges.push('A'+n+':B'+n);sec.lines.forEach(line=>rows.push([{v:line,s:4},null]));(sec.note||[]).forEach(note=>rows.push([{v:'Комментарий: '+note,s:2},null]));});
 return {rows,merges};
}
function exportRows(){const s=styledExcelRows();return s.rows.map(r=>r.map(c=>c&&c.v!==undefined?c.v:''));}

$('word').addEventListener('click',()=>{
 if(!selected)return;
 try{const html=wordReportHtml();const blob=new Blob(['\\ufeff'+html],{type:'application/msword;charset=utf-8'});download(blob,'Отчёт_контроль_'+safe(selected.date)+'_'+safe(selected.employee)+'.doc');}
 catch(e){$('downloadStatus').textContent='Ошибка Word: '+e.message;}
});
$('excel').addEventListener('click',()=>{
 if(!selected)return;
 try{const x=styledExcelRows();const blob=buildXlsx([{name:'Отчёт',rows:x.rows,opts:{cols:[62,82],merge:x.merges,freeze:6}}]);download(blob,'Отчёт_контроль_'+safe(selected.date)+'_'+safe(selected.employee)+'.xlsx');}
 catch(e){$('downloadStatus').textContent='Ошибка Excel: '+e.message;}
});
function safe(s){return String(s||'Отчёт').replace(/[^\p{L}\p{N}_-]+/gu,'_').slice(0,70);}
function download(blob,name){const url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download=name;document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1500);}
$('search').addEventListener('click',()=>load().catch(e=>setStatus(e.message,true)));
$('object').addEventListener('change',()=>load().catch(e=>setStatus(e.message,true)));
$('employee').addEventListener('change',render);
load().catch(e=>{setStatus(e.message,true);$('rows').innerHTML='<tr><td colspan="6" class="empty">Не удалось загрузить архив.</td></tr>';});
})();
</script>
</body>
</html>