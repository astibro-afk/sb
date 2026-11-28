<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
ini_set('session.use_strict_mode','1');
$isHttps=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Strict']);
session_name('sbapp_session');
session_start();
if (empty($_SESSION['sb_user_id'])) { header('Location: index.html'); exit; }
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#f3f7f8">
<title>База документов — СБ</title>
<link rel="stylesheet" href="site-theme.css">
<link rel="stylesheet" href="site-header.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f3f7f8;color:#18343a;font:14px/1.5 Inter,Arial,sans-serif}
.page{max-width:1120px;margin:auto;padding:25px 16px 48px}
.title-row{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:19px}
h1{font-size:25px;letter-spacing:-.4px;margin:0}
.subtitle{margin:5px 0 0;color:#71858a}
.btn{border:1px solid #dce7e9;background:#fff;color:#18343a;border-radius:9px;padding:9px 13px;font:700 12px Inter,Arial,sans-serif;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px}
.btn:hover{border-color:#9bc9c5;color:#08777b}
.btn.primary{background:#08777b;color:#fff;border-color:#08777b}
.btn.danger{color:#a84438;border-color:#efd1cc}
.btn:disabled{opacity:.55;cursor:wait}
.panel{background:#fff;border:1px solid #e1eaec;border-radius:14px;padding:17px;margin-bottom:15px;box-shadow:0 5px 20px rgba(24,52,58,.04)}
.panel h2{font-size:15px;margin:0 0 13px}
.form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px}
.field{display:flex;flex-direction:column;gap:5px;min-width:0}
.field.wide{grid-column:span 2}
.field.full{grid-column:1/-1}
label{font-size:11px;font-weight:750;color:#597578}
input,select,textarea{width:100%;border:1px solid #dce7e9;border-radius:9px;background:#fff;padding:10px 11px;color:#18343a;font:13px Inter,Arial,sans-serif;min-width:0}
textarea{min-height:70px;resize:vertical}
.form-footer{grid-column:1/-1;display:flex;justify-content:flex-end;align-items:center;gap:10px}
.hint{font-size:11px;color:#819398}
.toolbar{display:grid;grid-template-columns:minmax(0,1fr) 230px auto;gap:9px;margin:16px 0}
.doc-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
.doc{background:#fff;border:1px solid #e1eaec;border-radius:13px;padding:15px;min-width:0;box-shadow:0 4px 16px rgba(24,52,58,.035)}
.doc-top{display:flex;align-items:flex-start;gap:11px}
.file-icon{width:40px;height:44px;border-radius:9px;background:#edf6f4;color:#08777b;display:grid;place-items:center;font-size:10px;font-weight:850;flex-shrink:0;text-transform:uppercase}
.doc-main{min-width:0;flex:1}
.doc h3{font-size:14px;line-height:1.35;margin:1px 0 4px;overflow-wrap:anywhere}
.filename{font-size:11px;color:#819398;overflow-wrap:anywhere}
.doc-desc{font-size:12px;color:#536a70;margin:11px 0 0;white-space:pre-wrap;overflow-wrap:anywhere}
.doc-meta{display:flex;flex-wrap:wrap;gap:6px 12px;color:#819398;font-size:10px;margin-top:12px}
.pill{display:inline-flex;align-items:center;border-radius:20px;padding:3px 8px;background:#edf6f4;color:#286d6d;font-size:10px;font-weight:750}
.doc-actions{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-top:13px;padding-top:11px;border-top:1px solid #edf2f3}
.doc-actions .right{display:flex;gap:6px;flex-wrap:wrap}
.empty,.error,.success{padding:25px 12px;text-align:center;border-radius:12px;font-size:12px}
.empty{border:1px dashed #cbd9dc;color:#71858a;background:#fff}
.error{display:none;background:#fff0ed;border:1px solid #f0c9c3;color:#a63f2d;margin-bottom:12px;text-align:left}
.success{display:none;background:#edf8f3;border:1px solid #cfe9dc;color:#286d54;margin-bottom:12px;text-align:left}
.loading{color:#71858a;text-align:center;padding:25px}
.ack-state{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-top:12px;padding:10px 11px;border:1px solid #e3eded;border-radius:10px;background:#f8fbfb}
.ack-state-text{font-size:11px;color:#49666b;font-weight:700}
.ack-state-text.done{color:#26734f}
.ack-panel{margin-top:10px;padding:12px;border:1px solid #e3eded;border-radius:10px;background:#fbfdfd}
.ack-summary{font-size:12px;font-weight:800;margin-bottom:9px}
.ack-list{display:grid;gap:7px}
.ack-person{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;padding:7px 0;border-top:1px solid #edf2f3}
.ack-person-name{font-size:12px;font-weight:700}
.ack-person-meta{font-size:10px;color:#819398}
.ack-person-status{font-size:10px;font-weight:750;white-space:nowrap}
.ack-person-status.done{color:#26734f}
.ack-person-status.pending{color:#a76a27}
.check-field{display:flex;align-items:flex-start;gap:9px;font-size:12px;font-weight:700;color:#31565b}
.check-field input{width:16px;height:16px;margin:1px 0 0;flex:0 0 auto}
.check-hint{margin-left:25px}
@media(max-width:760px){.doc-list{grid-template-columns:1fr}.form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.toolbar{grid-template-columns:1fr 1fr}.toolbar input{grid-column:1/-1}}
@media(max-width:520px){.page{padding:18px 12px 36px}.title-row{flex-direction:column}h1{font-size:22px}.form-grid{grid-template-columns:1fr}.field.wide,.field.full{grid-column:auto}.form-footer{justify-content:stretch}.form-footer .btn{width:100%}.toolbar{grid-template-columns:1fr}.toolbar input{grid-column:auto}}
</style>
</head>
<body>
<header class="sb-site-header">
 <div class="sb-site-top">
  <a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a>
  <div class="sb-site-actions">
   <div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div>
   <a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a>
   <a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a>
   <a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a>
   <button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button>
  </div>
 </div>
 <nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner">
  <a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a class="active" href="/documents.php">База документов</a><a href="/notifications.php">Уведомления</a>
 </div></nav>
</header>
<main class="page">
 <div class="title-row"><div><h1>База документов</h1><p class="subtitle">Инструкции, регламенты, приказы, памятки и рабочие материалы службы безопасности</p></div><button class="btn" id="reload" type="button">Обновить список</button></div>
 <div class="error" id="error" role="alert"></div><div class="success" id="success" role="status"></div>
 <section class="panel" id="uploadPanel" hidden>
  <h2>Добавить документ</h2>
  <form id="uploadForm" class="form-grid" enctype="multipart/form-data">
   <div class="field wide"><label for="title">Название документа *</label><input id="title" name="title" maxlength="180" required placeholder="Например: Порядок действий при выявлении хищения"></div>
   <div class="field"><label for="category">Категория *</label><select id="category" name="category"><option>Инструкции</option><option>Регламенты</option><option>Приказы и распоряжения</option><option>Памятки</option><option>Формы и шаблоны</option><option>Прочее</option></select></div>
   <div class="field"><label for="object">Доступ</label><select id="object" name="object_id"></select><span class="hint" id="scopeHint"></span></div>
   <div class="field full"><label for="description">Описание</label><textarea id="description" name="description" maxlength="3000" placeholder="Кратко опишите содержание документа и кому он предназначен"></textarea></div>
   <div class="field full"><label class="check-field"><input id="requiresAck" type="checkbox" name="requires_ack" value="1"> Требуется подтверждение ознакомления</label><span class="hint check-hint">Сотрудникам, которым доступен документ, придёт уведомление. Руководитель увидит, кто подтвердил прочтение.</span></div>
   <div class="field full"><label for="file">Файл *</label><input id="file" name="file" type="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.odt,.ods,.odp,.jpg,.jpeg,.png"><span class="hint">PDF, Word, Excel, PowerPoint, ODT/ODS/ODP, TXT/CSV, JPG, PNG. Максимум 20 МБ.</span></div>
   <div class="form-footer"><button class="btn primary" id="uploadBtn" type="submit">Загрузить документ</button></div>
  </form>
 </section>
 <div class="toolbar">
  <input id="search" type="search" placeholder="Поиск по названию, описанию или имени файла">
  <select id="categoryFilter"><option value="">Все категории</option></select>
  <span class="hint" id="count" style="align-self:center"></span>
 </div>
 <section class="doc-list" id="documents"><div class="loading">Загружаем документы…</div></section>
</main>
<script>
(function(){
'use strict';
const $=id=>document.getElementById(id);
const state={csrf:'',user:null,documents:[],objects:[],categories:[]};
let searchTimer=null,loading=false,pendingLoad=false;
const fixedCategories=['Инструкции','Регламенты','Приказы и распоряжения','Памятки','Формы и шаблоны','Прочее'];
function node(tag,cls,text){const n=document.createElement(tag);if(cls)n.className=cls;if(text!==undefined)n.textContent=text;return n;}
function showError(message){$('error').textContent=message;$('error').style.display='block';$('success').style.display='none';}
function showSuccess(message){$('success').textContent=message;$('success').style.display='block';$('error').style.display='none';}
function clearMessages(){$('error').style.display='none';$('success').style.display='none';}
async function read(response){const raw=await response.text();let data;try{data=JSON.parse(raw);}catch(e){throw new Error('Сервер вернул некорректный ответ.');}if(!response.ok||!data.ok)throw new Error(data.error||'Не удалось выполнить запрос.');if(data.csrf)state.csrf=data.csrf;return data;}
function fileSize(bytes){const n=Number(bytes)||0;if(n<1024)return n+' Б';if(n<1048576)return (n/1024).toFixed(0)+' КБ';return (n/1048576).toFixed(1)+' МБ';}
function date(value){if(!value)return '—';const d=new Date(String(value).replace(' ','T'));return isNaN(d)?value:d.toLocaleDateString('ru-RU',{day:'2-digit',month:'short',year:'numeric'});}
function fillObjects(){
 const select=$('object');select.replaceChildren();
 if(state.user.role==='admin')select.add(new Option('Общий документ — доступен всем','0'));
 state.objects.forEach(o=>select.add(new Option(o.name,String(o.id))));
 $('scopeHint').textContent=state.user.role==='admin'?'Общий документ или выбранный объект':'Документ будет доступен сотрудникам ваших объектов';
 if(state.user.role==='moderator'&&state.objects.length===1)select.value=String(state.objects[0].id);
}
function fillCategories(){
 const selected=$('categoryFilter').value;
 const all=Array.from(new Set([...fixedCategories,...state.categories])).sort((a,b)=>a.localeCompare(b,'ru'));
 $('categoryFilter').replaceChildren(new Option('Все категории',''));
 all.forEach(c=>$('categoryFilter').add(new Option(c,c)));
 $('categoryFilter').value=selected;
}
async function acknowledgeDocument(doc,button){
 if(!confirm('Подтвердить, что вы ознакомились с документом «'+doc.title+'»?'))return;
 button.disabled=true;button.textContent='Сохраняем…';
 try{
  const response=await fetch('api.php?action=document_acknowledge',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({csrf:state.csrf,id:Number(doc.id)})});
  await read(response);await load();showSuccess('Ознакомление с документом подтверждено.');
 }catch(e){showError(e.message);button.disabled=false;button.textContent='Подтвердить ознакомление';}
}
async function toggleAckStatus(doc,panel,button){
 if(!panel.hidden){panel.hidden=true;button.textContent='Кто ознакомился';return;}
 panel.hidden=false;panel.replaceChildren(node('div','loading','Загружаем список ознакомления…'));button.textContent='Скрыть статус';
 try{
  const data=await read(await fetch('api.php?action=document_ack_status&id='+encodeURIComponent(doc.id),{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}}));
  panel.replaceChildren();
  panel.append(node('div','ack-summary','Ознакомились: '+data.done+' из '+data.total+' · Не ознакомились: '+data.pending));
  const list=node('div','ack-list');
  if(!data.employees.length)list.append(node('div','hint','Нет сотрудников в зоне контроля.'));
  data.employees.forEach(employee=>{
   const row=node('div','ack-person'),left=node('div',''),right=node('div','ack-person-status '+(employee.acknowledged?'done':'pending'));
   left.append(node('div','ack-person-name',employee.full_name||'Сотрудник'));
   if(employee.object_names)left.append(node('div','ack-person-meta',employee.object_names));
   right.textContent=employee.acknowledged?'Ознакомлен · '+date(employee.acknowledged_at):'Ожидает';
   row.append(left,right);list.append(row);
  });
  panel.append(list);
 }catch(e){panel.replaceChildren(node('div','hint',e.message));showError(e.message);}
}
function render(){
 const box=$('documents');box.replaceChildren();
 $('count').textContent='Документов: '+state.documents.length+(state.documents.length===250?' · показаны последние 250':'');
 if(!state.documents.length){box.append(node('div','empty','Документы не найдены. Измените условия поиска или добавьте первый документ.'));return;}
 state.documents.forEach(d=>{
  const card=node('article','doc'),top=node('div','doc-top'),icon=node('div','file-icon',(d.original_name.split('.').pop()||'FILE').slice(0,5));top.append(icon);
  const main=node('div','doc-main');main.append(node('h3','',d.title),node('div','filename',d.original_name));top.append(main);card.append(top);
  if(d.description)card.append(node('p','doc-desc',d.description));
  const meta=node('div','doc-meta');meta.append(node('span','pill',d.category||'Прочее'),node('span','',d.object_name||'Общий документ'),node('span','',fileSize(d.file_size)),node('span','',date(d.created_at)),node('span','',d.uploader_name||'Сотрудник'));card.append(meta);
  if(Number(d.requires_ack)===1){
   const ackBox=node('div','ack-state'),ackText=node('span','ack-state-text',state.user.role==='user'?(d.acknowledged?'Ознакомление подтверждено':'Требуется подтвердить ознакомление'):(state.user.role==='admin'?'Ознакомились: '+d.ack_done+' из '+d.ack_total:'Контроль ознакомления'));
   if(d.acknowledged)ackText.classList.add('done');
   ackBox.append(ackText);
   if(state.user.role==='user'&&!d.acknowledged){
    const confirmBtn=node('button','btn primary','Подтвердить ознакомление');confirmBtn.type='button';confirmBtn.addEventListener('click',()=>acknowledgeDocument(d,confirmBtn));ackBox.append(confirmBtn);
   }else if(state.user.role==='admin'||state.user.role==='moderator'){
    const statusBtn=node('button','btn','Кто ознакомился');statusBtn.type='button';
    const panel=node('div','ack-panel');panel.hidden=true;statusBtn.addEventListener('click',()=>toggleAckStatus(d,panel,statusBtn));ackBox.append(statusBtn);card.append(ackBox,panel);
   }
   if(state.user.role==='user'||!['admin','moderator'].includes(state.user.role))card.append(ackBox);
  }
  const actions=node('div','doc-actions'),right=node('div','right');
  const download=node('a','btn primary','Скачать файл');download.href='api.php?action=document_download&id='+encodeURIComponent(d.id);download.setAttribute('rel','noopener');right.append(download);
  actions.append(node('span','hint',d.mime_type||''),right);
  if(Number(d.can_delete)===1){const del=node('button','btn danger','Удалить');del.type='button';del.addEventListener('click',()=>removeDocument(d));actions.insertBefore(del, right);}
  card.append(actions);box.append(card);
 });
}
async function load(){
 if(loading){pendingLoad=true;return;}loading=true;clearMessages();
 try{
  const params=new URLSearchParams({action:'documents_data',search:$('search').value.trim(),category:$('categoryFilter').value});
  const data=await read(await fetch('api.php?'+params.toString(),{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}}));
  state.user=data.user;state.documents=data.documents||[];state.objects=data.objects||[];state.categories=data.categories||[];
  $('uploadPanel').hidden=!(state.user.role==='admin'||state.user.role==='moderator');
  fillObjects();fillCategories();render();
 }catch(e){showError(e.message);$('documents').replaceChildren(node('div','empty','Не удалось загрузить список документов.'));}
 finally{loading=false;if(pendingLoad){pendingLoad=false;load();}}
}
async function removeDocument(doc){
 if(!confirm('Удалить документ «'+doc.title+'»? Сотрудники больше не смогут его скачать.'))return;
 try{
  const response=await fetch('api.php?action=document_delete',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({csrf:state.csrf,id:Number(doc.id)})});
  await read(response);await load();showSuccess('Документ удалён.');
 }catch(e){showError(e.message);}
}
$('uploadForm').addEventListener('submit',async e=>{
 e.preventDefault();clearMessages();
 const file=$('file').files[0];if(!file){showError('Выберите файл.');return;}
 if(file.size>20*1024*1024){showError('Файл превышает допустимый размер 20 МБ.');return;}
 const button=$('uploadBtn');button.disabled=true;button.textContent='Загружаем…';
 try{
  const form=new FormData();form.append('csrf',state.csrf);form.append('title',$('title').value.trim());form.append('category',$('category').value);form.append('description',$('description').value.trim());form.append('object_id',$('object').value);form.append('requires_ack',$('requiresAck').checked?'1':'0');form.append('file',file);
  const response=await fetch('api.php?action=document_upload',{method:'POST',credentials:'same-origin',body:form});
  await read(response);$('uploadForm').reset();fillObjects();await load();showSuccess('Документ добавлен в базу.');
 }catch(err){showError(err.message);}
 finally{button.disabled=false;button.textContent='Загрузить документ';}
});
$('search').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(load,300);});
$('categoryFilter').addEventListener('change',load);
$('reload').addEventListener('click',load);
load();
})();
</script>
<script>(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,60000);document.addEventListener('visibilitychange',ping);})();</script>
<script src="site-header.js"></script>
</body>
</html>
