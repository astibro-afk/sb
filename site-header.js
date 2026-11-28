(function(){
'use strict';
const api='/api.php?action=',$=id=>document.getElementById(id);
let csrf='',noticeTimer=null,noticeBusy=false;
async function request(action,options){const r=await fetch(api+action,Object.assign({credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}},options||{}));const raw=await r.text();let d;try{d=JSON.parse(raw);}catch(e){throw new Error('Некорректный ответ сервера.');}if(!r.ok||d.ok===false)throw new Error(d.error||'Ошибка запроса.');if(d.csrf)csrf=d.csrf;return d;}

function documentsNavSetup(){
 const nav=document.querySelector('.sb-site-nav-inner,.nav-inner');
 if(!nav)return;
 let link=nav.querySelector('a[href="/documents.php"],a[href="documents.php"]');
 if(!link){
  link=document.createElement('a');
  link.href=nav.classList.contains('nav-inner')?'documents.php':'/documents.php';
  link.textContent='База документов';
  const notifications=nav.querySelector('a[href$="notifications.php"]');
  if(notifications)nav.insertBefore(link,notifications);else nav.append(link);
 }
 if(/(?:^|\/)documents\.php$/.test(location.pathname)){
  nav.querySelectorAll('a').forEach(item=>item.classList.remove('active'));
  link.classList.add('active');
 }
}
function roleName(user){const role=user&&user.role;return user&&user.role_name||({admin:'Администратор',moderator:'Менеджер по безопасности',user:'Специалист по безопасности'})[role]||role||'';}
function setupPermissionNav(user){
 const nav=document.querySelector('.sb-site-nav-inner,.nav-inner');if(!nav)return;
 const permissions=new Set(user.permissions||[]);const allowed=(key)=>user.role==='admin'||permissions.has(key);
 const links=[['tasks.php','Задачи','tasks.view'],['reports.php','Отчёты','reports.view'],['employees.php','Сотрудники','employees.view'],['documents.php','База документов','documents.view']];
 for(const [href,label,permission] of links){let link=nav.querySelector('a[href$="'+href+'"]');if(!link&&allowed(permission)){link=document.createElement('a');link.href=nav.classList.contains('nav-inner')?href:'/'+href;link.textContent=label;nav.append(link);}if(link)link.hidden=!allowed(permission);}
}
function localLink(value){return typeof value==='string'&&/^\/?[a-z0-9_/-]+\.php(?:\?[a-z0-9_=&%-]*)?$/i.test(value)&&!value.includes('..')?value:'/notifications.php';}
function noticeSetup(){
 const trigger=document.querySelector('.sb-site-actions > a[href="/notifications.php"],.header-right > a[href="notifications.php"],.header-right > a[href="/notifications.php"],.header-actions > a[href*="notifications.php"]');
 if(!trigger||trigger.dataset.noticeReady==='1')return;
 trigger.dataset.noticeReady='1';trigger.classList.add('sb-notification-trigger');trigger.setAttribute('aria-label','Уведомления');trigger.setAttribute('aria-haspopup','dialog');trigger.setAttribute('aria-expanded','false');trigger.title='Уведомления';
 let badge=trigger.querySelector('#sbHeaderBadge,#notificationBadge,.tag');
 if(!badge){badge=document.createElement('span');badge.id='sbHeaderBadge';badge.hidden=true;}
 badge.textContent=badge.textContent||'0';
 const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');
 svg.setAttribute('viewBox','0 0 24 24');svg.setAttribute('width','21');svg.setAttribute('height','21');svg.setAttribute('fill','none');svg.setAttribute('stroke','currentColor');svg.setAttribute('stroke-width','1.8');svg.setAttribute('stroke-linecap','round');svg.setAttribute('stroke-linejoin','round');svg.setAttribute('aria-hidden','true');
 const path=document.createElementNS('http://www.w3.org/2000/svg','path');path.setAttribute('d','M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4');svg.append(path);
 trigger.replaceChildren(svg,badge);
 const panel=document.createElement('section');panel.id='sbNotificationDropdown';panel.className='sb-notification-dropdown';panel.hidden=true;panel.setAttribute('role','dialog');panel.setAttribute('aria-label','Последние уведомления');
 const head=document.createElement('div');head.className='sb-notice-head';const title=document.createElement('strong');title.textContent='Уведомления';const count=document.createElement('span');count.className='sb-notice-count';count.textContent='Загрузка…';head.append(title,count);
 const list=document.createElement('div');list.className='sb-notice-list';list.textContent='Загружаем уведомления…';
 const foot=document.createElement('div');foot.className='sb-notice-foot';const all=document.createElement('button');all.type='button';all.className='sb-notice-read-all';all.textContent='Прочитать все';const more=document.createElement('a');more.href='/notifications.php';more.textContent='Все уведомления';foot.append(all,more);panel.append(head,list,foot);trigger.insertAdjacentElement('afterend',panel);
 const parent=trigger.parentElement;if(parent)parent.classList.add('sb-notice-anchor');
 trigger.addEventListener('click',e=>{e.preventDefault();const open=panel.hidden;panel.hidden=!open;trigger.setAttribute('aria-expanded',String(open));if(open)refreshNotices().catch(()=>{});});
 all.addEventListener('click',()=>markNotice(0,true).catch(e=>{count.textContent=e.message;}));
 document.addEventListener('click',e=>{if(!panel.hidden&&!trigger.parentElement.contains(e.target)){panel.hidden=true;trigger.setAttribute('aria-expanded','false');}});
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!panel.hidden){panel.hidden=true;trigger.setAttribute('aria-expanded','false');trigger.focus();}});
}
function setNoticeCount(n){const badge=$('sbHeaderBadge')||$('notificationBadge');if(!badge)return;const count=Math.max(0,Number(n)||0);badge.textContent=count>99?'99+':String(count);badge.hidden=!count;}
function renderNotices(data){
 const panel=$('sbNotificationDropdown');if(!panel)return;
 const count=Number(data.unread)||0;setNoticeCount(count);
 const countNode=panel.querySelector('.sb-notice-count');if(countNode)countNode.textContent='Непрочитанных: '+count;
 const list=panel.querySelector('.sb-notice-list');list.style.maxHeight='';list.replaceChildren();
 // В выпадающем списке показываем только непрочитанные сообщения.
 const items=Array.isArray(data.notifications)?data.notifications.filter(item=>!Number(item.is_read)).slice(0,8):[];
 if(!items.length){const empty=document.createElement('div');empty.className='sb-notice-empty';empty.textContent='Новых уведомлений пока нет.';list.append(empty);return;}
 items.forEach(item=>{
  const row=document.createElement('article');row.className='sb-notice-item'+(Number(item.is_read)?' is-read':' is-unread');
  const link=document.createElement('a');link.className='sb-notice-content';link.href=localLink(item.link);
  const heading=document.createElement('strong');heading.textContent=item.title||'Уведомление';
  const message=document.createElement('span');message.textContent=item.message||'';
  const time=document.createElement('small');time.textContent=item.created_at||'';
  link.append(heading,message,time);link.addEventListener('click',async e=>{if(!Number(item.is_read)){e.preventDefault();try{await markNotice(Number(item.id),false);}catch(err){}location.href=link.href;}});
  row.append(link);
  if(!Number(item.is_read)){const read=document.createElement('button');read.type='button';read.className='sb-notice-mark';read.textContent='Прочитано';read.setAttribute('aria-label','Отметить прочитанным');read.addEventListener('click',()=>markNotice(Number(item.id),false).catch(()=>{}));row.append(read);}
  list.append(row);
 });
 // Ограничиваем видимую область ровно высотой первых трёх уведомлений.
 const third=list.querySelector('.sb-notice-item:nth-child(3)');
 if(third){const listTop=list.getBoundingClientRect().top;const thirdBottom=third.getBoundingClientRect().bottom;list.style.maxHeight=Math.ceil(thirdBottom-listTop)+'px';}
}
async function refreshNotices(){if(noticeBusy||document.visibilityState==='hidden')return;noticeBusy=true;try{const d=await request('notifications_data');renderNotices(d);}catch(e){}finally{noticeBusy=false;}}
async function markNotice(id,all){await request('notifications_read',{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify({csrf,id,all})});await refreshNotices();}
async function init(){
 documentsNavSetup();
 noticeSetup();
 const add=$('sbHeaderAddUser')||$('btnHeaderAddUser');
 const manage=$('sbHeaderManage')||$('adminManageLink')||$('btnHeaderManage');
 if(add)add.hidden=true;if(manage)manage.hidden=true;
 try{const d=await request('status');if(d.authenticated&&d.user){const u=d.user,wrap=$('sbHeaderUser'),name=$('sbHeaderName'),meta=$('sbHeaderMeta');if(wrap)wrap.hidden=false;if(name)name.textContent=u.full_name||u.username||'';if(meta)meta.textContent=roleName(u)+((u.object_names||[]).length?' · '+u.object_names.join(', '):'');if(add)add.hidden=!(u.role==='admin'||(u.role==='moderator'&&(u.permissions||[]).includes('employees.manage')));if(manage)manage.hidden=u.role!=='admin';setupPermissionNav(u);}}catch(e){if(add)add.hidden=true;if(manage)manage.hidden=true;}
 await refreshNotices();
 if(noticeTimer)clearInterval(noticeTimer);noticeTimer=setInterval(refreshNotices,5000);
 document.addEventListener('visibilitychange',refreshNotices);window.addEventListener('focus',refreshNotices);
 const logout=$('sbHeaderLogout');if(logout)logout.addEventListener('click',async()=>{logout.disabled=true;try{await request('logout',{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify({csrf})});}catch(e){alert(e.message);logout.disabled=false;return;}location.href='/index.html';});
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();