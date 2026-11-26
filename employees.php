<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
ini_set('session.use_strict_mode','1');
$isHttps=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Strict']);
session_name('sbapp_session'); session_start();
if (empty($_SESSION['sb_user_id'])) { header('Location: index.html'); exit; }
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#f3f8f7">
<title>Сотрудники — СБ</title>
<link rel="stylesheet" href="site-theme.css">
<style>
:root{--brand:var(--sb-brand,#0d7377);--ink:var(--sb-ink,#1a3438);--muted:var(--sb-muted,#5a777b);--line:var(--sb-line,#dce9e7);--paper:#fff}
*{box-sizing:border-box}
body{margin:0;background:var(--sb-page,#f3f8f7)!important;color:var(--ink);font:14px/1.45 Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif}
.page{width:min(1120px,100%);margin:0 auto;padding:24px 22px 48px}
.header{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px}
.heading{display:flex;align-items:center;gap:13px;min-width:0}
.heading-icon{width:44px;height:44px;flex:0 0 44px;display:grid;place-items:center;border-radius:14px;background:#e4f2ef;color:var(--brand)}
h1{margin:0;font-size:23px;line-height:1.2;letter-spacing:-.45px;font-weight:750}
.subtitle{margin:5px 0 0;color:var(--muted);font-size:13px}
.back{display:inline-flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--ink);font-weight:650;text-decoration:none;white-space:nowrap;transition:.16s ease}
.back:hover{border-color:#a8c9c5;background:#fafffe}
.toolbar{display:grid;grid-template-columns:minmax(220px,1fr) minmax(150px,190px) minmax(170px,220px) auto;align-items:center;gap:10px;margin-bottom:14px}
.search-wrap{position:relative;min-width:0}
.filter{width:100%;height:42px;padding:0 34px 0 12px;border:1px solid var(--line);border-radius:11px;background:#fff;color:var(--ink);font:inherit;font-size:12.5px;outline:none;cursor:pointer}
.filter:focus{border-color:#81b9b4;box-shadow:0 0 0 3px rgba(13,115,119,.1)}
.search-wrap svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#789195;pointer-events:none}
.search{width:100%;height:42px;padding:0 14px 0 40px;border:1px solid var(--line);border-radius:11px;background:#fff;color:var(--ink);font:inherit;outline:none;transition:border-color .15s,box-shadow .15s}
.search:focus{border-color:#81b9b4;box-shadow:0 0 0 3px rgba(13,115,119,.1)}
.count{flex:0 0 auto;padding:8px 11px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--muted);font-size:12px;font-weight:650;white-space:nowrap}
.cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.employee{min-width:0;padding:15px 16px 13px;border:1px solid var(--line);border-radius:15px;background:var(--paper);box-shadow:0 3px 12px rgba(22,58,59,.035);transition:transform .16s ease,border-color .16s ease,box-shadow .16s ease}
.employee:hover{transform:translateY(-1px);border-color:#c4dcda;box-shadow:0 7px 20px rgba(22,58,59,.07)}
.person{display:flex;align-items:center;gap:11px;min-width:0}
.avatar{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:13px;background:#e9f3f1;color:var(--brand);font-size:14px;font-weight:800;letter-spacing:.3px}
.person-main{min-width:0;flex:1}
.name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:15px;font-weight:750;letter-spacing:-.15px}
.login{margin-top:2px;color:var(--muted);font-size:11.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.state{width:8px;height:8px;flex:0 0 8px;border-radius:50%;background:#2d9a70;box-shadow:0 0 0 3px #e9f6ef}
.state.inactive{background:#c56a56;box-shadow:0 0 0 3px #faeeeb}
.meta{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin:12px 0 11px}
.role{padding:4px 8px;border-radius:7px;background:#e8f4f2;color:#176c6c;font-size:10.5px;font-weight:750}
.status{font-size:10.5px;font-weight:650;color:#27815d}
.status.off{color:#b65a47}
.metrics{display:grid;grid-template-columns:.75fr 1.15fr 1.15fr;gap:7px}
.metric{min-width:0;padding:9px 9px 8px;border-radius:9px;background:#f5f9f8}
.metric strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px;font-weight:750;color:var(--ink);font-variant-numeric:tabular-nums}
.metric span{display:block;margin-top:2px;color:var(--muted);font-size:10px;line-height:1.25}
.objects{display:flex;gap:6px;align-items:flex-start;margin-top:11px;color:var(--muted);font-size:11.5px}
.objects svg{flex:0 0 14px;margin-top:1px;color:#7d9999}
.objects span{min-width:0;overflow-wrap:anywhere}
.card-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:12px;padding-top:10px;border-top:1px solid #edf2f1}
.joined{color:#8a9b9d;font-size:10.5px}
.history-btn{display:inline-flex;align-items:center;gap:6px;border:0;background:transparent;color:var(--brand);padding:5px 2px;font-family:inherit;font-size:11.5px;font-weight:650;cursor:pointer}
.history-btn:hover{color:#07585b}
.history-btn:disabled{opacity:.55;cursor:wait}
.details{margin-top:10px;padding-top:10px;border-top:1px solid #edf2f1}
.details-title{font-size:11.5px;font-weight:750;margin-bottom:7px}
.history{display:grid;gap:6px}
.event{padding:8px 9px;border-radius:8px;background:#f5f9f8;font-size:11px;color:#38585a;overflow-wrap:anywhere}
.event small{display:block;margin-top:3px;color:#829698;font-size:10px}
.empty{grid-column:1/-1;padding:34px 18px;border:1px dashed #cbdcda;border-radius:14px;background:rgba(255,255,255,.72);text-align:center;color:var(--muted)}
.empty strong{display:block;margin-bottom:4px;color:var(--ink);font-size:14px}
[hidden]{display:none!important}
@media(max-width:760px){.toolbar{grid-template-columns:1fr 1fr}.search-wrap{grid-column:1/-1}.cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:620px){.page{padding:17px 13px 32px}.header{align-items:flex-start;margin-bottom:16px}.heading{gap:10px}.heading-icon{width:39px;height:39px;flex-basis:39px;border-radius:12px}h1{font-size:20px}.subtitle{font-size:12px}.back{padding:8px 10px;font-size:12px}.toolbar{grid-template-columns:1fr}.search-wrap{grid-column:auto}.cards{grid-template-columns:1fr;gap:10px}.employee{padding:14px}.toolbar{gap:8px}.count{padding:8px 9px;font-size:11px}}
@media(max-width:390px){.header{flex-wrap:wrap}.back{margin-left:49px;margin-top:-7px}.metrics{grid-template-columns:.7fr 1.15fr 1.15fr}.metric{padding:8px 7px}.metric strong{font-size:12px}}
@media(prefers-reduced-motion:reduce){.employee,.back,.search{transition:none}}
</style>
<link rel="stylesheet" href="site-header.css">
</head>
<body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a href="/tasks.php">Задачи</a><a class="active" href="/employees.php">Сотрудники</a><a href="/notifications.php">Уведомления</a></div></nav></header>
<script>
(function(){
  'use strict';
  var add=document.getElementById('sbHeaderAddUser')||document.getElementById('btnHeaderAddUser');
  var manage=document.getElementById('adminManageLink')||document.getElementById('sbHeaderManage')||document.getElementById('btnHeaderManage');
  fetch('/api.php?action=status',{credentials:'same-origin',cache:'no-store'})
    .then(function(r){return r.json();})
    .then(function(d){
      var role=d&&d.authenticated&&d.user?d.user.role:'';
      if(add)add.hidden=!(role==='admin'||role==='moderator');
      if(manage)manage.hidden=role!=='admin';
    })
    .catch(function(){if(add)add.hidden=true;if(manage)manage.hidden=true;});
})();
</script>
<main class="page">
<div class="sb-page-title"><div><h1 id="heading">Сотрудники</h1><p class="subtitle" id="subtitle">Команда, отчёты и история активности</p></div></div>
<div class="toolbar" id="toolbar">
  <label class="search-wrap">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
    <input class="search" id="search" type="search" placeholder="Найти сотрудника или объект…" autocomplete="off">
  </label>
  <select class="filter" id="roleFilter" aria-label="Фильтр по роли"><option value="">Все роли</option></select>
  <select class="filter" id="objectFilter" aria-label="Фильтр по объекту"><option value="">Все объекты</option></select>
  <div class="count" id="count" aria-live="polite">Загрузка…</div>
</div>
<section class="cards" id="cards" aria-live="polite"><div class="empty">Загружаем данные сотрудников…</div></section>
</main>
<script>
(function(){
'use strict';
const cards=document.getElementById('cards'),search=document.getElementById('search'),count=document.getElementById('count'),roleFilter=document.getElementById('roleFilter'),objectFilter=document.getElementById('objectFilter');
const labels={admin:'Администратор',moderator:'Модератор',user:'Специалист СБ'};
function node(tag,cls,txt){const n=document.createElement(tag);if(cls)n.className=cls;if(txt!==undefined)n.textContent=txt;return n;}
function readJson(response){return response.text().then(text=>{if(!text.trim())throw new Error('Сервер вернул пустой ответ. Обновите страницу; если ошибка повторится, сообщите администратору.');try{return JSON.parse(text);}catch(_){throw new Error('Сервер вернул некорректный ответ. Проверьте авторизацию и попробуйте обновить страницу.');}});}
function date(v,withTime){if(!v)return '—';const d=new Date(String(v).replace(' ','T'));if(isNaN(d))return String(v);return d.toLocaleString('ru-RU',withTime?{day:'2-digit',month:'2-digit',year:'2-digit',hour:'2-digit',minute:'2-digit'}:{day:'2-digit',month:'2-digit',year:'2-digit'});}
function initials(name){const parts=String(name||'СБ').trim().split(/\s+/).filter(Boolean);return (parts.length>1?parts[0][0]+parts[1][0]:parts[0].slice(0,2)).toLocaleUpperCase('ru');}
let all=[];
function render(){
 const q=search.value.trim().toLocaleLowerCase('ru');
 const selectedRole=roleFilter.value,selectedObject=objectFilter.value;
 const rows=all.filter(u=>(!selectedRole||u.role===selectedRole)&&(!selectedObject||(u.objects||[]).includes(selectedObject))&&(String(u.full_name||'')+' '+String(u.username||'')+' '+(u.objects||[]).join(' ')).toLocaleLowerCase('ru').includes(q));
 cards.replaceChildren();count.textContent=(q||selectedRole||selectedObject)?rows.length+' из '+all.length:all.length+' сотрудников';
 if(!rows.length){const e=node('div','empty');e.append(node('strong','',q?'Ничего не найдено':'Список пока пуст'),node('span','',q?'Попробуйте изменить поисковый запрос.':'Нет сотрудников для отображения.'));cards.append(e);return;}
 rows.forEach(u=>{
  const card=node('article','employee');card.dataset.userId=String(u.id);
  const person=node('div','person'),avatar=node('div','avatar',initials(u.full_name||u.username));avatar.setAttribute('aria-hidden','true');
  const main=node('div','person-main');main.append(node('div','name',u.full_name||u.username||'Без имени'),node('div','login','@'+(u.username||'—')));
  const online=!!u.is_online&&!!u.is_active;const state=node('span','state'+(online?'':' inactive'));state.title=!u.is_active?'Аккаунт отключён':(online?'Пользователь в сети':'Пользователь не в сети');state.setAttribute('aria-label',state.title);
  person.append(avatar,main,state);card.append(person);
  const meta=node('div','meta'),roleTag=node('span','role',labels[u.role]||u.role||'Сотрудник'),presenceTag=node('span','status '+(online?'':'off'),!u.is_active?'Отключён':(online?'В сети':'Не в сети'));presenceTag.dataset.presence='1';meta.append(roleTag,presenceTag);card.append(meta);
  const metrics=node('div','metrics');
  [[u.reports_count??0,'Отчётов'],[date(u.last_login,true),'Последний вход'],[date(u.last_report,true),'Последний отчёт']].forEach(([v,l])=>{const m=node('div','metric');m.append(node('strong','',String(v)),node('span','',l));metrics.append(m);});
  card.append(metrics);
  const objects=node('div','objects');objects.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg>';objects.append(node('span','',(u.objects||[]).length?(u.objects||[]).join(' · '):'Объекты не назначены'));card.append(objects);
  const footer=node('div','card-footer'),joined=node('span','joined','В системе с '+date(u.created_at,false)),btn=node('button','history-btn');btn.type='button';btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg><span>История</span>';footer.append(joined,btn);card.append(footer);
  const detail=node('div','details');detail.hidden=true;card.append(detail);
  btn.addEventListener('click',async()=>{
   if(!detail.hidden){detail.hidden=true;btn.querySelector('span').textContent='История';return;}
   btn.disabled=true;
   try{
    const r=await fetch('api.php?action=employee_history&user_id='+encodeURIComponent(u.id),{credentials:'same-origin',cache:'no-store'}),d=await readJson(r);
    if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось загрузить историю.');
    detail.replaceChildren(node('div','details-title','Последние события'));const list=node('div','history');
    (d.history||[]).forEach(e=>{const item=node('div','event');item.append(node('div','',String(e.action||'Событие')+(e.target_label?' · '+e.target_label:'')),node('small','',date(e.created_at,true)));list.append(item);});
    if(!(d.history||[]).length)list.append(node('div','event','История пока пуста.'));
    detail.append(list);detail.hidden=false;btn.querySelector('span').textContent='Скрыть';
   }catch(e){detail.replaceChildren(node('div','event',e.message));detail.hidden=false;}
   finally{btn.disabled=false;}
  });
  cards.append(card);
 });
}
function refreshPresence(){fetch('api.php?action=employees_data',{credentials:'same-origin',cache:'no-store'}).then(readJson).then(d=>{if(!d.ok)return;(d.employees||[]).forEach(u=>{const card=cards.querySelector('[data-user-id="'+CSS.escape(String(u.id))+'"]');if(!card)return;const online=!!u.is_online&&!!u.is_active;const dot=card.querySelector('.state'),tag=card.querySelector('[data-presence="1"]');if(dot){dot.classList.toggle('inactive',!online);dot.title=!u.is_active?'Аккаунт отключён':(online?'Пользователь в сети':'Пользователь не в сети');dot.setAttribute('aria-label',dot.title);}if(tag){tag.classList.toggle('off',!online);tag.textContent=!u.is_active?'Отключён':(online?'В сети':'Не в сети');}});}).catch(function(){});}
setInterval(refreshPresence,30000);
search.addEventListener('input',render);roleFilter.addEventListener('change',render);objectFilter.addEventListener('change',render);
fetch('api.php?action=employees_data',{credentials:'same-origin',cache:'no-store'}).then(async r=>{const d=await readJson(r);if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось загрузить сотрудников.');all=d.employees||[];
 const roleOrder=['admin','moderator','user'];const roles=[...new Set(all.map(u=>u.role).filter(Boolean))].sort((a,b)=>roleOrder.indexOf(a)-roleOrder.indexOf(b));roles.forEach(role=>roleFilter.append(new Option(labels[role]||role,role)));
 const objects=[...new Set(all.flatMap(u=>u.objects||[]).filter(Boolean))].sort((a,b)=>a.localeCompare(b,'ru'));objects.forEach(name=>objectFilter.append(new Option(name,name)));
 if(d.user&&d.user.role==='user'){document.getElementById('heading').textContent='Моя карточка';document.getElementById('subtitle').textContent='Ваши отчёты и история активности';document.getElementById('toolbar').hidden=true;}render();}).catch(e=>{count.textContent='Ошибка';cards.replaceChildren(node('div','empty',e.message));});
})();
</script>
<script>
(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,30000);document.addEventListener('visibilitychange',ping);})();
</script>
<script src="site-header.js"></script>
</body>
</html>