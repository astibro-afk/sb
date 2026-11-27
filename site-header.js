(function(){
'use strict';
const api='/api.php?action=',$=id=>document.getElementById(id);
let csrf='';
async function request(action,options){const r=await fetch(api+action,Object.assign({credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}},options||{}));const raw=await r.text();let d;try{d=JSON.parse(raw);}catch(e){throw new Error('Некорректный ответ сервера.');}if(!r.ok||d.ok===false)throw new Error(d.error||'Ошибка запроса.');if(d.csrf)csrf=d.csrf;return d;}
function roleName(role){return ({admin:'Администратор',moderator:'Менеджер по безопасности',user:'Специалист по безопасности'})[role]||role||'';}
async function init(){
 const add=$('sbHeaderAddUser')||$('btnHeaderAddUser');
 const manage=$('sbHeaderManage')||$('adminManageLink')||$('btnHeaderManage');
 if(add)add.hidden=true;
 if(manage)manage.hidden=true;
 try{const d=await request('status');if(d.authenticated&&d.user){const u=d.user,wrap=$('sbHeaderUser'),name=$('sbHeaderName'),meta=$('sbHeaderMeta');if(wrap)wrap.hidden=false;if(name)name.textContent=u.full_name||u.username||'';if(meta)meta.textContent=roleName(u.role)+((u.object_names||[]).length?' · '+u.object_names.join(', '):'');if(add)add.hidden=!(u.role==='admin'||u.role==='moderator');if(manage)manage.hidden=u.role!=='admin';}}catch(e){if(add)add.hidden=true;if(manage)manage.hidden=true;}
 try{const n=await request('notifications_data'),badge=$('sbHeaderBadge');if(badge){const count=Number(n.unread)||0;badge.textContent=count>99?'99+':String(count);badge.hidden=!count;}}catch(e){}
 const logout=$('sbHeaderLogout');if(logout)logout.addEventListener('click',async()=>{logout.disabled=true;try{await request('logout',{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify({csrf})});}catch(e){alert(e.message);logout.disabled=false;return;}location.href='/index.html';});
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();