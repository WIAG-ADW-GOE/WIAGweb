<?php

namespace App\Service;

use App\Entity\Canon;
use App\Entity\CanonGS;
use App\Entity\CnOffice;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Place;


use App\Entity\Person;
use App\Service\ParseDates;
use Doctrine\ORM\EntityManagerInterface;


class CnOfficeService {
    private $parsedates;
    private $em;

    public function __construct(ParseDates $parsedates,
                                EntityManagerInterface $em) {
        $this->parsedates = $parsedates;
        $this->em = $em;
    }

    public function fillNumdates(CnOffice $office) {
        $pd = $this->parsedates;
        $numdatestart = $pd->parse($office->getDateStart(), 'lower');
        $office->setNumdateStart($numdatestart);
        $numdateend = $pd->parse($office->getDateEnd(), 'upper');
        $office->setNumdateEnd($numdateend);
    }

    public function fillLocationShow(CnOffice $office): ?string {
        $office->setLocationShow(null);

        # use values of `location` or `diocese` if present
        $location_show = $office->getLocation();
        if (!is_null($location_show)) {
            $office->setLocationShow($location_show);
            return $location_show;
        }
        $location_show = $office->getDiocese();
        if (!is_null($location_show)) {
            $office->setLocationShow($location_show);
            return $location_show;
        }

        # use location of `monastery`
        $monastery = $office->getMonastery();

        if (is_null($monastery)) {
            return null;
        } else {
            $id_monastery = $monastery->getWiagid();
        }

        # use `location_name` in `monastery_location` if present
        $cml = $this->em->getRepository(Monasterylocation::class)
                        ->findByWiagidMonastery($id_monastery);

        if (count($cml) == 1) {
            $location_show = $this->findLocationName($cml[0]);
            $office->setLocationShow($location_show);
            return $location_show;
        }
        # filter by time
        $datestart = $office->getNumdateStart();
        $dateend = $office->getNumdateEnd();
        if (count($cml) > 1) {
            $ml = array_filter($cml,
                                     function($mli) use ($datestart, $dateend) {
                                         return $this->filterLocByDate($mli, $datestart, $dateend);
                                     });
            if (count($ml) > 0) {
                $location_show = $this->findLocationName($ml[0]);
                $office->setLocationShow($location_show);
            }
            return $location_show;
        }

        return null;

    }

    /**
     * aux
     */
    public function findLocationName(Monasterylocation $ml) {
        $location_name = $ml->getLocationName();
        if (!is_null($location_name)) {
            return $location_name;
        }

        $place = $this->em->getRepository(Place::class)
                          ->findOneByIdPlaces($ml->getPlaceId());


        if (is_null($place)) {
            return null;
        }

        return $place->getPlaceName();

    }

    /**
     * aux
     */
    public function filterLocByDate($ml, $officestart, $officeend) {
        $locstart = $this->parsedates->parse($ml->getLocationBeginTpq(), 'lower');
        if (!is_null($locstart) && !is_null($officeend) && $locstart > $officeend) {
            return false;
        }

        $locend = $this->parsedates->parse($ml->getLocationEndTpq(), 'upper');
        if (!is_null($locend) && !is_null($officestart) && $locend < $officestart) {
            return false;
        }

        return true;
    }

    public function fillMonastery($office, $monastery_name) {
        if (!is_null($monastery_name) && $monastery_name != "") {
            $repository = $this->em->getRepository(Monastery::class);
            $monastery = $repository->findOneByName($monastery_name);
            $office->setMonastery($monastery);
        }
        return $office;
    }

};
