<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Exportar: edbank - Adminer</title>
<link rel="stylesheet" href="?file=default.css&amp;version=5.4.0">
<meta name='color-scheme' content='light'>
<script src='?file=functions.js&amp;version=5.4.0' nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ="></script>
<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>
<link rel='apple-touch-icon' href='?file=logo.png&amp;version=5.4.0'>
<link rel='stylesheet' href='adminer.css?v=626768237'>

<body class='ltr nojs adminer'>
<script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick});
document.body.classList.replace('nojs', 'js');
const offlineMessage = 'You are offline.';
const thousandsSeparator = ' ';</script>
<div id='help' class='jush-sql jsonly hidden'></div>
<script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});</script>
<div id='content'>
<span id='menuopen' class='jsonly'><button type='submit' name='' title='' class='icon icon-move'><span>menu</span></button></span><script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }</script>
<p id="breadcrumb"><a href="?server=">MariaDB</a> » <a href='?server=&amp;username=admin_tesa' accesskey='1' title='Alt+Shift+1'>Servidor</a> » <a href="?server=&amp;username=admin_tesa&amp;db=edbank">edbank</a> » Exportar
<h2>Exportar: edbank</h2>
<div id='ajaxstatus' class='jsonly hidden'></div>

<form action="" method="post">
<table class="layout">
<tr><th>Saída<td><label><input type='radio' name='output' value='text' checked>abrir</label><label><input type='radio' name='output' value='file'>salvar</label><label><input type='radio' name='output' value='gz'>gzip</label>
<tr><th>Formato<td><label><input type='radio' name='format' value='sql' checked>SQL</label><label><input type='radio' name='format' value='csv'>CSV,</label><label><input type='radio' name='format' value='csv;'>CSV;</label><label><input type='radio' name='format' value='tsv'>TSV</label>
<tr><th>Base de dados<td><select name='db_style'><option selected><option>USE<option>DROP+CREATE<option>CREATE</select><label><input type='checkbox' name='routines' value='1' checked>Rotinas</label><label><input type='checkbox' name='events' value='1' checked>Eventos</label><tr><th>Tabelas<td><select name='table_style'><option><option selected>DROP+CREATE<option>CREATE</select><label><input type='checkbox' name='auto_increment' value='1'>Incremento Automático</label><label><input type='checkbox' name='triggers' value='1' checked>Triggers</label><tr><th>Dados<td><select name='data_style'><option><option>TRUNCATE+INSERT<option selected>INSERT<option>INSERT+UPDATE</select></table>
<p><input type="submit" value="Exportar">
<input type='hidden' name='token' value='374478:241448'>

<table>
<script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">qsl('table').onclick = dumpClick;</script>
<thead><tr><th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables' checked>Tabelas</label><script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">qs('#check-tables').onclick = partial(formCheck, /^tables\[/);</script><th style='text-align: right;'><label class='block'>Dados<input type='checkbox' id='check-data' checked></label><script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">qs('#check-data').onclick = partial(formCheck, /^data\[/);</script></thead>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='agencia' checked>agencia</label><td align='right'><label class='block'><span id='Rows-agencia'></span><input type='checkbox' name='data[]' value='agencia' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='agencia_config' checked>agencia_config</label><td align='right'><label class='block'><span id='Rows-agencia_config'></span><input type='checkbox' name='data[]' value='agencia_config' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='cartoes' checked>cartoes</label><td align='right'><label class='block'><span id='Rows-cartoes'></span><input type='checkbox' name='data[]' value='cartoes' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='clientes' checked>clientes</label><td align='right'><label class='block'><span id='Rows-clientes'></span><input type='checkbox' name='data[]' value='clientes' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='config' checked>config</label><td align='right'><label class='block'><span id='Rows-config'></span><input type='checkbox' name='data[]' value='config' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='config_tesa' checked>config_tesa</label><td align='right'><label class='block'><span id='Rows-config_tesa'></span><input type='checkbox' name='data[]' value='config_tesa' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='investimentos' checked>investimentos</label><td align='right'><label class='block'><span id='Rows-investimentos'></span><input type='checkbox' name='data[]' value='investimentos' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='profissoes' checked>profissoes</label><td align='right'><label class='block'><span id='Rows-profissoes'></span><input type='checkbox' name='data[]' value='profissoes' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='sorte_reves' checked>sorte_reves</label><td align='right'><label class='block'><span id='Rows-sorte_reves'></span><input type='checkbox' name='data[]' value='sorte_reves' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='transacoes' checked>transacoes</label><td align='right'><label class='block'><span id='Rows-transacoes'></span><input type='checkbox' name='data[]' value='transacoes' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='user' checked>user</label><td align='right'><label class='block'><span id='Rows-user'></span><input type='checkbox' name='data[]' value='user' checked></label>
<tr><td><label class='block'><input type='checkbox' name='tables[]' value='userinfo' checked>userinfo</label><td align='right'><label class='block'><span id='Rows-userinfo'></span><input type='checkbox' name='data[]' value='userinfo' checked></label>
<script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">ajaxSetHtml('?server=&username=admin_tesa&db=edbank&script=db');</script>
</table>
</form>
<p><a href='?server=&amp;username=admin_tesa&amp;db=edbank&amp;dump=agencia%25'>agencia</a> <a href='?server=&amp;username=admin_tesa&amp;db=edbank&amp;dump=config%25'>config</a></div>

