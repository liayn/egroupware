<?php
/**
 * EGgroupware administration PHP FPM status page
 *
 * 1. The status page needs to be enabled in EGroupware's Nginx talking to PHP FPM (/etc/egroupware-docker/egroupware-nginx.conf):
 *
 *      location = /status {
 *          if ($http_referer !~ /admin/fpm-status.php$) {
 *              return 404;
 *          }
 *          include fastcgi_params;
 *          fastcgi_param SCRIPT_NAME /status;
 *          fastcgi_param SCRIPT_FILENAME /status;
 *          fastcgi_pass $egroupware:9000;
 *      }
 *
 * 2. The Nginx need to be restarted:
 *
 *      docker restart egroupware-nginx
 *
 * 3. FPM need to be configured to serve status under /status
 *
 *      docker exec -it egroupware bash
 *      cd /etc/php/*; sed -i cd fpm/pool.d/www.conf -e 's/^;pm.status_path/pm.status_path/'; exit
 *      docker restart egroupware
 *
 * Please note: the next EGroupware update will disable it again, and it's a good idea to do that even before!
 *
 * --> FPM status is available then under: https://example.org/egroupware/admin/fpm-status.php for logged in EGroupware admins.
 *
 * @link https://www.egroupware.org
 * @package admin
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

$GLOBALS['egw_info']['flags'] = array(
	'noheader'   => True,
	'nonavbar'   => True,
	'currentapp' => 'admin'
);
include('../header.inc.php');

if ($GLOBALS['egw']->acl->check('info_access',1,'admin'))
{
	$GLOBALS['egw']->redirect_link('/index.php');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
	(c) 2011 Jerome Loyet
	The PHP License, version 3.01
	This is sample real-time status page for FPM. You can change it to better fit your needs.
	
	Original from: https://github.com/php/php-src/blob/master/sapi/fpm/status.html.in
-->
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<style type="text/css">
			body {background-color: #ffffff; color: #000000;}
			body, td, th, h1, h2 {font-family: sans-serif;}
			pre {margin: 0px; font-family: monospace;}
			a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
			a:hover {text-decoration: underline;}
			table {border-collapse: collapse;}
			.center {text-align: center;}
			.center table { margin-left: auto; margin-right: auto; text-align: left;}
			.center th { text-align: center !important; }
			td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
			h1 {font-size: 150%;}
			h2 {font-size: 125%;}
			.p {text-align: left;}
			.e {background-color: #ccccff; font-weight: bold; color: #000000;}
			.h {background-color: #9999cc; font-weight: bold; color: #000000;}

			.v {background-color: #cccccc; color: #000000;}
			.w {background-color: #ccccff; color: #000000;}

			.h th {
				cursor: pointer;
			}
			img {float: right; border: 0px;}
			hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
		</style>
	<title>PHP-FPM status page</title>
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" /></head>
	<body>
		<div class="center">
			<table border="0" cellpadding="3" width="95%">
				<tr class="h">
					<td>
						<a href="http://www.php.net/"><img border="0" src="data:image/png;base64,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" alt="PHP Logo" /></a><h1 class="p">PHP-FPM real-time status page</h1>
					</td>
				</tr>
			</table>
			<br />
			<table border="0" cellpadding="3" width="95%">
				<tr><td class="e">Status URL</td><td class="v"><input type="text" id="url" size="45" /></td></tr>
				<tr><td class="e">Ajax status</td><td class="v" id="status"></td></tr>
				<tr><td class="e">Refresh Rate</td><td class="v"><input type="text" id="rate" value="1" /></td></tr>
				<tr>
					<td class="e">Actions</td>
					<td class="v">
						<button onclick="javascript:refresh();">Manual Refresh</button>
						<button id="play" onclick="javascript:playpause();">Play</button>
					</td>
				</tr>
			</table>
			<h1>Pool Status</h1>
			<table border="0" cellpadding="3" width="95%" id="short">
				<tr style="display: none;"><td>&nbsp;</td></tr>
			</table>
			<h1>Active Processes status</h1>
			<table border="0" cellpadding="3" width="95%" id="active">
				<tr class="h"><th>PID&darr;</th><th>Start Time</th><th>Start Since</th><th>Requests Served</th><th>Request Duration</th><th>Request method</th><th>Request URI</th><th>Content Length</th><th>User</th><th>Script</th></tr>
			</table>
			<h1>Idle Processes status</h1>
			<table border="0" cellpadding="3" width="95%" id="idle">
				<tr class="h"><th>PID&darr;</th><th>Start Time</th><th>Start Since</th><th>Requests Served</th><th>Request Duration</th><th>Request method</th><th>Request URI</th><th>Content Length</th><th>User</th><th>Script</th><th>Last Request %CPU</th><th>Last Request Memory</th></tr>
			</table>
		</div>
		<p>
			<a href="http://validator.w3.org/check?uri=referer">
				<img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
			</a>
		 </p>
		<script type="text/javascript">
<!--
			var xhr_object = null;
			var doc_url = document.getElementById("url");
			var doc_rate = document.getElementById("rate");
			var doc_status = document.getElementById("status");
			var doc_play = document.getElementById("play");
			var doc_short = document.getElementById("short");
			var doc_active = document.getElementById("active");
			var doc_idle = document.getElementById("idle");
			var rate = 0;
			var play=0;
			var delay = 1000;
			var order_active_index = 0;
			var order_active_reverse = 0;
			var order_idle_index = 0;
			var order_idle_reverse = 0;
			var sort_index;
			var sort_order;

			doc_url.value = location.protocol + '//' + location.host + "/status?json&full";

			ths = document.getElementsByTagName("th");
			for (var i=0; i<ths.length; i++) {
				var th = ths[i];
				if (th.parentNode.className == "h") {
					th.onclick = function() { order(this); return false; };
				}
			}

			xhr_object = create_ajax();

            // immediately start play / automatic refresh
            playpause();

			function create_ajax() {
				if (window.XMLHttpRequest) {
					return new XMLHttpRequest();
				}
				var names = [
					"Msxml2.XMLHTTP.6.0",
					"Msxml2.XMLHTTP.3.0",
					"Msxml2.XMLHTTP",
					"Microsoft.XMLHTTP"
				];
				for(var i in names)
				{
					try {
						return new ActiveXObject(names[i]);
					}	catch(e){}
				}
				alert("Browser not compatible ...");
			}

			function order(cell) {
				var table;

				if (cell.constructor != HTMLTableCellElement && cell.constructor != HTMLTableHeaderCellElement) {
					return;
				}

				table = cell.parentNode.parentNode.parentNode;

				if (table == doc_active) {
					if (order_active_index == cell.cellIndex) {
						if (order_active_reverse == 0) {
							cell.innerHTML = cell.innerHTML.replace(/.$/, "&uarr;");
							order_active_reverse = 1;
						} else {
							cell.innerHTML = cell.innerHTML.replace(/.$/, "&darr;");
							order_active_reverse = 0;
						}
					} else {
						var c = doc_active.rows[0].cells[order_active_index];
						c.innerHTML = c.innerHTML.replace(/.$/, "");
						cell.innerHTML = cell.innerHTML.replace(/$/, order_active_reverse == 0 ? "&darr;" : "&uarr;");
						order_active_index = cell.cellIndex;
					}
					reorder(table, order_active_index, order_active_reverse);
					return;
				}

				if (table == doc_idle) {
					if (order_idle_index == cell.cellIndex) {
						if (order_idle_reverse == 0) {
							cell.innerHTML = cell.innerHTML.replace(/.$/, "&uarr;");
							order_idle_reverse = 1;
						} else {
							cell.innerHTML = cell.innerHTML.replace(/.$/, "&darr;");
							order_idle_reverse = 0;
						}
					} else {
						var c = doc_idle.rows[0].cells[order_idle_index];
						c.innerHTML = c.innerHTML.replace(/.$/, "");
						cell.innerHTML = cell.innerHTML.replace(/$/, order_idle_reverse == 0 ? "&darr;" : "&uarr;");
						order_idle_index = cell.cellIndex;
					}
					reorder(table, order_idle_index, order_idle_reverse);
					return;
				}
			}

			function reorder(table, index, order) {
				var rows = [];
				while (table.rows.length > 1) {
					rows.push(table.rows[1]);
					table.deleteRow(1);
				}
				sort_index = index;
				sort_order = order;
				rows.sort(sort_table);
				for (var i in rows) {
					table.appendChild(rows[i]);
				}
				var odd = 1;
				for (var i=1; i<table.rows.length; i++) {
					table.rows[i].className = odd++ % 2 == 0 ? "v" : "w";
				}
				return;
			}

			function sort_table(a, b) {
				if (a.cells[0].tagName == "TH") return -1;
				if (b.cells[0].tagName == "TH") return 1;

				if (a.cells[sort_index].__search_t == 0) { /* integer */
					if (!sort_order) return a.cells[sort_index].__search_v - b.cells[sort_index].__search_v;
					return b.cells[sort_index].__search_v - a.cells[sort_index].__search_v;;
				}

				/* string */
				if (!sort_order) return a.cells[sort_index].__search_v.localeCompare(b.cells[sort_index].__search_v);
				else return b.cells[sort_index].__search_v.localeCompare(a.cells[sort_index].__search_v);
			}

			function playpause() {
				rate = 0;
				if (play) {
					play = 0;
					doc_play.innerHTML = "Play";
					doc_rate.disabled = false;
				} else {
					delay = parseInt(doc_rate.value);
					if (!delay || delay < 1) {
						doc_status.innerHTML = "Not valid 'refresh' value";
						return;
					}
					play = 1;
					doc_rate.disabled = true;
					doc_play.innerHTML = "Pause";
					setTimeout("callback()", delay * 1000);
				}
			}

			function refresh() {
				if (xhr_object == null) return;
				if (xhr_object.readyState > 0 && xhr_object.readyState < 4) {
					return; /* request is running */
				}
				xhr_object.open("GET", doc_url.value, true);
				xhr_object.onreadystatechange = function() {
					switch(xhr_object.readyState) {
						case 0:
							doc_status.innerHTML = "uninitialized";
							break;
						case 1:
							doc_status.innerHTML = "loading ...";
							break;
						case 2:
							doc_status.innerHTML = "loaded";
							break;
						case 3:
							doc_status.innerHTML = "interactive";
							break;
						case 4:
							doc_status.innerHTML = "complete";
							if (xhr_object.status == 200) {
								fpm_status(xhr_object.responseText);
							} else {
								doc_status.innerHTML = "Error " + xhr_object.status;
							}
							break;
					}
				}
				xhr_object.send();
			}

			function callback() {
				if (!play) return;
				refresh();
				setTimeout("callback()", delay * 1000);
			}

			function fpm_status(txt) {
				var json = null;

				while (doc_short.rows.length > 0) {
					doc_short.deleteRow(0);
				}

				while (doc_active.rows.length > 1) {
					doc_active.deleteRow(1);
				}

				while (doc_idle.rows.length > 1) {
					doc_idle.deleteRow(1);
				}

				try {
					json = JSON.parse(txt.replace(/\\/g,'\\\\'));   // backslashs NOT encoded by php-fpm giving invalid JSON
				} catch (e) {
					doc_status.innerHTML =  "Error while parsing json: '" + e + "': <br /><pre>" + txt + "</pre>";
					return;
				}

				for (var key in json) {
					if (key == "processes") continue;
					if (key == "state") continue;
					var row = doc_short.insertRow(doc_short.rows.length);
					var value = json[key];
					if (key == "start time") {
						value = new Date(value * 1000).toLocaleString();
					}
					if (key == "start since") {
						value = time_s(value);
					}
					var cell = row.insertCell(row.cells.length);
					cell.className = "e";
					cell.innerHTML = key;

					cell = row.insertCell(row.cells.length);
					cell.className = "v";
					cell.innerHTML = value;
				}

				if (json.processes) {
					process_full(json.processes, doc_active, "Idle", 0, 0);
					reorder(doc_active, order_active_index, order_active_reverse);

					process_full(json.processes, doc_idle, "Idle", 1, 1);
					reorder(doc_idle, order_idle_index, order_idle_reverse);
				}
			}

			function process_full(processes, table, state, equal, cpumem) {
				var odd = 1;

				for (var i in processes) {
					var proc = processes[i];
                    if (proc['request uri'].match(/^\/status(\?|$)/)) continue;     // suppress own probes
                    if ((equal && proc.state == state) || (!equal && proc.state != state)) {
						var c = odd++ % 2 == 0 ? "v" : "w";
						var row = table.insertRow(-1);
						row.className = c;
						row.insertCell(-1).innerHTML = proc.pid;
						row.cells[row.cells.length - 1].__search_v = proc.pid;
						row.cells[row.cells.length - 1].__search_t = 0;

						row.insertCell(-1).innerHTML = date(proc['start time'] * 1000);;
						row.cells[row.cells.length - 1].__search_v = proc['start time'];
						row.cells[row.cells.length - 1].__search_t = 0;

						row.insertCell(-1).innerHTML = time_s(proc['start since']);
						row.cells[row.cells.length - 1].__search_v = proc['start since'];
						row.cells[row.cells.length - 1].__search_t = 0;

						row.insertCell(-1).innerHTML = proc.requests;
						row.cells[row.cells.length - 1].__search_v = proc.requests;
						row.cells[row.cells.length - 1].__search_t = 0;

						row.insertCell(-1).innerHTML = time_u(proc['request duration']);
						row.cells[row.cells.length - 1].__search_v = proc['request duration'];
						row.cells[row.cells.length - 1].__search_t = 0;

						row.insertCell(-1).innerHTML = proc['request method'];
						row.cells[row.cells.length - 1].__search_v = proc['request method'];
						row.cells[row.cells.length - 1].__search_t = 1;

						row.insertCell(-1).innerHTML = proc['request uri'];
						row.cells[row.cells.length - 1].__search_v = proc['request uri'];
						row.cells[row.cells.length - 1].__search_t = 1;

						row.insertCell(-1).innerHTML = proc['content length'];
						row.cells[row.cells.length - 1].__search_v = proc['content length'];
						row.cells[row.cells.length - 1].__search_t = 0;

						row.insertCell(-1).innerHTML = proc.user;
						row.cells[row.cells.length - 1].__search_v = proc.user;
						row.cells[row.cells.length - 1].__search_t = 1;

						row.insertCell(-1).innerHTML = proc.script;
						row.cells[row.cells.length - 1].__search_v = proc.script;
						row.cells[row.cells.length - 1].__search_t = 1;

						if (cpumem) {
							row.insertCell(-1).innerHTML = cpu(proc['last request cpu']);
							row.cells[row.cells.length - 1].__search_v = proc['last request cpu'];
							row.cells[row.cells.length - 1].__search_t = 0;

							row.insertCell(-1).innerHTML = memory(proc['last request memory']);
							row.cells[row.cells.length - 1].__search_v = proc['last request memory'];
							row.cells[row.cells.length - 1].__search_t = 0;
						}
					}
				}
			}

			function date(d) {
				var t = new Date(d);
				var r = "";

				r += (t.getDate() < 10 ? '0' : '') + t.getDate();
				r += '/';
				r += (t.getMonth() + 1 < 10 ? '0' : '') + (t.getMonth() + 1);
				r += '/';
				r += t.getFullYear();
				r += ' ';
				r += (t.getHours() < 10 ? '0' : '') + t.getHours();
				r += ':';
				r += (t.getMinutes() < 10 ? '0' : '') + t.getMinutes();
				r += ':';
				r += (t.getSeconds() < 10 ? '0' : '') + t.getSeconds();


				return r;
			}

			function cpu(c) {
				if (c == 0) return 0;
				return c + "%";
			}

			function memory(mem) {
				if (mem == 0) return 0;
				if (mem < 1024) {
					return mem + "B";
				}
				if (mem < 1024 * 1024) {
					return mem/1024 + "KB";
				}
				if (mem < 1024*1024*1024) {
					return mem/1024/1024 + "MB";
				}
			}

			function time_s(t) {
				var r = "";
				if (t < 60) {
					return t + 's';
				}

				r = (t % 60) + 's';
				t = Math.floor(t / 60);
				if (t < 60) {
					return t + 'm ' + r;
				}

				r = (t % 60) + 'm ' + r;
				t = Math.floor(t/60);

				if (t < 24) {
					return t + 'h ' + r;
				}

				return Math.floor(t/24) + 'd ' + (t % 24) + 'h ' + t;
			}

			function time_u(t) {
				var r = "";
				if (t < 1000) {
					return t + '&micro;s'
				}

				r = (t % 1000) + '&micro;s';
				t = Math.floor(t / 1000);
				if (t < 1000) {
					return t + 'ms ' + r;
				}

				return time_s(Math.floor(t/1000)) + ' ' + (t%1000) + 'ms ' + r;
			}

-->
		</script>
	</body>
</html>