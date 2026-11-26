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
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#f3f7f8"><title>Карточки сотрудников — СБ</title><link rel="stylesheet" href="site-theme.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f7f8;color:#18343a;font:14px/1.5 Inter,Arial,sans-serif}.page{max-width:1250px;margin:auto;padding:24px 16px 48px}.top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px}.top h1{font-size:24px;margin:0}.sub{color:#71858a;margin:4px 0 0}.btn{border:1px solid #dce7e9;background:#fff;color:#18343a;border-radius:9px;padding:9px 13px;text-decoration:none;font-weight:700;cursor:pointer}.tools{display:flex;gap:10px;margin-bottom:14px}.tools input{flex:1;min-width:0;padding:11px 13px;border:1px solid #dce7e9;border-radius:9px;font:inherit}.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(275px,1fr));gap:12px}.card{background:#fff;border:1px solid #e4ecee;border-radius:14px;padding:16px;box-shadow:0 5px 20px rgba(24,52,58,.04)}.card h2{font-size:16px;margin:0}.login{font-size:11px;color:#829398;margin-top:2px}.role{display:inline-flex;margin:9px 0 12px;padding:4px 8px;border-radius:20px;background:#eaf5f5;color:#08777b;font-size:11px;font-weight:700}.stats{display:grid;grid-template-columns:1fr 1fr;gap:8px}.stat{background:#f6f9fa;border-radius:9px;padding:9px}.stat strong{display:block;font-size:18px}.stat span{font-size:11px;color:#71858a}.objects{font-size:12px;color:#536a70;margin:11px 0}.card button{margin-top:10px;width:100%}.details{margin-top:12px;border-top:1px solid #e4ecee;padding-top:10px}.history{display:grid;gap:8px;margin-top:8px}.event{font-size:12px;padding:8px 9px;background:#f6f9fa;border-radius:8px}.event small{display:block;color:#829398;margin-top:2px}.empty{background:#fff;border:1px dashed #cbd9dc;border-radius:13px;padding:34px;text-align:center;color:#71858a}.status{font-size:11px;font-weight:700}.off{color:#b94b35}.active{color:#16734f}@media(max-width:620px){.top{align-items:flex-start;flex-direction:column}.tools{flex-direction:column}}
</style></head><body><main class="page"><header class="top"><div><h1 id="heading">Карточки сотрудников</h1><p class="sub" id="subtitle">Сводная информация, отчёты и история активности</p></div><a class="btn" href="index.html">К отчётам</a></header><div class="tools"><input id="search" type="search" placeholder="Поиск по ФИО, логину или объекту…"></div><section class="cards" id="cards"><div class="empty">Загружаем данные…</div></section></main>
<script>
(function(){
'use strict';
const cards=document.getElementById('cards'),search=document.getElementById('search');
const labels={admin:'Администратор',moderator:'Модератор',user:'Специалист СБ'};
function node(tag,cls,txt){const n=document.createElement(tag);if(cls)n.className=cls;if(txt!==undefined)n.textContent=txt;return n;}
function date(v){if(!v)return 'Нет данных';const d=new Date(String(v).replace(' ','T'));return isNaN(d)?v:d.toLocaleString('ru-RU');}
let all=[];
function render(){
 const q=search.value.trim().toLocaleLowerCase('ru');cards.replaceChildren();
 const rows=all.filter(u=>(u.full_name+' '+u.username+' '+(u.objects||[]).join(' ')).toLocaleLowerCase('ru').includes(q));
 if(!rows.length){cards.append(node('div','empty','Сотрудники не найдены.'));return;}
 rows.forEach(u=>{
  const card=node('article','card');card.dataset.id=u.id;
  const head=node('div');head.append(node('h2','',u.full_name||u.username),node('div','login',u.username+' · '+(u.is_active?'Активна':'Отключена')),node('span','role',labels[u.role]||u.role));card.append(head);
  const stats=node('div','stats');
  [[u.reports_count,'Отчётов'],[date(u.last_login),'Последний вход'],[date(u.last_report),'Последний отчёт'],[date(u.created_at),'В системе с']].forEach(([v,l])=>{const s=node('div','stat');s.append(node('strong','',String(v)),node('span','',l));stats.append(s);});
  card.append(stats,node('div','objects','Объекты: '+((u.objects||[]).join(', ')||'не назначены')));
  const btn=node('button','btn','История активности');btn.type='button';card.append(btn);
  const detail=node('div','details');detail.hidden=true;card.append(detail);
  btn.addEventListener('click',async()=>{if(!detail.hidden){detail.hidden=true;btn.textContent='История активности';return;}btn.disabled=true;try{const r=await fetch('api.php?action=employee_history&user_id='+encodeURIComponent(u.id),{credentials:'same-origin',cache:'no-store'}),d=await r.json();if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось загрузить историю.');detail.replaceChildren();detail.append(node('strong','','Последние события'));const list=node('div','history');(d.history||[]).forEach(e=>{const item=node('div','event');item.append(node('div','',e.action+' · '+(e.target_label||'')),node('small','',date(e.created_at)));list.append(item);});if(!(d.history||[]).length)list.append(node('div','event','История пока пуста.'));detail.append(list);detail.hidden=false;btn.textContent='Скрыть историю';}catch(e){detail.replaceChildren(node('div','event',e.message));detail.hidden=false;}finally{btn.disabled=false;}});
  cards.append(card);
 });
}
search.addEventListener('input',render);
fetch('api.php?action=employees_data',{credentials:'same-origin',cache:'no-store'}).then(async r=>{const d=await r.json();if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось загрузить сотрудников.');all=d.employees||[];if(d.user&&d.user.role==='user'){document.getElementById('heading').textContent='Моя карточка';document.getElementById('subtitle').textContent='Ваши отчёты и история активности';search.hidden=true;}render();}).catch(e=>cards.replaceChildren(node('div','empty',e.message)));
})();
</script></body></html>