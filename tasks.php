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
*{box-sizing:border-box}body{margin:0;background:#f3f8f7;color:#1a3438;font:14px/1.5 Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif}.page{max-width:1180px;margin:auto;padding:24px 18px 48px}.top{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px}.heading h1{font-size:25px;line-height:1.2;margin:0}.heading p{margin:6px 0 0;color:#668085}.actions{display:flex;gap:8px;flex-wrap:wrap}.btn{border:1px solid #d5e5e2;background:#fff;color:#214347;border-radius:10px;padding:9px 13px;font-weight:650;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px}.btn.primary{background:#08777b;border-color:#08777b;color:white}.btn:disabled{opacity:.55;cursor:not-allowed}.summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px}.stat{background:#fff;border:1px solid #e0ebe8;border-radius:13px;padding:13px 15px}.stat strong{display:block;font-size:23px;line-height:1.2}.stat span{color:#71888a;font-size:12px}.toolbar{display:flex;gap:9px;align-items:center;flex-wrap:wrap;margin:0 0 14px}.toolbar input,.toolbar select,.form input,.form select,.form textarea,.comment-form textarea{width:100%;border:1px solid #d8e6e3;background:#fff;border-radius:9px;padding:10px 11px;color:#1a3438;font:inherit}.toolbar .search{flex:1 1 230px}.toolbar select{width:auto;min-width:150px}.task-list{display:flex;flex-direction:column;gap:8px}.task-row-card{display:flex;align-items:center;gap:10px;min-width:0;padding:0 12px;background:#fff;border:1px solid #e0ebe8;border-radius:12px;box-shadow:0 2px 8px rgba(26,52,56,.025)}.task-row-card.overdue{border-color:#e7b8b1}.task-row-open{display:flex;align-items:center;justify-content:space-between;gap:16px;flex:1;min-width:0;min-height:66px;padding:10px 0;border:0;background:transparent;color:inherit;text-align:left;cursor:pointer}.task-row-open:focus-visible{outline:2px solid #08777b;outline-offset:3px;border-radius:8px}.task-row-main{display:flex;align-items:center;gap:12px;flex:1;min-width:0}.task-row-title{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px;font-weight:700;color:#1a3438}.task-row-meta{display:flex;align-items:center;justify-content:flex-end;gap:14px;min-width:0;color:#71888a;font-size:12px}.task-row-meta span{max-width:210px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.task-row-meta b{color:#29494c;font-weight:650}.task-row-controls{display:flex;align-items:center;gap:4px;flex:none}.task-row-icon{width:32px;height:32px;border:0;border-radius:8px;background:transparent;color:#819496;font-size:19px;cursor:pointer}.task-row-icon:hover{background:#eaf5f3;color:#08777b}.task-row-icon.danger:hover{background:#fff0ed;color:#b44335}.task-view-overlay{position:fixed;inset:0;z-index:1100;display:flex;align-items:center;justify-content:center;padding:18px;background:rgba(15,35,38,.56)}.task-view-dialog{display:flex;flex-direction:column;width:min(1120px,100%);height:min(820px,calc(100vh - 36px));min-height:0;overflow:hidden;background:#fff;border:1px solid #dce9e6;border-radius:16px;box-shadow:0 24px 70px rgba(10,30,34,.3)}.task-view-header{display:flex;align-items:center;justify-content:space-between;gap:14px;flex:none;padding:16px 20px;border-bottom:1px solid #edf2f1}.task-view-heading{min-width:0}.task-view-heading h2{margin:0 0 7px;font-size:19px;line-height:1.3;overflow-wrap:anywhere}.task-view-close{width:36px;height:36px;flex:none;border:0;border-radius:9px;background:#f1f6f5;color:#456164;font-size:24px;line-height:1;cursor:pointer}.task-view-body{flex:1;min-height:0;overflow:hidden;padding:20px}.task-view-layout{display:grid;grid-template-columns:minmax(280px,.85fr) minmax(0,1.15fr);gap:22px;height:100%;min-height:0}.task-view-info,.task-view-discussion{min-width:0;min-height:0}.task-view-info{align-self:start}.task-view-discussion{height:100%;overflow:hidden}.task-view-description{margin-bottom:16px;padding:15px;background:#f6faf9;border-radius:12px}.task-view-description h3,.task-view-discussion h3{margin:0 0 8px;font-size:13px;color:#597578}.task-view-description p{margin:0;white-space:pre-wrap;overflow-wrap:anywhere;color:#29494c}.task-view-meta{display:grid;grid-template-columns:1fr;gap:0;padding:5px 15px;background:#f6faf9;border-radius:12px}.task-view-meta-item{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #e8f0ee;font-size:12px}.task-view-meta-item:last-child{border-bottom:0}.task-view-meta-item span{color:#71888a;flex:none}.task-view-meta-item strong{text-align:right;color:#29494c;overflow-wrap:anywhere}.task-view-actions{padding-top:14px}.task-view-discussion{display:flex;flex-direction:column;border-left:1px solid #edf2f1;padding-left:20px}.task-view-comments{display:flex;flex:1;flex-direction:column;border:0;margin:0;padding:0;min-width:0;min-height:0;overflow:hidden}.task-view-comments summary{list-style:none;display:block;margin:0 0 10px;color:#29494c;font-size:15px;font-weight:750;cursor:default}.task-view-comments summary::-webkit-details-marker{display:none}.task-view-comments .comments-list{flex:1;min-height:0;max-height:none;overflow-y:auto;overscroll-behavior:contain;padding-right:4px}.task-view-discussion .comment-form{flex:none;margin-top:0;padding-top:12px;border-top:1px solid #edf2f1}.task-view-discussion .comment-form textarea{min-height:76px;max-height:150px}.task-view-discussion .comment-form select,.task-view-discussion .comment-form input[type=file]{width:100%}.task-view-discussion .comment-form .btn{align-self:flex-end}.task-view-discussion .comment-form{transition:opacity .18s ease,transform .18s ease}.task-view-discussion .comment-form.is-sending{opacity:.68;transform:translateY(2px)}.task-view-discussion .comment-form.is-sending .btn{min-width:118px}@media(max-width:780px){.task-row-card{align-items:flex-start}.task-row-open{align-items:flex-start;flex-direction:column;gap:8px;min-height:0;padding:12px 0}.task-row-main{align-items:flex-start;flex-direction:column;gap:6px}.task-row-meta{justify-content:flex-start;flex-wrap:wrap;gap:5px 12px}.task-row-meta span{max-width:100%}.task-view-overlay{padding:0}.task-view-dialog{width:100%;height:100dvh;max-height:100dvh;border:0;border-radius:0}.task-view-header{padding:13px 15px}.task-view-body{padding:14px}.task-view-layout{grid-template-columns:minmax(0,1fr);grid-template-rows:auto minmax(0,1fr);gap:0}.task-view-info{padding-bottom:12px}.task-view-discussion{height:100%;border-left:0;border-top:1px solid #edf2f1;padding:12px 0 0}.task-view-comments .comments-list{max-height:none}.task-view-discussion .comment-form{position:static;background:#fff;padding:12px 0 0}}@media(max-width:520px){.task-row-card{padding:0 9px}.task-row-title{white-space:normal}.task-row-icon{width:29px;height:29px}.task-view-heading h2{font-size:16px}.task-view-meta-item{align-items:flex-start}}.task{position:relative;background:#fff;border:1px solid #e0ebe8;border-radius:14px;padding:16px;box-shadow:0 5px 18px rgba(26,52,56,.035);min-width:0}.task-delete-x,.task-edit-pencil{position:absolute;top:9px;width:30px;height:30px;padding:0;border:0;border-radius:8px;background:transparent;color:#819496;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center}.task-delete-x{right:10px;font-size:23px}.task-delete-x:hover{background:#fff0ed;color:#b44335}.task-edit-pencil{right:44px;font-size:18px}.task-edit-pencil:hover{background:#eaf5f3;color:#08777b}.task-delete-x:focus-visible,.task-edit-pencil:focus-visible{outline:2px solid #08777b;outline-offset:2px}.task:has(.task-delete-x) .task-head{padding-right:64px}.task.overdue{border-color:#e7b8b1}.task-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.task h2{font-size:16px;line-height:1.35;margin:0 0 7px;overflow-wrap:anywhere}.badges{display:flex;gap:5px;flex-wrap:wrap}.tag{font-size:11px;font-weight:700;padding:4px 8px;border-radius:20px;background:#edf4f3;color:#4c696b;white-space:nowrap}.tag.high{background:#fff0ed;color:#b44335}.tag.low{background:#eef5ff;color:#426a9b}.tag.done{background:#e9f6ef;color:#27734a}.tag.review{background:#fff5df;color:#8a6418}.tag.overdue{background:#fff0ed;color:#b44335}.desc{color:#536e70;white-space:pre-wrap;overflow-wrap:anywhere;margin:10px 0}.meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px 12px;color:#6c8385;font-size:12px;margin:10px 0}.meta b{color:#29494c;font-weight:650}.task-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}.task-actions .btn{font-size:12px;padding:7px 10px}.comments{border-top:1px solid #edf2f1;margin-top:13px;padding-top:10px}.comments summary{cursor:pointer;color:#557477;font-size:12px;font-weight:700}.comment{padding:8px 0;border-bottom:1px solid #f0f4f3}.comment:last-child{border:0}.comment b{font-size:12px}.comment small{color:#849797;margin-left:6px;font-size:10px}.comment p{margin:3px 0 0;color:#536e70;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;max-width:100%}.comments,.comments-list,.comment{min-width:0;max-width:100%}.comment-delete{float:right;border:0;background:transparent;color:#9a7773;font-size:17px;line-height:1;cursor:pointer;padding:2px 6px;border-radius:6px}.comment-delete:hover{background:#fff0ed;color:#b44335}.comment-delete:focus-visible{outline:2px solid #08777b;outline-offset:2px}.comment-form{display:flex;gap:7px;margin-top:9px;flex-wrap:wrap;align-items:flex-start}.comment-form textarea{min-height:38px;max-height:100px;resize:vertical;flex:1 1 100%;}.comment-form select,.comment-form input[type=file]{max-width:100%;border:1px solid #d8e6e3;background:#fff;border-radius:9px;padding:8px;color:#1a3438;font:inherit;font-size:12px}.comment-form .btn{align-self:flex-end} .comment a{display:block;color:#08777b;text-decoration:underline;font-size:12px;margin-top:5px;overflow-wrap:anywhere}.modal-overlay{position:fixed;inset:0;z-index:1000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(15,35,38,.48);overflow-y:auto}.modal-dialog{position:relative;width:min(760px,100%);max-height:calc(100vh - 40px);overflow-y:auto;background:#fff;border:1px solid #dce9e6;border-radius:16px;padding:18px;box-shadow:0 24px 70px rgba(10,30,34,.24)}.modal-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.modal-heading h2{font-size:18px;margin:0}.modal-close{width:34px;height:34px;flex:none;border:0;border-radius:9px;background:#f1f6f5;color:#456164;font-size:23px;line-height:1;cursor:pointer}.modal-close:hover{background:#e5efed}.form-panel{background:#fff;border:0;border-radius:0;padding:0;margin:0}.modal-dialog .form{grid-template-columns:repeat(2,minmax(0,1fr))}.modal-dialog .field.full{grid-column:1/-1}.modal-dialog .form-footer{justify-content:flex-end}@media(max-width:520px){.modal-overlay{padding:10px;align-items:flex-start}.modal-dialog{max-height:calc(100vh - 20px);padding:15px;border-radius:13px}.modal-dialog .form{grid-template-columns:1fr}.modal-dialog .field.wide{grid-column:auto}}.form-panel h2{font-size:15px;margin:0 0 8px}.form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px 9px}.form .field{gap:3px}.form .field label{font-size:11px}.form input,.form select,.form textarea{padding:7px 9px;border-radius:8px;font-size:13px}.form textarea{min-height:54px}.form .form-footer{gap:7px}.form .form-footer .btn{padding:7px 11px;font-size:12px}.field{display:flex;flex-direction:column;gap:5px;min-width:0}.field label{font-size:12px;font-weight:700;color:#597578}.field.wide{grid-column:span 2}.field.full{grid-column:1/-1}.form textarea{min-height:78px;resize:vertical}.form-footer{grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px}.empty{grid-column:1/-1;background:#fff;border:1px dashed #cbdcda;border-radius:14px;padding:34px;text-align:center;color:#71888a}.error{position:fixed;z-index:2005;top:max(12px,env(safe-area-inset-top));left:50%;transform:translateX(-50%);width:min(560px,calc(100vw - 24px));background:#fff1ef;color:#a53d31;border:1px solid #e8aaa1;border-left:4px solid #c84d40;border-radius:12px;padding:13px 42px 13px 15px;margin:0;box-shadow:0 12px 34px rgba(48,24,20,.2);font-weight:650;cursor:pointer;animation:errorToastIn .18s ease-out}.error::after{content:'×';position:absolute;right:13px;top:50%;transform:translateY(-50%);font-size:23px;line-height:1;color:#a53d31;font-weight:400}@keyframes errorToastIn{from{opacity:0;transform:translate(-50%,-8px)}to{opacity:1;transform:translate(-50%,0)}}.pagination{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:16px}.pagination span{color:#607b7d;font-size:13px}.hidden{display:none!important}
@media(max-width:780px){.task-list{grid-template-columns:1fr}.summary{grid-template-columns:repeat(2,minmax(0,1fr))}.form{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:520px){.page{padding:18px 12px 36px}.top{align-items:flex-start;flex-direction:column}.heading h1{font-size:22px}.form{grid-template-columns:1fr}.field.wide{grid-column:auto}.toolbar select{flex:1;min-width:0}.meta{grid-template-columns:1fr}.task-head{flex-direction:column}.comment-form{flex-direction:column}.comment-form .btn{align-self:flex-start}.comment-form select,.comment-form input[type=file]{width:100%}}

/* Компактные выпадающие мультиселекты с чекбоксами */
.native-multi-select{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
.multi-dropdown{position:relative;width:100%;min-width:0}
.multi-dropdown-trigger{width:100%;min-height:36px;display:flex;align-items:center;gap:8px;padding:7px 9px;border:1px solid #d8e6e3;border-radius:8px;background:#fff;color:#29494c;font:inherit;font-size:12px;text-align:left;cursor:pointer;transition:border-color .15s,box-shadow .15s}
.multi-dropdown-trigger:hover,.multi-dropdown.open .multi-dropdown-trigger{border-color:#8bbab5}
.multi-dropdown-trigger:focus-visible{outline:2px solid rgba(8,119,123,.2);outline-offset:2px}
.multi-dropdown-caption{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600}
.multi-dropdown-count{flex:none;min-width:20px;padding:1px 6px;border-radius:20px;background:#e8f4f2;color:#08777b;font-size:10px;font-weight:750;text-align:center}
.multi-dropdown-count:empty{display:none}
.multi-dropdown-chevron{flex:none;color:#71888a;font-size:15px;line-height:1;transition:transform .15s}
.multi-dropdown.open .multi-dropdown-chevron{transform:rotate(180deg)}
.multi-dropdown-panel{position:absolute;z-index:30;top:calc(100% + 5px);left:0;right:0;max-height:230px;overflow:auto;padding:5px;background:#fff;border:1px solid #dce9e7;border-radius:10px;box-shadow:0 12px 28px rgba(26,52,56,.14)}
.multi-dropdown-option{display:flex;align-items:center;gap:8px;min-height:32px;padding:5px 7px;border-radius:7px;color:#29494c;font-size:12px;cursor:pointer;user-select:none}
.multi-dropdown-option:hover{background:#f1f7f6}
.multi-dropdown-option.is-all{margin-bottom:4px;padding-bottom:8px;border-bottom:1px solid #edf2f1;border-radius:7px 7px 0 0;font-weight:700;color:#08777b}
.multi-dropdown-option input{width:14px;height:14px;flex:none;margin:0;accent-color:#08777b;cursor:pointer}
.multi-dropdown-option span{min-width:0;overflow-wrap:anywhere}
.multi-dropdown-empty{padding:10px 8px;color:#819496;font-size:11px}

/* Единая сетка строк задач: без наложения заголовков, метаданных и кнопок */
.task-list{display:flex;flex-direction:column;gap:8px}
.task-row-card{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:stretch;gap:12px;padding:0 14px;min-height:78px;border:1px solid #dfe9e7;border-radius:12px;background:#fff;box-shadow:0 2px 7px rgba(26,52,56,.035);transition:border-color .15s,box-shadow .15s}
.task-row-card:hover{border-color:#b9d4d0;box-shadow:0 4px 13px rgba(26,52,56,.07)}
.task-row-card.overdue{border-left:3px solid #cf6658}
.task-row-open{display:grid;grid-template-columns:minmax(220px,.9fr) minmax(0,1.1fr);align-items:center;gap:22px;width:100%;min-width:0;min-height:76px;padding:11px 0}
.task-row-main{display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:7px;min-width:0}
.task-row-title{display:block;width:100%;font-size:14px;line-height:1.35;font-weight:700;color:#203e42;white-space:normal;overflow-wrap:anywhere}
.task-row-main .badges{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
.task-row-main .tag{padding:3px 8px;font-size:10px;line-height:1.35}
.task-row-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:center;justify-content:stretch;gap:8px 14px;min-width:0;color:#526e70;font-size:12px}
.task-row-meta>span{display:flex;flex-direction:column;gap:2px;min-width:0;max-width:none;white-space:normal;line-height:1.3}
.task-row-meta b{display:block;color:#87999a;font-size:10px;font-weight:650;letter-spacing:.02em}
.task-row-meta>span:not(:has(b)){overflow-wrap:anywhere}
.task-row-controls{display:flex;align-items:center;justify-content:center;gap:3px;padding-left:9px;border-left:1px solid #edf2f1}
.task-row-icon{width:32px;height:32px;flex:none;border-radius:8px}
.task-row-icon:focus-visible{outline:2px solid #08777b;outline-offset:2px}
@media(max-width:980px){
 .task-row-open{grid-template-columns:minmax(190px,.8fr) minmax(0,1.2fr);gap:14px}
 .task-row-meta{gap:7px}
}
@media(max-width:780px){
 .task-row-card{grid-template-columns:minmax(0,1fr) auto;gap:8px;padding:0 10px;min-height:0}
 .task-row-open{display:flex;flex-direction:column;align-items:stretch;gap:9px;min-height:0;padding:12px 0}
 .task-row-main{align-items:flex-start;gap:7px}
 .task-row-title{font-size:14px}
 .task-row-meta{grid-template-columns:repeat(2,minmax(0,1fr));justify-content:start;gap:8px 14px}
 .task-row-controls{align-items:flex-start;padding:12px 0 0 7px}
}
@media(max-width:420px){
 .task-row-meta{grid-template-columns:minmax(0,1fr);gap:6px}
 .task-row-meta>span{display:grid;grid-template-columns:76px minmax(0,1fr);gap:6px;align-items:start}
 .task-row-meta b{font-size:10px}
 .task-row-controls{gap:0}
 .task-row-icon{width:29px;height:29px}
}
</style><link rel="stylesheet" href="site-header.css">
</head><body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a class="active" href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a href="/notifications.php">Уведомления</a></div></nav></header>
<main class="page">
<div class="sb-page-title"><div><h1 id="tasksHeading">Задачи и поручения</h1><p>Постановка задач, контроль сроков и обратная связь по объектам</p></div><div class="actions"><button class="btn primary" id="openCreate" type="button" hidden>Создать</button><button class="btn" id="archiveToggle" type="button">Архив задач</button><button class="btn" id="reload" type="button">Обновить</button></div></div>
<div class="summary"><div class="stat"><strong id="total">—</strong><span>Всего задач</span></div><div class="stat"><strong id="active">—</strong><span>В работе</span></div><div class="stat"><strong id="reviewCount">—</strong><span>На проверке</span></div><div class="stat"><strong id="overdue">—</strong><span>Просрочено</span></div></div>
<div id="error" class="error hidden" role="alert"></div>
<div id="createPanel" class="modal-overlay hidden" role="presentation"><section class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="taskModalTitle"><div class="modal-heading"><h2 id="taskModalTitle">Поставить новую задачу</h2><button class="modal-close" id="closeTaskModal" type="button" aria-label="Закрыть">×</button></div><form class="form" id="createForm">
<div class="field wide"><label for="title">Название задачи *</label><input id="title" maxlength="180" required placeholder="Например: Проверить соблюдение кассовой дисциплины"></div>
<div class="field"><label for="objectDropdown">Доступные ТО</label><div class="multi-dropdown" id="objectDropdown" data-select="object"><button class="multi-dropdown-trigger" type="button" aria-expanded="false"><span class="multi-dropdown-caption">Выберите объекты</span><span class="multi-dropdown-count"></span><span class="multi-dropdown-chevron">⌄</span></button><div class="multi-dropdown-panel" hidden></div></div><select id="object" class="native-multi-select" multiple aria-label="Выберите один или несколько объектов"><option value="__all__">Все объекты</option></select></div>
<div class="field"><label for="assigneeDropdown">Сотрудники</label><div class="multi-dropdown" id="assigneeDropdown" data-select="assignee"><button class="multi-dropdown-trigger" type="button" aria-expanded="false"><span class="multi-dropdown-caption">Выберите сотрудников</span><span class="multi-dropdown-count"></span><span class="multi-dropdown-chevron">⌄</span></button><div class="multi-dropdown-panel" hidden></div></div><select id="assignee" class="native-multi-select" multiple aria-label="Выберите одного или нескольких сотрудников"><option value="__all__">Все сотрудники</option></select></div>
<div class="field"><label for="priority">Приоритет</label><select id="priority"><option value="normal">Обычный</option><option value="high">Высокий</option><option value="low">Низкий</option></select></div>
<div class="field"><label for="due">Срок выполнения</label><input id="due" type="date"></div>
<div class="field full"><label for="description">Описание и ожидаемый результат</label><textarea id="description" maxlength="5000" placeholder="Опишите задачу, ожидаемый результат и важные детали"></textarea></div>
<div class="form-footer"><button class="btn" type="button" id="cancelEdit" hidden>Отмена</button><button class="btn primary" type="submit" id="createBtn">Создать задачу</button></div>
</form></section></div>
<div class="toolbar"><input class="search" id="search" type="search" placeholder="Поиск по задаче, объекту или сотруднику"><select id="statusFilter"><option value="">Все статусы</option><option value="new">Новые</option><option value="in_progress">В работе</option><option value="review">На проверке</option><option value="done">Выполнены</option></select><select id="objectFilter"><option value="">Все объекты</option></select></div>
<section class="task-list" id="tasks"><div class="empty">Загружаем задачи…</div></section>
<div class="pagination" id="pagination" hidden><button class="btn" id="prevPage" type="button">Назад</button><span id="pageInfo"></span><button class="btn" id="nextPage" type="button">Далее</button></div>
</main>
<script>
(function(){
'use strict';
const $=id=>document.getElementById(id),tasksEl=$('tasks');
let state={tasks:[],objects:[],assignees:[],user:null,csrf:'',stats:{},archiveView:false,pagination:{page:1,total_pages:1,total:0}};let loadToken=0,searchTimer=null,editingTaskId=0,errorTimer=null;
const statusLabels={new:'Новая',in_progress:'В работе',review:'На проверке',done:'Выполнена'};
const priorityLabels={high:'Высокий приоритет',normal:'Обычный приоритет',low:'Низкий приоритет'};
function node(tag,cls,txt){const n=document.createElement(tag);if(cls)n.className=cls;if(txt!==undefined)n.textContent=txt;return n;}
async function json(response){const raw=await response.text();if(!raw.trim())throw new Error('Сервер вернул пустой ответ. Обновите страницу.');try{return JSON.parse(raw);}catch(e){throw new Error('Сервер вернул некорректный ответ.');}}
async function api(action,body){const r=await fetch('api.php?action='+encodeURIComponent(action),{method:body?'POST':'GET',credentials:'same-origin',cache:'no-store',headers:body?{'Content-Type':'application/json'}:{},body:body?JSON.stringify({...body,csrf:state.csrf}):undefined});const d=await json(r);if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось выполнить запрос.');return d;}
async function get(action,params){const query=new URLSearchParams(params||{});const r=await fetch('api.php?action='+encodeURIComponent(action)+'&'+query.toString(),{credentials:'same-origin',cache:'no-store'});const d=await json(r);if(!r.ok||!d.ok)throw new Error(d.error||'Не удалось выполнить запрос.');return d;}
function showError(message){const notice=$('error');clearTimeout(errorTimer);notice.textContent=message||'Произошла ошибка. Попробуйте ещё раз.';notice.classList.remove('hidden');notice.setAttribute('role','alert');errorTimer=setTimeout(clearError,5000);}
function clearError(){clearTimeout(errorTimer);errorTimer=null;const notice=$('error');notice.textContent='';notice.classList.add('hidden');}
$('error').addEventListener('click',clearError);
function date(v){if(!v)return 'Без срока';const d=new Date(v+'T00:00:00');return isNaN(d)?v:d.toLocaleDateString('ru-RU',{day:'2-digit',month:'long',year:'numeric'});}
function time(v){if(!v)return '';const raw=String(v).trim();const iso=/Z$|[+-]\\d{2}:?\\d{2}$/.test(raw)?raw:raw.replace(' ','T')+'Z';const d=new Date(iso);return isNaN(d)?v:d.toLocaleString('ru-RU',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});}
function selectedObjectIds(){const values=Array.from($('object').selectedOptions).map(o=>o.value);if(values.includes('__all__'))return state.objects.map(o=>Number(o.id));return values.map(Number).filter(Boolean);}
function normalizeMultiSelect(sel,sentinel){const all=Array.from(sel.options).find(o=>o.value==='__all__');if(!all)return;if(all.selected){Array.from(sel.options).forEach(o=>{if(o!==all)o.selected=false;});}else if(Array.from(sel.selectedOptions).some(o=>o.value!==sentinel)){all.selected=false;}}
function refreshMultiDropdown(selectId){
 const select=$(selectId);const root=$(selectId==='object'?'objectDropdown':'assigneeDropdown');if(!select||!root)return;
 const panel=root.querySelector('.multi-dropdown-panel'),caption=root.querySelector('.multi-dropdown-caption'),count=root.querySelector('.multi-dropdown-count');if(!panel||!caption||!count)return;
 const options=Array.from(select.options),selected=options.filter(o=>o.selected),allSelected=selected.some(o=>o.value==='__all__');
 if(allSelected){caption.textContent=options[0]?.textContent||'Все';count.textContent='Все';}
 else if(selected.length){caption.textContent=selected.length===1?selected[0].textContent:'Выбрано: '+selected.length;count.textContent=String(selected.length);}
 else{caption.textContent=selectId==='object'?'Выберите объекты':'Выберите сотрудников';count.textContent='';}
 panel.replaceChildren();
 if(!options.length){panel.append(node('div','multi-dropdown-empty','Нет доступных вариантов'));return;}
 options.forEach((option,index)=>{const label=node('label','multi-dropdown-option'+(option.value==='__all__'?' is-all':''));const checkbox=document.createElement('input');checkbox.type='checkbox';checkbox.checked=option.selected;checkbox.tabIndex=0;checkbox.addEventListener('change',()=>{option.selected=checkbox.checked;select.dispatchEvent(new Event('change',{bubbles:true}));});label.append(checkbox,node('span','',option.textContent));panel.append(label);});
}
function closeMultiDropdowns(except){['objectDropdown','assigneeDropdown'].forEach(id=>{const root=$(id);if(root&&root!==except){const panel=root.querySelector('.multi-dropdown-panel'),trigger=root.querySelector('.multi-dropdown-trigger');root.classList.remove('open');if(panel)panel.hidden=true;if(trigger)trigger.setAttribute('aria-expanded','false');}});}
['objectDropdown','assigneeDropdown'].forEach(id=>{const root=$(id);if(!root)return;const trigger=root.querySelector('.multi-dropdown-trigger'),panel=root.querySelector('.multi-dropdown-panel');if(!trigger||!panel)return;trigger.addEventListener('click',()=>{const opening=!root.classList.contains('open');closeMultiDropdowns(root);root.classList.toggle('open',opening);panel.hidden=!opening;trigger.setAttribute('aria-expanded',String(opening));});});
document.addEventListener('click',e=>{if(!e.target.closest('.multi-dropdown'))closeMultiDropdowns(null);});
function fillAssignees(keepSelection=true){const sel=$('assignee');const previous=keepSelection?Array.from(sel.selectedOptions).map(o=>o.value):[];const hadAll=previous.includes('__all__');const objectIds=selectedObjectIds();const available=state.assignees.filter(u=>!state.user||Number(u.id)!==Number(state.user.id)).filter(u=>!objectIds.length||(u.object_ids||[]).some(id=>objectIds.includes(Number(id))));sel.replaceChildren(new Option('Все сотрудники','__all__'));available.forEach(u=>sel.add(new Option(u.full_name,u.id)));if(hadAll){sel.options[0].selected=true;}else{Array.from(sel.options).forEach(o=>{if(o.value!=='__all__'&&previous.includes(o.value))o.selected=true;});}refreshMultiDropdown('assignee');}
function stats(){const s=state.stats||{};$('total').textContent=Number(s.total||0);$('active').textContent=Number(s.active||0);$('reviewCount').textContent=Number(s.review||0);$('overdue').textContent=Number(s.overdue||0);}
function renderPagination(){const p=state.pagination||{page:1,total_pages:1,total:0};const wrap=$('pagination');wrap.hidden=Number(p.total_pages)<=1;$('prevPage').disabled=Number(p.page)<=1;$('nextPage').disabled=Number(p.page)>=Number(p.total_pages);$('pageInfo').textContent='Страница '+p.page+' из '+p.total_pages+' · задач: '+p.total;}
let activeTaskModal=null;
function closeTaskView(){
 if(!activeTaskModal)return;
 activeTaskModal.remove();activeTaskModal=null;document.body.style.overflow='';
 document.removeEventListener('keydown',activeTaskKeyHandler);
}
function activeTaskKeyHandler(e){if(e.key==='Escape')closeTaskView();}
function openTaskView(task){
 closeTaskView();
 const overlay=node('div','task-view-overlay');overlay.setAttribute('role','presentation');
 const dialog=node('section','task-view-dialog');dialog.setAttribute('role','dialog');dialog.setAttribute('aria-modal','true');dialog.setAttribute('aria-labelledby','taskViewTitle');
 const header=node('header','task-view-header'),heading=node('div','task-view-heading');
 heading.append(node('h2','',task.title||'Без названия'));
 const badges=node('div','badges');badges.append(node('span','tag '+task.status,statusLabels[task.status]||task.status));badges.append(node('span','tag '+task.priority,task.priority==='high'?'Высокий':task.priority==='low'?'Низкий':'Обычный'));if(task.is_overdue)badges.append(node('span','tag overdue','Просрочена'));if(state.archiveView)badges.append(node('span','tag done','В архиве'));heading.append(badges);heading.querySelector('h2').id='taskViewTitle';
 const close=node('button','task-view-close','×');close.type='button';close.setAttribute('aria-label','Закрыть задачу');header.append(heading,close);
 const body=node('div','task-view-body');const detail=node('div','task-view-layout');const info=node('section','task-view-info');const discussion=node('section','task-view-discussion');
 if(task.description){const description=node('div','task-view-description');description.append(node('h3','','Описание'),node('p','',task.description));info.append(description);}
 const meta=node('div','task-view-meta');
 [['Объект',task.object_names||task.object_name],['Исполнители',task.assignee_names||task.assignee_name],['Поставил',task.creator_name],['Срок',date(task.due_date)],['Статус',statusLabels[task.status]||task.status],['Создана',time(task.created_at)],['Обновлена',time(task.updated_at)],...(state.archiveView?[['В архиве',time(task.archived_at)]]:[])].forEach(([k,v])=>{const item=node('div','task-view-meta-item');item.append(node('span','',k),node('strong','',v||'—'));meta.append(item);});info.append(meta);
 const actions=node('div','task-actions task-view-actions');
 const runAction=fn=>()=>{closeTaskView();fn();};
 if(state.archiveView&&task.can_manage){const restore=node('button','btn primary','Вернуть в работу');restore.type='button';restore.addEventListener('click',runAction(()=>restoreTask(task.id)));actions.append(restore);}
 if(!state.archiveView&&task.can_update){
  if(task.status==='new'||task.status==='review'){const b=node('button','btn',task.status==='review'?'Вернуть в работу':'Взять в работу');b.type='button';b.addEventListener('click',runAction(()=>change(task.id,{status:'in_progress'})));actions.append(b);}
  if(task.status==='in_progress'&&task.is_recipient){const b=node('button','btn primary','На проверку');b.type='button';b.addEventListener('click',runAction(()=>change(task.id,{status:'review'})));actions.append(b);}
  if(task.status==='review'&&task.can_manage){const b=node('button','btn primary','Принять выполнение');b.type='button';b.addEventListener('click',runAction(()=>change(task.id,{status:'done'})));actions.append(b);}
  if(task.status==='done'&&task.can_manage){const b=node('button','btn','Вернуть в работу');b.type='button';b.addEventListener('click',runAction(()=>change(task.id,{status:'in_progress'})));actions.append(b);const archive=node('button','btn primary','В архив');archive.type='button';archive.addEventListener('click',runAction(()=>archiveTask(task.id)));actions.append(archive);}
 }
 if(actions.childNodes.length)info.append(actions);
 const comments=node('details','comments task-view-comments');comments.open=true;const summary=node('summary','','Обсуждение ('+(task.comment_count||0)+')');comments.append(summary);
 const commentItems=node('div','comments-list');comments.append(commentItems);
 comments.addEventListener('toggle',async()=>{
  if(!comments.open||comments.dataset.loaded==='1')return;
  comments.dataset.loaded='1';commentItems.replaceChildren(node('div','comment','Загружаем комментарии…'));
  try{
   const data=await get('task_comments',{id:task.id});comments._mentionable=data.mentionable_users||[];const mentionSelect=comments.querySelector('.comment-form select');if(mentionSelect)(data.mentionable_users||[]).forEach(u=>mentionSelect.add(new Option(u.full_name,u.id)));commentItems.replaceChildren();
   (data.comments||[]).forEach(c=>{
    const item=node('div','comment');
    if(Number(c.can_delete)===1){const del=node('button','comment-delete','×');del.type='button';del.title='Удалить комментарий';del.setAttribute('aria-label','Удалить комментарий');del.addEventListener('click',()=>deleteComment(c.id,task,item,summary,commentItems));item.append(del);}
    item.append(node('b','',c.user_name||'Сотрудник'),node('small','',time(c.created_at)),node('p','',c.comment));
    (c.mentions||[]).forEach(m=>item.append(node('span','tag','@'+m.full_name)));
    (c.attachments||[]).forEach(a=>{const link=node('a','',a.original_name+' · '+Math.max(1,Math.round(Number(a.file_size||0)/1024))+' КБ');link.href='api.php?action=task_comment_attachment_download&id='+encodeURIComponent(a.id);link.target='_blank';link.rel='noopener';item.append(link);});
    commentItems.append(item);
   });
   summary.textContent='Обсуждение ('+(data.comments||[]).length+')';if(!(data.comments||[]).length)commentItems.append(node('div','comment','Комментариев пока нет.'));
  }catch(err){comments.dataset.loaded='0';commentItems.replaceChildren(node('div','comment',err.message));}
 });
 if(!state.archiveView){
  const form=node('form','comment-form'),area=node('textarea'),mention=node('select'),fileInput=node('input'),send=node('button','btn primary','Отправить');
  area.maxLength=3000;area.rows=2;area.placeholder='Напишите комментарий…';mention.add(new Option('Упомянуть сотрудника…',''));mention.setAttribute('aria-label','Упомянуть сотрудника');fileInput.type='file';fileInput.multiple=true;fileInput.accept='.pdf,.txt,.csv,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx';fileInput.setAttribute('aria-label','Прикрепить файлы (до 5, по 10 МБ)');
  const mentionIds=new Set();mention.addEventListener('change',()=>{const id=Number(mention.value);if(!id)return;const person=(comments._mentionable||[]).find(u=>Number(u.id)===id);if(person&&!mentionIds.has(id)){area.value+=(area.value?' ':'')+'@'+person.full_name+' ';mentionIds.add(id);}mention.value='';});
  form.append(area,mention,fileInput,send);send.setAttribute('aria-live','polite');form.addEventListener('submit',async e=>{e.preventDefault();const comment=area.value.trim(),files=Array.from(fileInput.files||[]);if((!comment&&!files.length)||send.disabled)return;send.disabled=true;send.textContent='Отправляем…';form.classList.add('is-sending');clearError();try{const fd=new FormData();fd.append('csrf',state.csrf);fd.append('task_id',String(task.id));fd.append('comment',comment);fd.append('mentions',JSON.stringify(Array.from(mentionIds)));files.forEach(file=>fd.append('attachments[]',file));const response=await fetch('api.php?action=task_comment_add',{method:'POST',credentials:'same-origin',body:fd});const result=await json(response);if(!response.ok||!result.ok)throw new Error(result.error||'Не удалось отправить комментарий.');area.value='';fileInput.value='';mentionIds.clear();task.comment_count=Number(task.comment_count||0)+1;summary.textContent='Обсуждение ('+task.comment_count+')';await load(1);comments.dataset.loaded='0';comments.dispatchEvent(new Event('toggle'));}catch(err){showError(err.message);}finally{send.disabled=false;send.textContent='Отправить';form.classList.remove('is-sending');}});
  discussion.append(form);
 }
 discussion.prepend(comments);
 detail.append(info,discussion);body.append(detail);dialog.append(header,body);overlay.append(dialog);document.body.append(overlay);document.body.style.overflow='hidden';activeTaskModal=overlay;
 close.addEventListener('click',closeTaskView);overlay.addEventListener('click',e=>{if(e.target===overlay)closeTaskView();});document.addEventListener('keydown',activeTaskKeyHandler);
 comments.dispatchEvent(new Event('toggle'));
 requestAnimationFrame(()=>close.focus());
}
function render(){
 const rows=state.tasks;stats();renderPagination();tasksEl.replaceChildren();
 if(!rows.length){tasksEl.append(node('div','empty',Number(state.stats.total||0)>0?'По заданным фильтрам задач нет.':'Пока задач нет. Новые поручения появятся здесь.'));return;}
 rows.forEach(t=>{
  const card=node('article','task-row-card'+(t.is_overdue?' overdue':''));const open=node('button','task-row-open');open.type='button';open.setAttribute('aria-label','Открыть задачу: '+(t.title||''));
  const main=node('div','task-row-main'),title=node('strong','task-row-title',t.title||'Без названия');main.append(title);
  const badges=node('div','badges');badges.append(node('span','tag '+t.status,statusLabels[t.status]||t.status));badges.append(node('span','tag '+t.priority,t.priority==='high'?'Высокий':t.priority==='low'?'Низкий':'Обычный'));if(t.is_overdue)badges.append(node('span','tag overdue','Просрочена'));if(state.archiveView)badges.append(node('span','tag done','В архиве'));main.append(badges);open.append(main);
  const meta=node('div','task-row-meta');[['Исполнитель',t.assignee_names||t.assignee_name],['Срок',date(t.due_date)],['Объект',t.object_names||t.object_name]].forEach(([label,value])=>{if(value){const item=node('span','');item.append(node('b','',label+': '),document.createTextNode(value));meta.append(item);}});open.append(meta);
  const controls=node('div','task-row-controls');
  if(state.user&&state.user.role==='admin'){
   if(!state.archiveView){const edit=node('button','task-row-icon','✎');edit.type='button';edit.title='Изменить задачу';edit.setAttribute('aria-label','Изменить задачу');edit.addEventListener('click',e=>{e.stopPropagation();editTask(t);});controls.append(edit);}
   const del=node('button','task-row-icon danger','×');del.type='button';del.title='Удалить задачу';del.setAttribute('aria-label','Удалить задачу');del.addEventListener('click',e=>{e.stopPropagation();deleteTask(t.id,t.title);});controls.append(del);
  }
  open.addEventListener('click',()=>openTaskView(t));card.append(open,controls);tasksEl.append(card);
 });
}
function resetTaskForm(){editingTaskId=0;$('createForm').reset();Array.from($('object').options).forEach(o=>o.selected=false);Array.from($('assignee').options).forEach(o=>o.selected=false);$('taskModalTitle').textContent='Поставить новую задачу';$('createBtn').textContent='Создать задачу';$('cancelEdit').hidden=false;$('createPanel').classList.add('hidden');document.body.style.overflow='';fillAssignees(false);refreshMultiDropdown('object');refreshMultiDropdown('assignee');}
function openCreateModal(){if($('openCreate').hidden)return;resetTaskForm();$('createPanel').classList.remove('hidden');document.body.style.overflow='hidden';requestAnimationFrame(()=>$('title').focus({preventScroll:true}));}
function editTask(task){
 if(!state.user||state.user.role!=='admin'||state.archiveView)return;
 editingTaskId=Number(task.id);$('createPanel').classList.remove('hidden');document.body.style.overflow='hidden';$('taskModalTitle').textContent='Изменить задачу';$('createBtn').textContent='Сохранить изменения';$('cancelEdit').hidden=false;
 $('title').value=task.title||'';$('description').value=task.description||'';$('priority').value=task.priority||'normal';$('due').value=task.due_date||'';
 const objectIds=Array.isArray(task.object_ids)?task.object_ids.map(Number):[];const assigneeIds=Array.isArray(task.assignee_ids)?task.assignee_ids.map(Number):[];
 const allObjectIds=state.objects.map(o=>Number(o.id));const allObjectsSelected=allObjectIds.length>0&&allObjectIds.every(id=>objectIds.includes(id));
 Array.from($('object').options).forEach(o=>o.selected=o.value==='__all__'?allObjectsSelected:objectIds.includes(Number(o.value)));
 fillAssignees(false);const availableIds=state.assignees.filter(u=>(!state.user||Number(u.id)!==Number(state.user.id))&&(!objectIds.length||(u.object_ids||[]).some(id=>objectIds.includes(Number(id))))).map(u=>Number(u.id));const allAssigneesSelected=availableIds.length>0&&availableIds.every(id=>assigneeIds.includes(id));
 Array.from($('assignee').options).forEach(o=>o.selected=o.value==='__all__'?allAssigneesSelected:assigneeIds.includes(Number(o.value)));
 refreshMultiDropdown('object');refreshMultiDropdown('assignee');
 requestAnimationFrame(()=>$('title').focus({preventScroll:true}));
}
async function deleteComment(commentId,task, item, summary, commentItems){
 if(!confirm('Удалить этот комментарий?'))return;
 clearError();
 try{
  await api('task_comment_delete',{comment_id:Number(commentId)});
  item.remove();
  task.comment_count=Math.max(0,Number(task.comment_count||0)-1);
  summary.textContent='Комментарии ('+task.comment_count+')';
  if(!commentItems.querySelector('.comment'))commentItems.append(node('div','comment','Комментариев пока нет.'));
 }catch(e){showError(e.message);}
}
async function archiveTask(id){if(!confirm('Переместить выполненную задачу в архив?'))return;clearError();try{await api('task_archive',{id});await load(state.pagination.page||1);}catch(e){showError(e.message);}}
async function restoreTask(id){if(!confirm('Вернуть задачу из архива в работу?'))return;clearError();try{await api('task_restore',{id});await load(state.pagination.page||1);}catch(e){showError(e.message);}}
async function deleteTask(id,title){if(!confirm('Удалить задачу «'+title+'» без возможности восстановления? Комментарии и назначения этой задачи также будут удалены.'))return;clearError();try{await api('task_delete',{id});await load(state.pagination.page||1);}catch(e){showError(e.message);}}
async function change(id,payload){clearError();try{await api('task_update',{id,...payload});await load(state.pagination.page||1);}catch(e){showError(e.message);}}
async function load(page){const token=++loadToken;clearError();try{
 const params={page:page||state.pagination.page||1,search:$('search').value.trim(),status:$('statusFilter').value,object_id:$('objectFilter').value,archive:state.archiveView?'1':'0'};
 const d=await get('tasks_data',params);if(token!==loadToken)return;
 state.tasks=d.tasks||[];state.stats=d.stats||{};state.pagination=d.pagination||{page:1,total_pages:1,total:0};state.objects=d.objects||[];state.assignees=d.assignees||[];state.user=d.user;state.csrf=d.csrf||'';
 const taskPermissions=new Set(state.user.permissions||[]);const canCreate=state.user.role==='admin'||(state.user.role==='moderator'&&taskPermissions.has('tasks.create'));$('openCreate').hidden=!canCreate;$('description').placeholder='Опишите задачу, ожидаемый результат и важные детали';
 const selectedObjects=Array.from($('object').selectedOptions).map(o=>o.value);const selectedAssignees=Array.from($('assignee').selectedOptions).map(o=>o.value);
 $('object').replaceChildren(new Option('Все объекты','__all__'));$('objectFilter').replaceChildren(new Option('Все объекты',''));
 state.objects.forEach(o=>{$('object').add(new Option(o.name,o.id));$('objectFilter').add(new Option(o.name,o.id));});
 $('objectFilter').value=params.object_id||'';
 Array.from($('object').options).forEach(o=>o.selected=selectedObjects.includes(o.value));
 fillAssignees(false);Array.from($('assignee').options).forEach(o=>o.selected=selectedAssignees.includes(o.value));
 refreshMultiDropdown('object');refreshMultiDropdown('assignee');render();
 }catch(e){if(token!==loadToken)return;showError(e.message);tasksEl.replaceChildren(node('div','empty','Не удалось загрузить задачи.'));}}
fillAssignees();refreshMultiDropdown('object');refreshMultiDropdown('assignee');
$('object').addEventListener('change',()=>{normalizeMultiSelect($('object'),'__all__');const assigneeWasAll=Array.from($('assignee').selectedOptions).some(o=>o.value==='__all__');fillAssignees(false);if(assigneeWasAll)$('assignee').options[0].selected=true;refreshMultiDropdown('object');refreshMultiDropdown('assignee');});
$('assignee').addEventListener('change',()=>{normalizeMultiSelect($('assignee'),'__all__');refreshMultiDropdown('assignee');});
$('createForm').addEventListener('submit',async e=>{e.preventDefault();clearError();const b=$('createBtn');b.disabled=true;try{const object_ids=selectedObjectIds();const assigneeOptions=Array.from($('assignee').selectedOptions);const assignee_ids=assigneeOptions.some(o=>o.value==='__all__')?Array.from($('assignee').options).filter(o=>o.value!=='__all__').map(o=>Number(o.value)):assigneeOptions.map(o=>Number(o.value));const payload={title:$('title').value.trim(),description:$('description').value.trim(),object_ids,assignee_ids,priority:$('priority').value,due_date:$('due').value};if(editingTaskId){await api('task_edit',{id:editingTaskId,...payload});}else{await api('task_create',payload);}resetTaskForm();await load(1);}catch(err){showError(err.message);}finally{b.disabled=false;}});
$('cancelEdit').addEventListener('click',resetTaskForm);
$('openCreate').addEventListener('click',openCreateModal);
$('closeTaskModal').addEventListener('click',resetTaskForm);
$('createPanel').addEventListener('click',e=>{if(e.target===$('createPanel'))resetTaskForm();});
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!$('createPanel').classList.contains('hidden'))resetTaskForm();});
$('search').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>load(1),300);});
$('statusFilter').addEventListener('change',()=>load(1));$('objectFilter').addEventListener('change',()=>load(1));
$('prevPage').addEventListener('click',()=>load(Math.max(1,Number(state.pagination.page)-1)));
$('nextPage').addEventListener('click',()=>load(Math.min(Number(state.pagination.total_pages),Number(state.pagination.page)+1)));
$('archiveToggle').addEventListener('click',()=>{state.archiveView=!state.archiveView;$('archiveToggle').textContent=state.archiveView?'Текущие задачи':'Архив задач';$('tasksHeading').textContent=state.archiveView?'Архив задач':'Задачи и поручения';$('statusFilter').value='';$('search').value='';$('objectFilter').value='';load(1);});
$('reload').addEventListener('click',()=>load(state.pagination.page||1));load(1);
})();
</script>
<script>(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,60000);document.addEventListener('visibilitychange',ping);})();</script>
<script src="site-header.js"></script>
</body></html>