<div id='foot' class='foot'>
<div id='menu'>
<h1><a href='https://www.adminer.org/' target="_blank" rel="noreferrer noopener" id='h1'><img src='?file=logo.png&amp;version=5.4.0' width='24' height='24' alt='' id='logo'>Adminer</a> <span class='version'>5.4.0 <a href='https://www.adminer.org/#download' target="_blank" rel="noreferrer noopener" id='version'>5.4.1</a></span></h1>
<form action='' method='post'>
<div id='lang'><label>Idioma: <select name='lang'><option value="en">English<option value="ar">العربية<option value="bg">Български<option value="bn">বাংলা<option value="bs">Bosanski<option value="ca">Català<option value="cs">Čeština<option value="da">Dansk<option value="de">Deutsch<option value="el">Ελληνικά<option value="es">Español<option value="et">Eesti<option value="fa">فارسی<option value="fi">Suomi<option value="fr">Français<option value="gl">Galego<option value="he">עברית<option value="hi">हिन्दी<option value="hu">Magyar<option value="id">Bahasa Indonesia<option value="it">Italiano<option value="ja">日本語<option value="ka">ქართული<option value="ko">한국어<option value="lt">Lietuvių<option value="lv">Latviešu<option value="ms">Bahasa Melayu<option value="nl">Nederlands<option value="no">Norsk<option value="pl">Polski<option value="pt">Português<option value="pt-br" selected>Português (Brazil)<option value="ro">Limba Română<option value="ru">Русский<option value="sk">Slovenčina<option value="sl">Slovenski<option value="sr">Српски<option value="sv">Svenska<option value="ta">த‌மிழ்<option value="th">ภาษาไทย<option value="tr">Türkçe<option value="uk">Українська<option value="uz">Oʻzbekcha<option value="vi">Tiếng Việt<option value="zh">简体中文<option value="zh-tw">繁體中文</select><script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">qsl('select').onchange = function () { this.form.submit(); };</script></label> <input type='submit' value='Usar' class='hidden'>
<input type='hidden' name='token' value='144519:273761'>
</div>
</form>
<script src='?file=jush.js&amp;version=5.4.0' nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=" defer></script>
<script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">
var jushLinks = { sql:{
	"?server=&username=admin_tesa&db=edbank&table=$&": /\b(agencia|agencia_config|cartoes|clientes|config|config_tesa|investimentos|profissoes|sorte_reves|transacoes|user|userinfo)\b/g
}
};
jushLinks.bac = jushLinks.sql;
jushLinks.bra = jushLinks.sql;
jushLinks.sqlite_quo = jushLinks.sql;
jushLinks.mssql_bra = jushLinks.sql;
</script>
<script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">syntaxHighlighting('5.5', 'maria');</script>
<form action=''>
<p id='dbs'>
<input type='hidden' name='server' value=''>
<input type='hidden' name='username' value='admin_tesa'>
<label title='Base de dados'>DB: <select name='db'><option value=""><option>admin_novaTesa<option>BENotas<option>ecommerce<option selected>edbank<option>gamescool<option>information_schema<option>mysql<option>performance_schema<option>phpmyadmin<option>sys</select><script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});</script>
</label><input type='submit' value='Usar' class='hidden'>
<input type='hidden' name='dump' value=''>
</p></form>
<p class='links'>
<a href='?server=&amp;username=admin_tesa&amp;db=edbank&amp;sql='>Comando SQL</a>
<a href='?server=&amp;username=admin_tesa&amp;db=edbank&amp;import='>Importar</a>
<a href='?server=&amp;username=admin_tesa&amp;db=edbank&amp;dump=' id='dump' class='active '>Exportar</a>
<a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;create=">Criar tabela</a>
<ul id='tables'><script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});</script>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=agencia" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=agencia" class='structure' title='Mostrar estrutura'>agencia</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=agencia_config" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=agencia_config" class='structure' title='Mostrar estrutura'>agencia_config</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=cartoes" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=cartoes" class='structure' title='Mostrar estrutura'>cartoes</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=clientes" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=clientes" class='structure' title='Mostrar estrutura'>clientes</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=config" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=config" class='structure' title='Mostrar estrutura'>config</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=config_tesa" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=config_tesa" class='structure' title='Mostrar estrutura'>config_tesa</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=investimentos" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=investimentos" class='structure' title='Mostrar estrutura'>investimentos</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=profissoes" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=profissoes" class='structure' title='Mostrar estrutura'>profissoes</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=sorte_reves" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=sorte_reves" class='structure' title='Mostrar estrutura'>sorte_reves</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=transacoes" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=transacoes" class='structure' title='Mostrar estrutura'>transacoes</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=user" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=user" class='structure' title='Mostrar estrutura'>user</a>
<li><a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;select=userinfo" class='select' title='Selecionar dados'>selecionar</a> <a href="?server=&amp;username=admin_tesa&amp;db=edbank&amp;table=userinfo" class='structure' title='Mostrar estrutura'>userinfo</a>
</ul>
</div>
<form action="" method="post">
<p class="logout">
<span>admin_tesa
</span>
<input type="submit" name="logout" value="Sair" id="logout">
<input type='hidden' name='token' value='932107:532717'>
</form>
</div>

<script nonce="YzU0ZmM3YjJkYTIzZWMzNWJhOGZmOTJkYjhmMzhmNzQ=">setupSubmitHighlight(document);</script>
