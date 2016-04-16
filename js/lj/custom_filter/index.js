define(['jquery', 'app/pager', 'app/lazy'], function (require, exports, module) {
	require.async(['jquery/jquery.ui', 'jquery/jquery.tmpl'], function () {
		$(function () {
			$('.custom_filter').on('click', 'ul.tab li', function () {
				$('.custom_filter ul.tab li, .custom_filter .tab-content span').removeClass('active');
				$(this).addClass('active');
				var group = $(this).data('group');
				$('.custom_filter .tab-content span.group-' + group).addClass('active');
			});

			$('#custom_filter-search')
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
				// 最大允许九个筛选字段
				if ($(this).is('.displayed') && $(this).find('li').size() >= 9) {
					alert('最多允许使用9个筛选字段！');
					return false;
				}
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

		$.getJSON('/custom_filter/current', function (keyword_ids) {
			if (!keyword_ids) {

				$.getJSON('/custom_filter/default', function (default_keyword_ids) {
					if (!default_keyword_ids) {
						return false;
					}
					$.each(default_keyword_ids, function (i, keyword_id) {
						var $selected = $("ul.display-key.available li[data-keyword-id=" + keyword_id + "]").last();
						if ($selected.length) {
							$('<li data-keyword-id="' + $selected.data('keyword-id') + '"></li>').html($selected.html()).appendTo("ul.display-key.displayed");
							$selected.remove();
						}
					});
				});

				return false;
			}
			$.each(keyword_ids, function (i, keyword_id) {
				var $selected = $("ul.display-key.available li[data-keyword-id=" + keyword_id + "]").last();
				if ($selected.length) {
					$('<li data-keyword-id="' + $selected.data('keyword-id') + '"></li>').html($selected.html()).appendTo("ul.display-key.displayed");
					$selected.remove();
				}
			});
		});


		// 获取表单提交的查询字符串
		var getReportQueryString = function () {
			var $form = $("#custom_filter-form");
			var keyword_ids = [];
			$form.find("ul.display-key.displayed>li").each(function () {
				keyword_ids.push($(this).data('keyword-id'));
			});

			return $.map(keyword_ids, function (keyword_id) {
				return 'keyword_ids%5B%5D=' + keyword_id;
			}).join('&');
		};

		$("#custom_filter-form").on('submit', function (e) {
			e.preventDefault();
			$.ajax({
				url: '/custom_filter',
				type: "POST",
				data: getReportQueryString(),
				success: function (msg) {
					//$('#report-table').html(msg);
					alert(msg);
				}
			});

		});

	});
});