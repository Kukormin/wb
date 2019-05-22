<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сформировать поставку");

$stores = \Local\Main\Stores::getAll();
$collections = \Local\Main\Collections::getAll();
$sections = \Local\Main\Sections::getAll();

?>
<div class="container">
    <form method="get" action="detail.php">
        <div style="margin:30px 0 0;">
            <h3>Склад</h3>
            <?

                foreach ($stores['ITEMS'] as $store)
                {
                    if ($store['ID'] == \Local\Main\Stores::PODOLSK_ID)
                    {
                        ?>
                        <label><input type="radio" name="store" value="-1" required /> <?= $store['NAME'] ?> (общий дефицит)</label><br /><?
                    }

                    ?>
                    <label><input type="radio" name="store" value="<?= $store['ID'] ?>" required /> <?= $store['NAME'] ?></label><br /><?
                }

                ?>
        </div>
        <div>
            <h3>Вид поставки</h3>
            <label><input type="radio" name="k" value="1" required checked /> Микс</label><br />
            <label><input type="radio" name="k" value="2" required /> Моно</label><br />
            <label><input type="radio" name="k" value="3" required /> Моно+микс</label>
        </div>
		<div>
			<h3>Опции</h3>
			<label><input type="checkbox" name="igd" value="1" checked /> Игнорировать дефицит</label><br />
		</div>
        <div>
            <h3>Фильтр по категориям (можно выбрать несколько - Ctrl)</h3>
            <select name="section[]" multiple style="height:140px;">
            <?

            foreach ($sections['ITEMS'] as $section)
            {
                ?>
                <option value="<?= $section['ID'] ?>"><?= $section['NAME'] ?></option><?
            }

            ?>
            </select>
        </div>
        <div>
            <h3>Фильтр по коллекциям (можно выбрать несколько - Ctrl)</h3>
            <select name="collection[]" multiple style="height:140px;">
                <?

                foreach ($collections['ITEMS'] as $collection)
                {
                    ?>
                    <option value="<?= $collection['ID'] ?>"><?= $collection['NAME'] ?></option><?
                }

                ?>
            </select>
        </div>
        <div style="margin:20px 0;">
            <input type="submit" value="Предварительная таблица">
        </div>
    </form>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>