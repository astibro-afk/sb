(function(root){
'use strict';
function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function num(s){var n=parseFloat(s);if(isNaN(n))return s;return n.toLocaleString('ru-RU');}
function num2(s){var x=parseFloat(String(s||'').replace(',','.'));return isNaN(x)?0:x;}
function fdate(s){if(!s)return '';var p=String(s).split('-');return p.length===3?p[2]+'.'+p[1]+'.'+p[0]:s;}
var data={},dynData={};
function v(id){return String(data&&data[id]!=null?data[id]:'').trim();}
function recNum(d,k){var x=parseFloat(String(d&&d[k]||'').replace(',','.'));return isNaN(x)?0:x;}
function validateReport(){return [];}
function build(input,dynamic){
data=input||{};dynData=dynamic||{};
var B11_POSTS = [['cash', 'Кассовая зона'], ['kso', 'КСО'], ['entr', 'Служебный вход']];
var INSTR_TOPICS = {
  t1: 'О запрете применения физической силы к покупателям, посетителям и персоналу',
  t2: 'Порядок действий сотрудника ЧОО при ЧП/ЧС',
  t3: 'Обеспечение внутриобъектового и пропускного режимов',
  t4: 'Правила осмотра личных вещей персонала и посетителей',
  t5: 'Правила обхода террритории ГМ',
  t6: 'Действия при обнаружении признаков правонарушения (взлом, проникновение)',
  t7: 'Оказание первой помощи пострадавшим',
  t8: 'Другая тема инструктажа'
};
function has(x) {
  if (x === '') return false;
  var n = parseFloat(x);
  return !(isNaN(n)) ? n !== 0 : false;
}

function num(s) {
  var n = parseFloat(s);
  if (isNaN(n)) return s;
  return n.toLocaleString('ru-RU');
}

function num2(s) {
  var x = parseFloat(String(s || '').replace(',', '.'));
  return isNaN(x) ? 0 : x;
}

function fdate(s) {
  if (!s) return '';
  var p = s.split('-');
  if (p.length === 3) return p[2] + '.' + p[1] + '.' + p[0];
  return s;
}

function ph(x) {
  return x ? esc(x) : '<span class="ph">[не заполнено]</span>';
}

// Логика парных строк (количество + сумма):
// qty+sum -> "N шт. — выявлены нарушения на сумму S руб."
// qty only -> "N шт. — нарушений не выявлено"
// sum only -> "выявлены нарушения на сумму S руб."
// none -> строка не выводится
function pair(label, qtyId, sumId, unit, neutral) {
  var q = v(qtyId), s = v(sumId);
  var hq = has(q), hs = has(s);
  if (!hq && !hs) return null;
  if (neutral) {
    if (hq && hs) return label + ': ' + q + ' ' + unit + ' на сумму ' + num(s) + ' руб.';
    if (hq) return label + ': ' + q + ' ' + unit;
    return label + ': на сумму ' + num(s) + ' руб.';
  }
  if (hq && hs) return label + ': ' + q + ' ' + unit + ' — выявлены нарушения на сумму ' + num(s) + ' руб.';
  if (hq) return label + ': ' + q + ' ' + unit + ' — нарушений не выявлено';
  return label + ': выявлены нарушения на сумму ' + num(s) + ' руб.';
}

function single(label, id, unit) {
  if (!has(v(id))) return null;
  return label + ': ' + v(id) + ' ' + unit;
}

function dateline(label, value) {
  if (!value) return null;
  return label + ': ' + value;
}

function dynline(prefix, key, devNote) {
  var rows = dynData[key] || [];
  var out = [];
  for (var i = 0; i < rows.length; i++) {
    var r = rows[i], parts = [];
    if (has(r.num)) parts.push('№ ' + r.num);
    if (has(r.date)) parts.push('от ' + fdate(r.date));
    if (has(r.sum)) parts.push('на сумму ' + num(r.sum) + ' руб.');
    if (has(r.cnt)) parts.push('проверено позиций: ' + r.cnt);
    if (has(r.dev)) parts.push('выявлено расхождений, руб.: ' + r.dev);
    else if (devNote && parts.length) parts.push('нарушений не выявлено');
    if (!parts.length) continue;
    var ln = prefix + ': ' + parts.join(', ');
    if (!/\.\s*$/.test(ln)) ln += '.';
    out.push(ln);
  }
  return out;
}

// Формирование краткого текста по результатам проверок и выявленным нарушениям.
// Проверок нет, но нарушения есть -> строка «выявлено нарушений…»; проверок нет и нарушений нет -> строка не выводится.
function checkPair(label, chkId, qtyId, sumId, vword) {
  vword = vword || 'нарушений';
  var c = num2(v(chkId)), q = num2(v(qtyId)), s = num2(v(sumId));
  if (!c && !q && !s) return null;
  if (!c) {
    if (q || s) return label + ': выявлено ' + vword + ': ' + num(q) + ' шт.' + (s ? ', на сумму ' + num(s) + ' руб.' : '') + '.';
    return null;
  }
  var base = label + ': проведено проверок: ' + num(c) + ' шт.';
  if (!q && !s) return base + ' ' + vword.charAt(0).toUpperCase() + vword.slice(1) + ' не выявлено.';
  return base + ' Выявлено ' + vword + ': ' + num(q) + ' шт.' + (s ? ', на сумму ' + num(s) + ' руб.' : '') + '.';
}
function checkMulti(chkId, pairs) {
  var c = num2(v(chkId));
  var totQ = 0, totS = 0, parts = [], any = false;
  for (var i = 0; i < pairs.length; i++) {
    var q = num2(v(pairs[i][1])), s = num2(v(pairs[i][2]));
    if (q || s) {
      any = true;
      totQ += q; totS += s;
      parts.push(pairs[i][0] + ' — ' + num(q) + ' шт.' + (s ? ', ' + num(s) + ' руб.' : ''));
    }
  }
  if (!c && !any) return null;
  if (!c) {
    var o0 = 'Выявлено нарушений: ' + num(totQ) + ' шт.' + (totS ? ', на сумму ' + num(totS) + ' руб.' : '');
    if (parts.length > 1) o0 += ' В том числе: ' + parts.join('; ');
    return o0 + '.';
  }
  if (!any) return 'Проведено проверок: ' + num(c) + ' шт. Нарушений не выявлено.';
  var o = 'Проведено проверок: ' + num(c) + ' шт. Выявлено нарушений: ' + num(totQ) + ' шт.' + (totS ? ', на сумму ' + num(totS) + ' руб.' : '');
  if (parts.length > 1) o += ' В том числе: ' + parts.join('; ');
  return o + '.';
}

// Автоматическая проверка логической согласованности показателей.
// Все найденные несоответствия являются предупреждениями: пользователь может продолжить экспорт.
function chopText() {
    var lines = [], missing = [];
    B11_POSTS.forEach(function(p) {
      if (v('b11_post_' + p[0]) === '1') {
        var from = v('b11_from_' + p[0]), to = v('b11_to_' + p[0]);
        var t = '';
        if (from || to) {
          var rd = v('b0_date');
          var dF = v('b11_dfrom_' + p[0]) || rd;
          var dT = v('b11_dto_' + p[0]) || dF;
          t = ' (с ' + (fdate(dF) || '--.--.----') + ' г. ' + (from || '--:--') + ' до ' + (fdate(dT) || '--.--.----') + ' г. ' + (to || '--:--') + ')';
        }
        missing.push('На посту «' + p[1] + '» отсутствовал сотрудник ЧОО' + t + '.');
      }
    });
    if (missing.length) missing.forEach(function(m) { lines.push(m); lines.push('Составлен соответствующий акт о выявленном нарушении.'); });
    else lines.push('Посты ЧОП: нарушений нет — все посты укомплектованы сотрудниками ЧОО.');
    var uch = v('b11_uch');
    if (uch === 'ok') lines.push('Проверка УЧО у сотрудника ЧОО: нарушений не выявлено.');
    else if (uch === 'none') { lines.push('Проверка УЧО: УЧО отсутствует' + (v('b11_uch_fio') ? ' у сотрудника ЧОО ' + v('b11_uch_fio') : '') + '.'); lines.push('Составлен соответствующий акт о выявленном нарушении.'); }
    var instr = v('b11_instr');
    if (instr === 'none') lines.push('Инструктаж сотрудников ЧОП: не проводился.');
    else if (instr === 'other') { if (v('b11_instr_topic')) lines.push('Проведён инструктаж по теме: «' + v('b11_instr_topic') + '».'); }
    else if (instr) lines.push('Проведён инструктаж по теме: «' + INSTR_TOPICS[instr] + '».');
    return lines;
  }
function sumDetailSpec() {
  return [
    { t: '1. Кассовые операции', items: [
      ['Проверок: отмена позиций в чеке', 'P', ['f','b1_pos_checks'], null],
      ['Нарушения: отмена позиций в чеке', 'V', ['f','b1_pos_qty'], ['f','b1_pos_sum']],
      ['Проверок: аннулирование чека', 'P', ['f','b1_chek_checks'], null],
      ['Нарушения: аннулирование чека', 'V', ['f','b1_chek_qty'], ['f','b1_chek_sum']],
      ['Проверок: фиктивные возвраты', 'P', ['f','b1_ret_checks'], null],
      ['Нарушения: фиктивные возвраты', 'V', ['f','b1_ret_qty'], ['f','b1_ret_sum']],
      ['Проверок: продажа с заниженным весом', 'P', ['f','b1_weight_checks'], null],
      ['Нарушения: продажа с заниженным весом', 'V', ['f','b1_weight_qty'], ['f','b1_weight_sum']]
    ]},
    { t: '2. Работа с FaceID, реагирование, пополнение базы данных', items: [
      ['Срабатываний', 'S', ['f','b2_triggers'], null],
      ['Отработано (реагирование)', 'S', ['f','b2_worked'], null],
      ['Пополнено базы данных', 'S', ['f','b2_db'], null]
    ]},
    { t: '3. Перемещения ТМЦ (внутри объекта, между объектами)', items: [
      ['Проверок перемещений внутри объекта', 'P', ['f','b3_in_checks'], null],
      ['Нарушения внутри объекта', 'V', ['f','b3_in_qty'], ['f','b3_in_sum']],
      ['Проверок перемещений между объектами', 'P', ['f','b3_between_checks'], null],
      ['Нарушения между объектами', 'V', ['f','b3_between_qty'], ['f','b3_between_sum']]
    ]},
    { t: '4. Контрольные проверки проведённых инвентаризаций', items: [
      ['Проведено контрольных проверок', 'P', ['f','b4_checks'], null],
      ['Выявленные расхождения', 'V', ['f','b4_dev_qty'], ['f','b4_dev_sum']],
      ['Инвентаризаций по строкам, шт.', 'S', ['d','b4_inv','CNT'], null],
      ['Сумма по строкам инвентаризаций, руб.', 'S', null, ['d','b4_inv','sum']]
    ]},
    { t: '5. Выгрузка виртуальных остатков', items: [
      ['Выгружено виртуальных остатков, поз.', 'S', ['f','b5_positions'], null],
      ['Сумма выгрузки, руб.', 'S', null, ['f','b5_sum']]
    ]},
    { t: '6. Поставки с РЦ', items: [
      ['Проведено проверок', 'P', ['f','b6_checks'], null],
      ['Нарушения по качеству', 'V', ['f','b6_q_qty'], ['f','b6_q_sum']],
      ['Нарушения по количеству', 'V', ['f','b6_qty_qty'], ['f','b6_qty_sum']],
      ['Нарушения по срокам годности', 'V', ['f','b6_sg_qty'], ['f','b6_sg_sum']],
      ['Проверено позиций по строкам поставок', 'C', ['d','b6_sup','cnt'], null],
      ['Расхождения по строкам поставок, руб.', 'R', null, ['d','b6_sup','dev']]
    ]},
    { t: '7. Поставки от прямых поставщиков', items: [
      ['Проведено проверок', 'P', ['f','b7_checks'], null],
      ['Нарушения по качеству', 'V', ['f','b7_q_qty'], ['f','b7_q_sum']],
      ['Нарушения по количеству', 'V', ['f','b7_qty_qty'], ['f','b7_qty_sum']],
      ['Нарушения по срокам годности', 'V', ['f','b7_sg_qty'], ['f','b7_sg_sum']],
      ['Проверено позиций по строкам поставок', 'C', ['d','b7_sup','cnt'], null],
      ['Расхождения по строкам поставок, руб.', 'R', null, ['d','b7_sup','dev']]
    ]},
    { t: '8. Проверка качества списания ТМЦ', items: [
      ['Проведено проверок', 'P', ['f','b8_checks'], null],
      ['Выявленные нарушения', 'V', ['f','b8_qty'], ['f','b8_sum']],
      ['Проверено позиций по строкам списаний', 'C', ['d','b8_sp','cnt'], null],
      ['Расхождения по строкам списаний, руб.', 'R', null, ['d','b8_sp','dev']]
    ]},
    { t: '9. Служебные записки на ДГМ', items: [
      ['Количество записок', 'S', ['f','b9_count'], null]
    ]},
    { t: '10. Хищения (уголовные, административные)', items: [
      ['Уголовные преступления', 'V', ['f','b10_crim_qty'], ['f','b10_crim_sum']],
      ['Административные правонарушения', 'V', ['f','b10_adm_qty'], ['f','b10_adm_sum']],
      ['Разбор на месте', 'V', ['f','b10_local_qty'], ['f','b10_local_sum']]
    ]}
  ];
}
function sumDetailAgg(sp, it) {
  if (!sp) return null;
  if (sp[0] === 'f') {
    var x;
    if (it) x = num2(it.data && it.data[sp[1]]);
    else { var y = parseFloat(String(v(sp[1]) || '').replace(',', '.')); x = isNaN(y) ? 0 : y; }
    return x !== 0 ? x : null;
  }
  var rows = it ? ((it.dyn && it.dyn[sp[1]]) || []) : (dynData[sp[1]] || []);
  if (!rows.length) return null;
  if (sp[2] === 'CNT') return rows.length;
  var s2 = 0, any2 = false;
  for (var r = 0; r < rows.length; r++) {
    var y2 = parseFloat(String(rows[r][sp[2]] || '').replace(',', '.'));
    if (!isNaN(y2) && y2 !== 0) { s2 += y2; any2 = true; }
  }
  return any2 ? s2 : null;
}
function sumDetailData(recs) {
  var list = recs || [null];
  var out = [];
  sumDetailSpec().forEach(function(sec) {
    var items = [];
    sec.items.forEach(function(it) {
      var q = null, s = null, violations = 0;
      for (var i = 0; i < list.length; i++) {
        var a = sumDetailAgg(it[2], list[i]), b = sumDetailAgg(it[3], list[i]);
        if (a !== null) q = (q || 0) + a;
        if (b !== null) {
          s = (s || 0) + b;
          // Для кассовых операций каждая строка с суммой > 0 означает одно нарушение.
          if (it[1] === 'M' && b > 0) violations++;
        }
      }
      if (q === null && s === null) return;
      items.push({ t: it[0], k: it[1], q: q, s: s, v: it[1] === 'M' ? violations : null });
    });
    if (items.length) out.push({ t: sec.t, items: items });
  });
  return out;
}
function sumDetailTotals(data) {
  // P — проверки; M — кассовые проверки (количество) и 1 нарушение на строку при сумме > 0;
  // V — количество нарушений; R — только денежные расхождения, без увеличения количества нарушений.
  var tp = 0, tv = 0, ts = 0, any = false;
  (data || []).forEach(function(sec) {
    sec.items.forEach(function(it) {
      if (it.k === 'P') { if (it.q !== null) { tp += it.q; any = true; } }
      else if (it.k === 'M') {
        if (it.q !== null) { tp += it.q; any = true; }
        if (it.v) { tv += it.v; any = true; }
        if (it.s !== null) { ts += it.s; any = true; }
      }
      else if (it.k === 'V') {
        // Количество нарушения учитывается только при положительной сумме ущерба.
        if (it.s !== null && it.s > 0) {
          if (it.q !== null) tv += it.q;
          ts += it.s;
          any = true;
        }
      }
      else if (it.k === 'R') { if (it.s !== null && it.s > 0) { ts += it.s; any = true; } }
    });
  });
  return any ? { p: tp, v: tv, s: ts } : null;
}
function buildDoc() {
  var secs = [];
  function sec(t, arr) {
    var lines = [];
    for (var i = 0; i < arr.length; i++) {
      var it = arr[i];
      if (!it) continue;
      if (it.join) { for (var k2 = 0; k2 < it.length; k2++) if (it[k2]) lines.push(it[k2]); }
      else lines.push(it);
    }
    secs.push({ t: t, lines: lines });
  }

  sec('1. Проверка кассовых операций (удаление позиций из чека, удаление чека, возвраты, низкий вес товара)', [
    checkPair('Отмена позиций в чеке', 'b1_pos_checks', 'b1_pos_qty', 'b1_pos_sum'),
    checkPair('Анулирование чека в отсутствии администратора', 'b1_chek_checks', 'b1_chek_qty', 'b1_chek_sum'),
    checkPair('Фиктивные возвраты', 'b1_ret_checks', 'b1_ret_qty', 'b1_ret_sum'),
    checkPair('Продажа товара с заниженным весом', 'b1_weight_checks', 'b1_weight_qty', 'b1_weight_sum')
  ]);

  sec('2. Работа с FaceID, реагирование, пополнение базы данных', [
    single('Срабатываний', 'b2_triggers', 'шт.'),
    single('Отработано (реагирование)', 'b2_worked', 'шт.'),
    single('Пополнено базы данных', 'b2_db', 'чел.')
  ]);

  sec('3. Контроль за перемещениями ТМЦ (внутри объекта, между объектами)', [
    checkPair('Перемещения внутри объекта', 'b3_in_checks', 'b3_in_qty', 'b3_in_sum'),
    checkPair('Перемещения между объектами', 'b3_between_checks', 'b3_between_qty', 'b3_between_sum')
  ]);

  sec('4. Контрольные проверки проведённых инвентаризаций операционной службой', [
    checkPair('Контрольные проверки инвентаризаций', 'b4_checks', 'b4_dev_qty', 'b4_dev_sum', 'расхождений'),
    dynline('Инвентаризация', 'b4_inv')
  ]);

  sec('5. Выгрузка виртуальных остатков, предоставление информации операционной службе для пересчёта (СЗ на ДГМ, копия Директору Группы)', [
    dateline('Дата выгрузки', fdate(v('b5_date'))),
    pair('Выгружено виртуальных остатков', 'b5_positions', 'b5_sum', 'поз.', true),
    dateline('Служебная записка на ДГМ от', fdate(v('b5_sz_date'))),
    dateline('Копия Директору Группы', v('b5_copy'))
  ]);

  sec('6. Проверка качества поставки ТМЦ с РЦ (сырьё для производства, сыры, колбасы, мясо и т.д. — качество, количество, СГ)', [
    checkMulti('b6_checks', [
      ['по качеству', 'b6_q_qty', 'b6_q_sum'],
      ['по количеству', 'b6_qty_qty', 'b6_qty_sum'],
      ['по срокам годности', 'b6_sg_qty', 'b6_sg_sum']
    ]),
    dynline('Проверка поставки', 'b6_sup')
  ]);

  sec('7. Проверка качества поставки ТМЦ от прямых поставщиков', [
    checkMulti('b7_checks', [
      ['по качеству', 'b7_q_qty', 'b7_q_sum'],
      ['по количеству', 'b7_qty_qty', 'b7_qty_sum'],
      ['по срокам годности', 'b7_sg_qty', 'b7_sg_sum']
    ]),
    dynline('Проверка поставки', 'b7_sup')
  ]);

  sec('8. Проверка качества списания ТМЦ', [
    checkMulti('b8_checks', [['нарушений', 'b8_qty', 'b8_sum']]),
    dynline('Проверка списаний', 'b8_sp', true)
  ]);

  sec('9. Количество служебных записок по выявленным нарушениям на ДГМ', [
    single('Количество служебных записок', 'b9_count', 'шт.')
  ]);

  sec('10. Хищения (уголовные, административные)', [
    pair('Уголовные преступления', 'b10_crim_qty', 'b10_crim_sum', 'шт.'),
    pair('Административные правонарушения', 'b10_adm_qty', 'b10_adm_sum', 'шт.'),
    pair('Разбор на месте', 'b10_local_qty', 'b10_local_sum', 'шт.')
  ]);

  sec('11. Работа ООО ЧОП «Артакс+»', chopText());

  var cb = [['1', 'b1_comment'], ['2', 'b2_comment'], ['3', 'b3_comment'], ['4', 'b4_comment'], ['5', 'b5_comment'], ['6', 'b6_comment'], ['7', 'b7_comment'], ['8', 'b8_comment'], ['9', 'b9_comment'], ['10', 'b10_comment'], ['11', 'b11_comment']];
  for (var ci = 0; ci < cb.length; ci++) {
    var ct = v(cb[ci][1]);
    if (ct) {
      var nl = String(ct).split(/\r?\n/);
      for (var ni = 0; ni < nl.length; ni++) { if (nl[ni].trim()) (secs[ci].note = secs[ci].note || []).push(nl[ni].trim()); }
    }
  }

  return {
    to: v('b0_to'),
    fio: v('b0_fio'),
    date: v('b0_date'),
    secs: secs,
    qualityIssues: []
  };
}
var result=buildDoc();
result.totals=sumDetailTotals(sumDetailData([{data:data,dyn:dynData}]));
return result;
}
function docCss() {
  return '' +
    '@page{size:A4;margin:1.5cm 1.6cm 1.5cm 1.5cm} ' +
    'html,body{margin:0;padding:0;background:#fff} ' +
    'body{font-family:Arial,Helvetica,sans-serif;color:#000;background:#fff} ' +
    '.doc-wrap{font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.38;color:#000;width:100%;mso-line-height-rule:at-least} ' +
    '.doc-wrap p{orphans:3;widows:3;color:#000} ' +
    '.doc-brand{margin:0 0 17pt 0;padding:0 0 6pt 0;border-bottom:.55pt solid #777;font-size:8.5pt;line-height:1.2;font-weight:bold;letter-spacing:.3pt;color:#000;text-transform:uppercase} ' +
    '.doc-meta{margin:0 0 17pt 0;padding:0} ' +
    '.doc-meta p{margin:4pt 0;font-size:9.7pt;line-height:1.3;color:#000} ' +
    '.doc-meta strong{font-weight:bold;color:#000} ' +
    '.doc-section{margin:0 0 11pt 0;padding:0;page-break-inside:auto} ' +
    '.sec-title{margin:18pt 0 8pt 0;padding:0 0 5pt 0;font-size:11pt;line-height:1.22;font-weight:bold;color:#000;background:#fff;border:0;border-bottom:.65pt solid #888;text-indent:0;text-align:left;page-break-after:avoid;mso-pagination:keep-with-next} ' +
    '.doc-line{margin:0 0 6pt 11pt;padding:0;text-indent:0;font-size:10pt;line-height:1.38;color:#000;page-break-inside:avoid} ' +
    '.doc-line-label{font-weight:bold;color:#000} ' +
    '.doc-line-sub{margin:0 0 5pt 22pt;padding:0;font-size:9.7pt;line-height:1.34;color:#000;page-break-inside:avoid} ' +
    '.doc-note{margin:8pt 0 10pt 11pt;padding:0 0 0 7pt;border-left:.6pt solid #888;font-size:9.5pt;line-height:1.38;color:#000;text-indent:0;text-align:left} ' +
    '.doc-note strong{font-weight:bold;color:#000} ' +
    '.doc-totals{margin:19pt 0 0 0;padding:9pt 0 0 0;border-top:.9pt solid #555;page-break-inside:avoid} ' +
    '.doc-totals-title{margin:0 0 8pt 0;font-size:11pt;line-height:1.2;font-weight:bold;color:#000} ' +
    '.doc-total-line{margin:4pt 0 4pt 11pt;font-size:10pt;line-height:1.35;color:#000} ' +
    '.doc-total-line strong{font-weight:bold;color:#000} ' +
    '.doc-sign{margin:23pt 0 0 0;padding:9pt 0 0 0;border-top:.6pt solid #777;font-size:9.7pt;line-height:1.45;color:#000;page-break-inside:avoid} ' +
    '.doc-sign p{margin:5pt 0;color:#000} ' +
    '.doc-ver{margin:15pt 0 0 0;padding-top:4pt;border-top:.4pt solid #999;font-size:7.5pt;color:#000;text-align:right;font-family:Arial,Helvetica,sans-serif}';
}
function docMetaTable(rows) {
  var h = '<div class="doc-meta">';
  for (var i = 0; i < rows.length; i++) {
    h += '<p style="margin:4pt 0;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:9.7pt;line-height:1.3;color:#000000;"><strong style="font-weight:bold;color:#000000;">' + rows[i][0] + ':</strong> ' + rows[i][1] + '</p>';
  }
  return h + '</div>';
}
function docMasthead(tag) {
  return '<p class="doc-brand" style="margin:0 0 15pt 0;padding:0 0 5pt 0;border-bottom:.6pt solid #777777;font-family:Arial,Helvetica,sans-serif;font-size:8.5pt;line-height:1.2;font-weight:bold;letter-spacing:.35pt;color:#000000;text-transform:uppercase;">Группа БФ Волгоград 1&nbsp;&nbsp; / &nbsp;&nbsp;' + (tag || 'СЛУЖЕБНЫЙ ДОКУМЕНТ') + '</p>';
}
function docLineHtml(line) {
  var raw = String(line || '');
  var chunks = raw.split(/\s+(?=Выявлено(?: нарушений| расхождений)?\s*:|В том числе\s*:)/);
  var h = '';
  for (var c = 0; c < chunks.length; c++) {
    var part = chunks[c].trim();
    if (!part) continue;
    var colon = part.indexOf(':');
    if (colon > 0 && colon < 90 && part.slice(colon + 1, colon + 2) === ' ') {
      var label = part.slice(0, colon + 1);
      var value = part.slice(colon + 1).trim();
      h += '<p class="doc-line" style="margin:0 0 6pt 11pt;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.38;color:#000000;page-break-inside:avoid;"><strong style="font-weight:bold;color:#000000;">' + esc(label) + '</strong>' + (value ? ' ' + esc(value) : '') + '</p>';
    } else {
      h += '<p class="doc-line-sub" style="margin:0 0 5pt 22pt;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:9.7pt;line-height:1.34;color:#000000;page-break-inside:avoid;">' + esc(part) + '</p>';
    }
  }
  return h;
}
function docSection(title, lines, notes) {
  var h = '<div class="doc-section" style="margin:0 0 11pt 0;padding:0;">';
  h += '<p class="sec-title" style="margin:18pt 0 8pt 0;padding:0 0 5pt 0;border-bottom:.65pt solid #888888;font-family:Arial,Helvetica,sans-serif;font-size:11pt;line-height:1.22;font-weight:bold;color:#000000;text-align:left;text-indent:0;page-break-after:avoid;">' + esc(title) + '</p>';
  if (lines && lines.length) {
    for (var i = 0; i < lines.length; i++) {
      if (lines[i]) h += docLineHtml(lines[i]);
    }
  }
  if (notes && notes.length) for (var j = 0; j < notes.length; j++) {
    h += '<p class="doc-note" style="margin:8pt 0 10pt 11pt;padding:0 0 0 7pt;border-left:.6pt solid #888888;font-family:Arial,Helvetica,sans-serif;font-size:9.5pt;line-height:1.38;color:#000000;"><strong style="font-weight:bold;color:#000000;">Комментарий:</strong> ' + esc(notes[j]) + '</p>';
  }
  return h + '</div>';
}
function ensureDocCss() {
  if (document.getElementById('docCssInjected')) return;
  var el = document.createElement('style');
  el.id = 'docCssInjected';
  el.textContent = docCss();
  document.head.appendChild(el);
}


function sumDetailSpec() {
  return [
    { t: '1. Кассовые операции', items: [
      // M: количество в поле «количество» — проверено; сумма > 0 — одно выявленное нарушение по строке.
      ['Отмена позиций в чеке', 'M', ['f','b1_pos_qty'], ['f','b1_pos_sum']],
      ['Анулирование чека в отсутствии администратора', 'M', ['f','b1_chek_qty'], ['f','b1_chek_sum']],
      ['Фиктивные возвраты', 'M', ['f','b1_ret_qty'], ['f','b1_ret_sum']],
      ['Продажа товара с заниженным весом', 'M', ['f','b1_weight_qty'], ['f','b1_weight_sum']]
    ]},
    { t: '2. Работа с FaceID, реагирование, пополнение базы данных', items: [
      ['Срабатываний', 'S', ['f','b2_triggers'], null],
      ['Отработано (реагирование)', 'S', ['f','b2_worked'], null],
      ['Пополнено базы данных', 'S', ['f','b2_db'], null]
    ]},
    { t: '3. Перемещения ТМЦ (внутри объекта, между объектами)', items: [
      ['Проверок перемещений внутри объекта', 'P', ['f','b3_in_checks'], null],
      ['Нарушения внутри объекта', 'V', ['f','b3_in_qty'], ['f','b3_in_sum']],
      ['Проверок перемещений между объектами', 'P', ['f','b3_between_checks'], null],
      ['Нарушения между объектами', 'V', ['f','b3_between_qty'], ['f','b3_between_sum']]
    ]},
    { t: '4. Контрольные проверки проведённых инвентаризаций', items: [
      ['Проведено контрольных проверок', 'P', ['f','b4_checks'], null],
      ['Выявленные расхождения', 'V', ['f','b4_dev_qty'], ['f','b4_dev_sum']],
      ['Инвентаризаций по строкам, шт.', 'S', ['d','b4_inv','CNT'], null],
      ['Сумма по строкам инвентаризаций, руб.', 'S', null, ['d','b4_inv','sum']]
    ]},
    { t: '5. Выгрузка виртуальных остатков', items: [
      ['Выгружено виртуальных остатков, поз.', 'S', ['f','b5_positions'], null],
      ['Сумма выгрузки, руб.', 'S', null, ['f','b5_sum']]
    ]},
    { t: '6. Поставки с РЦ', items: [
      ['Проведено проверок', 'P', ['f','b6_checks'], null],
      ['Нарушения по качеству', 'V', ['f','b6_q_qty'], ['f','b6_q_sum']],
      ['Нарушения по количеству', 'V', ['f','b6_qty_qty'], ['f','b6_qty_sum']],
      ['Нарушения по срокам годности', 'V', ['f','b6_sg_qty'], ['f','b6_sg_sum']],
      ['Проверено позиций по строкам поставок', 'C', ['d','b6_sup','cnt'], null],
      ['Расхождения по строкам поставок, руб.', 'R', null, ['d','b6_sup','dev']]
    ]},
    { t: '7. Поставки от прямых поставщиков', items: [
      ['Проведено проверок', 'P', ['f','b7_checks'], null],
      ['Нарушения по качеству', 'V', ['f','b7_q_qty'], ['f','b7_q_sum']],
      ['Нарушения по количеству', 'V', ['f','b7_qty_qty'], ['f','b7_qty_sum']],
      ['Нарушения по срокам годности', 'V', ['f','b7_sg_qty'], ['f','b7_sg_sum']],
      ['Проверено позиций по строкам поставок', 'C', ['d','b7_sup','cnt'], null],
      ['Расхождения по строкам поставок, руб.', 'R', null, ['d','b7_sup','dev']]
    ]},
    { t: '8. Проверка качества списания ТМЦ', items: [
      ['Проведено проверок', 'P', ['f','b8_checks'], null],
      ['Выявленные нарушения', 'V', ['f','b8_qty'], ['f','b8_sum']],
      ['Проверено позиций по строкам списаний', 'C', ['d','b8_sp','cnt'], null],
      ['Расхождения по строкам списаний, руб.', 'R', null, ['d','b8_sp','dev']]
    ]},
    { t: '9. Служебные записки на ДГМ', items: [
      ['Количество записок', 'S', ['f','b9_count'], null]
    ]},
    { t: '10. Хищения (уголовные, административные)', items: [
      ['Уголовные преступления', 'V', ['f','b10_crim_qty'], ['f','b10_crim_sum']],
      ['Административные правонарушения', 'V', ['f','b10_adm_qty'], ['f','b10_adm_sum']],
      ['Разбор на месте', 'V', ['f','b10_local_qty'], ['f','b10_local_sum']]
    ]}
  ];
}
function sumDetailAgg(sp, it) {
  if (!sp) return null;
  if (sp[0] === 'f') {
    var x;
    if (it) x = recNum(it.data, sp[1]);
    else { var y = parseFloat(String(v(sp[1]) || '').replace(',', '.')); x = isNaN(y) ? 0 : y; }
    return x !== 0 ? x : null;
  }
  var rows = it ? ((it.dyn && it.dyn[sp[1]]) || []) : (dynData[sp[1]] || []);
  if (!rows.length) return null;
  if (sp[2] === 'CNT') return rows.length;
  var s2 = 0, any2 = false;
  for (var r = 0; r < rows.length; r++) {
    var y2 = parseFloat(String(rows[r][sp[2]] || '').replace(',', '.'));
    if (!isNaN(y2) && y2 !== 0) { s2 += y2; any2 = true; }
  }
  return any2 ? s2 : null;
}
function sumDetailData(recs) {
  var list = recs || [null];
  var out = [];
  sumDetailSpec().forEach(function(sec) {
    var items = [];
    sec.items.forEach(function(it) {
      var q = null, s = null, violations = 0;
      for (var i = 0; i < list.length; i++) {
        var a = sumDetailAgg(it[2], list[i]), b = sumDetailAgg(it[3], list[i]);
        if (a !== null) q = (q || 0) + a;
        if (b !== null) {
          s = (s || 0) + b;
          // Для кассовых операций каждая строка с суммой > 0 означает одно нарушение.
          if (it[1] === 'M' && b > 0) violations++;
        }
      }
      if (q === null && s === null) return;
      items.push({ t: it[0], k: it[1], q: q, s: s, v: it[1] === 'M' ? violations : null });
    });
    if (items.length) out.push({ t: sec.t, items: items });
  });
  return out;
}
function sumDetailTotals(data) {
  // P — проверки; M — кассовые проверки (количество) и 1 нарушение на строку при сумме > 0;
  // V — количество нарушений; R — только денежные расхождения, без увеличения количества нарушений.
  var tp = 0, tv = 0, ts = 0, any = false;
  (data || []).forEach(function(sec) {
    sec.items.forEach(function(it) {
      if (it.k === 'P') { if (it.q !== null) { tp += it.q; any = true; } }
      else if (it.k === 'M') {
        if (it.q !== null) { tp += it.q; any = true; }
        if (it.v) { tv += it.v; any = true; }
        if (it.s !== null) { ts += it.s; any = true; }
      }
      else if (it.k === 'V') { if (it.q !== null) { tv += it.q; any = true; } if (it.s !== null) { ts += it.s; any = true; } }
      else if (it.k === 'R') { if (it.s !== null) { ts += it.s; any = true; } }
    });
  });
  return any ? { p: tp, v: tv, s: ts } : null;
}
function sumDetailFlat() {
  var flat = [];
  sumDetailSpec().forEach(function(sec) {
    sec.items.forEach(function(it) { flat.push({ sec: sec.t, t: it[0], k: it[1], q: it[2], s: it[3] }); });
  });
  return flat;
}

function docHtml(d) {

  var out = '<div class="doc-wrap">';
  out += docMasthead('ЕЖЕДНЕВНЫЙ ОТЧЕТ');
  out += '<h1 style="margin:0 0 4pt 0;font-family:Calibri,Arial,sans-serif;font-size:17pt;line-height:1.15;font-weight:bold;color:#000000;text-align:left;page-break-after:avoid;">Отчёт по итогам контрольных мероприятий</h1>';
  out += '<p style="margin:0 0 15pt 0;font-family:Calibri,Arial,sans-serif;font-size:10pt;color:#000000;text-align:left;">' + (d.date ? 'Ежедневный отчет за ' + esc(fdate(d.date)) : 'Ежедневный отчет') + '</p>';
  var meta = [];
  if (d.to) meta.push(['Объект (ТО)', esc(d.to)]);
  if (d.fio) meta.push(['Составитель', esc(d.fio)]);
  if (d.date) meta.push(['Дата', esc(fdate(d.date))]);
  if (meta.length) out += docMetaTable(meta);
  for (var i = 0; i < d.secs.length; i++) {
    var sec = d.secs[i];
    if (!sec.lines.length && !(sec.note && sec.note.length)) continue;
    out += docSection(sec.t, sec.lines, sec.note);
  }
  // Не повторяем по второму кругу показатели разделов: выводим только общий итог.
  var dayTotals = d.totals;
  if (dayTotals) {
    out += '<div class="doc-totals" style="margin:19pt 0 0 0;padding:9pt 0 0 0;border-top:.9pt solid #555555;page-break-inside:avoid;">';
    out += '<p class="doc-totals-title" style="margin:0 0 8pt 0;font-family:Arial,Helvetica,sans-serif;font-size:11pt;line-height:1.2;font-weight:bold;color:#000000;">ИТОГО ЗА ДЕНЬ</p>';
    out += '<p class="doc-total-line" style="margin:4pt 0 4pt 11pt;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.35;color:#000000;"><strong>Проверено:</strong> ' + num(dayTotals.p) + '</p>';
    out += '<p class="doc-total-line" style="margin:4pt 0 4pt 11pt;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.35;color:#000000;"><strong>Выявлено нарушений:</strong> ' + num(dayTotals.v) + '</p>';
    out += '<p class="doc-total-line" style="margin:4pt 0 4pt 11pt;font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:1.35;color:#000000;"><strong>Сумма нарушений и расхождений:</strong> ' + num(dayTotals.s) + ' руб.</p>';
    out += '</div>';
  }
  out += '<div class="doc-sign"><p><strong>Отчёт составил:</strong> ' + (d.fio ? esc(d.fio) : '_______________________') + '</p><p><strong>Подпись:</strong> ____________________</p></div>';
  return out + '</div>';
}

function wordHtml(d) {
  var body = docHtml(d);
  var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">' +
    '<head><meta charset="utf-8"><title>Отчет по итогам контрольных мероприятий</title>' +
    '<style>' + docCss() + '<\/style></head><body>' + body +
    '</body></ht' + 'ml>';
  return html;
}
function crc32(data) {
  var T = window.__sbCrc32T;
  if (!T) {
    T = [];
    for (var n = 0; n < 256; n++) {
      var c = n;
      for (var k = 0; k < 8; k++) c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
      T[n] = c >>> 0;
    }
    window.__sbCrc32T = T;
  }
  var crc = 0 ^ (-1);
  for (var i = 0; i < data.length; i++) crc = (crc >>> 8) ^ T[(crc ^ data[i]) & 0xFF];
  return (crc ^ (-1)) >>> 0;
}
function utf8Bytes(s) {
  if (window.TextEncoder) return new TextEncoder().encode(s);
  var out = [];
  unescape(encodeURIComponent(s)).split('').forEach(function(ch) { out.push(ch.charCodeAt(0)); });
  return new Uint8Array(out);
}
function xlEsc(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
// sheets: [{name, rows}] — значение ячейки: число => numeric, иначе текст
function buildXlsx(sheets) {
  function colName(nn) {
    var s2 = ''; nn++;
    while (nn) { var md = (nn - 1) % 26; s2 = String.fromCharCode(65 + md) + s2; nn = Math.floor((nn - md - 1) / 26); }
    return s2;
  }
      function sheetXml(rows, opts) {
    opts = opts || {};
    var out = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
    if (opts.freeze || opts.freezeX) { var ySp = opts.freeze || 0, xSp = opts.freezeX || 0; out += '<sheetViews><sheetView workbookViewId="0"><pane' + (xSp ? ' xSplit="' + xSp + '"' : '') + (ySp ? ' ySplit="' + ySp + '"' : '') + ' topLeftCell="' + colName(xSp) + (ySp + 1) + '" activePane="bottomRight" state="frozen"/></sheetView></sheetViews>'; }
    out += '<sheetFormatPr defaultRowHeight="23"/>'; 
    if (opts.cols && opts.cols.length) {
      out += '<cols>';
      for (var ci = 0; ci < opts.cols.length; ci++) out += '<col min="' + (ci + 1) + '" max="' + (ci + 1) + '" width="' + opts.cols[ci] + '" customWidth="1"/>';
      out += '</cols>';
    }
    out += '<sheetData>';
    for (var r = 0; r < rows.length; r++) {
      out += '<row r="' + (r + 1) + '"' + (r === 0 ? ' ht="36" customHeight="1"' : ((r === 1 || r === 2 || r === 3) ? ' ht="32" customHeight="1"' : ' ht="23" customHeight="1"')) + '>';
      var ra = rows[r] || [];
      for (var c = 0; c < ra.length; c++) {
        var cell = ra[c];
        if (cell === null || cell === undefined || cell === '') continue;
        var isObj = (cell && typeof cell === 'object' && !Array.isArray(cell));
        var val = isObj ? cell.v : cell;
        var st = isObj ? cell.s : undefined;
        if (val === null || val === undefined || val === '') continue;
        var ref = colName(c) + (r + 1);
        if (typeof val === 'number' && isFinite(val)) out += '<c r="' + ref + '"' + (st ? ' s="' + st + '"' : '') + '><v>' + val + '</v></c>';
        else out += '<c r="' + ref + '"' + (st ? ' s="' + st + '"' : '') + ' t="inlineStr"><is><t xml:space="preserve">' + xlEsc(String(val)) + '</t></is></c>';
      }
      out += '</row>';
    }
    out += '</sheetData>';
    if (opts.merge && opts.merge.length) out += '<mergeCells count="' + opts.merge.length + '">' + opts.merge.map(function(m) { return '<mergeCell ref="' + m + '"/>'; }).join('') + '</mergeCells>';
    out += '<printOptions horizontalCentered="0"/><pageMargins left="0.28" right="0.28" top="0.55" bottom="0.55" header="0.2" footer="0.2"/><pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/><headerFooter><oddFooter><![CDATA[&LГруппа БФ Волгоград 1&RСтраница &P из &N]]></oddFooter></headerFooter>';
    out += '</worksheet>';
    return out;
  }
  var parts = [
    ['[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' +
      sheets.map(function(_, si) { return '<Override PartName="/xl/worksheets/sheet' + (si + 1) + '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'; }).join('') + '</Types>'],
    ['_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>'],
    ['xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>' +
      sheets.map(function(s, si) { return '<sheet name="' + xlEsc(s.name) + '" sheetId="' + (si + 1) + '" r:id="rId' + (si + 1) + '"/>'; }).join('') + '</sheets></workbook>'],
    ['xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
      sheets.map(function(_, si) { return '<Relationship Id="rId' + (si + 1) + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' + (si + 1) + '.xml"/>'; }).join('') +
      '<Relationship Id="rIdS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>'],
    ['xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">\n<numFmts count="2"><numFmt numFmtId="164" formatCode="#,##0"/><numFmt numFmtId="165" formatCode="#,##0.00"/></numFmts>\n<fonts count="5">\n<font><sz val="10"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><b/><sz val="10"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><b/><sz val="16"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><b/><sz val="9"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n<font><i/><sz val="9"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>\n</fonts>\n<fills count="7">\n<fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFE9EBED"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFF0F1F2"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFE5E7E9"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFF8F9FA"/></patternFill></fill>\n<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>\n</fills>\n<borders count="4">\n<border><left/><right/><top/><bottom/><diagonal/></border>\n<border><left style="thin"><color rgb="FFD9DDE0"/></left><right style="thin"><color rgb="FFD9DDE0"/></right><top style="thin"><color rgb="FFD9DDE0"/></top><bottom style="thin"><color rgb="FFD9DDE0"/></bottom><diagonal/></border>\n<border><left style="thin"><color rgb="FFC9CDD1"/></left><right style="thin"><color rgb="FFC9CDD1"/></right><top style="thin"><color rgb="FFC9CDD1"/></top><bottom style="thin"><color rgb="FFC9CDD1"/></bottom><diagonal/></border>\n<border><left/><right/><top/><bottom style="medium"><color rgb="FF9EA4AA"/></bottom><diagonal/></border>\n</borders>\n<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>\n<cellXfs count="13">\n<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>\n<xf numFmtId="0" fontId="2" fillId="0" borderId="3" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1" indent="1"/></xf>\n<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>\n<xf numFmtId="0" fontId="1" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>\n<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1" indent="1"/></xf>\n<xf numFmtId="164" fontId="0" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="165" fontId="0" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="0" fontId="3" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>\n<xf numFmtId="164" fontId="3" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="165" fontId="3" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="0" fontId="3" fillId="3" borderId="3" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1" indent="1"/></xf>\n<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n<xf numFmtId="0" fontId="3" fillId="4" borderId="2" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>\n</cellXfs></styleSheet>'],
  ];
  sheets.forEach(function(s, si) { parts.push(['xl/worksheets/sheet' + (si + 1) + '.xml', sheetXml(s.rows, s.opts)]); });

  var chunks = [], central = [], offset = 0;
  function pack(values) {
    var out = new Uint8Array(values.length), view = new DataView(out.buffer);
    for (var i = 0; i < values.length; i++) view.setUint8(i, values[i] & 255);
    chunks.push(out); offset += out.length;
  }
  function u16(v) { return [v & 255, (v >>> 8) & 255]; }
  function u32(v) { return [v & 255, (v >>> 8) & 255, (v >>> 16) & 255, (v >>> 24) & 255]; }
  function ascii(str) { var out = new Uint8Array(str.length); for (var i = 0; i < str.length; i++) out[i] = str.charCodeAt(i) & 255; return out; }
  function pushBytes(arr) { var out = arr instanceof Uint8Array ? arr : new Uint8Array(arr); chunks.push(out); offset += out.length; }
  parts.forEach(function(p) {
    var name = p[0], nameBytes = ascii(name), data = utf8Bytes(p[1]);
    var crc = crc32(data), off = offset;
    pack([].concat(u32(0x04034b50), u16(20), u16(0), u16(0), u16(0), u16(0), u32(crc), u32(data.length), u32(data.length), u16(nameBytes.length), u16(0)));
    pushBytes(nameBytes); pushBytes(data);
    central.push({ nameBytes: nameBytes, crc: crc, size: data.length, off: off });
  });
  var cdOffset = offset;
  central.forEach(function(c) {
    pack([].concat(u32(0x02014b50), u16(20), u16(20), u16(0), u16(0), u16(0), u16(0), u32(c.crc), u32(c.size), u32(c.size), u16(c.nameBytes.length), u16(0), u16(0), u16(0), u16(0), u32(0), u32(c.off)));
    pushBytes(c.nameBytes);
  });
  var cdSize = offset - cdOffset;
  pack([].concat(u32(0x06054b50), u16(0), u16(0), u16(central.length), u16(central.length), u32(cdSize), u32(cdOffset), u16(0)));
  return new Blob(chunks, { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
}

function excelBlob(d){
 var rows=[],merges=[],rowNo=1;
 function addRow(cells){rows.push(cells);rowNo++;return rowNo-1;}
 function mergeRow(value,style){var r=rowNo;addRow([{v:value,s:style},null,null,null,null]);merges.push('A'+r+':E'+r);}
 mergeRow('ЕЖЕДНЕВНЫЙ ОТЧЕТ — '+(d.date?fdate(d.date):''),1);
 addRow([{v:'Объект (ТО)',s:3},{v:d.to||'',s:4},null,{v:'Составитель',s:3},{v:d.fio||'',s:4}]);
 addRow([{v:'Дата',s:3},{v:d.date?fdate(d.date):'',s:4},null,{v:'Документ',s:3},{v:'Отчёт по итогам контрольных мероприятий',s:4}]);
 addRow([]);
 for(var i=0;i<d.secs.length;i++){
  var sec=d.secs[i];if(!sec.lines.length&&!(sec.note&&sec.note.length))continue;
  mergeRow(sec.t,10);
  for(var j=0;j<sec.lines.length;j++){var r=rowNo;addRow([{v:'  '+String(sec.lines[j]),s:4},null,null,null,null]);merges.push('A'+r+':E'+r);}
  if(sec.note&&sec.note.length)for(var n=0;n<sec.note.length;n++){var nr=rowNo;addRow([{v:'Комментарий: '+String(sec.note[n]),s:2},null,null,null,null]);merges.push('A'+nr+':E'+nr);}
 }
 mergeRow('ИТОГО ЗА ДЕНЬ',10);
 var totals=d.totals||{p:0,v:0,s:0};
 addRow([{v:'Проверено',s:7},{v:totals.p,s:8},null,null,null]);
 addRow([{v:'Выявлено нарушений',s:7},{v:totals.v,s:8},null,null,null]);
 addRow([{v:'Сумма нарушений и расхождений, руб.',s:7},{v:totals.s,s:9},null,null,null]);
 addRow([]);
 addRow([{v:'Отчёт составил: '+(d.fio||''),s:2},null,null,null,null]);
 addRow([{v:'Подпись: ______________________________',s:2},null,null,null,null]);
 return buildXlsx([{name:'Ежедневный отчёт',rows:rows,opts:{cols:[64,18,4,18,42],merge:merges,freeze:3}}]);
}

root.SBDocument={build:build,docHtml:docHtml,wordHtml:wordHtml,excelBlob:excelBlob,buildXlsx:buildXlsx,css:docCss};
})(window);