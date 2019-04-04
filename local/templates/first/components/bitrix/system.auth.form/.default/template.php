<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

?>
<div class="container">

<?
if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR'])
	ShowMessage($arResult['ERROR_MESSAGE']);
?>

<?if($arResult["FORM_TYPE"] == "login"):?>

<form name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
<?if($arResult["BACKURL"] <> ''):?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?endif?>
<?foreach ($arResult["POST"] as $key => $value):?>
	<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
<?endforeach?>
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="AUTH" />
	<p>
		<label for="USER_LOGIN">Логин:</label><br />
		<input type="text" name="USER_LOGIN" id="USER_LOGIN" maxlength="50" value="" size="17" />
	</p>
	<p>
		<label for="USER_PASSWORD">Пароль:</label><br />
		<input type="password" name="USER_PASSWORD" id="USER_PASSWORD" maxlength="50" size="17" autocomplete="off" />
	</p>
<?if ($arResult["STORE_PASSWORD"] == "Y"):?>
	<p>
		<label><input type="checkbox" id="USER_REMEMBER_frm" name="USER_REMEMBER" value="Y" /> Запомнить меня</label>
	</p>
<?endif?>
		<tr>
			<td colspan="2"><input type="submit" name="Login" value="Войти" /></td>
		</tr>

	</table>
</form>

<?endif?>
</div>
