<?php
declare(strict_types=1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
ini_set('session.use_strict_mode','1');ini_set('session.gc_maxlifetime','28800');
$isHttps=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Strict']);
session_name('sbapp_session');session_start();if(empty($_SESSION['sb_user_id'])){header('Location: index.html');exit;}
?>
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#f3f7f8"><title>Возмещение ущерба — СБ</title><link rel="stylesheet" href="site-theme.css"><link rel="stylesheet" href="site-header.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f7f8;color:#18343a;font:14px/1.5 Inter,Arial,sans-serif}.page{max-width:1120px;margin:auto;padding:24px 16px 48px}.head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:18px}.head h1{font-size:25px;margin:0}.muted{font-size:11px;color:#71858a}.card{background:#fff;border:1px solid #e1eaeb;border-radius:14px;padding:17px;margin-bottom:13px;box-shadow:0 5px 20px rgba(24,52,58,.04)}.card h2{font-size:15px;margin:0}.meta{color:#71858a;font-size:11px;margin:6px 0 13px}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}.stat{background:#f7faf9;border:1px solid #e7eeee;border-radius:9px;padding:10px}.stat small{display:block;color:#71858a;font-size:10px}.stat strong{display:block;font-size:15px;margin-top:3px;overflow-wrap:anywhere}.form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:13px}.field{display:flex;flex-direction:column;gap:4px}.field label{font-size:10px;font-weight:750}.field input{width:100%;padding:9px;border:1px solid #dce7e9;border-radius:8px;font:12px Inter,Arial,sans-serif}.field.wide{grid-column:span 2}.btn{border:1px solid #dce7e9;background:#fff;color:#18343a;border-radius:9px;padding:9px 12px;font-weight:750;font-size:11px;cursor:pointer}.btn.primary{background:#08777b;color:#fff;border-color:#08777b}.btn:disabled{opacity:.55}.actions{display:flex;align-items:flex-end}.payments{margin-top:13px;border-top:1px solid #edf2f2;padding-top:10px}.payments h3{font-size:12px;margin:0 0 7px}.payment{display:flex;justify-content:space-between;gap:12px;padding:6px 0;border-bottom:1px solid #f0f4f4;font-size:11px}.payment:last-child{border:0}.empty{padding:25px;background:#fff;border:1px dashed #cbd9dc;border-radius:12px;text-align:center;color:#71858a}.notice{font-size:11px;color:#71858a;margin-top:9px}@media(max-width:720px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.form{grid-template-columns:repeat(2,minmax(0,1fr))}.field.wide{grid-column:span 2}.head{align-items:flex-start;flex-direction:column}}@media(max-width:420px){.form{grid-template-columns:1fr}.field.wide{grid-column:auto}}
</style></head><body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a href="/notifications.php">Уведомления</a></div></nav></header>
<main class="page"><div class="head"><div><h1>Контроль возмещения ущерба</h1><div class="muted">Установленная сумма, предъявленные требования, поступившие возмещения и остаток</div></div><a class="btn" href="incidents.php">Журнал происшествий</a></div><div id="items"><div class="empty">Загружаем данные…</div></div></main>
<script>
(function(){
'use strict';const $=id=>document.getElementById(id);let csrf='',user=null,items=[];
function el(tag,cls,txt){const n=document.createElement(tag);if(cls)n.className=cls;if(txt!==undefined)n.textContent=txt;return n;}
async function read(r){const raw=await r.text();let d;try{d=JSON.parse(raw);}catch(e){throw new Error('Некорректный ответ сервера.');}if(!r.ok||!d.ok)throw new Error(d.error||'Ошибка запроса.');if(d.csrf)csrf=d.csrf;return d;}
async function get(action){return read(await fetch('api.php?action='+action,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}}));}
function money(v){return (Number(v)||0).toLocaleString('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2})+' ₽';}
function date(v){if(!v)return '—';const d=new Date(String(v).replace(' ','T'));return isNaN(d)?v:d.toLocaleDateString('ru-RU');}
function render(){
 const box=$('items');box.replaceChildren();
 if(!items.length){box.append(el('div','empty','Нет зарегистрированных случаев ущерба.'));return;}
 items.forEach(item=>{
  const card=el('article','card');card.append(el('h2','', '#'+item.id+' · '+item.title),el('div','meta',item.object_name+' · '+date(item.occurred_at)+' · '+(item.creator_name||'Сотрудник')));
  const stats=el('div','stats');
  [['Установлено',item.damage_amount],['Предъявлено',item.claimed_amount],['Возмещено',item.reimbursed_amount],['Остаток',item.balance_amount]].forEach(([label,value])=>{const s=el('div','stat');s.append(el('small','',label),el('strong','',money(value)));stats.append(s);});card.append(stats);
  if(user.permissions&&user.permissions['damage.manage']){
   const form=el('form','form');form.dataset.id=item.id;
   const f1=el('div','field'),l1=el('label','', 'Предъявленная сумма, ₽'),i1=el('input');i1.name='claimed_amount';i1.inputMode='decimal';i1.value=String(item.claimed_amount||item.damage_amount||0);f1.append(l1,i1);
   const f2=el('div','field'),l2=el('label','','Новое возмещение, ₽'),i2=el('input');i2.name='payment_amount';i2.inputMode='decimal';i2.placeholder='Необязательно';f2.append(l2,i2);
   const f3=el('div','field'),l3=el('label','','Дата возмещения'),i3=el('input');i3.type='date';i3.name='paid_at';i3.value=new Date().toISOString().slice(0,10);f3.append(l3,i3);
   const f4=el('div','field wide'),l4=el('label','','Комментарий к платежу'),i4=el('input');i4.name='note';i4.maxLength=500;i4.placeholder='Например, частичное возмещение';f4.append(l4,i4);
   const act=el('div','actions');const btn=el('button','btn primary','Сохранить');btn.type='submit';act.append(btn);form.append(f1,f2,f3,f4,act);
   form.addEventListener('submit',async e=>{e.preventDefault();btn.disabled=true;btn.textContent='Сохраняем…';try{await read(await fetch('api.php?action=damage_update',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({csrf,id:item.id,claimed_amount:i1.value,payment_amount:i2.value,paid_at:i3.value,note:i4.value})}));await load();}catch(err){alert(err.message);}finally{btn.disabled=false;btn.textContent='Сохранить';}});
   card.append(form);card.append(el('div','notice','Можно изменить предъявленную сумму и добавить один новый платёж. Сумма всех возмещений не может превышать предъявленную сумму.'));
  }
  if(item.payments&&item.payments.length){const list=el('div','payments');list.append(el('h3','','История возмещений'));item.payments.forEach(p=>{const row=el('div','payment');row.append(el('span','',date(p.paid_at)+' · '+(p.note||p.author_name||'Платёж')),el('strong','',money(p.amount)));list.append(row);});card.append(list);}
  box.append(card);
 });
}
async function load(){try{const d=await get('damage_data');items=d.items||[];render();}catch(e){$('items').replaceChildren(el('div','empty',e.message));}}
async function init(){try{const s=await get('status');if(!s.authenticated){location.href='index.html';return;}user=s.user;csrf=s.csrf||'';if(!user.permissions||!user.permissions['damage.view'])throw new Error('Нет доступа к разделу возмещения ущерба.');await load();}catch(e){$('items').replaceChildren(el('div','empty',e.message));}}
init();
})();
</script><script>(function(){'use strict';function ping(){if(document.visibilityState!=='hidden')fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,60000);document.addEventListener('visibilitychange',ping);})();</script><script src="site-header.js"></script>
</body></html>