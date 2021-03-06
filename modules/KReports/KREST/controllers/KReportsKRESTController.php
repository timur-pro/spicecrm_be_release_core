<?php

namespace SpiceCRM\modules\KReports\KREST\controllers;

use KREST\ForbiddenException;

class KReportsKRESTController
{
    /**
     * @param $req
     * @param $res
     * @param $args
     * @return array
     * @throws ForbiddenException
     */
    public function getPublishedKReports($req, $res, $args) {
        if (!$GLOBALS['ACLController']->checkAccess('KReports', 'list', true))
            throw (new KREST\ForbiddenException("Forbidden to list in module KReports."))->setErrorCode('noModuleList');
        global $db;
        $list = [];
        $type = $db->quote($args['type']);
        $searchKey = $_GET['searchKey'] ? $db->quote($_GET['searchKey']) : '';
        $offset = $_GET['offset'] ? $db->quote($_GET['offset']) : 0;
        $limit = $_GET['limit'] ? $db->quote($_GET['limit']) : 40;
        $where = "integration_params LIKE '%\"$type\":\"on\"%' AND (integration_params LIKE '%\"kpublishing\":1%' OR integration_params LIKE '%\"kpublishing\":\"1\"%')";
        if ($searchKey != '') {
            $where .= " AND name LIKE '%$searchKey%'";
        }
        $query = "SELECT id, name, description, report_module, integration_params FROM kreports WHERE $where LIMIT $limit OFFSET $offset";
        $query = $db->query($query);
        while ($row = $db->fetchByAssoc($query)) $list[] = $row;
        return $res->withJson($list);
    }
}
