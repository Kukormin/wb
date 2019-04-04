<?
namespace Components;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

class Nav extends \CBitrixComponent
{
	public function executeComponent()
	{
		$this->IncludeComponentTemplate();
	}

}