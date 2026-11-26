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
.actions{display:flex;gap:9px;flex-wrap:wrap}.btn{border:1px solid var(--line);background:#fff;color:var(--ink);border-radius:9px;padding:9px 14px;text-decoration:none;font:600 13px/1.2 Segoe UI,Arial,sans-serif;cursor:pointer}.btn.primary{background:var(--teal);border-color:var(--teal);color:#fff}.btn:hover{border-color:var(--teal);color:var(--teal)}.btn.primary:hover{background:var(--teal-dark);color:#fff}
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
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
</head>
<body>
<header class="top"><div class="top-inner">
  <div class="brand"><div class="logo">СБ</div><div><h1>Ежедневный отчёт СБ</h1><p>Группа БФ Волгоград 1</p></div></div>
  <div class="actions"><a class="btn" href="index.html">← К форме отчёта</a><button class="btn" id="logoutBtn" type="button">Выйти</button></div>
</div></header>
<main>
  <div class="intro"><div><h2>Отчёты сотрудников</h2><p id="roleInfo">Загрузка доступных данных…</p></div><div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap"><span class="role" id="periodLabel">Сводная по сохранённым отчётам</span><button class="btn primary" id="exportWorkbook" type="button">↓ Скачать полный Excel-отчёт</button></div></div>
  <section class="filters">
    <div class="field"><label for="periodType">Период отчёта</label><select id="periodType"><option value="month">За месяц</option><option value="quarter">За квартал</option><option value="year">За год</option><option value="custom">Произвольный период</option></select></div>
    <div class="field"><label for="periodYear">Год</label><select id="periodYear"></select></div>
    <div class="field" id="unitField"><label for="periodUnit">Месяц</label><select id="periodUnit"></select></div>
    <div class="field"><label for="objectFilter">Торговый объект</label><select id="objectFilter"><option value="0">Все доступные объекты</option></select></div>
    <div class="field"><label for="employeeFilter">Поиск сотрудника</label><input type="search" id="employeeFilter" placeholder="ФИО сотрудника"></div>
    <div class="field"><label for="dateFrom">Дата с (для произвольного периода)</label><input type="date" id="dateFrom" value="<?php echo htmlspecialchars($monthStart, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="field"><label for="dateTo">Дата по</label><input type="date" id="dateTo" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <button class="btn primary" id="applyBtn" type="button">Показать</button>
  </section>
  <section class="stats">
    <div class="stat"><span>Сохранено отчётов</span><strong id="statReports">—</strong></div>
    <div class="stat"><span>Сотрудников</span><strong id="statEmployees">—</strong></div>
    <div class="stat"><span>Торговых объектов</span><strong id="statObjects">—</strong></div>
  </section>
  <section class="panel">
    <div class="panel-head"><div><h3>Сводка по сотрудникам</h3><small>Количество отчётов и среднее заполнение за выбранный период</small></div><button class="btn" id="exportSummary" type="button">Скачать лист Excel</button></div>
    <div class="table-wrap"><table><thead><tr><th>Сотрудник</th><th>Отчётов</th><th>Объектов</th><th>Последний отчёт</th><th>Среднее заполнение</th></tr></thead><tbody id="summaryBody"><tr><td colspan="5" class="empty">Загрузка…</td></tr></tbody></table></div>
  </section>
  <section class="panel">
    <div class="panel-head"><div><h3>Сводные показатели по всем выбранным объектам</h3><small>Итоги за период: количество случаев отдельно от сумм выявленных нарушений</small></div><button class="btn" id="exportMetrics" type="button">Скачать сводку Excel</button></div>
    <div class="table-wrap"><table><thead><tr><th>Направление работы</th><th>Показатель</th><th>Итого</th><th>Ед. изм.</th><th>Отчётов с данными</th></tr></thead><tbody id="metricsBody"><tr><td colspan="5" class="empty">Загрузка…</td></tr></tbody></table></div>
  </section>
  <section class="panel">
    <div class="panel-head"><div><h3>Показатели в разрезе объектов</h3><small>Каждый объект показан отдельными строками — можно сравнивать результаты между объектами</small></div></div>
    <div class="table-wrap"><table><thead><tr><th>Торговый объект</th><th>Направление работы</th><th>Показатель</th><th>Значение</th><th>Ед. изм.</th></tr></thead><tbody id="objectMetricsBody"><tr><td colspan="5" class="empty">Загрузка…</td></tr></tbody></table></div>
  </section>
  <section class="panel">
    <div class="panel-head"><div><h3>Содержание отчётов</h3><small>Все значения, внесённые в форму, включая комментарии и дополнительные строки</small></div><button class="btn" id="exportDetails" type="button">Скачать лист Excel</button></div>
    <div class="table-wrap"><table><thead><tr><th>Дата</th><th>Торговый объект</th><th>Сотрудник</th><th>Кем сохранён</th><th>Заполнение</th><th>Последнее обновление</th></tr></thead><tbody id="detailsBody"><tr><td colspan="6" class="empty">Загрузка…</td></tr></tbody></table></div>
  </section>
</main>
<script>
(function(){
'use strict';
var records=[],filtered=[],user=null,objects=[],fieldLabels={};
 var $=function(id){return document.getElementById(id);};
 function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
 function pct(r){return r.total>0?Math.max(0,Math.min(100,Math.round(r.done/r.total*100))):0;}
 function roleName(r){return r==='admin'?'Руководитель':r==='moderator'?'Менеджер по безопасности':'Специалист по безопасности';}
 function fmtDate(v){if(!v)return '—';var p=String(v).slice(0,10).split('-');return p.length===3?p[2]+'.'+p[1]+'.'+p[0]:v;}
 function fmtTime(v){if(!v)return '—';var d=new Date(v);return isNaN(d.getTime())?'—':d.toLocaleString('ru-RU');}
 function csvCell(v){if(v&&typeof v==='object')v=JSON.stringify(v);return '"'+String(v==null?'':v).replace(/"/g,'""')+'"';}
 function downloadCsv(name,rows){var csv='\\ufeff'+rows.map(function(row){return row.map(csvCell).join(';');}).join('\\r\\n');var blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});var url=URL.createObjectURL(blob);var a=document.createElement('a');a.href=url;a.download=name;a.click();setTimeout(function(){URL.revokeObjectURL(url);},1000);}
 function makeSummary(list){var map=Object.create(null);list.forEach(function(r){var name=r.employee||'Сотрудник не указан';if(!map[name])map[name]={name:name,count:0,objects:Object.create(null),last:'',sum:0};var x=map[name];x.count++;x.objects[r.object||'']=true;if(r.date>x.last)x.last=r.date;x.sum+=pct(r);});return Object.keys(map).map(function(k){var x=map[k];return {name:x.name,count:x.count,objects:Object.keys(x.objects).filter(Boolean).length,last:x.last,average:x.count?Math.round(x.sum/x.count):0};}).sort(function(a,b){return a.name.localeCompare(b.name,'ru');});}
 function allValues(r){var out={};Object.keys(r.data||{}).forEach(function(k){out[k]=r.data[k];});Object.keys(r.dyn||{}).forEach(function(k){out['Доп. таблица: '+k]=r.dyn[k];});return out;}
 var METRICS=[
  ['1. Кассовые операции','Отмена позиций в чеке — количество','b1_pos_qty','шт.'],
  ['1. Кассовые операции','Отмена позиций в чеке — сумма нарушений','b1_pos_sum','руб.'],
  ['1. Кассовые операции','Анулирование чека — количество','b1_chek_qty','шт.'],
  ['1. Кассовые операции','Анулирование чека — сумма нарушений','b1_chek_sum','руб.'],
  ['1. Кассовые операции','Фиктивные возвраты — количество','b1_ret_qty','шт.'],
  ['1. Кассовые операции','Фиктивные возвраты — сумма нарушений','b1_ret_sum','руб.'],
  ['1. Кассовые операции','Продажа с заниженным весом — количество','b1_weight_qty','шт.'],
  ['1. Кассовые операции','Продажа с заниженным весом — сумма нарушений','b1_weight_sum','руб.'],
  ['2. FaceID','Срабатывания','b2_triggers','шт.'],
  ['2. FaceID','Отработано реагированием','b2_worked','шт.'],
  ['2. FaceID','Пополнено базы данных','b2_db','чел.'],
  ['3. Перемещения ТМЦ','Проверки внутри объекта','b3_in_checks','проверок'],
  ['3. Перемещения ТМЦ','Выявлено нарушений внутри объекта','b3_in_qty','шт.'],
  ['3. Перемещения ТМЦ','Сумма нарушений внутри объекта','b3_in_sum','руб.'],
  ['3. Перемещения ТМЦ','Проверки между объектами','b3_between_checks','проверок'],
  ['3. Перемещения ТМЦ','Выявлено нарушений между объектами','b3_between_qty','шт.'],
  ['3. Перемещения ТМЦ','Сумма нарушений между объектами','b3_between_sum','руб.'],
  ['4. Инвентаризации','Контрольные проверки','b4_checks','проверок'],
  ['4. Инвентаризации','Выявленные расхождения','b4_dev_qty','шт.'],
  ['4. Инвентаризации','Сумма расхождений','b4_dev_sum','руб.'],
  ['5. Виртуальные остатки','Позиций передано на пересчёт','b5_positions','ед.'],
  ['5. Виртуальные остатки','Сумма по выгрузке','b5_sum','руб.'],
  ['6. Поставки с РЦ','Проверки поставок','b6_checks','проверок'],
  ['6. Поставки с РЦ','Нарушения по качеству','b6_q_qty','шт.'],
  ['6. Поставки с РЦ','Сумма нарушений по качеству','b6_q_sum','руб.'],
  ['6. Поставки с РЦ','Нарушения по количеству','b6_qty_qty','шт.'],
  ['6. Поставки с РЦ','Сумма нарушений по количеству','b6_qty_sum','руб.'],
  ['6. Поставки с РЦ','Нарушения по срокам годности','b6_sg_qty','шт.'],
  ['6. Поставки с РЦ','Сумма нарушений по срокам годности','b6_sg_sum','руб.'],
  ['7. Прямые поставщики','Проверки поставок','b7_checks','проверок'],
  ['7. Прямые поставщики','Нарушения по качеству','b7_q_qty','шт.'],
  ['7. Прямые поставщики','Сумма нарушений по качеству','b7_q_sum','руб.'],
  ['7. Прямые поставщики','Нарушения по количеству','b7_qty_qty','шт.'],
  ['7. Прямые поставщики','Сумма нарушений по количеству','b7_qty_sum','руб.'],
  ['7. Прямые поставщики','Нарушения по срокам годности','b7_sg_qty','шт.'],
  ['7. Прямые поставщики','Сумма нарушений по срокам годности','b7_sg_sum','руб.'],
  ['8. Списание ТМЦ','Проверки качества списания','b8_checks','проверок'],
  ['8. Списание ТМЦ','Выявленные нарушения','b8_qty','шт.'],
  ['8. Списание ТМЦ','Сумма выявленных нарушений','b8_sum','руб.'],
  ['9. Служебные записки','Количество служебных записок','b9_count','шт.'],
  ['10. Правонарушения','Уголовные преступления — количество','b10_crim_qty','случаев'],
  ['10. Правонарушения','Уголовные преступления — сумма','b10_crim_sum','руб.'],
  ['10. Правонарушения','Административные правонарушения — количество','b10_adm_qty','случаев'],
  ['10. Правонарушения','Административные правонарушения — сумма','b10_adm_sum','руб.'],
  ['10. Правонарушения','Разбор на месте — количество','b10_local_qty','случаев'],
  ['10. Правонарушения','Разбор на месте — сумма','b10_local_sum','руб.']
 ];
 function numericValue(raw){if(raw===null||raw===undefined||raw==='')return null;if(typeof raw==='number')return Number.isFinite(raw)?raw:null;var s=String(raw).replace(/\u00a0/g,' ').trim();if(!s||s==='—'||s==='-')return null;s=s.replace(/\s/g,'').replace(',','.').replace(/руб\.?/gi,'');if(!/^[-+]?(?:\d+\.?\d*|\.\d+)$/.test(s))return null;var n=Number(s);return Number.isFinite(n)?n:null;}
 function metricRows(list){return METRICS.map(function(def){var total=0,count=0;list.forEach(function(r){var n=numericValue((r.data||{})[def[2]]);if(n!==null){total+=n;count++;}});return {section:def[0],label:def[1],key:def[2],unit:def[3],total:total,count:count};}).filter(function(x){return x.count>0;});}
 function objectMetricRows(list){var groups=Object.create(null);list.forEach(function(r){var name=r.object||'Объект не указан';if(!groups[name])groups[name]=[];groups[name].push(r);});var out=[];Object.keys(groups).sort(function(a,b){return a.localeCompare(b,'ru');}).forEach(function(name){metricRows(groups[name]).forEach(function(m){out.push({object:name,section:m.section,label:m.label,unit:m.unit,total:m.total,count:m.count});});});return out;}
 function fmtMetric(n){return Number(n).toLocaleString('ru-RU',{maximumFractionDigits:2});}

 function render(){var q=$('employeeFilter').value.trim().toLocaleLowerCase('ru');filtered=records.filter(function(r){return !q||(r.employee||'').toLocaleLowerCase('ru').includes(q);});
 $('statReports').textContent=filtered.length;$('statEmployees').textContent=new Set(filtered.map(function(r){return r.employee||'Сотрудник не указан';})).size;$('statObjects').textContent=new Set(filtered.map(function(r){return r.object||'';}).filter(Boolean)).size;
 var sums=makeSummary(filtered);$('summaryBody').innerHTML=sums.length?sums.map(function(x){return '<tr><td><strong>'+esc(x.name)+'</strong></td><td>'+x.count+'</td><td>'+x.objects+'</td><td>'+fmtDate(x.last)+'</td><td><span class="progress"><i style="width:'+x.average+'%"></i></span>'+x.average+'%</td></tr>';}).join(''):'<tr><td colspan="5" class="empty">За выбранный период отчёты не найдены</td></tr>';
 var metrics=metricRows(filtered);$('metricsBody').innerHTML=metrics.length?metrics.map(function(x){return '<tr><td>'+esc(x.section)+'</td><td>'+esc(x.label)+'</td><td><strong>'+fmtMetric(x.total)+'</strong></td><td>'+esc(x.unit)+'</td><td>'+x.count+'</td></tr>';}).join(''):'<tr><td colspan="5" class="empty">За выбранный период нет данных по показателям</td></tr>';
 var byObject=objectMetricRows(filtered);$('objectMetricsBody').innerHTML=byObject.length?byObject.map(function(x){return '<tr><td><strong>'+esc(x.object)+'</strong></td><td>'+esc(x.section)+'</td><td>'+esc(x.label)+'</td><td><strong>'+fmtMetric(x.total)+'</strong></td><td>'+esc(x.unit)+'</td></tr>';}).join(''):'<tr><td colspan="5" class="empty">Нет данных по выбранным объектам</td></tr>';
 $('detailsBody').innerHTML=filtered.length?filtered.map(function(r){var p=pct(r);return '<tr><td>'+fmtDate(r.date)+'</td><td>'+esc(r.object||'—')+'</td><td><strong>'+esc(r.employee||'Не указан')+'</strong></td><td>'+esc(r.author||'—')+'</td><td><span class="progress"><i style="width:'+p+'%"></i></span>'+p+'%</td><td>'+fmtTime(r.updated_at)+'</td></tr>';}).join(''):'<tr><td colspan="6" class="empty">За выбранный период отчёты не найдены</td></tr>';
 $('periodLabel').textContent=$('periodType').selectedOptions[0].text+' · '+$('dateFrom').value+' — '+$('dateTo').value;}
 function pad(n){return String(n).padStart(2,'0');}
 function fillUnits(){var t=$('periodType').value,box=$('unitField'),sel=$('periodUnit');sel.innerHTML='';if(t==='year'||t==='custom'){box.style.display='none';return;}box.style.display='';if(t==='month'){$('unitField').querySelector('label').textContent='Месяц';['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'].forEach(function(x,i){sel.add(new Option(x,i+1));});sel.value=new Date().getMonth()+1;}else{$('unitField').querySelector('label').textContent='Квартал';for(var q=1;q<=4;q++)sel.add(new Option('Квартал '+q,q));sel.value=Math.floor(new Date().getMonth()/3)+1;}}
 function syncDates(){var t=$('periodType').value;if(t==='custom')return;var y=Number($('periodYear').value),from,to;if(t==='year'){from=y+'-01-01';to=y+'-12-31';}else if(t==='quarter'){var q=Number($('periodUnit').value),m=(q-1)*3,last=new Date(y,m+3,0);from=y+'-'+pad(m+1)+'-01';to=y+'-'+pad(last.getMonth()+1)+'-'+pad(last.getDate());}else{var mo=Number($('periodUnit').value),last=new Date(y,mo,0);from=y+'-'+pad(mo)+'-01';to=y+'-'+pad(mo)+'-'+pad(last.getDate());}$('dateFrom').value=from;$('dateTo').value=to;}
 function initPeriod(){var y=$('periodYear'),now=new Date();for(var v=now.getFullYear()+1;v>=now.getFullYear()-8;v--)y.add(new Option(v,v));y.value=now.getFullYear();fillUnits();$('periodType').addEventListener('change',function(){fillUnits();syncDates();});y.addEventListener('change',syncDates);$('periodUnit').addEventListener('change',syncDates);['dateFrom','dateTo'].forEach(function(id){$(id).addEventListener('change',function(){$('periodType').value='custom';fillUnits();});});syncDates();}
 async function load(){var from=$('dateFrom').value,to=$('dateTo').value;if(!from||!to||from>to){alert('Проверьте даты периода.');return;}$('applyBtn').disabled=true;$('applyBtn').textContent='Загрузка…';try{var params=new URLSearchParams({from:from,to:to,object_id:$('objectFilter').value||'0'});var res=await fetch('api.php?action=reports_data&'+params.toString(),{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});var data=await res.json();if(res.status===401){location.href='index.html';return;}if(!res.ok||!data.ok)throw new Error(data.error||'Не удалось загрузить отчёты.');window.__SB_CSRF=data.csrf||'';user=data.user;objects=data.objects||[];records=data.records||[];$('roleInfo').textContent=roleName(user.role)+' · '+(user.object_names||[]).join(', ');var sel=$('objectFilter'),cur=sel.value;sel.innerHTML='<option value="0">Все доступные объекты</option>'+objects.map(function(o){return '<option value="'+Number(o.id)+'">'+esc(o.name)+'</option>';}).join('');if(objects.some(function(o){return String(o.id)===cur;}))sel.value=cur;render();}catch(e){alert(e.message||'Ошибка загрузки отчётов.');}finally{$('applyBtn').disabled=false;$('applyBtn').textContent='Показать';}}
 function safeCell(v){if(v===null||v===undefined)return '';if(typeof v==='object')return JSON.stringify(v);return v;}
 function periodFilePart(){return String($('dateFrom').value||'')+'_'+String($('dateTo').value||'');}
 function addStyledSheet(wb,name,title,subtitle,headers,rows,widths){
   var ws=wb.addWorksheet(name,{views:[{state:'frozen',ySplit:4}]});
   var n=Math.max(headers.length,1);ws.mergeCells(1,1,1,n);ws.getCell(1,1).value=title;
   ws.getRow(1).height=30;ws.getCell(1,1).font={name:'Aptos Display',size:16,bold:true,color:{argb:'FFFFFFFF'}};
   ws.getCell(1,1).fill={type:'pattern',pattern:'solid',fgColor:{argb:'FF087F83'}};
   ws.mergeCells(2,1,2,n);ws.getCell(2,1).value=subtitle;
   ws.getRow(2).height=23;ws.getCell(2,1).font={name:'Aptos',size:10,color:{argb:'FF526775'},italic:true};
   ws.getRow(3).height=8;
   var hr=ws.getRow(4);headers.forEach(function(h,i){hr.getCell(i+1).value=h;});
   hr.height=32;hr.eachCell(function(c){c.font={name:'Aptos',size:10,bold:true,color:{argb:'FFFFFFFF'}};c.fill={type:'pattern',pattern:'solid',fgColor:{argb:'FF164E63'}};c.alignment={vertical:'middle',horizontal:'center',wrapText:true};c.border={bottom:{style:'medium',color:{argb:'FF087F83'}}};});
   rows.forEach(function(row){ws.addRow(row.map(safeCell));});
   for(var ri=5;ri<=ws.rowCount;ri++){var rr=ws.getRow(ri);rr.height=30;rr.eachCell(function(c){c.font={name:'Aptos',size:10,color:{argb:'FF203642'}};c.alignment={vertical:'top',wrapText:true};c.border={bottom:{style:'hair',color:{argb:'FFDCE6EA'}}};if(ri%2===0)c.fill={type:'pattern',pattern:'solid',fgColor:{argb:'FFF2F8F8'}};});}
   widths.forEach(function(w,i){ws.getColumn(i+1).width=w;});
   if(rows.length)ws.autoFilter={from:{row:4,column:1},to:{row:4+rows.length,column:headers.length}};
   ws.pageSetup={orientation:headers.length>6?'landscape':'portrait',fitToPage:true,fitToWidth:1,fitToHeight:0};
   ws.properties.defaultRowHeight=20;
   return ws;
 }
 async function downloadXlsx(filename,specs){
   if(!window.ExcelJS){alert('Не удалось загрузить модуль Excel. Проверьте подключение к интернету и обновите страницу.');return;}
   var wb=new ExcelJS.Workbook();wb.creator='Система отчётности СБ';wb.subject='Отчёты сотрудников';wb.created=new Date();wb.modified=new Date();
   specs.forEach(function(x){addStyledSheet(wb,x.name,x.title,x.subtitle,x.headers,x.rows,x.widths);});
   try{var buffer=await wb.xlsx.writeBuffer();var blob=new Blob([buffer],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});var url=URL.createObjectURL(blob);var a=document.createElement('a');a.href=url;a.download=filename;a.click();setTimeout(function(){URL.revokeObjectURL(url);},1500);}
   catch(e){console.error(e);alert('Не удалось сформировать Excel-файл. Попробуйте ещё раз.');}
 }
 function summarySpec(){var rows=makeSummary(filtered).map(function(x){return [x.name,x.count,x.objects,fmtDate(x.last),x.average/100];});return {name:'Сводка сотрудников',title:'СВОДКА ПО СОТРУДНИКАМ',subtitle:'Период: '+fmtDate($('dateFrom').value)+' — '+fmtDate($('dateTo').value)+' · Сформировано: '+new Date().toLocaleString('ru-RU'),headers:['Сотрудник','Количество отчётов','Количество объектов','Последний отчёт','Среднее заполнение'],rows:rows,widths:[34,20,20,20,22]};}
 function metricsSpec(){var rows=metricRows(filtered).map(function(x){return [x.section,x.label,x.total,x.unit,x.count];});return {name:'Сводка показателей',title:'СВОДНЫЕ ПОКАЗАТЕЛИ ПО ВЫБРАННЫМ ОБЪЕКТАМ',subtitle:'Суммируются только однотипные показатели формы; количество, проверки и рублёвые суммы показаны раздельно.',headers:['Направление работы','Показатель','Итого за период','Ед. изм.','Отчётов с данными'],rows:rows,widths:[28,46,20,14,20]};}
 function objectMetricsSpec(){var rows=objectMetricRows(filtered).map(function(x){return [x.object,x.section,x.label,x.total,x.unit,x.count];});return {name:'По объектам',title:'ПОКАЗАТЕЛИ В РАЗРЕЗЕ ТОРГОВЫХ ОБЪЕКТОВ',subtitle:'Каждая строка содержит один показатель одного объекта. Можно фильтровать и сравнивать объекты.',headers:['Торговый объект','Направление работы','Показатель','Значение','Ед. изм.','Отчётов с данными'],rows:rows,widths:[30,28,44,18,14,20]};}
 function detailsSpec(){var keys=Object.create(null);filtered.forEach(function(r){Object.keys(allValues(r)).forEach(function(k){keys[k]=true;});});var fields=Object.keys(keys).sort(function(a,b){return (fieldLabels[a]||a).localeCompare(fieldLabels[b]||b,'ru');});var headers=['Дата','Торговый объект','Сотрудник','Кем сохранён','Заполнено полей','Всего полей'].concat(fields.map(function(k){return fieldLabels[k]||k;}));var rows=filtered.map(function(r){var vals=allValues(r);return [fmtDate(r.date),r.object,r.employee,r.author,r.done,r.total].concat(fields.map(function(k){return vals[k]===undefined?'':safeCell(vals[k]);}));});return {name:'Полные отчёты',title:'ПОЛНЫЕ ДАННЫЕ ОТЧЁТОВ',subtitle:'Одна строка — один самостоятельный отчёт сотрудника. Включены поля формы и дополнительные данные.',headers:headers,rows:rows,widths:[14,28,28,26,16,14].concat(fields.map(function(){return 24;}))};}
 function dynamicSpec(){var rows=[];filtered.forEach(function(r){Object.keys(r.dyn||{}).forEach(function(k){rows.push([fmtDate(r.date),r.object,r.employee,k,safeCell(r.dyn[k])]);});});return {name:'Дополнительные данные',title:'ДОПОЛНИТЕЛЬНЫЕ ТАБЛИЦЫ И СТРОКИ',subtitle:'Содержимое динамических таблиц, сохранённых вместе с отчётами.',headers:['Дата','Торговый объект','Сотрудник','Раздел / таблица','Содержимое'],rows:rows,widths:[14,28,28,30,70]};}
 async function exportWorkbook(){if(!filtered.length){alert('За выбранный период нет отчётов для выгрузки.');return;}var btn=$('exportWorkbook');btn.disabled=true;btn.textContent='Формирование…';try{await downloadXlsx('Отчёты_СБ_'+periodFilePart()+'.xlsx',[summarySpec(),metricsSpec(),objectMetricsSpec(),detailsSpec(),dynamicSpec()]);}finally{btn.disabled=false;btn.textContent='↓ Скачать полный Excel-отчёт';}}
 async function exportSummary(){if(!filtered.length){alert('Нет данных для выгрузки.');return;}await downloadXlsx('Сводка_сотрудников_'+periodFilePart()+'.xlsx',[summarySpec()]);}
 async function exportMetrics(){if(!filtered.length){alert('Нет данных для выгрузки.');return;}await downloadXlsx('Показатели_формы_'+periodFilePart()+'.xlsx',[metricsSpec(),objectMetricsSpec()]);}
 async function exportDetails(){if(!filtered.length){alert('Нет данных для выгрузки.');return;}await downloadXlsx('Полные_отчёты_'+periodFilePart()+'.xlsx',[detailsSpec(),dynamicSpec()]);}
 async function loadFieldLabels(){try{var response=await fetch('index.html',{credentials:'same-origin',cache:'no-store'});var html=await response.text();var doc=new DOMParser().parseFromString(html,'text/html');doc.querySelectorAll('[data-field][id]').forEach(function(el){var group=el.closest('.form-group'),label=group&&group.querySelector('label');if(label)fieldLabels[el.id]=label.textContent.trim();});}catch(e){}}
 $('applyBtn').addEventListener('click',load);$('employeeFilter').addEventListener('input',render);$('objectFilter').addEventListener('change',load);$('exportSummary').addEventListener('click',exportSummary);$('exportMetrics').addEventListener('click',exportMetrics);$('exportDetails').addEventListener('click',exportDetails);$('exportWorkbook').addEventListener('click',exportWorkbook);
 initPeriod();loadFieldLabels().then(load);
$('logoutBtn').addEventListener('click',async function(){try{await fetch('api.php?action=logout',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:window.__SB_CSRF||''})});}catch(e){}location.href='index.html';});
load();
})();
</script>
</body>
</html>
