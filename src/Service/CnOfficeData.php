<?php

namespace App\Service;

use App\Entity\CnOffice;

class CnOfficeData {

    static public function toArray(CnOffice $oc) {
        $ocj = array();

        $ocj['officeTitle'] = $oc->getOfficeName();

        $fv = $oc->getDiocese();
        if($fv) $ocj['diocese'] = $fv;

        $fv = $oc->getMonastery();
        if($fv) {
            $ocj['monasteryName'] = $fv->getMonasteryName();
            $ocj['monasteryGsnId'] = $fv->getWiagid();
        }

        $fv = $oc->getDateStart();
        if($fv) $ocj['dateStart'] = $fv;

        $fv = $oc->getDateEnd();
        if($fv) $ocj['dateEnd'] = $fv;

        $fv = $oc->getComment();
        if($fv) $ocj['comment'] = $fv;

        $fv = $oc->getSortKey();
        if($fv) $ocj['sort'] = $fv;
        
        return $ocj;
    }
}

