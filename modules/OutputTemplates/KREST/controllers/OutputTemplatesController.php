<?php
/**
 * Created by PhpStorm.
 * User: maretval
 * Date: 07.05.2019
 * Time: 13:20
 */
namespace SpiceCRM\modules\OutputTemplates\KREST\controllers;

class OutputTemplatesController
{

    public function compile($req, $res, $args)
    {
        $bean = \BeanFactory::getBean('OutputTemplates', $args['id']);
        $bean->bean_id = $args['bean_id'];
        return json_encode(['content' => $bean->translateBody()]);

    }

    public function convertToFormat($req, $res, $args){
        $bean = \BeanFactory::getBean('OutputTemplates', $args['id']);
        $bean->bean_id = $args['bean_id'];
        $file = $bean->getPdfContent();
        return $res->withHeader('Content-Type', 'application/pdf')->write($file);
    }

    public function convertToBase64($req, $res, $args){
        $bean = \BeanFactory::getBean('OutputTemplates', $args['id']);
        $bean->bean_id = $args['bean_id'];
        $file = $bean->getPdfContent();
        return json_encode(['content' => base64_encode($file)]);
    }
}