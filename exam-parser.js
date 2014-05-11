function add_notice_title_action(){
	title = document.getElementById("notice-title");
	content = document.getElementById("notice-content");
	title.innerHTML = '须知(查看) ↓↓'
	content.style.display = 'none';
	title.onclick = function(){
		style = content.style.display;
		if(style == 'none'){
			content.style.display = 'block';
			title.innerHTML = '须知(收起) ↑↑'
		}else{
			content.style.display = 'none';
			title.innerHTML = '须知(查看) ↓↓'
		}
	}
}
window.onload=add_notice_title_action;
