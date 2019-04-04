<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$sections = \Local\Main\Sections::getAll();

    ?>
    <ul><?

        foreach ($sections['ITEMS'] as $section)
        {
            ?>
            <li><a href="/cat/<?= $section['ID'] ?>/"><?= $section['NAME'] ?></a></li><?
        }

        ?>
    </ul>