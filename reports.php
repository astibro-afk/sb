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
button,.linkbtn{border:0;border-radius:10px;padding:11px 16px;font:600 13px Inter,Arial;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px}.primary{background:var(--grad);color:white}.secondary{background:#E0F2F1;color:var(--pd)}.outline{background:white;color:var(--text);border:1px solid var(--line)}button:disabled{opacity:.45;cursor:not-allowed}.danger{background:#FEE4E2;color:#B42318}
.actions{display:flex;align-items:center;gap:6px;flex-wrap:nowrap;white-space:nowrap}.actions button{flex:0 0 auto;white-space:nowrap;padding:8px 11px;font-size:12px}.status{font-size:13px;color:var(--muted);margin-top:12px}.status.error{color:#b42318}.summary{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}.pill{background:#E0F2F1;color:var(--pd);padding:7px 11px;border-radius:20px;font-size:12px;font-weight:700}
.report-tools{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 14px}.report-search{flex:1;max-width:420px;height:40px;border:1px solid var(--line);border-radius:10px;padding:0 12px;font:inherit;font-size:13px;background:#fff;color:var(--text)}.report-search:focus{outline:none;border-color:var(--p);box-shadow:0 0 0 3px rgba(13,115,119,.1)}.pagination{display:flex;align-items:center;justify-content:flex-end;gap:8px;padding-top:14px}.pagination-info{font-size:12px;color:var(--muted);margin-right:auto}.pagination select{height:34px;border:1px solid var(--line);border-radius:8px;padding:0 8px;background:#fff;color:var(--text)}.pagination button{padding:8px 11px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:720px}th,td{text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);font-size:13px;vertical-align:middle}th{font-size:11px;text-transform:uppercase;letter-spacing:.35px;color:var(--muted);background:#F7FBFB;position:sticky;top:0}tr:hover td{background:#FAFDFD}.empty{text-align:center;padding:32px;color:var(--muted)}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(12,35,40,.48);z-index:20;align-items:center;justify-content:center;padding:18px}.modal-backdrop.open{display:flex}.modal{width:min(460px,100%);background:#fff;border-radius:18px;padding:22px;box-shadow:0 20px 70px rgba(0,0,0,.22)}.modal-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}.modal h2{margin:0;font-size:20px}.modal .actions{display:grid;grid-template-columns:1fr 1fr}.modal .actions button{min-height:48px}@media(max-width:440px){.modal .actions{grid-template-columns:1fr}}
@media(max-width:760px){header{padding:13px 15px}.head{align-items:flex-start}.brand h1{font-size:15px}main{padding:18px 13px 40px}.panel{padding:16px}.filters{grid-template-columns:repeat(2,minmax(0,1fr))}.detail-grid{grid-template-columns:1fr}}
@media(max-width:440px){.filters{grid-template-columns:1fr}.head .outline{padding:9px 10px}}
.preview-modal{display:none;position:fixed;inset:0;background:rgba(12,35,40,.62);z-index:30;padding:14px;align-items:center;justify-content:center}
.preview-modal.open{display:flex}
.preview-dialog{width:min(1180px,100%);height:min(94vh,1100px);background:#F0F4F4;border-radius:18px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.3)}
.preview-dialog-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;background:#fff;border-bottom:1px solid var(--line)}
.preview-dialog-head h2{font-size:17px;margin:0}
.preview-dialog-head p{font-size:12px;color:var(--muted);margin:4px 0 0}
.preview-frame{display:block;width:100%;height:100%;border:0;background:#eef2f2}
@media(max-width:600px){.preview-modal{padding:0}.preview-dialog{height:100vh;border-radius:0}.preview-dialog-head{padding:12px}.preview-dialog-head h2{font-size:15px}}
</style>
<link rel="stylesheet" href="site-theme.css">
</head>
<body>
<header><div class="head"><div class="brand"><div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M9 13h6M9 17h6"></path></svg></div><div><h1>Отчёты сотрудников</h1><small>Архив ежедневных отчётов</small></div></div><a class="linkbtn outline" href="index.html">К ежедневному отчёту</a></div></header>
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
<div class="report-tools"><input class="report-search" id="reportSearch" type="search" placeholder="Быстрый поиск по дате, объекту или сотруднику…" aria-label="Поиск по отчётам"><span class="pill" id="searchCount">—</span></div><div class="summary" id="summary"></div>
<div class="table-wrap"><table><thead><tr><th>Дата</th><th>Объект</th><th>Сотрудник</th><th>Автор записи</th><th>Обновлён</th><th>Действие</th></tr></thead><tbody id="rows"><tr><td colspan="6" class="empty">Загрузка отчётов…</td></tr></tbody></table></div><div class="pagination" id="pagination"><span class="pagination-info" id="pageInfo"></span><label for="pageSize" style="font-size:12px;color:var(--muted)">На странице</label><select id="pageSize" aria-label="Количество отчётов на странице"><option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option></select><button class="outline" id="prevPage" type="button">Назад</button><button class="outline" id="nextPage" type="button">Вперёд</button></div>
</section>
<div class="modal-backdrop" id="downloadModal" aria-hidden="true"><div class="modal" role="dialog" aria-modal="true"><div class="modal-head"><div><h2>Скачать отчёт</h2><p class="sub" id="downloadMeta" style="margin:5px 0 0"></p></div><button class="outline" id="closeModal" type="button">Закрыть</button></div><p class="sub" style="margin:16px 0 10px">Выберите формат документа:</p><div class="actions"><button class="primary" id="word" type="button">Word (.doc)</button><button class="secondary" id="excel" type="button">Excel (.xlsx)</button></div><div class="status" id="downloadStatus"></div></div></div>
<div class="preview-modal" id="previewModal" aria-hidden="true">
 <div class="preview-dialog" role="dialog" aria-modal="true" aria-labelledby="previewTitle">
  <div class="preview-dialog-head">
   <div><h2 id="previewTitle">Предварительный просмотр отчёта</h2><p id="previewMeta"></p></div>
   <button class="outline" id="closePreview" type="button">Закрыть</button>
  </div>
  <iframe class="preview-frame" id="previewFrame" title="Предварительный просмотр документа"></iframe>
 </div>
</div>
</main>
<script src="report-document.js"></script>
<script>
(function(){
'use strict';
const $=id=>document.getElementById(id);
let allRecords=[], selected=null, currentUser=null, csrfToken='', currentPage=1, pageSize=25, searchTerm='';
function loadScript(src,test){return new Promise((resolve,reject)=>{if(test())return resolve();const s=document.createElement('script');s.src=src;s.async=true;s.onload=()=>test()?resolve():reject(new Error('Библиотека не загрузилась.'));s.onerror=()=>reject(new Error('Не удалось загрузить библиотеку. Проверьте интернет.'));document.head.appendChild(s);});}
const today=new Date(), iso=d=>{const x=new Date(d.getTime()-d.getTimezoneOffset()*60000);return x.toISOString().slice(0,10)};
$('from').value='2026-01-01';$('to').value=iso(today);
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
 currentUser=data.user;csrfToken=data.csrf||'';allRecords=data.records||[];
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
 const objectId=$('object').value, employeeId=$('employee').value, q=searchTerm.trim().toLocaleLowerCase('ru');
 const filtered=allRecords.filter(r=>(!objectId||(r.object_id?String(r.object_id)===objectId:true))&&(!employeeId||String(r.owner_user_id||'')===employeeId)&&(!q||[r.date,r.object,r.employee,r.author].some(v=>String(v||'').toLocaleLowerCase('ru').includes(q))));
 const pages=Math.max(1,Math.ceil(filtered.length/pageSize));currentPage=Math.min(currentPage,pages);
 const start=(currentPage-1)*pageSize, visible=filtered.slice(start,start+pageSize);
 $('summary').innerHTML='<span class="pill">Отчётов: '+filtered.length+'</span><span class="pill">Объектов: '+new Set(filtered.map(r=>r.object)).size+'</span><span class="pill">Сотрудников: '+new Set(filtered.map(r=>r.owner_user_id||r.author)).size+'</span>';
 $('searchCount').textContent=filtered.length+' найдено';
 $('pageInfo').textContent=filtered.length?(start+1)+'–'+Math.min(start+pageSize,filtered.length)+' из '+filtered.length:'0 из 0';
 $('prevPage').disabled=currentPage<=1;$('nextPage').disabled=currentPage>=pages;
 $('pagination').hidden=filtered.length===0;
 $('rows').innerHTML=visible.length?visible.map((r)=>'<tr><td>'+esc(r.date)+'</td><td>'+esc(r.object)+'</td><td>'+esc(r.employee)+'</td><td>'+esc(r.author)+'</td><td>'+esc(r.updated_at||'')+'</td><td><div class="actions"><button class="secondary" data-key="'+esc(r.record_key)+'">Открыть</button><button class="outline" data-download="'+esc(r.record_key)+'">Скачать</button><button class="danger" data-delete="'+esc(r.record_key)+'">Удалить</button></div></td></tr>').join(''):'<tr><td colspan="6" class="empty">По заданным условиям отчёты не найдены.</td></tr>';
 $('rows').querySelectorAll('button[data-key]').forEach(b=>b.addEventListener('click',()=>openRecord(b.dataset.key)));
 $('rows').querySelectorAll('button[data-download]').forEach(b=>b.addEventListener('click',()=>showDownload(b.dataset.download)));
 $('rows').querySelectorAll('button[data-delete]').forEach(b=>b.addEventListener('click',()=>deleteRecord(b.dataset.delete)));
}
async function deleteRecord(key){
 const record=allRecords.find(r=>r.record_key===key);
 if(!record)return;
 const label=[record.date,record.object,record.employee].filter(Boolean).join(' · ');
 if(!confirm('Удалить отчёт?\n'+label+'\n\nВосстановить запись будет нельзя.'))return;
 try{
  setStatus('Удаляем отчёт…');
  const response=await fetch('api.php?action=reports_delete',{
   method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},
   body:JSON.stringify({record_key:key,csrf:csrfToken})
  });
  const result=await response.json();
  if(!response.ok||!result.ok)throw new Error(result.error||'Не удалось удалить отчёт.');
  await load();
  setStatus('Отчёт удалён.');
 }catch(e){setStatus(e.message,true);}
}
function openRecord(key){
 selected=allRecords.find(r=>r.record_key===key);if(!selected)return;
 try{
  const source=Object.assign({},selected.data||{},{
   b0_to:selected.object||(selected.data&&selected.data.b0_to)||'',
   b0_fio:selected.employee||(selected.data&&selected.data.b0_fio)||'',
   b0_date:selected.date||(selected.data&&selected.data.b0_date)||''
  });
  const documentData=window.SBDocument.build(source,selected.dyn||{});
  const pageCss='.page{width:210mm;min-height:297mm;background:#fff;margin:24px auto;padding:20mm 25mm 25mm 30mm;box-shadow:0 8px 32px rgba(0,0,0,.12);font-family:"Times New Roman",Times,serif;font-size:12pt;line-height:1.5;color:#000}.page h1{font-size:12pt;font-weight:bold;text-align:center;margin:0 0 16pt;line-height:1.35}.page p{margin:0 0 5pt;text-indent:1.25cm;text-align:justify}.page p.no-indent{text-indent:0}.page strong{font-weight:bold}.page .ph{color:#000;font-weight:bold}.page .sec-title{margin-top:10pt}@media(max-width:850px){.page{width:100%;min-height:0;margin:0;padding:28px 22px;box-shadow:none}}';
  const html='<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>html,body{margin:0;padding:0;background:#eef2f2}'+pageCss+window.SBDocument.css()+'</style></head><body><div class="page">'+window.SBDocument.docHtml(documentData)+'</div><script>
(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,30000);document.addEventListener('visibilitychange',ping);})();
</script></body></html>';
  $('previewFrame').srcdoc=html;
  $('previewMeta').textContent=[selected.date,selected.object,selected.employee].filter(Boolean).join(' · ');
  $('previewModal').classList.add('open');$('previewModal').setAttribute('aria-hidden','false');
 }catch(e){setStatus('Не удалось сформировать предпросмотр: '+e.message,true);}
}
function closePreview(){$('previewModal').classList.remove('open');$('previewModal').setAttribute('aria-hidden','true');$('previewFrame').srcdoc='';}
$('closePreview').addEventListener('click',closePreview);
$('previewModal').addEventListener('click',e=>{if(e.target===$('previewModal'))closePreview();});
document.addEventListener('keydown',e=>{if(e.key==='Escape')closePreview();});
function showDownload(key){selected=allRecords.find(r=>r.record_key===key);if(!selected)return;$('downloadMeta').textContent=[selected.date,selected.object,selected.employee].filter(Boolean).join(' · ');$('downloadStatus').textContent='';$('downloadModal').classList.add('open');$('downloadModal').setAttribute('aria-hidden','false');}
function closeDownload(){$('downloadModal').classList.remove('open');$('downloadModal').setAttribute('aria-hidden','true');}
$('closeModal').addEventListener('click',closeDownload);
$('downloadModal').addEventListener('click',e=>{if(e.target===$('downloadModal'))closeDownload();});
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
 b8_checks:'Списания — проверок',b8_qty:'Списания — нарушений',b8_sum:'Списания — сумма, руб.',b9_count:'Количество служебных записок, шт.',
 b10_crim_qty:'Уголовные — нарушений',b10_crim_sum:'Уголовные — ущерб, руб.',b10_adm_qty:'Административные — нарушений',b10_adm_sum:'Административные — ущерб, руб.',b10_local_qty:'Разбор на месте — случаев',b10_local_sum:'Разбор на месте — ущерб, руб.',
 b11_post_cash:'Пост на кассах',b11_dfrom_cash:'Начало смены (кассы)',b11_from_cash:'Время начала (кассы)',b11_dto_cash:'Окончание смены (кассы)',b11_to_cash:'Время окончания (кассы)',
 b11_post_kso:'Пост КСО',b11_dfrom_kso:'Начало смены (КСО)',b11_from_kso:'Время начала (КСО)',b11_dto_kso:'Окончание смены (КСО)',b11_to_kso:'Время окончания (КСО)',
 b11_post_entr:'Пост на входе',b11_dfrom_entr:'Начало смены (вход)',b11_from_entr:'Время начала (вход)',b11_dto_entr:'Окончание смены (вход)',b11_to_entr:'Время окончания (вход)',
 b11_uch:'Проверка УЧО',b11_uch_fio:'ФИО сотрудника ЧОО без УЧО',b11_instr:'Инструктаж',b11_instr_topic:'Тема инструктажа'
};
const SECTIONS=[
 ['1. Проверка кассовых операций (удаление позиций из чека, удаление чека, возвраты, низкий вес товара)',['b1_pos_checks','b1_pos_qty','b1_pos_sum','b1_chek_checks','b1_chek_qty','b1_chek_sum','b1_ret_checks','b1_ret_qty','b1_ret_sum','b1_weight_checks','b1_weight_qty','b1_weight_sum']],
 ['2. Работа с FaceID, реагирование, пополнение базы данных',['b2_triggers','b2_worked','b2_db']],
 ['3. Контроль за перемещениями ТМЦ (внутри объекта, между объектами)',['b3_in_checks','b3_in_qty','b3_in_sum','b3_between_checks','b3_between_qty','b3_between_sum']],
 ['4. Контрольные проверки проведённых инвентаризаций операционной службой',['b4_checks','b4_dev_qty','b4_dev_sum']],
 ['5. Выгрузка виртуальных остатков, предоставление информации операционной службе для пересчёта',['b5_date','b5_positions','b5_sum','b5_sz_date','b5_copy']],
 ['6. Проверка качества поставки ТМЦ с РЦ',['b6_checks','b6_q_qty','b6_q_sum','b6_qty_qty','b6_qty_sum','b6_sg_qty','b6_sg_sum']],
 ['7. Проверка качества поставки ТМЦ от прямых поставщиков',['b7_checks','b7_q_qty','b7_q_sum','b7_qty_qty','b7_qty_sum','b7_sg_qty','b7_sg_sum']],
 ['8. Проверка качества списания ТМЦ',['b8_checks','b8_qty','b8_sum']],
 ['9. Количество служебных записок по выявленным нарушениям на ДГМ',['b9_count']],
 ['10. Хищения (уголовные, административные)',['b10_crim_qty','b10_crim_sum','b10_adm_qty','b10_adm_sum','b10_local_qty','b10_local_sum']],
 ['11. Работа ООО ЧОП «Артакс+»',['b11_post_cash','b11_dfrom_cash','b11_from_cash','b11_dto_cash','b11_to_cash','b11_post_kso','b11_dfrom_kso','b11_from_kso','b11_dto_kso','b11_to_kso','b11_post_entr','b11_dfrom_entr','b11_from_entr','b11_dto_entr','b11_to_entr','b11_uch','b11_uch_fio','b11_instr','b11_instr_topic']]
];
const COMMENT_KEYS=Array.from({length:11},(_,i)=>'b'+(i+1)+'_comment');
function recordSections(){
 const d=selected.data||{},dyn=selected.dyn||{},out=[];
 SECTIONS.forEach(([title,keys])=>{
  const lines=[];
  keys.forEach(k=>{const value=d[k];if(value!==undefined&&value!==null&&String(value).trim()!=='')lines.push((FIELD_LABELS[k]||k)+': '+String(value));});
  const dk=title.startsWith('4.')?'b4_inv':title.startsWith('6.')?'b6_sup':title.startsWith('7.')?'b7_sup':title.startsWith('8.')?'b8_sp':null;
  if(dk&&Array.isArray(dyn[dk]))dyn[dk].forEach((r,n)=>{
   const parts=Object.entries(r||{}).filter(([,v])=>v!==''&&v!=null).map(([k,v])=>({num:'Номер',date:'Дата',sum:'Сумма, руб.',cnt:'Проверено позиций',dev:'Расхождения, руб.'}[k]||k)+': '+v);
   if(parts.length)lines.push('Дополнительная запись '+(n+1)+': '+parts.join('; '));
  });
  if(lines.length)out.push({title,lines});
 });
 COMMENT_KEYS.forEach((k,i)=>{if(d[k]){let sec=out.find(x=>x.title.startsWith((i+1)+'.'));if(!sec){sec={title:(i+1)+'. Комментарий',lines:[]};out.push(sec);}sec.note=sec.note||[];sec.note.push(String(d[k]));}});
 return out;
}
function wordReportHtml(){
 const source=Object.assign({},selected.data||{},{
  b0_to:selected.object||(selected.data&&selected.data.b0_to)||'',
  b0_fio:selected.employee||(selected.data&&selected.data.b0_fio)||'',
  b0_date:selected.date||(selected.data&&selected.data.b0_date)||''
 });
 const documentData=window.SBDocument.build(source,selected.dyn||{});
 return window.SBDocument.wordHtml(documentData);
}
function exportRows(){
 if(!selected)return [];
 const rows=[['Поле','Значение'],['Дата',selected.date],['Объект',selected.object],['Сотрудник',selected.employee],['Автор записи',selected.author]];
 recordSections().forEach(sec=>{rows.push([sec.title,'']);sec.lines.forEach(line=>rows.push([line,'']));(sec.note||[]).forEach(note=>rows.push(['Комментарий: '+note,'']));});
 return rows;
}
$('word').addEventListener('click',()=>{
 if(!selected)return;
 try{
  const html=wordReportHtml();
  const blob=new Blob(['\ufeff',html],{type:'application/msword;charset=utf-8'});
  download(blob,'Отчёт_'+safe(selected.date)+'_'+safe(selected.employee)+'.doc');
 }catch(e){$('downloadStatus').textContent='Ошибка Word: '+e.message;}
});
$('excel').addEventListener('click',()=>{
 if(!selected)return;
 try{
  const source=Object.assign({},selected.data||{},{
   b0_to:selected.object||(selected.data&&selected.data.b0_to)||'',
   b0_fio:selected.employee||(selected.data&&selected.data.b0_fio)||'',
   b0_date:selected.date||(selected.data&&selected.data.b0_date)||''
  });
  const documentData=window.SBDocument.build(source,selected.dyn||{});
  const blob=window.SBDocument.excelBlob(documentData);
  download(blob,'Отчёт_'+safe(selected.date)+'_'+safe(selected.employee)+'.xlsx');
  $('downloadStatus').textContent='Excel-файл сформирован.';
 }catch(e){$('downloadStatus').textContent='Ошибка Excel: '+e.message;}
});
function safe(s){return String(s||'Отчёт').replace(/[^\p{L}\p{N}_-]+/gu,'_').slice(0,70);}
function download(blob,name){const url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download=name;document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1500);}
$('search').addEventListener('click',()=>{currentPage=1;load().catch(e=>setStatus(e.message,true));});
$('object').addEventListener('change',()=>{currentPage=1;load().catch(e=>setStatus(e.message,true));});
$('employee').addEventListener('change',()=>{currentPage=1;render();});
$('reportSearch').addEventListener('input',()=>{searchTerm=$('reportSearch').value;currentPage=1;render();});
$('pageSize').addEventListener('change',()=>{pageSize=Math.max(1,Number($('pageSize').value)||25);currentPage=1;render();});
$('prevPage').addEventListener('click',()=>{if(currentPage>1){currentPage--;render();}});
$('nextPage').addEventListener('click',()=>{currentPage++;render();});
load().catch(e=>{setStatus(e.message,true);$('rows').innerHTML='<tr><td colspan="6" class="empty">Не удалось загрузить архив.</td></tr>';});
})();
</script>
</body>
</html>