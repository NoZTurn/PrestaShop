{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if isset($tab_modules_list) && !empty($tab_modules_list)}
	<div class="row row-margin-bottom">
		<div class="col-lg-12">
			<ul class="nav nav-pills">
				{if count($tab_modules_list.not_installed)}
					<li class="active">
						<a href="#tab_modules_list_not_installed" data-toggle="tab">
							{l s='Not Installed'}
						</a>
					</li>
				{/if}
				{if count($tab_modules_list.installed)}
					<li {if count($tab_modules_list.not_installed) == 0}class="active"{/if}>
						<a href="#tab_modules_list_installed" data-toggle="tab">
							{l s='Installed'}
						</a>
					</li>
				{/if}
			</ul>
		</div>
	</div>
	<div id="modules_list_container_content" class="tab-content">
		{if count($tab_modules_list.not_installed)}
		<div class="tab-pane active" id="tab_modules_list_not_installed">
			<table id="tab_modules_list_not_installed" class="table">
				{foreach from=$tab_modules_list.not_installed item=module}
					{include file='controllers/modules/tab_module_line.tpl' class_row={cycle values=",rowalt"}}
				{/foreach}
			</table>
		</div>
		{/if}
		{if count($tab_modules_list.installed)}
		<div class="tab-pane {if count($tab_modules_list.not_installed) == 0}active{/if}" id="tab_modules_list_installed">
			<table id="tab_modules_list_installed" class="table">
				{foreach from=$tab_modules_list.installed item=module}
					{include file='controllers/modules/tab_module_line.tpl' class_row={cycle values=",rowalt"}}
				{/foreach}
			</table>
		</div>
		{/if}
	</div>
{/if}
<div class="alert alert-addons row-margin-top">
	<a href="http://addons.prestashop.com/?utm_source=backoffice_dispatch" target="_blank">{l s='Visit PrestaShop Addons for more modules.'}</a>
</div>
