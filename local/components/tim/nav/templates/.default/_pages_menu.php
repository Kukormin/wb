<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $pages */
/** @var string $page */
/** @var string $baseTitle */

    ?>
    <ul><?

        foreach ($pages as $p => $name)
        {
            if ($p == $page)
            {
                ?>
                <li><b><?= $name ?></b></li><?

                if ($p)
                {
                    $APPLICATION->SetTitle($baseTitle . ' - ' . $name);
                    $APPLICATION->AddChainItem($name);
                }
            }
            else
            {
                if ($p)
                    $href = '?p=' . $p;
                else
                    $href = $APPLICATION->GetCurDir();
                ?>
                <li><a href="<?= $href ?>"><?= $name ?></a></li><?
            }
        }

        ?>
    </ul><?

$file = __DIR__ . '/' . $arParams['PAGE'] . '-' . $page . '.php';
if (file_exists($file))
{
    /** @noinspection PhpIncludeInspection */
    include($file);
}