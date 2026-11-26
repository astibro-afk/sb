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
function exportRows(){
 if(!selected)return [];
 const data=selected.data||{},rows=[['Поле','Значение'],['Дата',selected.date],['Объект',selected.object],['Сотрудник',selected.employee],['Автор записи',selected.author]];
 Object.entries(data).forEach(([k,v])=>{if(!['b0_date','b0_to','b0_fio'].includes(k)&&v!==''&&v!=null)rows.push([k,typeof v==='object'?JSON.stringify(v):String(v)])});
 if(selected.dyn&&Object.keys(selected.dyn).length)rows.push(['Дополнительные данные',JSON.stringify(selected.dyn)]);
 return rows;
}
$('word').addEventListener('click',async()=>{
 if(!selected)return;
 try{
 await loadScript('https://unpkg.com/docx@8.5.0/build/index.umd.js',()=>!!window.docx);
 const {Document,Packer,Paragraph,Table,TableRow,TableCell,TextRun,WidthType,HeadingLevel}=window.docx;
 const rows=exportRows();
  const tableRows=rows.map((r,i)=>new TableRow({children:r.map(c=>new TableCell({children:[new Paragraph({children:[new TextRun({text:String(c),bold:i===0})]})]}))}));
  const table=new Table({width:{size:100,type:WidthType.PERCENTAGE},rows:tableRows});
 const doc=new Document({sections:[{children:[new Paragraph({text:'Ежедневный отчёт СБ',heading:HeadingLevel.TITLE}),new Paragraph({text:'Дата: '+selected.date}),new Paragraph({text:'Объект: '+selected.object}),new Paragraph({text:'Сотрудник: '+selected.employee}),new Paragraph({text:'Автор записи: '+selected.author}),new Paragraph({text:''}),table]}]});
 const blob=await Packer.toBlob(doc);download(blob,'Отчёт_'+safe(selected.date)+'_'+safe(selected.employee)+'.docx');
 }catch(e){$('downloadStatus').textContent='Ошибка Word: '+e.message;}
});
$('excel').addEventListener('click',()=>{
 if(!selected)return;
 loadScript('https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',()=>!!window.XLSX).then(()=>{
 const wb=XLSX.utils.book_new();const ws=XLSX.utils.aoa_to_sheet(exportRows());ws['!cols']=[{wch:34},{wch:70}];XLSX.utils.book_append_sheet(wb,ws,'Отчёт');
 XLSX.writeFile(wb,'Отчёт_'+safe(selected.date)+'_'+safe(selected.employee)+'.xlsx');
 }).catch(e=>$('downloadStatus').textContent='Ошибка Excel: '+e.message);
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