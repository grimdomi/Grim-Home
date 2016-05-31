<?php /* Smarty version 2.6.19, created on 2016-05-24 18:49:44
         compiled from tabsmenue-admin.tpl */ ?>
<div class="tabswrapper">
	<ul class="tabs">
		<li class="projects"><a <?php if ($this->_tpl_vars['projecttab'] == 'active'): ?>class="active"<?php endif; ?> href="admin.php?action=projects"><span><?php echo $this->_config[0]['vars']['projectadministration']; ?>
</span></a></li>
		<li class="user"><a <?php if ($this->_tpl_vars['usertab'] == 'active'): ?>class="active"<?php endif; ?> href="admin.php?action=users"><span><?php echo $this->_config[0]['vars']['useradministration']; ?>
</span></a></li>
		<li class="system-settings"><a <?php if ($this->_tpl_vars['settingstab'] == 'active'): ?>class="active"<?php endif; ?> href="admin.php?action=system"><span><?php echo $this->_config[0]['vars']['systemadministration']; ?>
</span></a></li>
	</ul>
</div>