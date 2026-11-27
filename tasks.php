<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
ini_set('session.use_strict_mode','1');
$isHttps=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Strict']);
session_name('sbapp_session');session_start();
if(empty($_SESSION['sb_user_id'])){header('Location: index.html');exit;}
?>
<!doctype html>
<html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#f3f8f7"><title>Задачи — СБ</title><link rel="stylesheet" href="site-theme.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f8f7;color:#1a3438;font:14px/1.5 Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif}.page{max-width:1180px;margin:auto;padding:24px 18px 48px}.top{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px}.heading h1{font-size:25px;line-height:1.2;margin:0}.heading p{margin:6px 0 0;color:#668085}.actions{display:flex;gap:8px;flex-wrap:wrap}.btn{border:1px solid #d5e5e2;background:#fff;color:#214347;border-radius:10px;padding:9px 13px;font-weight:650;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px}.btn.primary{background:#08777b;border-color:#08777b;color:white}.btn:disabled{opacity:.55;cursor:not-allowed}.summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px}.stat{background:#fff;border:1px solid #e0ebe8;border-radius:13px;padding:13px 15px}.stat strong{display:block;font-size:23px;line-height:1.2}.stat span{color:#71888a;font-size:12px}.toolbar{display:flex;gap:9px;align-items:center;flex-wrap:wrap;margin:0 0 14px}.toolbar input,.toolbar select,.form input,.form select,.form textarea,.comment-form textarea{width:100%;border:1px solid #d8e6e3;background:#fff;border-radius:9px;padding:10px 11px;color:#1a3438;font:inherit}.toolbar .search{flex:1 1 230px}.toolbar select{width:auto;min-width:150px}.task-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.task{background:#fff;border:1px solid #e0ebe8;border-radius:14px;padding:16px;box-shadow:0 5px 18px rgba(26,52,56,.035);min-width:0}.task.overdue{border-color:#e7b8b1}.task-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.task h2{font-size:16px;line-height:1.35;margin:0 0 7px;overflow-wrap:anywhere}.badges{display:flex;gap:5px;flex-wrap:wrap}.tag{font-size:11px;font-weight:700;padding:4px 8px;border-radius:20px;background:#edf4f3;color:#4c696b;white-space:nowrap}.tag.high{background:#fff0ed;color:#b44335}.tag.low{background:#eef5ff;color:#426a9b}.tag.done{background:#e9f6ef;color:#27734a}.tag.review{background:#fff5df;color:#8a6418}.tag.overdue{background:#fff0ed;color:#b44335}.desc{color:#536e70;white-space:pre-wrap;overflow-wrap:anywhere;margin:10px 0}.meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px 12px;color:#6c8385;font-size:12px;margin:10px 0}.meta b{color:#29494c;font-weight:650}.task-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}.task-actions .btn{font-size:12px;padding:7px 10px}.comments{border-top:1px solid #edf2f1;margin-top:13px;padding-top:10px}.comments summary{cursor:pointer;color:#557477;font-size:12px;font-weight:700}.comment{padding:8px 0;border-bottom:1px solid #f0f4f3}.comment:last-child{border:0}.comment b{font-size:12px}.comment small{color:#849797;margin-left:6px;font-size:10px}.comment p{margin:3px 0 0;color:#536e70;white-space:pre-wrap}.comment-form{display:flex;gap:7px;margin-top:9px}.comment-form textarea{min-height:38px;max-height:100px;resize:vertical;flex:1}.comment-form .btn{align-self:flex-end}.form-panel{background:#fff;border:1px solid #dce9e6;border-radius:14px;padding:16px;margin-bottom:16px}.form-panel h2{font-size:16px;margin:0 0 12px}.form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.field{display:flex;flex-direction:column;gap:5px;min-width:0}.field label{font-size:12px;font-weight:700;color:#597578}.field.wide{grid-column:span 2}.field.full{grid-column:1/-1}.form textarea{min-height:78px;resize:vertical}.form-footer{grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px}.empty{grid-column:1/-1;background:#fff;border:1px dashed #cbdcda;border-radius:14px;padding:34px;text-align:center;color:#71888a}.error{background:#fff1ef;color:#a53d31;border:1px solid #f0c9c3;border-radius:10px;padding:10px 12px;margin-bottom:12px}.pagination{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:16px}.pagination span{color:#607b7d;font-size:13px}.hidden{display:none!important}
@media(max-width:780px){.task-list{grid-template-columns:1fr}.summary{grid-template-columns:repeat(2,minmax(0,1fr))}.form{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:520px){.page{padding:18px 12px 36px}.top{align-items:flex-start;flex-direction:column}.heading h1{font-size:22px}.form{grid-template-columns:1fr}.field.wide{grid-column:auto}.toolbar select{flex:1;min-width:0}.meta{grid-template-columns:1fr}.task-head{flex-direction:column}.comment-form{flex-direction:column}.comment-form .btn{align-self:flex-start}}
</style><link rel="stylesheet" href="site-header.css">
</head><body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a class="active" href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a href="/notifications.php">Уведомления</a></div></nav></header>
<main class="page">
<div class="sb-page-title"><div><h1>Задачи и поручения</h1><p>Постановка задач, контроль сроков и обратная связь по объектам</p></div><button class="btn" id="reload" type="button">Обновить</button></div>
<div class="summary"><div class="stat"><strong id="total">—</strong><span>Всего задач</span></div><div class="stat"><strong id="active">—</strong><span>В работе</span></div><div class="stat"><strong id="reviewCount">—</strong><span>На проверке</span></div><div class="stat"><strong id="overdue">—</strong><span>Просрочено</span></div></div>
<div id="error" class="error hidden" role="alert"></div>
<section id="createPanel" class="form-panel hidden"><h2>Поставить новую задачу</h2><form class="form" id="createForm">
<div class="field wide"><label for="title">Название задачи *</label><input id="title" maxlength="180" required placeholder="Например: Проверить соблюдение кассовой дисциплины"></div>
<div class="field"><label for="object">Объекты (все активные сотрудники, кроме вас)</label><select id="object" multiple size="4" aria-label="Выберите один или несколько объектов"></select></div>
<div class="field"><label for="assignee">Дополнительные сотрудники (себя выбрать нельзя)</label><select id="assignee" multiple size="4" aria-label="Выберите одного или нескольких сотрудников"></select></div>
<div class="field"><label for="priority">Приоритет</label><select id="priority"><option value="normal">Обычный</option><option value="high">Высокий</option><option value="low">Низкий</option></select></div>
<div class="field"><label for="due">Срок выполнения</label><input id="due" type="date"></div>
<div class="field full"><label for="description">Описание и ожидаемый результат</label><textarea id="description" maxlength="5000" placeholder="Опишите задачу, ожидаемый результат и важные детали"></textarea></div>
<div class="form-footer"><button class="btn primary" type="submit" id="createBtn">Создать задачу</button></div>
</form></section>
<div class="toolbar"><input class="search" id="search" type="search" placeholder="Поиск по задаче, объекту или сотруднику"><select id="statusFilter"><option value="">Все статусы</option><option value="new">Новые</option><option value="in_progress">В работе</option><option value="review">На проверке</option><option value="done">Выполнены</option></select><select id="objectFilter"><option value="">Все объекты</option></select></div>
<section class="task-list" id="tasks"><div class="empty">Загружаем задачи…</div></section>
<div class="pagination" id="pagination" hidden><button class="btn" id="prevPage" type="button">Назад</button><span id="pageInfo"></span><button class="btn" id="nextPage" type="button">Далее</button></div>
</main>
<script>
(function(){
'use strict';
const $=id=>document.getElementById(id),tasksEl=$('tasks');
let state={tasks:[],objects:[],assignees:[],user:null,csrf:'',stats:{},pagination:{page:1,total_pages:1,total:0}};let loadToken=0,searchTimer=null;
const statusLabels={new:'Новая',in_progress:'В работе',review:'На проверке',done:'Выполнена'};
const priorityLabels={high:'Высокий приоритет',normal:'Обычный приоритет',low:'Низкий приоритет'};
function node(tag,cls,txt){const n=document.createElement(tag);if(cls)n.className=cls;if(txt!==undefined)n.textContent=txt;return n;}
async function json(response){const raw=await response.text();if(!raw.trim())throw new Error('Сервер вернул пустой ответ. Обновите страницу.');try{return JSON.parse(raw);}catch(e){throw new Error('Сервер вернул некорректный ответ.');}}
async function api(action,body){const r=await fetch('api.php?action='+encodeURIComponent(action),{method:body?'POST':'GET',credentials:'same-origin',cache:'no-store',headers:body?{'Content-Type':'application/json'}:{},body:body?JSON.stringify({...body,csrf:state.csrf}):undefined});const d=await json(r);if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось выполнить запрос.');return d;}
async function get(action,params){const query=new URLSearchParams(params||{});const r=await fetch('api.php?action='+encodeURIComponent(action)+'&'+query.toString(),{credentials:'same-origin',cache:'no-store'});const d=await json(r);if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось выполнить запрос.');return d;}
function showError(message){$('error').textContent=message;$('error').classList.remove('hidden');}
function clearError(){$('error').textContent='';$('error').classList.add('hidden');}
function date(v){if(!v)return 'Без срока';const d=new Date(v+'T00:00:00');return isNaN(d)?v:d.toLocaleDateString('ru-RU',{day:'2-digit',month:'long',year:'numeric'});}
function time(v){if(!v)return '';const d=new Date(String(v).replace(' ','T'));return isNaN(d)?v:d.toLocaleString('ru-RU',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});}
function fillAssignees(){const sel=$('assignee');sel.replaceChildren();state.assignees.filter(u=>!state.user||Number(u.id)!==Number(state.user.id)).forEach(u=>sel.add(new Option(u.full_name+' · '+(u.role==='moderator'?'модератор':'сотрудник'),u.id)));}
function stats(){const s=state.stats||{};$('total').textContent=Number(s.total||0);$('active').textContent=Number(s.active||0);$('reviewCount').textContent=Number(s.review||0);$('overdue').textContent=Number(s.overdue||0);}
function renderPagination(){const p=state.pagination||{page:1,total_pages:1,total:0};const wrap=$('pagination');wrap.hidden=Number(p.total_pages)<=1;$('prevPage').disabled=Number(p.page)<=1;$('nextPage').disabled=Number(p.page)>=Number(p.total_pages);$('pageInfo').textContent='Страница '+p.page+' из '+p.total_pages+' · задач: '+p.total;}
function render(){
 const rows=state.tasks;
 stats();renderPagination();tasksEl.replaceChildren();
 if(!rows.length){tasksEl.append(node('div','empty',Number(state.stats.total||0)>0?'По заданным фильтрам задач нет.':'Пока задач нет. Новые поручения появятся здесь.'));return;}
 rows.forEach(t=>{
  const card=node('article','task'+(t.is_overdue?' overdue':''));const head=node('div','task-head'),left=node('div');left.append(node('h2','',t.title));
  const badges=node('div','badges');badges.append(node('span','tag '+t.status,t.status==='done'?statusLabels.done:statusLabels[t.status]));badges.append(node('span','tag '+t.priority,t.priority==='high'?'Высокий':t.priority==='low'?'Низкий':'Обычный'));if(t.is_overdue)badges.append(node('span','tag overdue','Просрочена'));left.append(badges);head.append(left);card.append(head);
  if(t.description)card.append(node('p','desc',t.description));
  const meta=node('div','meta');[['Объект',t.object_names||t.object_name],['Исполнители',t.assignee_names||t.assignee_name],['Поставил',t.creator_name],['Срок',date(t.due_date)],['Создана',time(t.created_at)],['Обновлена',time(t.updated_at)]].forEach(([k,v])=>{const x=node('div');x.append(node('b','',k+': '),document.createTextNode(v||'—'));meta.append(x);});card.append(meta);
  const actions=node('div','task-actions');
  if(t.can_update){
   if(t.status==='new'||t.status==='review'){const b=node('button','btn',t.status==='review'?'Вернуть в работу':'Взять в работу');b.type='button';b.addEventListener('click',()=>change(t.id,{status:'in_progress'}));actions.append(b);}
   if(t.status==='in_progress'&&t.is_recipient){const b=node('button','btn primary','На проверку');b.type='button';b.addEventListener('click',()=>change(t.id,{status:'review'}));actions.append(b);}
   if(t.status==='review'&&t.can_manage){const b=node('button','btn primary','Принять выполнение');b.type='button';b.addEventListener('click',()=>change(t.id,{status:'done'}));actions.append(b);}
   if(t.status==='done'&&t.can_manage){const b=node('button','btn','Вернуть в работу');b.type='button';b.addEventListener('click',()=>change(t.id,{status:'in_progress'}));actions.append(b);}
  }
  if(actions.childNodes.length)card.append(actions);
  const details=node('details','comments'),summary=node('summary','','Комментарии ('+(t.comment_count||0)+')');details.append(summary);
  const commentItems=node('div','comments-list');details.append(commentItems);
  details.addEventListener('toggle',async()=>{
   if(!details.open||details.dataset.loaded==='1')return;
   details.dataset.loaded='1';commentItems.replaceChildren(node('div','comment','Загружаем комментарии…'));
   try{
    const data=await get('task_comments',{id:t.id});commentItems.replaceChildren();
    (data.comments||[]).forEach(c=>{const item=node('div','comment');item.append(node('b','',c.user_name||'Сотрудник'),node('small','',time(c.created_at)),node('p','',c.comment));commentItems.append(item);});
    summary.textContent='Комментарии ('+(data.comments||[]).length+')';
    if(!(data.comments||[]).length)commentItems.append(node('div','comment','Комментариев пока нет.'));
   }catch(err){details.dataset.loaded='0';commentItems.replaceChildren(node('div','comment',err.message));}
  });
  const form=node('form','comment-form'),area=node('textarea');area.maxLength=3000;area.rows=1;area.placeholder='Добавить комментарий…';const send=node('button','btn','Отправить');send.type='submit';form.append(area,send);form.addEventListener('submit',e=>{e.preventDefault();const comment=area.value.trim();if(comment)change(t.id,{comment});});details.append(form);card.append(details);tasksEl.append(card);
 });
}
async function change(id,payload){clearError();try{await api('task_update',{id,...payload});await load(state.pagination.page||1);}catch(e){showError(e.message);}}
async function load(page){const token=++loadToken;clearError();try{
 const params={page:page||state.pagination.page||1,search:$('search').value.trim(),status:$('statusFilter').value,object_id:$('objectFilter').value};
 const d=await get('tasks_data',params);if(token!==loadToken)return;
 state.tasks=d.tasks||[];state.stats=d.stats||{};state.pagination=d.pagination||{page:1,total_pages:1,total:0};state.objects=d.objects||[];state.assignees=d.assignees||[];state.user=d.user;state.csrf=d.csrf||'';
 const canCreate=state.user.role==='admin'||state.user.role==='moderator';$('createPanel').classList.toggle('hidden',!canCreate);
 const selectedObject=$('object').value;
 $('object').replaceChildren(new Option('Выберите объект',''));$('objectFilter').replaceChildren(new Option('Все объекты',''));
 state.objects.forEach(o=>{$('object').add(new Option(o.name,o.id));$('objectFilter').add(new Option(o.name,o.id));});
 $('objectFilter').value=params.object_id||'';
 if(selectedObject&&state.objects.some(o=>String(o.id)===selectedObject))$('object').value=selectedObject;
 fillAssignees();render();
 }catch(e){if(token!==loadToken)return;showError(e.message);tasksEl.replaceChildren(node('div','empty','Не удалось загрузить задачи.'));}}
fillAssignees();
$('createForm').addEventListener('submit',async e=>{e.preventDefault();clearError();const b=$('createBtn');b.disabled=true;try{const object_ids=Array.from($('object').selectedOptions).map(o=>Number(o.value));const assignee_ids=Array.from($('assignee').selectedOptions).map(o=>Number(o.value));await api('task_create',{title:$('title').value.trim(),description:$('description').value.trim(),object_ids,assignee_ids,priority:$('priority').value,due_date:$('due').value});$('createForm').reset();fillAssignees();await load(1);}catch(err){showError(err.message);}finally{b.disabled=false;}});
$('search').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>load(1),300);});
$('statusFilter').addEventListener('change',()=>load(1));$('objectFilter').addEventListener('change',()=>load(1));
$('prevPage').addEventListener('click',()=>load(Math.max(1,Number(state.pagination.page)-1)));
$('nextPage').addEventListener('click',()=>load(Math.min(Number(state.pagination.total_pages),Number(state.pagination.page)+1)));
$('reload').addEventListener('click',()=>load(state.pagination.page||1));load(1);
})();
</script>
<script>(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,60000);document.addEventListener('visibilitychange',ping);})();</script>
<script src="site-header.js"></script>
</body></html>