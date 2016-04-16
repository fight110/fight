
define(['jquery'], function(require, exports, module) {
	var $sql = $('textarea[name=sql]'), $name = $('input[name=name]');
	$('#select').on('change', function(){
		var key = this.value;
		$name.val($(this).find('option[value='+key+']').html());
		if(key) {
			$.get('/data/export_sql/', {key:key}, function(text){
				$sql.val(text);
			}, 'text');
		}
	});
});