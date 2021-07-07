<?php

namespace App\Service;


class ParseDates {

    # see: https://www.php.net/manual/de/reference.pcre.pattern.syntax.php

    # parse time data
    const RGPCENTURY = "([1-9][0-9]?)\\. (Jahrh|Jh)";
    const RGPYEAR = "([1-9][0-9][0-9]+)";
    const RGPYEARFC = "([1-9][0-9]+)";

    # turn of the century
    const RGXTCENTURY = "~([1-9][0-9]?)\./".self::RGPCENTURY."~i";

    # quarter
    const RGX1QCENTURY = "~(1\.|erstes) Viertel +(des )?".self::RGPCENTURY."~i";
    const RGX2QCENTURY = "~(2\.|zweites) Viertel +(des )?".self::RGPCENTURY."~i";
    const RGX3QCENTURY = "~(3\.|drittes) Viertel +(des )?".self::RGPCENTURY."~i";
    const RGX4QCENTURY = "~(4\.|viertes) Viertel +(des )?".self::RGPCENTURY."~i";

    # begin, middle end
    const RGX1TCENTURY = "~Anfang (des )?".self::RGPCENTURY."~i";
    const RGX2TCENTURY = "~Mitte (des )?".self::RGPCENTURY."~i";
    const RGX3TCENTURY = "~Ende (des )?".self::RGPCENTURY."~i";

    # half
    const RGX1HCENTURY = "~(1\.|erste) Hälfte +(des )?".self::RGPCENTURY."~i";
    const RGX2HCENTURY = "~(2\.|zweite) Hälfte +(des )?".self::RGPCENTURY."~i";

    # between
    const RGXBETWEEN = "~zwischen ".self::RGPYEAR." und ".self::RGPYEAR."~i";

    # early, late
    const RGXEARLYCENTURY = "~frühes ".self::RGPCENTURY."~i";
    const RGXLATECENTURY = "~spätes ".self::RGPCENTURY."~i";

    # around, before, after
    # month is ignored
    const RGPMONTH = "(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember|Jan\.|Feb\.|Mrz\.|Apr\.|Jun\.|Jul\.|Aug\.|Sep\.|Okt\.|Nov\.|Dez\.)";
    const RGXBEFORE = "~(vor|bis|spätestens|spät\.|v\.)( [1-9][0-9]?\.)? ".self::RGPMONTH."? ?".self::RGPYEAR."~i";
    const RGXAROUND = "~(um|circa|ca\\.|wahrscheinlich|wohl|etwa|evtl\\.) ".self::RGPYEAR."~i";
    const RGXAFTER = "~(nach|frühestens|seit|ab) ".self::RGPYEAR."~i";

    const RGXCENTURY = "~^ *".self::RGPCENTURY."~";
    const RGXYEAR = "~^( *|erwählt *)".self::RGPYEAR."~";
    const RGXYEARFC = "~^( *|erwählt *)".self::RGPYEARFC."~";
    const STRIPCHARS = "†[]() ";
    const RGXBELEGT = "~belegt(.*)~";

