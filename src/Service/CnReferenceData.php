<?php

namespace App\Service;

use App\Entity\CnReference;

class CnReferenceData {

    static public function toArray(CnReference $ref): array {
        $rfj = array();

        $rfj['title'] = $ref->getTitle();

        $fv = $ref->getAuthor();
        if($fv) $rfj['author'] = $fv;

        $fv = $ref->getOnlineResource();
        if($fv) $rfj['online'] = $fv;

        $fv = $ref->getShortTitle();
        if($fv) $rfj['shortTitle'] = $fv;

        return $rfj;

    }
}

