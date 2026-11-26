<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_name('sbapp_session');
session_start();
if (empty($_SESSION['sb_user_id'])) {
    header('Location: index.html');
    exit;
}
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Отчёты сотрудников — СБ</title>
<style>
:root{--ink:#172b3a;--muted:#6c7d89;--line:#e3ebef;--paper:#fff;--bg:#f3f7f8;--teal:#087f83;--teal-dark:#06696d;--soft:#e8f5f4;--shadow:0 12px 34px rgba(23,43,58,.08)}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.5 Inter,Segoe UI,Arial,sans-serif}
.top{background:#fff;border-bottom:1px solid var(--line)}.top-inner{max-width:1440px;margin:auto;padding:18px 28px;display:flex;align-items:center;justify-content:space-between;gap:18px}
.brand{display:flex;align-items:center;gap:13px}.logo{width:42px;height:42px;border-radius:13px;background:var(--teal);color:#fff;display:grid;place-items:center;font-weight:800;font-size:18px}.brand h1{font-size:20px;margin:0;line-height:1.2}.brand p{margin:4px 0 0;color:var(--muted);font-size:12px}
.actions{display:flex;gap:9px;flex-wrap:wrap}.btn{border:1px solid var(--line);background:#fff;color:var(--ink);border-radius:9px;padding:9px 14px;text-decoration:none;font:600 13px inherit;cursor:pointer}.btn.primary{background:var(--teal);border-color:var(--teal);color:#fff}.btn:hover{border-color:var(--teal);color:var(--teal)}.btn.primary:hover{background:var(--teal-dark);color:#fff}
main{max-width:1440px;margin:26px auto;padding:0 28px 40px}.intro{display:flex;justify-content:space-between;align-items:flex-end;gap:15px;margin-bottom:20px}.intro h2{margin:0;font-size:24px}.intro p{margin:5px 0 0;color:var(--muted)}
.filters,.panel,.stat{background:var(--paper);border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow)}.filters{padding:18px;display:grid;grid-template-columns:repeat(4,minmax(150px,1fr)) auto;gap:12px;align-items:end;margin-bottom:18px}
.field label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin:0 0 6px}.field input,.field select{width:100%;height:40px;border:1px solid #d7e2e7;border-radius:8px;padding:0 10px;background:#fff;color:var(--ink);font:inherit;min-width:0}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;margin-bottom:18px}.stat{padding:16px 18px}.stat span{display:block;color:var(--muted);font-size:12px;font-weight:600}.stat strong{display:block;font-size:26px;margin-top:3px}
.panel{overflow:hidden;margin-bottom:18px}.panel-head{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px}.panel-head h3{font-size:16px;margin:0}.panel-head small{color:var(--muted)}
.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:720px}th,td{text-align:left;padding:11px 14px;border-bottom:1px solid var(--line);vertical-align:middle}th{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);background:#f8fbfc;white-space:nowrap}td{font-size:13px}tbody tr:last-child td{border-bottom:0}.progress{height:7px;width:100px;border-radius:10px;background:#e8eef0;overflow:hidden;display:inline-block;vertical-align:middle;margin-right:7px}.progress i{height:100%;display:block;background:var(--teal);border-radius:10px}.empty{padding:34px 15px;text-align:center;color:var(--muted)}
.status{color:var(--teal);font-weight:700}.role{font-size:12px;color:var(--muted);padding:5px 9px;border-radius:20px;background:#f1f5f6}
@media(max-width:900px){.filters{grid-template-columns:repeat(2,minmax(0,1fr))}.top-inner{align-items:flex-start}.stats{grid-template-columns:repeat(3,1fr)}}
@media(max-width:600px){.top-inner{padding:14px 16px;display:block}.actions{margin-top:13px}.intro{display:block}main{padding:0 14px 28px;margin:20px auto}.filters{grid-template-columns:1fr 1fr;padding:13px}.stats{grid-template-columns:1fr}.stat{padding:12px 15px}.stat strong{font-size:22px}.panel-head{align-items:flex-start;flex-direction:column}.brand h1{font-size:18px}}
</style>
</head>
<body>
<header class="top"><div class="top-inner">
  <div class="brand"><div class="logo">СБ</div><div><h1>Ежедневный отчёт СБ</h1><p>Группа БФ Волгоград 1</p></div></div>
  <div class="actions"><a class="btn" href="index.html">← К форме отчёта</a><button class="btn" id="logoutBtn" type="button">Выйти</button></div>
</div></header>
<main>
  <div class="intro"><div><h2>Отчёты сотрудников</h2><p id="roleInfo">Загрузка доступных данных…</p></div><span class="role" id="periodLabel">Сводная по сохранённым отчётам</span></div>
  <section class="filters">
    <div class="field"><label for="dateFrom">Дата с</label><input type="date" id="dateFrom" value="<?php echo htmlspecialchars($monthStart, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="field"><label for="dateTo">Дата по</label><input type="date" id="dateTo" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="field"><label for="objectFilter">Торговый объект</label><select id="objectFilter"><option value="0">Все доступные объекты</option></select></div>
    <div class="field"><label for="employeeFilter">Поиск сотрудника</label><input type="search" id="employeeFilter" placeholder="ФИО сотрудника"></div>
    <button class="btn primary" id="applyBtn" type="button">Показать</button>
  </section>
  <section class="stats">
    <div class="stat"><span>Сохранено отчётов</span><strong id="statReports">—</strong></div>
    <div class="stat"><span>Сотрудников</span><strong id="statEmployees">—</strong></div>
    <div class="stat"><span>Торговых объектов</span><strong id="statObjects">—</strong></div>
  </section>
  <section class="panel">
    <div class="panel-head"><div><h3>Сводка по сотрудникам</h3><small>Количество отчётов и среднее заполнение за выбранный период</small></div><button class="btn" id="exportSummary" type="button">Выгрузить сводку в Excel</button></div>
    <div class="table-wrap"><table><thead><tr><th>Сотрудник</th><th>Отчётов</th><th>Объектов</th><th>Последний отчёт</th><th>Среднее заполнение</th></tr></thead><tbody id="summaryBody"><tr><td colspan="5" class="empty">Загрузка…</td></tr></tbody></table></div>
  </section>
  <section class="panel">
    <div class="panel-head"><div><h3>Список отчётов</h3><small>Одна строка — один сохранённый отчёт по объекту и дате</small></div><button class="btn" id="exportDetails" type="button">Выгрузить список в Excel</button></div>
    <div class="table-wrap"><table><thead><tr><th>Дата</th><th>Торговый объект</th><th>Сотрудник</th><th>Кем сохранён</th><th>Заполнение</th><th>Последнее обновление</th></tr></thead><tbody id="detailsBody"><tr><td colspan="6" class="empty">Загрузка…</td></tr></tbody></table></div>
  </section>
</main>
<script>
(function(){
'use strict';
var records=[], filtered=[], user=null, objects=[];
var $=function(id){return document.getElementById(id);};
function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
function pct(r){return r.total>0?Math.max(0,Math.min(100,Math.round(r.done/r.total*100))):0;}
function roleName(r){return r==='admin'?'Руководитель':r==='moderator'?'Менеджер по безопасности':'Специалист по безопасности';}
function fmtDate(v){if(!v)return '—';var p=String(v).slice(0,10).split('-');return p.length===3?p[2]+'.'+p[1]+'.'+p[0]:v;}
function fmtTime(v){if(!v)return '—';var d=new Date(v);return isNaN(d.getTime())?'—':d.toLocaleString('ru-RU');}
function csvCell(v){return '"'+String(v==null?'':v).replace(/"/g,'""')+'"';}
function downloadCsv(name,rows){var csv='\ufeff'+rows.map(function(row){return row.map(csvCell).join(';');}).join('\r\n');var blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});var url=URL.createObjectURL(blob);var a=document.createElement('a');a.href=url;a.download=name;a.click();setTimeout(function(){URL.revokeObjectURL(url);},1000);}
function makeSummary(list){var map=Object.create(null);list.forEach(function(r){var name=r.employee||'Сотрудник не указан';if(!map[name])map[name]={name:name,count:0,objects:Object.create(null),last:'',sum:0,filled:0};var x=map[name];x.count++;x.objects[r.object||''] = true;if(r.date>x.last)x.last=r.date;x.sum+=pct(r);x.filled++;});return Object.keys(map).map(function(k){var x=map[k];return {name:x.name,count:x.count,objects:Object.keys(x.objects).filter(Boolean).length,last:x.last,average:x.filled?Math.round(x.sum/x.filled):0};}).sort(function(a,b){return a.name.localeCompare(b.name,'ru');});}
function render(){
 var query=$('employeeFilter').value.trim().toLocaleLowerCase('ru');
 filtered=records.filter(function(r){return !query||(r.employee||'').toLocaleLowerCase('ru').includes(query);});
 $('statReports').textContent=filtered.length;
 $('statEmployees').textContent=new Set(filtered.map(function(r){return r.employee||'Сотрудник не указан';})).size;
 $('statObjects').textContent=new Set(filtered.map(function(r){return r.object||'';}).filter(Boolean)).size;
 var sums=makeSummary(filtered), sb=$('summaryBody');
 sb.innerHTML=sums.length?sums.map(function(x){return '<tr><td><strong>'+esc(x.name)+'</strong></td><td>'+x.count+'</td><td>'+x.objects+'</td><td>'+fmtDate(x.last)+'</td><td><span class="progress"><i style="width:'+x.average+'%"></i></span>'+x.average+'%</td></tr>';}).join(''):'<tr><td colspan="5" class="empty">За выбранный период отчёты не найдены</td></tr>';
 var db=$('detailsBody');
 db.innerHTML=filtered.length?filtered.map(function(r){var p=pct(r);return '<tr><td>'+fmtDate(r.date)+'</td><td>'+esc(r.object||'—')+'</td><td><strong>'+esc(r.employee||'Не указан')+'</strong></td><td>'+esc(r.author||'—')+'</td><td><span class="progress"><i style="width:'+p+'%"></i></span>'+p+'%</td><td>'+fmtTime(r.updated_at)+'</td></tr>';}).join(''):'<tr><td colspan="6" class="empty">За выбранный период отчёты не найдены</td></tr>';
}
async function load(){
 var from=$('dateFrom').value,to=$('dateTo').value;
 if(!from||!to||from>to){alert('Проверьте даты периода.');return;}
 $('applyBtn').disabled=true;$('applyBtn').textContent='Загрузка…';
 try{
  var params=new URLSearchParams({from:from,to:to,object_id:$('objectFilter').value||'0'});
  var res=await fetch('api.php?action=reports_data&'+params.toString(),{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});
  var data=await res.json();
  if(res.status===401){location.href='index.html';return;}
  if(!res.ok||!data.ok)throw new Error(data.error||'Не удалось загрузить отчёты.');
  window.__SB_CSRF=data.csrf||'';user=data.user;objects=data.objects||[];records=data.records||[];
  $('roleInfo').textContent=roleName(user.role)+' · '+(user.object_names||[]).join(', ');
  var sel=$('objectFilter'),current=sel.value;
  sel.innerHTML='<option value="0">Все доступные объекты</option>'+objects.map(function(o){return '<option value="'+Number(o.id)+'">'+esc(o.name)+'</option>';}).join('');
  if(objects.some(function(o){return String(o.id)===current;}))sel.value=current;
  render();
 }catch(e){alert(e.message||'Ошибка загрузки отчётов.');}
 finally{$('applyBtn').disabled=false;$('applyBtn').textContent='Показать';}
}
function exportSummary(){var rows=[['Сотрудник','Количество отчётов','Количество объектов','Последний отчёт','Среднее заполнение, %']];makeSummary(filtered).forEach(function(x){rows.push([x.name,x.count,x.objects,fmtDate(x.last),x.average]);});downloadCsv('Сводка_по_сотрудникам.csv',rows);}
function exportDetails(){var rows=[['Дата','Торговый объект','Сотрудник','Кем сохранён','Заполнено полей','Всего полей','Заполнение, %','Обновлено']];filtered.forEach(function(r){rows.push([fmtDate(r.date),r.object,r.employee,r.author,r.done,r.total,pct(r),fmtTime(r.updated_at)]);});downloadCsv('Отчёты_сотрудников.csv',rows);}
$('applyBtn').addEventListener('click',load);$('employeeFilter').addEventListener('input',render);$('objectFilter').addEventListener('change',load);$('exportSummary').addEventListener('click',exportSummary);$('exportDetails').addEventListener('click',exportDetails);
$('logoutBtn').addEventListener('click',async function(){try{await fetch('api.php?action=logout',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:window.__SB_CSRF||''})});}catch(e){}location.href='index.html';});
load();
})();
</script>
</body>
</html>