    static public function parse($ds, $dir) {
        if (!in_array($dir, ['lower', 'upper'])) {
            throw new \InvalidArgumentException('$dir must be \'lower\' or \'upper\'');
        }

        $year = null;
        if (is_null($ds) || $ds == "") {
            return $year;
        }

        # strip 'belegt'
        $ds = preg_replace(self::RGXBELEGT, "", $ds);

        # remove certain non numeric characters
        $ds = trim($ds, self::STRIPCHARS);


        # turn of the century
        $cyear = [];
        preg_match(self::RGXTCENTURY, $ds, $cyear);
        if (count($cyear) >= 3) {
            $year = (int)$cyear[2] * 100;
            return $year;
        }

        # quarter
        $rgxq = [self::RGX1QCENTURY, self::RGX2QCENTURY, self::RGX3QCENTURY, self::RGX4QCENTURY];
        foreach ($rgxq as $q => $rgx) {
            preg_match($rgx, $ds, $cyear);
            if (count($cyear) >= 4) {
                $century = (int)$cyear[3];
                if ($dir == 'lower') {
                    $year = ($century - 1) * 100 + $q * 25 + 1;
                    return $year;
                } elseif ($dir == 'upper') {
                    $year = ($century - 1) * 100 + ($q + 1) * 25;
                    return $year;
                }
            }
        }

        # begin, middle, end
        $rgxq = [self::RGX1TCENTURY, self::RGX2TCENTURY, self::RGX3TCENTURY];
        foreach ($rgxq as $q => $rgx) {
            preg_match($rgx, $ds, $cyear);
            if (count($cyear) >= 3) {
                $century = (int)$cyear[2];
                if ($dir == 'lower') {
                    $year = ($century - 1) * 100 + $q * 33 + 1;
                    return $year;
                } elseif ($dir == 'upper') {
                    $year = ($century - 1) * 100 + ($q + 1) * 33 + ($q == 2 ? 1 : 0);
                    return $year;
                }
            }
        }

        # half
        $rgxq = [self::RGX1HCENTURY, self::RGX2HCENTURY];
        foreach ($rgxq as $q => $rgx) {
            preg_match($rgx, $ds, $cyear);
            if (count($cyear) >= 4) {
                $century = (int)$cyear[3];
                if ($dir == 'lower') {
                    $year = ($century - 1) * 100 + $q * 50 + 1;
                    return $year;
                } elseif ($dir == 'upper') {
                    $year = ($century - 1) * 100 + ($q + 1) * 50;
                    return $year;
                }
            }
        }

        # between
        $cyear = [];
        preg_match(self::RGXBETWEEN, $ds, $cyear);
        if (count($cyear) >= 3) {
            if ($dir == 'lower') {
                $year = (int)$cyear[1];
                return $year;
            } elseif ($dir == 'upper') {
                $year = (int)$cyear[2];
                return $year;
            }
        }

        # century
        $cyear = [];
        preg_match(self::RGXEARLYCENTURY, $ds, $cyear);
        if (count($cyear) >= 2) {
            $century = (int)$cyear[1];
            if ($dir == 'lower') {
                $year = ($century - 1) * 100 + 1;
                return $year;
            } elseif ($dir == 'upper') {
                $year = ($century - 1) * 100 + 20;
                return $year;
            }
        }

        # century
        $cyear = [];
        preg_match(self::RGXLATECENTURY, $ds, $cyear);
        if (count($cyear) >= 2) {
            $century = (int)$cyear[1];
            if ($dir == 'lower') {
                $year = $century * 100 - 19;
                return $year;
            } elseif ($dir == 'upper') {
                $year = $century * 100;
                return $year;
            }
        }

        # before, around, after
        $cyear = [];
        preg_match(self::RGXBEFORE, $ds, $cyear);
        if (count($cyear) >= 5) {
            $year = (int)$cyear[4];
            if ($dir == 'lower') {
                $year -= 50;
            }
            return $year;
        }

        $cyear = [];
        preg_match(self::RGXAFTER, $ds, $cyear);
        if (count($cyear) >= 3) {
            $year = (int)$cyear[2];
            if ($dir == 'upper') {
                $year += 50;
            }
            return $year;
        }

        $cyear = [];
        preg_match(self::RGXAROUND, $ds, $cyear);        
        if (count($cyear) >= 3) {
            $year = (int)$cyear[2];
            if ($dir == 'lower') {
                $year -= 5;
                return $year;
            } elseif ($dir == 'upper') {
                $year += 5;
                return $year;
            }
        }

        # plain year
        $cyear = [];
        preg_match(self::RGXYEAR, $ds, $cyear);
        if (count($cyear) >= 3) {
            $year = (int)$cyear[2];
            return $year;
        }

        return $year;

    }



};
