<?php

function ps_remove_controller_tab($className, $quickAccessUrls = [])
{
    // select given tab
    $tabsToBeDeleted = [];
    $rolesToBeDeleted = [];
    $tabToDelete = Db::getInstance()->getRow(
        sprintf("SELECT id_tab, id_parent, class_name FROM %stab WHERE class_name = '%s'", _DB_PREFIX_, $className)
    );

    if (empty($tabToDelete)) {
        return;
    }

    // get tabs and roles that should be deleted
    getElementsToBeDeleted($tabToDelete['id_tab'], $tabToDelete['id_parent'], $className, $tabsToBeDeleted, $rolesToBeDeleted);

    // delete tabs fetched by the recursive function
    Db::getInstance()->execute(
        sprintf(
            'DELETE FROM %stab WHERE id_tab IN (%s)',
            _DB_PREFIX_,
            implode(', ', $tabsToBeDeleted)
        )
    );

    // delete orphan tab langs
    Db::getInstance()->execute(
        sprintf(
            'DELETE FROM `%stab_lang` WHERE `id_tab` NOT IN (SELECT id_tab FROM `%stab`)',
            _DB_PREFIX_,
            _DB_PREFIX_
        )
    );

    // delete orphan legacy quick access links
    $sqlLegacyQuickAccessLinkDeletion = sprintf(
        "DELETE FROM `%squick_access_lang`
        WHERE id_quick_access IN (SELECT id_quick_access FROM `%squick_access` WHERE link LIKE '%%controller=%s%%')",
        _DB_PREFIX_,
        _DB_PREFIX_,
        $className
    );

    Db::getInstance()->execute($sqlLegacyQuickAccessLinkDeletion);
    Db::getInstance()->execute(
        sprintf(
            "DELETE FROM `%squick_access` WHERE link LIKE '%%controller=%s%%'",
            _DB_PREFIX_,
            $className
        )
    );

    if (!empty($quickAccessUrls)) {
        // delete orphan quick access links (given links, for symfony urls)
        foreach ($quickAccessUrls as &$link) {
            $link = "'" . $link . "'";
        }
        Db::getInstance()->execute(
            sprintf(
                'DELETE FROM %squick_access WHERE link IN (%s)',
                _DB_PREFIX_,
                implode(', ', $quickAccessUrls)
            )
        );
    }

    // delete orphan roles
    $sqlRoleDeletion = sprintf('DELETE FROM %sauthorization_role WHERE ', _DB_PREFIX_);
    foreach ($rolesToBeDeleted as $key => $role) {
        if ($key === 0) {
            $sqlRoleDeletion .= "slug LIKE '" . $role . "'";
            continue;
        }
        $sqlRoleDeletion .= "OR slug LIKE '" . $role . "'";
    }
    Db::getInstance()->execute($sqlRoleDeletion);
}

function getElementsToBeDeleted($idTab, $idParent, $className, &$tabsToBeDeleted, &$rolesToBeDeleted)
{
    // add current tab to tabs that will be deleted
    $tabsToBeDeleted[] = $idTab;
    $rolesToBeDeleted[] = sprintf('ROLE_MOD_TAB_%s%%', strtoupper($className));
    if (empty($idParent)) {
        return;
    }

    // check if parent has any other children
    $sibling = Db::getInstance()->getRow(
        sprintf(
            'SELECT id_tab FROM %stab WHERE id_parent = ' . $idParent . ' AND id_tab NOT IN (%s)',
            _DB_PREFIX_,
            implode(', ', $tabsToBeDeleted)
        )
    );

    // tab has at least one sibling, we stop here
    if (!empty($sibling)) {
        return;
    }

    // no sibling, get parent and repeat the process recursively
    $parentTab = Db::getInstance()->getRow(
        sprintf('SELECT id_tab, id_parent, class_name FROM %stab WHERE id_tab = %s', _DB_PREFIX_, $idParent)
    );

    // this is just in case, it should never happen, if a tab has an id_parent, parent should exist
    if (empty($parentTab)) {
        return;
    }

    getElementsToBeDeleted($parentTab['id_tab'], $parentTab['id_parent'], $parentTab['class_name'], $tabsToBeDeleted, $rolesToBeDeleted);
}
