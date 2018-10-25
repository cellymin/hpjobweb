<?php if(!defined("PATH_LC"))exit;?>
<?php if(is_array($menu_data)):?><?php  foreach($menu_data as $menu){ ?>
<li>
<span><a href=""><?php echo $menu['menu_name'];?></a></span>
<ul class="son">
	<?php if(is_array($menu['three_menu'])):?><?php  foreach($menu['three_menu'] as $three_menu){ ?>
	<li><a href="http://localhost/hpjobweb/index.php/<?php echo $three_menu['app'];?>/<?php echo $three_menu['control'];?>/<?php echo $three_menu['method'];?><?php echo $three_menu['query_string'];?>" target="opt"><?php echo $three_menu['menu_name'];?></a></li>
	<?php }?><?php endif;?>
</ul>
</li>
<li>
<?php }?><?php endif;?>
<script type="text/javascript">
	$('#menu .son a').hover(function(){
        $(this).addClass('son-hover');
      },function(){
        $(this).removeClass('son-hover');
      });
</script>