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
.field label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin:0 0 6px}.field input,.field select{width:100%;height:40px;border:1px solid #d7e2e7;border-radius:8px;padding:0 10px;background:#fff;color:var(--ink);font:inherit;min-width:0}.field select[multiple]{height:auto;min-height:86px;padding:7px 9px}.field select[multiple] option{padding:4px 6px}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;margin-bottom:18px}.stat{padding:16px 18px}.stat span{display:block;color:var(--muted);font-size:12px;font-weight:600}.stat strong{display:block;font-size:26px;margin-top:3px}
.panel{overflow:hidden;margin-bottom:18px}.panel-head{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px}.panel-head h3{font-size:16px;margin:0}.panel-head small{color:var(--muted)}
.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:720px}th,td{text-align:left;padding:11px 14px;border-bottom:1px solid var(--line);vertical-align:middle}th{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);background:#f8fbfc;white-space:nowrap}td{font-size:13px}tbody tr:last-child td{border-bottom:0}.progress{height:7px;width:100px;border-radius:10px;background:#e8eef0;overflow:hidden;display:inline-block;vertical-align:middle;margin-right:7px}.progress i{height:100%;display:block;background:var(--teal);border-radius:10px}.empty{padding:34px 15px;text-align:center;color:var(--muted)}
.status{color:var(--teal);font-weight:700}.role{font-size:12px;color:var(--muted);padding:5px 9px;border-radius:20px;background:#f1f5f6}
.stats{grid-template-columns:repeat(4,minmax(0,1fr))}.stat small{display:block;color:var(--muted);font-size:11px;margin-top:3px}
.ranking-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;padding:14px 18px}.ranking-grid h4{margin:0 0 8px;font-size:13px;color:var(--muted)}
.comment-cell{display:flex;align-items:flex-start;gap:8px;min-width:220px}.comment-cell>span:first-child{max-width:240px;white-space:normal;overflow-wrap:anywhere}.muted{color:var(--muted)}.comment-btn{padding:5px 8px;font-size:11px;white-space:nowrap}
.comment-editor{min-width:260px;margin-top:8px}.comment-editor textarea{width:100%;min-height:74px;border:1px solid #cbd9df;border-radius:8px;padding:8px;font:inherit;resize:vertical}.comment-editor>div{display:flex;gap:7px;margin-top:6px}
@media(max-width:900px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.ranking-grid{grid-template-columns:1fr}}
@media(max-width:600px){.stats{grid-template-columns:1fr 1fr}.ranking-grid{padding:12px;gap:10px}}
@media(max-width:900px){.filters{grid-template-columns:repeat(2,minmax(0,1fr))}.top-inner{align-items:flex-start}.stats{grid-template-columns:repeat(3,1fr)}}
@media(max-width:600px){.top-inner{padding:14px 16px;display:block}.actions{margin-top:13px}.intro{display:block}main{padding:0 14px 28px;margin:20px auto}.filters{grid-template-columns:1fr 1fr;padding:13px}.stats{grid-template-columns:1fr}.stat{padding:12px 15px}.stat strong{font-size:22px}.panel-head{align-items:flex-start;flex-direction:column}.brand h1{font-size:18px}}

/* Компактная управленческая панель */
main{max-width:1320px;margin:22px auto;padding:0 24px 38px}
.intro{align-items:center;margin-bottom:14px}.intro h2{font-size:22px}.intro p{font-size:13px}
.filters{padding:13px 15px;grid-template-columns:repeat(4,minmax(130px,1fr));gap:9px;margin-bottom:14px;box-shadow:none;border-radius:11px}
.field label{font-size:11px;margin-bottom:4px}.field input,.field select{height:36px;font-size:13px}.field select[multiple]{height:66px;min-height:66px;overflow:auto;padding:5px 8px}


.compact-stats{grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:14px}
.stat{padding:13px 17px;box-shadow:none;border-radius:11px}.stat strong{font-size:25px}.stat small{font-size:11px}
.tabs{display:flex;gap:5px;border-bottom:1px solid var(--line);margin:4px 0 16px;padding:0 2px}
.tab{border:0;background:transparent;color:var(--muted);padding:10px 15px;font:600 13px Segoe UI,Arial;cursor:pointer;border-bottom:2px solid transparent}
.tab.active{color:var(--teal);border-bottom-color:var(--teal)}
.tab-pane{display:none}.tab-pane.active{display:block}
.panel{box-shadow:none;border-radius:11px;margin-bottom:14px}.panel-head{padding:13px 16px}.panel-head h3{font-size:15px}.panel-head small{font-size:11px}
.table-wrap table{min-width:650px}th,td{padding:10px 13px}.simple-table{min-width:600px}
.sort-select{height:34px;border:1px solid var(--line);border-radius:8px;padding:0 10px;color:var(--ink);background:#fff;font:600 12px Segoe UI,Arial}
.chart-area{padding:12px 18px 8px;min-height:220px}.chart-area svg{width:100%;height:auto;display:block;overflow:visible}
.legend{display:flex;gap:14px;color:var(--muted);font-size:11px}.legend span{display:flex;align-items:center;gap:6px}.legend i{width:15px;height:3px;background:#087f83;border-radius:2px}.legend span+span i{background:#aab9c2}
.comment-cell{min-width:200px}.comment-cell>span:first-child{max-width:260px}
@media(max-width:900px){.filters{grid-template-columns:repeat(2,minmax(0,1fr))}.compact-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:600px){main{padding:0 12px 25px;margin:15px auto}.filters{grid-template-columns:1fr 1fr;padding:11px}.compact-stats{grid-template-columns:1fr 1fr}.stat:last-child{grid-column:1/-1}.stat{padding:11px 13px}.tabs{overflow:auto}.tab{padding:9px 11px;white-space:nowrap}.panel-head{flex-direction:row;align-items:center}.legend{display:none}.chart-area{padding:8px}.intro{display:flex;flex-direction:column;align-items:flex-start}.intro>div:last-child{width:100%}}

</style>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
</head>
<body>
<header class="top"><div class="top-inner">
  <div class="brand"><div class="logo">СБ</div><div><h1>Ежедневный отчёт СБ</h1><p>Группа БФ Волгоград 1</p></div></div>
  <div class="actions"><a class="btn" href="index.html">← К форме отчёта</a><button class="btn" id="logoutBtn" type="button">Выйти</button></div>
</div></header>
<main>
  <div class="intro"><div><h2>Отчёты сотрудников</h2><p id="roleInfo">Загрузка доступных данных…</p></div><div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap"><span class="role" id="periodLabel">Сводная по сохранённым отчётам</span><button class="btn primary" id="exportWorkbook" type="button">↓ Скачать Excel по нарушениям</button></div></div>
  <section class="filters">
    <div class="field"><label for="periodType">Период отчёта</label><select id="periodType"><option value="year" selected>За год</option><option value="month">За месяц</option><option value="quarter">За квартал</option><option value="custom">Произвольный период</option></select></div>
    <div class="field"><label for="periodYear">Год</label><select id="periodYear"></select></div>
    <div class="field" id="unitField"><label for="periodUnit">Месяц</label><select id="periodUnit"></select></div>
    <div class="field"><label for="objectFilter">Торговый объект</label><select id="objectFilter" multiple size="3" aria-label="Выбор нескольких торговых объектов"></select><small style="display:block;color:var(--muted);margin-top:5px">Можно выбрать несколько объектов (Ctrl/Shift). Если ничего не выбрано — будут показаны все доступные.</small></div>
    <div class="field"><label for="employeeFilter">Поиск сотрудника</label><input type="search" id="employeeFilter" placeholder="ФИО сотрудника"></div>
    <div class="field"><label for="dateFrom">Дата с (для произвольного периода)</label><input type="date" id="dateFrom" value="<?php echo htmlspecialchars($monthStart, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="field"><label for="dateTo">Дата по</label><input type="date" id="dateTo" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <button class="btn primary" id="applyBtn" type="button">Показать</button>
  </section>
  <section class="stats compact-stats">
    <div class="stat"><span>Количество нарушений</span><strong id="statCases">—</strong></div>
    <div class="stat"><span>Сумма нарушений и ущерба</span><strong id="statDamage">—</strong><small>рублей</small></div>
    <div class="stat"><span>Отчётов с нарушениями</span><strong id="statViolationReports">—</strong></div>
  </section>
  <nav class="tabs"><button class="tab active" data-tab="overview" type="button">Сводка нарушений</button><button class="tab" data-tab="objects" type="button">По объектам</button><button class="tab" data-tab="details" type="button">Отчёты с нарушениями</button></nav>
  <section class="tab-pane active" id="tab-overview"><section class="panel"><div class="panel-head"><div><h3>Динамика нарушений</h3><small>Количество выявлений по месяцам, сравнение с прошлым годом</small></div><div class="legend"><span><i></i>Текущий год</span><span><i></i>Прошлый год</span></div></div><div id="trendChart" class="chart-area"></div></section>
  <section class="panel"><div class="panel-head"><div><h3>Нарушения по направлениям</h3><small>Количество и сумма по данным отчётов</small></div></div><div class="table-wrap"><table><thead><tr><th>Направление</th><th>Вид нарушения</th><th>Количество</th><th>Сумма, руб.</th></tr></thead><tbody id="violationSummaryBody"></tbody></table></div></section></section>
  <section class="tab-pane" id="tab-objects"><section class="panel"><div class="panel-head"><div><h3>Нарушения по объектам</h3><small>Количество выявлений и сумма ущерба</small></div><select id="objectSort" class="sort-select"><option value="cases">Больше нарушений</option><option value="damage">Большая сумма</option></select></div><div class="table-wrap"><table><thead><tr><th>Торговый объект</th><th>Количество нарушений</th><th>Сумма, руб.</th></tr></thead><tbody id="objectSummaryBody"></tbody></table></div></section></section>
  <section class="tab-pane" id="tab-details"><section class="panel"><div class="panel-head"><div><h3>Отчёты с нарушениями</h3><small>Только записи, в которых зафиксированы нарушения или сумма ущерба</small></div></div><div class="table-wrap"><table><thead><tr><th>Дата</th><th>Объект</th><th>Сотрудник</th><th>Выявленные нарушения</th><th>Количество</th><th>Сумма, руб.</th><th>Комментарий руководителя</th></tr></thead><tbody id="detailsBody"></tbody></table></div></section></section>
</main>
<script>
(function(){
'use strict';
var records=[],filtered=[],previousFiltered=[],user=null,objects=[],fieldLabels={};
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
['Кассовые операции','Отмена позиций','b1_pos_qty','шт.'],['Кассовые операции','Сумма отменённых позиций','b1_pos_sum','руб.'],['Кассовые операции','Аннулирование чека','b1_chek_qty','шт.'],['Кассовые операции','Сумма аннулированных чеков','b1_chek_sum','руб.'],['Кассовые операции','Фиктивные возвраты','b1_ret_qty','шт.'],['Кассовые операции','Сумма фиктивных возвратов','b1_ret_sum','руб.'],['Кассовые операции','Продажа с заниженным весом','b1_weight_qty','шт.'],['Кассовые операции','Сумма продажи с заниженным весом','b1_weight_sum','руб.'],
['Перемещения ТМЦ','Нарушения внутри объекта','b3_in_qty','шт.'],['Перемещения ТМЦ','Сумма нарушений внутри объекта','b3_in_sum','руб.'],['Перемещения ТМЦ','Нарушения между объектами','b3_between_qty','шт.'],['Перемещения ТМЦ','Сумма нарушений между объектами','b3_between_sum','руб.'],
['Инвентаризации','Выявленные расхождения','b4_dev_qty','шт.'],['Инвентаризации','Сумма расхождений','b4_dev_sum','руб.'],
['Поставки с РЦ','Нарушения по качеству','b6_q_qty','шт.'],['Поставки с РЦ','Сумма нарушений по качеству','b6_q_sum','руб.'],['Поставки с РЦ','Нарушения по количеству','b6_qty_qty','шт.'],['Поставки с РЦ','Сумма нарушений по количеству','b6_qty_sum','руб.'],['Поставки с РЦ','Нарушения по срокам годности','b6_sg_qty','шт.'],['Поставки с РЦ','Сумма нарушений по срокам годности','b6_sg_sum','руб.'],
['Прямые поставщики','Нарушения по качеству','b7_q_qty','шт.'],['Прямые поставщики','Сумма нарушений по качеству','b7_q_sum','руб.'],['Прямые поставщики','Нарушения по количеству','b7_qty_qty','шт.'],['Прямые поставщики','Сумма нарушений по количеству','b7_qty_sum','руб.'],['Прямые поставщики','Нарушения по срокам годности','b7_sg_qty','шт.'],['Прямые поставщики','Сумма нарушений по срокам годности','b7_sg_sum','руб.'],
['Списание ТМЦ','Выявленные нарушения','b8_qty','шт.'],['Списание ТМЦ','Сумма выявленных нарушений','b8_sum','руб.'],['Правонарушения','Уголовные — количество','b10_crim_qty','случаев'],['Правонарушения','Уголовные — сумма ущерба','b10_crim_sum','руб.'],['Правонарушения','Административные — количество','b10_adm_qty','случаев'],['Правонарушения','Административные — сумма ущерба','b10_adm_sum','руб.'],['Правонарушения','Разбор на месте — количество','b10_local_qty','случаев'],['Правонарушения','Разбор на месте — сумма ущерба','b10_local_sum','руб.']
 ];
 var VIOLATION_PAIRS=[
 ['Кассовые операции','Отмена позиций','b1_pos_qty','b1_pos_sum'],
 ['Кассовые операции','Аннулирование чека без администратора','b1_chek_qty','b1_chek_sum'],
 ['Кассовые операции','Фиктивные возвраты','b1_ret_qty','b1_ret_sum'],
 ['Кассовые операции','Продажа с заниженным весом','b1_weight_qty','b1_weight_sum'],
 ['Перемещения ТМЦ','Нарушения внутри объекта','b3_in_qty','b3_in_sum'],
 ['Перемещения ТМЦ','Нарушения между объектами','b3_between_qty','b3_between_sum'],
 ['Инвентаризации','Выявленные расхождения','b4_dev_qty','b4_dev_sum'],
 ['Поставки с РЦ','Нарушения по качеству','b6_q_qty','b6_q_sum'],
 ['Поставки с РЦ','Нарушения по количеству','b6_qty_qty','b6_qty_sum'],
 ['Поставки с РЦ','Нарушения по срокам годности','b6_sg_qty','b6_sg_sum'],
 ['Прямые поставщики','Нарушения по качеству','b7_q_qty','b7_q_sum'],
 ['Прямые поставщики','Нарушения по количеству','b7_qty_qty','b7_qty_sum'],
 ['Прямые поставщики','Нарушения по срокам годности','b7_sg_qty','b7_sg_sum'],
 ['Списание ТМЦ','Выявленные нарушения','b8_qty','b8_sum'],
 ['Правонарушения','Уголовные','b10_crim_qty','b10_crim_sum'],
 ['Правонарушения','Административные','b10_adm_qty','b10_adm_sum'],
 ['Правонарушения','Разбор на месте','b10_local_qty','b10_local_sum']
 ];
 function numericValue(raw){if(raw===null||raw===undefined||raw==='')return null;if(typeof raw==='number')return Number.isFinite(raw)?raw:null;var s=String(raw).replace(/\u00a0/g,' ').trim();if(!s||s==='—'||s==='-')return null;s=s.replace(/\s/g,'').replace(',','.').replace(/руб\.?/gi,'');if(!/^[-+]?(?:\d+\.?\d*|\.\d+)$/.test(s))return null;var n=Number(s);return Number.isFinite(n)?n:null;}
 function metricRows(list){return METRICS.map(function(def){var total=0,count=0;list.forEach(function(r){var n=numericValue((r.data||{})[def[2]]);if(n!==null){total+=n;count++;}});return {section:def[0],label:def[1],key:def[2],unit:def[3],total:total,count:count};}).filter(function(x){return x.count>0;});}
 function objectMetricRows(list){var groups=Object.create(null);list.forEach(function(r){var name=r.object||'Объект не указан';if(!groups[name])groups[name]=[];groups[name].push(r);});var out=[];Object.keys(groups).sort(function(a,b){return a.localeCompare(b,'ru');}).forEach(function(name){metricRows(groups[name]).forEach(function(m){out.push({object:name,section:m.section,label:m.label,unit:m.unit,total:m.total,count:m.count});});});return out;}
 function fmtMetric(n){return Number(n).toLocaleString('ru-RU',{maximumFractionDigits:2});}

 function violationTotals(r){var d=r.data||{},qty=0,damage=0,labels=[];VIOLATION_PAIRS.forEach(function(p){var amount=numericValue(d[p[3]]);if(amount===null||amount<=0)return;damage+=amount;var n=numericValue(d[p[2]]);if(n!==null&&n>0)qty+=n;labels.push(p[1]+(n!==null&&n>0?' ('+fmtMetric(n)+')':''));});return {qty:qty,damage:damage,labels:labels};}
 function hasViolations(r){var t=violationTotals(r);return t.qty>0||t.damage>0;}
 function render(){
 var q=$('employeeFilter').value.trim().toLocaleLowerCase('ru'),from=$('dateFrom').value,to=$('dateTo').value;
 filtered=records.filter(function(r){return r.date>=from&&r.date<=to&&(!q||(r.employee||'').toLocaleLowerCase('ru').includes(q));});
 var year=Number(from.slice(0,4));previousFiltered=records.filter(function(r){return r.date>=String(year-1)+'-01-01'&&r.date<=String(year-1)+'-12-31';});
 var list=filtered.filter(hasViolations),qty=0,damage=0;list.forEach(function(r){var t=violationTotals(r);qty+=t.qty;damage+=t.damage;});
 $('statCases').textContent=fmtMetric(qty);$('statDamage').textContent=fmtMetric(damage);$('statViolationReports').textContent=list.length;
 var summary=VIOLATION_PAIRS.map(function(p){var qv=0,sv=0;filtered.forEach(function(r){var amount=numericValue((r.data||{})[p[3]]);if(amount===null||amount<=0)return;sv+=amount;var n=numericValue((r.data||{})[p[2]]);if(n!==null&&n>0)qv+=n;});return {section:p[0],label:p[1],qty:qv,damage:sv};}).filter(function(x){return x.damage>0;});
 $('violationSummaryBody').innerHTML=summary.length?summary.map(function(x){return '<tr><td>'+esc(x.section)+'</td><td><strong>'+esc(x.label)+'</strong></td><td>'+fmtMetric(x.qty)+'</td><td>'+fmtMetric(x.damage)+'</td></tr>';}).join(''):'<tr><td colspan="4" class="empty">Нарушений не зафиксировано</td></tr>';
 var om=Object.create(null);list.forEach(function(r){var n=r.object||'Объект не указан',t=violationTotals(r);if(!om[n])om[n]={name:n,qty:0,damage:0};om[n].qty+=t.qty;om[n].damage+=t.damage;});
 var oa=Object.keys(om).map(function(k){return om[k];});oa.sort(function(a,b){return $('objectSort').value==='damage'?b.damage-a.damage:b.qty-a.qty;});
 $('objectSummaryBody').innerHTML=oa.length?oa.map(function(x){return '<tr><td><strong>'+esc(x.name)+'</strong></td><td>'+fmtMetric(x.qty)+'</td><td>'+fmtMetric(x.damage)+'</td></tr>';}).join(''):'<tr><td colspan="3" class="empty">Нарушений не зафиксировано</td></tr>';
 $('detailsBody').innerHTML=list.length?list.map(function(r){var t=violationTotals(r),c=r.manager_comment||'';return '<tr><td>'+fmtDate(r.date)+'</td><td>'+esc(r.object||'—')+'</td><td>'+esc(r.employee||'Не указан')+'</td><td>'+esc(t.labels.join('; '))+'</td><td>'+fmtMetric(t.qty)+'</td><td>'+fmtMetric(t.damage)+'</td><td><div class="comment-cell">'+(c?'<span>'+esc(c)+'</span>':'<span class="muted">Нет комментария</span>')+(user&&user.role==='admin'?'<button class="btn comment-btn" data-comment="'+esc(r.record_key)+'" type="button">'+(c?'Изменить':'Добавить')+'</button>':'')+'</div></td></tr>';}).join(''):'<tr><td colspan="7" class="empty">Нет отчётов с нарушениями</td></tr>';
 renderTrends();$('periodLabel').textContent=$('periodType').selectedOptions[0].text+' · '+from+' — '+to;
 }
 function renderTrends(){var labels=['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],now=[],old=[];function sum(arr,m){return arr.reduce(function(n,r){return Number(String(r.date||'').slice(5,7))===m?n+violationTotals(r).qty:n;},0);}for(var m=1;m<=12;m++){now.push(sum(filtered,m));old.push(sum(previousFiltered,m));}var max=Math.max.apply(null,now.concat(old).concat([1])),W=900,H=205,L=36,R=16,T=12,B=30,ph=H-T-B,pw=W-L-R;function pts(arr){return arr.map(function(v,i){return (L+i*pw/11)+','+(T+ph-v/max*ph);}).join(' ');}var grid='';for(var g=0;g<=4;g++){var y=T+ph*g/4;grid+='<line x1="'+L+'" y1="'+y+'" x2="'+(W-R)+'" y2="'+y+'" stroke="#e8eef0"/><text x="'+(L-8)+'" y="'+(y+4)+'" text-anchor="end" font-size="10" fill="#82919a">'+Math.round(max*(4-g)/4)+'</text>';}var ticks=labels.map(function(x,i){return '<text x="'+(L+i*pw/11)+'" y="'+(H-7)+'" text-anchor="middle" font-size="10" fill="#82919a">'+x+'</text>';}).join('');$('trendChart').innerHTML='<svg viewBox="0 0 '+W+' '+H+'"><g>'+grid+'</g><polyline points="'+pts(old)+'" fill="none" stroke="#aab9c2" stroke-width="2.5"/><polyline points="'+pts(now)+'" fill="none" stroke="#087f83" stroke-width="3"/>'+ticks+'</svg>';}
 function saveComment(key,initial){
 var old=document.querySelector('.comment-editor');if(old)old.remove();
 var tr=Array.from($('detailsBody').querySelectorAll('tr')).find(function(row){var b=row.querySelector('[data-comment]');return b&&b.getAttribute('data-comment')===key;});if(!tr)return;
 var box=document.createElement('div');box.className='comment-editor';box.innerHTML='<textarea maxlength="5000" placeholder="Комментарий руководителя (до 5000 символов)"></textarea><div><button type="button" class="btn primary save-comment">Сохранить</button><button type="button" class="btn cancel-comment">Отмена</button></div>';box.querySelector('textarea').value=initial||'';tr.cells[6].appendChild(box);
 box.querySelector('.cancel-comment').onclick=function(){box.remove();};
 box.querySelector('.save-comment').onclick=async function(){var btn=this;btn.disabled=true;try{var response=await fetch('api.php?action=report_comment',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({csrf:window.__SB_CSRF||'',record_key:key,comment:box.querySelector('textarea').value})});var result=await response.json();if(!response.ok||!result.ok)throw new Error(result.error||'Не удалось сохранить комментарий');var record=records.find(function(r){return r.record_key===key;});if(record)record.manager_comment=result.comment;box.remove();render();}catch(e){alert(e.message);}finally{btn.disabled=false;}};
}
$('detailsBody').addEventListener('click',function(e){var b=e.target.closest('[data-comment]');if(!b)return;var key=b.getAttribute('data-comment'),r=records.find(function(x){return x.record_key===key;});if(r)saveComment(key,r.manager_comment||'');});
function pad(n){return String(n).padStart(2,'0');}
 function fillUnits(){var t=$('periodType').value,box=$('unitField'),sel=$('periodUnit');sel.innerHTML='';if(t==='year'||t==='custom'){box.style.display='none';return;}box.style.display='';if(t==='month'){$('unitField').querySelector('label').textContent='Месяц';['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'].forEach(function(x,i){sel.add(new Option(x,i+1));});sel.value=new Date().getMonth()+1;}else{$('unitField').querySelector('label').textContent='Квартал';for(var q=1;q<=4;q++)sel.add(new Option('Квартал '+q,q));sel.value=Math.floor(new Date().getMonth()/3)+1;}}
 function syncDates(){var t=$('periodType').value;if(t==='custom')return;var y=Number($('periodYear').value),from,to;if(t==='year'){from=y+'-01-01';to=y+'-12-31';}else if(t==='quarter'){var q=Number($('periodUnit').value),m=(q-1)*3,last=new Date(y,m+3,0);from=y+'-'+pad(m+1)+'-01';to=y+'-'+pad(last.getMonth()+1)+'-'+pad(last.getDate());}else{var mo=Number($('periodUnit').value),last=new Date(y,mo,0);from=y+'-'+pad(mo)+'-01';to=y+'-'+pad(mo)+'-'+pad(last.getDate());}$('dateFrom').value=from;$('dateTo').value=to;}
 function initPeriod(){var y=$('periodYear'),now=new Date();for(var v=now.getFullYear()+1;v>=now.getFullYear()-8;v--)y.add(new Option(v,v));y.value=now.getFullYear();fillUnits();$('periodType').addEventListener('change',function(){fillUnits();syncDates();});y.addEventListener('change',syncDates);$('periodUnit').addEventListener('change',syncDates);['dateFrom','dateTo'].forEach(function(id){$(id).addEventListener('change',function(){$('periodType').value='custom';fillUnits();});});syncDates();}
 async function load(){var from=$('dateFrom').value,to=$('dateTo').value;if(!from||!to||from>to){alert('Проверьте даты периода.');return;}$('applyBtn').disabled=true;$('applyBtn').textContent='Загрузка…';try{var selectedObjectIds=Array.from($('objectFilter').selectedOptions).map(function(o){return o.value;});var fetchFrom=$('periodType').value==='year'?(Number(from.slice(0,4))-1)+'-01-01':from;var params=new URLSearchParams({from:fetchFrom,to:to,object_ids:selectedObjectIds.join(',')});var res=await fetch('api.php?action=reports_data&'+params.toString(),{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});var data=await res.json();if(res.status===401){location.href='index.html';return;}if(!res.ok||!data.ok)throw new Error(data.error||'Не удалось загрузить отчёты.');window.__SB_CSRF=data.csrf||'';user=data.user;objects=data.objects||[];records=data.records||[];$('roleInfo').textContent=roleName(user.role)+' · '+(user.object_names||[]).join(', ');var sel=$('objectFilter'),cur=selectedObjectIds;sel.innerHTML=objects.map(function(o){return '<option value="'+Number(o.id)+'">'+esc(o.name)+'</option>';}).join('');Array.from(sel.options).forEach(function(o){o.selected=cur.length?cur.includes(o.value):true;});render();}catch(e){alert(e.message||'Ошибка загрузки отчётов.');}finally{$('applyBtn').disabled=false;$('applyBtn').textContent='Показать';}}
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
 function detailsSpec(){var keys=Object.create(null);filtered.forEach(function(r){Object.keys(allValues(r)).forEach(function(k){keys[k]=true;});});var fields=Object.keys(keys).sort(function(a,b){return (fieldLabels[a]||a).localeCompare(fieldLabels[b]||b,'ru');});var headers=['Дата','Торговый объект','Сотрудник','Кем сохранён','Заполнено полей','Всего полей'].concat(fields.map(function(k){return fieldLabels[k]||k;}),['Комментарий руководителя']);var rows=filtered.map(function(r){var vals=allValues(r);return [fmtDate(r.date),r.object,r.employee,r.author,r.done,r.total].concat(fields.map(function(k){return vals[k]===undefined?'':safeCell(vals[k]);}),[r.manager_comment||'']);});return {name:'Полные отчёты',title:'ПОЛНЫЕ ДАННЫЕ ОТЧЁТОВ',subtitle:'Одна строка — один самостоятельный отчёт. Включены поля формы, дополнительные данные и комментарии руководителя.',headers:headers,rows:rows,widths:[14,28,28,26,16,14].concat(fields.map(function(){return 24;}),[48])};}
 function dynamicSpec(){var rows=[];filtered.forEach(function(r){Object.keys(r.dyn||{}).forEach(function(k){rows.push([fmtDate(r.date),r.object,r.employee,k,safeCell(r.dyn[k])]);});});return {name:'Дополнительные данные',title:'ДОПОЛНИТЕЛЬНЫЕ ТАБЛИЦЫ И СТРОКИ',subtitle:'Содержимое динамических таблиц, сохранённых вместе с отчётами.',headers:['Дата','Торговый объект','Сотрудник','Раздел / таблица','Содержимое'],rows:rows,widths:[14,28,28,30,70]};}
 function rankingSpec(){var map=Object.create(null);filtered.forEach(function(r){var k=r.object||'Объект не указан';if(!map[k])map[k]={name:k,reports:0,fill:0,cases:0,damage:0};var x=map[k];x.reports++;x.fill+=pct(r);['b10_crim_qty','b10_adm_qty','b10_local_qty'].forEach(function(k){x.cases+=numericValue((r.data||{})[k])||0;});['b10_crim_sum','b10_adm_sum','b10_local_sum'].forEach(function(k){x.damage+=numericValue((r.data||{})[k])||0;});});var rows=Object.keys(map).map(function(k){var x=map[k];return [x.name,x.reports,x.reports?x.fill/x.reports/100:0,x.reports?x.cases/x.reports:0,x.reports?x.damage/x.reports:0];});return {name:'Рейтинг объектов',title:'РЕЙТИНГ ОБЪЕКТОВ',subtitle:'Средние показатели на один самостоятельный отчёт; не являются оценкой качества объекта.',headers:['Объект','Отчётов','Среднее заполнение','Случаев на отчёт','Сумма на отчёт, руб.'],rows:rows,widths:[32,14,22,22,24]};}
function trendSpec(){var rows=[],y=Number($('dateFrom').value.slice(0,4));for(var m=1;m<=12;m++){var mm=pad(m),prefix=y+'-'+mm,list=filtered.filter(function(r){return String(r.date||'').slice(0,7)===prefix;}),prev=previousFiltered.filter(function(r){return String(r.date||'').slice(5,7)===mm;});function sum(rs,key){return rs.reduce(function(a,r){return a+(numericValue((r.data||{})[key])||0);},0);}var cases=sum(list,'b10_crim_qty')+sum(list,'b10_adm_qty')+sum(list,'b10_local_qty'),old=sum(prev,'b10_crim_qty')+sum(prev,'b10_adm_qty')+sum(prev,'b10_local_qty');rows.push([prefix,list.length,sum(list,'b10_crim_qty'),sum(list,'b10_adm_qty'),sum(list,'b10_local_qty'),sum(list,'b10_crim_sum')+sum(list,'b10_adm_sum')+sum(list,'b10_local_sum'),old,prev.length&&old?((cases-old)/old):null]);}return {name:'Динамика',title:'ДИНАМИКА ПО МЕСЯЦАМ',subtitle:'Сравнение числа правонарушений с аналогичным месяцем предыдущего года.',headers:['Месяц','Отчётов','Уголовные','Административные','Разбор на месте','Сумма, руб.','Случаев годом ранее','Изменение'],rows:rows,widths:[16,12,14,18,18,20,22,16]};}
function violationsWorkbookSpecs(){
 var types=VIOLATION_PAIRS.map(function(p){var qv=0,sv=0;filtered.forEach(function(r){var amount=numericValue((r.data||{})[p[3]]);if(amount===null||amount<=0)return;sv+=amount;var n=numericValue((r.data||{})[p[2]]);if(n!==null&&n>0)qv+=n;});return [p[0],p[1],qv,sv];}).filter(function(x){return x[3]>0;});
 var objs=Object.create(null),details=[];filtered.filter(hasViolations).forEach(function(r){var t=violationTotals(r),n=r.object||'Объект не указан';if(!objs[n])objs[n]={qty:0,damage:0};objs[n].qty+=t.qty;objs[n].damage+=t.damage;details.push([fmtDate(r.date),n,r.employee||'',t.labels.join('; '),t.qty,t.damage,r.manager_comment||'']);});
 return [{name:'Сводка нарушений',title:'СВОДКА НАРУШЕНИЙ',subtitle:'Период: '+fmtDate($('dateFrom').value)+' — '+fmtDate($('dateTo').value),headers:['Направление','Вид нарушения','Количество','Сумма, руб.'],rows:types,widths:[28,48,18,20]},{name:'По объектам',title:'НАРУШЕНИЯ ПО ОБЪЕКТАМ',subtitle:'Объекты с зафиксированными нарушениями',headers:['Торговый объект','Количество нарушений','Сумма, руб.'],rows:Object.keys(objs).map(function(k){return [k,objs[k].qty,objs[k].damage];}),widths:[40,24,24]},{name:'Отчёты с нарушениями',title:'ОТЧЁТЫ С НАРУШЕНИЯМИ',subtitle:'Только отчёты с выявлениями',headers:['Дата','Объект','Сотрудник','Выявленные нарушения','Количество','Сумма, руб.','Комментарий руководителя'],rows:details,widths:[14,28,28,60,16,20,50]}];
}
async function exportWorkbook(){if(!filtered.some(hasViolations)){alert('Нет нарушений для выгрузки.');return;}var btn=$('exportWorkbook');btn.disabled=true;btn.textContent='Формирование…';try{await downloadXlsx('Нарушения_СБ_'+periodFilePart()+'.xlsx',violationsWorkbookSpecs());}finally{btn.disabled=false;btn.textContent='↓ Скачать Excel по нарушениям';}}
 async function loadFieldLabels(){try{var response=await fetch('index.html',{credentials:'same-origin',cache:'no-store'});var html=await response.text();var doc=new DOMParser().parseFromString(html,'text/html');doc.querySelectorAll('[data-field][id]').forEach(function(el){var group=el.closest('.form-group'),label=group&&group.querySelector('label');if(label)fieldLabels[el.id]=label.textContent.trim();});}catch(e){}}
 document.querySelectorAll('[data-tab]').forEach(function(btn){btn.addEventListener('click',function(){document.querySelectorAll('.tab').forEach(function(x){x.classList.toggle('active',x===btn);});document.querySelectorAll('.tab-pane').forEach(function(x){x.classList.toggle('active',x.id==='tab-'+btn.dataset.tab);});});});
 $('objectSort').addEventListener('change',render);
 $('applyBtn').addEventListener('click',load);$('employeeFilter').addEventListener('input',render);$('objectFilter').addEventListener('change',load);$('exportWorkbook').addEventListener('click',exportWorkbook);
 initPeriod();$('periodType').value='year';fillUnits();syncDates();loadFieldLabels().then(load);
$('logoutBtn').addEventListener('click',async function(){try{await fetch('api.php?action=logout',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:window.__SB_CSRF||''})});}catch(e){}location.href='index.html';});
})();
</script>
</body>
</html>
