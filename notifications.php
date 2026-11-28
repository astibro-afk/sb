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
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#f3f7f8"><title>Уведомления — СБ</title><link rel="stylesheet" href="site-theme.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f7f8;color:#18343a;font:14px/1.5 Inter,Arial,sans-serif}.page{max-width:1050px;margin:auto;padding:24px 16px 48px}.top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px}.top h1{font-size:24px;margin:0}.sub{color:#71858a;margin:4px 0 0}.actions{display:flex;gap:8px;flex-wrap:wrap}.btn{border:1px solid #dce7e9;background:#fff;color:#18343a;border-radius:9px;padding:9px 13px;text-decoration:none;font-weight:700;cursor:pointer}.btn.primary{background:#08777b;color:#fff;border-color:#08777b}.summary{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px}.count{font-size:12px;color:#71858a}.list{display:grid;gap:10px}.item{background:#fff;border:1px solid #e4ecee;border-radius:13px;padding:15px 16px;display:grid;grid-template-columns:10px minmax(0,1fr) auto;gap:12px;align-items:start;box-shadow:0 5px 20px rgba(24,52,58,.04)}.item.unread{border-color:#b8dddd;background:#fbffff}.dot{width:9px;height:9px;border-radius:50%;background:#08777b;margin-top:6px}.item.read .dot{background:#d6e0e2}.item h2{font-size:15px;margin:0 0 4px}.item p{margin:0;color:#536a70}.meta{font-size:11px;color:#829398;margin-top:8px}.item-actions{display:flex;gap:6px;flex-wrap:wrap}.item-actions a,.item-actions button{font-size:11px}.empty{background:#fff;border:1px dashed #cbd9dc;border-radius:13px;padding:34px;text-align:center;color:#71858a}@media(max-width:620px){.top{align-items:flex-start;flex-direction:column}.item{grid-template-columns:10px minmax(0,1fr)}.item-actions{grid-column:2}.summary{align-items:flex-start;flex-direction:column}}
</style><link rel="stylesheet" href="site-header.css">
</head><body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a class="active" href="/notifications.php">Уведомления</a></div></nav></header>
<main class="page"><div class="sb-page-title"><div><h1>Центр уведомлений</h1><p>Важные события вашей учётной записи и отчётов</p></div><div style="display:flex;gap:8px;flex-wrap:wrap"><a class="btn" href="push.php">Настроить push</a><button class="btn primary" id="readAll" type="button">Прочитать все</button></div></div><div class="summary"><strong id="unread">Непрочитанных: —</strong><span class="count" id="total"></span></div><section class="list" id="list"><div class="empty">Загружаем уведомления…</div></section></main>
<script>
(function(){
'use strict';
const list=document.getElementById('list'), unread=document.getElementById('unread'), total=document.getElementById('total');
let csrf='';
function el(tag,cls,txt){const n=document.createElement(tag);if(cls)n.className=cls;if(txt!==undefined)n.textContent=txt;return n;}
function localLink(value){return typeof value==='string'&&/^\/?[a-z0-9_/-]+\.php(?:\?[a-z0-9_=&%-]*)?$/i.test(value)&&!value.includes('..')?value:'index.html';}
async function load(){
 const r=await fetch('api.php?action=notifications_data',{credentials:'same-origin',cache:'no-store'}),d=await r.json();
 if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось загрузить уведомления.');
 csrf=d.csrf||''; unread.textContent='Непрочитанных: '+(d.unread||0); total.textContent='Всего: '+(d.notifications||[]).length;
 list.replaceChildren();
 if(!(d.notifications||[]).length){list.append(el('div','empty','Пока нет уведомлений. Здесь будут появляться важные сообщения о ваших отчётах и доступе.'));return;}
 d.notifications.forEach(item=>{
  const card=el('article','item '+(item.is_read?'read':'unread'));
  card.append(el('span','dot'));
  const body=el('div');body.append(el('h2','',item.title||'Уведомление'),el('p','',item.message||''),el('div','meta',item.created_at||''));card.append(body);
  const actions=el('div','item-actions');
  const link=el('a','btn','Открыть');link.href=localLink(item.link);actions.append(link);
  if(!item.is_read){const b=el('button','btn','Прочитано');b.type='button';b.addEventListener('click',async()=>{await mark(item.id);});actions.append(b);}
  card.append(actions);list.append(card);
 });
}
async function mark(id,all){const r=await fetch('api.php?action=notifications_read',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf,id:id||0,all:!!all})});const d=await r.json();if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось обновить уведомления.');await load();}
document.getElementById('readAll').addEventListener('click',()=>mark(0,true).catch(e=>alert(e.message)));
load().catch(e=>{list.replaceChildren(el('div','empty',e.message));});
})();
</script><script>
(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,60000);document.addEventListener('visibilitychange',ping);})();
</script><script src="site-header.js"></script>
</body></html>