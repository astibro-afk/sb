<?php
declare(strict_types=1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
ini_set('session.use_strict_mode','1'); ini_set('session.gc_maxlifetime','28800');
$isHttps=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Strict']);
session_name('sbapp_session'); session_start();
if(empty($_SESSION['sb_user_id'])){header('Location: index.html');exit;}
?>
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#f3f7f8"><title>Происшествия — СБ</title><link rel="stylesheet" href="site-theme.css"><link rel="stylesheet" href="site-header.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f7f8;color:#18343a;font:14px/1.5 Inter,Arial,sans-serif}.page{max-width:1120px;margin:auto;padding:24px 16px 48px}.page-head{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin-bottom:18px}.page-head h1{font-size:25px;margin:0}.muted{color:#71858a;font-size:12px}.panel,.incident{background:#fff;border:1px solid #e1eaeb;border-radius:14px;padding:17px;margin-bottom:14px;box-shadow:0 5px 20px rgba(24,52,58,.04)}.panel h2{font-size:16px;margin:0 0 12px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.field{display:flex;flex-direction:column;gap:5px}.field.full{grid-column:1/-1}.field label{font-size:11px;font-weight:750}.field input,.field select,.field textarea{width:100%;border:1px solid #dce7e9;border-radius:9px;padding:10px 11px;background:#fff;color:#18343a;font:13px Inter,Arial,sans-serif}.field textarea{min-height:84px;resize:vertical}.btn{border:1px solid #dce7e9;background:#fff;color:#18343a;border-radius:9px;padding:9px 13px;font-weight:750;font-size:12px;cursor:pointer}.btn.primary{background:#08777b;border-color:#08777b;color:#fff}.btn:disabled{opacity:.55;cursor:wait}.form-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:12px}.message{display:none;margin:10px 0;padding:10px 12px;border-radius:9px;font-size:12px}.message.error{display:block;background:#fff0ed;color:#a63f2d}.message.success{display:block;background:#eaf7f2;color:#176b55}.incident-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.incident h3{margin:0;font-size:15px}.tag{display:inline-flex;padding:3px 8px;border-radius:20px;background:#edf6f4;color:#286d6d;font-size:10px;font-weight:750}.meta{display:flex;flex-wrap:wrap;gap:7px;margin:8px 0;color:#71858a;font-size:11px}.desc{white-space:pre-wrap;overflow-wrap:anywhere;margin:10px 0;color:#344f55}.photos{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}.photos a{display:block;width:94px;height:80px;border-radius:8px;overflow:hidden;border:1px solid #e1eaeb}.photos img{width:100%;height:100%;object-fit:cover}.empty{padding:24px;text-align:center;color:#71858a;background:#fff;border:1px dashed #cbd9dc;border-radius:12px}.top-actions{display:flex;gap:8px;flex-wrap:wrap}@media(max-width:650px){.page-head{align-items:flex-start;flex-direction:column}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.incident-head{flex-direction:column}}
</style></head><body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a href="/notifications.php">Уведомления</a></div></nav></header>
<main class="page"><div class="page-head"><div><h1>Журнал происшествий</h1><div class="muted">Регистрация событий, фотографии и сведения об ущербе</div></div><div class="top-actions"><a class="btn" href="damage.php">Возмещение ущерба</a></div></div>
<section class="panel" id="createPanel"><h2>Быстро зарегистрировать происшествие</h2><form id="incidentForm"><div class="form-grid">
<div class="field"><label for="object">Торговый объект *</label><select id="object" name="object_id" required></select></div>
<div class="field"><label for="type">Тип происшествия *</label><select id="type" name="incident_type"><option>Кража</option><option>Конфликт</option><option>Нарушение</option><option>Задержание</option><option>Повреждение имущества</option><option>Недостача</option><option>Мошенничество</option><option>Прочее</option></select></div>
<div class="field"><label for="title">Краткое название *</label><input id="title" name="title" maxlength="180" required placeholder="Например: выявлена недостача товара"></div>
<div class="field"><label for="occurred">Дата и время происшествия *</label><input id="occurred" name="occurred_at" type="datetime-local" required></div>
<div class="field"><label for="damage">Установленная сумма ущерба, ₽</label><input id="damage" name="damage_amount" inputmode="decimal" value="0" placeholder="0.00"></div>
<div class="field"><label for="photos">Фотографии (до 5 файлов, JPG/PNG/WEBP, до 5 МБ каждый)</label><input id="photos" name="photos" type="file" accept="image/jpeg,image/png,image/webp" multiple></div>
<div class="field full"><label for="description">Описание обстоятельств</label><textarea id="description" name="description" maxlength="10000" placeholder="Что произошло, какие действия предприняты…"></textarea></div>
<div class="field full"><label for="persons">Участники и свидетели</label><textarea id="persons" name="persons" maxlength="3000" placeholder="ФИО, должности, контакты (если известны)"></textarea></div>
</div><div id="formMessage" class="message" role="status"></div><div class="form-actions"><button class="btn primary" id="saveIncident" type="submit">Зарегистрировать происшествие</button></div></form></section>
<section><div class="page-head"><div><h2 style="font-size:17px;margin:0">Последние происшествия</h2><div class="muted" id="count">Загрузка…</div></div><button class="btn" id="reload" type="button">Обновить</button></div><div id="incidents"><div class="empty">Загружаем происшествия…</div></div></section>
</main>
<script>
(function(){
'use strict';
const $=id=>document.getElementById(id);let csrf='',user=null,objects=[];
function node(tag,cls,txt){const n=document.createElement(tag);if(cls)n.className=cls;if(txt!==undefined)n.textContent=txt;return n;}
async function read(r){const raw=await r.text();let d;try{d=JSON.parse(raw);}catch(e){throw new Error('Некорректный ответ сервера (HTTP '+r.status+').');}if(!r.ok||!d.ok)throw new Error(d.error||'Ошибка запроса.');if(d.csrf)csrf=d.csrf;return d;}
async function get(action){return read(await fetch('api.php?action='+action,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}}));}
function localNow(){const d=new Date(Date.now()-new Date().getTimezoneOffset()*60000);return d.toISOString().slice(0,16);}
function date(v){if(!v)return '—';const d=new Date(String(v).replace(' ','T'));return isNaN(d)?v:d.toLocaleString('ru-RU');}
function money(v){return (Number(v)||0).toLocaleString('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2})+' ₽';}
function message(text,kind){const n=$('formMessage');n.textContent=text;n.className='message '+kind;}
function render(rows){
 const box=$('incidents');box.replaceChildren();$('count').textContent='Записей: '+rows.length;
 if(!rows.length){box.append(node('div','empty','Происшествий пока нет.'));return;}
 rows.forEach(item=>{
  const card=node('article','incident'),head=node('div','incident-head'),title=node('h3','', '#'+item.id+' · '+item.title);
  head.append(title,node('span','tag',item.incident_type||'Прочее'));card.append(head);
  card.append(node('div','meta',item.object_name+' · '+date(item.occurred_at)+' · '+(item.creator_name||'Сотрудник')));
  if(item.description)card.append(node('p','desc',item.description));
  if(item.persons)card.append(node('div','meta','Участники: '+item.persons));
  if(Number(item.damage_amount)>0)card.append(node('div','meta','Установленный ущерб: '+money(item.damage_amount)+' · Предъявлено: '+money(item.claimed_amount)+' · Возмещено: '+money(item.reimbursed_amount)+' · Остаток: '+money(item.balance_amount)));
  if(item.files&&item.files.length){const photos=node('div','photos');item.files.forEach(file=>{const a=node('a');a.href='api.php?action=incident_file&id='+encodeURIComponent(file.id);a.target='_blank';a.rel='noopener';const img=node('img');img.src=a.href;img.alt=file.original_name||'Фото происшествия';a.append(img);photos.append(a);});card.append(photos);}
  box.append(card);
 });
}
async function load(){try{const d=await get('incidents_data');render(d.incidents||[]);}catch(e){$('incidents').replaceChildren(node('div','empty',e.message));}}
async function init(){
 try{
  const s=await get('status');if(!s.authenticated){location.href='index.html';return;}user=s.user;csrf=s.csrf||'';
  if(!user.permissions||!user.permissions['incidents.create'])$('createPanel').hidden=true;
  const o=await get('objects_list');objects=o.objects||[];$('object').replaceChildren();
  objects.forEach(x=>$('object').add(new Option(x.name,String(x.id))));
  if(!objects.length){$('object').add(new Option('Нет доступных объектов',''));$('saveIncident').disabled=true;}
  $('occurred').value=localNow();await load();
 }catch(e){message(e.message,'error');}
}
$('incidentForm').addEventListener('submit',async e=>{
 e.preventDefault();message('','');const btn=$('saveIncident');btn.disabled=true;btn.textContent='Сохраняем…';
 try{
  const form=new FormData();form.append('csrf',csrf);['object_id','incident_type','title','occurred_at','description','persons','damage_amount'].forEach(k=>{const el=document.querySelector('[name="'+k+'"]');form.append(k,el.value);});
  Array.from($('photos').files||[]).forEach(file=>form.append('photos[]',file));
  const d=await read(await fetch('api.php?action=incident_create',{method:'POST',credentials:'same-origin',body:form}));
  $('incidentForm').reset();$('occurred').value=localNow();$('damage').value='0';message('Происшествие №'+d.id+' зарегистрировано.','success');await load();
 }catch(err){message(err.message,'error');}
 finally{btn.disabled=false;btn.textContent='Зарегистрировать происшествие';}
});
$('reload').addEventListener('click',load);init();
})();
</script><script>(function(){'use strict';function ping(){if(document.visibilityState!=='hidden')fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,60000);document.addEventListener('visibilitychange',ping);})();</script><script src="site-header.js"></script>
</body></html>