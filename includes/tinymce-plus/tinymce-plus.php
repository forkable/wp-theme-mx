<?php
/*
Feature Name:	theme_tinymce_plus
Feature URI:	http://www.inn-studio.com
Version:		1.0.4
Description:	add more futures for wp editor
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
/**
 * 加粗（bold）、斜体（italic）、下划线（underline）、删除线（strikethrough）、左对齐（justifyleft）、居中（justifycenter）、右对齐（justfyright）、两端对齐（justfyfull）、无序列表（bullist）、编号列表（numlist）、减少缩进（outdent）、缩进（indent）、剪切（cut）、复制（copy）、粘贴（paste）、撤销（undo）、重做（redo）、插入超链接（link）、取消超链接（unlink）、插入图片（image）、清除格式（removeformat）、帮助（wp_help）、打开HTML代码编辑器（code）、水平线（hr）、清除冗余代码（cleanup）、格式选择（formmatselect）、字体选择（fontselect）、字号选择（fontsizeselect）、样式选择（styleselect）、上标（sub）、下标（sup）、字体颜色（forecolor）、字体背景色（backcolor）、特殊符号（charmap）、隐藏按钮显示开关（wp_adv）、隐藏按钮区起始部分（wp_adv_start）、隐藏按钮区结束部分（wp_adv_end）、锚文本（anchor）、新建文本（类似于清空文本）（newdocument）、插入more标签（wp_more）、插入分页标签（wp_page）、拼写检查（spellchecker）。
 */
add_filter("mce_buttons_2", "theme_tinymce_plus::tmce");
add_action('after_wp_tiny_mce', 'theme_tinymce_plus::html');

class theme_tinymce_plus{
	public static $iden = 'theme_tinymce_plus';

	public static function tmce($buttons){
		$buttons[] = 'anchor';   
		$buttons[] = 'sub';   
		$buttons[] = 'sup';   
		$buttons[] = 'fontsizeselect';   
   		$buttons[] = 'wp_page';
		return $buttons;
	}
	public static function html(){
		
		?>
	    <script>
		QTags.addButton('nextpage','nextpage',bolo_QTnextpage_arg1,'','n','<?= ___('Next Page');?>', 121);
		function bolo_QTnextpage_arg1(){
			QTags.insertContent('<!--nextpage-->');
		}
	    </script>
	    <?php
	}
}
?>