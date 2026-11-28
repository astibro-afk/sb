<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
ini_set('session.use_strict_mode','1');$isHttps=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Strict']);session_name('sbapp_session');session_start();
if(empty($_SESSION['sb_user_id'])){header('Location: index.html');exit;}
?>
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Настройки системы — СБ</title><link rel="stylesheet" href="site-theme.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f8f7;color:#1a3438;font:14px/1.5 Inter,Arial,sans-serif}.page{max-width:900px;margin:auto;padding:24px 16px 48px}.top{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px}.top h1{font-size:24px;margin:0}.top a,.btn{border:1px solid #d5e5e2;border-radius:9px;padding:9px 13px;background:#fff;color:#214347;text-decoration:none;font-weight:700;cursor:pointer}.btn.primary{background:#08777b;border-color:#08777b;color:#fff}.panel{background:#fff;border:1px solid #e0ebe8;border-radius:14px;padding:18px}.form{display:grid;grid-template-columns:1fr 1fr;gap:13px}.field{display:flex;flex-direction:column;gap:5px}.field.wide{grid-column:1/-1}.field label{font-size:12px;font-weight:700;color:#597578}.field input,.field textarea{width:100%;border:1px solid #d8e6e3;border-radius:9px;padding:10px;font:inherit}.field textarea{min-height:110px;resize:vertical}.muted{color:#71888a;font-size:12px}.error{color:#b44335}.success{color:#27734a}@media(max-width:600px){.form{grid-template-columns:1fr}.field.wide{grid-column:auto}.top{align-items:flex-start;flex-direction:column}}
</style></head><body><main class="page"><div class="top"><div><h1>Настройки системы</h1><div class="muted">Название организации, логотип, уведомления и шаблоны</div></div><a href="index.html">На главную</a></div>
<section class="panel"><form id="settingsForm" class="form">
<div class="field wide"><label>Название организации</label><input name="organization_name" maxlength="120" placeholder="Служба безопасности"></div>
<div class="field wide"><label>Ссылка на логотип (HTTPS)</label><input name="logo_url" type="url" maxlength="500" placeholder="https://..."></div>
<div class="field wide"><label>Контактный e-mail для уведомлений</label><input name="notification_email" type="email" maxlength="180" placeholder="security@example.com"></div>
<div class="field wide"><label>Шаблон задачи</label><textarea name="task_template" maxlength="3000" placeholder="Стандартный текст задачи..."></textarea></div>
<div class="field wide"><label>Шаблон регистрации инцидента</label><textarea name="incident_template" maxlength="3000" placeholder="Какие сведения необходимо указать..."></textarea></div>
<div class="field wide"><button class="btn primary" type="submit">Сохранить настройки</button><span id="message" class="muted" role="status"></span></div>
</form></section></main>
<script>
const api=a=>'api.php?action='+encodeURIComponent(a);let csrf='';
async function load(){const r=await fetch(api('settings_data'),{credentials:'same-origin'}),d=await r.json();if(!r.ok||!d.ok){document.querySelector('.panel').innerHTML='<div class="error">'+String(d.error||'Нет доступа')+'</div>';return;}csrf=d.csrf||'';for(const [k,v] of Object.entries(d.settings||{})){const el=document.querySelector('[name="'+k+'"]');if(el)el.value=v;}}
document.getElementById('settingsForm').addEventListener('submit',async e=>{e.preventDefault();const settings=Object.fromEntries(new FormData(e.currentTarget).entries());const m=document.getElementById('message');m.textContent='Сохранение...';try{const r=await fetch(api('settings_save'),{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf,settings})});const d=await r.json();if(!r.ok||!d.ok)throw new Error(d.error||'Ошибка сохранения');m.className='success';m.textContent='Настройки сохранены.';}catch(err){m.className='error';m.textContent=err.message;}});
load();
</script></body></html>