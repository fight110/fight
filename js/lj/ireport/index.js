define(['jquery', 'app/pager', 'app/lazy'], function (require, exports, module) {
	require.async(['jquery/jquery.ui', 'jquery/jquery.tmpl'], function () {
		$(function () {
			$('.ireport').on('click', 'ul.tab li', function () {
				$('.ireport ul.tab li, .ireport .tab-content span').removeClass('active');
				$(this).addClass('active');
				var group = $(this).data('group');
				$('.ireport .tab-content span.group-' + group).addClass('active');
			});

			$('#ireport-search')
				.on('submit', function (e) {
					e.preventDefault();
				})
				.on('click', 'button.print', function () {
					if (!$('.mainbox').hasClass('menuhide')) {
						$('#mini_menu').trigger('click');
					}
					window.print();
				})
			;
		});

		// 维度拖放处理
		$("ul.display-key").droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			//accept: ":not(.ui-sortable-helper)",
			drop: function (event, ui) {
				// 判断是否拖放到自己原来的框子里面了，是的话就不处理，交给sortable处理
				if (!ui.draggable.parent().is(this)) {
					$(this).find(".placeholder").remove();
					$('<li data-keyword-id="' + ui.draggable.data('keyword-id') + '"></li>').html(ui.draggable.html()).appendTo(this);
					ui.draggable.remove();
				}
			}
		}).sortable({
			revert: true,
			items: "li:not(.placeholder)",
			sort: function () {
				$(this).removeClass("ui-state-default");
			}
		});

		$("ul.display-key li").draggable({
			appendTo: "body",
			revert: true
		});

		for (var i = 0; i < 3; i++) {
			$last = $("ul.display-key.available li").last();
			$('<li data-keyword-id="' + $last.data('keyword-id') + '"></li>').html($last.html()).prependTo("ul.display-key.displayed");
			$last.remove();
		}


		// 获取表单提交的查询字符串
		var getReportQueryString = function () {
			$form = $("#ireport-form");
			var keyword_ids = [];
			$form.find("ul.display-key.displayed>li").each(function () {
				keyword_ids.push($(this).data('keyword-id'));
			});

			var query_string = $form.serialize();
			var keyword_ids_string = $.map(keyword_ids, function (keyword_id) {
				return 'keyword_ids%5B%5D=' + keyword_id;
			}).join('&');
			return [query_string, keyword_ids_string].join('&');
		};

		var lazy = require('app/lazy');
		var pager = require('app/pager');
		var api = null;
		var lz = new lazy('.foot', function () {
			api.next()
		}, {delay: 200, top: 200});
		api = new pager();

		$("#ireport-form").on('submit', function (e) {
			e.preventDefault();
			//$.ajax({
			//	url: '/ireport/report',
			//	type: "POST",
			//	data: getReportQueryString(),
			//	success: function (msg) {
			//		$('#report-table').html(msg);
			//		//console.log(msg)
			//	}
			//});

			$('#report-table table.table01').html('');

			var data = {};
			data['q'] = getReportQueryString();
			api.reset('/ireport/report_pager', data, {
				autorun: true,
				id: '#report-table table.table01'
			});

		}).on('click', 'button.export', function (e) {
			e.preventDefault();
			var url = '/ireport/export?' + getReportQueryString();
			window.location = url;
			//$('#report-download').attr('href', url);
			//// 触发浏览器默认点击事件
			//var download_link = document.getElementById('report-download');
			//if (document.all) {
			//	// For IE
			//	download_link.click();
			//} else if (document.createEvent) {
			//	//FOR DOM2
			//	var ev = document.createEvent('MouseEvents');
			//	ev.initEvent('click', false, true);
			//	download_link.dispatchEvent(ev);
			//}
		});

	});
});