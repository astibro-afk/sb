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
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Быстрый инцидент — СБ</title><link rel="stylesheet" href="site-theme.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f8f7;color:#1a3438;font:14px/1.5 Inter,Arial,sans-serif}.page{max-width:1100px;margin:auto;padding:24px 16px 48px}.top{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px}.top h1{font-size:24px;margin:0}.top a,.btn{border:1px solid #d5e5e2;border-radius:9px;padding:9px 13px;background:#fff;color:#214347;text-decoration:none;font-weight:700;cursor:pointer}.btn.primary{background:#08777b;border-color:#08777b;color:#fff}.panel{background:#fff;border:1px solid #e0ebe8;border-radius:14px;padding:17px;margin-bottom:14px}.form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.field{display:flex;flex-direction:column;gap:5px}.field.wide{grid-column:1/-1}.field label{font-size:12px;font-weight:700;color:#597578}.field input,.field select,.field textarea{width:100%;border:1px solid #d8e6e3;border-radius:9px;padding:10px;font:inherit}.field textarea{min-height:110px;resize:vertical}.list{display:grid;gap:10px}.item{border:1px solid #e0ebe8;border-radius:12px;padding:13px}.item h3{margin:0 0 5px;font-size:15px}.muted{color:#71888a;font-size:12px}.photo{max-width:220px;max-height:180px;border-radius:9px;margin-top:8px}.error{color:#b44335}.success{color:#27734a}@media(max-width:650px){.form{grid-template-columns:1fr}.field.wide{grid-column:auto}.top{align-items:flex-start;flex-direction:column}}
</style></head><body><main class="page">
<div class="top"><div><h1>Быстрая регистрация инцидента</h1><div class="muted">Телефон, описание, дата и фото события</div></div><a href="index.html">На главную</a></div>
<section class="panel" id="createPanel" hidden><h2 style="margin:0 0 12px;font-size:17px">Новый инцидент</h2><form id="incidentForm" class="form" enctype="multipart/form-data">
<div class="field"><label>Торговый объект</label><select name="object_id" id="objectId" required></select></div>
<div class="field"><label>Дата и время события</label><input name="event_at" type="datetime-local" id="eventAt" required></div>
<div class="field"><label>Телефон для связи</label><input name="phone" type="tel" maxlength="40" placeholder="+7 ..."></div>
<div class="field"><label>Фото (JPG, PNG, WEBP до 10 МБ)</label><input name="photo" type="file" accept="image/jpeg,image/png,image/webp"></div>
<div class="field wide"><label>Описание инцидента</label><textarea name="description" maxlength="5000" required placeholder="Кратко опишите событие, обстоятельства и участников"></textarea></div>
<div class="field wide"><button class="btn primary" type="submit">Зарегистрировать инцидент</button><div id="formMessage" class="muted" role="status"></div></div>
</form></section>
<section class="panel"><div style="display:flex;justify-content:space-between;gap:10px;align-items:center"><h2 style="margin:0;font-size:17px">Последние инциденты</h2><button class="btn" id="refreshBtn" type="button">Обновить</button></div><div id="incidents" class="list" style="margin-top:12px"><div class="muted">Загрузка...</div></div></section>
</main>
<script>
const api=(action)=>'api.php?action='+encodeURIComponent(action);
const esc=(v)=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let csrf='';
async function load(){
 const r=await fetch(api('incidents_data'),{credentials:'same-origin'});const d=await r.json();
 if(!r.ok||!d.ok){document.getElementById('incidents').textContent=d.error||'Не удалось загрузить список';return;}
 csrf=d.csrf||'';document.querySelector('[name="description"]').placeholder=d.incident_template||'Кратко опишите событие, обстоятельства и участников';
 if(d.can_create){document.getElementById('createPanel').hidden=false;const s=document.getElementById('objectId');s.innerHTML=d.objects.map(o=>'<option value="'+Number(o.id)+'">'+esc(o.name)+'</option>').join('');}
 const list=document.getElementById('incidents');
 list.innerHTML=d.incidents.length?d.incidents.map(i=>'<article class="item"><h3>'+esc(i.object_name)+' · '+esc(i.event_at)+'</h3><div class="muted">Зарегистрировал: '+esc(i.creator_name)+(i.phone?' · Телефон: '+esc(i.phone):'')+'</div><p style="white-space:pre-wrap;margin:8px 0">'+esc(i.description)+'</p>'+(i.photo_url?'<a href="'+esc(i.photo_url)+'" target="_blank" rel="noopener"><img class="photo" src="'+esc(i.photo_url)+'" alt="Фото инцидента"></a>':'')+'</article>').join(''):'<div class="muted">Инцидентов пока нет.</div>';
}
document.getElementById('incidentForm').addEventListener('submit',async e=>{
 e.preventDefault();const form=e.currentTarget;const fd=new FormData(form);fd.append('csrf',csrf);
 const msg=document.getElementById('formMessage');msg.className='muted';msg.textContent='Сохранение...';
 try{const r=await fetch(api('incident_create'),{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!r.ok||!d.ok)throw new Error(d.error||'Ошибка сохранения');form.reset();document.getElementById('eventAt').value=new Date(Date.now()-new Date().getTimezoneOffset()*60000).toISOString().slice(0,16);msg.className='success';msg.textContent='Инцидент зарегистрирован.';await load();}catch(err){msg.className='error';msg.textContent=err.message;}
});
document.getElementById('refreshBtn').addEventListener('click',load);
document.getElementById('eventAt').value=new Date(Date.now()-new Date().getTimezoneOffset()*60000).toISOString().slice(0,16);
load();
</script></body></html>